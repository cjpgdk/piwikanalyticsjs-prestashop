{*
* this files uses the Piwik async init function attached to the window object
* to extend the Piwik tracker you can use the following in your theme
* or you can simply override this file in your theme directory.
*  - themes/myTheme99/modules/piwikanalytics/views/hook/piwikAsync.tpl
*       (if that file exists it is used instead of this one.!)
* 
* Piwik tracker object
*
* window.piwikTracker
* - this object holds the current Piwik tracker.
*   to use this object from your theme do check the object is initialized first to avoid JavaScript errors
* 
*   /* track a search */
*   if (typeof window.piwikTracker != 'undefined' && window.piwikTracker !== null) {
*       /* okay the Piwik object is initialized */
*       window.piwikTracker.trackSiteSearch(keyword, category, resultsCount);
*   }
* ---
*
* you also have the option to create modules that will be called when attached to one of the following 4 hook names
* NOTE: Hooks are a new introduction to this module that was needed on a special project, so the placement
*       and the data available from the hooks may be completely wrong for you, in that case don't hesitate to
*       contact me with information on where and how you like the hooks to be implemented, so we can get a 
*       good and smooth integration with Piwik and PrestaShop, thanks
*
* - piwikTrackerStart      : appended after getTracker()
* - piwikTrackerEnd        : appended before trackPageView() and before trackSiteSearch()
* - piwikTrackerPageView   : appended before trackPageView()
* - piwikTrackerSiteSearch : appended before trackSiteSearch()
* 
* ---
* 
* lastly i also added a function check that can be set any where in your theme
* this function is called once the Piwik object is loaded.
*
* function: piwikTrackerLoaded();
* 
* To use this function simply place it any where in your theme
* 
* function piwikTrackerLoaded() {
*   alert("Piwik is now loaded and ready.");
*   /* now that we know the object is initialized we do all the theme required piwik stuff */
*   window.piwikTracker.setDocumentTitle(" My title override ");
* }
*
*
*}

<script type="text/javascript">
    (function () {
        var d = document, g = d.createElement("script"), s = d.getElementsByTagName("script")[0];
        g.type = "text/javascript";
        g.defer = true;
        g.async = true;
    {if $piwikUseProxy eq true}
        g.src = "{$piwikProtocol}{$piwikHost}";
    {else}
        g.src = "{$piwikProtocol}{$piwikHost}piwik.js";
    {/if}
        s.parentNode.insertBefore(g, s);
    })();
    window.piwikTracker = null;
    window.piwikAsyncInit = function () {
        try {
            var u = "{$piwikProtocol}{$piwikHost}";
    {if $piwikUseProxy eq true}
            window.piwikTracker = Piwik.getTracker(u, {$piwikIdSite});
    {else}
            window.piwikTracker = Piwik.getTracker(u + 'piwik.php', {$piwikIdSite});
    {/if}
    {$piwikHookTrackerStart}
    {if $piwikEnableJSErrorTracking eq true}
            window.piwikTracker.enableJSErrorTracking();
    {/if}
    {if $piwikEnableHeartBeatTimer eq true}
            window.piwikTracker.enableHeartBeatTimer({$piwikHeartBeatTimerDelay});
    {/if}
    {if $piwikDNT eq true}
            window.piwikTracker.setDoNotTrack(true);
    {/if}
    {if isset($piwikCookieDomain) && $piwikCookieDomain != ""}
            window.piwikTracker.setCookieDomain('{$piwikCookieDomain}');
    {/if}
    {if isset($piwikCookiePath) && $piwikCookiePath != "" && $piwikCookiePath != "/"}
            window.piwikTracker.setCookiePath({$piwikCookiePath});
    {/if}
    {if isset($piwikSetDomains) && $piwikSetDomains != ""}
            window.piwikTracker.setDomains({$piwikSetDomains});
    {/if}
    {if isset($piwikVisitorCookieTimeout) && $piwikVisitorCookieTimeout != ""}
            window.piwikTracker.setVisitorCookieTimeout({$piwikVisitorCookieTimeout});
    {/if}
    {if isset($piwikReferralCookieTimeout) && $piwikReferralCookieTimeout != ""}
            window.piwikTracker.setReferralCookieTimeout({$piwikReferralCookieTimeout});
    {/if}
    {if isset($piwikSessionCookieTimeout) && $piwikSessionCookieTimeout != ""}
            window.piwikTracker.setSessionCookieTimeout({$piwikSessionCookieTimeout});
    {/if}
            window.piwikTracker.enableLinkTracking(true);
    {if isset($piwikUserId)}
            window.piwikTracker.setUserId('{$piwikUserId}');
    {/if}
    {if isset($piwikProducts) && is_array($piwikProducts)}
        {foreach from=$piwikProducts item=piwikproduct}
            window.piwikTracker.setEcommerceView('{$piwikproduct.sku}', '{$piwikproduct.name|escape:'htmlall':'UTF-8'}', {$piwikproduct.category}, '{$piwikproduct.price|floatval}');
        {/foreach}
    {/if}
    {if isset($piwikCategory)}
            window.piwikTracker.setEcommerceView(false, false, '{$piwikCategory|escape:'htmlall':'UTF-8'}');
    {/if}
    {if $piwikIsCart eq true}
        {if is_array($piwikCartProducts)}
            {foreach from=$piwikCartProducts item=cartproduct}
            window.piwikTracker.addEcommerceItem('{$cartproduct.sku}', '{$cartproduct.name}', {$cartproduct.category}, '{$cartproduct.price}', '{$cartproduct.quantity}');
            {/foreach}
        {/if}
        {if isset($piwikCartTotal)}
            window.piwikTracker.trackEcommerceCartUpdate({$piwikCartTotal|floatval});
        {/if}
    {/if}
    {if $piwikIsOrder eq true}
        {if is_array($piwikOrderProducts)}
            {foreach from=$piwikOrderProducts item=orderproduct}
            window.piwikTracker.addEcommerceItem('{$orderproduct.sku}', '{$orderproduct.name}', {$orderproduct.category}, '{$orderproduct.price}', '{$orderproduct.quantity}');
            {/foreach}
        {/if}
            window.piwikTracker.trackEcommerceOrder("{$piwikOrderDetails.id}", '{$piwikOrderDetails.total}', '{$piwikOrderDetails.sub_total}', '{$piwikOrderDetails.tax}', '{$piwikOrderDetails.shipping}', '{$piwikOrderDetails.discount}');
    {/if}
    {if isset($piwikMaintenanceTitle)}
            window.piwikTracker.setDocumentTitle('{$piwikMaintenanceTitle}/' + document.title);
    {/if}
    {if isset($piwik404) && $piwik404 eq true}
            window.piwikTracker.setDocumentTitle('404/URL = ' + encodeURIComponent(document.location.pathname + document.location.search) + '/From = ' + encodeURIComponent(document.referrer));
    {/if}
    {$piwikHookTrackerEnd}
    {if isset($piwikIsSearch) && $piwikIsSearch eq true}
        {$piwikHookTrackerSiteSearch}
            window.piwikTracker.trackSiteSearch('{$piwikSearchWord}', false, '{$piwikSearchTotal}');
    {else}
        {$piwikHookTrackerPageView}
            window.piwikTracker.trackPageView();
    {/if}
            if (typeof piwikTrackerLoaded == 'function') {
                piwikTrackerLoaded();
            }
        } catch (err) {
        }
    };
</script>       
{if isset($piwikEXHTML)}
    {$piwikEXHTML}
{/if}