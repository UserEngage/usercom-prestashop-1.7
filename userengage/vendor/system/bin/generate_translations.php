<?php
/*
 * File from https://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *  @authors     PrestaShow.pl <kontakt@prestashow.pl>
 *  @copyright   2019 PrestaShow.pl
 *  @license     https://prestashow.pl/license
 *
 * Script generates file with translations from whole module.
 * 
 * Run it via browser:
 *  .../generate_translations.php?target=de
 * Run it via terminal:
 *  php generate_translations.php de
 */

if (isset($argv[1]))
    define('_TARGET_LANG_', $argv[1]);
elseif (isset($_GET['target']))
    define('_TARGET_LANG_', $_GET['target']);
else
    define('_TARGET_LANG_', 'pl');

define('_USE_GOOGLE_TRANSLATE_', true);

/**
 * Get module path
 *
 * @param string $filepath
 * @return string
 */
function getModulePath($filepath)
{
    if (substr($filepath, -1, 1) == DIRECTORY_SEPARATOR) {
        $filepath = Tools::substr($filepath, 0, Tools::strlen($filepath) - 1);
    }

    $explode = explode(DIRECTORY_SEPARATOR, dirname($filepath));

    $stay = array_search('modules', $explode) + 1;

    if (!array_key_exists($stay, $explode)) {
        return $filepath . DIRECTORY_SEPARATOR;
    }

    $newpath = array();

    for ($i = 0; $i <= $stay; ++$i)
        $newpath[] = $explode[$i];

    $newpath = implode(DIRECTORY_SEPARATOR, $newpath);

    return $newpath . DIRECTORY_SEPARATOR;
}

/**
 * Get module name
 *
 * @param string $filepath
 * @return string
 */
function getModuleName($filepath)
{
    $module_path = getModulePath($filepath);
    $module_path_arr = explode(DIRECTORY_SEPARATOR, $module_path);

    return $module_path_arr[count($module_path_arr) - 2];
}

/**
 * FIle tranlations in .tpl files
 *
 * @param string $filepath
 * @param string $modulename
 * @return array
 */
function findTranslationsInTplFile($filepath, $modulename = _PSHOW_MODULE_NAME_)
{
    $content = file_get_contents($filepath);

    preg_match_all('~\{l s=\'(.*?)\' mod=\'(' . $modulename . ')\'\}~', $content, $matches);

    if (isset($matches[1]) && count($matches[1]) > 0)
        return $matches[1];

    return array();
}

/**
 * File tranlations in .php files
 *
 * @param string $filepath
 * @return array
 */
function findTranslationsInPhpFile($filepath)
{
    $content = file_get_contents($filepath);

    preg_match_all('~\$this-\>l\(\'(.*?)\'\)~', $content, $matches);

    if (isset($matches[1]) && count($matches[1]) > 0)
        return $matches[1];

    return array();
}

/**
 * Get microtime
 *
 * @return float
 */
function getmicrotime()
{
    return array_sum(explode(' ', microtime()));
}

$translator_timer = 0;

/**
 * Translate string using Google Translate
 *
 * @global integer $translator_timer
 * @param string $text
 * @return string
 */
function __($text)
{
    if (_USE_GOOGLE_TRANSLATE_ === false)
        return $text;

    global $translator_timer;

    $t = getmicrotime();

    $url = 'https://translate.google.com/?sl=en&tl=' . _TARGET_LANG_ . '&prev=_t&hl=it&ie=UTF-8&eotf=1&text=' . urlencode($text);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    $html = curl_exec($ch);
    curl_close($ch);

    $matches = array();

    preg_match_all('~TRANSLATED_TEXT=\'(.*?)\'~', $html, $matches);

    unset($html);

    $translator_timer += getmicrotime() - $t;

    if (!isset($matches[1]) || count($matches[1]) == 0)
        return $text;

    $translation = reset($matches[1]);
    $translation = mb_convert_encoding($translation, 'utf-8');

    return $translation;
}

define('_PSHOW_MODULE_NAME_', getModuleName(__FILE__));
define('_PSHOW_MODULE_PATH_', getModulePath(__FILE__));
$views_path = _PSHOW_MODULE_PATH_ . "views/templates/";
$translations_path = _PSHOW_MODULE_PATH_ . "translations/";

$timer = getmicrotime();

$files1 = glob($views_path . "*/*.tpl");
$files2 = glob($views_path . "*.tpl");
$files = array_merge($files1, $files2);

