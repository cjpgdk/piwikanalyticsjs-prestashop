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

    public function processSubmitConfiguration() {
        if (Tools::isSubmit('submitAddconfiguration')) {

            // update site id.
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'SITEID')) {
                $piwikSiteID = (int) Tools::getValue(PiwikHelper::CPREFIX . 'SITEID');
                if (Validate::isInt($piwikSiteID) && $piwikSiteID > 0) {
                    Configuration::updateValue(PiwikHelper::CPREFIX . 'SITEID', $piwikSiteID);
                } else {
                    $this->errors[] = Tools::displayError($this->l('Piwik site id is not valid, must be an integer and higher than 0', $this->name));
                }
            }

            // update cookie domain
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'COOKIE_DOMAIN')) {
                $piwikCookieDomain = preg_replace('/\s+/', '', Tools::getValue(PiwikHelper::CPREFIX . 'COOKIE_DOMAIN'));
                Configuration::updateValue(PiwikHelper::CPREFIX . 'COOKIE_DOMAIN', $piwikCookieDomain);
            }

            // update alias urls
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'SET_DOMAINS')) {
                $piwikSetDomain = preg_replace('/\s+/', '', Tools::getValue(PiwikHelper::CPREFIX . 'SET_DOMAINS'));
                Configuration::updateValue(PiwikHelper::CPREFIX . 'SET_DOMAINS', $piwikSetDomain);
            }

            // update do not track
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'DNT')) {
                $piwikDNT = (int) Tools::getValue(PiwikHelper::CPREFIX . 'DNT');
                Configuration::updateValue(PiwikHelper::CPREFIX . 'DNT', ($piwikDNT == 1 ? 1 : 0));
            }

            // update currency
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY')) {
                $piwikDefaultCurrency = strtoupper(Tools::getValue(PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY'));
                if (!empty($piwikDefaultCurrency) && $piwikDefaultCurrency !== false) {
                    $cisd = Currency::getIdByIsoCode($piwikDefaultCurrency);
                    if ($cisd !== false && Validate::isInt($cisd)) {
                        Configuration::updateValue(PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY', $piwikDefaultCurrency);
                    } else {
                        $this->errors[] = Tools::displayError($this->l('Selected currency do not exists in this shop.!', $this->name));
                    }
                } else {
                    $this->errors[] = Tools::displayError($this->l('Currency cannot be empty', $this->name));
                }
            }

            // update extra html
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'EXHTML')) {
                $piwikEXHTML = Tools::getValue(PiwikHelper::CPREFIX . 'EXHTML');
                Configuration::updateValue(PKHelper::CPREFIX . 'EXHTML', $piwikEXHTML, TRUE);
            }

            // update report product id v1
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'PRODID_V1')) {
                $PRODID_V1 = Tools::getValue(PiwikHelper::CPREFIX . 'PRODID_V1');
                Configuration::updateValue(PKHelper::CPREFIX . 'PRODID_V1', $PRODID_V1);
            }

            // update report product id v2
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'PRODID_V2')) {
                $PRODID_V2 = Tools::getValue(PiwikHelper::CPREFIX . 'PRODID_V2');
                Configuration::updateValue(PKHelper::CPREFIX . 'PRODID_V2', $PRODID_V2);
            }

            // update report product id v3
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'PRODID_V3')) {
                $PRODID_V3 = Tools::getValue(PiwikHelper::CPREFIX . 'PRODID_V3');
                Configuration::updateValue(PKHelper::CPREFIX . 'PRODID_V3', $PRODID_V3);
            }

            // update session timout
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'SESSION_TIMEOUT')) {
                $piwikSessionTimout = (int) Tools::getValue(PiwikHelper::CPREFIX . 'SESSION_TIMEOUT');
                if (Validate::isInt($piwikSessionTimout) && $piwikSessionTimout > 0) {
                    Configuration::updateValue(PKHelper::CPREFIX . 'SESSION_TIMEOUT', $piwikSessionTimout);
                } else {
                    $this->errors[] = Tools::displayError($this->l('Session Timout is not valid, must be an integer and higher than 0', $this->name));
                }
            }

            // update cookie timout
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'COOKIE_TIMEOUT')) {
                $piwikCookieTimout = (int) Tools::getValue(PiwikHelper::CPREFIX . 'COOKIE_TIMEOUT');
                if (Validate::isInt($piwikCookieTimout) && $piwikCookieTimout > 0) {
                    Configuration::updateValue(PKHelper::CPREFIX . 'COOKIE_TIMEOUT', $piwikCookieTimout);
                } else {
                    $this->errors[] = Tools::displayError($this->l('Cookie Timout is not valid, must be an integer and higher than 0', $this->name));
                }
            }

            // update referral cookie timeout 
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'RCOOKIE_TIMEOUT')) {
                $piwikCookieTimout = (int) Tools::getValue(PiwikHelper::CPREFIX . 'RCOOKIE_TIMEOUT');
                if (Validate::isInt($piwikCookieTimout) && $piwikCookieTimout > 0) {
                    Configuration::updateValue(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT', $piwikCookieTimout);
                } else {
                    $this->errors[] = Tools::displayError($this->l('Referral Cookie Timout is not valid, must be an integer and higher than 0', $this->name));
                }
            }

            // update cookie path 
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'COOKIE_PATH')) {
                $piwikCookiePath = preg_replace('/\s+/', '', Tools::getValue(PiwikHelper::CPREFIX . 'COOKIE_PATH'));
                Configuration::updateValue(PiwikHelper::CPREFIX . 'COOKIE_PATH', $piwikCookiePath);
            }

            // update use proxy
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'USE_PROXY')) {
                $USE_PROXY = Tools::getValue(PiwikHelper::CPREFIX . 'USE_PROXY');
                Configuration::updateValue(PiwikHelper::CPREFIX . 'USE_PROXY', ($USE_PROXY == 1 ? 1 : 0));
            }

            // update proxy script
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'PROXY_SCRIPT')) {
                $PROXY_SCRIPT = str_replace(
                        array("http://", "https://", '//'),
                        '',
                        preg_replace('/\s+/', '', Tools::getValue(PiwikHelper::CPREFIX . 'PROXY_SCRIPT'))
                );
                Configuration::updateValue(PiwikHelper::CPREFIX . 'PROXY_SCRIPT', $PROXY_SCRIPT);
            }

            $this->confirmations[] = $this->l("Update process complete", $this->name);
        }
    }

    public function renderForm() {
        $this->processSubmitConfiguration();

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
            PiwikHelper::CPREFIX . 'COOKIE_PATH' => Configuration::get(PiwikHelper::CPREFIX . 'COOKIE_PATH'),
        );

        $piwik_host = $this->fields_value[PiwikHelper::CPREFIX . 'HOST'];
        $piwik_token = $this->fields_value[PiwikHelper::CPREFIX . 'TOKEN_AUTH'];
        $piwik_site = null;
        if (!empty($piwik_token) && ($piwik_token !== false) && ((!empty($piwik_host) && ($piwik_host !== false)))) {
            // get current selected site
            $piwik_site = PiwikHelper::getPiwikSite();
        } else {
            $this->errors[] = str_replace(
                    array('{LINK}', '{/LINK}'), 
                    array('<a href="' . Context::getContext()->link->getAdminLink('PiwikAnalyticsSiteManager') . '">', "</a>"), 
                    Tools::displayError($this->l("You need to configure Piwik Site ID and/or Auth token in {LINK}Site Manager{/LINK}", $this->name))
            );
        }

        // display host/token or link to manager.
        if (empty($piwik_host) || ($piwik_host === false)) {
            $piwik_host = "<a href='" . Context::getContext()->link->getAdminLink('PiwikAnalyticsSiteManager') . "'>" . $this->l('Missing click here to goto Site Manager.', $this->name) . "</a>";
        }
        if (empty($piwik_token) || ($piwik_token === false)) {
            $piwik_token = "<a href='" . Context::getContext()->link->getAdminLink('PiwikAnalyticsSiteManager') . "'>" . $this->l('Missing click here to goto Site Manager.', $this->name) . "</a>";
        } else {
            $piwik_token = $this->l('***HIDDEN***', $this->name);
        }

        // do stuff with valid site
        $piwik_currency = $this->l('unknown', $this->name);
        $piwik_image_url = "";
        $piwik_image_url_proxy = "";
        if ($piwik_site !== false) {
            $piwik_currency = $piwik_site[0]->currency;
            $piwik_image_url = '&lt;noscript&gt;' . htmlentities(PiwikHelper::getImageTrackingCode()) . '&lt;/noscript&gt;';

            if ((bool) Configuration::get('PS_REWRITING_SETTINGS'))
                $piwik_image_url_proxy = str_replace($this->fields_value[PiwikHelper::CPREFIX . 'HOST'] . 'piwik.php', $this->fields_value[PiwikHelper::CPREFIX . 'PROXY_SCRIPT'], $piwik_image_url);
            else
                $piwik_image_url_proxy = str_replace($this->fields_value[PiwikHelper::CPREFIX . 'HOST'] . 'piwik.php?', $this->fields_value[PiwikHelper::CPREFIX . 'PROXY_SCRIPT'] . '&', $piwik_image_url);
        }

        // done with piwik lookups display errors if any
        $this->displayErrorsFromPiwikHelper();

        // load currencies installed in the shop
        $piwik_currency_valid = false;
        $currencies = array();
        foreach (Currency::getCurrencies() as $key => $val) {
            $currencies[$key] = array(
                'iso_code' => $val['iso_code'],
                'name' => "{$val['name']} {$val['iso_code']}",
            );

            if ($this->fields_value[PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY'] == $val['iso_code'])
                $piwik_currency_valid = true;
        }
        if (!$piwik_currency_valid) {
            $this->errors[] = Tools::displayError(sprintf($this->l("The selected currency (%s), is not installed in this shop, either install it or change it.", $this->name), $this->fields_value[PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY']));
        }
        if ($this->fields_value[PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY'] != $piwik_currency) {
            $this->errors[] = Tools::displayError(sprintf($this->l("The selected currency (%s), do not match the currency selected in piwik (%s)", $this->name), $this->fields_value[PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY'], $piwik_currency));
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
                    ),
                    array(
                        'type' => 'html',
                        'name' => $this->l('Piwik image tracking code append one of them to field "Extra HTML" this will add images tracking code to all your pages', $this->name) . "<br>"
                        . "<strong>" . $this->l('default', $this->name) . "</strong>:<br /><i>{$piwik_image_url}</i><br>"
                        . "<strong>" . $this->l('using proxy script', $this->name) . "</strong>:<br /><i>{$piwik_image_url_proxy}</i><br>"
                        . (version_compare(_PS_VERSION_, '1.6.0.7', '>=') ?
                                "<br><strong>{$this->l("Before you add the image tracking code make sure the HTMLPurifier library isn't in use, check the settings in 'Preferences => General', you can enable the HTMLPurifier again after you made your changes", $this->name)}</strong>" :
                                ""
                        )
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Extra HTML', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'EXHTML',
                        'desc' => $this->l('Some extra HTML code to put after the piwik tracking code, this can be any html of your choice', $this->name),
                        'rows' => 10,
                        'cols' => 50,
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
                    'label' => $this->l('Piwik Cookie Path', $this->name),
                    'name' => PiwikHelper::CPREFIX . 'COOKIE_PATH',
                    'required' => false,
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
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save', $this->name),
                'class' => 'btn btn-default'
            )
        );
        // $this->fields_form_override

        return parent::renderForm();
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
        if (!empty($errors) && is_array($errors))
            foreach ($errors as $key => $value)
                $this->errors[] = Tools::displayError($value);
    }

    private function displayErrorsFromPiwikHelper() {
        $this->displayErrors(PiwikHelper::$errors);
        PiwikHelper::$errors = PiwikHelper::$error = "";
    }

}
