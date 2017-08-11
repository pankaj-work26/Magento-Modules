<?php

class Pk_Facebook_ProductController extends Mage_Catalog_ProductController {

    protected function _initProduct() {

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerid = $customer->getId();

        $facebookorder = Mage::getModel('facebook/facebook')->getCollection()->addFilter('facebookusermagentoid', $customerid);

        $array = array();
        foreach ($facebookorder as $val) {
            $array[] = $val->getMagentoconnect();
        }

        Mage::register('facebook_connect', $array);


        return parent::_initProduct();
    }

}
