<?php

class Pk_Facebook_CustomerController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_id = $customer->getId();

        if ($customer_id) {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'))->sendResponse();
        }
    }

}
