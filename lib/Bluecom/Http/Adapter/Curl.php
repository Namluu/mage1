<?php

/**
 * HTTP CURL Adapter
 *
 * @category   Bluecom
 * @package    Bluecom_Http
 * @author     Bluecom group
 */
class Bluecom_Http_Adapter_Curl extends  Varien_Http_Adapter_Curl
{
    /**
     * Allow parameters
     *
     * @var array
     */
    protected $_allowedParams = array(
        'timeout'       => CURLOPT_TIMEOUT,
        'maxredirects'  => CURLOPT_MAXREDIRS,
        'proxy'         => CURLOPT_PROXY,
        'ssl_cert'      => CURLOPT_SSLCERT,
        'userpwd'       => CURLOPT_USERPWD,
        'ciphers_list'  => CURLOPT_SSL_CIPHER_LIST,
        'ssl_version'   => CURLOPT_SSLVERSION
    );

}
