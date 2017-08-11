<?php

class Pk_Facebook_Model_Observer {

    public function saveOrderAfter($observer) {

        $order = $observer->getOrder();
        $quote = $observer->getQuote();


        $orderid = $order->getId();

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerid = $customer->getId();
        $facebookid = $customer->getFbid();

        $datetime = $order->getCreatedAtStoreDate()->toString();

try{

        foreach ($order->getItemsCollection() as $item) {


            if ($item->getProductType() == 'grouped') {
                $super_product = $item->getProductOptions();
                $productid = $super_product['super_product_config']['product_id'];
            } else {

                if ($item->getParentItem())
                    continue;
                $productid = $item->getProductId();
            }

            $facebookorder = Mage::getModel('facebook/facebookorder');

            $previous_order = $facebookorder->getCollection()->addFilter('productid', $productid)->addFilter('fbid', $facebookid);

            $count = $previous_order->count();
         //   $oid = $previous_order->getData();
      //      print_r($previous_order->getData());
          //  $oid = $oid['0']['id'];


            if ($count == "") {

                $facebookorder->setOrderid($orderid)->setProductid($productid)->setFbid($facebookid)->setCustomerid($customerid)->setOrderdate($datetime);
                $facebookorder->save();
            } else {
            // echo $datetime;

                $newDate = date("Y-m-d h:i:s", strtotime($datetime));

                $write = Mage::getSingleton('core/resource')->getConnection('core_write');

                $write->query("update facebookorder set orderdate='$newDate' where productid='$productid' AND fbid='$facebookid'");
            }
        }
        
        } catch (Exception $e) {
                $response['success'] = false;
                $response['message'] = $e->getMessage();
            //    print_r($e->getMessage());die;
            }
    }

    public function saveBeforeCategory($category) {

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerid = $customer->getId();

        $facebookorder = Mage::getModel('facebook/facebook')->getCollection()->addFilter('facebookusermagentoid', $customerid);

        $array = array();
        foreach ($facebookorder as $val) {
            $array[] = $val->getMagentoconnect();
        }

        Mage::register('facebook_connect', $array);
    }

    public function insertBlock($observer) {
        $_block = $observer->getBlock();
        $transport = $observer->getTransport();
        $_type = $_block->getType();
        /* Check block type */
        if (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'facebook/index/index') {
            return $this;
        }
        if ($_type == 'customer/form_register' || $_type == 'customer/form_login' || $_type == "checkout/onepage_login") {
            //$stHtml = $this->getHtml();

            $stHtml = $this->getHtml();
            $html = $transport->getHtml();
            $html = str_replace('<form', $stHtml . '<form', $html);
            // $html .= $stHtml;
            $transport->setHtml($html);
        }
    }

    public function getHtml() {
        $appid = Mage::getStoreConfig('facebook/facebook_group/facebook_input1');
        $clientid = Mage::getStoreConfig('facebook/google_group/google_input1');
        $clientsecret = Mage::getStoreConfig('facebook/google_group/google_input2');
        $redurl = Mage::getStoreConfig('facebook/google_group/google_input3');
        $ajaxurl = Mage::getUrl('facebook/index/facebooklogin');
        $gajaxurl = Mage::getUrl('facebook/index/googlelogin');
        $html = '<div id="login_fb"><a href="#" onclick="return fblogin();" class="fb_button_ps"><img src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
                'frontend/base/default/images/fb.png' . '" alt="Connect with Facebook" /></a></div>  <br/>';

        $html .= '

                <script>


                  window.fbAsyncInit = function() {
                    FB.init({
                      appId      : "' . $appid . '",
                      scope	 :  "email",
                      xfbml      : true,
                      version    : "v2.2"
                    });
                  };

                  (function(d, s, id){
                     var js, fjs = d.getElementsByTagName(s)[0];
                     if (d.getElementById(id)) {return;}
                     js = d.createElement(s); js.id = id;
                     js.src = "//connect.facebook.net/en_US/sdk.js";
                     fjs.parentNode.insertBefore(js, fjs);
                   }(document, "script", "facebook-jssdk"));

                function testAPI() {
                    FB.api("/me", function(response) {

                        new Ajax.Request("' . $ajaxurl . '", {
                            method:"post"
                            , parameters: {
                                   email: response.email,
                                   fbid: response.id,
                                   fname: response.first_name,
                                   lname: response.last_name,
                                   gender: response.gender	 
                                  }
                            , requestHeaders: {Accept: "application/json"},
                            onSuccess: function(transport) {
                                 timeresult = transport.responseText.evalJSON();
                                if(timeresult.url)
                                {		
                                window.location="' . Mage::getBaseUrl() . '"+timeresult.url;
                                }
                                else
                                {
                                window.location.reload();	
                                }			
                                 }
                          });

                    });
                  }
                </script>';

         $html .= ' <script>
                       function fblogin(){
                      FB.login(function(response) {
                testAPI();
                  if (response.status === "connected") {

                  } else if (response.status === "not_authorized") {

                  } else {
                  }
                },{scope: "email"});

                    }
                </script>';

        return $html;
    }

}
