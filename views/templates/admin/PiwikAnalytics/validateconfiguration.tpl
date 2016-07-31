

<h2>{l s="Validation result" mod='piwikanalyticsjs'}</h2>

<table class="table tableDnD" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;min-width: 400px;">
    <thead>
        <tr><th class="center" colspan="3">{l s="Recommended config settings" mod='piwikanalyticsjs'}</th></tr>
    </thead>
    <tbody>
        {if $useHttps}
            <tr style="background: lightgreen none repeat scroll 0% 0%;">
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>{l s="Name" mod='piwikanalyticsjs'}: USE_HTTPS<br>{l s="Value" mod='piwikanalyticsjs'}: {l s="Yes" mod='piwikanalyticsjs'}</td>
                <td>{l s="OK: No issues" mod='piwikanalyticsjs'}</td>
            </tr>
        {else}
            <tr style="background: lightgoldenrodyellow none repeat scroll 0% 0%;">
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>{l s="Name" mod='piwikanalyticsjs'}: USE_HTTPS<br>{l s="Value" mod='piwikanalyticsjs'}: {l s="No" mod='piwikanalyticsjs'}</td>
                <td>{l s="Warning: it's recommended to use SSL/HTTPS for your Piwik installation" mod='piwikanalyticsjs'}</td>
            </tr>
        {/if} {*/$useHttps*}
        {if $useProxy}
            <tr style="background: lightgreen none repeat scroll 0% 0%;">
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>{l s="Name" mod='piwikanalyticsjs'}: USE_PROXY<br>{l s="Value" mod='piwikanalyticsjs'}: {l s="Yes" mod='piwikanalyticsjs'}</td>
                <td>{l s="OK: No issues" mod='piwikanalyticsjs'}</td>
            </tr>
        {else}
            <tr style="background: lightgoldenrodyellow none repeat scroll 0% 0%;">
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>{l s="Name" mod='piwikanalyticsjs'}: USE_PROXY<br>{l s="Value" mod='piwikanalyticsjs'}: {l s="No" mod='piwikanalyticsjs'}</td>
                <td>{l s="Warning: it's recommended to use a proxy script to protect your Piwik installation against unnecessary suspicious requests" mod='piwikanalyticsjs'}</td>
            </tr>
        {/if} {*/$useProxy*}
        {if $useDnt}
            <tr style="background: lightgreen none repeat scroll 0% 0%;">
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>{l s="Name" mod='piwikanalyticsjs'}: DNT (Do Not Track)<br>{l s="Value" mod='piwikanalyticsjs'}: {l s="Yes" mod='piwikanalyticsjs'}</td>
                <td>{l s="OK: No issues" mod='piwikanalyticsjs'}</td>
            </tr>
        {else}
            <tr style="background: lightgoldenrodyellow none repeat scroll 0% 0%;">
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>{l s="Name" mod='piwikanalyticsjs'}: DNT (Do Not Track)<br>{l s="Value" mod='piwikanalyticsjs'}: {l s="No" mod='piwikanalyticsjs'}</td>
                <td>{l s="Warning: it's recommended to respect your users right to privacy" mod='piwikanalyticsjs'}</td>
            </tr>
        {/if} {*/$useDnt*}
        {if $piwikCurrencyMatchesShop}
            <tr style="background: lightgreen none repeat scroll 0% 0%;">
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>
                    <table>
                        <thead>
                            <tr><td class="center">{l s="Name" mod='piwikanalyticsjs'}</td>
                                <td class="center">{l s="Value" mod='piwikanalyticsjs'}</td></tr>
                        </thead><tbody>
                            <tr>
                                <td>{l s="DEFAULT_CURRENCY (Module)" mod='piwikanalyticsjs'}</td>
                                <td>{$pkCurrency}</td>
                            </tr>
                            <tr>
                                <td>{l s="Piwik site settings" mod='piwikanalyticsjs'}</td>
                                <td>{$pkSiteCurrency}</td>
                            </tr>
                            <tr>
                                <td>{l s="CURRENCY_DEFAULT (Shop)" mod='piwikanalyticsjs'}</td>
                                <td>{$pkShopCurrency}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>{l s="OK: No issues" mod='piwikanalyticsjs'}</td>
            </tr>
        {else}
            <tr style="background: lightcoral none repeat scroll 0% 0%;">
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>
                    <table>
                        <thead>
                            <tr><th class="center">{l s="Name" mod='piwikanalyticsjs'}</th>
                                <th class="center">{l s="Value" mod='piwikanalyticsjs'}</th></tr>
                        </thead><tbody>
                            <tr>
                                <td>{l s="DEFAULT_CURRENCY (Module)" mod='piwikanalyticsjs'}</td>
                                <td>{$pkCurrency}</td>
                            </tr>
                            <tr>
                                <td>{l s="Piwik site settings" mod='piwikanalyticsjs'}</td>
                                <td>{$pkSiteCurrency}</td>
                            </tr>
                            <tr>
                                <td>{l s="CURRENCY_DEFAULT (Shop)" mod='piwikanalyticsjs'}</td>
                                <td>{$pkShopCurrency}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>{l s="ERROR: it's recommended use the same currency in both Piwik and your shop" mod='piwikanalyticsjs'}</td>
            </tr>
        {/if}{*/$piwikCurrencyMatchesShop*}
    </tbody>
