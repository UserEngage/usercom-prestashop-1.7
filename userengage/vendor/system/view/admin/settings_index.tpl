{if $mod_settings|count == 0}

    <div class="alert alert-info">
        <p>{l s='This module do not have any settings' mod='pshowimporter'}</p>
    </div>

{/if}

{$form}

<script>

    $(function () {
        $('#configuration_form .panel').removeClass('panel');
    });

</script>