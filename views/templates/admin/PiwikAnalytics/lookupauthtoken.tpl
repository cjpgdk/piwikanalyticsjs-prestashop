{if isset($piwikToken)}
    <h2>{l s='Lookup Piwik authentication token' mod='piwikanalyticsjs'}</h2>
    {l s='Token:' mod='piwikanalyticsjs'} <strong>{$piwikToken}</strong><br>
    {l s='your settings has been updated with the above token and settings from previous step' mod='piwikanalyticsjs'}
{else}
<div id="formUpdatePiwikAnalyticsjsLookupToken-wrapper">
    <form name="formUpdatePiwikAnalyticsjsLookupToken" id="formUpdatePiwikAnalyticsjsLookupToken" autocomplete="off" class="pkforms" method="post" action="?sdfg" style="max-width: 500px;">
        <h2>{l s='Lookup Piwik authentication token' mod='piwikanalyticsjs'}</h2>
        <label>
            <span>{l s='Piwik Host' mod='piwikanalyticsjs'} <sup>*</sup></span>
            <input id='PKLOOKUPTOKENHOST' type='text' name='PKLOOKUPTOKENHOST' placeholder='analytics.example.com/' value="{$piwik_host}"/>
            <small>{l s='The host where piwik is installed, Example: www.example.com/piwik/ (without protocol and with / at the end!)' mod='piwikanalyticsjs'}</small>
        </label>
        <label>
            <span>{l s='Piwik User name' mod='piwikanalyticsjs'}</span>
            <input id='PKLOOKUPTOKENUSRNAME' type='text' name='PKLOOKUPTOKENUSRNAME' placeholder='user-name' value="{$piwik_user}" autocomplete="off"/>
            <small>{l s='You can store your Username for Piwik here to make it easy to open piwik interface from your stats page with automatic login'}</small>
        </label>
        <label>
            <span>{l s='Piwik User password' mod='piwikanalyticsjs'}</span>
            <input id='PKLOOKUPTOKENUSRPASSWD' type='password' name='PKLOOKUPTOKENUSRPASSWD' placeholder='password' value="{$piwik_passwd}" autocomplete="off"/>
            <small>{l s='You can store your Password for Piwik here to make it easy to open piwik interface from your stats page with automatic login'}</small>
        </label>
        <label class="switch">
            <span>{l s='Save username and password' mod='piwikanalyticsjs'}</span>
            <input id="PKLOOKUPTOKENSAVEUSRPWD" class="pka-toggle pka-toggle-yes-no" type="checkbox" name="PKLOOKUPTOKENSAVEUSRPWD" />
            <label for="PKLOOKUPTOKENSAVEUSRPWD" data-on="Yes" data-off="No" style="clear: both;"></label>
            <small>{l s='Whether or not to save the username and password, saving the username and password will enable quick(automatic) login to piwik from the integrated stats page' mod='piwikanalyticsjs'}</small>
        </label>
        <hr class="bd-blue"/>
        <strong style="text-align: center; width: 100%; float: left;">{l s='HTTP Basic Authorization' mod='piwikanalyticsjs'}</strong>
        <label>
            <span>{l s='HTTP Auth Username' mod='piwikanalyticsjs'} <sup>*</sup></span>
            <input id='PKLOOKUPTOKENPAUTHUSR' type='text' name='PKLOOKUPTOKENPAUTHUSR' placeholder='user-name' value="{$piwik_auser}" autocomplete="off"/>
            <small>{l s='this field along with password can be used if piwik installation is protected by HTTP Basic Authorization' mod='piwikanalyticsjs'}</small>
        </label>
        <label>
            <span>{l s='HTTP Auth Password' mod='piwikanalyticsjs'} <sup>*</sup></span>
            <input id='PKLOOKUPTOKENPAUTHPWD' type='password' name='PKLOOKUPTOKENPAUTHPWD' placeholder='password' value="{$piwik_apasswd}" autocomplete="off"/>
            <small>{l s='this field along with username can be used if piwik installation is protected by HTTP Basic Authorization' mod='piwikanalyticsjs'}</small>
        </label>
        <hr class="bd-blue"/>
        <a class='button pkbutton bg-blue' id='submitUpdatePiwikAnalyticsjsLookupToken' >{l s='Lookup token' mod='piwikanalyticsjs'}</a>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#submitUpdatePiwikAnalyticsjsLookupToken').click(function () {
            $('#formUpdatePiwikAnalyticsjsLookupToken-wrapper').load('{$piwikAnalyticsControllerLink}&ajax=1&action=lookupauthtoken',{
                    PKLOOKUPTOKENHOST:$('#PKLOOKUPTOKENHOST').val(),
                    PKLOOKUPTOKENUSRNAME:$('#PKLOOKUPTOKENUSRNAME').val(),
                    PKLOOKUPTOKENUSRPASSWD:$('#PKLOOKUPTOKENUSRPASSWD').val(),
                    PKLOOKUPTOKENSAVEUSRPWD:$('#PKLOOKUPTOKENSAVEUSRPWD').is(':checked'),
                    PKLOOKUPTOKENPAUTHUSR:$('#PKLOOKUPTOKENPAUTHUSR').val(),
                    PKLOOKUPTOKENPAUTHPWD:$('#PKLOOKUPTOKENPAUTHPWD').val()
            });
        });
    });
</script>
{/if}