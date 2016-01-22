/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    design
 * @package     rwd_default
 * @copyright   Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

Checkout.prototype.gotoSection = function (section, reloadProgressBlock) {
    // Adds class so that the page can be styled to only show the "Checkout Method" step
    if ((this.currentStep == 'login' || this.currentStep == 'billing') && section == 'billing') {
        $j('body').addClass('opc-has-progressed-from-login');
    }

    if (reloadProgressBlock) {
        this.reloadProgressBlock(this.currentStep);
    }
    this.currentStep = section;
    var sectionElement = $('opc-' + section);
    sectionElement.addClassName('allow');
    this.accordion.openSection('opc-' + section);

    // Scroll viewport to top of checkout steps for smaller viewports
    if (Modernizr.mq('(max-width: ' + bp.xsmall + 'px)')) {
        $j('html,body').animate({scrollTop: $j('#checkoutSteps').offset().top}, 800);
    }

    if (!reloadProgressBlock) {
        this.resetPreviousSteps();
    }
}

Payment.prototype._oldInitialize = Payment.prototype.initialize;
Payment.prototype.initialize = function(form, saveUrl, saveMdUrl){
    Payment.prototype._oldInitialize.call(this, form, saveUrl);
    this.saveMdUrl = saveMdUrl;
};
/* Override save of checkout for save purpose */
Payment.prototype.save = function(){
    if (checkout.loadWaiting!=false) return;
    var validator = new Validation(this.form);

    /* Check moduslink function */

    var isTrueForModuslink = true,
        isModuslink = jQuery('[name="payment[method]"]:checked').attr('data-type') === "moduslink",
        saveUrl = this.saveUrl, mdPMName = ''
        ;

    if(isModuslink) {

        /* Do nothing if moduslink is not exists */
        var $input = jQuery("#moduslink_list input:checked"), $dd = $input.parent("dt").next();
        if($dd.find("form.cnpForm").length === 0){
            return;
        }

        mdPMName = $dd.find('[name="ACCOUNT.BRAND"]').val();
        if($input.val().toLowerCase() === "md_creditcard") {

            isTrueForModuslink = window.cnp_Payment.validateCardSync();
        }

        saveUrl = this.saveMdUrl;
    }


    if (this.validate() && validator.validate() && isTrueForModuslink ) {
        checkout.setLoadWaiting('payment');
        var parameter = Form.serializeMdElements(Form.getElements(this.form), {denyKey: ["ACCOUNT.NUMBER", "ACCOUNT.EXPIRY_MONTH", "ACCOUNT.EXPIRY_YEAR", "ACCOUNT.HOLDER", "ACCOUNT.VERIFICATION", "FRONTEND.RESPONSE_URL", "FRONTEND.VERSION", "FRONTEND.MODE", "ACCOUNT.BRAND"]});

        if(isModuslink) {
            parameter["ACCOUNT.BRAND"] = encodeURIComponent(mdPMName);
        }

        var request = new Ajax.Request(
            saveUrl,
            {
                method:'post',
                onComplete: this.onComplete,
                onSuccess: this.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout),
                parameters: parameter
            }
        );
    }

};
/* Override form. Don't allow some special key */
Form.serializeMdElements = function(elements, options) {
    if (typeof options != 'object') options = { hash: !!options };
    else if (Object.isUndefined(options.hash)) options.hash = true;
    var key, value, submitted = false, submit = options.submit, accumulator, initial;

    if (options.hash) {
        initial = {};
        accumulator = function(result, key, value) {
            if (key in result) {
                if (!Object.isArray(result[key])) result[key] = [result[key]];
                result[key].push(value);
            } else result[key] = value;
            return result;
        };
    } else {
        initial = '';
        accumulator = function(result, key, value) {
            return result + (result ? '&' : '') + encodeURIComponent(key) + '=' + encodeURIComponent(value);
        }
    }

    return elements.inject(initial, function(result, element) {
        if (!element.disabled && element.name) {
            key = element.name; value = $(element).getValue();
            if (value != null && element.type != 'file' && (element.type != 'submit' || (!submitted &&
                submit !== false && (!submit || key == submit) && (submitted = true)))) {
                if(options.denyKey.indexOf(key) === -1) {
                    result = accumulator(result, key, value);
                }
            }
        }
        return result;
    });
};