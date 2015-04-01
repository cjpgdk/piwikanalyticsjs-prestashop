<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;

/**
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
 * @version 0.7.5 PrestaShop? 1.4 Final
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class PiwikAnalyticsJS extends Module {

    var $_errors = "";
    static $htmlout = "";
    static $search = "";

    /**
     * setReferralCookieTimeout
     */
    const PK_RC_TIMEOUT = 262974;

    /**
     * setVisitorCookieTimeout
     */
    const PK_VC_TIMEOUT = 569777;

    /**
     * setSessionCookieTimeout
     */
    const PK_SC_TIMEOUT = 30;

    function __construct() {
        $this->name = 'piwikanalyticsjs';
        $this->tab = 'analytics_stats';
        $this->version = '0.7.5';
        $this->displayName = 'Piwik Analytics';
        parent::__construct();

        if ($this->id AND ! Configuration::get('PIWIK_SITEID'))
            $this->warning = $this->l('Piwik is not setup yet missing Site ID');
        else if ($this->id AND ! Configuration::get('PIWIK_TOKEN_AUTH'))
            $this->warning = $this->l('Piwik is not setup yet missing API token');
        else if ($this->id AND ! Configuration::get('PIWIK_HOST'))
            $this->warning = $this->l('Piwik is not setup yet missing host address');

        $this->description = $this->l('Integrate the Piwik Analytics script into your shop');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
    }

    function install() {
        return (
                parent::install() &&
                $this->registerHook('header') &&
                $this->registerHook('footer') &&
                $this->registerHook('search') &&
                $this->registerHook('extraRight') &&
                $this->registerHook('productfooter') &&
                $this->registerHook('orderConfirmation') &&
                $this->registerHook('adminStatsModules') &&
                Configuration::updateValue('PIWIK_SITEID', 0) &&
                Configuration::updateValue('PIWIK_COOKIE_TIMEOUT', ((int)self::PK_VC_TIMEOUT*60)) &&
                Configuration::updateValue('PIWIK_COOKIE_DOMAIN', '') &&
                Configuration::updateValue('PIWIK_SET_DOMAINS', '') &&
                Configuration::updateValue('PIWIK_DNT', 0) &&
                Configuration::updateValue('PIWIK_HOST', '') &&
                Configuration::updateValue('PIWIK_RCOOKIE_TIMEOUT', ((int)self::PK_RC_TIMEOUT*60)) &&
                Configuration::updateValue('PIWIK_SESSION_TIMEOUT', ((int)self::PK_SC_TIMEOUT*60)) &&
                Configuration::updateValue('PIWIK_PRODID_V1', '{ID}-{ATTRID}#{REFERENCE}') &&
                Configuration::updateValue('PIWIK_PRODID_V2', '{ID}#{REFERENCE}') &&
                Configuration::updateValue('PIWIK_PRODID_V3', '{ID}-{ATTRID}') &&
                Configuration::updateValue('PIWIK_DEFAULT_CURRENCY', 'DKK') &&
                Configuration::updateValue('PIWIK_USE_PROXY', 0) &&
                Configuration::updateValue('PIWIK_EXHTML', '') &&
                Configuration::updateValue('PIWIK_USE_CURL', 0) &&
                Configuration::updateValue('PIWIK_CRHTTPS', 0) &&
                Configuration::updateValue('PIWIK_TOKEN_AUTH', '') &&
                Configuration::updateValue('PIWIK_DREPDATE', 'day|today') &&
                Configuration::updateValue('PIWIK_USRPASSWD', '') &&
                Configuration::updateValue('PIWIK_USRNAME', '') &&
                Configuration::updateValue('PIWIK_PAUTHPWD', '') &&
                Configuration::updateValue('PIWIK_PAUTHUSR', '')
                );
    }

    function uninstall() {
        return (
                Configuration::deleteByName('PIWIK_SITEID') &&
                Configuration::deleteByName('PIWIK_COOKIE_TIMEOUT') &&
                Configuration::deleteByName('PIWIK_COOKIE_DOMAIN') &&
                Configuration::deleteByName('PIWIK_SET_DOMAINS') &&
                Configuration::deleteByName('PIWIK_DNT') &&
                Configuration::deleteByName('PIWIK_HOST') &&
                Configuration::deleteByName('PIWIK_RCOOKIE_TIMEOUT') &&
                Configuration::deleteByName('PIWIK_SESSION_TIMEOUT') &&
                Configuration::deleteByName('PIWIK_PRODID_V1') &&
                Configuration::deleteByName('PIWIK_PRODID_V2') &&
                Configuration::deleteByName('PIWIK_PRODID_V3') &&
                Configuration::deleteByName('PIWIK_DEFAULT_CURRENCY') &&
                Configuration::deleteByName('PIWIK_USE_PROXY') &&
                Configuration::deleteByName('PIWIK_EXHTML') &&
                Configuration::deleteByName('PIWIK_USE_CURL') &&
                Configuration::deleteByName('PIWIK_TOKEN_AUTH') &&
                Configuration::deleteByName('PIWIK_DREPDATE') &&
                Configuration::deleteByName('PIWIK_USRPASSWD') &&
                Configuration::deleteByName('PIWIK_USRNAME') &&
                Configuration::deleteByName('PIWIK_CRHTTPS') &&
                Configuration::deleteByName('PIWIK_PAUTHUSR') &&
                Configuration::deleteByName('PIWIK_PAUTHPWD') &&
                parent::uninstall()
                );
    }

    function getContent() {
        $output = '<h2>' . $this->l("Piwik Analytics") . '</h2>';
        if (Tools::isSubmit('submitPAnalytics')) {

            // Validate Piwik Host.
            if (Tools::getIsset('PIWIK_HOST')) {
                $tmp = Tools::getValue('PIWIK_HOST', '');
                if (!empty($tmp) && (filter_var($tmp, FILTER_VALIDATE_URL) || filter_var('http://' . $tmp, FILTER_VALIDATE_URL))) {
                    $tmp = str_replace(array('http://', 'https://', '//'), "", $tmp);
                    if (substr($tmp, -1) != "/")
                        $tmp .= "/";
                    Configuration::updateValue('PIWIK_HOST', $tmp);
                } else
                    $output .= $this->displayError($this->l('Piwik host cannot be empty'));
            }

            // Validate use proxy script
            if (Tools::getIsset('PIWIK_USE_PROXY')) {
                $tmp = Tools::getValue('PIWIK_USE_PROXY', 0);
                Configuration::updateValue('PIWIK_USE_PROXY', ($tmp == 0 ? 0 : 1));
            }

            // Validate Proxy script url.
            if (Tools::getIsset('PIWIK_PROXY_SCRIPT')) {
                $tmp = Tools::getValue('PIWIK_PROXY_SCRIPT', '');
                if (!empty($tmp) && (filter_var($tmp, FILTER_VALIDATE_URL) || filter_var('http://' . $tmp, FILTER_VALIDATE_URL))) {
                    $tmp = str_replace(array('http://', 'https://', '//'), "", $tmp);
                    Configuration::updateValue('PIWIK_PROXY_SCRIPT', $tmp);
                } else
                    $output .= $this->displayError($this->l('Piwik proxy script url cannot be empty'));
            }

            // Validate use cURL
            if (Tools::getIsset('PIWIK_USE_CURL')) {
                $tmp = Tools::getValue('PIWIK_USE_CURL', 0);
                Configuration::updateValue('PIWIK_USE_CURL', ($tmp == 0 ? 0 : 1));
            }

            // Validate site id
            if (Tools::getIsset('PIWIK_SITEID')) {
                $tmp = (int) Tools::getValue('PIWIK_SITEID', 0);
                if ($tmp > 0 && Validate::isInt($tmp)) {
                    Configuration::updateValue('PIWIK_SITEID', intval($tmp));
                } else
                    $output .= $this->displayError($this->l('Piwik site id is lower or equal to "0"'));
            }

            // Validate token auth
            if (Tools::getIsset('PIWIK_TOKEN_AUTH')) {
                $tmp = Tools::getValue('PIWIK_TOKEN_AUTH', '');
                if (!empty($tmp)) {
                    Configuration::updateValue('PIWIK_TOKEN_AUTH', $tmp);
                } else
                    $output .= $this->displayError($this->l('Piwik auth token is empty'));
            }

            // cookie domain
            if (Tools::getIsset('PIWIK_COOKIE_DOMAIN')) {
                $tmp = Tools::getValue('PIWIK_COOKIE_DOMAIN', '');
                Configuration::updateValue('PIWIK_COOKIE_DOMAIN', $tmp);
            }

            // set domain
            if (Tools::getIsset('PIWIK_SET_DOMAINS')) {
                $tmp = Tools::getValue('PIWIK_SET_DOMAINS', '');
                Configuration::updateValue('PIWIK_SET_DOMAINS', $tmp);
            }

            // DoNotTrack detection
            if (Tools::getIsset('PIWIK_DNT')) {
                $tmp = Tools::getValue('PIWIK_DNT', 0);
                Configuration::updateValue('PIWIK_DNT', ($tmp == 0 ? 0 : 1));
            }

            // Extra HTML
            if (Tools::getIsset('PIWIK_EXHTML'))
                Configuration::updateValue('PIWIK_EXHTML', Tools::getValue('PIWIK_EXHTML'), TRUE);

            // PIWIK CURRENCY
            if (Tools::getIsset('PIWIK_DEFAULT_CURRENCY'))
                Configuration::updateValue('PIWIK_DEFAULT_CURRENCY', Tools::getValue('PIWIK_DEFAULT_CURRENCY'));

            //Stats default report date
            if (Tools::getIsset('PIWIK_DREPDATE'))
                Configuration::updateValue('PIWIK_DREPDATE', Tools::getValue('PIWIK_DREPDATE'));

            //Stats username
            if (Tools::getIsset('PIWIK_USRNAME'))
                Configuration::updateValue('PIWIK_USRNAME', Tools::getValue('PIWIK_USRNAME'));

            //Stats password
            if (Tools::getIsset('PIWIK_USRPASSWD'))
                Configuration::updateValue('PIWIK_USRPASSWD', Tools::getValue('PIWIK_USRPASSWD'));

            // proxy use https
            if (Tools::getIsset('PIWIK_CRHTTPS')) {
                $tmp = Tools::getValue('PIWIK_CRHTTPS', 0);
                Configuration::updateValue('PIWIK_CRHTTPS', ($tmp == 0 ? 0 : 1));
            }

            // session cookie timeout
            if (Tools::getIsset('PIWIK_SESSION_TIMEOUT')) {
                $tmp = Tools::getValue('PIWIK_SESSION_TIMEOUT', self::PK_SC_TIMEOUT);
                Configuration::updateValue('PIWIK_SESSION_TIMEOUT', ((int) $tmp * 60));
            }

            // cookie timeout
            if (Tools::getIsset('PIWIK_COOKIE_TIMEOUT')) {
                $tmp = Tools::getValue('PIWIK_COOKIE_TIMEOUT', self::PK_VC_TIMEOUT);
                Configuration::updateValue('PIWIK_COOKIE_TIMEOUT', ((int) $tmp * 60));
            }

            // Referral cookie timeout
            if (Tools::getIsset('PIWIK_RCOOKIE_TIMEOUT')) {
                $tmp = Tools::getValue('PIWIK_RCOOKIE_TIMEOUT', self::PK_RC_TIMEOUT);
                Configuration::updateValue('PIWIK_RCOOKIE_TIMEOUT', ((int) $tmp * 60));
            }

            // proxy auth
            if (Tools::getIsset('PIWIK_PAUTHUSR') && Tools::getIsset('PIWIK_PAUTHPWD')) {
                Configuration::updateValue('PIWIK_PAUTHUSR', Tools::getValue('PIWIK_PAUTHUSR', ''));
                Configuration::updateValue('PIWIK_PAUTHPWD', Tools::getValue('PIWIK_PAUTHPWD', ''));
            }

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
        return $output . $this->displayForm();
    }

    function displayForm() {
        $output = "";

        require 'PKHelper.php';
        PKHelper::$_module = & $this;
        $piwikSite = PKHelper::getPiwikSite();
        $this->displayErrors(PKHelper::$errors);
        PKHelper::$errors = PKHelper::$error = "";
        
        if ($piwikSite !== false) {
            $output .= '<fieldset class="space">'
                    . $this->l('Based on the settings you provided this is the info i get from Piwik!')
                    . ' <br><strong>'
                    . $this->l('Name')
                    . '</strong>: <i>' . $piwikSite[0]->name . '</i><br><strong>'
                    . $this->l('Main Url')
                    . '</strong>: <i>' . $piwikSite[0]->main_url . '</i>'
                    . '<br></fieldset><br/>';
        }

        $output .= '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';

        $PIWIK_USE_PROXY = Configuration::get('PIWIK_USE_PROXY');
        $PIWIK_USE_CURL = Configuration::get('PIWIK_USE_CURL');
        $PIWIK_DNT = Configuration::get('PIWIK_DNT');
        $PIWIK_CRHTTPS = Configuration::get('PIWIK_CRHTTPS');

        // only allow curencies in prestashop.
        $currencies = array();
        foreach (Currency::getCurrencies() as $key => $val) {
            $currencies[$key] = array(
                'iso_code' => $val['iso_code'],
                'name' => "{$val['name']} ({$val['iso_code']})",
            );
        }

        $output .= '<fieldset class="space">'
                . '<legend><img src="../modules/piwikanalyticsjs/logo.gif" alt="" class="middle" />' . $this->l('Piwik Analytics') . '</legend>';

        /* Piwik Host */
        $output .= '<label>' . $this->l('Piwik Host') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_HOST" value="' . Configuration::get('PIWIK_HOST') . '" />'
                . '<p class="clear">' . $this->l('The host where your piwik is installed.!') . '<br/>'
                . $this->l("Example: www.example.com/piwik/ (without protocol and with / at the end!)") . ''
                . '</p></div>';

        /* Use proxy script */
        $output .= '<label>' . $this->l('Use proxy script') . '</label>'
                . '<div class="margin-form">'
                . '<input name="PIWIK_USE_PROXY" id="PIWIK_USE_PROXY_on" value="1" ' . ($PIWIK_USE_PROXY == 1 ? 'checked="checked"' : '') . ' type="radio">'
                . '<label class="t" for="PIWIK_USE_PROXY_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Yes') . '" title="' . $this->l('Yes') . '"></label>'
                . '<input name="PIWIK_USE_PROXY" id="PIWIK_USE_PROXY_off" value="0" ' . ($PIWIK_USE_PROXY == 0 ? 'checked="checked"' : '') . ' type="radio">'
                . '<label class="t" for="PIWIK_USE_PROXY_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('No') . '" title="' . $this->l('No') . '"></label>'
                . '<p class="clear">' . $this->l('Whether or not to use the proxy insted of Piwik Host ') . '</p>'
                . '</div>';

        /* Proxy script */
        $output .= '<label>' . $this->l('Proxy script') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_PROXY_SCRIPT" value="' . Configuration::get('PIWIK_PROXY_SCRIPT') . '" />'
                . '<p class="clear">' . $this->l('Example: www.example.com/pkproxy.php') . '<br/>'
                . $this->l('the FULL url and path to proxy script<br/>build-in:')
                . '<br/>'
                . '<i>' . $this->getModuleLink($this->name, 'piwik') . '</i>'
                . '</p></div>';

        /* Use cURL */
        $output .= '<label>' . $this->l('Use cURL') . '</label>'
                . '<div class="margin-form">'
                . '<input name="PIWIK_USE_CURL" id="PIWIK_USE_CURL_on" value="1" ' . ($PIWIK_USE_CURL == 1 ? 'checked="checked"' : '') . ' type="radio">'
                . '<label class="t" for="PIWIK_USE_CURL_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Yes') . '" title="' . $this->l('Yes') . '"></label>'
                . '<input name="PIWIK_USE_CURL" id="PIWIK_USE_CURL_off" value="0" ' . ($PIWIK_USE_CURL == 0 ? 'checked="checked"' : '') . ' type="radio">'
                . '<label class="t" for="PIWIK_USE_CURL_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('No') . '" title="' . $this->l('No') . '"></label>'
                . '<p class="clear">' . $this->l('Whether or not to use cURL in Piwik API and proxy requests?') . '</p>'
                . '</div>';

        /* Piwik site id */
        $output .= '<label>' . $this->l('Piwik site id') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_SITEID" value="' . Configuration::get('PIWIK_SITEID') . '" />'
                . '<p class="clear">' . $this->l('You can find your piwik site id by loggin to your piwik installation.') . '<br/>'
                . $this->l('Example: 10')
                . '</p></div>';

        /* Piwik token auth */
        $output .= '<label>' . $this->l('Piwik token auth') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_TOKEN_AUTH" value="' . Configuration::get('PIWIK_TOKEN_AUTH') . '" />'
                . '<p class="clear">' . $this->l('You can find your piwik token by loggin to your piwik installation. under API') . '<br/>'
                . '</p></div>';

        /* Track visitors across subdomains  */
        $output .= '<label>' . $this->l('Track visitors across subdomains') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_COOKIE_DOMAIN" value="' . Configuration::get('PIWIK_COOKIE_DOMAIN') . '" />'
                . '<p class="clear">'
                . $this->l('So if one visitor visits x.example.com and y.example.com, they will be counted as a unique visitor. (setCookieDomain)')
                . '<br/>'
                . $this->l('The default is the document domain; if your web site can be visited at both www.example.com and example.com, you would use: "*.example.com" OR ".example.com" without the quotes')
                . '<br/>'
                . $this->l('Leave empty to exclude this from the tracking code')
                . '</p></div>';

        /* Hide known alias URLs */
        $output .= '<label>' . $this->l('Hide known alias URLs') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_SET_DOMAINS" value="' . Configuration::get('PIWIK_SET_DOMAINS') . '" />'
                . '<p class="clear">'
                . $this->l('So clicks on links to Alias URLs (eg. x.example.com) will not be counted as "Outlink". (setDomains)')
                . '<br/>'
                . $this->l('In the "Outlinks" report, hide clicks to known alias URLs, Example: *.example.com')
                . '<br/>'
                . $this->l('Note: to add multiple domains you must separate them with space " " one space')
                . '<br/>'
                . $this->l('Note: the currently tracked website is added to this array automatically')
                . '<br/>'
                . $this->l('Leave empty to exclude this from the tracking code')
                . '</p></div>';

        /* Enable client side DoNotTrack detection */
        $output .= '<label>' . $this->l('Enable client side DoNotTrack detection') . '</label>'
                . '<div class="margin-form">'
                . '<input name="PIWIK_DNT" id="PIWIK_DNT_on" value="1" ' . ($PIWIK_DNT == 1 ? 'checked="checked"' : '') . ' type="radio">'
                . '<label class="t" for="PIWIK_DNT_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Yes') . '" title="' . $this->l('Yes') . '"></label>'
                . '<input name="PIWIK_DNT" id="PIWIK_DNT_off" value="0" ' . ($PIWIK_DNT == 0 ? 'checked="checked"' : '') . ' type="radio">'
                . '<label class="t" for="PIWIK_DNT_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('No') . '" title="' . $this->l('No') . '"></label>'
                . '<p class="clear">' . $this->l('So tracking requests will not be sent if visitors do not wish to be tracked.') . '</p>'
                . '</div>';

        /* Piwik Currency */
        $output .= '<label>' . $this->l('Piwik Currency') . '</label>'
                . '<div class="margin-form">'
                . '<select name="PIWIK_DEFAULT_CURRENCY" class="" id="PIWIK_DEFAULT_CURRENCY">'
                . '<option value="0">Choose currency</option>';
        foreach ($currencies as $currencyId => $currency) {
            if (Configuration::get('PIWIK_DEFAULT_CURRENCY') == $currency['iso_code'])
                $output .= '<option value="' . $currency['iso_code'] . '" selected="selected">' . $currency['name'] . '</option>';
            else
                $output .= '<option value="' . $currency['iso_code'] . '">' . $currency['name'] . '</option>';
        }
        $output .= '</select>'
                . '<p class="clear">' . sprintf($this->l('Based on your settings in Piwik your default currency is %s'), ($piwikSite !== FALSE ? $piwikSite[0]->currency : $this->l('unknown')))
                . '</p></div>';

        $piwikImageCode = PKHelper::getPiwikImageTrackingCode();
        /* Piwik image tracking code */
        $output .= '<div class="margin-form">'
                . 'Piwik image tracking code append one of them to field "Extra HTML" this will add images tracking code to all your pages'
                . '<br>'
                . '<strong>default</strong>:<br>'
                . '<i>' . $piwikImageCode['default'] . '</i>'
                . '<br>'
                . '<strong>using proxy script</strong>:<br>'
                . '<i>' . $piwikImageCode['proxy'] . '</i>'
                . '<br></div>';

        /* Extra HTML */
        $output .= '<label>' . $this->l('Extra HTML') . '</label>'
                . '<div class="margin-form">'
                . '<textarea name="PIWIK_EXHTML" id="PIWIK_EXHTML" cols="50" rows="10">' . Configuration::get('PIWIK_EXHTML') . '</textarea>'
                . '<p class="clear">' . $this->l('Some extra HTML code to put after the piwik tracking code, this can be any html of your choice') . '<br/>'
                . '</p></div>'
                . '<center><input type="submit" name="submitPAnalytics" value="' . $this->l('Save') . '" class="button" /></center>'
                . '</fieldset>';

        /* Piwik Report */
        $output .= '<fieldset class="space"><legend><img src="../modules/piwikanalyticsjs/logo.gif" alt="" class="middle" />' . $this->l('Piwik Analytics - "Stats => Piwik Analytics"') . '</legend>'


                /* Piwik Report date */
                . '<label>' . $this->l('Piwik Report date') . '</label>'
                . '<div class="margin-form">'
                . '<select name="PIWIK_DREPDATE" class="" id="PIWIK_DREPDATE">'
                . '<option value="day|today" ' . (Configuration::get('PIWIK_DREPDATE') == "day|today" ? 'selected="selected"' : '') . '>' . $this->l("Today") . '</option>'
                . '<option value="day|yesterday" ' . (Configuration::get('PIWIK_DREPDATE') == "day|yesterday" ? 'selected="selected"' : '') . '>' . $this->l("Yesterday") . '</option>'
                . '<option value="range|previous7" ' . (Configuration::get('PIWIK_DREPDATE') == "range|previous7" ? 'selected="selected"' : '') . '>' . $this->l("Previous 7 days (not including today)") . '</option>'
                . '<option value="range|previous30" ' . (Configuration::get('PIWIK_DREPDATE') == "range|previous30" ? 'selected="selected"' : '') . '>' . $this->l("Previous 30 days (not including today)") . '</option>'
                . '<option value="range|last7" ' . (Configuration::get('PIWIK_DREPDATE') == "range|last7" ? 'selected="selected"' : '') . '>' . $this->l("Last 7 days (including today)") . '</option>'
                . '<option value="range|last30" ' . (Configuration::get('PIWIK_DREPDATE') == "range|last30" ? 'selected="selected"' : '') . '>' . $this->l("Last 30 days (including today)") . '</option>'
                . '<option value="week|today" ' . (Configuration::get('PIWIK_DREPDATE') == "week|today" ? 'selected="selected"' : '') . '>' . $this->l("Current Week") . '</option>'
                . '<option value="month|today" ' . (Configuration::get('PIWIK_DREPDATE') == "month|today" ? 'selected="selected"' : '') . '>' . $this->l("Current Month") . '</option>'
                . '<option value="year|today" ' . (Configuration::get('PIWIK_DREPDATE') == "year|today" ? 'selected="selected"' : '') . '>' . $this->l("Current Year") . '</option>'
                . '</select>'
                . '<p class="clear">' . $this->l('Report date to load by default') . '<br/>'
                . '</p>'
                . '</div>'

                /* Piwik User name */
                . '<label>' . $this->l('Piwik User name') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_USRNAME" value="' . Configuration::get('PIWIK_USRNAME') . '" />'
                . '<p class="clear">' . $this->l('You can store your Username for Piwik here to make it easy to open your piwik interface from your stats page with automatic login') . '<br/>'
                . '</p></div>'

                /* Piwik User password */
                . '<label>' . $this->l('Piwik User password') . '</label>'
                . '<div class="margin-form">'
                . '<input type="password" name="PIWIK_USRPASSWD" value="' . Configuration::get('PIWIK_USRPASSWD') . '" />'
                . '<p class="clear">' . $this->l('You can store your Password for Piwik here to make it easy to open your piwik interface from your stats page with automatic login ') . '<br/>'
                . '</p></div>'
                . '<center><input type="submit" name="submitPAnalytics" value="' . $this->l('Save') . '" class="button" /></center>'
                . '</fieldset>'

                /* Piwik extras */
                . '<fieldset class="space"><legend><img src="../modules/piwikanalyticsjs/logo.gif" alt="" class="middle" />' . $this->l('Piwik Analytics - Extras') . '</legend>'
                . '<div class="margin-form"><strong>' . $this->l('In this section you can modify certain aspects of the way this plugin sends requests to piwik') . '</strong></div>'

                /* Use HTTPS */
                . '<label>' . $this->l('Use HTTPS') . '</label>'
                . '<div class="margin-form">'
                . '<input name="PIWIK_CRHTTPS" id="PIWIK_CRHTTPS_on" value="1" ' . ($PIWIK_CRHTTPS == 1 ? 'checked="checked"' : '') . ' type="radio">'
                . '<label class="t" for="PIWIK_CRHTTPS_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Yes') . '" title="' . $this->l('Yes') . '"></label>'
                . '<input name="PIWIK_CRHTTPS" id="PIWIK_CRHTTPS_off" value="0" ' . ($PIWIK_CRHTTPS == 0 ? 'checked="checked"' : '') . ' type="radio">'
                . '<label class="t" for="PIWIK_CRHTTPS_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('No') . '" title="' . $this->l('No') . '"></label>'
                . '<p class="clear"><i>' . $this->l('use Hypertext Transfer Protocol Secure (HTTPS) in all requests from code to piwik, this only affects how requests are sent from proxy script to piwik, your visitors will still use the protocol they visit your shop with') . '</i></p>'
                . '</div>'

                /* Piwik Session Cookie timeout */
                . '<label>' . $this->l('Piwik Session Cookie timeout') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_SESSION_TIMEOUT" value="' . ((int) Configuration::get('PIWIK_SESSION_TIMEOUT') / 60) . '" />'
                . '<p class="clear">' . $this->l('Piwik Session Cookie timeout, the default is 30 minutes') . '<br/>'
                . $this->l('this value must be set in minutes') . '<br/>'
                . '</p></div>'

                /* Piwik Visitor Cookie timeout */
                . '<label>' . $this->l('Piwik Visitor Cookie timeout') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_COOKIE_TIMEOUT" value="' . ((int) Configuration::get('PIWIK_COOKIE_TIMEOUT') / 60) . '" />'
                . '<p class="clear">' . $this->l('Piwik Visitor Cookie timeout, the default is 13 months (569777 minutes)') . '<br/>'
                . $this->l('this value must be set in minutes') . '<br/>'
                . '</p></div>'

                /* Piwik Referral Cookie timeout */
                . '<label>' . $this->l('Piwik Referral Cookie timeout') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_RCOOKIE_TIMEOUT" value="' . ((int) Configuration::get('PIWIK_RCOOKIE_TIMEOUT') / 60) . '" />'
                . '<p class="clear">' . $this->l('Piwik Referral Cookie timeout, the default is 6 months (262974 minutes) ') . '<br/>'
                . $this->l('this value must be set in minutes') . '<br/>'
                . '</p></div>'

                /* Piwik Proxy Script Authorization */
                . '<div class="margin-form">'
                . '<p><strong>'
                . $this->l('Piwik Proxy Script Authorization? if your piwik is installed behind HTTP Basic Authorization (Both password and username must be filled before the values will be used)')
                . '</strong></p></div>'

                /* Proxy Script Username  */
                . '<label>' . $this->l('Proxy Script Username ') . '</label>'
                . '<div class="margin-form">'
                . '<input type="text" name="PIWIK_PAUTHUSR" value="' . Configuration::get('PIWIK_PAUTHUSR') . '" />'
                . '<p class="clear">' . $this->l('this field along with password can be used if your piwik installation is protected by HTTP Basic Authorization')
                . '</p></div>'

                /* Proxy Script Password */
                . '<label>' . $this->l('Proxy Script Password') . '</label>'
                . '<div class="margin-form">'
                . '<input type="password" name="PIWIK_PAUTHPWD" value="' . Configuration::get('PIWIK_PAUTHPWD') . '" />'
                . '<p class="clear">' . $this->l('this field along with username can be used if your piwik installation is protected by HTTP Basic Authorization')
                . '</p></div>'
                . '<center><input type="submit" name="submitPAnalytics" value="' . $this->l('Save') . '" class="button" /></center>'
                . '';
        $output .= '</fieldset></form>'
                . '<div id="PiwikLookupModal" class="PiwikLookupModalDialog"></div>';
        return $this->_errors.$output;
    }

    function getModuleLink($module, $controller = 'default', array $params = array(), $ssl = null, $id_lang = null, $id_shop = null) {
        $query = http_build_query($params, '', '&');
        return Tools::getHttpHost(true)
                . _MODULE_DIR_
                . $module
                . '/'
                . $controller . '.php'
                . ($query ? '?' . $query : '');
    }

    function hookHeader($params) {
        if (!$this->isRegisteredInHook('footer'))
            $this->registerHook('footer');
    }

    function hookFooter($params) {
        if (strpos($_SERVER['REQUEST_URI'], __PS_BASE_URI__ . 'order-confirmation.php') === 0)
            return '';
        $PIWIK_SITEID = (int) Configuration::get('PIWIK_SITEID');
        if ($PIWIK_SITEID <= 0)
            return "";


        $PIWIK_DNT = (int) Configuration::get('PIWIK_DNT');
        if ($PIWIK_DNT != 0)
            $PIWIK_DNT = "_paq.push(['setDoNotTrack', true]);";
        else
            $PIWIK_DNT = "";

        $PIWIK_USE_PROXY = (bool) Configuration::get('PIWIK_USE_PROXY');
        $PIWIK_EXHTML = Configuration::get('PIWIK_EXHTML');

        $PIWIK_HOST = Configuration::get('PIWIK_HOST');
        if ($PIWIK_USE_PROXY == true) {
            $PKURL = $PKTrackerUrl = "";
            $PIWIK_HOST = Configuration::get('PIWIK_PROXY_SCRIPT');
        } else {
            $PKURL = "+ 'piwik.js'";
            $PKTrackerUrl = "+ 'piwik.php'";
        }

        // setCookieDomain
        $PIWIK_COOKIE_DOMAIN = Configuration::get('PIWIK_COOKIE_DOMAIN');
        if (!empty($PIWIK_COOKIE_DOMAIN)) {
            $PIWIK_COOKIE_DOMAIN = "_paq.push(['setCookieDomain', '{$PIWIK_COOKIE_DOMAIN}']);";
        } else
            $PIWIK_COOKIE_DOMAIN = "";

        // setDomains
        $PIWIK_SET_DOMAINS = Configuration::get('PIWIK_SET_DOMAINS');
        if (!empty($PIWIK_SET_DOMAINS)) {
            $sdArr = explode(' ', $PIWIK_SET_DOMAINS);
            if (count($sdArr) > 1)
                $PIWIK_SET_DOMAINS = "['" . trim(implode("','", $sdArr), ",'") . "']";
            else
                $PIWIK_SET_DOMAINS = "'{$sdArr[0]}'";

            $PIWIK_SET_DOMAINS = "_paq.push(['setDomains', {$PIWIK_SET_DOMAINS}]);";
        } else
            $PIWIK_SET_DOMAINS = "";

        // setVisitorCookieTimeout
        $pkvct = (int) Configuration::get('PIWIK_COOKIE_TIMEOUT'); /* no isset if the same as default */
        if ($pkvct != 0 && $pkvct !== FALSE && ($pkvct != (int) (self::PK_VC_TIMEOUT * 60))) {
            $PIWIK_COOKIE_TIMEOUT = "_paq.push(['setVisitorCookieTimeout', '{$pkvct}']);";
        } else {
            $PIWIK_COOKIE_TIMEOUT = "";
        }
        unset($pkvct);

        // setReferralCookieTimeout
        $pkrct = (int) Configuration::get('PIWIK_RCOOKIE_TIMEOUT'); /* no isset if the same as default */
        if ($pkrct != 0 && $pkrct !== FALSE && ($pkrct != (int) (self::PK_RC_TIMEOUT * 60))) {
            $PIWIK_RCOOKIE_TIMEOUT = "_paq.push(['setReferralCookieTimeout', '{$pkrct}']);";
        } else {
            $PIWIK_RCOOKIE_TIMEOUT = "";
        }
        unset($pkrct);

        // setSessionCookieTimeout
        $pksct = (int) Configuration::get('PIWIK_SESSION_TIMEOUT'); /* no isset if the same as default */
        if ($pksct != 0 && $pksct !== FALSE && ($pksct != (int) (self::PK_SC_TIMEOUT * 60))) {
            $PIWIK_SESSION_TIMEOUT = "_paq.push(['setSessionCookieTimeout', '{$pksct}']);";
        } else {
            $PIWIK_SESSION_TIMEOUT = "";
        }
        unset($pksct);

        // setUserId
        $PIWIK_UUID = "";
        if ($params['cookie']->isLogged()) {
            $PIWIK_UUID = "_paq.push(['setUserId', '{$params['cookie']->id_customer}']);";
        }

        /* product tracking */
        $PIWIK_PRODUCTS = "";
        if (isset($_GET['id_product']) && Validate::isUnsignedInt($_GET['id_product'])) {
            $product = new Product($_GET['id_product'], false, (isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (int) Configuration::get('PS_LANG_DEFAULT')));
            if (Validate::isLoadedObject($product)) {
                $product_categorys = $this->get_category_names_by_product($product->id, FALSE);
                $PIWIK_PRODUCTS = "_paq.push(['setEcommerceView', '{$this->parseProductSku($product->id, FALSE, (isset($product->reference) ? $product->reference : FALSE))}', '" . htmlentities($product->name) . "', {$product_categorys}, '{$this->currencyConvertion(array('price' => Product::getPriceStatic($product->id, true, false), 'conversion_rate' => false,))}']);";
            }
        }

        /* category tracking */
        $PIWIK_CATEGORY = "";
        if (isset($_GET['id_category']) && Validate::isUnsignedInt($_GET['id_category'])) {
            $category = new Category($_GET['id_category'], (isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (int) Configuration::get('PS_LANG_DEFAULT')));
            $PIWIK_CATEGORY = "_paq.push(['setEcommerceView', false, false, '" . htmlentities($category->name) . "']);";
        }

        /* cart tracking */
        $PIWIK_CART = "";
        if (!$params['cookie']->PIWIKTrackCartFooter)
            $params['cookie']->PIWIKTrackCartFooter = time();
        if (strtotime($params['cart']->date_upd) >= $params['cookie']->PIWIKTrackCartFooter) {
            $params['cookie']->PIWIKTrackCartFooter = strtotime($params['cart']->date_upd) + 2;

            $Currency = new Currency($params['cart']->id_currency);
            $isCart = false;
            foreach ($params['cart']->getProducts() as $key => $value) {
                if (!isset($value['id_product']) || !isset($value['name']) || !isset($value['total_wt']) || !isset($value['quantity'])) {
                    continue;
                }
                $PIWIK_CART .= "\t_paq.push(['addEcommerceItem', '{$this->parseProductSku($value['id_product'], (isset($value['id_product_attribute']) && $value['id_product_attribute'] > 0 ? $value['id_product_attribute'] : FALSE), (isset($value['reference']) ? $value['reference'] : FALSE))}', '" . $value['name'] . (isset($value['attributes']) ? ' (' . $value['attributes'] . ')' : '') . "', {$this->get_category_names_by_product($value['id_product'], FALSE)}, '{$this->currencyConvertion(
                                array(
                                    'price' => $value['total_wt'],
                                    'conversion_rate' => $Currency->conversion_rate,
                                )
                        )}', '{$value['quantity']}']);" . PHP_EOL;
                $isCart = true;
            }

            if ($isCart) {
                $PIWIK_CART .= "\t_paq.push(['trackEcommerceCartUpdate', {$this->currencyConvertion(
                                array(
                                    'price' => $params['cart']->getOrderTotal(),
                                    'conversion_rate' => $Currency->conversion_rate,
                                )
                        )}]);" . PHP_EOL;
            }
        }

        $Page404 = "";
        if (strstr($_SERVER['PHP_SELF'], '404.php')) {
            $Page404 = "_paq.push(['setDocumentTitle',  '404/URL = ' +  encodeURIComponent(document.location.pathname+document.location.search) + '/From = ' + encodeURIComponent(document.referrer)]);";
        }
        if (!empty(self::$search)) {
            $TrackPage = self::$search;
            self::$search = "";
        } else {
            $TrackPage = "_paq.push(['trackPageView']);";
        }

        self::$htmlout = <<< EOF
