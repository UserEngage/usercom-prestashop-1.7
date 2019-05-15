<h3>{l s='Module update' mod='pshowsystem'} "{$PShowUpdateInstance->getModuleName()}"</h3>

<div class="row">

    <div class="col-xs-6">

        <p>{l s='Your module version' mod='pshowsystem'}: {$ModuleVersionNumber}</p>
        <p>{l s='Newest module version' mod='pshowsystem'}: {$NewestVersionNumber}</p>
        <p>
            {if !$compareModuleAndNewestVersion}
                <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Update", true)}&page=update"
                   onclick="if (!confirm('{l s='This action will override all module files! Are you sure?' mod='pshowsystem'}')) return false;">
                    <button class="btn btn-success">
                        {l s='Click to start auto update' mod='pshowsystem'}
                    </button>
                </a>
            {else}
                <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Update", true)}&page=update&force=1"
                   onclick="if (!confirm('{l s='This action will override all module files! Are you sure?' mod='pshowsystem'}')) return false;">
                    <button class="btn btn-warning">{l s='Force update' mod='pshowsystem'}</button>
                </a>
                <br>
                <small>{l s='Use this button to update module before new version is released' mod='pshowsystem'}</small>
            {/if}
        </p>

    </div>

    <div class="col-xs-6">
        <p><strong>{l s='Recommended for this module' mod='pshowsystem'}:</strong></p>
        <p class="col-xs-6">
            <span class="label {if version_compare(phpversion(), '5.5.9') == -1}label-danger{else}label-success{/if}">
                {l s='PHP version' mod='pshowsystem'} >=5.5.9 
            </span>
        </p>
        <p class="col-xs-6">
            <span class="label {if !class_exists('ZipArchive')}label-danger{else}label-success{/if}">
                {l s='installed ZIP extension' mod='pshowsystem'}
            </span>
        </p>
        {if $PShowUpdateInstance->getModuleName() == 'pshowimporter'}
            <p class="col-xs-6">
                <span class="label {if !function_exists('libxml_use_internal_errors')}label-danger{else}label-success{/if}">
                    {l s='installed LIBXML extension' mod='pshowsystem'}
                </span>
            </p>
        {/if}
    </div>

</div>

<hr>

<p>
    <strong>
        {l s='Remember to read changelog before every update to see what changes will be introduced.' mod='pshowsystem'}
    </strong>
</p>

<hr>

<p style="overflow-y: scroll; height: 400px;">
    {$changelog|htmlspecialchars|nl2br}
</p>
