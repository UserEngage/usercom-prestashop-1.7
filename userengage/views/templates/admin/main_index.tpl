<div class="panel-heading">
    {l s='Module settings' mod='userengage'}
</div>

<form method="POST" action="" class="form-horizontal">

    <div class="panel-content">

        <div class="form-group clearfix">
            <label class="control-label col-lg-3" for='apiKey'>
                {l s='Enter API key' mod='userengage'}:
            </label>
            <div class="col-lg-7">
                <input type='text' name='apiKey' id='apiKey' placeholder="XXXXXX"
                       autocomplete="off" class="form-control" value="{$apiKey}"> 
            </div>
            <p class="clearfix">&nbsp;</p>
            <p class="alert alert-info col-lg-offset-3 col-lg-7">
                {l s='To get API key go to:' mod='userengage'} 
                <a href="https://app.userengage.com/api/integrations/">
                    {l s='UserEngage integrations' mod='userengage'}
                </a>
            </p>
        </div>

        <div class="clearfix"></div>

        <div class="form-group clearfix">
            <label class="control-label col-lg-3" for='server'>
                {l s='Enter custom server domain' mod='userengage'}:
            </label>
            <div class="col-lg-7">
                <input type='text' name='server' id='server' placeholder="app.userengage.com"
                       autocomplete="off" class="form-control" value="{$server}"> 
            </div>
            <p class="clearfix">&nbsp;</p>
            <p class="alert alert-info col-lg-offset-3 col-lg-7">
                {l s='Default: app.userengage.com' mod='userengage'} 
            </p>
        </div>

        <div class="clearfix"></div>

        <div class="form-group clearfix">
            <label class="control-label col-lg-3" for='debug'>
                {l s='Enable debug mode' mod='userengage'}:
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="debug" id="debug_on" 
                           value="1" {if $debug}checked{/if}>
                    <label for="debug_on">
                        {l s='Yes' mod='userengage'}
                    </label>
                    <input type="radio" name="debug" id="debug_off" 
                           value="0" {if !$debug}checked{/if}>
                    <label for="debug_off">
                        {l s='No' mod='userengage'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
            <p class="clearfix"></p>
            <p class="alert alert-info col-lg-offset-3 col-lg-7">
                {l s='If enabled, all sent events will be visible in the internet browser console.' mod='userengage'} 
            </p>
        </div>

        <div class="clearfix"></div>

    </div>

    <div class="panel-footer">
        <button type="submit" name="submitSave" class="btn btn-default pull-right">
            <i class="process-icon-save"></i>
            {l s='Save' mod='userengage'}
        </button>
    </div>

</form>