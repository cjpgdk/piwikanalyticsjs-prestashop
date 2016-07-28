<form action='#tabs-pk5' method='post' class='pkforms' autocomplete="off" name="formUpdatePiwikAnalyticsjsHTML">
    {if !empty($pkfvEXHTML_Warning)}
        <h3>{$pkfvEXHTML_Warning}</h3>
    {/if}

    <label>
        <span>{l s='Extra HTML' mod='piwikanalyticsjs'}</span>
        <textarea rows="10" cols="50" id="{$pkCPREFIX}EXHTML" name="{$pkCPREFIX}EXHTML" onchange="tabContentChanged(true);">{$pkfvEXHTML}</textarea>
        <small>{l s='Some extra HTML code to put after Piwik tracking code, this can be any html of your choice' mod='piwikanalyticsjs'}</small>
    </label>
    <br><br>
    <span>
        {l s='Piwik image tracking code append one of them to field "Extra HTML" this will add images tracking code to all your pages' mod='piwikanalyticsjs'}<br/>
        {if $pkfvEXHTML_ImageTracker != false && $pkfvEXHTML_ImageTrackerProxy != false}
            <strong>{l s='default' mod='piwikanalyticsjs'}</strong>:<br>
            <i>{$pkfvEXHTML_ImageTracker}</i><br><br>
            <strong>{l s='using proxy script' mod='piwikanalyticsjs'}</strong>:<br>
            <i>{$pkfvEXHTML_ImageTrackerProxy}</i>
        {else}
            <strong>{l s='Unable to get image tracking code from Piwik API, maby you are using an OLD version of Piwik' mod='piwikanalyticsjs'}</strong><br>
            <i>Your Piwik version is {$piwikVersion}</i>
        {/if}
    </span>

    <hr/>
    <input type='submit' class='button pkbutton bg-blue' value='{l s='Save' mod='piwikanalyticsjs'}' name='submitUpdatePiwikAnalyticsjsHTML' />
</form>