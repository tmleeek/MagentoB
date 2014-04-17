<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Tiercustomergroup {
    public function toOptionArray()
    {
        $options = array();
        $options[] = array('value'=>Mage_Customer_Model_Group::CUST_GROUP_ALL,'label'=>'All Groups');

        $groups = Mage::getModel('customer/group')->getCollection();
        foreach($groups as $group) {
            $options[]=array('value'=>$group->getCustomerGroupId(),'label'=>$group->getCustomerGroupCode());
        }

        RETURN $options;
    }
}