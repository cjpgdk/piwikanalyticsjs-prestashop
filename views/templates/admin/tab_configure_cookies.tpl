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
<form action='#tabs-pk6' method='post' class='pkforms' autocomplete="off" name="formUpdatePiwikAnalyticsjsCookies">
    
    <label>
        <span>{l s='Track visitors across subdomains' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}COOKIE_DOMAIN' type='text' name='{$pkCPREFIX}COOKIE_DOMAIN' placeholder='.example.com' value="{$pkfvCOOKIE_DOMAIN}" onchange="tabContentChanged(true);"/>
        <small>{l s='The default is the document domain; if your web site can be visited at both www.example.com and example.com, you would use: "*.example.com" OR ".example.com" without quotes, Leave empty to exclude this from the tracking code' mod='piwikanalyticsjs'}</small>
    </label>
    
    <label>
        <span>{l s='Cookie name prefix' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}COOKIEPREFIX' type='text' name='{$pkCPREFIX}COOKIEPREFIX' placeholder='_pk_' value="{$pkfvCOOKIEPREFIX}" onchange="tabContentChanged(true);"/>
        <small>{l s='Leave empty to use Piwik default' mod='piwikanalyticsjs'}</small>
    </label>
    
    <label>
        <span>{l s='Cookie path' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}COOKIEPATH' type='text' name='{$pkCPREFIX}COOKIEPATH' placeholder='/' value="{$pkfvCOOKIEPATH}" onchange="tabContentChanged(true);"/>
        <small>{l s='Leave empty to use Piwik default' mod='piwikanalyticsjs'}</small>
    </label>
    
    <label>
        <span>{l s='Piwik Session Cookie timeout' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}SESSION_TIMEOUT' type='text' name='{$pkCPREFIX}SESSION_TIMEOUT' placeholder='30' value="{$pkfvSESSION_TIMEOUT}" onchange="tabContentChanged(true);"/>
        <small>{l s='Piwik Session Cookie timeout, the default is 30 minutes, this value must be set in minutes' mod='piwikanalyticsjs'}</small>
    </label>
    
    <label>
        <span>{l s='Piwik Visitor Cookie timeout' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}PCOOKIE_TIMEOUT' type='text' name='{$pkCPREFIX}COOKIE_TIMEOUT' placeholder='569777' value="{$pkfvCOOKIE_TIMEOUT}" onchange="tabContentChanged(true);"/>
        <small>{l s='Piwik Visitor Cookie timeout, the default is 13 months (569777 minutes), this value must be set in minutes' mod='piwikanalyticsjs'}</small>
    </label>
    
    <label>
        <span>{l s='Piwik Referral Cookie timeout' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}RCOOKIE_TIMEOUT' type='text' name='{$pkCPREFIX}RCOOKIE_TIMEOUT' placeholder='262974' value="{$pkfvRCOOKIE_TIMEOUT}" onchange="tabContentChanged(true);"/>
        <small>{l s='Piwik Referral Cookie timeout, the default is 6 months (262974 minutes), this value must be set in minutes' mod='piwikanalyticsjs'}</small>
    </label>
    <hr/>
    <input type='submit' class='button pkbutton bg-blue' value='{l s='Save' mod='piwikanalyticsjs'}' name='submitUpdatePiwikAnalyticsjsCookies' />
</form>