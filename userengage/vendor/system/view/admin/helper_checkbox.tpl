<span class="switch prestashop-switch fixed-width-lg">
    <input type="radio" name="{$s['name']}" id="{$s['name']}_on" value="1" {if $s['value'] == "1"}checked="checked"{/if}>
    <label for="{$s['name']}_on" class="radioCheck">
        {l s='Yes' mod='pshowimporter'}
    </label>
    <input type="radio" name="{$s['name']}" id="{$s['name']}_off" value="0" {if $s['value'] == "0"}checked="checked"{/if}>
    <label for="{$s['name']}_off" class="radioCheck">
        {l s='No' mod='pshowimporter'}
    </label>
    <a class="slide-button btn"></a>
</span>