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
class PShowHookController extends PShowAdminController
{

    public $default_action = 'index';
    public $filepath = null;

    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        $this->filepath = $reflection->getFileName();

        require_once dirname($this->filepath) . "/../../config.php";

        parent::__construct();

        $this->id_shop = (int) $this->context->shop->id;
        $this->id_lang = (int) $this->context->cookie->id_lang;

        $this->controller_displayName = $this->l('Hooks');
    }

    public function indexAction()
    {
        $this->action_displayName = $this->l('List');

        $hooks = PShowHook::getHooks();
        $hooksCount = PShowHook::countHooks();

        $this->context->smarty->assign('hooks', $hooks);
        $this->context->smarty->assign('hooksCount', $hooksCount);
    }

    public function removeAction()
    {
        $this->action_displayName = $this->l('List');

        $id_hook = Tools::getValue('id_hook');

        if (!$id_hook) {
            $this->action = 'index';
            $this->indexAction();
            return;
        }

        $hook = new PShowHook($id_hook);

        if (!is_object($hook)) {
            $this->action = 'index';
            $this->indexAction();
            return;
        }

        PShowHook::unregisterHook('skeleton', $hook->hook_name, $this->id_shop);
        $hook->delete();

        $this->alerts[] = array('success', $this->l('Hook removed'));

        $this->action = 'index';
        $this->indexAction();
        return;
    }

    public function editAction()
    {
        $this->action_displayName = $this->l('Edit hook');

        $id_hook = Tools::getValue('id_hook');

        if (!$id_hook) {
            $this->action = 'index';
            $this->indexAction();
            return;
        }

        $h = new PShowHook($id_hook);

        if (!is_object($h)) {
            $this->action = 'index';
            $this->indexAction();
            return;
        }

        if (Tools::isSubmit('submit')) {
            $hook_name = pSQL(Tools::getValue('hook_name'));

            if (!empty($hook_name)) {
                try {
                    PShowHook::unregisterHook('skeleton', $h->hook_name, $this->id_shop);
                    $presta_id_hook = PShowHook::registerHook('skeleton', $hook_name, $this->id_shop);

                    $h->hook_name = $hook_name;
                    $h->presta_id_hook = $presta_id_hook;
                    $h->update();

                    $this->alerts[] = array('success', $this->l('Hook updated'));
                    $this->action = 'index';
                    $this->indexAction();
                    return;
                } catch (PrestaShopException $e) {
                    $this->alerts[] = array('warning', $this->l('Hook update error, try again'));
                }
            } else {
                $this->alerts[] = array('warning', $this->l('Hook update error, try again'));
            }
        }

        $hooks = Hook::getHooks();

        $this->context->smarty->assign('h', $h);
        $this->context->smarty->assign('hooks', $hooks);
    }

    public function addAction()
    {
        $this->action_displayName = $this->l('Add hook');

        $hooks = PShowHook::getAvailableHooks('skeleton', $this->id_shop, false);

        if (Tools::isSubmit('submit')) {
            $hook_name = pSQL(Tools::getValue('hook_name'));

            if (!empty($hook_name)) {
                $hook = new PShowHook();

                $hook->hook_name = $hook_name;

                try {
                    $presta_id_hook = PShowHook::registerHook('skeleton', $hook_name, $this->id_shop);

                    $hook->presta_id_hook = (int) $presta_id_hook;
                    $hook->add();

                    $this->alerts[] = array('success', $this->l('Hook added'));
                    $this->action = 'index';
                    $this->indexAction();
                    return;
                } catch (PrestaShopException $e) {
                    $this->alerts[] = array('warning', $this->l('Hook add error, try again'));
                }
            } else {
                $this->alerts[] = array('warning', $this->l('Hook add error, try again'));
            }
        }

        $this->context->smarty->assign('hooks', $hooks);
    }
}
