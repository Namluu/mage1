<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS `{$installer->getTable('tomblog/category')}`;
    CREATE TABLE `{$installer->getTable('tomblog/category')}` (
        `id` int(11) NOT NULL auto_increment,
        `title` text,
        `description` text,
        `date` datetime default NULL,
        `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
        `status` int(1) default 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
    DROP TABLE IF EXISTS `{$installer->getTable('tomblog/article')}`;
    CREATE TABLE `{$installer->getTable('tomblog/article')}` (
        `id` int(11) NOT NULL auto_increment,
        `title` text,
        `content` text,
        `date` datetime default NULL,
        `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
        `category_id` int(11) NOT NULL,
        `customer_id` int(11) NOT NULL,
        `status` int(1) default 1,
        PRIMARY KEY (`id`),
        KEY `category_id` (`category_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ALTER TABLE `{$installer->getTable('tomblog/article')}` ".
    "ADD CONSTRAINT `FK_{$installer->getTable('tomblog/article')}_ref` ".
    "FOREIGN KEY (`category_id`) REFERENCES `{$installer->getTable('tomblog/category')}` (`id`) ".
    "ON DELETE CASCADE ON UPDATE CASCADE;
");

$installer->endSetup();