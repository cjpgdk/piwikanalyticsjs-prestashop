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
        '821dc1363c6c3a23185ea0cf3bee5261' => "Select a site.",
        '4ddd9129714e7146ed2215bcbd559335' => "I encountered an unknown error while trying to get the selected site, id#%s",
        'e41246ca9fd83a123022c5c5b7a6f866' => "I'm unable, to get admin access to the selected site id #%s",
        'ea4788705e6873b424c65e91c2846b19' => "Cancel", '00617256bf279d54780075598d7e958c' => "Create new Site",
        '3c68005461c0d81c1626c01c9aa400e0' => "Unable to get a list of websites from Piwik, if you dont have any sites in piwik yet click 'Create new Site' button.",
        '10ac3d04253ef7e1ddc73e6091c0cd55' => 'Next',
        '11552a6e511d4ab1ee43f2e0ab9d623f' => 'this field along with username can be used if piwik installation is protected by HTTP Basic Authorization',
        'e4a1b909bb1918a40f18d8dfb013fd28' => 'HTTP Auth Password', '4b3e9319a9c0c328221080116e0d5104' => 'HTTP Auth Username',
        '7965ca87322fb45ebc60071041580e8f' => "HTTP Basic Authorization",
        'b9f5c797ebbf55adccdd8539a65a0241' => 'Disabled', '00d23a76e43b46dae9ec7aa9dcbebb32' => 'Enabled',
        '3b6761cfe4215c632072f87259970d84' => 'Whether or not to save the username and password, saving the username and password will enable quick(automatic) login to piwik from the integrated stats page',
        'ea88860b951ee567e988d794ef0ca090' => 'Save username and password',
        '47f896968366f1688c401fece093c2d1' => 'Enter your password for Piwik, we need this in order to fetch your api authentication token',
        '3eb1a362b1cfc065415c6f31730bfd84' => 'Piwik User password',
        '8e70216e1e56d8d2f7e3cd229171ba1f' => 'Enter your username for Piwik, we need this in order to fetch your api authentication token',
        '4081cf3a3e78277c30f0acd948082cb8' => 'Piwik User name',
        '6fbd6e012c9a1a4b2f0796196d060e6d' => 'The full url to your piwik installation.!',
        '6752ab12af9a9878bf9d08c751ac2aa5' => 'Example: http://www.example.com/piwik/',
        '3c6805325f65f0ee32244920e46aac39' => 'Piwik Host',
    );

    // wizard step 2 ?init
    public static function pkws2(& $step, & $pkToken, & $pkSites, & $fields_form, & $helperform) {
        if ($step == 2) {
            PKHelper::$httpAuthUsername = (PiwikWizardHelper::$usernamehttp !== false ? PiwikWizardHelper::$usernamehttp : '');
            PKHelper::$httpAuthPassword = (PiwikWizardHelper::$passwordhttp !== false ? PiwikWizardHelper::$passwordhttp : '');
            PKHelper::$piwikHost = str_replace(array('http://', 'https://'), '', PiwikWizardHelper::$piwikhost);
            $pkToken = PKHelper::getTokenAuth(PiwikWizardHelper::$username, PiwikWizardHelper::$password);
            if (!empty(PKHelper::$error)) {
                foreach (PKHelper::$errors as $value)
                    PiwikWizardHelper::$errors[] = $value;
                $step = 1;
            } else {
                if ($pkToken !== false) {
                    // wee need to saved the token to avoid error from PKHelper class
                    // @todo allow token overide 'PKHelper::getSitesWithAdminAccess' (skip missing error)
                    Configuration::updateValue(PKHelper::CPREFIX . 'TOKEN_AUTH', $pkToken);
                    if ($_pkSites = PKHelper::getSitesWithAdminAccess(false, array(
                                'idSite' => 0, 'pkHost' => PKHelper::$piwikHost,
                                'https' => (strpos(PiwikWizardHelper::$piwikhost, 'https://') !== false),
                                'pkModule' => 'API', 'isoCode' => NULL, 'tokenAuth' => $pkToken))) {
                        if (!empty($_pkSites)) {
                            foreach ($_pkSites as $value) {
                                $pkSites[] = array(
                                    'idsite' => $value->idsite,
                                    'name' => $value->name,
                                    'main_url' => $value->main_url,
                                );
                            }
                        }
                        unset($_pkSites);
                    }
                    if (!empty(PKHelper::$error)) {
                        foreach (PKHelper::$errors as $key => $value)
                            PiwikWizardHelper::$errors[] = $value;
                        $step = 1;
                    }
                }
            }
        }
        if ($step == 2 && is_array($pkSites) && !empty($pkSites)) {
            $fields_form[0]['form']['input'][] = array('type' => 'html', 'name' => "<strong>" . PiwikWizardHelper::$strings['821dc1363c6c3a23185ea0cf3bee5261'] . "</strong>");

            foreach ($pkSites as $key => $value) {
                $fields_form[0]['form']['input'][] = array(
                    'name' => '', 'type' => 'myBtn',
                    'href' => $helperform->currentIndex . '&usePiwikSite=' . $value['idsite'] . "&token=" . Tools::getAdminTokenLite('AdminModules'),
                    'id' => 'slectedpksid_' . $value['idsite'], 'icon' => 'icon-chevron-right',
                    'title' => '#' . $value['idsite'] . ' - ' . $value['name'] . ' [<strong>' . $value['main_url'] . '</strong>]',
                );
            }

            $fields_form[0]['form']['buttons'][] = array(
                'title' => PiwikWizardHelper::$strings['ea4788705e6873b424c65e91c2846b19'],
                'icon' => 'process-icon-cancel', 'class' => ' donotdisable',
                'ps15style' => 'font-size: 14px; padding: 5px 10px;', 'type' => 'button',
                'href' => str_replace('&pkwizard', '', $helperform->currentIndex) . "&token=" . Tools::getAdminTokenLite('AdminModules'),
                'id' => 'btnCreateNewSite', 'name' => 'btnCreateNewSite',
            );
            $fields_form[0]['form']['buttons'][] = array(
                'title' => PiwikWizardHelper::$strings['00617256bf279d54780075598d7e958c'],
                'icon' => 'process-icon-new', 'class' => '  pull-right donotdisable',
                'type' => 'button', 'id' => 'btnCreateNewSite',
                'name' => 'btnCreateNewSite',
                'js' => "alert('this functionality is not implemented yet, i apologise for the inconvenience')",
            );
        } else if ($step == 2) {
            $step = 1;
            PiwikWizardHelper::$errors[] = PiwikWizardHelper::$strings['3c68005461c0d81c1626c01c9aa400e0'];
        }
    }

    // wizard step 1
    public static function pkws1(& $step, & $fields_form, & $helperform) {
        if ($step == 1) {
            $fields_form[0]['form']['input'][] = array(
                'type' => 'text', 'required' => true,
                'label' => PiwikWizardHelper::$strings['3c6805325f65f0ee32244920e46aac39'],
                'name' => PKHelper::CPREFIX . 'HOST_WIZARD',
                'desc' => PiwikWizardHelper::$strings['6752ab12af9a9878bf9d08c751ac2aa5'],
                'hint' => PiwikWizardHelper::$strings['6fbd6e012c9a1a4b2f0796196d060e6d'],
            );
            $fields_form[0]['form']['input'][] = array(
                'type' => 'text', 'required' => true, 'autocomplete' => false,
                'name' => PKHelper::CPREFIX . 'USRNAME_WIZARD',
                'label' => PiwikWizardHelper::$strings['4081cf3a3e78277c30f0acd948082cb8'],
                'desc' => PiwikWizardHelper::$strings['8e70216e1e56d8d2f7e3cd229171ba1f'],
            );
            $fields_form[0]['form']['input'][] = array(
                'type' => 'password', 'required' => true, 'autocomplete' => false,
                'name' => PKHelper::CPREFIX . 'USRPASSWD_WIZARD',
                'label' => PiwikWizardHelper::$strings['3eb1a362b1cfc065415c6f31730bfd84'],
                'desc' => PiwikWizardHelper::$strings['47f896968366f1688c401fece093c2d1'],
            );
            $fields_form[0]['form']['input'][] = array(
                'type' => 'switch', 'is_bool' => true, 'required' => false,
                'label' => PiwikWizardHelper::$strings['ea88860b951ee567e988d794ef0ca090'],
                'name' => PKHelper::CPREFIX . 'SAVE_USRPWD_WIZARD',
                'desc' => PiwikWizardHelper::$strings['3b6761cfe4215c632072f87259970d84'],
                'values' => array(
                    array('id' => 'active_on', 'value' => 1, 'label' => PiwikWizardHelper::$strings['00d23a76e43b46dae9ec7aa9dcbebb32']),
                    array('id' => 'active_off', 'value' => 0, 'label' => PiwikWizardHelper::$strings['b9f5c797ebbf55adccdd8539a65a0241'])
                ),
            );
            $fields_form[0]['form']['input'][] = array('type' => 'html', 'name' => "<strong>" . PiwikWizardHelper::$strings['7965ca87322fb45ebc60071041580e8f'] . "</strong>");

            $fields_form[0]['form']['input'][] = array(
                'type' => 'text',
                'label' => PiwikWizardHelper::$strings['4b3e9319a9c0c328221080116e0d5104'],
                'name' => PKHelper::CPREFIX . 'PAUTHUSR_WIZARD',
                'required' => false, 'autocomplete' => false,
                'desc' => PiwikWizardHelper::$strings['11552a6e511d4ab1ee43f2e0ab9d623f'],
            );
            $fields_form[0]['form']['input'][] = array(
                'label' => PiwikWizardHelper::$strings['e4a1b909bb1918a40f18d8dfb013fd28'],
                'type' => 'password', 'name' => PKHelper::CPREFIX . 'PAUTHPWD_WIZARD',
                'required' => false, 'autocomplete' => false,
                'desc' => PiwikWizardHelper::$strings['11552a6e511d4ab1ee43f2e0ab9d623f'],
            );
            $fields_form[0]['form']['submit'] = array(
                'title' => PiwikWizardHelper::$strings['10ac3d04253ef7e1ddc73e6091c0cd55'],
                'icon' => 'process-icon-next', 'class' => (version_compare(_PS_VERSION_, '1.6', '>=') ? 'btn btn-default' : 'button'),
            );
            $fields_form[0]['form']['buttons'][] = array(
                'title' => PiwikWizardHelper::$strings['ea4788705e6873b424c65e91c2846b19'],
                'icon' => 'process-icon-cancel', 'class' => ' donotdisable',
                'ps15style' => 'font-size: 14px; padding: 5px 10px;', 'type' => 'button',
                'href' => str_replace('&pkwizard', '', $helperform->currentIndex) . "&token=" . Tools::getAdminTokenLite('AdminModules'),
                'id' => 'btnCreateNewSite', 'name' => 'btnCreateNewSite',
            );
            $fields_form[0]['form']['buttons'][] = array(
                'title' => PiwikWizardHelper::$strings['00617256bf279d54780075598d7e958c'],
                'icon' => 'process-icon-new', 'class' => '  pull-right donotdisable', 'type' => 'button',
                'id' => 'btnCreateNewSite', 'name' => 'btnCreateNewSite',
                'js' => "alert('this functionality is not implemented yet, i apologise for the inconvenience')",
            );
        }
    }

    // get username password etc from posted form and set the propper vars
    public static function getFormValuesInternal() {
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
    }

    // set piwik site to use & redirect or set errors if any
    public static function setUsePiwikSite(&$helperform) {
        if (Tools::getIsset('usePiwikSite') && ((int) Tools::getValue('usePiwikSite') != 0)) {
            $pksiteid = (int) Tools::getValue('usePiwikSite');
            if ($pksite = PKHelper::getPiwikSite2($pksiteid)) {
                if (isset($pksite[0]) && is_object($pksite[0])) {
                    PKHelper::updatePiwikSite(
                            $pksiteid /* $idSite */, $pksite[0]->name /* $siteName */, $pksite[0]->main_url /* $urls */, 1 /* $ecommerce */, 1 /* $siteSearch */, $pksite[0]->sitesearch_keyword_parameters /* $searchKeywordParameters */, $pksite[0]->sitesearch_category_parameters /* $searchCategoryParameters */, $pksite[0]->excluded_ips /* $excludedIps */, $pksite[0]->excluded_parameters /* $excludedQueryParameters */, $pksite[0]->timezone /* $timezone */, $pksite[0]->currency /* $currency */, $pksite[0]->group /* $group */, $pksite[0]->ts_created /* $startDate */, $pksite[0]->excluded_user_agents /* $excludedUserAgents */, $pksite[0]->keep_url_fragment /* $keepURLFragments */, $pksite[0]->type /* $type */);
                    PKHelper::$error = PKHelper::$errors = null; // don't need them, we redirect
                    // save site id
                    Configuration::updateValue(PKHelper::CPREFIX . 'SITEID', $pksiteid);
                    Tools::redirectAdmin(str_replace('&pkwizard', '', $helperform->currentIndex) . "&token=" . Tools::getAdminTokenLite('AdminModules'));
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