</table>

<table class="table tableDnD" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;min-width: 400px;">
    <thead>
        <tr><th class="center" colspan="3">{l s="Hooks Registered" mod='piwikanalyticsjs'}</th></tr>
    </thead>
    <tbody>
        {foreach from=$section_hooks  key=section_hooks_name item=section_hooks_hook}
            <tr{if $section_hooks_hook} style="background: lightgreen none repeat scroll 0% 0%;"{else} style="background: lightcoral none repeat scroll 0% 0%;"{/if}>
                <td>{l s="System" mod='piwikanalyticsjs'}</td>
                <td>{l s="Hook" mod='piwikanalyticsjs'}: {$section_hooks_name}</td>
                <td>{if $section_hooks_hook}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}{l s="Error: not valid" mod='piwikanalyticsjs'}{/if}</td>
            </tr>
        {/foreach}
    </tbody>
</table>

<table class="table tableDnD" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;min-width: 400px;">
    <thead>
        <tr>
            <th class="center" colspan="3">{l s="Connection to Piwik" mod='piwikanalyticsjs'}</th>
        </tr>
    </thead>
    <tbody>
        <tr{if $section_piwik.siteid} style="background: lightgreen none repeat scroll 0% 0%;"{else} style="background: lightcoral none repeat scroll 0% 0%;"{/if}>
            <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
            <td>{l s="Name" mod='piwikanalyticsjs'}: SITEID<br>{l s="Value" mod='piwikanalyticsjs'}: {$pksiteid}</td>
            <td>{if $section_piwik.siteid}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}{l s="Error: not valid" mod='piwikanalyticsjs'}{/if}</td>
        </tr>
        <tr{if $section_piwik.token} style="background: lightgreen none repeat scroll 0% 0%;"{else} style="background: lightcoral none repeat scroll 0% 0%;"{/if}>
            <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
            <td>{l s="Name" mod='piwikanalyticsjs'}: TOKEN</td>
            <td>{if $section_piwik.token}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}{l s="Error: not valid" mod='piwikanalyticsjs'}{/if} </td>
        </tr>
        <tr{if $section_piwik.token} style="background: lightgreen none repeat scroll 0% 0%;"{else} style="background: lightcoral none repeat scroll 0% 0%;"{/if}>
            <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
            <td>{l s="Name" mod='piwikanalyticsjs'}: HOST<br>{l s="Value" mod='piwikanalyticsjs'}: {$pkhost}</td>
            <td>{if $section_piwik.token}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}{l s="Error: not valid" mod='piwikanalyticsjs'}{/if} </td>
        </tr>
        <tr{if $section_piwik.piwik_connection.result} style="background: lightgreen none repeat scroll 0% 0%;"{else} style="background: lightcoral none repeat scroll 0% 0%;"{/if}>
            <td>{l s="Test" mod='piwikanalyticsjs'}</td>
            <td>{l s="Connection to Piwik API" mod='piwikanalyticsjs'}</td>
            <td>
                {$section_piwik.piwik_connection.errors}
                {if $section_piwik.piwik_connection.result}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}{l s="Error: not valid" mod='piwikanalyticsjs'}{/if}
            </td>
        </tr>
    </tbody>
