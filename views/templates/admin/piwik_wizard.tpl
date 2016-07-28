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
<fieldset id='fieldset_piwik_analytics'>
    {if $wizardStep == "1"}
        <legend><img alt='{l s='Piwik Analytics' mod='piwikanalyticsjs'}' src='{$piwik_module_dir}/logox22.png'>{l s='Piwik Analytics - Configuration Wizard [Step 1/2]' mod='piwikanalyticsjs'}</legend>
        <form action='' method='post' class='pkforms small' autocomplete="off" name="formPiwikAnalyticsjsWizard" id="formPiwikAnalyticsjsWizard">
            <input id="{$pkCPREFIX}STEP_WIZARD" type="hidden" value="{$wizardStep}" name="{$pkCPREFIX}STEP_WIZARD">
            <label>
                <span>{l s='Piwik Host' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <input id='{$pkCPREFIX}HOST_WIZARD' type='text' name='{$pkCPREFIX}HOST_WIZARD' placeholder='http://piwik.example.com/' value="{$pkfvHOST}"/>
                <small>{l s='The full url to your piwik installation, example: http://www.example.com/piwik/' mod='piwikanalyticsjs'}</small>
            </label>

            <label>
                <span>{l s='Piwik User name' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <input id='{$pkCPREFIX}USRNAME_WIZARD' type='text' name='{$pkCPREFIX}USRNAME_WIZARD' placeholder='user-name' value="" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/>
                <small>{l s='Enter your username for Piwik, we need this in order to fetch your api authentication token' mod='piwikanalyticsjs'}</small>
            </label>

            <label>
                <span>{l s='Piwik User password' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <input id='{$pkCPREFIX}USRPASSWD_WIZARD' type='password' name='{$pkCPREFIX}USRPASSWD_WIZARD' placeholder='password' value="" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/>
                <small>{l s='Enter your password for Piwik, we need this in order to fetch your api authentication token' mod='piwikanalyticsjs'}</small>
            </label>

            <label class="switch">
                <span>{l s='Save username and password' mod='piwikanalyticsjs'}</span>
                <input id="{$pkCPREFIX}SAVE_USRPWD_WIZARD" class="pka-toggle pka-toggle-yes-no" type="checkbox" name="{$pkCPREFIX}SAVE_USRPWD_WIZARD" />
                <label for="{$pkCPREFIX}SAVE_USRPWD_WIZARD" data-on="Yes" data-off="No" style="clear: both;"></label>
                <small>{l s='Whether or not to save the username and password, saving the username and password will enable quick(automatic) login to piwik from the integrated stats page' mod='piwikanalyticsjs'}</small>
            </label>

            <hr class="bd-blue"/>

            <strong style="text-align: center; width: 100%; float: left;">{l s='HTTP Basic Authorization' mod='piwikanalyticsjs'}</strong>

            <label>
                <span>{l s='HTTP Auth Username' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <input id='{$pkCPREFIX}PAUTHUSR_WIZARD' type='text' name='{$pkCPREFIX}PAUTHUSR_WIZARD' placeholder='user-name' value="" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/>
                <small>{l s='this field along with password can be used if piwik installation is protected by HTTP Basic Authorization' mod='piwikanalyticsjs'}</small>
            </label>

            <label>
                <span>{l s='HTTP Auth Password' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <input id='{$pkCPREFIX}PAUTHPWD_WIZARD' type='password' name='{$pkCPREFIX}PAUTHPWD_WIZARD' placeholder='password' value="" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/>
                <small>{l s='this field along with username can be used if piwik installation is protected by HTTP Basic Authorization' mod='piwikanalyticsjs'}</small>
            </label>

            <hr/>
            <input type='submit' class='button pkbutton bg-blue disable' value='{l s='Next' mod='piwikanalyticsjs'}' name='submitPiwikAnalyticsjsWizard' />
            <input type='button' class='button pkbutton bg-gray donotdisable' value='{l s='Cancel' mod='piwikanalyticsjs'}' name='btnPiwikAnalyticsjsWizardCancel' onclick="document.location = '{$pscurrentIndex|replace:'&pkwizard':''}';return false;" />
            <input type='button' class='button pkbutton bg-green float-right' value='{l s='Create new site' mod='piwikanalyticsjs'}' name='btnPiwikAnalyticsjsWizardNewSite' onclick="return createNewSiteFromStep1();" />
        </form>
    {else if $wizardStep == "2"}
        <legend><img alt='{l s='Piwik Analytics' mod='piwikanalyticsjs'}' src='{$piwik_module_dir}/logox22.png'>{l s='Piwik Analytics - Configuration Wizard [Step 2/2]' mod='piwikanalyticsjs'}</legend>
        <form action='' method='post' class='pkforms small' autocomplete="off" name="formPiwikAnalyticsjsWizard" id="formPiwikAnalyticsjsWizard">
            <input id="{$pkCPREFIX}STEP_WIZARD" type="hidden" value="{$wizardStep}" name="{$pkCPREFIX}STEP_WIZARD">
            <input type="hidden" name="{$pkCPREFIX}HOST_WIZARD" id="{$pkCPREFIX}HOST_WIZARD" value="{$pkfvHOST_WIZARD}" />
            <input type="hidden" name="{$pkCPREFIX}USRNAME_WIZARD" id="{$pkCPREFIX}USRNAME_WIZARD" value="{$pkfvUSRNAME_WIZARD}" />
            <input type="hidden" name="{$pkCPREFIX}USRPASSWD_WIZARD" id="{$pkCPREFIX}USRPASSWD_WIZARD" value="{$pkfvUSRPASSWD_WIZARD}" />
            <input type="hidden" name="{$pkCPREFIX}PAUTHUSR_WIZARD" id="{$pkCPREFIX}PAUTHUSR_WIZARD" value="{$pkfvPAUTHUSR_WIZARD}" />
            <input type="hidden" name="{$pkCPREFIX}PAUTHPWD_WIZARD" id="{$pkCPREFIX}PAUTHPWD_WIZARD" value="{$pkfvPAUTHPWD_WIZARD}" />
            <p><strong>{l s='Select a site to use, or click create new site' mod='piwikanalyticsjs'}</strong></p>
            {foreach from=$pkSites  key=pkSite_key item=pkSite_value}
                {if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
                    <a href="{$pscurrentIndexLnk}&pkwizard&usePiwikSite={$pkSite_value.idsite}" id="slectedpksid_{$pkSite_value.idsite}" class="button">
                        #{$pkSite_value.idsite} - {$pkSite_value.name} [<strong>{$pkSite_value.main_url}</strong>]
                    </a>
                {else}
                    <a href="{$pscurrentIndexLnk}&pkwizard&usePiwikSite={$pkSite_value.idsite}" id="slectedpksid_{$pkSite_value.idsite}" class="btn btn-default">
                        <i class="icon-chevron-right" ></i> #{$pkSite_value.idsite} - {$pkSite_value.name} [<strong>{$pkSite_value.main_url}</strong>]
                    </a>
                {/if}
            {/foreach}
            
            <hr/>
            <input type='button' class='button pkbutton bg-gray' value='{l s='Cancel' mod='piwikanalyticsjs'}' name='btnPiwikAnalyticsjsWizardCancel' onclick="document.location = '{$pscurrentIndex|replace:'&pkwizard':''}';return false;" />
            <input type='button' class='button pkbutton bg-green float-right' value='{l s='Create new site' mod='piwikanalyticsjs'}' name='btnPiwikAnalyticsjsWizardNewSite' onclick="return createNewSiteFromStep1();" />
        </form>
    {else if $wizardStep == "99"}
    
        <script type="text/javascript">
            $().ready(function () {
                $('#PKNewAddtionalUrls').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add URL' mod='piwikanalyticsjs'}'{rdelim});
                $('#PKNewSearchKeywordParameters').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add Parameter' mod='piwikanalyticsjs'}'{rdelim});
                $('#PKNewSearchCategoryParameters').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add Parameter' mod='piwikanalyticsjs'}'{rdelim});
                $('#PKNewExcludedIps').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add IP' mod='piwikanalyticsjs'}'{rdelim});
                $('#PKNewExcludedQueryParameters').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add Parameter' mod='piwikanalyticsjs'}'{rdelim});
                $('#PKNewExcludedUserAgents').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add User Agent' mod='piwikanalyticsjs'}'{rdelim});

                $('#formPiwikAnalyticsjsWizard').submit(function () {
                    $(this).find('#PKNewAddtionalUrls').val($('#PKNewAddtionalUrls').tagify('serialize'));
                    $(this).find('#PKNewSearchKeywordParameters').val($('#PKNewSearchKeywordParameters').tagify('serialize'));
                    $(this).find('#PKNewSearchCategoryParameters').val($('#PKNewSearchCategoryParameters').tagify('serialize'));
                    $(this).find('#PKNewExcludedIps').val($('#PKNewExcludedIps').tagify('serialize'));
                    $(this).find('#PKNewExcludedQueryParameters').val($('#PKNewExcludedQueryParameters').tagify('serialize'));
                    $(this).find('#PKNewExcludedUserAgents').val($('#PKNewExcludedUserAgents').tagify('serialize'));
                });
            });
        </script>
    
    
        <legend><img alt='{l s='Piwik Analytics' mod='piwikanalyticsjs'}' src='{$piwik_module_dir}/logox22.png'>{l s='Piwik Analytics - Create new site [Step 2/2]' mod='piwikanalyticsjs'}</legend>
        <form action='' method='post' class='pkforms small' autocomplete="off" name="formPiwikAnalyticsjsWizard">
            <input id="{$pkCPREFIX}STEP_WIZARD" type="hidden" value="2" name="{$pkCPREFIX}STEP_WIZARD">
            <input type="hidden" name="{$pkCPREFIX}HOST_WIZARD" id="{$pkCPREFIX}HOST_WIZARD" value="{$pkfvHOST_WIZARD}" />
            <input type="hidden" name="{$pkCPREFIX}USRNAME_WIZARD" id="{$pkCPREFIX}USRNAME_WIZARD" value="{$pkfvUSRNAME_WIZARD}" />
            <input type="hidden" name="{$pkCPREFIX}USRPASSWD_WIZARD" id="{$pkCPREFIX}USRPASSWD_WIZARD" value="{$pkfvUSRPASSWD_WIZARD}" />
            <input type="hidden" name="{$pkCPREFIX}PAUTHUSR_WIZARD" id="{$pkCPREFIX}PAUTHUSR_WIZARD" value="{$pkfvPAUTHUSR_WIZARD}" />
            <input type="hidden" name="{$pkCPREFIX}PAUTHPWD_WIZARD" id="{$pkCPREFIX}PAUTHPWD_WIZARD" value="{$pkfvPAUTHPWD_WIZARD}" />
        
            <label>
                <span>{l s='Piwik Site Name' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <input id='PKNewSiteName' type='text' name='PKNewSiteName' placeholder='My site' value="{$PKNewSiteName}"/>
                <small>{l s='Name of this site in Piwik' mod='piwikanalyticsjs'}</small>
            </label>
            
            <label>
                <span>{l s='Main Url' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <input id='PKNewMainUrl' type='text' name='PKNewMainUrl' placeholder='www.example.com' value="{$PKNewMainUrl}"/>
            </label>
            
            <label>
                <span>{l s='Addtional Urls' mod='piwikanalyticsjs'}</span>
                <input id='PKNewAddtionalUrls' type='text' class="tagify" name='PKNewAddtionalUrls' value="{$PKNewAddtionalUrls}"/>
            </label>
            
            <label class="switch">
                <span>{l s='Ecommerce' mod='piwikanalyticsjs'}</span>
                <input id="PKNewEcommerce" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $PKNewEcommerce==1} checked="checked"{/if} name="PKNewEcommerce"/>
                <label for="PKNewEcommerce" data-on="{l s='Yes' mod='piwikanalyticsjs'}" data-off="{l s='No' mod='piwikanalyticsjs'}" style="clear: both;"></label>
                <small>{l s='Is this site an ecommerce site?' mod='piwikanalyticsjs'}</small>
            </label>
            
            <label class="switch">
                <span>{l s='Site Search' mod='piwikanalyticsjs'}</span>
                <input id="PKNewSiteSearch" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $PKNewSiteSearch==1} checked="checked"{/if} name="PKNewSiteSearch"/>
                <label for="PKNewSiteSearch" data-on="{l s='Yes' mod='piwikanalyticsjs'}" data-off="{l s='No' mod='piwikanalyticsjs'}" style="clear: both;"></label>
                <small>{l s='Enable site search for this site?' mod='piwikanalyticsjs'}</small>
            </label>
            
            <label class="switch">
                <span>{l s='Keep URL Fragments' mod='piwikanalyticsjs'}</span>
                <input id="PKNewKeepURLFragments" class="pka-toggle pka-toggle-yes-no" type="checkbox" name="PKNewKeepURLFragments"/>
                <label for="PKNewKeepURLFragments" data-on="{l s='Yes' mod='piwikanalyticsjs'}" data-off="{l s='No' mod='piwikanalyticsjs'}" style="clear: both;"></label>
            </label>
            
            <label>
                <span>{l s='Addtional Urls' mod='piwikanalyticsjs'}</span>
                <input id='PKNewSearchKeywordParameters' type='text' class="tagify" name='PKNewSearchKeywordParameters' value="{$PKNewSearchKeywordParameters}"/>
                <small><strong>tag</strong> & <strong>search_query</strong> {l s='keyword parameters must be excluded to avoid normal page views to be interpreted as searches (the tracking code will see them and make the required postback to Piwik if it is a real search), if you are only using PrestaShop with this site setting this to empty, will be sufficient' mod='piwikanalyticsjs'}</small>
            </label>
            
            <label>
                <span>{l s='Search Category Parameters' mod='piwikanalyticsjs'}</span>
                <input id='PKNewSearchCategoryParameters' type='text' class="tagify" name='PKNewSearchCategoryParameters' value="{$PKNewSearchCategoryParameters}"/>
            </label>
            
            <label>
                <span>{l s='Excluded ip addresses, your current IP is:' mod='piwikanalyticsjs'} {$pkfvMyIPis}</span>
                <input id='PKNewExcludedIps' type='text' class="tagify" name='PKNewExcludedIps' value="{$PKNewExcludedIps}"/>
                <small>{l s='ip addresses excluded from tracking, separated by comma ","' mod='piwikanalyticsjs'}</small>
            </label>
            
            <label>
                <span>{l s='Excluded Query Parameters' mod='piwikanalyticsjs'}</span>
                <input id='PKNewExcludedQueryParameters' type='text' class="tagify" name='PKNewExcludedQueryParameters' value="{$PKNewExcludedQueryParameters}"/>
                <small>{l s='please read: http://piwik.org/faq/how-to/faq_81/' mod='piwikanalyticsjs'}</small>
            </label>

            <label>
                <span>{l s='Timezone' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <select name="PKNewTimezone" id="PKNewTimezone">
                    <option value="0"{if $PKNewTimezone == "0"} selected="selected"{/if}>{l s='Choose Timezone' mod='piwikanalyticsjs'}</option>
                    {foreach from=$pkfvTimezoneList  key=timezoneList_key item=timezoneList_value}
                        <optgroup  label="{$timezoneList_value.name}">
                            {foreach from=$timezoneList_value.query  key=timezoneList_query_key item=timezoneList_query_value}
                                <option value="{$timezoneList_query_value.tzId}"{if $PKNewTimezone == $timezoneList_query_value.tzId} selected="selected"{/if}>{$timezoneList_query_value.tzName}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
                <small>{l s='The timezone for this site' mod='piwikanalyticsjs'} {$pkfvCURRENCY_DEFAULT}</small>
            </label>

            <label>
                <span>{l s='Currency' mod='piwikanalyticsjs'} <sup>*</sup></span>
                <select name="PKNewCurrency" id="PKNewCurrency" >
                    <option value="0"{if $PKNewCurrency == "0"} selected="selected"{/if}>{l s='Choose currency' mod='piwikanalyticsjs'}</option>
                    {foreach from=$pkfvCurrencies  key=currency_key item=currency_value}
                    <option value="{$currency_value.iso_code}"{if $PKNewCurrency == $currency_value.iso_code} selected="selected"{/if}>{$currency_value.name}</option>
                    {/foreach}
                </select>
                <small>{l s='The currency for this site, only currencies install in your shop is listed.' mod='piwikanalyticsjs'}</small>
            </label>
            
            <label>
                <span>{l s='Excluded User Agents' mod='piwikanalyticsjs'}</span>
                <input id='PKNewExcludedUserAgents' type='text' class="tagify" name='PKNewExcludedUserAgents'/>
                <small>{l s='please read: http://piwik.org/faq/how-to/faq_17483/' mod='piwikanalyticsjs'}</small>
            </label>
    
            <input type='submit' class='button pkbutton bg-blue' value='{l s='Save' mod='piwikanalyticsjs'}' name='PKNewSiteSubmit' />
            <input type='button' class='button pkbutton bg-gray' value='{l s='Cancel' mod='piwikanalyticsjs'}' name='btnPiwikAnalyticsjsWizardCancel' onclick="document.location = '{$pscurrentIndexLnk}';return false;" />
    
        </form>
    {/if}
</fieldset><br/>