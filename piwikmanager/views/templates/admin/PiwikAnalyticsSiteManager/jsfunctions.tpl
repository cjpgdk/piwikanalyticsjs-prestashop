{*
* Copyright (C) 2015 Christian Jensen
*
* This file is part of PiwikManager for prestashop.
* 
* PiwikManager for prestashop is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* PiwikManager for prestashop is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with PiwikManager for prestashop. If not, see <http://www.gnu.org/licenses/>.
*
* @author Christian M. Jensen
* @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*}
<script type="text/javascript">
    $(document).ready(function () {
        $('a#desc-module-update').attr('href', "#");

        $('a#page-header-desc-configuration-update, a#page-header-desc-PiwikAnalyticsSiteManager-update').on('click', function () {
            showLoadingStuff();
            jConfirm('{l s="You are about to check for updates, if you continue a call to github repository will be made in order to check for new stable releases" mod='piwikmanager'}', '{l s="Check for updates to PiwikManager" mod='piwikmanager'}', moduleUpdateConfirmClick)
            return false;
        });

        $('#configuration_form_reset_btn').click(function () {
            window.location.reload(false);
            return false;
        });
    });

    function moduleUpdateConfirmClick(result) {
        hideLoadingStuff();
        if (result === true) {
            alert("Check for updates, not yet implemented");
        }
    }
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

    function SubmitStay(jQform, action) {
        if (jQform !== undefined) {
            jQform.attr('action',action)
        }
        return true;
    }
</script>