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
    {if  $input.type == 'password' && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
        {* fix not isset input.size *}
        <input type="password" name="{$input.name}" {if isset($input.size)}size="{$input.size}"{/if} class="{if isset($input.class)}{$input.class}{/if}" value="" {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
    {elseif $input.type == 'html' && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6.0.4'}
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
    {elseif $input.type == 'tags' && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.5.6.1'}
        {* copy from ps version 1.5.6.2 *}
        {if isset($input.lang) AND $input.lang}
            <div class="translatable">
                {foreach $languages as $language}
                    <div class="lang_{$language.id_lang}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
                        {if $input.type == 'tags'}
                            {literal}
                                <script type="text/javascript">
                                    $().ready(function () {
                                        var input_id = '{/literal}{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}{literal}';
                                                $('#' + input_id).tagify({delimiters: [13, 44], addTagPrompt: '{/literal}{l s='Add tag' js=1}{literal}'});
                                                $({/literal}'#{$table}{literal}_form').submit(function () {
                                                    $(this).find('#' + input_id).val($('#' + input_id).tagify('serialize'));
                                                });
                                            });
                                </script>
                            {/literal}
                        {/if}
                        {assign var='value_text' value=$fields_value[$input.name][$language.id_lang]}
                        <input type="text"
                               name="{$input.name}_{$language.id_lang}"
                               id="{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}"
                               value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'htmlall':'UTF-8'}{else}{$value_text|escape:'htmlall':'UTF-8'}{/if}"
                               class="{if $input.type == 'tags'}tagify {/if}{if isset($input.class)}{$input.class}{/if}"
                               {if isset($input.size)}size="{$input.size}"{/if}
                               {if isset($input.maxlength)}maxlength="{$input.maxlength}"{/if}
                               {if isset($input.readonly) && $input.readonly}readonly="readonly"{/if}
                               {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
                               {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
                        {if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
                    </div>
                {/foreach}
            </div>
        {else}
            {if $input.type == 'tags'}
                {literal}
                    <script type="text/javascript">
                        $().ready(function () {
                            var input_id = '{/literal}{if isset($input.id)}{$input.id}{else}{$input.name}{/if}{literal}';
                            $('#' + input_id).tagify({delimiters: [13, 44], addTagPrompt: '{/literal}{l s='Add tag'}{literal}'});
                            $({/literal}'#{$table}{literal}_form').submit(function () {
                                $(this).find('#' + input_id).val($('#' + input_id).tagify('serialize'));
                            });
                        });
                    </script>
                {/literal}
            {/if}
            {assign var='value_text' value=$fields_value[$input.name]}
            <input type="text"
                   name="{$input.name}"
                   id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
                   value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'htmlall':'UTF-8'}{else}{$value_text|escape:'htmlall':'UTF-8'}{/if}"
                   class="{if $input.type == 'tags'}tagify {/if}{if isset($input.class)}{$input.class}{/if}"
                   {if isset($input.size)}size="{$input.size}"{/if}
                   {if isset($input.maxlength)}maxlength="{$input.maxlength}"{/if}
                   {if isset($input.class)}class="{$input.class}"{/if}
                   {if isset($input.readonly) && $input.readonly}readonly="readonly"{/if}
                   {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
                   {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
            {if isset($input.suffix)}{$input.suffix}{/if}
            {if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
        {/if}
    {elseif $input.type == 'myBtn'}
        {if  $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
            <a {if isset($input.href)}href="{$input.href}"{/if} {if isset($input.id)}id="{$input.id}"{/if} class="button{if isset($input.class)} {$input.class}{/if}" {if isset($input.extraattr)}{$input.extraattr}{/if}>{if isset($input.title)}<span>{$input.title}</span>{/if}</a>
                {else}
            <a {if isset($input.href)}href="{$input.href}"{/if} {if isset($input.id)}id="{$input.id}"{/if} class="btn btn-default{if isset($input.class)} {$input.class}{/if}" {if isset($input.extraattr)}{$input.extraattr}{/if}>{if isset($input.icon)}<i class="{$input.icon}" ></i> {/if}{if isset($input.title)}{$input.title}{/if}</a>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* show buttons in ps 1.5 *}
{block name="other_input"}
    {if $key == 'buttons' && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
        {* show buttons in ps 1.5 *}
        <div class="margin-form">
            {foreach from=$field item=btn key=k}
                {if isset($btn.href) && trim($btn.href) != ''}
                    <a {if isset($btn['ps15style'])}style="{$btn['ps15style']}"{/if} href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="button{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{$btn.title}</a>
                {else}
                    <button type="button" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="button{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn['name'])}name="{$btn['name']}"{/if}{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{$btn.title}</button>
                {/if}
            {/foreach}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* only available in >= ps 1.6 *}
{block name="footer"}
{capture name='form_submit_btn'}{counter name='form_submit_btn'}{/capture}
{if isset($fieldset['form']['submit']) || isset($fieldset['form']['buttons']) && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6.0.3'}
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