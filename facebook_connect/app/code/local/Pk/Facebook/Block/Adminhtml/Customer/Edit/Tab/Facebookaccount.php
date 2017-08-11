<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * admin customer left menu
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Pk_Facebook_Block_Adminhtml_Customer_Edit_Tab_Facebookaccount extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {

        $log_customer = Mage::getSingleton('customer/customer')->load(Mage::registry('current_customer')->getId());
        $facebook_id = $log_customer->getFbid();

        $facebook_data = $log_customer->getfacebookdata();
        $facebook_data = json_decode($facebook_data, TRUE);

        $html = "<div class='entry-edit' id='facebook_profile'>
                <div class='entry-edit-head'>
                <h4 class='icon-head head-customer-view'>Facebook Profile</h4>
                </div>
                <fieldset><table class='box-left' cellspacing='2'>
                <tbody>
                <tr>
                <td class='label'>
                <strong>Facebook Id:</strong>
                </td>
                <td class='value'><input class='input-text' type='text' value='" . $facebook_id . "' disabled='disabled'></td>
                </tr><tr class='headings' ><td colspan='2'><strong>Profile Details :</strong></td></tr>";

        foreach ($facebook_data as $key => $val) {
            if ($key == "profile") {
                foreach ($val as $key_profile => $val_profile) {
                    $html .= "<tr><td>" . $key_profile . "</td><td>" . $val_profile . "</td> </tr>";
                }
            }
        }

        $html .= "</tbody></fieldset></div>";
        print_r($html);
    }

}
