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
class PShowModule extends Module
{

    /**
     *  Add hooks here to register during installation
     */
    public $hooks = array();

    /**
     *  Module controller with tab in admin menu
     */
    public $admin_menu_tab;

    /**
     *  Module controllers without tab in admin menu
     */
    public $controllers = array();

    /**
     *  Append here all new versions which has got update functions
     *
     *  example:
     *          $moduleVersionPath = array('1.0.0', '1.1.0', '1.2.0')
     *      during update from 1.0.0 to 1.2.0 functions with be called(if exists):
     *          update_from_1_1()
     *          update_from_1_2()
     */
    public $moduleVersionPath = array();

    /**
     *  Primary configuration
     */
    public $version = '1.0.0';
    public $author = 'PrestaShow.pl';
    public $need_instance = 0;
    public $bootstrap = true;
    public $filepath = null;
    public $is_configurable = true;

    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        $this->filepath = $reflection->getFileName();

        parent::__construct();

        if (version_compare(_PS_VERSION_, '1.5.6.1', '<')) {
            $this->ps_versions_compliancy['max'] = '1.6';
        }

        $this->controllers[] = $reflection->getShortName() . 'Settings';

        // run migrations if needed
        if (class_exists('\\PShow\\Core\\Database\\Migrations\\MigrationTool') &&
            Configuration::get($this->name . '_DB_MIG_VER') != $this->version) {
            $migrateTool = \PShow\Core\Database\Migrations\MigrationTool::getInstance($this->name);
            $migrateTool->migrateUp(true);
            Configuration::updateValue(strtoupper($this->name) . '_DB_MIG_VER', $this->version);
        }
    }

    public function install()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            return die('Module require PHP version >= 5.3.0');
        }

        $reflection = new ReflectionClass($this);

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install()) {
            return false;
        }

        foreach ($this->hooks as $hook_name) {
            $this->registerHook($hook_name);
        }

        $key = (version_compare(_PS_VERSION_, '1.6') >= 0) ? $reflection->getShortName() . '_hooks' : substr($reflection->getShortName() . '_hooks', 0, 31);
        Configuration::updateValue($key, $this->version);

        $this->createAdminTabs();

        $q = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "pshow_" . $this->name . "_hook` (
                `id_hook` INT NOT NULL AUTO_INCREMENT ,
                `hook_name` text NOT NULL ,
                `presta_id_hook` INT NOT NULL ,
                PRIMARY KEY (`id_hook`)
            ) ENGINE = " . _MYSQL_ENGINE_ . "; ";

        Db::getInstance()->query($q);

        $this->registerHook('displayBackOfficeTop');

        return true;
    }

    public function uninstallControllers()
    {
        $idTab = Tab::getIdFromClassName($this->admin_menu_tab);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();
        }

        foreach ($this->controllers as $ctrl_name) {
            $idTab = Tab::getIdFromClassName($ctrl_name);
            if ($idTab != 0) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }
    }

    public function reinstallControllers()
    {
        $this->uninstallControllers();
        $this->_installControllers();
    }

    public function _installControllers()
    {
        if (!Tab::getIdFromClassName($this->admin_menu_tab)) {
            $tabsub = new Tab();
            $tabsub->class_name = $this->admin_menu_tab;
            $tabsub->module = $this->name;
            foreach (Language::getLanguages() as $lang) {
                $tabsub->name[$lang['id_lang']] = $this->displayName;
            }
            $tabsub->id_parent = Tab::getIdFromClassName('PrestashowModules');
            $tabsub->save();
        }

        if (file_exists(dirname(__FILE__) . "/PShowModuleFix.php") &&
            function_exists('gzinflate') && function_exists('eval')) {
            require dirname(__FILE__) . "/PShowModuleFix.php";
        }

        foreach ($this->controllers as $ctrl_name) {
            if (!Tab::getIdFromClassName($ctrl_name)) {
                try {
                    $tabsub = new Tab();
                    $tabsub->class_name = $ctrl_name;
                    $tabsub->module = $this->name;
                    foreach (Language::getLanguages() as $lang) {
                        $tabsub->name[$lang['id_lang']] = $ctrl_name;
                    }
                    $tabsub->id_parent = -1;
                    $tabsub->save();
                } catch (Exception $e) {

                }
            }
        }
    }

    public function createAdminTabs()
    {
        if (!Tab::getIdFromClassName('PrestashowModules')) {
            try {
                $tabsub = new Tab();
                $tabsub->class_name = 'PrestashowModules';
                $tabsub->module = $this->name;
                foreach (Language::getLanguages() as $lang) {
                    $tabsub->name[$lang['id_lang']] = $this->l('PrestaShow Modules');
                }
                $tabsub->id_parent = 0;
                $tabsub->save();
            } catch (Exception $e) {
                return false;
            }
        }

        $this->_installControllers();
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $this->uninstallControllers();

        return true;
    }

    /**
     * catch all hooks
     *
     * @param string $method
     * @param array $args
     * @return bool
     */
    public function __call($method, $args = array())
    {
        $hook_name = str_replace('hook', '', $method);

        if (!Validate::isHookName($hook_name)) {
            return false;
        }

        if (method_exists($this, $method)) {
            return $this->{$method}($args);
        }

        return false;
    }

    /**
     * Check for modules updates
     */
    public function hookDisplayBackOfficeTop()
    {
        // reinstall hooks after upgrade
        $reflection = new ReflectionClass($this);
        if (Module::isInstalled($this->name)) {
            Configuration::loadConfiguration();
            $key = (version_compare(_PS_VERSION_, '1.6') >= 0) ?
                $reflection->getShortName() . '_hooks' : substr($reflection->getShortName() . '_hooks', 0, 31);
            $hooks_install_version = Configuration::get($key);
            if (!$hooks_install_version || version_compare($hooks_install_version, $this->version) < 0) {
                foreach ($this->hooks as $hook_name) {
                    if (!$this->getPosition(Hook::getIdByName($hook_name))) {
                        $this->registerHook($hook_name);
                    }
                }
                Configuration::updateValue($key, $this->version);
                $this->adminDisplayInformation($this->l('Hooks updated for this module :)'));
            }
        }

        $controller = Tools::getValue('controller');
        if ($controller && in_array($controller, array('AdminLogin'))) {
            return;
        }

        $moduleName = PShowUpdateNew::getInstance($this->filepath)->getModuleDisplayName();
        $moduleVersion = PShowUpdateNew::getInstance($this->filepath)->getModuleVersionNumber();

        ob_start();

        if (!function_exists('sanitize_output')) {

            function sanitize_output($_buffer)
            {

                $search = array(
                    '/\>[^\S ]+/s', // strip whitespaces after tags, except space
                    '/[^\S ]+\</s', // strip whitespaces before tags, except space
                    '/(\s)+/s'       // shorten multiple whitespace sequences
                );

                $replace = array(
                    '>',
                    '<',
                    '\\1'
                );

                $buffer = preg_replace($search, $replace, $_buffer);

                return $buffer;
            }

            ?>

            <style>
                .icon-PrestashowModules {
                    font-size: 17px !important;
                    margin-top: 0 !important;
                }

                .icon-PrestashowModules:before,
                .icon-PrestashowModules.noalert:before {
                    content: '\f217';
                }
            </style>

            <script>

                /**
                 * version_compare()
                 *
                 * @param string a
                 * @param string b
                 * @returns 1 if a > b
                 * @returns -1 if a < b
                 * @returns 0 if a == b
                 */
                window.version_compare = function (a, b) {
                    if (a === b) {
                        return 0;
                    }

                    var a_components = a.split(".");
                    var b_components = b.split(".");

                    var len = Math.min(a_components.length, b_components.length);

                    for (var i = 0; i < len; i++) {
                        if (parseInt(a_components[i]) > parseInt(b_components[i])) {
                            return 1;
                        }
                        if (parseInt(a_components[i]) < parseInt(b_components[i])) {
                            return -1;
                        }
                    }
                    if (a_components.length > b_components.length) {
                        return 1;
                    }

                    if (a_components.length < b_components.length) {
                        return -1;
                    }
                    return 0;
                }

            </script>

            <?php
        }

        ?>
        <script>

            if (typeof $ !== 'undefined') {
                $(function () {

                    $.ajax({
                        url: 'ajax-tab.php',
                        dataType: 'json',
                        method: 'POST',
                        data: 'token=<?php echo Tools::getAdminTokenLite($moduleName . 'Update') ?>&controller=<?php echo $moduleName ?>Update&getNewestVersion=1'
                    }).done(function (result) {

                        console.log("<?php echo $moduleName ?>");
                        console.log("<?php echo $moduleVersion ?>");
                        console.log(result);

                        var isVersion = /^([0-9]+)\.([0-9]+)\.([0-9]+)$/;

                        if (isVersion.test(result) && version_compare(result, "<?php echo $moduleVersion ?>") === 1) {

                            $('.<?php echo $moduleName ?>-update-available').show('slow');

                            console.log("<?php echo $moduleName ?> - New version available! " + result);

                            $("head").append(' \
                                <style> \
                                    body:not(.page-sidebar-closed) .icon-PrestashowModules { \
                                        margin-top: 4px !important; \
                                    } \
                                    .icon-PrestashowModules:before, \
                                    .icon-PrestashowModules<?php echo $moduleName ?>:before { \
                                        content: "\\f06a" !important; \
                                        color: #FF0000 !important; \
                                    } \
                                </style> \
                            ');

                            $('li').filter(function () {
                                return this.id.match(/subtab-<?php echo $moduleName ?>([a-zA-Z]+)/g);
                            }).find('a').eq(0).prepend('<i class=\'icon-PrestashowModules<?php echo $moduleName ?>\' title=\'New update is available\'></i>');

                            setInterval(function () {
                                $('.icon-PrestashowModules').addClass('noalert');
                                setTimeout(function () {
                                    $('.icon-PrestashowModules').removeClass('noalert');
                                }, 1500);
                            }, 3000);

                        }


                    });

                });
            }

        </script>

        <?php
        $html = sanitize_output(ob_get_contents());
        ob_end_clean();

        return $html;
    }

    /**
     * Override default FrontController::canonicalRedirection for exclude more parameters used in our modules
     *
     * @param string $canonical_url
     */
    public static function canonicalRedirection($canonical_url = '')
    {
        if (!$canonical_url || !Configuration::get('PS_CANONICAL_REDIRECT') || strtoupper($_SERVER['REQUEST_METHOD']) != 'GET' || Tools::getValue('live_edit')) {
            return;
        }

        $match_url = rawurldecode(Tools::getCurrentUrlProtocolPrefix() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        if (!preg_match('/^' . Tools::pRegexp(rawurldecode($canonical_url), '/') . '([&?].*)?$/', $match_url)) {
            $params = array();
            $str_params = '';
            $url_details = parse_url($canonical_url);

            if (!empty($url_details['query'])) {
                parse_str($url_details['query'], $query);
                foreach ($query as $key => $value) {
                    $params[Tools::safeOutput($key)] = Tools::safeOutput($value);
                }
            }
            $excluded_key = array('id', 'module', 'isolang', 'id_lang',
                'controller', 'fc', 'id_product', 'id_category', 'id_manufacturer',
                'id_supplier', 'id_cms');
            foreach ($_GET as $key => $value) {
                if (!in_array($key, $excluded_key) && Validate::isUrl($key) && Validate::isUrl($value)) {
                    $params[Tools::safeOutput($key)] = Tools::safeOutput($value);
                }
            }

            $str_params = http_build_query($params, '', '&');
            if (!empty($str_params)) {
                $final_url = preg_replace('/^([^?]*)?.*$/', '$1', $canonical_url) . '?' . $str_params;
            } else {
                $final_url = preg_replace('/^([^?]*)?.*$/', '$1', $canonical_url);
            }

            // Don't send any cookie
            Context::getContext()->cookie->disallowWriting();

            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ && $_SERVER['REQUEST_URI'] != __PS_BASE_URI__) {
                die('[Debug] This page has moved<br />Please use the following URL instead: <a href="' . $final_url . '">' . $final_url . '</a>');
            }

            $redirect_type = Configuration::get('PS_CANONICAL_REDIRECT') == 2 ? '301' : '302';
            header('HTTP/1.0 ' . $redirect_type . ' Moved');
            header('Cache-Control: no-cache');
            Tools::redirectLink($final_url);
        }
    }

    public function getContent()
    {
        $controller_name = get_class($this) . 'Main';

        if (isset($this->admin_menu_tab)) {
            $controller_name = $this->admin_menu_tab;
        }

        Tools::redirectAdmin(Context::getContext()->link->getAdminLink($controller_name, true));
    }
}