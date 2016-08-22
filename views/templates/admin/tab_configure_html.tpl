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
<form action='#tabs-pk5' method='post' class='pkforms' autocomplete="off" name="formUpdatePiwikAnalyticsjsHTML" id="formUpdatePiwikAnalyticsjsHTML">
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
        {l s='Piwik image tracking code append one of them to field "Extra HTML" this will add image tracking code to all your pages' mod='piwikanalyticsjs'}<br/>
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

    <hr class="bd-blue" />

    <label class="switch">
        <span>{l s='Enable link tracking' mod='piwikanalyticsjs'}</span>
        <input id="{$pkCPREFIX}LINKTRACK" class="pka-toggle pka-toggle-yes-no" type="checkbox"{if $pkfvLINKTRACK==1} checked="checked"{/if} name="{$pkCPREFIX}LINKTRACK" onchange="tabContentChanged(true);" />
        <label for="{$pkCPREFIX}LINKTRACK" data-on="Yes" data-off="No" style="clear: both;"></label>
        <small>{l s='Install link tracking on all applicable link elements.' mod='piwikanalyticsjs'}</small>
    </label>
    <script type="text/javascript">
        $().ready(function () {
            $('#{$pkCPREFIX}LINKClS').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add Class' mod='piwikanalyticsjs'}'{rdelim});
            $('#formUpdatePiwikAnalyticsjsHTML').submit(function () {
                $(this).find('#{$pkCPREFIX}LINKClS').val($('#{$pkCPREFIX}LINKClS').tagify('serialize'));
            });
        });
    </script>
    <label>
        <span>{l s='Set link classes' mod='piwikanalyticsjs'}</span>
        <input type="text" class="tagify " value="{$pkfvLINKClS}" id="{$pkCPREFIX}LINKClS" name="{$pkCPREFIX}LINKClS" onchange="tabContentChanged(true);"/>
        <small>{l s='Set classes to be treated as outlinks (in addition to piwik_link)' mod='piwikanalyticsjs'}</small>
    </label>
    
    <label>
        <span>{l s='Set link tracking timer' mod='piwikanalyticsjs'}</span>
        <input id='{$pkCPREFIX}LINKTTIME' type='text' name='{$pkCPREFIX}LINKTTIME' value="{$pkfvLINKTTIME}" onchange="tabContentChanged(true);"/>
        <small>{l s='Set delay for link tracking in milliseconds. (set to 0 uses piwik default)' mod='piwikanalyticsjs'}</small>
    </label>
    
    <script type="text/javascript">
        $().ready(function () {
            $('#{$pkCPREFIX}LINKClSIGNORE').tagify({ldelim}delimiters: [13, 44], addTagPrompt: '{l s='Add Class' mod='piwikanalyticsjs'}'{rdelim});
            $('#formUpdatePiwikAnalyticsjsHTML').submit(function () {
                $(this).find('#{$pkCPREFIX}LINKClSIGNORE').val($('#{$pkCPREFIX}LINKClSIGNORE').tagify('serialize'));
            });
        });
    </script>
    <label>
        <span>{l s='Set ignore link classes' mod='piwikanalyticsjs'}</span>
        <input type="text" class="tagify " value="{$pkfvLINKClSIGNORE}" id="{$pkCPREFIX}LINKClSIGNORE" name="{$pkCPREFIX}LINKClSIGNORE" onchange="tabContentChanged(true);"/>
        <small>{l s='Set classes to be ignored if present in link (in addition to piwik_ignore)' mod='piwikanalyticsjs'}</small>
    </label>
    
    <hr/>
    <input type='submit' class='button pkbutton bg-blue' value='{l s='Save' mod='piwikanalyticsjs'}' name='submitUpdatePiwikAnalyticsjsHTML' />
</form>