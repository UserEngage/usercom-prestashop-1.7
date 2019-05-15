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
class FrontController extends FrontControllerCore
{

    protected function smartyOutputContent($content)
    {
        // render shop to buffer
        ob_start();
        parent::smartyOutputContent($content);
        $html = ob_get_flush();
        ob_clean();

        // UserEngage
        if (Configuration::get('userengage_apikey')) {
            $module = Module::getInstanceByName('userengage');
            if ($module) {
                $module->hookSmartyOutputContent($html);
            }
        }

        echo $html;
    }
}
