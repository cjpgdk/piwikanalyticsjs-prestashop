<?php

if (!defined('_PS_VERSION_'))
    exit;

include dirname(__FILE__) . '/../../../piwikmanager/PKClassLoader.php';
PKClassLoader::LoadStatic(array('PiwikHelper', 'PKTools'));
PiwikHelper::initialize();

/**
 * Copyright (C) 2015 Christian Jensen
 *
 * This file is part of PiwikAnalytics for prestashop.
 * 
 * PiwikAnalytics for prestashop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * PiwikAnalytics for prestashop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with PiwikAnalytics for prestashop. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Christian M. Jensen
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class PiwikAnalyticsTrackingConfigController extends ModuleAdminController {

    public $name = "PiwikAnalyticsTrackingConfig";

    /** @var array */
    public $messages = array();

    public function __construct() {
        parent::__construct();
    }

    public function init() {
        parent::init();

        $this->bootstrap = true;
        $this->action = 'edit';
        $this->display = 'edit';
        $this->show_page_header_toolbar = true;

        // force pd core to load default
        $this->default_form_language = null;
    }

    public function initContent() {
        if ($this->ajax)
            return;

        $this->toolbar_title = $this->l('Piwik TrackingCode Config', $this->name);

        parent::initContent();

        $this->context->smarty->assign('help_link', 'https://github.com/cmjnisse/piwikanalyticsjs-prestashop/wiki/Piwik-Analytics');
    }

    public function renderForm() {

        $this->addJqueryPlugin('tagify', _PS_JS_DIR_ . 'jquery/plugins/');

        $this->multiple_fieldsets = true; // i choose the structure.!

        $PIWIK_PRODID_V1 = Configuration::get(PiwikHelper::CPREFIX . 'PRODID_V1');
        $PIWIK_PRODID_V2 = Configuration::get(PiwikHelper::CPREFIX . 'PRODID_V2');
        $PIWIK_PRODID_V3 = Configuration::get(PiwikHelper::CPREFIX . 'PRODID_V3');
        $PIWIK_RCOOKIE_TIMEOUT = (int) Configuration::get(PiwikHelper::CPREFIX . 'RCOOKIE_TIMEOUT');
        $PIWIK_COOKIE_TIMEOUT = (int) Configuration::get(PiwikHelper::CPREFIX . 'COOKIE_TIMEOUT');
        $PIWIK_SESSION_TIMEOUT = (int) Configuration::get(PiwikHelper::CPREFIX . 'SESSION_TIMEOUT');
        $PIWIK_PROXY_SCRIPT = Configuration::get(PiwikHelper::CPREFIX . 'PROXY_SCRIPT');
        $this->fields_value = array(
            PiwikHelper::CPREFIX . 'SITEID' => (int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID'),
            PiwikHelper::CPREFIX . 'HOST' => Configuration::get(PiwikHelper::CPREFIX . 'HOST'),
            PiwikHelper::CPREFIX . 'TOKEN_AUTH' => Configuration::get(PiwikHelper::CPREFIX . 'TOKEN_AUTH'),
            PiwikHelper::CPREFIX . 'COOKIE_DOMAIN' => Configuration::get(PiwikHelper::CPREFIX . 'COOKIE_DOMAIN'),
            PiwikHelper::CPREFIX . 'SET_DOMAINS' => Configuration::get(PiwikHelper::CPREFIX . 'SET_DOMAINS'),
            PiwikHelper::CPREFIX . 'DNT' => Configuration::get(PiwikHelper::CPREFIX . 'DNT'),
            PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY' => Configuration::get(PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY'),
            PiwikHelper::CPREFIX . 'PRODID_V1' => (!empty($PIWIK_PRODID_V1) ? $PIWIK_PRODID_V1 : '{ID}-{ATTRID}#{REFERENCE}'),
            PiwikHelper::CPREFIX . 'PRODID_V2' => (!empty($PIWIK_PRODID_V2) ? $PIWIK_PRODID_V2 : '{ID}#{REFERENCE}'),
            PiwikHelper::CPREFIX . 'PRODID_V3' => (!empty($PIWIK_PRODID_V3) ? $PIWIK_PRODID_V3 : '{ID}-{ATTRID}'),
            PiwikHelper::CPREFIX . 'SESSION_TIMEOUT' => ($PIWIK_SESSION_TIMEOUT != 0 ? (int) $PIWIK_SESSION_TIMEOUT : (int) (piwikanalytics::PK_SC_TIMEOUT )),
            PiwikHelper::CPREFIX . 'COOKIE_TIMEOUT' => ($PIWIK_COOKIE_TIMEOUT != 0 ? (int) $PIWIK_COOKIE_TIMEOUT : (int) (piwikanalytics::PK_VC_TIMEOUT)),
            PiwikHelper::CPREFIX . 'RCOOKIE_TIMEOUT' => ($PIWIK_RCOOKIE_TIMEOUT != 0 ? (int) $PIWIK_RCOOKIE_TIMEOUT : (int) (piwikanalytics::PK_RC_TIMEOUT)),
            PiwikHelper::CPREFIX . 'USE_PROXY' => Configuration::get(PiwikHelper::CPREFIX . 'USE_PROXY'),
            PiwikHelper::CPREFIX . 'PROXY_SCRIPT' => empty($PIWIK_PROXY_SCRIPT) ? str_replace(array("http://", "https://"), '', Context::getContext()->link->getModuleLink($this->module->name, 'piwik')) : $PIWIK_PROXY_SCRIPT,
        );

        $piwik_host = $this->fields_value[PiwikHelper::CPREFIX . 'HOST'];
        $piwik_token = $this->fields_value[PiwikHelper::CPREFIX . 'TOKEN_AUTH'];
        $piwik_site = null;
        if (!empty($piwik_token) && ($piwik_token !== false) && ((!empty($piwik_host) && ($piwik_host !== false)))) {
            // get current selected site
            $piwik_site = PiwikHelper::getPiwikSite();
        }

        // display host/token or link to manager.
        if (empty($piwik_host) || ($piwik_host === false)) {
            $piwik_host = "<a href='" . Context::getContext()->link->getAdminLink('PiwikAnalyticsSiteManager') . "'>" . $this->l('Missing click here to goto Piwik Manager.', $this->name) . "</a>";
        }
        if (empty($piwik_token) || ($piwik_token === false)) {
            $piwik_token = "<a href='" . Context::getContext()->link->getAdminLink('PiwikAnalyticsSiteManager') . "'>" . $this->l('Missing click here to goto Piwik Manager.', $this->name) . "</a>";
        } else {
            $piwik_token = $this->l('***HIDDEN***', $this->name);
        }

        // do stuff with valid site
        $piwik_currency = $this->l('unknown', $this->name);
        if ($piwik_site !== false) {
            $piwik_currency = $piwik_site[0]->currency;
        }

        // done with piwik lookups display errors if any
        $this->displayErrorsFromPiwikHelper();

        // load currencies installed in the shop
        $currencies = array();
        foreach (Currency::getCurrencies() as $key => $val) {
            $currencies[$key] = array(
                'iso_code' => $val['iso_code'],
                'name' => "{$val['name']} {$val['iso_code']}",
            );
        }

        // main form.

        $this->fields_form[0] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Piwik TrackingCode Options', $this->name),
                    'image' => $this->module->getPathUri() . 'logox22.png'
                ),
                'input' => array(
                    array(
                        'type' => 'html',
                        'name' => '<button'
                        . ' onclick="alert(\'this functionality is not implemented yet, i apologise for the inconvenience\')"'
                        . ' name="btnLookupPKSite"'
                        . ' class="btn btn-default "'
                        . ' id="btnLookupPKSite"'
                        . ' type="button">'
                        . '<i class="icon-search"></i> '
                        . $this->l('Lookup - Piwik site', $this->name)
                        . '</button>&nbsp;&nbsp;'
                    ),
                    array(
                        'type' => 'html',
                        'name' => '<strong>' . $this->l('Piwik Host:', $this->name) . '</strong> ' . $piwik_host,
                    ),
                    array(
                        'type' => 'html',
                        'name' => '<strong>' . $this->l('Piwik Auth token:', $this->name) . '</strong> ' . $piwik_token,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Piwik site id', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'SITEID',
                        'desc' => $this->l('Example: 10', $this->name),
                        'hint' => $this->l('You can find piwik site id by loggin to piwik installation.', $this->name),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Track visitors across subdomains', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'COOKIE_DOMAIN',
                        'desc' => $this->l('The default is the document domain; if your web site can be visited at both www.example.com and example.com, you would use: "*.example.com" OR ".example.com" without the quotes', $this->name)
                        . '<br />'
                        . $this->l('*Leave empty to exclude this from the tracking code', $this->name),
                        'hint' => $this->l('if one visitor visits x.example.com and y.example.com, they will be counted as one unique visitor. (setCookieDomain)', $this->name),
                        'required' => false
                    ),
                    array(
                        'type' => 'tags',
                        'label' => $this->l('Hide known alias URLs', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'SET_DOMAINS',
                        'desc' => $this->l('In the "Outlinks" report, hide clicks to known alias URLs, Example: *.example.com', $this->name)
                        . '<br />'
                        . $this->l('Note:', $this->name)
                        . '<br />'
                        . $this->l('to add multiple domains you must separate them with comma ","', $this->name)
                        . '<br />'
                        . $this->l('the currently tracked website is added to this array automatically', $this->name)
                        . '<br />'
                        . $this->l('*Leave empty to exclude this from the tracking code', $this->name),
                        'hint' => $this->l('Clicks on links to Alias URLs (eg. x.example.com) will not be counted as "Outlink". (setDomains)', $this->name),
                        'required' => false
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable client side DoNotTrack detection', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'DNT',
                        'desc' => $this->l('Tracking requests will not be sent if visitors do not wish to be tracked.', $this->name),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled', $this->name)
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled', $this->name)
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Piwik Currency', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY',
                        'desc' => sprintf($this->l('Based on your settings in Piwik your default currency is %s', $this->name), $piwik_currency),
                        'options' => array(
                            'default' => array('value' => 0, 'label' => $this->l('Choose Currency', $this->name)),
                            'query' => $currencies,
                            'id' => 'iso_code',
                            'name' => 'name'
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save', $this->name),
                    'class' => 'btn btn-default'
                ),
            ),
        );

        // 'Advanced' form (extra options)

        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Piwik TrackingCode Advanced Options', $this->name),
                'image' => $this->module->getPathUri() . 'logox22.png'
            ),
            'input' => array(
                array(
                    'type' => 'html',
                    'name' => $this->l('In this section you can modify certain aspects of the way this plugin sends products, searches, category view etc.. to piwik', $this->name)
                ),
                array(
                    'type' => 'html',
                    'name' => "<strong>{$this->l('Product id', $this->name)}</strong>"
                    . '<br />'
                    . $this->l('in the next few inputs you can set how the product id is passed on to piwik', $this->name)
                    . '<br />'
                    . $this->l('there are three variables you can use:', $this->name)
                    . '<br />'
                    . $this->l('{ID} : this variable is replaced with id of product in prestashop', $this->name)
                    . '<br />'
                    . $this->l('{REFERENCE} : this variable is replaced with the unique reference you set for the product', $this->name)
                    . '<br />'
                    . $this->l('{ATTRID} : this variable is replaced with id the product attribute', $this->name)
                    . '<br />'
                    . $this->l('in cases where only the product id is available it be parsed as ID and nothing else', $this->name),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product id V1', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'PRODID_V1',
                    'desc' => $this->l('This template is used in case ALL three values are available ("Product ID", "Product Attribute ID" and "Product Reference")', $this->name),
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product id V2', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'PRODID_V2',
                    'desc' => $this->l('This template is used in case only "Product ID" and "Product Reference" are available', $this->name),
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product id V3', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'PRODID_V3',
                    'desc' => $this->l('This template is used in case only "Product ID" and "Product Attribute ID" are available', $this->name),
                    'required' => false
                ),
                array(
                    'type' => 'html',
                    'name' => "<strong>{$this->l('Piwik Cookies', $this->name)}</strong>"
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Session Cookie timeout', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'SESSION_TIMEOUT',
                    'required' => false,
                    'hint' => $this->l('this value must be set in minutes', $this->name),
                    'desc' => $this->l('Piwik Session Cookie timeout, the default is 30 minutes', $this->name),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Visitor Cookie timeout', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'COOKIE_TIMEOUT',
                    'required' => false,
                    'hint' => $this->l('this value must be set in minutes', $this->name),
                    'desc' => $this->l('Piwik Visitor Cookie timeout, the default is 13 months (569777 minutes)', $this->name),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Referral Cookie timeout', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'RCOOKIE_TIMEOUT',
                    'required' => false,
                    'hint' => $this->l('this value must be set in minutes', $this->name),
                    'desc' => $this->l('Piwik Referral Cookie timeout, the default is 6 months (262974 minutes)', $this->name),
                ),
                array(
                    'type' => 'html',
                    'name' => "<strong>{$this->l('Piwik Proxy Script', $this->name)}</strong>",
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use proxy script', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'USE_PROXY',
                    'desc' => $this->l('Whether or not to use the proxy insted of Piwik Host', $this->name),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled', $this->name)
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled', $this->name)
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Proxy script', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'PROXY_SCRIPT',
                    'hint' => $this->l('Example: www.example.com/pkproxy.php', $this->name),
                    'desc' => sprintf($this->l('the FULL url+path to proxy script to use, build-in: [%s]', $this->name), Context::getContext()->link->getModuleLink($this->module->name, 'piwik')),
                    'required' => false
                )
            ),
            'submit' => array(
                'title' => $this->l('Save', $this->name),
                'class' => 'btn btn-default'
            )
        );
        // $this->fields_form_override

        return parent::renderForm();
    }

    private function displayErrors($errors) {
        if (!empty($errors) && is_array($errors))
            foreach ($errors as $key => $value)
                $this->errors[] = Tools::displayError($value);
    }

    private function displayErrorsFromPiwikHelper() {
        $this->displayErrors(PiwikHelper::$errors);
        PiwikHelper::$errors = PiwikHelper::$error = "";
    }

}
