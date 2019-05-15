{foreach from=$alerts item='alert'}

    <div class="alert alert-{$alert[0]}">
        <p>{$alert[1]}</p>
    </div>

{/foreach}