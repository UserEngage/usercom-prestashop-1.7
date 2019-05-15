{foreach from=$tips item='tip'}
    {showTip type=$tip.type id=$tip.id message=$tip.message}
{/foreach}