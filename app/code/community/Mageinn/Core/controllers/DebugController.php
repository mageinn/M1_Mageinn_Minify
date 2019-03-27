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
 * Mageinn_Core Debug Controller
 *
 * @author Mageinn
 * @package Mageinn_Core
 * @category Mageinn
 */
class Mageinn_Core_DebugController extends Mage_Core_Controller_Front_Action
{
    /**
     * Checks rewrites conflicts for a particular module
     */
    public function testAction()
    {
        $m  = $this->getRequest()->getParam('m');
        $helper = Mage::helper('mageinn_core');
        switch($m){
            case 'hints':
                $result = $helper->getRewrites('catalog/layer_filter_attribute', 'blocks');
                var_dump($result);
                break;
            case 'adminextra':
                $result = $helper->getRewrites('adminhtml/catalog_product_edit_action_attribute', 'helpers');
                var_dump($result);
                break;
            case 'priceslider':
                $result = $helper->getRewrites('catalog/layer_filter_price');
                var_dump($result);
                $result2 = $helper->getRewrites('catalog_resource/layer_filter_price');
                var_dump($result2);
                $result3 = $helper->getRewrites('catalog/layer_filter_attribute');
                var_dump($result3);
                $result4 = $helper->getRewrites('catalog_resource/layer_filter_attribute');
                var_dump($result4);
                $result5 = $helper->getRewrites('catalog/layer_filter_category');
                var_dump($result4);
                $result6 = $helper->getRewrites('catalogsearch/layer_filter_attribute');
                var_dump($result4);
                break;
        }
    }
}
