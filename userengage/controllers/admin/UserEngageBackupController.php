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

class UserEngageBackupController extends PShowBackupController
{

    public $select_menu_tab = 'subtab-UserEngageMain';

    public function __construct()
    {
        parent::__construct();

        global $smarty;
        $smarty->assign('lang_iso', $this->context->language->iso_code);
    }
}
