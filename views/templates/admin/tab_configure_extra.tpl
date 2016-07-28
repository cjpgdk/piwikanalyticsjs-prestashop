<form action='#tabs-pk4' method='post' class='pkforms' autocomplete="off" name="formUpdatePiwikAnalyticsjsExtra" id="formUpdatePiwikAnalyticsjsExtra">
    <span>
        <strong>{l s='Product id' mod='piwikanalyticsjs'}</strong><br/>
        {l s='in the next few inputs you can set how the product id is passed on to piwik, there are three variables you can use:' mod='piwikanalyticsjs'}<br/>
        <strong>{ldelim}ID{rdelim}</strong> {l s=': this variable is replaced with id of the product in prestashop' mod='piwikanalyticsjs'}<br/>
        <strong>{ldelim}REFERENCE{rdelim}</strong> {l s=': this variable is replaced with the unique reference you set when adding/updating a product' mod='piwikanalyticsjs'}<br/>
        <strong>{ldelim}ATTRID{rdelim}</strong> {l s=': this variable is replaced with id of the product attribute' mod='piwikanalyticsjs'}<br/>
        {l s='in cases where only the product id is available it will be parsed as ID and nothing else' mod='piwikanalyticsjs'}<br/>
    </span>

    <label>
        <span>{l s='Product id V1' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}PRODID_V1' type='text' name='{$pkCPREFIX}PRODID_V1' placeholder='{ldelim}ID{rdelim}-{ldelim}ATTRID{rdelim}#{ldelim}REFERENCE{rdelim}' value="{$pkfvPRODID_V1}" onchange="tabContentChanged(true);"/>
        <small>{l s='This template is used in case ALL three values are available ("Product ID", "Product Attribute ID" and "Product Reference")' mod='piwikanalyticsjs'}</small>
    </label>

    <label>
        <span>{l s='Product id V2' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}PRODID_V2' type='text' name='{$pkCPREFIX}PRODID_V2' placeholder='{ldelim}ID{rdelim}#{ldelim}REFERENCE{rdelim}' value="{$pkfvPRODID_V2}" onchange="tabContentChanged(true);"/>
        <small>{l s='This template is used in case only "Product ID" and "Product Reference" are available' mod='piwikanalyticsjs'}</small>
    </label>

    <label>
        <span>{l s='Product id V3' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}PRODID_V3' type='text' name='{$pkCPREFIX}PRODID_V3' placeholder='{ldelim}ID{rdelim}-{ldelim}ATTRID{rdelim}' value="{$pkfvPRODID_V3}" onchange="tabContentChanged(true);"/>
        <small>{l s='This template is used in case only "Product ID" and "Product Attribute ID" are available' mod='piwikanalyticsjs'}</small>
    </label>

    <hr class="bd-blue" />
    
    <span>
        <strong>{l s='Searches' mod='piwikanalyticsjs'}</strong><br/>
        {l s='the following input is used when a search is made with the page selection in use. You can use the following variables' mod='piwikanalyticsjs'}<br/>
        <strong>{ldelim}QUERY{rdelim}</strong> {l s=': is replaced with the search query' mod='piwikanalyticsjs'}<br/>
        <strong>{ldelim}PAGE{rdelim}</strong> {l s=': is replaced with the page number' mod='piwikanalyticsjs'}<br/>
    </span>

    <label>
        <span>{l s='Searches' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}SEARCH_QUERY' type='text' name='{$pkCPREFIX}SEARCH_QUERY' placeholder='{ldelim}QUERY{rdelim} ({ldelim}PAGE{rdelim})' value="{$pkfvSEARCH_QUERY}" onchange="tabContentChanged(true);"/>
        <small>{l s='Template to use when a multipage search is made' mod='piwikanalyticsjs'}</small>
    </label>
    
    <hr class="bd-blue" />

    <script type="text/javascript">
        $().ready(function () {
            $('#{$pkCPREFIX}SET_DOMAINS').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add URL' mod='piwikanalyticsjs'}'{rdelim});
            $('#formUpdatePiwikAnalyticsjsExtra').submit(function () {
                $(this).find('#{$pkCPREFIX}SET_DOMAINS').val($('#{$pkCPREFIX}SET_DOMAINS').tagify('serialize'));
            });
        });
    </script>

    <label>
        <span>{l s='Hide known alias URLs' mod='piwikanalyticsjs'}</span>
        <input type="text" class="tagify " value="{$pkfvSET_DOMAINS}" id="{$pkCPREFIX}SET_DOMAINS" name="{$pkCPREFIX}SET_DOMAINS" onchange="tabContentChanged(true);"/>
        <small>{l s='In the "Outlinks" report, hide clicks to known alias URLs, Example: *.example.com' mod='piwikanalyticsjs'}<br>
            {l s='to add multiple domains you must separate them with comma ",", the currently tracked website is added to this array automatically, Leave empty to exclude this from the tracking code' mod='piwikanalyticsjs'}</small>
    </label>

    <hr/>
    <input type='submit' class='button pkbutton bg-blue' value='{l s='Save' mod='piwikanalyticsjs'}' name='submitUpdatePiwikAnalyticsjsExtra' />
</form>
