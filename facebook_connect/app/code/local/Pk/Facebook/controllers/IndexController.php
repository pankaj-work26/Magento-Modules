<?php

class Pk_Facebook_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        $this->loadLayout();
        $this->renderLayout();
    }

    public function customerAction() {

        $this->loadLayout();
        $this->renderLayout();
    }

    public function facebookloginAction() {
        $response = array();
        $websiteid = Mage::app()->getWebsite()->getId();
        $email = $this->getRequest()->getParam('email');
        $fname = $this->getRequest()->getParam('fname');
        $lname = $this->getRequest()->getParam('lname');
        $fbid = $this->getRequest()->getParam('fbid');
        $time = time();
        $hash = md5($time);
        $password = $hash;
        if (!empty($email)) {
            try {
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId($websiteid);
                $customer->loadByEmail($email); //load customer by email i 
                /* if customer has ,then login */
                if ($customer->getId() > 0) {

                    if ($customer->getData('fbid') == $fbid) {
                        $session = Mage::getSingleton('customer/session');
                        $session->loginById($customer->getId());
                    } else {
                        $response['url'] = "facebook/index/index";
                        $_SESSION['fbemail'] = $email;
                        Mage::getSingleton('core/session')->addError('You already have account with us');
                    }
                    $response['success'] = true;
                } else {

                    $customer = Mage::getModel("customer/customer");
                    $customer->setWebsiteId($websiteid)
                            ->setFirstname($fname)
                            ->setLastname($lname)
                            ->setEmail($email)
                            ->setFbid($fbid)
                            ->setPassword($password);

                    try {
                        $customer->save();
                        $customer->sendNewAccountEmail();
                        $session = Mage::getSingleton('customer/session');
                        $session->loginById($customer->getId());
                    } catch (Exception $e) {
                        Zend_Debug::dump($e->getMessage());
                    }
                }
            } catch (Exception $e) {
                $response['success'] = false;
                $response['message'] = $e->getMessage();
            }
            $this->getResponse()->setBody(Zend_Json::encode($response));
        }
    }

    public function disconnectAction() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_id = $customer->getId();

        try {

            $customer = Mage::getModel("customer/customer")->load($customer_id);
            $customer->setLinkedinid(null);
            $customer->setGoogleid(null);
            $customer->setFbid(null);
            $customer->save();
            Mage::getSingleton('core/session')->addSuccess("Facebook Account  Disconnected Successfully.");
        } catch (Exception $e) {

            Mage::getSingleton('core/session')->addError("Facebook Account Disconnection unsuccessfully.");
            $this->_redirectReferer();
        }
        $this->_redirectReferer();
        return;
    }

    public function setfacebookAction() {
        $response = array();
        $fbid = $this->getRequest()->getParam('fbid');
        // $linkedin_data = $this->getRequest()->getParam('data');

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_id = $customer->getId();

        if (!empty($fbid)) {
            try {
                $customer = Mage::getModel("customer/customer")->load($customer_id);

                $customer->setFbid($fbid);
                $customer->save();
                Mage::getSingleton('core/session')->addSuccess("Facebook Account Connected Successfully.");

                $response['success'] = true;
            } catch (Exception $e) {
                $response['success'] = false;
                $response['message'] = $e->getMessage();
            }
            $this->getResponse()->setBody(Zend_Json::encode($response));
        }
    }

    public function connectAction() {

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_id = $customer->getId();

        $googleid = $customer->getgoogleid();
        $linkedinid = $customer->getLinkedinid();
        $fbid = $customer->getFbid();

        $response = array();

        if ($googleid == '' && $linkedinid == '' && $fbid == '') {
            $response['result'] = true;
        } else {
            $response['result'] = false;
            $response['msg'] = 'You are already connected with other facebook account.';
        }

        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    public function savefacebookprofileAction() {
        $id = $this->getRequest()->getParam('id');
        $profile_data = $this->getRequest()->getParam('profile_data');
        $friend_data = $this->getRequest()->getParam('friends_data');

        $data['id'] = $id;
        $data['profile'] = json_decode($profile_data, true);
        $data['friends'] = json_decode($friend_data, true);

        $data = json_encode($data);
        $log_customer = Mage::getSingleton('customer/session')->getCustomer();
        $log_customer->setFbid($id)->setFacebookdata($data);


        $model = Mage::getModel('facebook/facebooksave')->Saveuserconnect($id, $data);
        if ($model == "success") {
            try {
                $log_customer->save();
                $result = "success";
            } catch (Exception $e) {
                $result = "fail to add the facebook details to the user account.";
            }
        } else {
            $result = "fail to add the facebook details to the user account.";
        }


        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function removefacebookprofileAction() {

        $log_customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerid = $log_customer->getId();
        $log_customer->setFbid("")->setFacebookdata("");
        $log_customer->save();

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $write->query("delete from facebook where facebookusermagentoid='$customerid'");


        $result = "success";
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function openfancyAction() {

        $id = $this->getRequest()->getParam('id');

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerid = $customer->getId();


        $facebookorder = Mage::getModel('facebook/facebook')->getCollection()->addFilter('facebookusermagentoid', $customerid);

        $array = array();
        foreach ($facebookorder as $val) {
            $array[] = $val->getMagentoconnect();
        }

        $facebookorder = Mage::getModel('facebook/facebookorder')->getCollection()->addFieldToFilter('productid', $id);


        $html = "<div class='facebook_details'>";
        $friendsHtml ='';
        $otherHtml ='';
                
        foreach ($facebookorder->getData() as $value) {

            $now = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));

            $date1 = $value['orderdate'];
            $date2 = $now;


            $diff = abs(strtotime($date2) - strtotime($date1));
            $years = floor($diff / (365 * 60 * 60 * 24));
            $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
            $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
            $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
            $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));



            if ($years == 0) {
                if ($months == 0) {
                    if ($days == 0) {
                        if ($hours == 0) {
                            if ($minutes == 0) {
                                if ($seconds == 0) {
                                    
                                } else {
                                    $ordertime = $seconds . " sec ago";
                                }
                            } else {
                                $ordertime = $minutes . " min " . $seconds . " sec ago";
                            }
                        } else {
                            $ordertime = $hours . " hr " . $minutes . " min ago";
                        }
                    } else {
                        $ordertime = $days . " days " . $hours . " hr ago";
                    }
                } else {
                    $ordertime = $months . " months " . $days . " days ago";
                }
            } else {
                $ordertime = $years . " years " . $months . " months ago";
            }



            $fb_icon = Mage::getDesign()->getSkinUrl('images/facebook/fb_icon.png');
            $customer_facebook_data = Mage::getSingleton('customer/customer')->load($value['customerid'])->getfacebookdata();
            $facebook_data = json_decode($customer_facebook_data, TRUE);
       
            $connectHtml = " <div class='connect' id='" . $facebook_data['id'] . "'><a class='facebook_profile_link' href=' ". $facebook_data['profile']['link']."' target='_blank'><div class='img fb_user_details'><img class='profile_img'  src='" . stripslashes("http://graph.facebook.com/" . $facebook_data['id'] . "/picture?type=large") . "' ><div class='data_details'><span class='name'>" . $facebook_data['profile']['name'] . "</span><br><span class='gender'>( " . $facebook_data['profile']['gender'] . " )</span><br>  <div class='other'>" . $ordertime . " </div></div><i class='icon ion-social-facebook' ></i></div><div class='clear'></div></a></div>";
          
            $connectHtml .="  <script type='text/javascript'>
                    jQuery('.fb_user_details').on('mouseenter', function(event){
                         jQuery(this).find( '.data_details' ).slideDown();
                    });
                    jQuery('.fb_user_details').on('mouseleave', function(event){
                         jQuery(this).find( '.data_details' ).slideUp();
                    });
                     </script>";
         //   echo $value['customerid'];
            if(in_array($value['customerid'], $array)){
                $friendsHtml .=$connectHtml;
            }else{
                $otherHtml .=$connectHtml;
            }
        }
     //   print_r($array);
        if($friendsHtml){
            $html .= 'Facebook Friends Chooses this product:'.$friendsHtml ;
        }
        if($otherHtml){
            $html .= 'Other Facebook Users Chooses this product:'.$otherHtml ;
        }
        
         $html .= "</div>";
        $this->getResponse()->setBody(Zend_Json::encode($html));
    }

}
