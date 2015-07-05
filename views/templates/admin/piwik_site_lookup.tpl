{*
* Copyright (C) 2015 Christian Jensen
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
    var hostvalid = false;
    var passwordvalid = false;
    var usernamevalid = false;
    $(document).ready(function () {
        /*PIWIK_ XXXXXXXX _WIZARD */

        $("#PIWIK_HOST_WIZARD").on("keyup", function () {
            $("#PIWIK_HOST_WIZARD").trigger("input");
            return true;
        });

        $("#PIWIK_USRNAME_WIZARD").on("keyup", function () {
            $("#PIWIK_USRNAME_WIZARD").trigger("input");
            return true;
        });

        $("#PIWIK_USRPASSWD_WIZARD").on("keyup", function () {
            $("#PIWIK_USRPASSWD_WIZARD").trigger("input");
            return true;
        });

        $("#PIWIK_HOST_WIZARD").on("input", function () {
            var strvalue = $(this).val();
            hostvalid = false;
            var stringLength = strvalue.length;

            if (stringLength > 2) {
                var lastChar = strvalue.charAt(stringLength - 1);
                
                if (lastChar === '/')
                    hostvalid = true;
                else
                    hostvalid = false;

                if ((strvalue.indexOf("http://") === -1) && (strvalue.indexOf("https://") === -1))
                {
                    hostvalid = false;
                }
                else if (hostvalid === true)
                    hostvalid = true;
            }
            if (hostvalid === true)
                $(this).parent('div').removeClass('has-error');
            else
                $(this).parent('div').addClass('has-error');

            isValidInputsStep1();
            return true;
        });

        $("#PIWIK_USRNAME_WIZARD").on("input", function () {
            var strvalue = $(this).val();
            if (strvalue !== undefined && strvalue !== "" && strvalue.length > 2)
                usernamevalid = true;
            else
                usernamevalid = false;

            if (usernamevalid === true)
                $(this).parent('div').removeClass('has-error');
            else
                $(this).parent('div').addClass('has-error');

            isValidInputsStep1();
            return true;
        });

        $("#PIWIK_USRPASSWD_WIZARD").on("input", function () {
            var strvalue = $(this).val();
            if (strvalue !== undefined && strvalue !== "" && strvalue.length > 2)
                passwordvalid = true;
            else
                passwordvalid = false;

            if (passwordvalid === true)
                $(this).parent('div').removeClass('has-error');
            else
                $(this).parent('div').addClass('has-error');

            isValidInputsStep1();
            return true;
        });


        disableSubmit();
        $("#PIWIK_HOST_WIZARD").trigger("keyup");
        $("#PIWIK_USRNAME_WIZARD").trigger("keyup");
        $("#PIWIK_USRPASSWD_WIZARD").trigger("keyup");

        $('a#desc-module-back').attr('href', window.location.href.replace("&pkwizard", ""));

    });

    function isValidInputsStep1() {
        if ((hostvalid === true) && (passwordvalid === true) && (usernamevalid === true))
            enableSubmit();
        else
            disableSubmit();
    }
</script>