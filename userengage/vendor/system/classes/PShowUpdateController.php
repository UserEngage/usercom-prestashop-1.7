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
class PShowUpdateController extends PShowAdminController
{

    public $default_action = 'index';
    public $filepath = null;

    public function ajaxProcess()
    {
        if (!defined('PS_ADMIN_DIR')) {
            define('PS_ADMIN_DIR', true);
        }
        if (Tools::isSubmit('getNewestVersion')) {
            die(Tools::jsonEncode(PShowUpdate::getInstance($this->filepath)->getNewestVersionNumber()));
        }
    }

    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        $this->filepath = $reflection->getFileName();

        require_once dirname($this->filepath) . "/../../config.php";

        parent::__construct();

        $this->controller_displayName = $this->l('Module update');
        $this->action_displayName = $this->l('Update informations');

        $smarty = Context::getContext()->smarty;

        $displayModuleVersion = PShowUpdate::getInstance($this->filepath)->formatVersionToDisplay(
            PShowUpdate::getInstance($this->filepath)->getModuleVersionNumber()
        );
        $displayNewestVersion = PShowUpdate::getInstance($this->filepath)->formatVersionToDisplay(
            PShowUpdate::getInstance($this->filepath)->getNewestVersionNumber()
        );

        $changelog_url = 'https://git.layersshow.com/Prestashow/changelog/raw/master/' .
            PShowUpdate::getInstance($this->filepath)->getModuleName() . '.md';
        $changelog = file_get_contents($changelog_url);

        $changelog_path = PShowUpdate::getInstance($this->filepath)->getModulePath() . 'changelog.md';

        if (!$changelog) {
            if (file_exists($changelog_path)) {
                $changelog = Tools::file_get_contents($changelog_path);
            } else {
                $changelog = $this->l('Changelog not found :(');
            }
        }

        $smarty->assign('changelog', $changelog);
        $smarty->assign('PShowUpdateInstance', PShowUpdate::getInstance($this->filepath));
        $smarty->assign('ModuleVersionNumber', $displayModuleVersion);
        $smarty->assign('NewestVersionNumber', $displayNewestVersion);
        $smarty->assign('compareModuleAndNewestVersion', PShowUpdate::getInstance($this->filepath)->compareModuleAndNewestVersion());
    }

    public function getCHMOD()
    {
        return Tools::substr(sprintf('%o', fileperms($this->filepath)), -4);
    }

    public function indexAction()
    {
        $this->action_displayName = $this->l('Update informations');

        if ($this->getCHMOD() !== "0755") {
            $this->updateChmod();
            if ($this->getCHMOD() !== "0755") {
                $this->alerts[] = array('danger', 'To update module you must set write permissions for all module files');
                return;
            }
        }
    }

    public function updateChmod($path = null)
    {
        if ($path === null) {
            $path = PShowUpdate::getInstance($this->filepath)->getModulePath();
        }

        $files = glob($path);

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->updateChmod($file . "/*");
            }

            @chmod($file, 0755);
        }
    }

    public function updateAction()
    {
        $this->action = 'index';
        $this->action_displayName = $this->l('Update informations');

        $this->updateChmod();

        try {

            $from_ver = PShowUpdate::getInstance($this->filepath)->getModuleVersionNumber();

            if (PShowUpdate::getInstance($this->filepath)->compareModuleAndNewestVersion() && !Tools::getValue('force')) {
                throw new \PShowUpdateException('Your are running newest version.');
            }

            PShowUpdate::getInstance($this->filepath)->makeModuleBackup();
            PShowUpdate::getInstance($this->filepath)->clearTmpDir();
            PShowUpdate::getInstance($this->filepath)->downloadUpdate();
            PShowUpdate::getInstance($this->filepath)->extractUpdate();
            PShowUpdate::getInstance($this->filepath)->moveUpdateToModule();

            Tools::redirectAdmin('index.php?controller=' . PShowUpdate::getInstance($this->filepath)->getModuleName()
                . 'Update&token=' . $this->token . '&page=execupdate&from_version=' . $from_ver);
        } catch (\PShowUpdateException $e) {
            $this->alerts[] = array('warning', $e->getMessage());
        }
    }

    public function execupdateAction()
    {
        $this->action = 'index';
        $this->action_displayName = $this->l('Update informations');

        try {
            PShowUpdate::getInstance($this->filepath)->execUpdate(Tools::getValue('from_version'));
            PShowUpdate::getInstance($this->filepath)->clearTmpDir();

            $this->alerts[] = array('success', $this->l('Module updated. In case of problems, please reinstall the module.'));
        } catch (\PShowUpdateException $e) {
            $this->alerts[] = array('warning', $e->getMessage());
        }

        $this->indexAction();
    }
}
