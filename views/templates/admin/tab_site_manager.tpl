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
<form action='#tabs-pk3' method='post' class='pkforms' name="formUpdatePiwikAnalyticsjsSiteManager" id="formUpdatePiwikAnalyticsjsSiteManager">
    <script type="text/javascript">
        $(document).ready(function () {
            $('#PKAdminSearchKeywordParameters').tagify({
                delimiters: [13, 44],
                addTagPrompt: '{l s='Add Parameter' mod='piwikanalyticsjs'}'
            });
            $('#PKAdminSearchCategoryParameters').tagify({
                delimiters: [13, 44],
                addTagPrompt: '{l s='Add Parameter' mod='piwikanalyticsjs'}'
            });
            $('#PKAdminExcludedIps').tagify({
                delimiters: [13, 44],
                addTagPrompt: '{l s='Add IP' mod='piwikanalyticsjs'}'
            });
            $('#PKAdminExcludedQueryParameters').tagify({
                delimiters: [13, 44],
                addTagPrompt: '{l s='Add Parameter' mod='piwikanalyticsjs'}'
            });
            $('#formUpdatePiwikAnalyticsjsSiteManager').submit(function () {
                $('#PKAdminExcludedQueryParameters').val($('#PKAdminExcludedQueryParameters').tagify('serialize'));
                $('#PKAdminSearchCategoryParameters').val($('#PKAdminSearchCategoryParameters').tagify('serialize'));
                $('#PKAdminSearchKeywordParameters').val($('#PKAdminSearchKeywordParameters').tagify('serialize'));
                $('#PKAdminExcludedIps').val($('#PKAdminExcludedIps').tagify('serialize'));
            });
        });
    </script>
    <input type="hidden" name="PKAdminIdSite" id="PKAdminIdSite" value="{$piwikSiteId}" />
    <input type="hidden" name="PKAdminGroup" id="PKAdminGroup" value="{$PKAdminGroup}" />
    <input type="hidden" name="PKAdminStartDate" id="PKAdminStartDate" value="{$PKAdminStartDate}" />
    <input type="hidden" name="PKAdminSiteUrls" id="PKAdminSiteUrls" value="{$PKAdminSiteUrls}" />
    <input type="hidden" name="PKAdminSiteType" id="PKAdminSiteType" value="{$PKAdminSiteType}" />

    <label>
        <span>{l s='In this section you can modify your settings in piwik just so you don\'t have to login to Piwik to do this' mod='piwikanalyticsjs'}</span>
    </label>

    <label>
        <span>{l s='Piwik Site Name' mod='piwikanalyticsjs'}</span>
        <input id='PKAdminSiteName' type='text' name='PKAdminSiteName' {* placeholder='{$PKAdminSiteNamePlaceholder}' *} value="{$PKAdminSiteName}" onchange="tabContentChanged(true);"/>
        <small>{l s='Name of this site in Piwik' mod='piwikanalyticsjs'}</small>
    </label>
    {*
    * <label>
    *     <span>{l s='Site urls' mod='piwikanalyticsjs'}</span>
    *     <input id='PKAdminSiteUrls' type='text' name='PKAdminSiteUrls' placeholder='{$PKAdminSiteUrlsPlaceholder}' value="{$PKAdminSiteUrls}" onchange="tabContentChanged(true);"/>
    *     <small></small>
    * </label>
    *}
    <div style="float: left; display: block; width: 100%; margin-bottom: 8px;">
        <label class="switch" style="max-width: 150px; text-align: center; float: left ! important;">
            <span style="margin: 0px auto; float: none;">{l s='Ecommerce' mod='piwikanalyticsjs'}</span>
            <input id="PKAdminEcommerce" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $PKAdminEcommerce==1} checked="checked"{/if} name="PKAdminEcommerce" onchange="tabContentChanged(true);"/>
            <label for="PKAdminEcommerce" data-on="{l s='Yes' mod='piwikanalyticsjs'}" data-off="{l s='No' mod='piwikanalyticsjs'}" style="margin: 0px auto; float: none;"></label>
            <small>{l s='Is this site an ecommerce site?' mod='piwikanalyticsjs'}</small>
        </label>
        <label class="switch" style="max-width: 150px; text-align: center; float: left ! important;">
            <span style="margin: 0px auto; float: none;">{l s='Site Search' mod='piwikanalyticsjs'}</span>
            <input id="PKAdminSiteSearch" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $PKAdminSiteSearch==1} checked="checked"{/if} name="PKAdminSiteSearch" onchange="tabContentChanged(true);"/>
            <label for="PKAdminSiteSearch" data-on="{l s='Yes' mod='piwikanalyticsjs'}" data-off="{l s='No' mod='piwikanalyticsjs'}" style="margin: 0px auto; float: none;"></label>
            <small>{l s='Track searches on this site' mod='piwikanalyticsjs'}</small>
        </label>
        <label class="switch" style="max-width: 150px; text-align: center; float: left ! important;">
            <span style="margin: 0px auto; float: none;">{l s='Keep URL Fragments' mod='piwikanalyticsjs'}</span>
            <input id="PKAdminKeepURLFragments" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $PKAdminKeepURLFragments==1} checked="checked"{/if} name="PKAdminKeepURLFragments" onchange="tabContentChanged(true);"/>
            <label for="PKAdminKeepURLFragments" data-on="{l s='Yes' mod='piwikanalyticsjs'}" data-off="{l s='No' mod='piwikanalyticsjs'}" style="margin: 0px auto; float: none;"></label>
            <small></small>
        </label>
    </div>
    <label>
        <span>{l s='Search Keyword Parameters' mod='piwikanalyticsjs'}</span>
        <input type="text" class="tagify" value="{$PKAdminSearchKeywordParameters}" id="PKAdminSearchKeywordParameters" name="PKAdminSearchKeywordParameters"/>
        <small>
            {l s='the following keyword parameters must be excluded to avoid normal page views to be interpreted as searches (the tracking code will see them and make the required postback to Piwik if it is a real search), if you are only using PrestaShop with this site setting this to empty, will be sufficient' mod='piwikanalyticsjs'}<br>
            <strong>tag</strong> {l s='and' mod='piwikanalyticsjs'} <strong>search_query</strong>
        </small>
    </label>
    <label>
        <span>{l s='Search Category Parameters' mod='piwikanalyticsjs'}</span>
        <input type="text" class="tagify" value="{$PKAdminSearchCategoryParameters}" id="PKAdminSearchCategoryParameters" name="PKAdminSearchCategoryParameters"/>
        <small></small>
    </label>
    <label>
        <span>{l s='Excluded ip addresses' mod='piwikanalyticsjs'}</span>
        <input type="text" class="tagify" value="{$PKAdminExcludedIps}" id="PKAdminExcludedIps" name="PKAdminExcludedIps"/>
        <small>{l s='ip addresses excluded from tracking, separated by comma ","' mod='piwikanalyticsjs'}</small>
    </label>
    <label>
        <span>{l s='Excluded Query Parameters' mod='piwikanalyticsjs'}</span>
        <input type="text" class="tagify" value="{$PKAdminExcludedQueryParameters}" id="PKAdminExcludedQueryParameters" name="PKAdminExcludedQueryParameters"/>
        <small>{l s='please read: http://piwik.org/faq/how-to/faq_81/' mod='piwikanalyticsjs'}</small>
    </label>
    <label>
        <span>{l s='Timezone' mod='piwikanalyticsjs'}</span>
        <select name="PKAdminTimezone" id="PKAdminTimezone" onchange="tabContentChanged(true);" >
            <option value="0"{if $PKAdminTimezone == "0"} selected="selected"{/if}>{l s='Choose Timezone' mod='piwikanalyticsjs'}</option>
            {foreach from=$pktimezones  key=timezone_key item=timezone_value}
                <optgroup label="{$timezone_value.name}">
                    {foreach from=$timezone_value.query key=timezonequery_key item=timezonequery_value}
                        <option value="{$timezonequery_value.tzId}"{if $PKAdminTimezone == $timezonequery_value.tzId} selected="selected"{/if}>{$timezonequery_value.tzName}</option>
                    {/foreach}
                </optgroup>
            {/foreach}
        </select>
        <small>{l s='Based on your settings in Piwik your default timezone is' mod='piwikanalyticsjs'} {$PKAdminTimezone}</small>
    </label>
    <label>
        <span>{l s='Currency' mod='piwikanalyticsjs'}</span>
        <select name="PKAdminCurrency" id="PKAdminCurrency" onchange="tabContentChanged(true);" >
            <option value="0"{if $PKAdminCurrency == "0"} selected="selected"{/if}>{l s='Choose currency' mod='piwikanalyticsjs'}</option>
            {foreach from=$pkfvCurrencies  key=currency_key item=currency_value}
                <option value="{$currency_value.iso_code}"{if $PKAdminCurrency == $currency_value.iso_code} selected="selected"{/if}>{$currency_value.name}</option>
            {/foreach}
        </select>
        <small>{l s='Based on your settings in Piwik your default currency is' mod='piwikanalyticsjs'} {$PKAdminCurrency}</small>
    </label>
    {*
    * <label>
    *     <span>{l s='Website group' mod='piwikanalyticsjs'}</span>
    *     <input type="text" class="tagify " value="{$PKAdminGroup}" id="PKAdminGroup" name="PKAdminGroup" onchange="tabContentChanged(true);"/>
    *     <small>{l s='Requires plugin "WebsiteGroups" before it can be used from within Piwik' mod='piwikanalyticsjs'}</small>
    * </label>
    * <label>
    *     <span>{l s='Website start date' mod='piwikanalyticsjs'}</span>
    *     <input type="text" class="tagify " value="{$PKAdminStartDate}" id="PKAdminStartDate" name="PKAdminStartDate" onchange="tabContentChanged(true);"/>
    *     <small></small>
    * </label>
    *}
    <label>
        <span>{l s='Excluded User Agents' mod='piwikanalyticsjs'}</span>
        <textarea rows="10" cols="50" id="PKAdminExcludedUserAgents" name="PKAdminExcludedUserAgents" onchange="tabContentChanged(true);">{$pkfvEXHTML}</textarea>
        <small>{l s='please read: http://piwik.org/faq/how-to/faq_17483/, NOTE* this setting requires that website specific user agent exclusion is enabled in piwik' mod='piwikanalyticsjs'}<br>
            {l s='If the visitor\'s user agent string contains any of the strings you specify, the visitor will be excluded from Piwik.' mod='piwikanalyticsjs'}</small>
    </label>
    {*
    * <label>
    *     <span>{l s='Site Type' mod='piwikanalyticsjs'}</span>
    *     <input type="text" class="tagify " value="{$PKAdminSiteType}" id="PKAdminSiteType" name="PKAdminSiteType" onchange="tabContentChanged(true);"/>
    *     <small></small>
    * </label>
    *}
    <hr/>
    <input type='submit' class='button pkbutton bg-blue' value='{l s='Save' mod='piwikanalyticsjs'}' name='submitUpdatePiwikAnalyticsjsSiteManager' />
</form>