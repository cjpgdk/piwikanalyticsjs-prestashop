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
<script type="text/javascript">
    var tab_content_changed = false;
    function tabContentChanged(value) {
        if (typeof value === 'undefined')
            return tab_content_changed;
        tab_content_changed = value;
    }
</script>
<fieldset id='fieldset_piwik_analytics' style="width: 700px;margin-left: auto;margin-right: auto;">
    <legend>
        <img alt='{l s='Piwik Analytics' mod='piwikanalyticsjs'}' src='{$piwik_module_dir}/logox22.png'>
        {l s='Piwik Analytics' mod='piwikanalyticsjs'}
    </legend>
    <div style="display: block;width: 100%;float: left;min-height: 40px;">
        <div class='float-left'>
            <i>{l s='Piwik version' mod='piwikanalyticsjs'} {$piwikVersion}</i><br>
            {if $piwikSite}
                {l s='Current Piwik site' mod='piwikanalyticsjs'}<br>
                <table style="font-size: 12px ! important;">
                    <tr><td>{l s='Id' mod='piwikanalyticsjs'}</td><td>{$piwikSiteId}</td></tr>
                    <tr><td>{l s='Name' mod='piwikanalyticsjs'}</td><td>{$piwikSiteName}</td></tr>
                    <tr><td>{l s='URL' mod='piwikanalyticsjs'}</td><td>{$piwikMainUrl}</td></tr>
                    <tr><td>{l s='Excluded IPs' mod='piwikanalyticsjs'}</td><td>{$piwikExcludedIps}</td></tr>
                </table>
                <br>
            {/if}
        </div>
        <div class='float-right' style="max-width: 50%;">
            <ul class='tabs-pk not-tab'>
                <li>
                    <a onclick="return showLookupTokenForm();" class='bg-blue link-not-tab' href="#" title="{l s='Click to lookup your Piwik auth token' mod='piwikanalyticsjs'}">{l s='Lookup auth token' mod='piwikanalyticsjs'}</a>
                </li>
                <li>
                    <a href='{$config_wizard_link}' class='bg-blue link-not-tab' title='{l s='Click here to open piwik site lookup wizard' mod='piwikanalyticsjs'}'>
                        {l s='Configuration Wizard' mod='piwikanalyticsjs'}
                    </a>
                </li>
                <li>
                    <a href="#" onclick="return validateConfiguration();" class='link-not-tab' title='{l s='Validate the configuration and module installation' mod='piwikanalyticsjs'}'>
                        {l s='Validate installation' mod='piwikanalyticsjs'}
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <ul class='tabs-pk'>
        <li><a href='#tabs-pk1'>{l s='Defaults' mod='piwikanalyticsjs'}</a></li>
        <li><a href='#tabs-pk2'>{l s='Proxy script' mod='piwikanalyticsjs'}</a></li>
        <li><a href='#tabs-pk4'>{l s='Extra' mod='piwikanalyticsjs'}</a></li>
        <li><a href='#tabs-pk5'>{l s='HTML' mod='piwikanalyticsjs'}</a></li>
        <li><a href='#tabs-pk6'>{l s='Cookies' mod='piwikanalyticsjs'}</a></li>
        <li><a href='#tabs-pk3'>{l s='Site Manager' mod='piwikanalyticsjs'}</a></li>
    </ul>
    <div id='tabs-pk1'>{include file=$tab_defaults_file}</div>
    <div id='tabs-pk2'>{include file=$tab_proxyscript_file}</div>
    <div id='tabs-pk4'>{include file=$tab_extra_file}</div>
    <div id='tabs-pk5'>{include file=$tab_html_file}</div>
    <div id='tabs-pk6'>{include file=$tab_cookies_file}</div>
    <div id='tabs-pk3'>{include file=$tab_site_manager_file}</div>
</fieldset><br/>
