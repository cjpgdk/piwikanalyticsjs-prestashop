<?php

if (!defined('_PS_VERSION_'))
    exit;

if (class_exists('PiwikWizardHelper', FALSE))
    return;

/*
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
 */

class PiwikWizardHelper {

    public static $password = false;
    public static $username = false;
    public static $usernamehttp = false;
    public static $passwordhttp = false;
    public static $piwikhost = false;
    public static $errors = array();
    public static $strings = array(
        '4ddd9129714e7146ed2215bcbd559335' => "I encountered an unknown error while trying to get the selected site, id #%s",
        'e41246ca9fd83a123022c5c5b7a6f866' => "I'm unable, to get admin access to the selected site id #%s",
        '8a7d6b386e97596cb28878e9be5804b8' => "Piwik sitename is missing",
        '7948cb754538ab57b44c956c22aa5517' => "Piwik main url is missing",
        '0f30ab07916f20952f2e6ef70a91d364' => "Piwik currency is missing",
        'f7472169e468dd1fd901720f4bae1957' => "Piwik timezone is missing",
        '75e263846f84003f0180137e79542d38' => "Error while creating site in piwik please check the following messages for clues",
    );

    public static function createNewSite() {
        if (Tools::isSubmit('PKNewSiteSubmit')) {
            /* 
             * set the POSTed password for http auth and piwik auth so we can create the new site.
             * @todo add seperated functions, or just take whats posted for this to avoid multiple overides.!
             */
            PiwikWizardHelper::getFormValuesInternal(true);
            /*
              Array
              (
              [PIWIK_STEP_WIZARD] => 2
              [PIWIK_HOST_WIZARD] => http://ps.dev.------/piwik/piwik-2.0/
              [PIWIK_USRNAME_WIZARD] => cmj
              [PIWIK_USRPASSWD_WIZARD] => -----------
              [PIWIK_PAUTHUSR_WIZARD] => christian
              [PIWIK_PAUTHPWD_WIZARD] => ----------
             * 
              ) */
            if (Tools::getIsset('PKNewSiteName'))
                $siteName = Tools::getValue('PKNewSiteName');
            else {
                PiwikWizardHelper::$errors[] = PiwikWizardHelper::$strings['8a7d6b386e97596cb28878e9be5804b8'];
                return FALSE;
            }
            if (Tools::getIsset('PKNewMainUrl'))
                $urls = Tools::getValue('PKNewMainUrl');
            else {
                PiwikWizardHelper::$errors[] = PiwikWizardHelper::$strings['7948cb754538ab57b44c956c22aa5517'];
                return FALSE;
            }
            if (Tools::getIsset('PKNewEcommerce'))
                $ecommerce = (Tools::getValue('PKNewEcommerce') == 'on') ? 1 : 0;
            if (Tools::getIsset('PKNewSiteSearch'))
                $siteSearch = (Tools::getValue('PKNewSiteSearch') == 'on') ? 1 : 0;
            if (Tools::getIsset('PKNewSearchKeywordParameters'))
                $searchKeywordParameters = Tools::getValue('PKNewSearchKeywordParameters');
            if (Tools::getIsset('PKNewSearchCategoryParameters'))
                $searchCategoryParameters = Tools::getValue('PKNewSearchCategoryParameters');
            if (Tools::getIsset('PKNewExcludedIps'))
                $excludedIps = Tools::getValue('PKNewExcludedIps');
            if (Tools::getIsset('PKNewExcludedQueryParameters'))
                $excludedQueryParameters = Tools::getValue('PKNewExcludedQueryParameters');
            if (Tools::getIsset('PKNewTimezone'))
                $timezone = Tools::getValue('PKNewTimezone');
            else {
                PiwikWizardHelper::$errors[] = PiwikWizardHelper::$strings['f7472169e468dd1fd901720f4bae1957'];
                return FALSE;
            }
            if (Tools::getIsset('PKNewCurrency'))
                $currency = Tools::getValue('PKNewCurrency');
            else {
                PiwikWizardHelper::$errors[] = PiwikWizardHelper::$strings['0f30ab07916f20952f2e6ef70a91d364'];
                return FALSE;
            }
            if (Tools::getIsset('PKNewExcludedUserAgents'))
                $excludedUserAgents = Tools::getValue('PKNewExcludedUserAgents');
            if (Tools::getIsset('PKNewKeepURLFragments'))
                $keepURLFragments = Tools::getValue('PKNewKeepURLFragments');

            if ($siteId = PKHelper::addPiwikSite($siteName, $urls, $ecommerce, $siteSearch, $searchKeywordParameters, $searchCategoryParameters, $excludedIps, $excludedQueryParameters, $timezone, $currency, "", "", $excludedUserAgents, $keepURLFragments)) {

                Configuration::updateValue(PKHelper::CPREFIX . 'SITEID', $siteId);
                Tools::redirectAdmin(AdminController::$currentIndex . '&configure=piwikanalyticsjs&token=' . Tools::getAdminTokenLite('AdminModules'));
                return true;
            } else
                PiwikWizardHelper::$errors[] = PiwikWizardHelper::$strings['75e263846f84003f0180137e79542d38'];
            PiwikWizardHelper::$errors = array_merge(PiwikWizardHelper::$errors, PKHelper::$errors);
            PKHelper::$errors = PKHelper::$error = "";
            return false;
        }
    }

