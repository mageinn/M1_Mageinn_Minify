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
 * AdminNotification Feed model
 *
 * @category   Mageinn
 * @package    Mageinn_Core
 * @author     Mageinn
 */
class Mageinn_Core_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl() 
    {
        return 'http://mageinn.com/feed.php';
    }
    
    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return Mage::app()->loadCache('mageinn_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return Mage_AdminNotification_Model_Feed
     */
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'mageinn_notifications_lastcheck');
        return $this;
    }
}
