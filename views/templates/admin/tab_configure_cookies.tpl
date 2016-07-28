<form action='#tabs-pk6' method='post' class='pkforms' autocomplete="off" name="formUpdatePiwikAnalyticsjsCookies">
    
    <label>
        <span>{l s='Track visitors across subdomains' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}COOKIE_DOMAIN' type='text' name='{$pkCPREFIX}COOKIE_DOMAIN' placeholder='.example.com' value="{$pkfvCOOKIE_DOMAIN}" onchange="tabContentChanged(true);"/>
        <small>{l s='The default is the document domain; if your web site can be visited at both www.example.com and example.com, you would use: "*.example.com" OR ".example.com" without quotes, Leave empty to exclude this from the tracking code' mod='piwikanalyticsjs'}</small>
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