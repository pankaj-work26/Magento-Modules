<?php

class Pk_Facebook_Model_Facebooksave extends Mage_Core_Model_Abstract {

    public function Saveuserconnect($id, $data) {


        $data_array = json_decode($data, TRUE);


        // updating the magentoconnect column in table "facebook" that were previously added
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_id = $customer->getId();
        $write->query("update facebook set magentoconnect='$customer_id' where facebookconnect='$id'");

        // inserting the connects of user in the table

        foreach ($data_array['friends'] as $key => $val) {
            $log_customer = Mage::getModel('facebook/facebook');

            //inserting to the magentoconnect column of the user
            $log_custome = $log_customer->getCollection()->addFilter('facebookuser', $val['id']);
            $user_magentoconnect = $log_custome->getFirstItem()->getFacebookusermagentoid();

            if ($user_magentoconnect) {

                $log_customer->setFacebookusermagentoid($customer_id)->setFacebookuser($id)->setFacebookconnect($val['id'])->setMagentoconnect($user_magentoconnect);
            } else {
                $log_customer->setFacebookusermagentoid($customer_id)->setFacebookuser($id)->setFacebookconnect($val['id']);
            }
            $log_customer->save();
        }

        return "success";
    }

}
