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
 *
 *
 **************************************
 * For developers
 **************************************
 -- when piwik is loaded
 if(window.isPiwikLoaded)
    window.piwikLoaded(window.piwikTracker);
 --  -- 
so in your theme use
 --  -- 
window.piwikLoaded = function(tracker){
    // do your stuff, piwik object is 
    // tracker or window.piwikTracker
}
 --  -- 
or (depends on how you define your custom scripts)
 --  -- 
function myCustomPiwikLoaded(){
     if(window.isPiwikLoaded || typeof Piwik === 'object' || typeof Piwik === 'Object'){
        if(typeof window.piwikTracker === 'undefined' || 
            (typeof window.piwikTracker !== 'object' && typeof window.piwikTracker !== 'Object')
        ) {
            window.isPiwikLoaded = true;
            window.piwikTracker = Piwik.getAsyncTracker();
        }
        // do your stuff 
        window.piwikTracker.trackEvent(category, action, [name], [value]);
        
     } else{
        setTimeout(myCustomPiwikLoaded, 600);
     }
}
setTimeout(myCustomPiwikLoaded, 600);
*}
{*

setHeartBeatTimer( minimumVisitLength, heartBeatDelay )
    - records how long the page has been viewed if the minimumVisitLength (in seconds) is attained;
      the heartBeatDelay determines how frequently to update the server
enableHeartBeatTimer( delayInSeconds )

?? allow custom _paq to be created from admin

?? setRequestMethod to POST when using builtin proxy
?? setRequestContentType( contentType ) - Set request Content-Type header value. Applicable when "POST" request method is used via setRequestMethod.

*}
<script type="text/javascript">
    var u=(("https:" == document.location.protocol) ? "https://{$PIWIK_HOST}" : "http://{$PIWIK_HOST}");
    var _paq = _paq || [];
    _paq.push(["setSiteId",{$PIWIK_SITEID}]);
    {if $PIWIK_USE_PROXY eq true} _paq.push(['setTrackerUrl',u]);{else} _paq.push(['setTrackerUrl', u+'piwik.php']);{/if}
    {if $PIWIK_DNT eq true} _paq.push(["setDoNotTrack", true]);{/if}
    {if $PIWIK_DHashTag eq true} _paq.push(["discardHashTag", true]);{/if}
    {* left out since this requires Piwik to be installed in the same domain as your shop
    if isset($PIWIK_REQUEST_METHOD) && $PIWIK_REQUEST_METHOD eq true}
        _paq.push(['setRequestMethod', 'POST']);
    {/if
    *}
    {if isset($PIWIK_COOKIE_DOMAIN) && $PIWIK_COOKIE_DOMAIN eq true} _paq.push(['setCookieDomain', '{$PIWIK_COOKIE_DOMAIN}']);{/if}
    {if isset($PIWIK_COOKIEPREFIX) && $PIWIK_COOKIEPREFIX eq true} _paq.push(['setCookieNamePrefix', '{$PIWIK_COOKIEPREFIX}']);{/if}
    {if isset($PIWIK_COOKIEPATH) && $PIWIK_COOKIEPATH eq true} _paq.push(['setCookiePath', '{$PIWIK_COOKIEPATH}']);{/if}
    {if isset($PIWIK_SET_DOMAINS) && $PIWIK_SET_DOMAINS eq true} _paq.push(['setDomains', {$PIWIK_SET_DOMAINS}]);{/if}
    {if isset($PIWIK_COOKIE_TIMEOUT)} _paq.push(['setVisitorCookieTimeout', '{$PIWIK_COOKIE_TIMEOUT|intval}']);{/if}
    {if isset($PIWIK_SESSION_TIMEOUT)} _paq.push(['setSessionCookieTimeout', '{$PIWIK_SESSION_TIMEOUT|intval}']);{/if}
    {if isset($PIWIK_RCOOKIE_TIMEOUT)} _paq.push(['setReferralCookieTimeout', '{$PIWIK_RCOOKIE_TIMEOUT|intval}']);{/if}
    {if $PIWIK_LINKTRACK eq true} _paq.push(['enableLinkTracking']);{/if}
    {if isset($PIWIK_LINKClS) && $PIWIK_LINKClS eq true} _paq.push(['setLinkClasses', {$PIWIK_LINKClS}]);{/if}
    {if isset($PIWIK_LINKClSIGNORE) && $PIWIK_LINKClSIGNORE eq true} _paq.push(['setIgnoreClasses', {$PIWIK_LINKClSIGNORE}]);{/if}
    {if isset($PIWIK_LINKTTIME) && $PIWIK_LINKTTIME eq true} _paq.push(['setLinkTrackingTimer', {$PIWIK_LINKTTIME|intval}]);{/if}
    {if isset($PIWIK_UUID) && version_compare($PIWIK_VER|floatval,'2.7.0','>=')} _paq.push(['setUserId', '{$PIWIK_UUID}']);{/if}
    {if $PIWIK_APTURL eq true} _paq.push(['setApiUrl', (("https:" == document.location.protocol) ? "https://{$PIWIK_HOSTAPI}" : "http://{$PIWIK_HOSTAPI}")]);{/if}
    {if isset($PIWIK_PRODUCTS) && is_array($PIWIK_PRODUCTS)}
        {foreach from=$PIWIK_PRODUCTS item=piwikproduct}
            _paq.push(['setEcommerceView', '{$piwikproduct.SKU}', '{$piwikproduct.NAME|replace:"'":"\'":'UTF-8'}', {$piwikproduct.CATEGORY}, '{$piwikproduct.PRICE|floatval}']);
        {/foreach}
    {/if}
    {if isset($piwik_category) && is_array($piwik_category)}
            _paq.push(['setEcommerceView', false, false, '{$piwik_category.NAME|replace:"'":"\'":'UTF-8'}']);
    {/if}
    {if $PIWIK_CART eq true}
        {if is_array($PIWIK_CART_PRODUCTS)}
            {foreach from=$PIWIK_CART_PRODUCTS item=_product}
                _paq.push(['addEcommerceItem', '{$_product.SKU}', '{$_product.NAME|replace:"'":"\'":'UTF-8'}', {$_product.CATEGORY}, '{$_product.PRICE|floatval}', '{$_product.QUANTITY}']);
            {/foreach}
        {/if}
        {if isset($PIWIK_CART_TOTAL)} _paq.push(['trackEcommerceCartUpdate', {$PIWIK_CART_TOTAL|floatval}]);{/if}
    {/if}
    {if $PIWIK_ORDER eq true}
        {if is_array($PIWIK_ORDER_PRODUCTS)}
            {foreach from=$PIWIK_ORDER_PRODUCTS item=_product}
                _paq.push(['addEcommerceItem', '{$_product.SKU}', '{$_product.NAME|replace:"'":"\'":'UTF-8'}', {$_product.CATEGORY}, '{$_product.PRICE|floatval}', '{$_product.QUANTITY}']);
            {/foreach}
        {/if}
        _paq.push(['trackEcommerceOrder','{$PIWIK_ORDER_DETAILS.order_id}', '{$PIWIK_ORDER_DETAILS.order_total}', '{$PIWIK_ORDER_DETAILS.order_sub_total}', '{$PIWIK_ORDER_DETAILS.order_tax}', '{$PIWIK_ORDER_DETAILS.order_shipping}', '{$PIWIK_ORDER_DETAILS.order_discount}']);
    {/if}
    {if isset($PIWIK_SITE_SEARCH) && !isset($PIWIK_PRODUCTS)}{$PIWIK_SITE_SEARCH}{else}
        {if isset($PK404) && $PK404 eq true}
        _paq.push(['setDocumentTitle',  '404/URL = ' +  encodeURIComponent(document.location.pathname+document.location.search) + '/From = ' + encodeURIComponent(document.referrer)]);
        {/if}
        _paq.push(['trackPageView']);
    {/if}
    {literal}
        (function() {var d = document, g = d.createElement("script"), s = d.getElementsByTagName("script")[0];g.type = "text/javascript";g.defer = true;g.async = true;g.src = {/literal}{if $PIWIK_USE_PROXY eq true}{literal}u{/literal}{else}{literal}u+'piwik.js'{/literal}{/if}{literal};s.parentNode.insertBefore(g, s);})();
        window.isPiwikLoaded=false;var _piwikLoaded1469750422Count = 0;setTimeout(piwikLoaded1469750422, 600);
        if(typeof window.piwikLoaded !== 'function') {window.piwikLoaded = function(tracker){}}
        function piwikLoaded1469750422 (){
             if ((typeof Piwik === 'object' || typeof Piwik === 'Object') && !window.isPiwikLoaded) {window.piwikTracker = Piwik.getAsyncTracker();window.isPiwikLoaded = true;}
             else {_piwikLoaded1469750422Count++;if(_piwikLoaded1469750422Count < 5)setTimeout(piwikLoaded1469750422, 500);};if(window.isPiwikLoaded)window.piwikLoaded(window.piwikTracker);}
    {/literal}
</script>
{if isset($PIWIK_EXHTML)}
    {$PIWIK_EXHTML}
{/if}