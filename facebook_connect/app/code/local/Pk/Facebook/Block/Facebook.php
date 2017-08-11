<?php

class Pk_Facebook_Block_Facebook extends Mage_Core_Block_Template {

    private $_username = -1;

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl() {
        return $this->helper('customer')->getLoginPostUrl();
    }

    /**
     * Retrieve create new account url
     *
     * @return string
     */
    public function getCreateAccountUrl() {
        $url = $this->getData('create_account_url');
        if (is_null($url)) {
            $url = $this->helper('customer')->getRegisterUrl();
        }
        return $url;
    }

    /**
     * Retrieve password forgotten url
     *
     * @return string
     */
    public function getForgotPasswordUrl() {
        return $this->helper('customer')->getForgotPasswordUrl();
    }

    /**
     * Retrieve username for form field
     *
     * @return string
     */
    public function getUsername() {
        if (-1 === $this->_username) {
            $this->_username = Mage::getSingleton('customer/session')->getUsername(true);
        }
        return $this->_username;
    }

    public function getFacebook() {
        if (!$this->hasData('facebook')) {
            $this->setData('facebook', Mage::registry('facebook'));
        }
        return $this->getData('facebook');
    }

     public function getfacebookno($id) {
        
        //$id = $this->getRequest()->getParam('id');
        $facebookordercount = Mage::getModel('facebook/facebookorder')->getCollection()->addFieldToFilter('productid', $id)->count();

        return $facebookordercount;
     
    }

    public function getfacebookid() {
        $log_customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_fb_id = $log_customer->getFbid();
        return $customer_fb_id;
    }

    public function getFb() {
        $log_customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($log_customer->getFbid()) {
            $display = "Disconnect";
        } else {
            $display = "Connect";
        }
        return $display;
    }

    public function getprofiledata() {

        $log_customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_facebook_data = $log_customer->getFacebookdata();
        $customer_facebook_data = json_decode($customer_facebook_data, TRUE);
        return $customer_facebook_data;
    }

    public function getconnectdata() {
        $log_customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_facebook_id = $log_customer->getFbid();
        $log_customer = Mage::getModel('facebook/facebook')->getCollection()->addFilter('facebookuser', $customer_facebook_id);
        $facebook_connect = $log_customer->getData();
        $connects = array();
        foreach ($facebook_connect as $val) {
            if ($val['magentoconnect']) {
                $log_customer = Mage::getSingleton('customer/customer')->load($val['magentoconnect']);
                $connect = $log_customer->getFacebookdata();
                $connects[] = json_decode($connect, TRUE);
            }
        }
        return $connects;
    }

}
