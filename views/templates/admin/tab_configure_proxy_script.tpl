{*
* Copyright (C) 2016 Christian Jensen
*
* This file is part of PiwikAnalyticsJS for prestashop.
* 
* PiwikAnalyticsJS for prestashop is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* PiwikAnalyticsJS for prestashop is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with PiwikAnalyticsJS for prestashop.  If not, see <http://www.gnu.org/licenses/>.
*
*
* @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*}
<form action='#tabs-pk2' method='post' class='pkforms' autocomplete="off" name="formUpdatePiwikAnalyticsjsProxyScript">
    <div style="float: left; display: block; width: 100%; margin-bottom: 8px;">
        <label class="switch" style="max-width: 150px; text-align: center; float: left ! important;">
            <span style="margin: 0px auto; float: none;">{l s='Use proxy script' mod='piwikanalyticsjs'}</span>
            <input id="{$pkCPREFIX}USE_PROXY" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $pkfvUSE_PROXY==1} checked="checked"{/if} name="{$pkCPREFIX}USE_PROXY" onchange="tabContentChanged(true);"/>
            <label for="{$pkCPREFIX}USE_PROXY" data-on="Yes" data-off="No" style="margin: 0px auto; float: none;"></label>
            <small>{l s='Whether or not to use the proxy insted of Piwik Host' mod='piwikanalyticsjs'}</small>
        </label>
        {if $has_cURL}
            <label class="switch" style="max-width: 150px; text-align: center; float: left ! important;">
                <span style="margin: 0px auto; float: none;">{l s='Use cURL' mod='piwikanalyticsjs'}</span>
                <input id="{$pkCPREFIX}USE_CURL" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $pkfvUSE_CURL==1} checked="checked"{/if} name="{$pkCPREFIX}USE_CURL" onchange="tabContentChanged(true);"/>
                <label for="{$pkCPREFIX}USE_CURL" data-on="Yes" data-off="No" style="margin: 0px auto; float: none;"></label>
                <small>{l s='Whether or not to use cURL in Piwik API and proxy requests?' mod='piwikanalyticsjs'}</small>
            </label>
        {/if}
        <label class="switch" style="max-width: 150px; text-align: center; float: left ! important;">
            <span style="margin: 0px auto; float: none;">{l s='Use HTTPS' mod='piwikanalyticsjs'}</span>
            <input id="{$pkCPREFIX}CRHTTPS" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $pkfvCRHTTPS==1} checked="checked"{/if} name="{$pkCPREFIX}CRHTTPS" onchange="tabContentChanged(true);"/>
            <label for="{$pkCPREFIX}CRHTTPS" data-on="Yes" data-off="No" style="margin: 0px auto; float: none;"></label>
            <small>{l s='use HTTPS in proxy script when sending requests to Piwik' mod='piwikanalyticsjs'}</small>
        </label>
    </div>
    <label>
        <span>{l s='Timeout' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}PROXY_TIMEOUT' type='text' name='{$pkCPREFIX}PROXY_TIMEOUT' placeholder='5' value="{$pkfvPROXY_TIMEOUT}" onchange="tabContentChanged(true);"/>
        <small>{l s='the maximum time in seconds to wait for proxied request to piwik' mod='piwikanalyticsjs'}</small>
    </label>
    <label>
        <span>{l s='Proxy script' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}PROXY_SCRIPT' type='text' name='{$pkCPREFIX}PROXY_SCRIPT' placeholder='{$pkfvPROXY_SCRIPTPlaceholder}' value="{$pkfvPROXY_SCRIPT}" onchange="tabContentChanged(true);"/>
        <small>
            {l s='URL to proxy script. Example: www.example.com/pkproxy.php' mod='piwikanalyticsjs'}
            <a href="#{$pkCPREFIX}PROXY_SCRIPT" onclick="$('#{$pkCPREFIX}PROXY_SCRIPT').val('{$pkfvPROXY_SCRIPTBuildIn}');return false;">{l s='Use default' mod='piwikanalyticsjs'}</a>
        </small>
    </label>
    <hr/>
    <strong>{l s='Piwik Proxy Script Authorization? if piwik is installed behind HTTP Basic Authorization (Both password and username must be filled before the values will be used)' mod='piwikanalyticsjs'}</strong>
    <input type="hidden" name="pusername_changed" id="pusername_changed" value="0"/>
    <label>
        <span>{l s='Proxy Script Username' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}PAUTHUSR' type='text' name='{$pkCPREFIX}PAUTHUSR' placeholder='User-Name' value="{$pkfvPAUTHUSR}" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" onchange="if ($('#{$pkCPREFIX}PAUTHUSR').val() !== '{$pkfvPAUTHUSR}'){ldelim}
        $('#pusername_changed').val(1);
                    tabContentChanged(true);{rdelim} else{ldelim}
        $('#pusername_changed').val(0);{rdelim}"/>
        <small>{l s='this field along with password can be used if piwik installation is protected by HTTP Basic Authorization' mod='piwikanalyticsjs'}</small>
    </label>
    <input type="hidden" name="ppassword_changed" id="ppassword_changed" value="0"/>
    <label>
        <span>{l s='Proxy Script Password' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}PAUTHPWD' type='password' name='{$pkCPREFIX}PAUTHPWD' placeholder='password'{* value="{$pkfvPAUTHPWD}"*} autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" onchange="if ($('#{$pkCPREFIX}PAUTHPWD').val() !== '') {ldelim}
        $('#ppassword_changed').val(1);tabContentChanged(true);{rdelim}"/>
        <small>{l s='this field along with username can be used if piwik installation is protected by HTTP Basic Authorization' mod='piwikanalyticsjs'}</small>
    </label>
    <hr/>
    <input type='submit' class='button pkbutton bg-blue' value='{l s='Save' mod='piwikanalyticsjs'}' name='submitUpdatePiwikAnalyticsjsProxyScript' />
</form>