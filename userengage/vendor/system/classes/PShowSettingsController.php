<?php

/**
 * File from http://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *  @authors     PrestaShow.pl <kontakt@prestashow.pl>
 *  @copyright   2018 PrestaShow.pl
 *  @license     https://prestashow.pl/license
 */
class PShowSettingsController extends PShowAdminController
{

    public $default_action = 'index';
    public $filepath = null;

    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        $this->filepath = $reflection->getFileName();

        require_once dirname($this->filepath) . "/../../config.php";

        parent::__construct();

        $this->controller_displayName = $this->l('Module settings');
        $this->action_displayName = $this->l('Module settings');
    }

    public function updateAction()
    {
        $key = pSQL(Tools::getValue('key'));
        $value = pSQL(Tools::getValue('val'));
        \PShow_Settings::getInstance($this->filepath)->set($key, $value);
        die('OK');
    }

    public function indexAction()
    {
        $mod_settings = \PShow_Settings::getInstance($this->filepath)->getAll();

        if (Tools::getValue('btnSubmit')) {
            $settings = $_POST;
            unset($settings['btnSubmit']);

            foreach ($settings as $k => $v) {


                \PShow_Settings::getInstance($this->filepath)->set(pSQL($k), pSQL($v));

                if ($k == 'tips' && $v) {
                    $q = "DELETE FROM `%s` WHERE `name` LIKE '%s'; ";
                    Db::getInstance()->execute(
                        sprintf($q, _DB_PREFIX_ . 'configuration', getModuleName($this->filepath) . '_tip_%')
                    );
                }
            }
        }

        $this->context->smarty->assign('mod_settings', $mod_settings);
        $this->context->smarty->assign('form', $this->generateConfigForm($mod_settings));
    }

    public function getConfigFieldsValues()
    {
        $settings = \PShow_Settings::getInstance($this->filepath)->getAll();
        $res = array();

        foreach ($settings as $s) {
            $res[$s['name']] = \PShow_Settings::getInstance($this->filepath)->get($s['name']);
        }

        return $res;
    }

    public function generateConfigForm($mod_settings)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Module settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $mod_settings,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right" '
                    . 'onclick="$(\'#configuration_form\').submit();"',
                    'name' => 'btnSubmit'
                )
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $lang = new Language(
            (int) Configuration::get('PS_LANG_DEFAULT')
        );
        $helper->default_form_language = $lang->id;
        $this->fields_form = array();
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = null;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $helper->currentIndex = $this->context->link->getAdminLink('PShowImporterSettings', false);
            $helper->token = Tools::getAdminTokenLite('PShowImporterSettings');
        }

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }
}
