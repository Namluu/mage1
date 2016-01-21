<?php

$installer = $this;

$tableInfo = $installer->getConnection()->describeTable($installer->getTable('sales/quote_payment'));
if (!isset($tableInfo["moduslink_data"])) {
    $installer->startSetup();

    $installer->run(
        "ALTER TABLE `{$installer->getTable('sales/quote_payment')}`
        ADD `moduslink_data` VARCHAR( 255 ) NOT NULL;"
    );

    $installer->endSetup();
}

$tableInfo = $installer->getConnection()->describeTable($installer->getTable('sales/order_payment'));

if (!isset($tableInfo["moduslink_data"])) {
    $installer->startSetup();

    $installer->run(
        "ALTER TABLE `{$installer->getTable('sales/order_payment')}`
        ADD `moduslink_data` VARCHAR( 255 ) NOT NULL;"
    );

    $installer->endSetup();
}

