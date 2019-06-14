{function showTip type='success' id='' message='No message'}
    {if !PShow_Settings::getInstance($smarty.current_dir)->get('tip_'|cat:$id)}
        <div class="alert alert-{$type} fade in tip" id="{$id}">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <p>{$message}</p>
        </div>
    {/if}
{/function}

<script>
    if (typeof SELECT_TAB !== 'undefined') {
        SELECT_TAB.init('{$select_menu_tab}');
    }
    var PSHOW_MODULE_CLASS_NAME_ = "{$PSHOW_MODULE_CLASS_NAME_}";
    var SETTINGS_URL = "{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Settings", true)}";
    var MOD_SETTINGS = JSON.parse('{$mod_settings|json_encode}');

    {if PShow_Settings::getInstance($smarty.current_dir)->get('fold_menu_on_enter')}
    $('body').addClass('page-sidebar-closed');
    {/if}

</script>


{if version_compare($smarty.const._PS_VERSION_, '1.7.5.0', '>=')}
    <div class="row">
        <div class="page-head">
            <div class="col-xs-12">
                <ul class="breadcrumb page-breadcrumb">
                    <li class="breadcrumb-container">
                        <a href="https://prestashow.pl">
                            PrestaShow.pl
                        </a>
                    </li>

                    <li class="breadcrumb-container">
                        <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Main", true)}">{$module->displayName}</a>
                    </li>

                    <li class="breadcrumb-current">
                        <a href="#">{$controller_displayName}</a>
                    </li>

                </ul>

                <h2 class="page-title">{$action_displayName}</h2>
            </div>

        </div>
    </div>
{else}
    <div class="row">
        <div class="page-head">
            <h2 class="page-title">{$action_displayName}</h2>

            <ul class="breadcrumb page-breadcrumb">
                <li class="breadcrumb-container">
                    <a href="https://prestashow.pl">
                        PrestaShow.pl
                    </a>
                </li>

                <li class="breadcrumb-container">
                    <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Main", true)}">{$module->displayName}</a>
                </li>

                <li class="breadcrumb-current">
                    <a href="#">{$controller_displayName}</a>
                </li>

            </ul>
        </div>
    </div>
{/if}

