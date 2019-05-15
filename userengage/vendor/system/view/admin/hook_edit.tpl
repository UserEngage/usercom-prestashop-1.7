<form method="post" action="" class="defaultForm form-horizontal">
    <h3>{l s='Edit hook' mod='skeleton'}</h3>

    <div class="panel-wrapper">
        <div class="form-group clearfix">	
            <label class="control-label col-lg-3 required">
                <span>
                    {l s='Hook' mod='skeleton'}
                </span>
            </label>

            <div class="col-lg-9">
                <select name="hook_name">
                    {foreach from=$hooks item='hook'}
                        <option value="{$hook['name']}" 
                                {if $hook['name'] == $h->hook_name}selected{/if}>
                            {$hook['name']}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>

    </div>

    <div class="panel-footer">
        <a href="{$link->getAdminLink("{$PSHOW_MODULE_CLASS_NAME_}Hook")}" class="btn btn-default">
            <i class="process-icon-cancel"></i> {l s='Cancel' mod='skeleton'}
        </a>
        <button type="submit" name="submit" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Save' mod='skeleton'}
        </button>
    </div>

</form>