</table>

{if $useProxy}
    <table class="table tableDnD" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;min-width: 400px;">
        <thead>
            <tr>
                <th class="center" colspan="3">{l s="Proxy script" mod='piwikanalyticsjs'}</th>
            </tr>
        </thead>
        <tbody>
            {if $http_auth}
            <tr>
                <td>{l s="Configuration" mod='piwikanalyticsjs'}</td>
                <td>
                    {l s="Name" mod='piwikanalyticsjs'}: PAUTHUSR {l s="OK" mod='piwikanalyticsjs'}<br>
                    {l s="Name" mod='piwikanalyticsjs'}: PAUTHPWD {l s="OK" mod='piwikanalyticsjs'}<br>
                </td>
                <td>{l s="Using username and password to connect to Piwik" mod='piwikanalyticsjs'}</td>
            </tr>
            {/if}
            <tr{if $proxy_script_response_unauthorized eq false} style="background: lightgreen none repeat scroll 0% 0%;"{else} style="background: lightcoral none repeat scroll 0% 0%;"{/if}>
                <td>{l s="Test" mod='piwikanalyticsjs'}</td>
                <td>
                    {l s="Connection using proxy script" mod='piwikanalyticsjs'}<br>
                    {l s="URL:" mod='piwikanalyticsjs'} {$proxy_script_url}<br>
                    
                </td>
                <td>
                    {if $useCurl}
                        {if $proxy_script_response_header eq false}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}{$proxy_script_response_header}{/if}
                    {else}
                        {if $proxy_script_response_unauthorized eq false}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}
                        {l s="Response header" mod='piwikanalyticsjs'}<br>
                        {$proxy_script_response_header}
                        {/if}
                    {/if}
                </td>
            </tr>
            <tr{if $piwik_response_unauthorized eq false} style="background: lightgreen none repeat scroll 0% 0%;"{else} style="background: lightcoral none repeat scroll 0% 0%;"{/if}>
                <td>{l s="Test" mod='piwikanalyticsjs'}</td>
                <td>
                    {l s="Connection to Piwik" mod='piwikanalyticsjs'}<br>
                    {l s="URL:" mod='piwikanalyticsjs'} {$piwik_url}<br>
                </td>
                <td>
                    {if $useCurl}
                        {if $piwik_response_header eq false}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}{$piwik_response_header}{/if}
                    {else}
                        {if $piwik_response_unauthorized eq false}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}
                        {l s="Response header" mod='piwikanalyticsjs'}<br>
                        {$piwik_response_header}
                        {/if}
                    {/if}
                </td>
            </tr>
            <tr{if $proxy_response_match eq true} style="background: lightgreen none repeat scroll 0% 0%;"{else} style="background: lightgoldenrodyellow none repeat scroll 0% 0%;"{/if}>
                <td>{l s="Test" mod='piwikanalyticsjs'}</td>
                <td>
                    {l s="Compare response" mod='piwikanalyticsjs'}<br>
                    {l s="URL:" mod='piwikanalyticsjs'} {$proxy_script_url}<br>
                    {l s="URL:" mod='piwikanalyticsjs'} {$piwik_url}<br>
                </td>
                <td>
                    {if $proxy_response_match eq true}{l s="OK: No issues" mod='piwikanalyticsjs'}{else}
                    {l s="Warning: the two urls do not seem to return the same data, this is necessarily not an error but make sure tracking is working" mod='piwikanalyticsjs'}{/if}
                </td>
            </tr>
        </tbody>
    </table>
{/if}