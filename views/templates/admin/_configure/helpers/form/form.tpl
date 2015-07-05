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
    {if $input.type == 'html' && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6.1'}
        {if isset($input.html_content)}
            {$input.html_content}
        {else}
            {$input.name}
        {/if}
    {elseif $input.type == 'switch' && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
        {foreach $input.values as $value}
            <input type="radio" name="{$input.name}" id="{$value.id}" value="{$value.value|escape:'html':'UTF-8'}"
                   {if $fields_value[$input.name] == $value.value}checked="checked"{/if}
                   {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
            <label class="t" for="{$value.id}">
                {if isset($input.is_bool) && $input.is_bool == true}
                    {if $value.value == 1}
                        <img src="../img/admin/enabled.gif" alt="{$value.label}" title="{$value.label}" />
                    {else}
                        <img src="../img/admin/disabled.gif" alt="{$value.label}" title="{$value.label}" />
                    {/if}
                {else}
                    {$value.label}
                {/if}
            </label>
            {if isset($input.br) && $input.br}<br />{/if}
            {if isset($value.p) && $value.p}<p>{$value.p}</p>{/if}
        {/foreach}
    {elseif $input.type == 'myBtn'}
        <a href="{$input.href}" {if isset($input.id)}id="{$input.id}"{/if} class="btn btn-default{if isset($input.class)} {$input.class}{/if}" {if isset($input.extraattr)}{$input.extraattr}{/if}>{if isset($input.icon)}<i class="{$input.icon}" ></i> {/if}{$input.title}</a>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}


{block name="footer"}
{capture name='form_submit_btn'}{counter name='form_submit_btn'}{/capture}
{if isset($fieldset['form']['submit']) || isset($fieldset['form']['buttons']) && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6.1'}
    <div class="panel-footer">
        {if isset($fieldset['form']['submit']) && !empty($fieldset['form']['submit'])}
            <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="{if isset($fieldset['form']['submit']['class'])}{$fieldset['form']['submit']['class']}{else}btn btn-default pull-right{/if}">
                <i class="{if isset($fieldset['form']['submit']['icon'])}{$fieldset['form']['submit']['icon']}{else}process-icon-save{/if}"></i> {$fieldset['form']['submit']['title']}
            </button>
        {/if}
        {if isset($show_cancel_button) && $show_cancel_button}
            <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                <i class="process-icon-cancel"></i> {l s='Cancel'}
            </a>
        {/if}
        {if isset($fieldset['form']['reset'])}
            <button
                type="reset"
                id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                >
                {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
            </button>
        {/if}
        {if isset($fieldset['form']['buttons'])}
            {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                {if isset($btn.href) && trim($btn.href) != ''}
                    <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                {else}
                    <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                {/if}
            {/foreach}
        {/if}
    </div>
{else}
    {$smarty.block.parent}
{/if}
{/block}