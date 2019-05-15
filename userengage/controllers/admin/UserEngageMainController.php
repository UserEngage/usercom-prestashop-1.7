<?php

/**
 * File from https://prestashow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *  @authors     PrestaShow.pl <kontakt@prestashow.pl>
 *  @copyright   2018 PrestaShow.pl
 *  @license     https://prestashow.pl/license
 */
require_once dirname(__FILE__) . "/../../config.php";

class UserEngageMainController extends PShowAdminController
{

    public $default_action = 'index';
    public $select_menu_tab = 'subtab-UserEngageMain';

    public function __construct()
    {
        parent::__construct();

        $this->controller_displayName = $this->l('UserEngage Management');
    }

    public function indexAction()
    {
        $this->action_displayName = $this->l('Module Settings');

        if (Tools::isSubmit('submitSave')) {
            Configuration::updateValue('userengage_apikey', trim(pSQL(Tools::getValue('apiKey'))));
            Configuration::updateValue('userengage_server', trim(pSQL(Tools::getValue('server'))));
            Configuration::updateValue('userengage_debug', trim(pSQL(Tools::getValue('debug'))));

            $this->alerts[] = array('success', $this->l('Settings updated'));
        }

        $this->context->smarty->assign('apiKey', Configuration::get('userengage_apikey'));
        $this->context->smarty->assign('server', Configuration::get('userengage_server'));
        $this->context->smarty->assign('debug', Configuration::get('userengage_debug'));
    }
}
