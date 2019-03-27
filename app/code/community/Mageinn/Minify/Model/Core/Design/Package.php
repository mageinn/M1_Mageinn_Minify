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
class Mageinn_Minify_Model_Core_Design_Package extends Mage_Core_Model_Design_Package
{
    /**
     * Get the timestamp of the newest file
     * (inspired by http://smith-web.net/2013/02/09/magento-css-auto-versioning)
     *
     * @param array $srcFiles
     * @param string $type
     * @return int $timeStamp
     */
    protected function _getVersion($srcFiles, $type)
    {
        $vType = Mage::helper('mageinn_minify')->getVersionType();
        
        switch ($vType) {
            case 0:
                // Disabled
                return '';
            case 1:
                // Auto
                $timeStamp = null;
                foreach ($srcFiles as $file) {
                    if(is_null($timeStamp)) {
                        //if is first file, set $timeStamp to filemtime of file
                        $timeStamp = filemtime($file);
                    } else {
                        //get max of current files filemtime and the max so far
                        $timeStamp = max($timeStamp, filemtime($file));
                    }
                }
                return $timeStamp;
            case 2:
                // Manual
                return Mage::helper('mageinn_minify')->getVersion($type);
            default:
                return '';
        }
    }

    /**
     * Merge specified javascript files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedJsUrl($files)
    {
        if(!Mage::helper('mageinn_minify')->getVersionType()) {
            return parent::getMergedJsUrl($files);
        }
        
        $targetFilename = md5(implode(',', $files)) . ('-' . $this->_getVersion($files, 'js')) . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
        }
        return '';
    }


    /**
     * Merge specified css files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        if(!Mage::helper('mageinn_minify')->getVersionType()) {
            return parent::getMergedCssUrl($files);
        }
        
        // secure or unsecure
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        // merge into target file
        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . ('-' . $this->_getVersion($files, 'css')) . '.css';
        $mergeFilesResult = $this->_mergeFiles(
            $files, $targetDir . DS . $targetFilename,
            false,
            array($this, 'beforeMergeCss'),
            'css'
        );
        if ($mergeFilesResult) {
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
        }
        return '';
    }
    
    /**
     * Make sure merger dir exists and writeable
     * Also can clean it up
     *
     * @param string $dirRelativeName
     * @param bool $cleanup
     * @return bool
     */
    protected function _initMergerDir($dirRelativeName, $cleanup = false)
    {
        $mediaDir = Mage::getBaseDir('media');
        try {
            $dir = Mage::getBaseDir('media') . DS . $dirRelativeName;
            if ($cleanup) {
                Varien_Io_File::rmdirRecursive($dir);
                
                $helper = Mage::helper('core/file_storage_database');
                $helper->deleteFolder($dir);
                
                // Fix for CSS, JS, SECURE_CSS folders
                if ($helper->checkDbUsage()) {
                    $res = Mage::getSingleton('core/resource');
                    $write = $res->getConnection('core_write');
                    $write->delete($res->getTableName('core/file_storage'), new Zend_Db_Expr('directory IN (\'css\',\'js\',\'css_secure\')'));
                }
            }
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            return is_writeable($dir) ? $dir : false;
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return false;
    }
}
