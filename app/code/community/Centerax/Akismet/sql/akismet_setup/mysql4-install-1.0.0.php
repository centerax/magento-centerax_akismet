<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('centerax_akismet')}`;
CREATE TABLE `{$this->getTable('centerax_akismet')}` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `type` enum('contact', 'review') NOT NULL default 'contact',
  `status` int(1) unsigned NOT NULL default 1 COMMENT 'SPAM status',
  `extra`  text NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

$installer->endSetup();