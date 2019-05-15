<?php
/**
 * File from http://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @authors     PrestaShow.pl <kontakt@prestashow.pl>
 * @copyright   2019 PrestaShow.pl
 * @license     https://prestashow.pl/license
 *
 *    Emergency update.
 *
 *    Use only when you are not able to log into the admin panel.
 */
session_start();
define('_PSHOW_MODULE_EMERGENCY_UPDATE_', true);
error_reporting(E_ALL);
ini_set('display_errors', 'On');
@unlink(dirname(__FILE__) .'/../../module_class_index.php');
ini_set('default_socket_timeout',3);class PShowUpdateNew{public static $modulename;public static $customercode;public static $prestaversion;public static $tmppath;public static $reportbugurl;public static $updateurl;public static $modulepath;private static $instance=array();private $filepath;public static function getInstance($_filepath){$filepath=self::findRealFilePathFromBacktrace($_filepath);$md5=md5($filepath);if(!array_key_exists($md5,self::$instance)){self::$instance[$md5]=new self($filepath);}return self::$instance[$md5];}public static function findRealFilePathFromBacktrace($filepath){$backtrace=debug_backtrace();foreach($backtrace as $call){if(array_key_exists('file',$call)&&stripos($call['file'],'/vendor/system/')===false&&stripos($call['file'],'/modules/')!==false){return $call['file'];}}return $filepath;}public static function getRecommendedProduct(){if(!defined('PS_ADMIN_DIR')){return array('name'=>'PrestaShow.pl','description'=>'','image'=>'','url'=>'https://prestashow.pl');}if(_PS_CACHE_ENABLED_&&Cache::getInstance()->exists('pshow_recommended')){return Cache::getInstance()->get('pshow_recommended');}$context=stream_context_create(array('http'=>array('method'=>'GET','timeout'=>1)));$products=@unserialize(file_get_contents('https://modules.prestashow.pl/recommended_products/1',false,$context));if(!$products){$products=array(array('name'=>'PrestaShow.pl','description'=>'','image'=>'','url'=>'https://prestashow.pl'));}if(_PS_CACHE_ENABLED_){Cache::getInstance()->set('pshow_recommended',reset($products),60);}return reset($products);}private function __construct($filepath){$this->filepath=$filepath;}public function getModuleName(){$module_path=$this->getModulePath();$module_path_arr=explode(DIRECTORY_SEPARATOR,$module_path);return $module_path_arr[count($module_path_arr)- 2];}public function getModulePath(){$filepath=$this->filepath;if(Tools::getValue('controller')&&stripos(Tools::getValue('controller'),'PShow')){$controller=strtolower(Tools::getValue('controller'));$bestpath=false;for($i=0;$i<=strlen($controller);++$i){$tmp=_PS_MODULE_DIR_.substr($controller,0,$i);$bestpath=is_dir($tmp)?$tmp:$bestpath;}return $bestpath.'/';}if(Tools::substr($filepath,-1,1)==DIRECTORY_SEPARATOR){$filepath=Tools::substr($filepath,0,Tools::strlen($filepath)- 1);}$explode=explode(DIRECTORY_SEPARATOR,dirname($filepath));$stay=array_search('modules',$explode)+ 1;if(!array_key_exists($stay,$explode)){return $filepath.DIRECTORY_SEPARATOR;}$newpath_=array();for($i=0;$i<=$stay;++$i){$newpath_[]=$explode[$i];}$newpath=implode(DIRECTORY_SEPARATOR,$newpath_);return $newpath.DIRECTORY_SEPARATOR;}public function getCustomerCode(){static $customercode;if($customercode!==null){return $customercode;}$customercode='no-license';$customercode_file=$this->getModulePath($this->filepath)."license";if(file_exists($customercode_file)&&!file_exists($customercode_file.".php")){$key=file_get_contents($customercode_file);$key=preg_replace('/\s+/','',$key);file_put_contents($customercode_file.".php","<?php return '".$key."';");}if(file_exists($customercode_file.".php")){$customercode_=require($customercode_file.".php");if(is_string($customercode_)){$customercode=$customercode_;}if(file_exists($customercode_file)){unlink($customercode_file);}}return $customercode;}public function getModuleDisplayName(){$module_file=Tools::file_get_contents($this->getModulePath().$this->getModuleName().'.php');$matches=array();preg_match_all('~class ([a-zA-Z0-9]+) extends~',$module_file,$matches);$_matches=end($matches);return end($_matches);}public function getModuleVersionNumber(){$path=$this->getModulePath().$this->getModuleName().'.php';if(stripos($path,'modules')===false||!file_exists($path)){return $path;}$module=Module::getInstanceByName($this->getModuleName());if(!is_object($module)){return '0.0.0';}$module_version_arr=explode('.',$module->version);if(file_exists($this->getModulePath().'vendor/system/version')){$skeleton_version=(int)Tools::file_get_contents($this->getModulePath().'vendor/system/version');}else{$skeleton_version=0;}return((int)$module_version_arr[0]).'.'.((int)$module_version_arr[1]).'.'.((int)$skeleton_version +(int)$module_version_arr[2]);}public function getNewestVersionNumber(){static $newestVersionNumber;if($newestVersionNumber!==null){return $newestVersionNumber;}if(!defined('PS_ADMIN_DIR')&&!defined('_PSHOW_MODULE_EMERGENCY_UPDATE_')){return 'notInAdmin';}$controller=Tools::getValue('controller');if(!defined('_PSHOW_MODULE_EMERGENCY_UPDATE_')&&(!$controller||stripos($controller,'Update')===false)){return 'notInUpdatePage';}$url='https://modules.prestashow.pl';$url.='/'.$this->getPrestashopVersion();$url.='/'.$this->getModuleName();$url.='/'.$this->getModuleVersionNumber();$url.='/'.$this->getCustomerCode();$url.='/'.gethostbyname(gethostname());$url.='/'.$_SERVER["HTTP_HOST"];if(function_exists('curl_init')){$ch=curl_init();curl_setopt($ch,CURLOPT_URL,$url);curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);curl_setopt($ch,CURLOPT_VERBOSE,true);curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);$newestVersionNumber=curl_exec($ch);$httpCode=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($httpCode==200){return $newestVersionNumber;}}$headers=get_headers($url);if(!strpos($headers[0],'200')){return 'Unable to check. Try again later...';}$context=stream_context_create(array('http'=>array('method'=>'GET','timeout'=>3)));$newestVersionNumber=@file_get_contents($url,false,$context);if(!$newestVersionNumber||$newestVersionNumber=='0.0.0'){return 'Unable to check. Try again later...';}return $newestVersionNumber;}public function getPrestashopVersion(){return _PS_VERSION_;}public function compareModuleAndNewestVersion(){return version_compare($this->getModuleVersionNumber(),$this->getNewestVersionNumber(),'>=');}public function downloadUpdate(){$url='https://modules.prestashow.pl';$url.='/download';$url.='/'.$this->getPrestashopVersion();$url.='/'.$this->getModuleName();$url.='/'.$this->getModuleVersionNumber();$url.='/'.$this->getCustomerCode();$url.='/'.gethostbyname(gethostname());$url.='/http://'.$_SERVER["HTTP_HOST"].__PS_BASE_URI__;$tmppath=$this->getTmpPath();if(!is_dir($tmppath)){@mkdir($tmppath);if(!is_dir($tmppath)){throw new \PShowUpdateException('Missing directory '.$tmppath);}}if(!is_writable($this->getTmpPath())){throw new \PShowUpdateException('Missing write permissions to '.$tmppath);}if(!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/',$this->getNewestVersionNumber())){throw new \PShowUpdateException($this->getNewestVersionNumber());}if(function_exists('curl_init')){set_time_limit(0);$fp=fopen($tmppath.$this->getNewestVersionNumber().'.zip','w+');$ch=curl_init();curl_setopt($ch,CURLOPT_URL,$url);curl_setopt($ch,CURLOPT_TIMEOUT,50);curl_setopt($ch,CURLOPT_FILE,$fp);curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);curl_exec($ch);curl_close($ch);fclose($fp);}else{$package=Tools::file_get_contents($url);file_put_contents($tmppath.$this->getNewestVersionNumber().'.zip',$package);}return(file_exists($tmppath.$this->getNewestVersionNumber().'.zip'));}public function getTmpPath(){return $this->getModulePath().'update/tmp/';}public function extractUpdate(){$version=$this->getNewestVersionNumber();if(!file_exists($this->getTmpPath().$version.'.zip')){throw new \PShowUpdateException('File not exists '.$this->getTmpPath().$version.'.zip');}$zip=new ZipArchive();$res=$zip->open($this->getTmpPath().$version.'.zip');if($res===true){$this->recurseRemoveDir($this->getModulePath().'classes/',false);$this->recurseRemoveDir($this->getModulePath().'override/',false);$zip->extractTo($this->getTmpPath());$zip->close();@unlink($this->getModulePath().'module_class_index.php');return true;}else{throw new \PShowUpdateException('Error "'.(string)$this->zipArchiveTranslateErrorCode($res).'"'.' while trying to open '.$this->getTmpPath().$version.'.zip');}}public function zipArchiveTranslateErrorCode($code){switch($code){case 0:return 'No error';case 1:return 'Multi-disk zip archives not supported';case 2:return 'Renaming temporary file failed';case 3:return 'Closing zip archive failed';case 4:return 'Seek error';case 5:return 'Read error';case 6:return 'Write error';case 7:return 'CRC error';case 8:return 'Containing zip archive was closed';case 9:return 'No such file';case 10:return 'File already exists';case 11:return 'Can\'t open file';case 12:return 'Failure to create temporary file';case 13:return 'Zlib error';case 14:return 'Malloc failure';case 15:return 'Entry has been changed';case 16:return 'Compression method not supported';case 17:return 'Premature EOF';case 18:return 'Invalid argument';case 19:return 'Not a zip archive';case 20:return 'Internal error';case 21:return 'Zip archive inconsistent';case 22:return 'Can\'t remove file';case 23:return 'Entry has been deleted';default:return 'An unknown error has occurred('.intval($code).')';}}public function recurseCopy($src,$dst){$dir=opendir($src);@mkdir($dst);while(false!==($file=readdir($dir))){if(($file!='.')&&($file!='..')&&($file!='license')){if(is_dir($src.'/'.$file)){$this->recurseCopy($src.'/'.$file,$dst.'/'.$file);}else{copy($src.'/'.$file,$dst.'/'.$file);}}}closedir($dir);return true;}public function clearTmpDir(){$this->recurseRemoveDir($this->getTmpPath());return true;}public function moveUpdateToModule(){$from=$this->getTmpPath().$this->getModuleName()."/";$to=$this->getModulePath();$this->recurseCopy($from,$to);@array_map("unlink",glob($this->getModulePath().'/config*.xml'));return true;}public function recurseRemoveDir($_path,$removePath=false){$path=str_replace('//','/',$_path);if(!is_dir($_path)){return false;}if(Tools::substr($path,-1,1)==DIRECTORY_SEPARATOR){$path=Tools::substr($path,0,Tools::strlen($path)- 1);}if(stripos($path.'/',$this->getModulePath())!==0){return false;}$files=glob($path.'/*');foreach($files as $file){if(is_dir($file)){$this->recurseRemoveDir($file,true);continue;}unlink($file);}if($removePath===true){rmdir($path);}}public function makeModuleBackup(){$path=$this->getModulePath()."update/backups/";$destPath=$path.$this->getModuleName()."_".$this->getModuleVersionNumber()."_".date('H.i_d.m.Y').".zip";$zip=new ZipArchive();if(!is_dir($path)){@mkdir($path);if(!is_dir($path)){throw new \PShowUpdateException('Missing directory '.$path);}}if(!$zip->open($destPath,ZipArchive::CREATE | ZipArchive::OVERWRITE)){throw new \PShowUpdateException('Failed to init ZIP archive in '.$destPath);}$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getModulePath()),RecursiveIteratorIterator::LEAVES_ONLY);try{foreach($files as $name=>$file){if(!$file->isDir()){$filePath=$file->getRealPath();$relativePath=Tools::substr($filePath,Tools::strlen($this->getModulePath()));if(preg_match('~/update/~',$filePath)){continue;}if(preg_match('~\/.git\/~',$filePath)){continue;}if(preg_match('~\/license~',$filePath)){continue;}if(preg_match('~\/log\/~',$filePath)){continue;}if(preg_match('~\/'.$this->getModuleName().'_~',$filePath)){continue;}$zip->addFile($filePath,$relativePath);}}}catch(UnexpectedValueException $e){}$zip->close();return(file_exists($destPath));}public function moveBackupToTmp($file){if(!file_exists($this->getModulePath()."update/backups/".$file)){throw new \PShowUpdateException('File not exists '.$this->getModulePath()."update/backups/".$file);}copy($this->getModulePath()."update/backups/".$file,$this->getTmpPath().$file);if(!file_exists($this->getTmpPath().$file)){throw new \PShowUpdateException('Failed to copy file '.$this->getModulePath()."update/backups/".$file);}return true;}public function extractBackup($file){if(!file_exists($this->getTmpPath().$file)){throw new \PShowUpdateException('File not exists '.$this->getTmpPath().$file);}$zip=new ZipArchive();$res=$zip->open($this->getTmpPath().$file);if(!file_exists($this->getTmpPath().'backup/')){mkdir($this->getTmpPath().'backup/',0777);}if($res===TRUE){$zip->extractTo($this->getTmpPath().'backup/');$zip->close();return true;}else{throw new \PShowUpdateException('Failed to open '.$this->getTmpPath().$file);}}public function moveBackupToModule(){$from=$this->getTmpPath().'backup/';$to=$this->getModulePath();$this->recurseCopy($from,$to);return true;}public function execUpdate($from_ver){$ver_expl=explode('.',$from_ver);$from_ver=$ver_expl[0].'.'.$ver_expl[1];$modulename=$this->getModuleName();require_once $this->getModulePath().$modulename.'.php';$instance=new $modulename();if(!isset($instance->moduleVersionPath)){$instance->moduleVersionPath=array();}foreach($instance->moduleVersionPath as $ver){$funcName=str_replace('.','_',$from_ver);if(version_compare($instance->version,$from_ver.'.0')==1&&method_exists($instance,$funcName)){$instance->funcName();}}return false;}public function formatVersionToDisplay($version){return preg_replace('/([0-9]+).([0-9]+).([0-9]+)/i','$1.$2.$3',$version);}}
?>
    <hr>
    <p><strong>This script will update your module.</strong>
        <small>Use this only for emergency updates.</small>
    </p>
    <hr> <?php if (file_exists('./emergency_self_update.php')): ?>
    <small>You can update this script using this: <a
                href="<?php echo str_replace('_update', '_self_update', $_SERVER['PHP_SELF']); ?>">click to self
            update</a></small>
    <hr> <?php endif; ?>
    <form method="post" action=""><p><label for="path">Enter absolute path to any file in the module which you want
                to update:</label><br> <input type="text" name="path" id="path" value="<?php echo __FILE__; ?>"
                                              size="100"></p>
        <p><label for="email">Enter employee email:</label><br> <input type="email" name="email" id="email" size="100">
        </p>
        <p><label for="password">Enter employee password:</label><br> <input type="password" name="password"
                                                                             id="password" size="100"></p>
        <p><input type="checkbox" name="createBackup" id="createBackup" checked="checked">
            <label for="createBackup">Create backup</label><br> </p>
        <p><input type="submit" value="START UPDATE"></p></form>
