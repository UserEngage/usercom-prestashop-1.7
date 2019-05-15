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
class PShowAdminController extends ModuleAdminController
{

    public $bootstrap = true;
    public $alerts = array();
    public $filepath = null;
    public $template_isset = false;
    public $newSystemLoc = "";

    /**
     * Tips
     *
     * @var array
     */
    public $tips = array();

    /**
     *
     * @param warning|success|info|danger $type
     * @param string $id Uniq id
     * @param string $message
     */
    public function addTip($type, $id, $message)
    {
        $this->tips[] = array(
            'type' => $type,
            'id' => $id,
            'message' => $message
        );
    }

    public function __construct()
    {
        @ini_set('display_errors', 'on');
        @error_reporting(E_ERROR | E_PARSE | E_STRICT);

        if (version_compare(_PS_VERSION_, '1.7.0') >= 0) {
            $this->translator = Context::getContext()->getTranslator();
        }

        $reflection = new ReflectionClass($this);
        $this->filepath = $reflection->getFileName();

        if (is_dir(PShowUpdateNew::getInstance($this->filepath)->getModulePath() . "vendor/system")) {
            $this->newSystemLoc = "vendor/";
        }

        $this->addTip(
            'info', 'tips_information', $this->l('The module provides instructions on how to use the module.'
            . 'You can close each tip. At any time you can restore all the '
            . 'instructions by going to the module settings and activating '
            . 'the `Show hints` option.')
        );

        // create required directories
        $modulePath = getModulePath(__FILE__);
        $req = array(
            $modulePath . 'update',
            $modulePath . 'update/backups',
            $modulePath . 'update/tmp',
        );
        foreach ($req as $path) {
            if (!is_dir($path)) {
                mkdir($path);
                chmod($path, 0777);
            }
        }

        parent::__construct();

        if (Tools::getValue('controller') != 'PShowImporterImport') {
            PShowUpdateNew::getInstance($this->filepath)->getNewestVersionNumber();
        }

        $this->modulepath = PShowUpdateNew::getInstance($this->filepath)->getModulePath();
        $this->modulename = PShowUpdateNew::getInstance($this->filepath)->getModuleName();
        $this->module = new $this->modulename();

        $this->context->smarty->assign(
            'prestashow_logo_url', Tools::getShopDomain() . __PS_BASE_URI__ . 'modules/'
            . $this->modulename . '/' . $this->newSystemLoc . 'system/view/img/prestashow-logo.jpg'
        );

        $displayModuleVersion = (Tools::getValue('controller') == 'PShowImporterImport') ? 'ajax' :
            PShowUpdateNew::getInstance($this->filepath)->formatVersionToDisplay(
                PShowUpdateNew::getInstance($this->filepath)->getModuleVersionNumber()
            );

        $this->context->smarty->assign('moduleVersion', $displayModuleVersion);

        $this->context->smarty->assign('module', $this->module);

        $isUpdateAvailable = (Tools::getValue('controller') == 'PShowImporterImport') ? false :
            (!PShowUpdateNew::getInstance($this->filepath)->compareModuleAndNewestVersion());
        $this->context->smarty->assign('isUpdateAvailable', $isUpdateAvailable);

        $settings = \PShow_Settings::getInstance(__FILE__)->getAll();
        $this->mod_settings = array();

        foreach ($settings as $s) {
            $this->mod_settings[$s['name']] = \PShow_Settings::getInstance(__FILE__)->get($s['name']);
        }

        $this->context->smarty->assign('mod_settings', $this->mod_settings);

        $this->context->smarty->assign('module_content_container', 'panel');
    }

    public function createTemplate($tpl_name)
    {
        if (file_exists($this->getTemplatePath() . $tpl_name) && $this->viewAccess()) {
            return $this->context->smarty->createTemplate($this->getTemplatePath() . $tpl_name, $this->context->smarty);
        }

        return parent::createTemplate($tpl_name);
    }

    public function getTemplatePath()
    {
        return PShowUpdateNew::getInstance($this->filepath)->getModulePath() . 'views/templates/admin/';
    }

    public function initContent()
    {
        parent::initContent();

        if (!$this->display) {
            $this->display = $this->default_action;
        }

        // Temporary bypass to helpers
        if (method_exists($this, $this->display . 'HelperAction') === false) {
            $this->action = Tools::getValue('page');

            if (!$this->action || !method_exists($this, $this->action . 'Action')) {
                $this->action = $this->default_action;
            }

            $this->{$this->action . 'Action'}();
        }

        if (!Module::isEnabled(getModuleName(__FILE__))) {
            $this->alerts[] = array('danger', $this->l('This module is disabled and may work incorrect!'));
        }

        if (file_exists(dirname(__FILE__) . "/PShowModuleFix.php") &&
            function_exists('gzinflate') && function_exists('eval')) {
            require dirname(__FILE__) . "/PShowModuleFix.php";
        }

        if (!$this->template_isset) {
            $this->setTemplate(str_repeat('../', 4) . 'modules/' . $this->modulename . '/' . $this->newSystemLoc . "system/view/admin_controller.tpl");
        }

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            // PS 1.5 is not compatibile with bootstrap
            $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $this->modulename . '/' . $this->newSystemLoc . 'system/view/css/backward-compatibility.css');
        }

        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->modulename . '/' . $this->newSystemLoc . 'system/view/js/select_tab.js');

        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->modulename . '/' . $this->newSystemLoc . 'system/view/js/tips.js');

        $this->context->smarty->assign('moduleurl', __PS_BASE_URI__ . 'modules/' . $this->modulename . '/');

        $this->context->smarty->assign('TOKEN', Tools::getValue('token'));

        $mainClassContent = Tools::file_get_contents($this->modulepath . $this->modulename . '.php');
        preg_match_all('~class ([a-zA-Z]+) ~', $mainClassContent, $matches);
        $arrayWithClass = end($matches);
        $classname = end($arrayWithClass);

        $this->context->smarty->assign('PSHOW_MODULE_CLASS_NAME_', $classname);

        $recommended = PShowUpdateNew::getInstance($this->filepath)->getRecommendedProduct();
        $this->context->smarty->assign('recommended', $recommended);

        $this->context->smarty->assign('action', $this->action);
        $this->context->smarty->assign('alerts', $this->alerts);
        $this->context->smarty->assign('tips', $this->tips);
        $this->context->smarty->assign('action_displayName', $this->action_displayName);
        $this->context->smarty->assign('controller_displayName', $this->controller_displayName);
        $this->context->smarty->assign('__FILE__', __FILE__);

        $this->context->smarty->assign('select_menu_tab', $this->select_menu_tab);

        $modulename_low = Tools::strtolower($this->modulename);
        $classname_low = Tools::strtolower(get_class($this));
        $controllername = str_replace(array("controller", $modulename_low), "", $classname_low);
        $this->context->smarty->assign('controllername', $controllername);
    }
}