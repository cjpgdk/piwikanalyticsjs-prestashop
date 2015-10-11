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
            window.piwikTracker.enableJSErrorTracking();
    {hook h="piwikTrackerStart"}
    {if $piwikDNT eq true}
            window.piwikTracker.setDoNotTrack(true);
    {/if}
    {if isset($piwikCookieDomain) && $piwikCookieDomain != ""}
            window.piwikTracker.setCookieDomain('{$piwikCookieDomain}');
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
    {hook h="piwikTrackerEnd"}
    {if isset($piwikIsSearch) && $piwikIsSearch eq true}
        {hook h="piwikTrackerSiteSearch"}
            window.piwikTracker.trackSiteSearch('{$piwikSearchWord}', false, '{$piwikSearchTotal}');
    {else}
        {hook h="piwikTrackerPageView"}
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