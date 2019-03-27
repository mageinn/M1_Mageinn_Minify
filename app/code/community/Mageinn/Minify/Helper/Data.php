<?php
/**
 * Mageinn_Minify extension
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
 * @package     Mageinn_Minify
 * @copyright   Copyright (c) 2016 Mageinn. (http://mageinn.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Mageinn Minify
 *
 * @category   Mageinn
 * @package    Mageinn_Minify
 * @author     Mageinn
 */
class Mageinn_Minify_Helper_Data extends Mage_Core_Helper_Abstract {
    
    const XML_PATH_MINIFY_CSS_JS_ENABLED = 'mageinn_minify/minify_css_js/enabled';
    const XML_PATH_MINIFY_CSS_JS_REMOVE_COMMENTS = 'mageinn_minify/minify_css_js/remove_comments';
    const XML_PATH_MINIFY_CSS_JS_ENABLE_YUICOMPRESSOR  = 'mageinn_minify/minify_css_js/enable_yuicompressor';
    const XML_PATH_MINIFY_CSS_JS_VERSION_TYPE  = 'mageinn_minify/minify_css_js/version_type';
    const XML_PATH_MINIFY_CSS_JS_VERSION_CSS  = 'mageinn_minify/minify_css_js/version_css';
    const XML_PATH_MINIFY_CSS_JS_VERSION_JS  = 'mageinn_minify/minify_css_js/version_js';
    
    /**
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MINIFY_CSS_JS_ENABLED);
    }
    
    /**
     * 
     * @return bool
     */
    public function getRemoveCommentsFlag()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MINIFY_CSS_JS_REMOVE_COMMENTS);
    }
    

    /**
     * @return bool
     */
    public function isYUICompressEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MINIFY_CSS_JS_ENABLE_YUICOMPRESSOR);
    }
    
    /**
     * 
     * @return int
     */
    public function getVersionType()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_MINIFY_CSS_JS_VERSION_TYPE);
    }
    
    /**
     * 
     * @return string
     */
    public function getJsVersion()
    {
        return Mage::getStoreConfig(self::XML_PATH_MINIFY_CSS_JS_VERSION_JS);
    }
    
    /**
     * 
     * @return string
     */
    public function getCssVersion()
    {
        return Mage::getStoreConfig(self::XML_PATH_MINIFY_CSS_JS_VERSION_CSS);
    }
    
    /**
     * 
     * @return string
     */
    public function getVersion($type)
    {
        switch ($type) {
            case 'js':
                return $this->getJsVersion();
            case 'css':
                return $this->getCssVersion();
            default:
                return '';
        }
    }
    
    
    /**
     * Returns array of paths that will be scaned for css and js files.
     * 
     * @return array
     */
    public function minifyCss($unoptimized) 
    {
        if(!$this->isEnabled()) {
            return $unoptimized;
        }
        
        $YUICompressorFailed = false;
        if ($this->isYUICompressEnabled()) {
            Minify_YUICompressor::$jarFile = Mage::getBaseDir().DS.'lib'.DS.'yuicompressor'.DS.'yuicompressor.jar';
            Minify_YUICompressor::$tempDir = realpath(sys_get_temp_dir());
            
            try {
                Varien_Profiler::start('Minify_YUICompressor::minifyCss');
                $optimized = Minify_YUICompressor::minifyCss($unoptimized);
                Varien_Profiler::stop('Minify_YUICompressor::minifyCss');
                $YUICompressorFailed = false;
            } catch(Exception $e) {
                Mage::log(Minify_YUICompressor::$yuiCommand);
                Mage::logException($e);
                $YUICompressorFailed = true;
            }
        }

        if (!$this->isYUICompressEnabled() || $YUICompressorFailed === true) {
            Varien_Profiler::start('Minify_CSS::process');
            // Get remove important comments option
            if ($this->getRemoveCommentsFlag() == 1) {
                $optimized = Minify_CSS::minify($unoptimized, array('preserveComments' => false));
            } else {
                $optimized = Minify_CSS::minify($unoptimized);
            }
            Varien_Profiler::stop('Minify_CSS::process');
        }
        
        return $optimized;
    }

    /**
     * Returns array of paths that will be scaned for css and js files.
     * 
     * @return array
     */
    public function minifyJs($unoptimized) 
    {
        if(!$this->isEnabled()) {
            return $unoptimized;
        }
        
        $YUICompressorFailed = false;
        if ($this->isYUICompressEnabled()) {
            Minify_YUICompressor::$jarFile = Mage::getBaseDir().DS.'lib'.DS.'yuicompressor'.DS.'yuicompressor.jar';
            Minify_YUICompressor::$tempDir = realpath(sys_get_temp_dir());
        
            try {
                Varien_Profiler::start('Minify_YUICompressor::minifyJs');
                $optimized = Minify_YUICompressor::minifyJs($unoptimized);
                Varien_Profiler::stop('Minify_YUICompressor::minifyJs');
                $YUICompressorFailed = false;
            } catch(Exception $e) {
                Mage::log(Minify_YUICompressor::$yuiCommand);
                Mage::logException($e);
                $YUICompressorFailed = true;
            }
        }

        if (!$this->isYUICompressEnabled() || $YUICompressorFailed === true) {
            Varien_Profiler::start('JSMin::minify');
            // Get remove important comments option
            if ($this->getRemoveCommentsFlag()  == 1) {
                $optimized = JSMinMax::minify($unoptimized);
            } else {
                $optimized = JSMin::minify($unoptimized);
            }
            Varien_Profiler::stop('JSMin::minify');
        }
        
        return $optimized;
    }
    
    /**
     * @param string $data
     * @param string $target
     *
     * @return string
     */
    public function minifyJsCss($data, $target)
    {
        switch (pathinfo($target, PATHINFO_EXTENSION)) {
            case 'js':
                $data = $this->minifyJs($data);
                break;
            case 'css':
                $data = $this->minifyCss($data);
                break;
            default:
                return false;
        }

        return $data;
    }
}