    // get username password etc from posted form and set the propper vars
    public static function getFormValuesInternal($set_helper = false) {
        if (Tools::getIsset(PKHelper::CPREFIX . 'USRNAME_WIZARD'))
            PiwikWizardHelper::$username = Tools::getValue(PKHelper::CPREFIX . 'USRNAME_WIZARD');
        if (Tools::getIsset(PKHelper::CPREFIX . 'USRPASSWD_WIZARD'))
            PiwikWizardHelper::$password = Tools::getValue(PKHelper::CPREFIX . 'USRPASSWD_WIZARD');
        if (Tools::getIsset(PKHelper::CPREFIX . 'HOST_WIZARD'))
            PiwikWizardHelper::$piwikhost = Tools::getValue(PKHelper::CPREFIX . 'HOST_WIZARD');
        if (Tools::getIsset(PKHelper::CPREFIX . 'PAUTHUSR_WIZARD'))
            PiwikWizardHelper::$usernamehttp = Tools::getValue(PKHelper::CPREFIX . 'PAUTHUSR_WIZARD', 0);
        if (Tools::getIsset(PKHelper::CPREFIX . 'PAUTHPWD_WIZARD'))
            PiwikWizardHelper::$passwordhttp = Tools::getValue(PKHelper::CPREFIX . 'PAUTHPWD_WIZARD', 0);
        if ($set_helper) {
            PKHelper::$httpAuthUsername = (PiwikWizardHelper::$usernamehttp !== false ? PiwikWizardHelper::$usernamehttp : '');
            PKHelper::$httpAuthPassword = (PiwikWizardHelper::$passwordhttp !== false ? PiwikWizardHelper::$passwordhttp : '');
            PKHelper::$piwikHost = str_replace(array('http://', 'https://'), '', PiwikWizardHelper::$piwikhost);
            if (substr(PKHelper::$piwikHost, -1) != "/") {
                PKHelper::$piwikHost .= "/";
            }
        }
    }

    // set piwik site to use & redirect or set errors if any
    public static function setUsePiwikSite($currentIndex) {
        if (Tools::getIsset('usePiwikSite') && ((int) Tools::getValue('usePiwikSite') != 0)) {
            $pksiteid = (int) Tools::getValue('usePiwikSite');
            if ($pksite = PKHelper::getPiwikSite2($pksiteid)) {
                if (isset($pksite[0]) && is_object($pksite[0])) {
                    /* we run update to enforce search and ecommerce */
                    PKHelper::updatePiwikSite(
                            $pksiteid /* $idSite */,
                            $pksite[0]->name /* $siteName */,
                            $pksite[0]->main_url /* $urls */,
                            1 /* $ecommerce */, 1 /* $siteSearch */,
                            $pksite[0]->sitesearch_keyword_parameters /* $searchKeywordParameters */,
                            $pksite[0]->sitesearch_category_parameters /* $searchCategoryParameters */,
                            $pksite[0]->excluded_ips /* $excludedIps */,
                            $pksite[0]->excluded_parameters /* $excludedQueryParameters */,
                            $pksite[0]->timezone /* $timezone */,
                            $pksite[0]->currency /* $currency */,
                            $pksite[0]->group /* $group */,
                            $pksite[0]->ts_created /* $startDate */,
                            $pksite[0]->excluded_user_agents /* $excludedUserAgents */,
                            $pksite[0]->keep_url_fragment /* $keepURLFragments */,
                            $pksite[0]->type /* $type */);
                    PKHelper::$error = PKHelper::$errors = null; // don't need them, we redirect
                    // save site id
                    Configuration::updateValue(PKHelper::CPREFIX . 'SITEID', $pksiteid);
                    Tools::redirectAdmin(str_replace('&pkwizard', '', $currentIndex) . "&token=" . Tools::getAdminTokenLite('AdminModules'));
                } else {
                    PiwikWizardHelper::$errors[] = sprintf(PiwikWizardHelper::$strings['4ddd9129714e7146ed2215bcbd559335'], $pksiteid);
                    PiwikWizardHelper::$password = Configuration::get(PKHelper::CPREFIX . "USRPASSWD");
                    PiwikWizardHelper::$username = Configuration::get(PKHelper::CPREFIX . "USRNAME");
                    PiwikWizardHelper::$usernamehttp = Configuration::get(PKHelper::CPREFIX . 'PAUTHUSR');
                    PiwikWizardHelper::$passwordhttp = Configuration::get(PKHelper::CPREFIX . 'PAUTHPWD');
                    PiwikWizardHelper::$piwikhost = Configuration::get(PKHelper::CPREFIX . 'HOST');
                }
            } else {
                PiwikWizardHelper::$errors[] = sprintf(PiwikWizardHelper::$strings['e41246ca9fd83a123022c5c5b7a6f866'], $pksiteid);
                PiwikWizardHelper::$password = Configuration::get(PKHelper::CPREFIX . "USRPASSWD");
                PiwikWizardHelper::$username = Configuration::get(PKHelper::CPREFIX . "USRNAME");
                PiwikWizardHelper::$usernamehttp = Configuration::get(PKHelper::CPREFIX . 'PAUTHUSR');
                PiwikWizardHelper::$passwordhttp = Configuration::get(PKHelper::CPREFIX . 'PAUTHPWD');
                PiwikWizardHelper::$piwikhost = Configuration::get(PKHelper::CPREFIX . 'HOST');
            }
        }
    }

}
