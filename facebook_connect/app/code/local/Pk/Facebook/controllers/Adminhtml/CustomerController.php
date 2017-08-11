<?php

require_once 'Mage/Adminhtml/controllers/CustomerController.php';

class Pk_Facebook_Adminhtml_CustomerController extends Mage_Adminhtml_CustomerController {

    public function facebookaccountAction() {

        $this->_initCustomer();
        $this->getResponse()->setBody($this->getLayout()->createBlock('facebook/adminhtml_customer_edit_tab_facebookaccount', 'admin.customer.facebookaccount')->setCustomerId(Mage::registry('current_customer')->getId())->setUseAjax(true)->toHtml());
    }

}
