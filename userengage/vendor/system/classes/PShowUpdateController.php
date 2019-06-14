<?php

/**
 * File from http://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @authors     PrestaShow.pl <kontakt@prestashow.pl>
 * @copyright   2019 PrestaShow.pl
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
            die(json_encode(PShowUpdateNew::getInstance($this->filepath)->getNewestVersionNumber()));
        }
    }

    /**
     * PShowUpdateController constructor.
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        $this->filepath = $reflection->getFileName();

        require_once dirname($this->filepath) . "/../../config.php";

        parent::__construct();

        $this->controller_displayName = $this->trans('Module update');
        $this->action_displayName = $this->trans('Update informations');

        $smarty = Context::getContext()->smarty;

        $displayModuleVersion = PShowUpdateNew::getInstance($this->filepath)->formatVersionToDisplay(
            PShowUpdateNew::getInstance($this->filepath)->getModuleVersionNumber()
        );
        $displayNewestVersion = PShowUpdateNew::getInstance($this->filepath)->formatVersionToDisplay(
            PShowUpdateNew::getInstance($this->filepath)->getNewestVersionNumber()
        );

        $changelog_url = 'https://git.layersshow.com/Prestashow/changelog/raw/master/' .
            PShowUpdateNew::getInstance($this->filepath)->getModuleName() . '.md';
        $changelog = file_get_contents($changelog_url);

        $changelog_path = PShowUpdateNew::getInstance($this->filepath)->getModulePath() . 'changelog.md';

        if (!$changelog) {
            if (file_exists($changelog_path)) {
                $changelog = Tools::file_get_contents($changelog_path);
            } else {
                $changelog = $this->trans('Changelog not found :(');
            }
        }

        $smarty->assign('changelog', $changelog);
        $smarty->assign('PShowUpdateInstance', PShowUpdateNew::getInstance($this->filepath));
        $smarty->assign('ModuleVersionNumber', $displayModuleVersion);
        $smarty->assign('NewestVersionNumber', $displayNewestVersion);
        $smarty->assign(
            'compareModuleAndNewestVersion',
            PShowUpdateNew::getInstance($this->filepath)->compareModuleAndNewestVersion()
        );
    }

    public function indexAction()
    {
        $this->action_displayName = $this->trans('Update informations');

        if (is_writable($this->filepath)) {
            $this->alerts[] = array('danger', 'To update module you must set write permissions for all module files');
            return;
        }
    }

    public function updateAction()
    {
        $this->action = 'index';
        $this->action_displayName = $this->trans('Update informations');

        try {

            $from_ver = PShowUpdateNew::getInstance($this->filepath)->getModuleVersionNumber();

            if (PShowUpdateNew::getInstance($this->filepath)->compareModuleAndNewestVersion() &&
                !Tools::getValue('force')) {
                throw new \PShowUpdateException('Your are running newest version.');
            }

            PShowUpdateNew::getInstance($this->filepath)->makeModuleBackup();
            PShowUpdateNew::getInstance($this->filepath)->clearTmpDir();
            PShowUpdateNew::getInstance($this->filepath)->downloadUpdate();
            PShowUpdateNew::getInstance($this->filepath)->extractUpdate();
            PShowUpdateNew::getInstance($this->filepath)->moveUpdateToModule();

            Tools::redirectAdmin(
                'index.php?controller=' . PShowUpdateNew::getInstance($this->filepath)->getModuleName()
                . 'Update&token=' . $this->token . '&page=execupdate&from_version=' . $from_ver
            );
        } catch (\PShowUpdateException $e) {
            $this->alerts[] = array('warning', $e->getMessage());
        }
    }

    /**
     * @deprecated Use migrations instead https://git.layersshow.com/prestashow/module-docs/src/master/migrations
     */
    public function execupdateAction()
    {
        $this->action = 'index';
        $this->action_displayName = $this->trans('Update informations');

        try {
            PShowUpdateNew::getInstance($this->filepath)->execUpdate(Tools::getValue('from_version'));
            PShowUpdateNew::getInstance($this->filepath)->clearTmpDir();

            $this->alerts[] = array(
                'success',
                $this->trans('Module updated. In case of problems, please reinstall the module.')
            );
        } catch (\PShowUpdateException $e) {
            $this->alerts[] = array('warning', $e->getMessage());
        }

        $this->indexAction();
    }
}
