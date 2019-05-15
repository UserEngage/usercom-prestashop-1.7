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
class PShowBackupController extends PShowAdminController
{

    public $default_action = 'index';
    public $filepath = null;

    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        $this->filepath = $reflection->getFileName();

        require_once dirname($this->filepath) . "/../../config.php";

        parent::__construct();

        $this->controller_displayName = $this->l('Backup');
    }

    public function human_filesize($bytes, $dec = 2)
    {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    public function indexAction()
    {
        $this->action_displayName = $this->l('List');

        $backups = array();

        $glob = glob(getModulePath($this->filepath) . "update/backups/*.zip");

        foreach ($glob as $f) {
            $filename = pathinfo($f, PATHINFO_FILENAME);
            $data = explode("_", $filename);

            $backups[$f] = array(
                'filename' => $filename,
                'modulename' => $data[0],
                'version' => $data[1],
                'time' => $data[2],
                'size' => $this->human_filesize(filesize($f)),
                'date' => $data[3]
            );
        }

        usort($backups, function ($a, $b) {
            return ($a['date'] > $b['date']) ? 1 : -1;
        });

        $this->context->smarty->assign('backups', $backups);
    }

    public function removeAction()
    {
        if (($filename = Tools::getValue('filename')) && file_exists(getModulePath($this->filepath) . "update/backups/" . $filename . ".zip")) {
            $unlink = @unlink(getModulePath($this->filepath) . "update/backups/" . $filename . ".zip");

            if ($unlink)
                $this->alerts[] = array('success', $this->l('Removed module backup: ' . $filename));
            else
                $this->alerts[] = array('warning',
                    $this->l('I don\'t have permissions to delete file: ' . $filename));
        }

        $this->action = 'index';
        $this->indexAction();
    }

    public function backupAction()
    {
        try {
            PShowUpdate::getInstance($this->filepath)->makeModuleBackup();
            $this->alerts[] = array('success', $this->l('Created module backup'));
        } catch (\PShowUpdateException $e) {
            $this->alerts[] = array('warning', $e->getMessage());
        }

        $this->action = 'index';
        $this->indexAction();
    }

    public function updateChmod($path = null)
    {
        if ($path === null)
            $path = getModulePath($this->filepath);

        $files = glob($path);

        foreach ($files as $file) {
            if (is_dir($file))
                $this->updateChmod($file . "/*");

            @chmod($file, 0777);
        }
    }

    public function restorebackupAction()
    {
        $this->action = 'index';
        $this->indexAction();

        if (!Tools::getValue('filename')) {
            return false;
        }

        $this->updateChmod();

        try {
            PShowUpdate::getInstance($this->filepath)->clearTmpDir();

            PShowUpdate::getInstance($this->filepath)->makeModuleBackup();

            PShowUpdate::getInstance($this->filepath)->moveBackupToTmp(Tools::getValue('filename'));

            PShowUpdate::getInstance($this->filepath)->extractBackup(Tools::getValue('filename'));

            PShowUpdate::getInstance($this->filepath)->moveBackupToModule(Tools::getValue('filename'));

            PShowUpdate::getInstance($this->filepath)->clearTmpDir();

            $this->alerts[] = array('success', 'Backup restored.');
        } catch (\PShowUpdateException $e) {
            $this->alerts[] = array('warning', $e->getMessage());
        }
    }
}