<div class="row">
    <div class="col-lg-2">
        <div class="panel col-xs-offset-4 col-xs-4 col-md-offset-0 col-md-12">
            <h3 class="text-center"><strong><big>{$module->displayName}</big></strong></h3>
            <div class="panel-content">
                {if $isUpdateAvailable}
                    <p class="text-center">{l s='Version' mod='pshowsystem'}: <strong
                                class="label label-danger">{$moduleVersion}</strong></p>
                    <p class="text-center"><strong class="label label-danger">New version available!</strong>
                    <hr>
                    </p>
                {else}
                    <p class="text-center">{l s='Version' mod='pshowsystem'}: <strong
                                class="label label-success">{$moduleVersion}</strong></p>
                {/if}


                <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Update", true)}"
                   class="btn btn-default col-xs-12">
                    <i class="icon-search pull-left"></i>
                    {l s='See changelog' mod='pshowsystem'}
                </a>

                <br><br>

                <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Update", true)}&page=update"
                   onclick="javascript:if (!confirm('Update will override all module files. Continue?'))
                               return false;"
                   class="btn btn-warning col-xs-12 {$PSHOW_MODULE_CLASS_NAME_}-update-available"{if !$isUpdateAvailable} style="display: none;"{/if}>
                    <i class="icon-refresh pull-left"></i>
                    {l s='Click to update' mod='pshowsystem'}
                </a>

            </div>
        </div>

        <div class="clearfix"></div>

        <div class="tabs">
            <div class="list-group text-center">

                <strong><a class="list-group-item inactive">{l s='MODULE MENU' mod='pshowsystem'}</a></strong>

                {include file='../../../views/templates/side_menu.tpl'}

                <a class="list-group-item {if $smarty.get.controller == "{$PSHOW_MODULE_CLASS_NAME_}Hook"}active{/if}"
                   href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Hook", true)}">
                    {l s='Positions' mod='pshowsystem'}
                </a>

                <a class="list-group-item {if $smarty.get.controller == "{$PSHOW_MODULE_CLASS_NAME_}Settings"}active{/if}"
                   href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Settings", true)}">
                    {l s='Module settings' mod='pshowsystem'}
                </a>

                <a class="list-group-item {if $smarty.get.controller == "{$PSHOW_MODULE_CLASS_NAME_}ReportBug"}active{/if}"
                   href="https://helpdesk.prestashow.pl" target="_blank">
                    <span class="label label-default">{l s='Report bug or problem' mod='pshowsystem'}</span>
                </a>

                <a class="list-group-item {if $smarty.get.controller == "{$PSHOW_MODULE_CLASS_NAME_}Backup"}active{/if}"
                   href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Backup", true)}">
                    {l s='Backups' mod='pshowsystem'}
                </a>

            </div>
        </div>

        {if isset($pshowHook_below_side_menu)}{$pshowHook_below_side_menu}{/if}

        <div class="panel">
            <h3 role="button" data-toggle="collapse" aria-controls="collapseServerInfo"
                href="#collapseServerInfo" aria-expanded="true" id="headingServerInfo">
                {l s='Server info' mod='pshowsystem'}

                <i class="pull-right material-icons">expand_more</i>
            </h3>
            <div id="collapseServerInfo" class="panel-collapse collapse"
                 role="tabpanel" aria-labelledby="headingServerInfo">
                <p>
                    <strong>{l s='PHP version' mod='pshowsystem'}:</strong>
                    <span class="pull-right label {if version_compare(phpversion(), '5.6.0') == -1}label-danger{else}label-success{/if}">
                    {phpversion()}
                </span>
                <div class="clearfix"></div>
                </p>
                <p>
                    <strong>{l s='Memory limit' mod='pshowsystem'}:</strong>
                    <span class="pull-right label {if ((int)ini_get('memory_limit')) < 1000 && ((int)ini_get('memory_limit')) >= 0}label-danger{else}label-success{/if}">
                    {ini_get('memory_limit')}
                </span>
                <div class="clearfix"></div>
                </p>
                <p>
                    <strong>Max exec time:</strong>
                    <span class="pull-right label {if ((int)ini_get('max_execution_time')) < 300}label-danger{else}label-success{/if}">
                    {"H:i:s"|gmdate:((int)ini_get('max_execution_time'))}
                </span>
                <div class="clearfix"></div>
                </p>
                <p>
                    <strong>Upload max filesize:</strong>
                    <span class="pull-right label {if ((int)ini_get('upload_max_filesize')) < 10}label-warning{else}label-success{/if}">
                    {ini_get('upload_max_filesize')}
                </span>
                <div class="clearfix"></div>
                </p>
                <p>
                    <strong>Post max size:</strong>
                    <span class="pull-right label {if ((int)ini_get('post_max_size')) < 10}label-warning{else}label-success{/if}">
                    {ini_get('post_max_size')}
                </span>
                <div class="clearfix"></div>
                </p>
                {*<p>*}
                {*<strong>{l s='Free disk space [GB]' mod='pshowsystem'}:</strong>*}
                {*<span class="pull-right">*}
                {*{getDiskFreeSpace()}*}
                {*</span>*}
                {*<div class="clearfix"></div>*}
                {*</p>*}
                <p>
                    <strong>{l s='PCNTL extension' mod='pshowsystem'}:</strong>
                    <span class="pull-right">
                    {(int)extension_loaded('pcntl')}
                </span>
                <div class="clearfix"></div>
                </p>
                {if function_exists('libxml_clear_errors')}
                    <p>
                        <strong>{l s='LIBXML extension' mod='pshowsystem'}:</strong>
                        {if version_compare($smarty.const.LIBXML_DOTTED_VERSION, '2.9.3') == 0}
                            <span title="" data-toggle="tooltip" class="pull-right label label-tooltip label-danger"
                                  data-original-title="{l s='This version of PHP extension may cause problems in the operation of modules related to XML processing.' mod='pshowsystem'}"
                                  data-html="true" data-placement="top">
                            {$smarty.const.LIBXML_DOTTED_VERSION}
                        </span>
                        {else}
                            <span class="pull-right label label-success">
                            {$smarty.const.LIBXML_DOTTED_VERSION}
                        </span>
                        {/if}
                    <div class="clearfix"></div>
                    </p>
                {/if}
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="panel">
            <h3>{l s='Recommended' mod='pshowsystem'}</h3>
            <a href="{$recommended['url']}" target="_blank">
                <div class="col-xs-6">
                    <img class="img-responsive" alt="PrestaShow.pl" src="{$recommended['image']}">
                </div>
            </a>
            <div class="col-xs-6">
                <a href="{$recommended['url']}" target="_blank"><strong>{$recommended['name']}</strong></a>
            </div>
            <div class="col-xs-12">
                <p>{$recommended['description']}</p>
            </div>
            <div class="col-xs-12">
                <a href="{$recommended['url']}" target="_blank" class="btn btn-success btn-sm"
                   style="width: 100%;">
                    <strong>{l s='More' mod='pshowsystem'}</strong>
                </a>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="col-lg-10 modulecontainer">

        <div class="alert alert-danger {$PSHOW_MODULE_CLASS_NAME_}-update-available"{if !$isUpdateAvailable} style="display: none;"{/if}>
            {l s='Update your module! Updates are very important.' mod='pshowsystem'}
        </div>

        <div id="module_content">
            {include file='./admin/alerts.tpl'}
            {include file='./admin/tips.tpl'}

            {if isset($content) && $content}
                {$content}
            {else}
                <div class="{if isset($module_content_container)}{$module_content_container}{/if}">
                    {if in_array($controllername, array('settings', 'hook', 'backup', 'update', 'reportbug'))}
                        {include file="./admin/{$controllername|lower}_{$action|lower}.tpl"}
                    {else}
                        {include file="../../../views/templates/admin/{$controllername|lower}_{$action|lower}.tpl"}
                    {/if}
                </div>
            {/if}
        </div>
    </div>
    <div class="clearfix"></div>
</div>