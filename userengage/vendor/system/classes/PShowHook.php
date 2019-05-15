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
class PShowHook extends ObjectModel
{

    public $id;

    /** @var int Hook */
    public $id_hook;

    /** @var string Name */
    public $hook_name;

    /** @var int */
    public $presta_id_hook;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => null,
        'primary' => 'id_hook',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'hook_name' => array('type' => self::TYPE_STRING, 'required' => true),
            'presta_id_hook' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt',
                'required' => true),
        ),
    );

    public function __construct($id_hook = null, $id_lang = null, $id_shop = null)
    {
        self::initDefinition();
        $reflection = new ReflectionClass($this);
        $this->filepath = $reflection->getFileName();
        parent::__construct($id_hook, $id_lang, $id_shop);
    }

    public static function getReflection()
    {
        return new ReflectionClass(get_called_class());
    }

    public static function initDefinition()
    {
        self::$definition['table'] = 'pshow_' . getModuleName(self::getReflection()->getFileName()) . '_hook';
    }

    public static function getHooks()
    {
        self::initDefinition();

        $q = "SELECT * FROM `" . _DB_PREFIX_ . self::$definition['table'] . "` h";

        return Db::getInstance()->executeS($q);
    }

    public static function getHookIdByPrestaHookId($presta_id_hook)
    {
        self::initDefinition();

        $q = "SELECT h.`id_hook` FROM `" . _DB_PREFIX_ . self::$definition['table'] . "` h
			WHERE h.`presta_id_hook` = " . (int) $presta_id_hook;

        return Db::getInstance()->getValue($q);
    }

    public static function getHookIdByPrestaHookName($presta_hook_name)
    {
        $hook_id = Hook::getIdByName($presta_hook_name);

        return self::getHookIdByPrestaHookId($hook_id);
    }

    public static function countHooks()
    {
        self::initDefinition();

        $q = "SELECT COUNT(*) FROM `" . _DB_PREFIX_ . self::$definition['table'] . "` h";

        $query = Db::getInstance()->getValue($q);

        if ($query === false)
            return false;

        return $query;
    }

    public function add($autodate = true, $null_values = false)
    {
        return parent::add($autodate, $null_values);
    }

    public function update($null_values = false)
    {
        return parent::update($null_values);
    }

    public static function getAvailableHooks($module_name, $shop_id, $except_hook_name = false)
    {
        if ($except_hook_name)
            $hook_id = Hook::getIdByName($hook_name);

        $hooks = Hook::getHooks();

        foreach ($hooks as $key => $hook) {
            $hook_name = Hook::getNameById($hook['id_hook']);

            if ($except_hook_name && $hook_id == $hook['id_hook'])
                continue;

            if (!self::isHookRegistered($module_name, $hook_name, $shop_id))
                continue;

            unset($hooks[$key]);
        }

        return $hooks;
    }

    public static function isHookRegistered($module_name, $hook_name, $shop_id)
    {
        $id_module = Module::getModuleIdByName($module_name);
        $hook_id = Hook::getIdByName($hook_name);

        $sql = 'SELECT `id_hook`
			FROM `' . _DB_PREFIX_ . 'hook_module` WHERE
			`id_module` = ' . (int) $id_module . ' AND
			`id_hook` = ' . (int) $hook_id . ' AND
			`id_shop` = ' . (int) $shop_id;
        return Db::getInstance()->getRow($sql);
    }

    public static function unregisterHook($module_name, $hook_name, $shop_id)
    {
        $id_module = Module::getModuleIdByName($module_name);
        $hook_id = Hook::getIdByName($hook_name);

        // Unregister module on hook by id
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'hook_module` WHERE
			`id_module` = ' . (int) $id_module . ' AND
			`id_hook` = ' . (int) $hook_id . ' AND
			`id_shop` = ' . (int) $shop_id;
        $result = Db::getInstance()->execute($sql);

        return $result;
    }

    public static function registerHook($module_name, $hook_name, $shop_id)
    {
        $id_hook = Hook::getIdByName($hook_name);
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            $live_edit = false;
        } else {
            $live_edit = Hook::getLiveEditById((int) $id_hook);
        }

        if (!$id_hook) {
            $new_hook = new Hook();
            $new_hook->name = pSQL($hook_name);
            $new_hook->title = pSQL($hook_name);
            $new_hook->live_edit = false;
            $new_hook->position = (bool) $new_hook->live_edit;
            $new_hook->add();

            $id_hook = $new_hook->id;
        }

        if (!$id_hook)
            throw new PrestaShopException('Hook add error');

        // Get module position in hook
        $sql = 'SELECT MAX(`position`) AS position
			FROM `' . _DB_PREFIX_ . 'hook_module`
			WHERE `id_hook` = ' . (int) $id_hook . ' AND `id_shop` = ' . (int) $shop_id;
        if (!$position = Db::getInstance()->getValue($sql))
            $position = 0;

        $id_module = Module::getModuleIdByName($module_name);

        $q = Db::getInstance()->insert('hook_module', array(
            'id_module' => (int) $id_module,
            'id_hook' => (int) $id_hook,
            'id_shop' => (int) $shop_id,
            'position' => (int) ($position + 1),
        ));

        if (!$q)
            throw new PrestaShopException('Hook add error');

        return $id_hook;
    }
}
