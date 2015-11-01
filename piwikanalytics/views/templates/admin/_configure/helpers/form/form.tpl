{*
* Copyright (C) 2015 Christian Jensen
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
* @author Christian M. Jensen
* @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*}
{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if  $input.type == 'password'}
        {* fix password value.*}
        <div class="input-group fixed-width-lg">
            <span class="input-group-addon">
                <i class="icon-key"></i>
            </span>
            <input type="password"
                   id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
                   name="{$input.name}"
                   class="{if isset($input.class)}{$input.class}{/if}"
                   value="{$fields_value[$input.name]}"
                   {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if}
                   {if isset($input.required) && $input.required } required="required" {/if} />
        </div>

    {else if $input.type == 'html'}
        {$input.name}
    {else if $input.type == 'tagsurls'}
        {* set it this way to give a more meaning full translation insted of Add tag *}
        {literal}
            <script type="text/javascript">
                $().ready(function () {
                    var input_id = '{/literal}{if isset($input.id)}{$input.id}{else}{$input.name}{/if}{literal}';
                    $('#' + input_id).tagify({delimiters: [13, 44], addTagPrompt: '{/literal}{l s='Add Url'}{literal}'});
                    $({/literal}'#{$table}{literal}_form').submit(function () {
                        $(this).find('#' + input_id).val($('#' + input_id).tagify('serialize'));
                    });
                });
            </script>
        {/literal}
        {$input.class = 'tagify'}
        {$input.type = 'text'}
        {$smarty.block.parent}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}