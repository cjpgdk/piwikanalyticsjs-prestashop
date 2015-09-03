<script type="text/javascript">
    (function () {
        var d = document, g = d.createElement("script"), s = d.getElementsByTagName("script")[0];
        g.type = "text/javascript";
        g.defer = true;
        g.async = true;
    {if $useProxy eq true}
        g.src = "{$protocol}{$piwikHost}";
    {else}
        g.src = "{$protocol}{$piwikHost}piwik.js";
    {/if}
        s.parentNode.insertBefore(g, s);
    })();
    window.piwikTracker = null;
    window.piwikAsyncInit = function () {
        try {
            var u = "{$protocol}{$piwikHost}";
    {if $useProxy eq true}
            window.piwikTracker = Piwik.getTracker(u, {$idSite});
    {else}
            window.piwikTracker = Piwik.getTracker(u + 'piwik.php', {$idSite});
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
            window.piwikTracker.enableLinkTracking();

    {if isset($userId)}
            window.piwikTracker.setUserId('{$userId}');
    {/if}

    {if $isOrder eq true}

    {/if}
            window.piwikTracker.trackPageView();
        } catch (err) {
        }
    };
</script>