<?php

if (!defined('_PS_VERSION_'))
    exit;

include dirname(__FILE__) . '/../../PKClassLoader.php';
PKClassLoader::LoadStatic(array('PiwikHelper', 'PKTools'));
PiwikHelper::initialize();

/**
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
 */
class PiwikAnalyticsSiteManagerController extends ModuleAdminController {

    public $name = "PiwikAnalyticsSiteManager";

    /** @var array */
    public $messages = array();

    public function __construct() {
        parent::__construct();

        $tmp = Configuration::get(PiwikHelper::CPREFIX . 'HOST');
        if (!PKTools::is_valid_url('http://' . $tmp))
            $this->displayWarning($this->l('Piwik host url is not valid', 'PiwikAnalyticsSiteManager'));

        $tmp = Configuration::get(PiwikHelper::CPREFIX . 'TOKEN_AUTH');
        if (empty($tmp))
            $this->displayWarning($this->l('Piwik auth token is empty', 'PiwikAnalyticsSiteManager'));
    }

    public function init() {
        parent::init();

        $this->bootstrap = true;
        $this->action = 'view';
        $this->display = 'view';
        $this->show_page_header_toolbar = true;
    }

    public function initContent() {
        if ($this->ajax)
            return;

        $this->toolbar_title = $this->l('Piwik Site Manager', 'PiwikAnalyticsSiteManager');

        if (Tools::isSubmit('updatePiwikAnalyticsSiteManager') ||
                Tools::isSubmit('deletePiwikAnalyticsSiteManager') ||
                Tools::isSubmit('viewPiwikAnalyticsSiteManager') ||
                Tools::isSubmit('addPiwikAnalyticsSiteManager')) {

            $this->page_header_toolbar_btn['backparent'] = array(
                'href' => Context::getContext()->link->getAdminLink('PiwikAnalyticsSiteManager'),
                'desc' => $this->l('Back to list', 'PiwikAnalyticsSiteManager'),
                'icon' => 'process-icon-back'
            );
        }

        $this->page_header_toolbar_btn['update'] = array(
            'icon' => 'process-icon-refresh',
            'desc' => $this->l('Check for updates', 'PiwikAnalyticsSiteManager'),
            'href' => '#'
        );

        $this->page_header_toolbar_btn['addsite'] = array(
            'icon' => 'process-icon-new',
            'desc' => $this->l('Add new site', 'PiwikAnalyticsSiteManager'),
            'href' => Context::getContext()->link->getAdminLink('PiwikAnalyticsSiteManager') . '&addPiwikAnalyticsSiteManager',
        );

        parent::initContent();

        $this->context->smarty->assign('help_link', 'https://github.com/cmjnisse/piwikanalyticsjs-prestashop/wiki/Piwik-Analytics-Site-Manager');
    }

    public function renderView() {
        $view = parent::renderView();

        $tpl_folder = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/PiwikAnalyticsSiteManager/';

        $view .= $this->context->smarty->fetch($tpl_folder . "jsfunctions.tpl");

        $languages = Language::getLanguages(FALSE);
        foreach ($languages as $languages_key => $languages_value) {
            $languages[$languages_key]['is_default'] = ($languages_value['id_lang'] == (int) Configuration::get('PS_LANG_DEFAULT') ? true : false);
        }


        $this->processUpdatePiwikAnalyticsSiteFormUpdate();
        if (Tools::isSubmit('submitAddPiwikAnalyticsSite'))
            $this->processAddNewSite();


        if (Tools::isSubmit('addPiwikAnalyticsSiteManager')) {
            $view .= $this->generateAddNewSiteForm($languages);
        } else if (Tools::isSubmit('updatePiwikAnalyticsSiteManager') ||
                Tools::isSubmit('deletePiwikAnalyticsSiteManager') ||
                Tools::isSubmit('viewPiwikAnalyticsSiteManager')) {

            if (Tools::isSubmit('updatePiwikAnalyticsSiteManager')) {
                $idSite = Tools::getValue('idsite');
                $view .= $this->generateUpdatePiwikAnalyticsSiteForm($languages, $idSite);
            }
//            if (Tools::isSubmit('viewPiwikAnalyticsSiteManager')) {
//                $idSite = Tools::getValue('idsite');
//                $view .= $this->viewPiwikAnalyticsSite($languages, $idSite);
//            }
            if (Tools::isSubmit('deletePiwikAnalyticsSiteManager')) {
                $idSite = Tools::getValue('idsite');
                $view .= $this->deletePiwikAnalyticsSite($languages, $idSite);
            }
        } else {
            $view .= $this->generateConfigForm($languages);
            $view .= $this->generateListForm($languages);
        }
        return implode(',', $this->messages) . $view;
    }

//    private function viewPiwikAnalyticsSite($languages, $idSite) {
//        
//    }

