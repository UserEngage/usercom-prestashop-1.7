
{* Prepare customer data *}
{if $userengage_is_logged || $userengage_customer->is_guest}
    <script data-cfasync="false" type="text/javascript">
        window.civchat = {ldelim}
                apiKey: '{$userengage_apikey|escape:'javascript'}',
                name: '{$userengage_customer->firstname|cat:" "|cat:$userengage_customer->lastname|escape:'javascript'}',
                user_id: {$userengage_customer->id|intval},
                email: '{$userengage_customer->email|escape:'javascript'}',
                gender: {$userengage_customer->id_gender|intval},
                status: {$userengage_customer->active|intval},
                date_attr: '{$smarty.now|date_format:"%F%Z%R%z"}',
                newsletter: {$userengage_customer->newsletter|intval},
                registered_user: {$userengage_customer->is_guest|intval}
        {rdelim};

        {* Append customer address *}
        {if $userengage_address_delivery->id}
            window.civchat.address1 = "{$userengage_address_delivery->address1|escape:'javascript'}";
            window.civchat.address2 = "{$userengage_address_delivery->address2|escape:'javascript'}";
            window.civchat.city = "{$userengage_address_delivery->city|escape:'javascript'}";
            window.civchat.country = "{$userengage_address_delivery->country|escape:'javascript'}";
            window.civchat.state_id = {$userengage_address_delivery->id_state|intval};
            window.civchat.postal_code = "{$userengage_address_delivery->postcode|escape:'javascript'}";
            window.civchat.company = {ldelim}name: "{$userengage_address_delivery->company|escape:'javascript'}"{rdelim};
                window.civchat.phone = "{$userengage_address_delivery->phone|escape:'javascript'}";
        {/if}
    </script>
{else}
    <script data-cfasync="false" type="text/javascript">
        window.civchat = {ldelim}apiKey: '{$userengage_apikey|escape:'javascript'}'{rdelim};
    </script>
{/if}

{* Include base JavaScipt file *}
{if empty($userengage_server)}
    {assign var='userengage_server' value='app.userengage.com'}
{/if}
<script data-cfasync="false" type="text/javascript" src="https://{$userengage_server}/widget.js"></script>

{* Prepare path to the module JavaScipt file *}
{assign var='userengage_js_path' value=$smarty.const.__PS_BASE_URI__|cat:'modules/userengage/views/js/'}
{if isset($userengage_debug) && $userengage_debug}
    {assign var='userengage_js_path' value=$userengage_js_path|cat:'userengage.js'}
{else}
    {assign var='userengage_js_path' value=$userengage_js_path|cat:'userengage.min.js'}
{/if}
{assign var='userengage_js_path' value=($userengage_js_path|cat:'?v='|cat:($userengage_version|intval))}

{* Include module JavaScipt file *}
<script data-cfasync="false" type="text/javascript" src="{$userengage_js_path}"></script>

{* Enable debug mode *}
{if isset($userengage_debug) && $userengage_debug}
    <script data-cfasync="false">
        UserEngage.debug = true;
    </script>
{/if}

{* Send event.purchase *}
{if isset($userengage_purchase) && count($userengage_purchase)}
    <script data-cfasync="false">
        if (typeof UserEngage !== "undefined") {
            UserEngage.event.purchase({$userengage_purchase|@json_encode nofilter});
            UserEngage.event.product.purchase({$userengage_purchase.products|@json_encode nofilter});
        }
    </script>
{/if}

{* Send event.registration *}
{if isset($userengage_account_creation) && count($userengage_account_creation)}
    <script data-cfasync="false">
        if (typeof UserEngage !== "undefined")
            UserEngage.event.registration({$userengage_account_creation|@json_encode nofilter});
    </script>
{/if}

{* Send event.newsletter *}
{if isset($userengage_newsletter_signup) && count($userengage_newsletter_signup)}
    <script data-cfasync="false">
        if (typeof UserEngage !== "undefined")
            UserEngage.event.newsletter('{$userengage_newsletter_signup|escape:'javascript'}', document.location.href);
    </script>
{/if}

{* Watch for product event refund&return *}
{if isset($userengage_order_products) && count($userengage_order_products)}
    <script data-cfasync="false">
        if (typeof UserEngage !== "undefined") {ldelim}
                UserEngage.data.orderProducts = {$userengage_order_products|@json_encode nofilter};
                UserEngage.watchForRefundSubmit();
        {rdelim}
    </script>
{/if}