<?php
define('WAITING_TIME', 15 * 60);
define('MAX_TRIES', 10);
if (isset($_SESSION['loginTries']) && $_SESSION['loginTries'] > MAX_TRIES) {
    if ($_SESSION['loginLastTryTime'] > (time() - WAITING_TIME)) {
        die('<u>You have reached the limit login attempts. Wait 15 minutes and try again...</u>');
    } else {
        $_SESSION['loginTries'] = 0;
    }
}
require_once "../../../../config/config.inc.php";
if (!Tools::getValue('password') || !Tools::getValue('password')) {
    die();
}
error_reporting(E_ALL);
ini_set('display_errors', 'On');

function employeeNotFound()
{
    if (!isset($_SESSION['loginTries'])) {
        $_SESSION['loginTries'] = 0;
    }
    ++$_SESSION['loginTries'];
    $_SESSION['loginLastTryTime'] = time();
    die('<u>Employee not found or wrong password!</u>');
}

$email = pSQL(Tools::getValue('email'));
if (version_compare(_PS_VERSION_, '1.7') >= 0) {
    $sql = new DbQuery();
    $sql->select('e.*');
    $sql->from('employee', 'e');
    $sql->where('e.`email` = \'' . $email . '\'');
    $sql->where('e.`active` = 1');
    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    if (!$result) {
        employeeNotFound();
    }
    $crypto = \PrestaShop\PrestaShop\Adapter\ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
    if (!$crypto->checkHash(Tools::getValue('password'), $result['passwd'])) {
        employeeNotFound();
    }
} else {
    $password = Tools::encrypt(Tools::getValue('password'));
    $q = "SELECT COUNT(*) " . "FROM " . _DB_PREFIX_ . "employee " . "WHERE `email` = '" . $email . "' AND `passwd` = '" . $password . "'; ";
    $foundEmployee = Db::getInstance()->getValue($q);
    if (!$foundEmployee) {
        employeeNotFound();
    }
}
$path = Tools::getValue('path');
if (empty($path)) {
    die('<u>Module not found in path: ' . $path . '</u>');
}
$moduleName = PShowUpdateNew::getInstance($path)->getModuleName();
echo '<p>Updating module `' . $moduleName . '`...</p>';
$from_ver = PShowUpdateNew::getInstance($path)->getModuleVersionNumber();
if (Tools::getValue('createBackup')) {
    if (!PShowUpdateNew::getInstance($path)->makeModuleBackup()) {
        echo '<p>Module backup error!</p>';
    } else {
        echo '<p>Module backup completed</p>';
    }
}
PShowUpdateNew::getInstance($path)->clearTmpDir();
if (!PShowUpdateNew::getInstance($path)->downloadUpdate()) {
    echo '<p>Download error!</p>';
    die();
} else {
    echo '<p>Update downloaded</p>';
}
if (!PShowUpdateNew::getInstance($path)->extractUpdate()) {
    echo '<p>Update package extract error!</p>';
    die();
} else {
    echo '<p>Update package extracted</p>';
}
if (!PShowUpdateNew::getInstance($path)->moveUpdateToModule()) {
    echo '<p>Module update error!';
    die();
} else {
    echo '<p>Module updated</p>';
}
if (PShowUpdateNew::getInstance($path)->execUpdate($from_ver)) {
    echo '<p>Module update script executed</p>';
}
PShowUpdateNew::getInstance($path)->clearTmpDir();
echo '<p>Cleaned after update</p>';
echo '<p style="color: green;font-weight: bold;">All done!</p>';