    private function deletePiwikAnalyticsSite($languages, $idSite) {
        die("delete, site: " . Tools::getValue('idsite'));
        $this->displayInformation('Delete Site: Not yet sorry..');
    }

    private function processAddNewSite() {
        $this->displayInformation('Add New Site: Not yet sorry..');
        

        $addons = PKClassLoader::LoadAddons(array('controller' => & $this));
        foreach ($addons as $addon) {
            if (method_exists($addon, 'CreateSiteSubmitForm'))
                $addon->CreateSiteSubmitForm();
        }
    }

    private function generateAddNewSiteForm($languages) {

        $this->addJqueryPlugin('tagify', _PS_JS_DIR_ . 'jquery/plugins/');

        $helper = new HelperForm();

        $helper->languages = $languages;
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->identifier = 'id_module';
        $helper->token = Tools::getAdminTokenLite('PiwikAnalyticsSiteManager');
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->show_cancel_button = true;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->title = $this->module->displayName;
        $helper->submit_action = 'submitAddPiwikAnalyticsSite';


        $pktimezones = array();
        $tmp = PiwikHelper::getTimezonesList();
        $this->displayErrorsFromPiwikHelper();
        foreach ($tmp as $key => $pktz) {
            if (!isset($pktimezones[$key])) {
                $pktimezones[$key] = array(
                    'name' => $this->l($key),
                    'query' => array(),
                );
            }
            foreach ($pktz as $pktzK => $pktzV) {
                $pktimezones[$key]['query'][] = array(
                    'tzId' => $pktzK,
                    'tzName' => $pktzV,
                );
            }
        }

        $tmp = PiwikHelper::getCurrencyList();
        $tmp = isset($tmp[0]) && ((is_array($tmp[0]) && !empty($tmp[0])) || (is_object($tmp[0]) && !empty($tmp[0]))) ? (array) $tmp[0] : array();
        $this->displayErrorsFromPiwikHelper();
        $currencies = array();
        foreach ($tmp as $key => $val) {
            $currencies[] = array(
                'iso_code' => $key,
                'name' => "{$val}",
            );
        }
        if (empty($currencies)) {
            // in case of error fallback to currencies installed in the shop
            foreach (Currency::getCurrencies() as $key => $val) {
                $currencies[$key] = array(
                    'iso_code' => $val['iso_code'],
                    'name' => "{$val['name']} {$val['iso_code']}",
                );
            }
        }

        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Add new site to Piwik Analytics', 'PiwikAnalyticsSiteManager'),
                'image' => $this->module->getPathUri() . 'logox22.png'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Site Name', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewSiteName',
                    'desc' => $this->l('Name of this site in Piwik', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Main Url', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewMainUrl',
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Addtional Urls', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewAddtionalUrls',
                ),
                array(
                    'type' => 'switch',
                    'is_bool' => true,
                    'label' => $this->l('Ecommerce', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewEcommerce',
                    'desc' => $this->l('Is this site an ecommerce site?', 'PiwikAnalyticsSiteManager'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'PiwikAnalyticsSiteManager')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'PiwikAnalyticsSiteManager')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'is_bool' => true,
                    'label' => $this->l('Site Search', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewSiteSearch',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'PiwikAnalyticsSiteManager')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'PiwikAnalyticsSiteManager')
                        )
                    ),
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Search Keyword Parameters', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewSearchKeywordParameters',
                    'desc' => "<strong>tag</strong> and <strong>search_query</strong> " . $this->l('keywords parameters must be excluded to avoid normal page views to be interpreted as searches (the tracking code will see them and make the required postback to Piwik if it is a real search), if you are only using PrestaShop with this site setting this to empty, will be sufficient', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Search Category Parameters', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewSearchCategoryParameters',
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Excluded ip addresses', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewExcludedIps',
                    'desc' => $this->l('ip addresses excluded from tracking, separated by comma ","', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Excluded Query Parameters', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewExcludedQueryParameters',
                    'desc' => $this->l('please read: http://piwik.org/faq/how-to/faq_81/', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Timezone', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewTimezone',
                    'desc' => $this->l('The timezone for this site', 'PiwikAnalyticsSiteManager'),
                    'options' => array(
                        'default' => array('value' => 0, 'label' => $this->l('Choose Timezone', 'PiwikAnalyticsSiteManager')),
                        'optiongroup' => array(
                            'label' => 'name',
                            'query' => $pktimezones,
                        ),
                        'options' => array(
                            'id' => 'tzId',
                            'name' => 'tzName',
                            'query' => 'query',
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Currency', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKNewCurrency',
                    'desc' => $this->l('The currency for this site', 'PiwikAnalyticsSiteManager'),
                    'options' => array(
                        'default' => array('value' => 0, 'label' => $this->l('Choose Currency', 'PiwikAnalyticsSiteManager')),
                        'query' => $currencies,
                        'id' => 'iso_code',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Excluded User Agents'),
                    'name' => 'PKNewExcludedUserAgents',
                    'rows' => 10,
                    'cols' => 50,
                    'desc' => $this->l('please read: http://piwik.org/faq/how-to/faq_17483/', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'switch',
                    'is_bool' => true,
                    'label' => $this->l('Keep URL Fragments'),
                    'name' => 'PKNewKeepURLFragments',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'PiwikAnalyticsSiteManager')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'PiwikAnalyticsSiteManager')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Create', 'PiwikAnalyticsSiteManager'),
                'class' => 'btn btn-default'
            ),
//            'buttons' => array(
//                array(
//                    'type' => 'submit',
//                    'title' => $this->l('Create and stay', 'PiwikAnalyticsSiteManager'),
//                    'icon' => 'process-icon-save',
//                    'class' => ' pull-right',
//                    'js' => 'return SubmitStay($(\'#configuration_form\'), \'' . $_SERVER['REQUEST_URI'] . '\')',
//                    'id' => 'submitAddPiwikAnalyticsSiteAndStay',
//                    'name' => 'submitAddPiwikAnalyticsSiteAndStay',
//                ),
//            ),
        );
        $helper->fields_value = array(
            'PKNewSiteName' => 'Webshop - Prestashop \'' . _PS_VERSION_ . '\'',
            'PKNewMainUrl' => Tools::getShopDomain(),
            'PKNewAddtionalUrls' => '',
            'PKNewEcommerce' => 1,
            'PKNewSiteSearch' => 1,
            'PKNewSearchKeywordParameters' => '',
            'PKNewSearchCategoryParameters' => '',
            'PKNewExcludedIps' => '',
            'PKNewExcludedQueryParameters' => '',
            'PKNewTimezone' => 'UTC',
            'PKNewCurrency' => 'EUR',
            'PKNewExcludedUserAgents' => '',
            'PKNewKeepURLFragments' => 0,
        );

        $addons = PKClassLoader::LoadAddons(array('controller' => & $this));
        foreach ($addons as $addon) {
            if (method_exists($addon, 'CreateSiteForm'))
                $addon->CreateSiteForm($helper, $fields_form);
        }

        return $helper->generateForm($fields_form);
    }

    private function processUpdatePiwikAnalyticsSiteFormUpdate() {
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsSite')) {
            $idSite = Tools::getValue('idSite');
            if (((int) $idSite != $idSite) || ((int) $idSite <= 0)) {
                $this->errors[] = Tools::displayError($this->l('Piwik site id is not set, aborting update', 'PiwikAnalyticsSiteManager'));
                return;
            }

            $PKSiteName = Tools::getValue('PKSiteName');
            $PKEcommerce = Tools::getValue('PKEcommerce', 0);
            $PKSiteSearch = Tools::getValue('PKSiteSearch', 0);
            $PKSearchKeywordParameters = Tools::getValue('PKSearchKeywordParameters', '');
            $PKSearchCategoryParameters = Tools::getValue('PKSearchCategoryParameters', '');
            $PKExcludedIps = Tools::getValue('PKExcludedIps', '');
            $PKExcludedQueryParameters = Tools::getValue('PKExcludedQueryParameters', '');
            $PKTimezone = Tools::getValue('PKTimezone', 'UTC');
            $PKCurrency = Tools::getValue('PKCurrency', 'EUR');
            $PKExcludedUserAgents = Tools::getValue('PKExcludedUserAgents', '');
            $PKKeepURLFragments = Tools::getValue('PKKeepURLFragments', 0);

            $urls = NULL;
            $PKMainUrl = Tools::getValue('PKMainUrl');
            if ($PKMainUrl !== false) {
                $urls = $PKMainUrl;
                if ($PKAddtionalUrls = Tools::getValue('PKAddtionalUrls')) {
                    $urls .= "," . $PKAddtionalUrls;
                }
            }

            $result = PiwikHelper::updatePiwikSite(
                            $idSite, $PKSiteName,
                            /* NL */ $urls, $PKEcommerce, $PKSiteSearch, $PKSearchKeywordParameters,
                            /* NL */ $PKSearchCategoryParameters, $PKExcludedIps,
                            /* NL */ $PKExcludedQueryParameters, $PKTimezone, $PKCurrency,
                            /* $group */ NULL,
                            /* $startDate */ NULL, $PKExcludedUserAgents, $PKKeepURLFragments,
                            /* $type */ NULL);
            if ($result !== FALSE) {
                if (is_string($result)) {
                    $this->displayInformation(sprintf($this->l('Site update returned the following message: %s'), $result));
                } else {
                    $this->messages[] = $this->displayConfirmation($this->l('Piwik site updated'));
                }
            } else {
                $this->errors[] = Tools::displayError($this->l('Unkown error doing site update, Piwik site may not be updated?'));
            }



            $addons = PKClassLoader::LoadAddons(array('controller' => & $this));
            foreach ($addons as $addon) {
                if (method_exists($addon, 'SiteEditSubmitForm'))
                    $addon->SiteEditSubmitForm();
            }
        }
    }

    private function generateUpdatePiwikAnalyticsSiteForm($languages, $idSite) {

        $this->addJqueryPlugin('tagify', _PS_JS_DIR_ . 'jquery/plugins/');

        $pkSite = PiwikHelper::getPiwikSite2($idSite);
        $this->displayErrorsFromPiwikHelper();
        $pkSite = isset($pkSite[0]) && $pkSite[0] !== false ? (array) $pkSite[0] : FALSE;
        if ($pkSite === FALSE) {
            $this->displayWarning(sprintf($this->l('Unable to get the site data from Piwik for site with id: %s', 'PiwikAnalyticsSiteManager'), $idSite));
            return false;
        }

        /**
          [idsite] => 14
          [ts_created] => 2014-04-08 00:00:00
          [group] =>
          [type] => website
         */
        $urls = explode(',', $pkSite['main_url']);
        $main_url = $urls[0];
        $addtional_urls = "";
        foreach ($urls as $key => $value) {
            if ($value != $main_url)
                $addtional_urls .= $value . ",";
        }
        $addtional_urls = rtrim($addtional_urls, ',');

        $helper = new HelperForm();

        $helper->languages = $languages;
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->identifier = $this->module->getIdentifier();
        $helper->token = Tools::getAdminTokenLite('PiwikAnalyticsSiteManager');
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->show_cancel_button = true;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->title = $this->module->displayName;
        $helper->submit_action = 'submitUpdatePiwikAnalyticsSite';

        $helper->fields_value = array(
            'idSite' => $idSite,
            'PKMainUrl' => $main_url,
            'PKAddtionalUrls' => $addtional_urls,
            'PKCustomCss' => isset($pkSite['custom_css']) ? $pkSite['custom_css'] : '',
            'PKCustomCssFile' => isset($pkSite['custom_css_file']) ? $pkSite['custom_css_file'] : '',
            'PKCurrency' => isset($pkSite['currency']) ? $pkSite['currency'] : 'EUR',
            'PKTimezone' => isset($pkSite['timezone']) ? $pkSite['timezone'] : 'UTC',
            'PKSiteName' => isset($pkSite['name']) ? $pkSite['name'] : 'Empty',
            'PKEcommerce' => isset($pkSite['ecommerce']) ? $pkSite['ecommerce'] : 0,
            'PKSiteSearch' => isset($pkSite['sitesearch']) ? $pkSite['sitesearch'] : 0,
            'PKSearchKeywordParameters' => isset($pkSite['sitesearch_keyword_parameters']) ? $pkSite['sitesearch_keyword_parameters'] : '',
            'PKSearchCategoryParameters' => isset($pkSite['sitesearch_category_parameters']) ? $pkSite['sitesearch_category_parameters'] : '',
            'PKExcludedIps' => isset($pkSite['excluded_ips']) ? $pkSite['excluded_ips'] : '',
            'PKExcludedQueryParameters' => isset($pkSite['excluded_parameters']) ? $pkSite['excluded_parameters'] : '',
            'PKExcludedUserAgents' => isset($pkSite['excluded_user_agents']) ? $pkSite['excluded_user_agents'] : '',
            'PKKeepURLFragments' => isset($pkSite['keep_url_fragment']) ? $pkSite['keep_url_fragment'] : 0,
        );

        $pktimezones = array();
        $tmp = PiwikHelper::getTimezonesList();
        $this->displayErrorsFromPiwikHelper();
        foreach ($tmp as $key => $pktz) {
            if (!isset($pktimezones[$key])) {
                $pktimezones[$key] = array(
                    'name' => $this->l($key),
                    'query' => array(),
                );
            }
            foreach ($pktz as $pktzK => $pktzV) {
                $pktimezones[$key]['query'][] = array(
                    'tzId' => $pktzK,
                    'tzName' => $pktzV,
                );
            }
        }

        $tmp = PiwikHelper::getCurrencyList();
        $tmp = isset($tmp[0]) && ((is_array($tmp[0]) && !empty($tmp[0])) || (is_object($tmp[0]) && !empty($tmp[0]))) ? (array) $tmp[0] : array();
        $this->displayErrorsFromPiwikHelper();
        $currencies = array();
        foreach ($tmp as $key => $val) {
            $currencies[] = array(
                'iso_code' => $key,
                'name' => "{$val}",
            );
        }
        if (empty($currencies)) {
            // in case of error fallback to currencies installed in the shop
            foreach (Currency::getCurrencies() as $key => $val) {
                $currencies[$key] = array(
                    'iso_code' => $val['iso_code'],
                    'name' => "{$val['name']} {$val['iso_code']}",
                );
            }
        }

        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => sprintf($this->l('Edit Piwik site (%s #%s)', 'PiwikAnalyticsSiteManager'), $helper->fields_value['PKSiteName'], $idSite),
                'image' => $this->module->getPathUri() . 'logox22.png'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'idSite',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Site Name', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKSiteName',
                    'desc' => $this->l('Name of this site in Piwik', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Main Url', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKMainUrl',
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Addtional Urls', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKAddtionalUrls',
                ),
                array(
                    'type' => 'switch',
                    'is_bool' => true,
                    'label' => $this->l('Ecommerce', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKEcommerce',
                    'desc' => $this->l('Is this site an ecommerce site?', 'PiwikAnalyticsSiteManager'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'PiwikAnalyticsSiteManager')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'PiwikAnalyticsSiteManager')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'is_bool' => true,
                    'label' => $this->l('Site Search', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKSiteSearch',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'PiwikAnalyticsSiteManager')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'PiwikAnalyticsSiteManager')
                        )
                    ),
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Search Keyword Parameters', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKSearchKeywordParameters',
                    'desc' => "<strong>tag</strong> and <strong>search_query</strong> " . $this->l('keywords parameters must be excluded to avoid normal page views to be interpreted as searches (the tracking code will see them and make the required postback to Piwik if it is a real search), if you are only using PrestaShop with this site setting this to empty, will be sufficient', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Search Category Parameters', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKSearchCategoryParameters',
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Excluded ip addresses', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKExcludedIps',
                    'desc' => $this->l('ip addresses excluded from tracking, separated by comma ","', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Excluded Query Parameters', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKExcludedQueryParameters',
                    'desc' => $this->l('please read: http://piwik.org/faq/how-to/faq_81/', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Timezone', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKTimezone',
                    'desc' => $this->l('The timezone for this site', 'PiwikAnalyticsSiteManager'),
                    'options' => array(
                        'default' => array('value' => 0, 'label' => $this->l('Choose Timezone', 'PiwikAnalyticsSiteManager')),
                        'optiongroup' => array(
                            'label' => 'name',
                            'query' => $pktimezones,
                        ),
                        'options' => array(
                            'id' => 'tzId',
                            'name' => 'tzName',
                            'query' => 'query',
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Currency', 'PiwikAnalyticsSiteManager'),
                    'name' => 'PKCurrency',
                    'desc' => $this->l('The currency for this site', 'PiwikAnalyticsSiteManager'),
                    'options' => array(
                        'default' => array('value' => 0, 'label' => $this->l('Choose Currency', 'PiwikAnalyticsSiteManager')),
                        'query' => $currencies,
                        'id' => 'iso_code',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Excluded User Agents'),
                    'name' => 'PKExcludedUserAgents',
                    'rows' => 10,
                    'cols' => 50,
                    'desc' => $this->l('please read: http://piwik.org/faq/how-to/faq_17483/', 'PiwikAnalyticsSiteManager'),
                ),
                array(
                    'type' => 'switch',
                    'is_bool' => true,
                    'label' => $this->l('Keep URL Fragments'),
                    'name' => 'PKKeepURLFragments',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes', 'PiwikAnalyticsSiteManager')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No', 'PiwikAnalyticsSiteManager')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save', 'PiwikAnalyticsSiteManager'),
                'class' => 'btn btn-default'
            ),
            'reset' => array(
                'title' => $this->l('Reset', 'PiwikAnalyticsSiteManager'),
                'class' => 'btn btn-default',
                'icon' => 'process-icon-reset',
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title' => $this->l('Save and stay', 'PiwikAnalyticsSiteManager'),
                    'icon' => 'process-icon-save',
                    'class' => ' pull-right',
                    'js' => 'return SubmitStay($(\'#configuration_form\'), \'' . $_SERVER['REQUEST_URI'] . '\')',
                    'id' => 'submitUpdatePiwikAnalyticsSiteAndStay',
                    'name' => 'submitUpdatePiwikAnalyticsSiteAndStay',
                ),
            ),
        );

        $addons = PKClassLoader::LoadAddons(array('controller' => & $this));
        foreach ($addons as $addon) {
            if (method_exists($addon, 'SiteEditForm'))
                $addon->SiteEditForm($idSite, $helper, $fields_form);
        }

        return $helper->generateForm($fields_form);
    }

    private function processListFormUpdate() {
        
    }

    private function generateListForm($languages) {
        $this->processListFormUpdate();

        $helper = new HelperList();
        $helper->languages = $languages;
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->table = 'PiwikAnalyticsSiteManager';
        $helper->identifier = 'idsite';
        $helper->token = Tools::getAdminTokenLite('PiwikAnalyticsSiteManager');
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->shopLinkType = false;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->simple_header = true;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->title = $this->module->displayName;

        $helper->actions[] = 'edit';
//        $helper->actions[] = 'view';
        $helper->actions[] = 'delete';

        /*
         * sitesearch_keyword_parameters
         * sitesearch_category_parameters
         * excluded_ips
         * excluded_parameters
         * excluded_user_agents
         * keep_url_fragment
         */
        $fields_list = array(
            'idsite' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 30
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'width' => 'auto'
            ),
            'ecommerce' => array(
                'title' => $this->l('Ecommerce'),
                'width' => 'auto'
            ),
            'sitesearch' => array(
                'title' => $this->l('Site Search'),
                'width' => 'auto'
            ),
            'timezone' => array(
                'title' => $this->l('Timezone'),
                'width' => 'auto'
            ),
            'currency' => array(
                'title' => $this->l('Currency'),
                'width' => 'auto'
            ),
            'group' => array(
                'title' => $this->l('Group'),
                'width' => 'auto'
            ),
        );

        $piwiksites = PiwikHelper::getSitesWithAdminAccess();
        $this->displayErrorsFromPiwikHelper();

        $list = array();
        foreach ($piwiksites as $value)
            $list[] = (array) $value;
        return $helper->generateList($list, $fields_list);
    }

    private function processConfigFormUpdate() {
        if (Tools::isSubmit('submitUpdatepiwikmanager')) {
            // Validate and update Piwik Host
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'HOST')) {
                $tmp = Tools::getValue(PiwikHelper::CPREFIX . 'HOST', '');
                if (!empty($tmp)) {
                    $tmp = str_replace(array('http://', 'https://', '//'), "", $tmp);
                    if (PKTools::is_valid_url('http://' . $tmp)) {
                        if (substr($tmp, -1) != "/")
                            $tmp .= "/";

                        Configuration::updateValue(PiwikHelper::CPREFIX . 'HOST', $tmp);
                    } else
                        $this->errors[] = Tools::displayError($this->l('Piwik host url is not valid', 'PiwikAnalyticsSiteManager'));
                } else
                    $this->errors[] = Tools::displayError($this->l('Piwik host cannot be empty', 'PiwikAnalyticsSiteManager'));
            }
            // Validate and update Piwik auth token
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'TOKEN_AUTH')) {
                $tmp = Tools::getValue(PiwikHelper::CPREFIX . 'TOKEN_AUTH', '');
                if (empty($tmp))
                    $this->errors[] = Tools::displayError($this->l('Piwik auth token is empty', 'PiwikAnalyticsSiteManager'));
                else
                    Configuration::updateValue(PiwikHelper::CPREFIX . 'TOKEN_AUTH', $tmp);
            }
            // Validate and update use https
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'CRHTTPS')) {
                $tmp = Tools::getValue(PiwikHelper::CPREFIX . 'CRHTTPS', 0);
                if ((int) $tmp != $tmp)
                    $tmp = 0; // if int cast faild set to zero (disabled)
                Configuration::updateValue(PiwikHelper::CPREFIX . 'CRHTTPS', $tmp);
            }
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'DEBUG')) {
                $tmp = Tools::getValue(PiwikHelper::CPREFIX . 'DEBUG', 0);
                if ((int) $tmp != $tmp)
                    $tmp = 0;
                Configuration::updateValue(PiwikHelper::CPREFIX . 'DEBUG', $tmp);
            }
        }
    }

    private function generateConfigForm($languages) {
        $this->processConfigFormUpdate();

        $helper = new HelperForm();

        $helper->languages = $languages;
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->identifier = 'id_module';
        $helper->token = Tools::getAdminTokenLite('PiwikAnalyticsSiteManager');
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->title = $this->module->displayName;
        $helper->submit_action = 'submitUpdate' . $this->module->name;

        $fields_form = array();
        $fields_form[0]['form']['legend'] = array(
            'title' => $this->l('Piwik Analytics Site Manager', 'PiwikAnalyticsSiteManager') . ' ' . $this->l('- Configuration', 'PiwikAnalyticsSiteManager'),
            'image' => $this->module->getPathUri() . 'logox22.png'
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'switch',
            'label' => $this->l('Use HTTPS'),
            'name' => PiwikHelper::CPREFIX . 'CRHTTPS',
            'hint' => $this->l('ONLY enable this feature if piwik installation is accessible via https'),
            'desc' => $this->l('use Hypertext Transfer Protocol Secure (HTTPS) in all requests from code to piwik, '
                    . 'this affects how requests are sent from proxy script and admin to piwik, '
                    . 'your visitors will still use the protocol they visit your shop with'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled', 'PiwikAnalyticsSiteManager')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled', 'PiwikAnalyticsSiteManager')
                )
            ),
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik Host'),
            'name' => PiwikHelper::CPREFIX . 'HOST',
            'desc' => $this->l('Example: www.example.com/piwik/ (without protocol and with / at the end!)', 'PiwikAnalyticsSiteManager'),
            'hint' => $this->l('The host where piwik is installed.!', 'PiwikAnalyticsSiteManager'),
            'required' => true
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik token auth'),
            'name' => PiwikHelper::CPREFIX . 'TOKEN_AUTH',
            'desc' => $this->l('You can find piwik token by loggin to piwik installation. under API', 'PiwikAnalyticsSiteManager'),
            'required' => true
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'html',
            'name' => '<button'
            . ' onclick="alert(\'this functionality is not implemented yet, i apologise for the inconvenience\')"'
            . ' name="btnLookupAuthToken"'
            . ' class="btn btn-default "'
            . ' id="btnLookupAuthToken"'
            . ' type="button">'
            . '<i class="icon-search"></i> '
            . $this->l('Lookup - auth token', 'PiwikAnalyticsSiteManager')
            . '</button>'
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'switch',
            'label' => $this->l('Debug'),
            'name' => PiwikHelper::CPREFIX . 'DEBUG',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled', 'PiwikAnalyticsSiteManager')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled', 'PiwikAnalyticsSiteManager')
                )
            ),
        );

        $fields_form[0]['form']['submit'] = array(
            'title' => $this->l('Save'),
            'class' => 'btn btn-default'
        );
        $helper->fields_value = array(
            PiwikHelper::CPREFIX . 'CRHTTPS' => Configuration::get(PiwikHelper::CPREFIX . 'CRHTTPS'),
            PiwikHelper::CPREFIX . 'HOST' => Configuration::get(PiwikHelper::CPREFIX . 'HOST'),
            PiwikHelper::CPREFIX . 'TOKEN_AUTH' => Configuration::get(PiwikHelper::CPREFIX . 'TOKEN_AUTH'),
            PiwikHelper::CPREFIX . 'DEBUG' => Configuration::get(PiwikHelper::CPREFIX . 'DEBUG'),
        );

        return $helper->generateForm($fields_form);
    }

    public function displayConfirmation($string) {
        if (method_exists($this->module, 'displayConfirmation')) {
            return $this->module->displayConfirmation($string);
        }
        $output = '<div class="bootstrap">'
                . '<div class="module_confirmation conf confirm alert alert-success">'
                . '<button type="button" class="close" data-dismiss="alert">&times;</button>'
                . $string . '</div></div>';
        return $output;
    }

    private function displayErrors($errors) {
        if (!empty($errors))
            foreach ($errors as $key => $value)
                $this->errors[] = Tools::displayError($value);
    }

    private function displayErrorsFromPiwikHelper() {
        $this->displayErrors(PiwikHelper::$errors);
        PiwikHelper::$errors = PiwikHelper::$error = "";
    }

}
