<?php

$installer = $this;

$installer->startSetup();
$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$setup->addAttribute('customer', 'fbid', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'fbid',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'default' => '0',
    'visible_on_front' => 1,
    'source' => '',
));
$setup->addAttribute('customer', 'facebookdata', array(
    'type' => 'text',
    'input' => 'text',
    'label' => 'facebookdata',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'default' => '0',
    'visible_on_front' => 1,
    'source' => '',
));
if (version_compare(Mage::getVersion(), '1.6.0', '<=')) {
    $customer = Mage::getModel('customer/customer');
    $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
    $setup->addAttributeToSet('customer', $attrSetId, 'General', 'fbid');
    $setup->addAttributeToSet('customer', $attrSetId, 'General', 'facebookdata');
}
if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
    Mage::getSingleton('eav/config')
            ->getAttribute('customer', 'fbid')
            ->setData('used_in_forms', array('adminhtml_customer', 'customer_account_create', 'customer_account_edit', 'checkout_register'))
            ->save();
    Mage::getSingleton('eav/config')
            ->getAttribute('customer', 'facebookdata')
            ->setData('used_in_forms', array('adminhtml_customer', 'customer_account_create', 'customer_account_edit', 'checkout_register'))
            ->save();
}
$installer->endSetup();

//installing table for facebook in user connects

$installer = $this;

$installer->startSetup();

$installer->run("

 DROP TABLE IF EXISTS {$this->getTable('facebook')};
CREATE TABLE {$this->getTable('facebook')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `facebookusermagentoid` varchar(255) NOT NULL default '',
 `facebookuser` varchar(255) NOT NULL default '',
  `facebookconnect` text default '',
  `magentoconnect` text default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();

//installing table for facebook  user connects for storing orders

$installer = $this;

$installer->startSetup();

$installer->run("

 DROP TABLE IF EXISTS {$this->getTable('facebookorder')};
CREATE TABLE {$this->getTable('facebookorder')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `orderid` varchar(255) NOT NULL default '',
  `orderdate` datetime NOT NULL,
  `productid` varchar(255) NOT NULL default '',
  `fbid` varchar(255) default '',
  `customerid` varchar(255) default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();



