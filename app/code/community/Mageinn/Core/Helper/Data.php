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
 * This class is required for common functions getting used around this module.
 * 
 * @author Mageinn
 * @package Mageinn_Core
 * @category Mageinn
 */
class Mageinn_Core_Helper_Data extends Mage_Core_Helper_Data
{
    protected $_allRewrites;
    protected $_actualRewrites = array();
    
    
    /**
     * Example:
     * $config->checkRewrite('catalog/product', 'models');
     * 
     * @param string $className
     * @param string $cType
     * @return mixed
     */
    public function checkRewrite($className, $cType = 'models')
    {
        // All Rewrites
        $allRewrites = $this->getAllRewrites();
        
        // Actual rewrites
        $actualRewrites = $this->getActualRewrites($cType);
        
        // Check Logic
        if(array_key_exists($className, $allRewrites[$cType])) {
            foreach($allRewrites[$cType][$className] as $c) {
                if(in_array($c, $actualRewrites)) {
                    return $c;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get all rewrites for a particular class and type
     * 
     * @param string $className
     * @param string $cType
     * @return array
     */
    public function getRewrites($className, $cType = 'models')
    {
        $allRewrites = $this->getAllRewrites();
        return $allRewrites[$cType][$className];
    }
    
    /**
     * Get all Magento rewrites
     * 
     * @return array
     */
    public function getAllRewrites()
    {
        if(!$this->_allRewrites) {
            $folders = array('app/code/local/', 'app/code/community/');//folders to parse
            $configFiles = array();
            foreach ($folders as $folder){
                $files = glob($folder.'*/*/etc/config.xml');//get all config.xml files in the specified folder
                $configFiles = array_merge($configFiles, $files);//merge with the rest of the config files
            }
            $rewrites = array();//list of all rewrites

            foreach ($configFiles as $file){
                $dom = new DOMDocument;
                $dom->loadXML(file_get_contents($file));
                $xpath = new DOMXPath($dom);
                    $path = '//rewrite/*';//search for tags named 'rewrite'
                    $text = $xpath->query($path);
                    foreach ($text as $rewriteElement){
                        $type = $rewriteElement->parentNode->parentNode->parentNode->tagName;//what is overwritten (model, block, helper)
                        $parent = $rewriteElement->parentNode->parentNode->tagName;//module identifier that is being rewritten (core, catalog, sales, ...)
                        $name = $rewriteElement->tagName;//element that is rewritten (layout, product, category, order)
                        foreach ($rewriteElement->childNodes as $element){
                            $rewrites[$type][$parent.'/'.$name][] = $element->textContent;//class that rewrites it
                        }
                    }
            }
            $this->_allRewrites = $rewrites;
        }
        
        return $this->_allRewrites;
    }
    
    /**
     * Get actual Magento rewrites
     * 
     * @param string $cType
     * @return array
     */
    public function getActualRewrites($cType)
    {
        if(!isset($this->_actualRewrites[$cType])) {
            $this->_actualRewrites[$cType] = array();
            $xml = Mage::getConfig()->getNode()->xpath('//global/' . $cType . '//rewrite');
            foreach($xml as $r) {
                $classes = (array) $r;
                foreach($classes as $key => $class) {
                    $this->_actualRewrites[$cType][] = $class;
                }
            }
        }
        return $this->_actualRewrites[$cType];
    }
    
    /**
     * Common function to send notification emails    
     * 
     * @param array $vars
     * @param string $template_id
     * @param type $recipient
     * @param type $storeId
     */
    public function sendMail($vars, $template_id, $recipient, $storeId = null)
    {
        if(!preg_match('/twitter-user\.com$/', $recipient)) {
            $emailTemplate  = Mage::getModel('core/email_template');
            $emailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->sendTransactional(
                            $template_id,
                            'general',
                            $recipient,
                            null,
                            $vars,
                            $storeId
                            );
        }
    }
}