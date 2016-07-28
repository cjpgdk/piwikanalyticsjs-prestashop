<script type="text/javascript">
    var tab_content_changed = false;
    function tabContentChanged(value) {
        if (typeof value === 'undefined')
            return tab_content_changed;
        tab_content_changed = value;
    }
</script>
<fieldset id='fieldset_piwik_analytics' style="width: 700px; margin-left: auto; margin-right: auto;">
    <legend>
        <img alt='{l s='Piwik Analytics' mod='piwikanalyticsjs'}' src='{$piwik_module_dir}/logox22.png'>
        {l s='Piwik Analytics' mod='piwikanalyticsjs'}
    </legend>
    <div style="display: block; width: 100%; float: left;">
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
        <div class='float-right'>
            <ul class='tabs-pk not-tab'>
                <li>
                    <a href='{$config_wizard_link}' class='bg-blue link-not-tab' title='{l s='Click here to open piwik site lookup wizard' mod='piwikanalyticsjs'}'>
                        {l s='Configuration Wizard' mod='piwikanalyticsjs'}
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
    <div id='tabs-pk1' class='small-tab'>{include file=$tab_defaults_file}</div>
    <div id='tabs-pk2' class='small-tab'>{include file=$tab_proxyscript_file}</div>
    <div id='tabs-pk4' class='small-tab'>{include file=$tab_extra_file}</div>
    <div id='tabs-pk5' class='small-tab'>{include file=$tab_html_file}</div>
    <div id='tabs-pk6' class='small-tab'>{include file=$tab_cookies_file}</div>
    <div id='tabs-pk3' class='small-tab'>{include file=$tab_site_manager_file}</div>
</fieldset><br/>
