{*
* Copyright (C) 2016 Christian Jensen
*
* This file is part of PiwikAnalyticsJS for prestashop.
* 
* PiwikAnalyticsJS for prestashop is free software: you can redistribute 
* it and/or modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation, either version 3 of the 
* License, or (at your option) any later version.
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
<script type="text/javascript">
    $(document).ready(function () {
        $('a#desc-module-update').attr('href', "#");
        
    {if version_compare($psversion, '1.5.0.13','>=')}
        {* jQuery is to old *}
        $('a#desc-module-update').on('click', function () {
            showLoadingStuff();
            jConfirm('{l s="You are about to check for updates, if you continue, a call to github repository will be made in order to check for new stable releases" mod='piwikanalyticsjs'}',
            '{l s="Check for updates to PiwikAnalyticsJS" mod='piwikanalyticsjs'}', moduleUpdateConfirmClick);
            return false;
        });
    {/if}
    
        if ($('#PKNewAddtionalUrls').length > 0) {
        {literal}
            $('#PKNewAddtionalUrls').tagify({delimiters: [13, 44], addTagPrompt: '{/literal}{l s='Add Url' mod='piwikanalyticsjs'}{literal}'});
            $('#configuration_form').submit(function () { $(this).find('#PKNewAddtionalUrls').val($('#PKNewAddtionalUrls').tagify('serialize')); });
        {/literal}
        }
        if ($('#PKNewSearchKeywordParameters').length > 0) {
        {literal}
            $('#PKNewSearchKeywordParameters').tagify({delimiters: [13, 44], addTagPrompt: '{/literal}{l s='Add Keyword' mod='piwikanalyticsjs'}{literal}'});
            $('#configuration_form').submit(function () { $(this).find('#PKNewSearchKeywordParameters').val($('#PKNewSearchKeywordParameters').tagify('serialize')); });
        {/literal}
        }
        if ($('#PKNewSearchCategoryParameters').length > 0) {
        {literal}
            $('#PKNewSearchCategoryParameters').tagify({delimiters: [13, 44], addTagPrompt: '{/literal}{l s='Add Parameter' mod='piwikanalyticsjs'}{literal}'});
            $('#configuration_form').submit(function () { $(this).find('#PKNewSearchCategoryParameters').val($('#PKNewSearchCategoryParameters').tagify('serialize')); });
        {/literal}
        }
        if ($('#PKNewExcludedIps').length > 0) {
        {literal}
            $('#PKNewExcludedIps').tagify({delimiters: [13, 44], addTagPrompt: '{/literal}{l s='Add IP' mod='piwikanalyticsjs'}{literal}'});
            $('#configuration_form').submit(function () { $(this).find('#PKNewExcludedIps').val($('#PKNewExcludedIps').tagify('serialize')); });
        {/literal}
        }
        if ($('#PKNewExcludedQueryParameters').length > 0) {
        {literal}
            $('#PKNewExcludedQueryParameters').tagify({delimiters: [13, 44], addTagPrompt: '{/literal}{l s='Add Parameter' mod='piwikanalyticsjs'}{literal}'});
            $('#configuration_form').submit(function () { $(this).find('#PKNewExcludedQueryParameters').val($('#PKNewExcludedQueryParameters').tagify('serialize')); });
        {/literal}
        }
    
        /* Tabs */
        $('ul.tabs-pk').each(function(){
            if($(this).hasClass('not-tab'))
                return;
            var $active, $content, $links = $(this).find('a');
            $active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
            $active.addClass('active');
            $content = $($active[0].hash);
            $links.not($active).each(function () {
                $(this.hash).hide();
            });
            $(this).find('a').click(function(e){
                if($(this).hasClass('link-not-tab'))
                    return true;
                if(typeof tabContentChanged === 'undefined' ||
                        (typeof tabContentChanged === 'function' && tabContentChanged() !== true) || 
                        (typeof tabContentChanged === 'function' && tabContentChanged() === true &&
                        confirm("{l s='Tab content has changed are you sure you want to continue' mod='piwikanalyticsjs'}"))){
                    $active.removeClass('active');
                    $content.hide();
                    $active = $(this);
                    $content = $(this.hash);
                    $active.addClass('active');
                    $content.show();
                    if (typeof tabContentChanged === 'function') {
                        tabContentChanged(false);
                    }
                    e.preventDefault();
                }
            });
        });
    });
    
    function moduleUpdateConfirmClick(result) {
        hideLoadingStuff();
        if (result === true) {
            alert("Check for updates not yet implemented");
        }
    }

    function createNewSiteFromStep1(){
        /* used from step 1 only */
        var host = $('#PIWIK_HOST_WIZARD').val(),
                user = $('#PIWIK_USRNAME_WIZARD').val(),
                passwd = $('#PIWIK_USRPASSWD_WIZARD').val();
        if (host === "") {
            $('#PIWIK_HOST_WIZARD').parent('label').addClass('has-error').focus();
            alert('{l s="I need a Piwik host, sorry!" mod='piwikanalyticsjs'}');
            return false;
        }else{ $('#PIWIK_HOST_WIZARD').parent('label').removeClass('has-error'); }
        if (user === "") {
            $('#PIWIK_USRNAME_WIZARD').parent('label').addClass('has-error').focus();
            alert('{l s="I need a Piwik login username, sorry!" mod='piwikanalyticsjs'}');
            return false;
        }else{ $('#PIWIK_USRNAME_WIZARD').parent('label').removeClass('has-error'); }
        if (passwd === "") {
            $('#PIWIK_USRPASSWD_WIZARD').parent('label').addClass('has-error').focus();
            alert('{l s="I need a Piwik login password, sorry!" mod='piwikanalyticsjs'}');
            return false;
        }else{ $('#PIWIK_USRPASSWD_WIZARD').parent('label').removeClass('has-error'); }
        {if $psversion >= '1.6'}
            $('#formPiwikAnalyticsjsWizard').prop('action', '{$pscurrentIndex}&formPiwikAnalyticsjsWizard=1&createnewsite=1&token={$pstoken}');
        {else}
            $('#formPiwikAnalyticsjsWizard').attr('action', '{$pscurrentIndex}&formPiwikAnalyticsjsWizard=1&createnewsite=1&token={$pstoken}');
        {/if}
        $('#formPiwikAnalyticsjsWizard').submit();
        return false;
    }

    function disableSubmit() {
        $(".pkforms").children().find('.disable').each(function () {
            $(this).attr('disabled', 'disabled');
        });
    }
    function enableSubmit() {
        $(".pkforms").children().find('.disable').each(function () {
            $(this).removeAttr('disabled');
        });
    }

    {if version_compare($psversion, '1.5.4.999','>')}
    function hideLoadingStuff() {
        $('#ajax_running').hide('fast');
        clearTimeout(ajax_running_timeout);
        $.fancybox.helpers.overlay.close();
        $.fancybox.hideLoading();
    }
    function showLoadingStuff() {
        showAjaxOverlay();
        $.fancybox.helpers.overlay.open({ parent: $('body') });
        $.fancybox.showLoading();
    }
    {else}
    function showLoadingStuff() { $.fancybox.showActivity(); }
    function hideLoadingStuff() { $.fancybox.hideActivity(); }
    {/if}
        
</script>