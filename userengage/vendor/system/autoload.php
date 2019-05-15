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
if (!function_exists('getModuleName')) {

    function IsModulesInPath($dirpath)
    {
        $explode = explode(DIRECTORY_SEPARATOR, $dirpath);
        return in_array('modules', $explode);
    }

    function getModulePath($filepath_)
    {
        $filepath = findRealFilePath($filepath_);

        if (Tools::getValue('controller') && stripos(Tools::getValue('controller'), 'PShow')) {
            $controller = strtolower(Tools::getValue('controller'));

            $bestpath = false;

            for ($i = 0; $i <= strlen($controller); ++$i) {
                $tmp = _PS_MODULE_DIR_ . substr($controller, 0, $i);
                $bestpath = is_dir($tmp) ? $tmp : $bestpath;
            }

            return $bestpath . '/';
        }

        if (Tools::substr($filepath, -1, 1) == DIRECTORY_SEPARATOR) {
            $filepath = Tools::substr($filepath, 0, Tools::strlen($filepath) - 1);
        }

        $explode = explode(DIRECTORY_SEPARATOR, dirname($filepath));

        $stay = array_search('modules', $explode) + 1;

        if (!array_key_exists($stay, $explode)) {
            return $filepath . DIRECTORY_SEPARATOR;
        }

        $newpath_ = array();

        for ($i = 0; $i <= $stay; ++$i) {
            $newpath_[] = $explode[$i];
        }

        $newpath = implode(DIRECTORY_SEPARATOR, $newpath_);

        return $newpath . DIRECTORY_SEPARATOR;
    }

    /**
     *
     * @param string $filepath
     * @return string
     */
    function findRealFilePath($filepath)
    {
        $backtrace = debug_backtrace();

        foreach ($backtrace as $call) {
            if (array_key_exists('file', $call) && stripos($call['file'], '/system/') === false && stripos($call['file'], '/modules/') !== false) {
                return $call['file'];
            }
        }

        // this should not happen !
        return $filepath;
    }

    function getModuleName($filepath_)
    {
        $filepath = findRealFilePath($filepath_);

        $module_path = getModulePath($filepath);
        $module_path_arr = explode(DIRECTORY_SEPARATOR, $module_path);

        return $module_path_arr[count($module_path_arr) - 2];
    }
}

$composer_autoload = dirname(dirname(__FILE__)) . '/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
}

// fix for Windows Server
$presta_path = str_replace(array('/', '//'), DIRECTORY_SEPARATOR, _PS_MODULE_DIR_);

// get module main class name
$moduleMainClassName = getModuleName(__FILE__);
$h = fopen(_PS_MODULE_DIR_ . getModuleName(__FILE__) . "/" . getModuleName(__FILE__) . '.php', "r");
if ($h) {
    while (!feof($h) && ($line = fgets($h)) !== false) {
        if (preg_match('/class ([a-zA-Z\_]+) extends PShowModule/', $line, $matches) && count($matches)) {
            $moduleMainClassName = end($matches);
            break;
        }
    }
    fclose($h);
}

// remove old class index
$old_module_classes_index_path = getModulePath(__FILE__) . 'module_class_index';
if (file_exists($old_module_classes_index_path)) {
    @unlink($old_module_classes_index_path);
}

/**
 * Generate index of the module classes
 */
$module_classes_index_path = getModulePath(__FILE__) . 'module_class_index.php';
if (true || !file_exists($module_classes_index_path)) {

    $module_classes = array();

    $getClassesFromDir = function ($dir_name, $path, $getClassesFromDir) use ($presta_path, &$module_classes) {
        $glob = glob($path . '/*', GLOB_MARK);
        foreach ($glob as $path) {
            if (is_dir($path)) {
                $getClassesFromDir($dir_name, $path, $getClassesFromDir);
                continue;
            }

            if (pathinfo($path, PATHINFO_EXTENSION) != 'php') {
                continue;
            }

            $path = "modules/" . str_replace(array('//', $presta_path), array("/", ""), $path);
            $_classname = substr($path, (stripos($path, '/' . $dir_name . '/') + strlen('/' . $dir_name . '/')));
            $classname = str_replace(array('//', '/', '.php'), array('/', '\\', null), $_classname);

            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $module_classes[$classname] = array(
                    "path" => $path,
                    "type" => "class",
                    "override" => false
                );
            } else {
                $module_classes[$classname] = $path;
            }
        }
    };

    $getClassesFromDir('classes', getModulePath(__FILE__) . "vendor/system/classes", $getClassesFromDir);