<script type="text/javascript">
    var u=(("https:" == document.location.protocol) ? "https://{$PIWIK_HOST}" : "http://{$PIWIK_HOST}");
    var _paq = _paq || [];
    {$PIWIK_DNT}
    _paq.push(['setSiteId',{$PIWIK_SITEID}]);
    _paq.push(['setTrackerUrl',u{$PKTrackerUrl}]);
    {$PIWIK_COOKIE_DOMAIN}
    {$PIWIK_SET_DOMAINS}
    {$PIWIK_COOKIE_TIMEOUT}
    {$PIWIK_RCOOKIE_TIMEOUT}
    {$PIWIK_SESSION_TIMEOUT}
    _paq.push(['enableLinkTracking']);
    {$PIWIK_UUID}
    {$PIWIK_PRODUCTS}
    {$PIWIK_CATEGORY}
    {$PIWIK_CART}
    {$Page404}
    {$TrackPage}
    (function() { var d = document, g = d.createElement("script"), s = d.getElementsByTagName("script")[0];g.type = "text/javascript";g.defer = true;g.async = true;g.src = u{$PKURL};s.parentNode.insertBefore(g, s); })();
</script>
{$PIWIK_EXHTML}
EOF;
        return self::$htmlout;
    }

    function hookSearch($params) {
        if ((int) Configuration::get('PIWIK_SITEID') <= 0)
            return "";
        $params['total'] = intval($params['total']);
        /* if multi pages in search add page number of current if set! */
        $page = "";
        if (Tools::getIsset('p')) {
            $page = " (" . Tools::getValue('p') . ")";
        }

        // $params['expr'] is not the searched word if lets say search is Snitmøntre then the $params['expr'] will be Snitmontre
        $expr = Tools::getIsset('search_query') ? htmlentities(Tools::getValue('search_query')) : $params['expr'];
        self::$search = "_paq.push(['trackSiteSearch',\"{$expr}{$page}\",false,{$params['total']}]);" . PHP_EOL;
    }

    function hookExtraRight($params) {
        
    }

    function hookProductfooter($params) {
        
    }

    function hookOrderConfirmation($params) {
        $PIWIK_SITEID = (int) Configuration::get('PIWIK_SITEID');
        if ($PIWIK_SITEID <= 0)
            return "";


        $PIWIK_DNT = (int) Configuration::get('PIWIK_DNT');
        if ($PIWIK_DNT != 0)
            $PIWIK_DNT = "_paq.push(['setDoNotTrack', true]);";
        else
            $PIWIK_DNT = "";

        $PIWIK_USE_PROXY = (bool) Configuration::get('PIWIK_USE_PROXY');
        $PIWIK_EXHTML = Configuration::get('PIWIK_EXHTML');

        $PIWIK_HOST = Configuration::get('PIWIK_HOST');
        if ($PIWIK_USE_PROXY == true) {
            $PKURL = $PKTrackerUrl = "";
            $PIWIK_HOST = Configuration::get('PIWIK_PROXY_SCRIPT');
        } else {
            $PKURL = "+ 'piwik.js'";
            $PKTrackerUrl = "+ 'piwik.php'";
        }

        // setCookieDomain
        $PIWIK_COOKIE_DOMAIN = Configuration::get('PIWIK_COOKIE_DOMAIN');
        if (!empty($PIWIK_COOKIE_DOMAIN)) {
            $PIWIK_COOKIE_DOMAIN = "_paq.push(['setCookieDomain', '{$PIWIK_COOKIE_DOMAIN}']);";
        } else
            $PIWIK_COOKIE_DOMAIN = "";

        // setDomains
        $PIWIK_SET_DOMAINS = Configuration::get('PIWIK_SET_DOMAINS');
        if (!empty($PIWIK_SET_DOMAINS)) {
            $sdArr = explode(' ', $PIWIK_SET_DOMAINS);
            if (count($sdArr) > 1)
                $PIWIK_SET_DOMAINS = "['" . trim(implode("','", $sdArr), ",'") . "']";
            else
                $PIWIK_SET_DOMAINS = "'{$sdArr[0]}'";

            $PIWIK_SET_DOMAINS = "_paq.push(['setDomains', {$PIWIK_SET_DOMAINS}]);";
        } else
            $PIWIK_SET_DOMAINS = "";

        // setVisitorCookieTimeout
        $pkvct = (int) Configuration::get('PIWIK_COOKIE_TIMEOUT'); /* no isset if the same as default */
        if ($pkvct != 0 && $pkvct !== FALSE && ($pkvct != (int) (self::PK_VC_TIMEOUT * 60))) {
            $PIWIK_COOKIE_TIMEOUT = "_paq.push(['setVisitorCookieTimeout', '{$pkvct}']);";
        } else {
            $PIWIK_COOKIE_TIMEOUT = "";
        }
        unset($pkvct);

        // setReferralCookieTimeout
        $pkrct = (int) Configuration::get('PIWIK_RCOOKIE_TIMEOUT'); /* no isset if the same as default */
        if ($pkrct != 0 && $pkrct !== FALSE && ($pkrct != (int) (self::PK_RC_TIMEOUT * 60))) {
            $PIWIK_RCOOKIE_TIMEOUT = "_paq.push(['setReferralCookieTimeout', '{$pkrct}']);";
        } else {
            $PIWIK_RCOOKIE_TIMEOUT = "";
        }
        unset($pkrct);

        // setSessionCookieTimeout
        $pksct = (int) Configuration::get('PIWIK_SESSION_TIMEOUT'); /* no isset if the same as default */
        if ($pksct != 0 && $pksct !== FALSE && ($pksct != (int) (self::PK_SC_TIMEOUT * 60))) {
            $PIWIK_SESSION_TIMEOUT = "_paq.push(['setSessionCookieTimeout', '{$pksct}']);";
        } else {
            $PIWIK_SESSION_TIMEOUT = "";
        }
        unset($pksct);

        // setUserId
        $PIWIK_UUID = "";
        if ($params['cookie']->isLogged()) {
            $PIWIK_UUID = "_paq.push(['setUserId', '{$params['cookie']->id_customer}']);";
        }

        /* product tracking */
        $PIWIK_PRODUCTS = "";
        if (isset($_GET['id_product']) && Validate::isUnsignedInt($_GET['id_product'])) {
            $product = new Product($_GET['id_product'], false, (isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (int) Configuration::get('PS_LANG_DEFAULT')));
            if (Validate::isLoadedObject($product)) {
                $product_categorys = $this->get_category_names_by_product($product->id, FALSE);
                $PIWIK_PRODUCTS = "_paq.push(['setEcommerceView', '{$this->parseProductSku($product->id, FALSE, (isset($product->reference) ? $product->reference : FALSE))}', '" . htmlentities($product->name) . "', {$product_categorys}, '{$this->currencyConvertion(array('price' => Product::getPriceStatic($product->id, true, false), 'conversion_rate' => false,))}']);";
            }
        }

        /* category tracking */
        $PIWIK_CATEGORY = "";
        if (isset($_GET['id_category']) && Validate::isUnsignedInt($_GET['id_category'])) {
            $category = new Category($_GET['id_category'], (isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (int) Configuration::get('PS_LANG_DEFAULT')));
            $PIWIK_CATEGORY = "_paq.push(['setEcommerceView', false, false, '" . htmlentities($category->name) . "']);";
        }

        /* cart tracking */
        $PIWIK_CART = "";
        if (!$params['cookie']->PIWIKTrackCartFooter)
            $params['cookie']->PIWIKTrackCartFooter = time();
        if (strtotime($params['cart']->date_upd) >= $params['cookie']->PIWIKTrackCartFooter) {
            $params['cookie']->PIWIKTrackCartFooter = strtotime($params['cart']->date_upd) + 2;

            $Currency = new Currency($params['cart']->id_currency);
            $isCart = false;
            foreach ($params['cart']->getProducts() as $key => $value) {
                if (!isset($value['id_product']) || !isset($value['name']) || !isset($value['total_wt']) || !isset($value['quantity'])) {
                    continue;
                }
                $PIWIK_CART .= "\t_paq.push(['addEcommerceItem', '{$this->parseProductSku($value['id_product'], (isset($value['id_product_attribute']) && $value['id_product_attribute'] > 0 ? $value['id_product_attribute'] : FALSE), (isset($value['reference']) ? $value['reference'] : FALSE))}', '" . $value['name'] . (isset($value['attributes']) ? ' (' . $value['attributes'] . ')' : '') . "', {$this->get_category_names_by_product($value['id_product'], FALSE)}, '{$this->currencyConvertion(
                                array(
                                    'price' => $value['total_wt'],
                                    'conversion_rate' => $Currency->conversion_rate,
                                )
                        )}', '{$value['quantity']}']);" . PHP_EOL;
                $isCart = true;
            }

            if ($isCart) {
                $PIWIK_CART .= "\t_paq.push(['trackEcommerceCartUpdate', {$this->currencyConvertion(
                                array(
                                    'price' => $params['cart']->getOrderTotal(),
                                    'conversion_rate' => $Currency->conversion_rate,
                                )
                        )}]);" . PHP_EOL;
            }
        }

        $Page404 = "";
        if (strstr($_SERVER['PHP_SELF'], '404.php')) {
            $Page404 = "_paq.push(['setDocumentTitle',  '404/URL = ' +  encodeURIComponent(document.location.pathname+document.location.search) + '/From = ' + encodeURIComponent(document.referrer)]);";
        }

        $Products = $ORDER_DETAILS = "";
        $order = $params['objOrder'];
        if (Validate::isLoadedObject($order)) {
            $Products = "";
            foreach ($params['objOrder']->getProductsDetail() as $value) {
                $Products .= "_paq.push(['addEcommerceItem', "
                        . "'{$this->parseProductSku($value['product_id'], (isset($value['product_attribute_id']) ? $value['product_attribute_id'] : FALSE), (isset($value['product_reference']) ? $value['product_reference'] : FALSE))}', "
                        . "'{$value['product_name']}', "
                        . "{$this->get_category_names_by_product($value['product_id'], FALSE)}, "
                        . "'{$this->currencyConvertion(array(
                            'price' => (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : 0.00)),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        ))}', "
                        . "'{$value['product_quantity']}']);" . PHP_EOL;
            }


            if (isset($params['objOrder']->total_paid_tax_incl) && isset($params['objOrder']->total_paid_tax_excl))
                $tax = $params['objOrder']->total_paid_tax_incl - $params['objOrder']->total_paid_tax_excl;
            else if (isset($params['objOrder']->total_products_wt) && isset($params['objOrder']->total_products))
                $tax = $params['objOrder']->total_products_wt - $params['objOrder']->total_products;
            else
                $tax = 0.00;

            $ORDER_DETAILS = "_paq.push(['trackEcommerceOrder',"
                    . "'{$params['objOrder']->id}', "
                    . "'{$this->currencyConvertion(array(
                        'price' => floatval(isset($params['objOrder']->total_paid_tax_incl) ? $params['objOrder']->total_paid_tax_incl : (isset($params['objOrder']->total_paid) ? $params['objOrder']->total_paid : 0.00)),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ))}', "
                    . "'{$this->currencyConvertion(array(
                        'price' => floatval($params['objOrder']->total_products_wt),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ))}', "
                    . "'{$this->currencyConvertion(array(
                        'price' => floatval($tax),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ))}', "
                    . "'{$this->currencyConvertion(array(
                        'price' => floatval((isset($params['objOrder']->total_shipping_tax_incl) ? $params['objOrder']->total_shipping_tax_incl : (isset($params['objOrder']->total_shipping) ? $params['objOrder']->total_shipping : 0.00))),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ))}', "
                    . "'{$this->currencyConvertion(array(
                        'price' => (isset($params['objOrder']->total_discounts_tax_incl) ?
                                ($params['objOrder']->total_discounts_tax_incl > 0 ?
                                        floatval($params['objOrder']->total_discounts_tax_incl) : false) : (isset($params['objOrder']->total_discounts) ?
                                        ($params['objOrder']->total_discounts > 0 ?
                                                floatval($params['objOrder']->total_discounts) : false) : 0.00)),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ))}']);";
        }
        /*
          {$PIWIK_PRODUCTS}
          {$PIWIK_CATEGORY}
          {$PIWIK_CART}
          {$Page404}
         */
        self::$htmlout = <<< EOF
