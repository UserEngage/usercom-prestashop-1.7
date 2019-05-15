<?php
/**
 * File from https://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *  @authors     PrestaShow.pl <kontakt@prestashow.pl>
 *  @copyright   2019 PrestaShow.pl
 *  @license     https://prestashow.pl/license
 */
abstract class PShowSettingsAbstract
{

    /** @var array */
    public static $global_settings = array(
        array(
            'type' => 'switch',
            'name' => 'fold_menu_on_enter',
            'label' => 'Fold menu after entering the module',
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => 'Enabled'
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => 'Disabled'
                )
            )
        ),
        array(
            'type' => 'switch',
            'name' => 'tips',
            'label' => 'Show tips',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => 'Enabled'
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => 'Disabled'
                )
            ),
            'is_bool' => true
        ),
    );

    /** @var array of \PShow_Settings */
    protected static $instance = null;

    /** @var string */
    public static $filepath;

    public function __construct()
    {

    }

    /**
     *
     * @param string $filepath
     * @return \PShow_Settings
     */
    public static function getInstance($filepath)
    {
        self::$filepath = getModulePath($filepath);
        if (self::$instance === null) {
            self::$instance = new \PShow_Settings();
            $x = &self::$instance;
            if ($filepath !== null && file_exists(getModulePath($filepath) . 'settings.php')) {
                $x::$settings = array_merge(self::$global_settings, include(getModulePath($filepath) . 'settings.php'));
            }
            self::modyfiFormPS15($x::$settings);
        }
        return self::$instance;
    }

    /**
     * Change 'switch' elements to 'select' to support PS1.5
     *
     * @param \PShow_Settings $instance
     */
    public static function modyfiFormPS15(&$instance)
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '>=')) {
            return;
        }

        foreach ($instance as $key1 => $element) {
            $toChange = false;
            foreach ($element as $key2 => $item) {
                if ($item == 'switch' && $key2 == 'type') {
                    $toChange = true;
                    $instance[$key1][$key2] = 'select';
                }
                if ($toChange) {
                    if ($key2 == 'is_bool') {
                        unset($instance[$key1][$key2]);
                    }
                    if ($key2 == 'values') {
                        unset($instance[$key1][$key2]);

                        $instance[$key1]['options'] = array(
                            'query' => array(
                                array(
                                    'value' => 1,
                                    'label' => 'Enabled'
                                ),
                                array(
                                    'value' => 0,
                                    'label' => 'Disabled'
                                )
                            ),
                            'id' => 'value',
                            'name' => 'label'
                        );
                    }
                }
            }
        }
    }

    /**
     *
     * @return array
     */
    public function getAll()
    {
        return static::$settings;
    }

    /**
     * Get setting value
     *
     * @param string $name
     * @return string|null
     */
    public function get($name)
    {
        $confName = strtolower(getModuleName(self::$filepath)) . '_' . $name;

        // support PS1.5 - max length of key is 33 chars
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $confName = substr($confName, 0, 32);
        }

        return Configuration::get($confName);
    }

    /**
     * Set setting value
     *
     * @param string $name
     * @param string $value
     * @return string|null
     */
    public function set($name, $value)
    {
        $confName = strtolower(getModuleName(self::$filepath)) . '_' . $name;

        // support PS1.5 - max length of key is 33 chars
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $confName = substr($confName, 0, 32);
        }

        Configuration::updateValue($confName, $value);
    }
}