//    $glob_classes = glob(dirname(getModulePath(__FILE__)) . "/*/vendor/system/classes/*.php");
//    usort($glob_classes, function ($a, $b) {
//        return filemtime($b) - filemtime($a);
//    });
//    $added = array();
//
//    foreach ($glob_classes as $file) {
//        $path = "modules/" . str_replace($presta_path, "", $file);
//        $classname = pathinfo($path, PATHINFO_FILENAME);
//
//        if ($classname == 'AdminController') {
//            continue;
//        }
//
//        if (in_array($classname, $added)) {
//            continue;
//        }
//
//        array_push($added, $classname);
//
//        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
//            $module_classes[$classname] = array(
//                "path" => $path,
//                "type" => "class",
//                "override" => false
//            );
//        } else {
//            $module_classes[$classname] = $path;
//        }
//    }

    $glob_classes = glob(getModulePath(__FILE__) . "controllers/*.php");

    foreach ($glob_classes as $file) {
        $path = "modules/" . str_replace($presta_path, "", $file);
        $classname = pathinfo($path, PATHINFO_FILENAME);

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $module_classes[$classname] = array(
                "path" => $path,
                "type" => "class",
                "override" => false
            );
        } else {
            $module_classes[$classname] = $path;
        }
    }

    $getClassesFromDir('classes', getModulePath(__FILE__) . "classes", $getClassesFromDir);

    $moduleMainClassPath = "modules/" . getModuleName(__FILE__) . "/" . getModuleName(__FILE__) . '.php';

    if (version_compare(_PS_VERSION_, '1.6', '>=')) {
        $module_classes[$moduleMainClassName] = array(
            "path" => $moduleMainClassPath,
            "type" => "class",
            "override" => false
        );
    } else {
        $module_classes[$moduleMainClassName] = $moduleMainClassPath;
    }

    file_put_contents($module_classes_index_path, "<?php \nreturn " . var_export($module_classes, true) . ";");
}

/**
 * Merge module class index with prestashop autoloader
 */
$module_classes = require $module_classes_index_path;
unset($module_classes["index"]);
Autoload::getInstance()->index = array_merge(
    Autoload::getInstance()->index, $module_classes
);

/**
 * Get module main file
 */
if (!class_exists($moduleMainClassName)) {
    $path = getModulePath(__FILE__) . getModuleName(__FILE__) . ".php";
    if (file_exists($path)) {
        require_once $path;
    }
}

if (!class_exists('PShow_Settings')) {

    class PShow_Settings extends PShowSettingsAbstract
    {

        public static $settings = array();

    }

}

if (!function_exists('getDiskFreeSpace')) {

    function getDiskFreeSpace()
    {
        if (!function_exists('exec')) {
            echo 'Not supported';
            return;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo 'MS Windows not supported';
            return;
        }

        $sizes = 0;
        @exec("df -g / 2> /dev/null | awk 'FNR == 2 {print $4}'", $sizes);
        if (!$sizes || !count($sizes)) {
            echo 'Check unavailable';
            return;
        }
        $size = reset($sizes);

        echo $size . '/';

        $sizes = 0;
        @exec("df -g / 2> /dev/null | awk 'FNR == 2 {print $2}'", $sizes);
        if (!count($sizes)) {
            echo 'Check unavailable';
            return;
        }
        $size = reset($sizes);

        echo $size;
    }
}

if (!function_exists('showTip') && function_exists('smartyRegisterFunction')) {

    function showTip($params)
    {
        if (!array_key_exists('id', $params)) {
            $params['id'] = uniqid();
        }

        if (!array_key_exists('type', $params)) {
            $params['type'] = 'info';
        }

        if (!array_key_exists('message', $params)) {
            $params['message'] = 'Enter message...';
        }

        if (!PShow_Settings::getInstance(__FILE__)->get('tip_' . $params['id'])) {
            echo '<div class="alert alert-' . $params['type'] . ' fade in tip" id="' . $params['id'] . '">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <p>' . $params['message'] . '</p></div>';
        }
    }

    smartyRegisterFunction(Context::getContext()->smarty, 'function', 'showTip', 'showTip');
}

if (!function_exists('addJsDef') && function_exists('smartyRegisterFunction') &&
    !isset(Context::getContext()->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['addJsDef'])) {

    function addJsDef($js_def)
    {
        Media::addJsDef($js_def);
    }

    smartyRegisterFunction(Context::getContext()->smarty, 'function', 'addJsDef', 'addJsDef');
}