$translations = array();

if (file_exists($translations_path . _TARGET_LANG_ . '.php')) {
    $_MODULE = array();
    require_once $translations_path . _TARGET_LANG_ . '.php';
    $translations = $_MODULE;
}

foreach ($files as $file) {
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $translationsInFile = findTranslationsInTplFile($file);

    $translation_key = "<{" . _PSHOW_MODULE_NAME_ . "}prestashop>" . strtolower($filename) . "_";

    foreach ($translationsInFile as $t) {
        $key = $translation_key . md5($t);
        if (!array_key_exists($key, $translations)) {
            $translations[$key] = array($t, __($t));
        }

        if (!is_array($translations[$key])) {
            $__ = $translations[$key];
            $translations[$key] = array($t, $__);
        }
    }
}

//$files4_ = glob(_PSHOW_MODULE_PATH_ . "system/view/*/*.tpl");
//$files4__ = glob(_PSHOW_MODULE_PATH_ . "system/view/*.tpl");
//$files4 = array_merge($files4_, $files4__);
//
//foreach ($files4 as $file)
//{
//    $filename = pathinfo($file, PATHINFO_FILENAME);
//    $translationsInFile = findTranslationsInTplFile($file, 'skeleton');
//
//    $translation_key = "<{skeleton}prestashop>" . strtolower($filename) . "_";
//
//    foreach ($translationsInFile as $t)
//    {
//        $key = $translation_key . md5($t);
//        if (!array_key_exists($key, $translations))
//        {
//            $translations[$key] = array($t, __($t));
//        }
//
//        if (!is_array($translations[$key]))
//        {
//            $__ = $translations[$key];
//            $translations[$key] = array($t, $__);
//        }
//    }
//}

$files3 = glob(_PSHOW_MODULE_PATH_ . "controllers/*/*.php");

foreach ($files3 as $file) {
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $translationsInFile = findTranslationsInPhpFile($file);

    $translation_key = "<{" . _PSHOW_MODULE_NAME_ . "}prestashop>" . strtolower($filename) . "_";

    foreach ($translationsInFile as $t) {
        $key = $translation_key . md5($t);
        if (!array_key_exists($key, $translations)) {
            $translations[$key] = array($t, __($t));
        }

        if (!is_array($translations[$key])) {
            $__ = $translations[$key];
            $translations[$key] = array($t, $__);
        }
    }
}

$timer = getmicrotime() - $timer;

/**
 * Start generating new translation file
 */
$newFileContent = "<?php\n\n"
    . "// Generation date: \t\t" . date('Y-m-d H:i:s') . "\n"
    . "// Generation time: \t\t" . round($timer - $translator_timer, 5) . "s\n"
    . "// Translating time: \t\t" . round($translator_timer, 5) . "s\n"
    . "// Scanned files count: \t" . count($files + $files3) . "\n"
    . "// Translations count: \t\t" . count($translations) . "\n\n"
    . "/**\n"
    . "* File from http://PrestaShow.pl\n"
    . "*\n"
    . "* DISCLAIMER\n"
    . "* Do not edit or add to this file if you wish to upgrade this module to newer\n"
    . "* versions in the future.\n"
    . "*\n"
    . "*  @authors     PrestaShow.pl <kontakt@prestashow.pl>\n"
    . "*  @copyright   2018 PrestaShow.pl\n"
    . "*  @license     https://prestashow.pl/license\n"
    . "*/\n\n"
    . "require_once dirname(__FILE__) . '/../vendor/system/translations/pl.php';\n"
    . "global \$_MODULE;\n\n";

// generate translation variables
foreach ($translations as $key => $value) {
    if (is_array($value))
        $newFileContent .= "//en value: " . $value[0] . "\n\$_MODULE['$key'] = '" . str_replace('\'', '\\\'', $value[1]) . "';\n\n";
    else
        $newFileContent .= "\$_MODULE['$key'] = '" . str_replace('\'', '\\\'', $value) . "';\n\n";
}

$newFileContent .= "return \$_MODULE;";

// put translations to file
file_put_contents($translations_path . _TARGET_LANG_ . '.php', $newFileContent);

unset($newFileContent);

echo "\nDONE!\n"
    . "Generation time: " . round($timer - $translator_timer, 5) . "s\n"
    . "Translating time: " . round($translator_timer, 5) . "s\n"
    . "Translations count: " . count($translations) . "\n\n";