<script type="text/javascript">
    var u=(("https:" == document.location.protocol) ? "https://{$PIWIK_HOST}" : "http://{$PIWIK_HOST}");
    var _paq = _paq || [];
    {$PIWIK_DNT}
    _paq.push(['setSiteId',{$PIWIK_SITEID}]);
    _paq.push(['setTrackerUrl',u{$PKTrackerUrl}]);
    {$PIWIK_COOKIE_DOMAIN}
    {$PIWIK_SET_DOMAINS}
    {$PIWIK_COOKIE_TIMEOUT}
    {$PIWIK_RCOOKIE_TIMEOUT}
    {$PIWIK_SESSION_TIMEOUT}
    _paq.push(['enableLinkTracking']);
    {$PIWIK_UUID}
    {$Products}
    {$ORDER_DETAILS}
    _paq.push(['trackPageView']);
    (function() { var d = document, g = d.createElement("script"), s = d.getElementsByTagName("script")[0];g.type = "text/javascript";g.defer = true;g.async = true;g.src = u{$PKURL};s.parentNode.insertBefore(g, s); })();
</script>
{$PIWIK_EXHTML}
EOF;
        return self::$htmlout;
    }

    function displayErrors($errors) {
        if (!empty($errors)) {
            foreach ($errors as $key => $value) {
                $this->_errors .= $this->displayError($value);
            }
        }
    }
    /**
     * hook into admin stats page on prestashop version 1.4
     * @param array $params
     * @return string
     * @since 0.5
     */
    function hookAdminStatsModules($params) {
        $http = ((bool) Configuration::get('PIWIK_CRHTTPS') ? 'https://' : 'http://');
        $PIWIK_HOST = Configuration::get('PIWIK_HOST');
        $PIWIK_SITEID = (int) Configuration::get('PIWIK_SITEID');
        $PIWIK_TOKEN_AUTH = Configuration::get('PIWIK_TOKEN_AUTH');
        if ((empty($PIWIK_HOST) || $PIWIK_HOST === FALSE) ||
                ($PIWIK_SITEID <= 0 || $PIWIK_SITEID === FALSE) ||
                (empty($PIWIK_TOKEN_AUTH) || $PIWIK_TOKEN_AUTH === FALSE))
            return "<h3>{$this->l("You need to set 'Piwik host url', 'Piwik token auth' and 'Piwik site id', and save them before the dashboard can be shown here")}</h3>";
        $lng = new Language($params['cookie']->id_lang);

        $user = Configuration::get('PIWIK_USRNAME');
        $passwd = Configuration::get('PIWIK_USRPASSWD');
        if ((!empty($user) && $user !== FALSE) && (!empty($passwd) && $passwd !== FALSE))
            $PKUILINK = $http . $PIWIK_HOST . 'index.php?module=Login&action=logme&login=' . $user . '&password=' . md5($passwd) . '&idSite=' . $PIWIK_SITEID;
        else
            $PKUILINK = $http . $PIWIK_HOST . 'index.php';

        $DREPDATE = Configuration::get('PIWIK_DREPDATE');
        if ($DREPDATE !== FALSE && (strpos($DREPDATE, '|') !== FALSE)) {
            list($period, $date) = explode('|', $DREPDATE);
        } else {
            $period = "day";
            $date = "today";
        }

        $html = '<script type="text/javascript">function WidgetizeiframeDashboardLoaded() {var w = $(\'#content\').width();var h = $(\'body\').height();$(\'#WidgetizeiframeDashboard\').width(\'100%\');$(\'#WidgetizeiframeDashboard\').height(h);}</script>'
                . '<fieldset class="width3">'
                . '<legend><img src="../modules/' . $this->name . '/logo.gif" /> ' . $this->displayName . ''
                . ' | <a target="_blank" href="' . $PKUILINK . '">' . $this->l('Piwik') . '</a>'
                . ' | <a target="_blank" href="https://github.com/cmjnisse/piwikanalyticsjs-prestashop/wiki">' . $this->l('Help') . '</a>'
                . '</legend>'
                . '<iframe id="WidgetizeiframeDashboard"  onload="WidgetizeiframeDashboardLoaded();" '
                . 'src="' . $http . $PIWIK_HOST . 'index.php'
                . '?module=Widgetize'
                . '&action=iframe'
                . '&moduleToWidgetize=Dashboard'
                . '&actionToWidgetize=index'
                . '&idSite=' . $PIWIK_SITEID
                . '&period=' . $period
                . '&language=' . $lng->iso_code
                . '&token_auth=' . $PIWIK_TOKEN_AUTH
                . '&date=' . $date
                . '" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="550px"></iframe>'
                . '</fieldset>';
        return $html;
    }

    function get_category_names_by_product($id, $array = true) {
        if (method_exists('Product', 'getProductCategoriesFull')) {
            $_categories = Product::getProductCategoriesFull($id, $this->context->cookie->id_lang);
        } else {
            $_categories = array();
            $cc = 0;
            $tmpIDs = Product::getIndexedCategories($id);
            foreach ($tmpIDs as $key => $value) {
                $_categories[] = new Category($value['id_category']);
                $cc++;
                if ($cc == 5) {
                    break; /* Piwik is limited to max 5 categories */
                }
            }
        }


        if (!is_array($_categories)) {
            if ($array)
                return array();
            else
                return "[]";
        }
        if ($array) {
            $categories = array();
            foreach ($_categories as $category) {
                $categories[] = $category->name[(isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (int) Configuration::get('PS_LANG_DEFAULT'))];
                if (count($categories) == 5)
                    break;
            }
        } else {
            $categories = '[';
            $c = 0;
            foreach ($_categories as $category) {
                $c++;
                $categories .= '"' . $category->name[(isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (int) Configuration::get('PS_LANG_DEFAULT'))] . '",';
                if ($c == 5)
                    break;
            }
            $categories = rtrim($categories, ',');
            $categories .= ']';
        }
        return $categories;
    }

    function currencyConvertion($params) {
        $pkc = Configuration::get("PIWIK_DEFAULT_CURRENCY");
        if (empty($pkc))
            return (float) $params['price'];
        if ($params['conversion_rate'] === FALSE || $params['conversion_rate'] == 0.00 || $params['conversion_rate'] == 1.00) {
            //* shop default
            return Tools::convertPrice((float) $params['price'], Currency::getCurrencyInstance((int) (Currency::getIdByIsoCode($pkc))));
        } else {
            $_shop_price = (float) ((float) $params['price'] / (float) $params['conversion_rate']);
            return Tools::convertPrice($_shop_price, Currency::getCurrencyInstance((int) (Currency::getIdByIsoCode($pkc))));
        }
        return (float) $params['price'];
    }

    function parseProductSku($id, $attrid = FALSE, $ref = FALSE) {
        if (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== FALSE) && (!empty($ref) && !is_null($ref) && $ref !== FALSE)) {
            $PIWIK_PRODID_V1 = Configuration::get('PIWIK_PRODID_V1');
            return str_replace(array('{ID}', '{ATTRID}', '{REFERENCE}'), array($id, $attrid, $ref), $PIWIK_PRODID_V1);
        } elseif (Validate::isInt($id) && (!empty($ref) && !is_null($ref) && $ref !== FALSE)) {
            $PIWIK_PRODID_V2 = Configuration::get('PIWIK_PRODID_V2');
            return str_replace(array('{ID}', '{REFERENCE}'), array($id, $ref), $PIWIK_PRODID_V2);
        } elseif (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== FALSE)) {
            $PIWIK_PRODID_V3 = Configuration::get('PIWIK_PRODID_V3');
            return str_replace(array('{ID}', '{ATTRID}'), array($id, $attrid), $PIWIK_PRODID_V3);
        } else {
            return $id;
        }
    }

}
