<form action='#tabs-pk1' method='post' class='pkforms' autocomplete="off" name="formUpdatePiwikAnalyticsjsDefaults">
    <label>
        <span>{l s='Piwik Host' mod='piwikanalyticsjs'} <sup>*</sup></span>
        <input id='{$pkCPREFIX}HOST' type='text' name='{$pkCPREFIX}HOST' placeholder='analytics.example.com/' value="{$pkfvHOST}" onchange="tabContentChanged(true);" />
        <small>{l s='The host where piwik is installed, Example: www.example.com/piwik/ (without protocol and with / at the end!)' mod='piwikanalyticsjs'}</small>
    </label>

    <label>
        <span>{l s='Piwik site id' mod='piwikanalyticsjs'} <sup>*</sup></span>
        <input id='{$pkCPREFIX}SITEID' type='text' name='{$pkCPREFIX}SITEID' placeholder='32' value="{$pkfvSITEID}" onchange="tabContentChanged(true);" />
        <small>{l s='You can find piwik site id by loggin to piwik installation. Example: 10' mod='piwikanalyticsjs'}</small>
    </label>

    <label>
        <span>{l s='Piwik token auth' mod='piwikanalyticsjs'} <sup>*</sup></span>
        <input id='{$pkCPREFIX}TOKEN_AUTH' type='text' name='{$pkCPREFIX}TOKEN_AUTH' placeholder='abcdef12345678' value="{$pkfvTOKEN_AUTH}" onchange="tabContentChanged(true);" />
        <small>{l s='You can find piwik token by loggin to piwik installation. under API' mod='piwikanalyticsjs'}</small>
    </label>

    <label class="switch">
        <span>{l s='Enable client side DoNotTrack detection' mod='piwikanalyticsjs'}</span>
        <input id="{$pkCPREFIX}DNT" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $pkfvDNT==1} checked="checked"{/if} name="{$pkCPREFIX}DNT" onchange="tabContentChanged(true);" />
        <label for="{$pkCPREFIX}DNT" data-on="Yes" data-off="No" style="clear: both;"></label>
        <small>{l s='Tracking requests will not be sent if visitors do not wish to be tracked.' mod='piwikanalyticsjs'}</small>
    </label>

    <label>
        <span>{l s='Piwik Currency' mod='piwikanalyticsjs'} <sup>*</sup></span>
        <select name="{$pkCPREFIX}DEFAULT_CURRENCY" id="{$pkCPREFIX}DEFAULT_CURRENCY" onchange="tabContentChanged(true);" >
            <option value="0"{if $pkfvDEFAULT_CURRENCY == "0"} selected="selected"{/if}>{l s='Choose currency' mod='piwikanalyticsjs'}</option>
            {foreach from=$pkfvCurrencies  key=currency_key item=currency_value}
            <option value="{$currency_value.iso_code}"{if $pkfvDEFAULT_CURRENCY == $currency_value.iso_code} selected="selected"{/if}>{$currency_value.name}</option>
            {/foreach}
        </select>
        <small>{l s='Based on your settings in Piwik your default currency is' mod='piwikanalyticsjs'} {$pkfvCURRENCY_DEFAULT}</small>
    </label>
    
    <label>
        <span>{l s='Piwik Report date' mod='piwikanalyticsjs'}</span>
        <select name="{$pkCPREFIX}DREPDATE" id="{$pkCPREFIX}DREPDATE" onchange="tabContentChanged(true);" >
            <option value="day|today"{if $pkfvDREPDATE == "day|today"} selected="selected"{/if}>{l s='Today' mod='piwikanalyticsjs'}</option>
            <option value="day|yesterday"{if $pkfvDREPDATE == "day|yesterday"} selected="selected"{/if}>{l s='Yesterday' mod='piwikanalyticsjs'}</option>
            <option value="range|previous7"{if $pkfvDREPDATE == "range|previous7"} selected="selected"{/if}>{l s='Previous 7 days (not including today)' mod='piwikanalyticsjs'}</option>
            <option value="range|previous30"{if $pkfvDREPDATE == "range|previous30"} selected="selected"{/if}>{l s='Previous 30 days (not including today)' mod='piwikanalyticsjs'}</option>
            <option value="range|last7"{if $pkfvDREPDATE == "range|last7"} selected="selected"{/if}>{l s='Last 7 days (including today)' mod='piwikanalyticsjs'}</option>
            <option value="range|last30"{if $pkfvDREPDATE == "range|last30"} selected="selected"{/if}>{l s='Last 30 days (including today)' mod='piwikanalyticsjs'}</option>
            <option value="week|today"{if $pkfvDREPDATE == "week|today"} selected="selected"{/if}>{l s='Current Week' mod='piwikanalyticsjs'}</option>
            <option value="month|today"{if $pkfvDREPDATE == "month|today"} selected="selected"{/if}>{l s='Current Month' mod='piwikanalyticsjs'}</option>
            <option value="year|today"{if $pkfvDREPDATE == "year|today"} selected="selected"{/if}>{l s='Current Year' mod='piwikanalyticsjs'}</option>
        </select>
        <small>{l s='Report date to load by default from "Stats => Piwik Analytics"' mod='piwikanalyticsjs'}</small>
    </label>
    
    <hr class="bd-blue"/>

    <input type="hidden" name="username_changed" id="username_changed" value="0"/>
    <label>
        <span>{l s='Piwik User name' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}USRNAME' type='text' name='{$pkCPREFIX}USRNAME' placeholder='user-name' value="{$pkfvUSRNAME}" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" onchange="if ($('#{$pkCPREFIX}USRNAME').val() !== '{$pkfvUSRNAME}') {ldelim}$('#username_changed').val(1);tabContentChanged(true);{rdelim}else {ldelim}$('#username_changed').val(0);{rdelim}" />
        <small>{l s='You can store your Username for Piwik here to make it easy to open piwik interface from your stats page with automatic login'}</small>
    </label>

    <input type="hidden" name="password_changed" id="password_changed" value="0"/>
    <label>
        <span>{l s='Piwik User password' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}USRPASSWD' type='password' name='{$pkCPREFIX}USRPASSWD' placeholder='password'{*  value="{$pkfvUSRPASSWD}" *} autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" onchange="if ($('#{$pkCPREFIX}USRPASSWD').val() !== '') {ldelim}$('#password_changed').val(1);tabContentChanged(true);{rdelim}" />
        <small>{l s='You can store your Password for Piwik here to make it easy to open piwik interface from your stats page with automatic login'}</small>
    </label>

    <hr/>
    <input type='submit' class='button pkbutton bg-blue' value='{l s='Save' mod='piwikanalyticsjs'}' name='submitUpdatePiwikAnalyticsjsDefaults' /> 
</form>