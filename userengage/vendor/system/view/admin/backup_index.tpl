<h3>
    {l s='Module backups' mod='pshowsystem'}
    <span class="panel-heading-action">
        <a id="desc-cms_category-new" class="list-toolbar-btn" style='width: 250px; line-height: 28px;'
           href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Backup", true)}&page=backup">
            <i class="process-icon-new" style='float: left;'></i> {l s='Create new backup' mod='pshowsystem'}
        </a>
    </span>
</h3>

<div class="alert alert-info">
    {l s='Backup stores only files! You can not use them to back up your database!' mod='pshowsystem'}
</div>

{if count($backups) > 0}

    <table class="table table-hover">

        <thead>
            <tr>
                <th>#</th>
                <th>{l s='Filename' mod='pshowsystem'}</th>
                <th>{l s='Version' mod='pshowsystem'} </th>
                <th>{l s='Size' mod='pshowsystem'}</th>
                <th>{l s='Date' mod='pshowsystem'}</th>
                <th></th>
            </tr>
        </thead>

        <tbody>

            {assign var='i' value=1}

            {foreach from=$backups item='backup'}

                <tr>
                    <td>{$i}</td>
                    <td>{$backup['filename']}.zip</td>
                    <td>{$backup['version']}</td>
                    <td>{$backup['size']}</td>
                    <td>{$backup['time']|replace:".":":"} {$backup['date']}</td>
                    <td class="text-right">
                        <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Backup", true)}&page=remove&filename={$backup['filename']}" class="btn btn-warning">
                            <i class="icon-trash"></i>
                            {l s='Remove' mod='pshowsystem'}
                        </a>
                        <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Backup", true)}&page=restorebackup&filename={$backup['filename']}.zip" class="btn btn-danger">
                            <i class="icon-retweet"></i>
                            {l s='Restore' mod='pshowsystem'}
                        </a>
                    </td>
                </tr>

                {assign var='i' value=$i+1}

            {/foreach}

        </tbody>

    </table>

{else}

    <div class="alert alert-info">
        {l s='You don\'t have any backups' mod='pshowsystem'}
    </div>

{/if}
