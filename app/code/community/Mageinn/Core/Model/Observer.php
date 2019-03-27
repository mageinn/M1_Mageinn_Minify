<?php
/**
 * Mageinn_Core extension
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
 * @category    Mageinn
 * @package     Mageinn_Core
 * @copyright   Copyright (c) 2016 Mageinn. (http://mageinn.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Observer
 *
 * @category   Mageinn
 * @package    Mageinn_Core
 * @author     Mageinn
 */
class Mageinn_Core_Model_Observer
{
    /**
     * This function will remove any layout files that have 
     * ifconfig dependancy
     *
     * @param Varien_Event_Observer $observer
     */
    public function removeInactiveLayouts(Varien_Event_Observer $observer) {
        $updatesRoot = $observer->getEvent()->getUpdates();
        $unsetFiles = array();
        
        foreach($updatesRoot->children() as $key => $child) {
            if (isset($child['ifconfig']) && ($configPath = (string)$child['ifconfig'])) {
                if (!Mage::getStoreConfigFlag($configPath)) {
                    $unsetFiles[] = $key;
                }
            }
        }
        
        foreach($unsetFiles as $unset) {
            unset($updatesRoot->$unset);
        }
    }
    
    /**
     * Predispath admin action controller
     *
     * @param Varien_Event_Observer $observer
     */
    public function preDispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {

            $feedModel  = Mage::getModel('mageinn_core/feed');
            /* @var $feedModel Mageinn_Core_Model_Feed */

            $feedModel->checkUpdate();
        }
    }
}