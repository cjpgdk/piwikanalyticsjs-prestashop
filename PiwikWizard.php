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
        'b817abd7e8364a16b7edfcc78e74558e' => 'Piwik Site Name',
        '2cbae2cc76d6994fee1bb84712069eb7' => 'Main Url',
        '82419044af129bcd8894f7d208f4dd2b' => 'Addtional Urls',
        '34eea1731773212b3234ef8048dbee1e' => 'Is this site an ecommerce site?',
        '53ef2022ee91ccf50dd8b63da5a563b9' => 'Ecommerce',
        '93cba07454f06a4a960172bbd6e2a435' => 'Yes',
        'bafd7322c6e97d25b6299b5d6fe8920b' => 'No',
        '871e94256265ecc5d2ca1f9b42f861ac' => 'Site Search',
        '0eccdaa003c737691fe1153ea0a4550f' => 'Search Keyword Parameters',
        '28235d8369c0f9b740f83e25c4fe2f1d' => 'keyword parameters must be excluded to avoid normal page views to be interpreted as searches (the tracking code will see them and make the required postback to Piwik if it is a real search), if you are only using PrestaShop with this site setting this to empty, will be sufficient',
        'f4e283ffb009bdda02a06737c25bd93c' => 'Search Category Parameters',
        'dc1317a4a93a700507570dfd69c757d9' => 'Excluded ip addresses',
        '3443f50780c66a77394a10925b76bed7' => 'ip addresses excluded from tracking, separated by comma ","',
        '510940abcdfeff622ba993e36f47519f' => 'Excluded Query Parameters',
        '0040b52be769bb81e1e5d2051b7f6652' => 'please read: http://piwik.org/faq/how-to/faq_81/',
        '236df51bb0e6416236e255b528346fca' => 'Timezone',
        '63ce9117e223ae7871044b39e2ca28be' => 'The timezone for this site',
        '6ba290764cb95fbe109e7f3b317865ad' => 'Choose Timezone',
        '386c339d37e737a436499d423a77df0c' => 'Currency',
        '70cd4d21141d3d3198c8a606303d454b' => 'The currency for this site',
        'a7b3bae411492841bb245cee3ddcc599' => 'Excluded User Agents',
        '7a42168f46cbbe0357bcc36205123080' => 'please read: http://piwik.org/faq/how-to/faq_17483/',
        'a9d005356a04262c95dc815b96a65038' => 'Keep URL Fragments',
        '686e697538050e4664636337cc3b834f' => 'Create',
        '157c966cf06d25578931a8c74298c332' => 'Name of this site in Piwik',
    );

    // wizard create new site form
    public static function pkns(& $fields_form, & $helperform, $currencies, $default_currency) {

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => PiwikWizardHelper::$strings['b817abd7e8364a16b7edfcc78e74558e'],
            'name' => 'PKNewSiteName',
            'desc' => PiwikWizardHelper::$strings['157c966cf06d25578931a8c74298c332'],
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => PiwikWizardHelper::$strings['2cbae2cc76d6994fee1bb84712069eb7'],
            'name' => 'PKNewMainUrl',
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => PiwikWizardHelper::$strings['82419044af129bcd8894f7d208f4dd2b'],
            'name' => 'PKNewAddtionalUrls',
            'class' => 'tagify',
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'switch',
            'is_bool' => true,
            'label' => PiwikWizardHelper::$strings['53ef2022ee91ccf50dd8b63da5a563b9'],
            'name' => 'PKNewEcommerce',
            'desc' => PiwikWizardHelper::$strings['34eea1731773212b3234ef8048dbee1e'],
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => PiwikWizardHelper::$strings['93cba07454f06a4a960172bbd6e2a435']
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => PiwikWizardHelper::$strings['bafd7322c6e97d25b6299b5d6fe8920b']
                )
            ),
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'switch',
            'is_bool' => true,
            'label' => PiwikWizardHelper::$strings['871e94256265ecc5d2ca1f9b42f861ac'],
            'name' => 'PKNewSiteSearch',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => PiwikWizardHelper::$strings['93cba07454f06a4a960172bbd6e2a435']
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => PiwikWizardHelper::$strings['bafd7322c6e97d25b6299b5d6fe8920b']
                )
            ),
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text', 'class' => 'tagify',
            'label' => PiwikWizardHelper::$strings['0eccdaa003c737691fe1153ea0a4550f'],
            'name' => 'PKNewSearchKeywordParameters',
            'desc' => "<strong>tag</strong> & <strong>search_query</strong> " . PiwikWizardHelper::$strings['28235d8369c0f9b740f83e25c4fe2f1d'],
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text', 'class' => 'tagify',
            'label' => PiwikWizardHelper::$strings['f4e283ffb009bdda02a06737c25bd93c'],
            'name' => 'PKNewSearchCategoryParameters',
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text', 'class' => 'tagify',
            'label' => PiwikWizardHelper::$strings['dc1317a4a93a700507570dfd69c757d9'],
            'name' => 'PKNewExcludedIps',
            'desc' => PiwikWizardHelper::$strings['3443f50780c66a77394a10925b76bed7'],
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text', 'class' => 'tagify',
            'label' => PiwikWizardHelper::$strings['510940abcdfeff622ba993e36f47519f'],
            'name' => 'PKNewExcludedQueryParameters',
            'desc' => PiwikWizardHelper::$strings['0040b52be769bb81e1e5d2051b7f6652'],
        );

        $pktimezones = array();
        $tmp = PKHelper::getTimezonesList();
        if (!empty(PKHelper::$error))
            foreach (PKHelper::$errors as $value)
                PiwikWizardHelper::$errors[] = $value;
        PKHelper::$errors = PKHelper::$error = "";
        foreach ($tmp as $key => $pktz) {
            if (!isset($pktimezones[$key]))
                $pktimezones[$key] = array('name' => $key, 'query' => array());
            foreach ($pktz as $pktzK => $pktzV)
                $pktimezones[$key]['query'][] = array('tzId' => $pktzK, 'tzName' => $pktzV);
        }

        $fields_form[0]['form']['input'][] = array(
            'type' => 'select',
            'label' => PiwikWizardHelper::$strings['236df51bb0e6416236e255b528346fca'],
            'name' => 'PKNewTimezone',
            'desc' => PiwikWizardHelper::$strings['63ce9117e223ae7871044b39e2ca28be'],
            'options' => array(
                'default' => array('value' => 0, 'label' => PiwikWizardHelper::$strings['6ba290764cb95fbe109e7f3b317865ad']),
                'optiongroup' => array('label' => 'name', 'query' => $pktimezones),
                'options' => array('id' => 'tzId', 'name' => 'tzName', 'query' => 'query'),
            ),
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'select',
            'label' => PiwikWizardHelper::$strings['386c339d37e737a436499d423a77df0c'],
            'name' => 'PKNewCurrency',
            'desc' => PiwikWizardHelper::$strings['70cd4d21141d3d3198c8a606303d454b'],
            'options' => array('default' => $default_currency, 'query' => $currencies, 'id' => 'iso_code', 'name' => 'name'),
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'textarea',
            'label' => PiwikWizardHelper::$strings['a7b3bae411492841bb245cee3ddcc599'],
            'name' => 'PKNewExcludedUserAgents',
            'rows' => 10, 'cols' => 50,
            'desc' => PiwikWizardHelper::$strings['7a42168f46cbbe0357bcc36205123080'],
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'switch',
            'is_bool' => true,
            'label' => PiwikWizardHelper::$strings['a9d005356a04262c95dc815b96a65038'],
            'name' => 'PKNewKeepURLFragments',
            'values' => array(
                array('id' => 'active_on', 'value' => 1, 'label' => PiwikWizardHelper::$strings['93cba07454f06a4a960172bbd6e2a435']),
                array('id' => 'active_off', 'value' => 0, 'label' => PiwikWizardHelper::$strings['bafd7322c6e97d25b6299b5d6fe8920b'])
            ),
        );
        $fields_form[0]['form']['submit'] = array('title' => PiwikWizardHelper::$strings['686e697538050e4664636337cc3b834f'], 'class' => 'btn btn-default', 'id' => 'PKNewSiteSubmit');

        $helperform->fields_value = array(
            'PKNewSiteName' => Configuration::get('PS_SHOP_NAME'), 'PKNewAddtionalUrls' => '', 'PKNewEcommerce' => 1,
            'PKNewSiteSearch' => 1, 'PKNewKeepURLFragments' => 0, 'PKNewTimezone' => 'UTC',
            'PKNewSearchKeywordParameters' => '', 'PKNewExcludedUserAgents' => '', 'PKNewSearchCategoryParameters' => '',
            'PKNewCurrency' => Context::getContext()->currency->iso_code, 'PKNewExcludedQueryParameters' => '',
            'PKNewMainUrl' => Tools::getShopDomainSsl(true, true) . Context::getContext()->shop->getBaseURI(),
            'PKNewExcludedIps' => Configuration::get('PS_MAINTENANCE_IP'),
        );
    }

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
                PKHelper::$errors = PKHelper::$error = "";
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
                        PKHelper::$errors = PKHelper::$error = "";
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
                'id' => 'btnCancel', 'name' => 'btnCancel',
            );
            $fields_form[0]['form']['buttons'][] = array(
                'title' => PiwikWizardHelper::$strings['00617256bf279d54780075598d7e958c'],
                'icon' => 'process-icon-new', 'class' => '  pull-right donotdisable',
                'type' => 'button', 'id' => 'btnCreateNewSite',
                'name' => 'btnCreateNewSite',
                'js' => "return createNewSiteFromStep1();",
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
                'id' => 'btnCancel', 'name' => 'btnCancel',
            );
            $fields_form[0]['form']['buttons'][] = array(
                'title' => PiwikWizardHelper::$strings['00617256bf279d54780075598d7e958c'],
                'icon' => 'process-icon-new', 'class' => '  pull-right donotdisable', 'type' => 'button',
                'id' => 'btnCreateNewSite', 'name' => 'btnCreateNewSite',
                'js' => "return createNewSiteFromStep1();",
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
                    PKHelper::updatePiwikSite($pksiteid /* $idSite */, $pksite[0]->name /* $siteName */, $pksite[0]->main_url /* $urls */, 1 /* $ecommerce */, 1 /* $siteSearch */, $pksite[0]->sitesearch_keyword_parameters /* $searchKeywordParameters */, $pksite[0]->sitesearch_category_parameters /* $searchCategoryParameters */, $pksite[0]->excluded_ips /* $excludedIps */, $pksite[0]->excluded_parameters /* $excludedQueryParameters */, $pksite[0]->timezone /* $timezone */, $pksite[0]->currency /* $currency */, $pksite[0]->group /* $group */, $pksite[0]->ts_created /* $startDate */, $pksite[0]->excluded_user_agents /* $excludedUserAgents */, $pksite[0]->keep_url_fragment /* $keepURLFragments */, $pksite[0]->type /* $type */);
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
