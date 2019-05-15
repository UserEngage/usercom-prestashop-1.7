<h3>
    {l s='Module hooks' mod='skeleton'}

    <span class="badge">{$hooksCount}</span>

    <span class="panel-heading-action">
        <a id="desc-product-new" class="list-toolbar-btn" 
           href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Hook")}&page=add">
            <span title="" data-toggle="tooltip" class="label-tooltip" data-html="true" 
                  data-original-title="{l s='Add new' mod='skeleton'}" data-placement="top">
                <i class="process-icon-new"></i>
            </span>
        </a>
        <a class="list-toolbar-btn" href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Hook")}&page=categories">
            <span title="" data-toggle="tooltip" class="label-tooltip" data-html="true" 
                  data-original-title="{l s='Refresh list' mod='skeleton'}" data-placement="top">
                <i class="process-icon-refresh"></i>
            </span>
        </a>
    </span>
</h3>

<table class="hooks_table table table-striped">

    <thead>

        <tr>
            <th class="fixed-width-xs">{l s='ID' mod='skeleton'}</th>
            <th>{l s='Hook name' mod='skeleton'}</th>
            <th></th>
        </tr>

    </thead>

    <tbody>

        {foreach from=$hooks item='hook'}

            <tr>
                <td>{$hook['id_hook']}</td>
                <td>{$hook['hook_name']}</td>
                <td class="text-right">
                    <div class="btn-group-action">
                        <div class="btn-group pull-right">
                            <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Hook")}&page=edit&id_hook={$hook['id_hook']}" 
                               class="btn btn-default" title="{l s='Edit' mod='skeleton'}">
                                <i class="icon-pencil"></i> {l s='Edit' mod='skeleton'}
                            </a>
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-caret-down"></i>&nbsp;
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Hook")}&page=remove&id_hook={$hook['id_hook']}" 
                                       title="{l s='Remove' mod='skeleton'}" class="remove">
                                        <i class="icon-trash"></i> {l s='Remove' mod='skeleton'}
                                    </a>							
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>

        {/foreach}

    </tbody>

</table>
