<?php
/**
 * File from http://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @authors     PrestaShow.pl <kontakt@prestashow.pl>
 * @copyright   2018 PrestaShow.pl
 * @license     https://prestashow.pl/license
 */
ini_set('default_socket_timeout', 3);

/**
 * @deprecated since version 58, use PShowUpdateNew instead
 */
class PShowUpdate
{

    /**
     * For backward compatibility !
     * Get singleton instance
     *
     * @param string $filepath
     * @return \PShowUpdateNew
     */
    public static function getInstance($filepath)
    {
        return PShowUpdateNew::getInstance($filepath);
    }

    public static $modulename;
    public static $customercode;
    public static $prestaversion;
    public static $tmppath;
    public static $reportbugurl = 'http://modules.prestashow.pl/bug_report.php';
    public static $updateurl = 'http://modules.prestashow.pl';
    public static $modulepath = null;
    public static $newestVersionNumber = null;
    public static $hostname = null;
    public static $urlHeaders = array();

    public static function getBugReportUrl()
    {
        $context = Context::getContext();
        $employee = new Employee($context->cookie->id_employee);

        $url = self::$reportbugurl;
        $url .= "?module=" . getModuleName(__FILE__);
        $url .= "&email=" . $context->cookie->email;
        $url .= "&moduleversion=" . self::getModuleVersionNumber();
        $url .= "&prestaversion=" . self::$prestaversion;
        $url .= "&customercode=" . self::$customercode;
        $url .= "&shopname=" . Configuration::get('PS_SHOP_NAME');
        $url .= "&adminname=" . $employee->firstname . " " . $employee->lastname;
        $url .= "&domain=" . $context->shop->domain . $context->shop->physical_uri;
        $url .= "&moduledisplayname=" . self::getModuleDisplayName();

        return $url;
    }

    public static function getCustomerCode()
    {
        return self::getInstance(__FILE__)->getCustomerCode();
    }

    public static function getModuleDisplayName()
    {
        return self::getInstance(__FILE__)->getModuleDisplayName();
    }

    public static function setModuleName($name)
    {
        self::$modulename = $name;
    }

    public static function getModuleName()
    {
        return self::getInstance(__FILE__)->getModuleName();
    }

    public static function setModulePath($path)
    {
        self::$modulepath = $path;
        self::$tmppath = self::$modulepath . '/update/tmp/';
    }

    public static function getModulePath()
    {
        return self::getInstance(__FILE__)->getModulePath();
    }

    public static function getModuleVersionNumber($version = false, $modulename = false, $modulepath = false)
    {
        return self::getInstance(__FILE__)->getModuleVersionNumber();
    }

    public static function getRecommendedProduct()
    {
        return self::getInstance(__FILE__)->getRecommendedProduct();
    }

    public static function gethostname()
    {
        if (self::$hostname === null)
            self::$hostname = gethostname();

        return self::$hostname;
    }

    public static function get_headers($url)
    {
        $urlmd5 = md5($url);

        if (!array_key_exists($urlmd5, self::$urlHeaders))
            self::$urlHeaders[$urlmd5] = @get_headers($url);

        return self::$urlHeaders[$urlmd5];
    }

    public static function getNewestVersionNumber($modulename = false)
    {
        return self::getInstance(__FILE__)->getNewestVersionNumber();
    }

    public static function compareModuleAndNewestVersion($modulename = false, $modulepath = false)
    {
        return self::getInstance(__FILE__)->compareModuleAndNewestVersion();
    }

    public static function downloadUpdate()
    {
        return self::getInstance(__FILE__)->downloadUpdate();
    }

    public static function extractUpdate($version = false)
    {
        return self::getInstance(__FILE__)->extractUpdate();
    }

    public static function recurseCopy($src, $dst)
    {
        return self::getInstance(__FILE__)->recurseCopy($src, $dst);
    }

    public static function clearTmpDir()
    {
        return self::getInstance(__FILE__)->clearTmpDir();
    }

    public static function moveUpdateToModule()
    {
        return self::getInstance(__FILE__)->moveUpdateToModule();
    }

    public static function recurseRemoveDir($path, $removePath = false)
    {
        return self::getInstance(__FILE__)->recurseRemoveDir($path, $removePath);
    }

    public static function makeModuleBackup()
    {
        return self::getInstance(__FILE__)->makeModuleBackup();
    }

    public static function moveBackupToTmp($file)
    {
        return self::getInstance(__FILE__)->moveBackupToTmp($file);
    }

    public static function extractBackup($file)
    {
        return self::getInstance(__FILE__)->extractBackup($file);
    }

    public static function moveBackupToModule()
    {
        return self::getInstance(__FILE__)->moveBackupToModule();
    }

    public static function execUpdate($from_ver)
    {
        return self::getInstance(__FILE__)->execUpdate($from_ver);
    }

    public static function formatVersionToDisplay($version)
    {
        return self::getInstance(__FILE__)->formatVersionToDisplay($version);
    }
}
