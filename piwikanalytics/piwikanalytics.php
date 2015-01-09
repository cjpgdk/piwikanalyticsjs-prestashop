<?php

if (!defined('_PS_VERSION_'))
    exit;

/*
 * Copyright (C) 2014 Christian Jensen
 *
 * This file is part of piwikanalytics for prestashop.
 * 
 * piwikanalytics for prestashop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * piwikanalytics for prestashop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with piwikanalytics for prestashop.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* Backward compatibility */
if (_PS_VERSION_ < '1.5') {
    if (version_compare(_PS_VERSION_, '1.4.5.1', '<=')) {
        include _PS_ROOT_DIR_ . '/modules/piwikmanager/_classes/backward_compatibility/global.php';
    } else {
        require_once dirname(__FILE__) . '/../piwikmanager/_classes/backward_compatibility/global.php';
    }
}
/* Helpers */
require_once dirname(__FILE__) . '/../piwikmanager/_classes/MyHelperClass.php';
require_once dirname(__FILE__) . '/../piwikmanager/_classes/PKHelper.php';

class piwikanalytics extends Module {

    private static $_isOrder = FALSE;
    protected $_errors = "";
    protected $default_currency = array();
    protected $currencies = array();

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

    public function __construct($name = null, $context = null) {
        $this->dependencies[] = "piwikmanager";
        
        $this->name = 'piwikanalytics';
        $this->tab = 'analytics_stats';
        $this->version = '1b';
        $this->author = 'Christian M. Jensen';
        $this->displayName = 'Piwik Analytics Tracking';
        $this->bootstrap = true;
        if (_PS_VERSION_ < '1.5' && _PS_VERSION_ > '1.3')
            parent::__construct($name);
        /* Prestashop 1.5 and up implements "$context" */
        if (_PS_VERSION_ >= '1.5')
            parent::__construct($name, ($context instanceof Context ? $context : NULL));

        $this->description = $this->l('Adds Piwik Analytics JavaScript Tracking code to your shop');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this plugin ?');


        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            if (version_compare(_PS_VERSION_, '1.4.5.1', '<=')) {
                include _PS_ROOT_DIR_ . '/modules/piwikmanager/_classes/backward_compatibility/backward.php';
            } else {
                require dirname(__FILE__) . '/../piwikmanager/_classes/backward_compatibility/backward.php';
            }
        }
        self::$_isOrder = FALSE;
    }

    /**
     * get content to display in the admin area
     * @global string $currentIndex
     * @return string
     */
    public function getContent() {
        if (_PS_VERSION_ < '1.5')
            global $currentIndex;

        $_html = "";
        $_html .= $this->processFormsUpdate();
        $this->__setCurrencies();

        $fields_form = array();

        $languages = Language::getLanguages(FALSE);
        foreach ($languages as $languages_key => $languages_value) {
            // is_default
            $languages[$languages_key]['is_default'] = ($languages_value['id_lang'] == (int) Configuration::get('PS_LANG_DEFAULT') ? true : false);
        }


        $helper = MyHelperClass::GetHelperFormObject($this, $this->name, $this->identifier, Tools::getAdminTokenLite('AdminModules'));

        if (_PS_VERSION_ < '1.5')
            $helper->currentIndex = $currentIndex . '&configure=' . $this->name;
        else
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitUpdate' . $this->name;

        $fields_form[0]['form']['legend'] = array(
            'title' => $this->displayName,
            'image' => (_PS_VERSION_ < '1.5' ? $this->_path . 'logo.gif' : $this->_path . 'logo.png')
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'switch',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Use proxy script'),
            'name' => PKHelper::CPREFIX . 'USE_PROXY',
            'desc' => $this->l('Whether or not to use the proxy insted of Piwik Host'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled')
                )
            ),
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Proxy script'),
            'name' => PKHelper::CPREFIX . 'PROXY_SCRIPT',
            'hint' => $this->l('Example: www.example.com/pkproxy.php'),
            'desc' => sprintf($this->l('the FULL path to proxy script to use, build-in: [%s]'), self::getModuleLink($this->name, 'piwik')),
            'required' => false
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Track visitors across subdomains'),
            'name' => PKHelper::CPREFIX . 'COOKIE_DOMAIN',
            'desc' => $this->l('The default is the document domain; if your web site can be visited at both www.example.com and example.com, you would use: "*.example.com" OR ".example.com" without the quotes')
            . '<br />'
            . $this->l('Leave empty to exclude this from the tracking code'),
            'hint' => $this->l('So if one visitor visits x.example.com and y.example.com, they will be counted as a unique visitor. (setCookieDomain)'),
            'required' => false
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Hide known alias URLs'),
            'name' => PKHelper::CPREFIX . 'SET_DOMAINS',
            'desc' => $this->l('In the "Outlinks" report, hide clicks to known alias URLs, Example: *.example.com')
            . '<br />'
            . $this->l('Note: to add multiple domains you must separate them with space " " one space')
            . '<br />'
            . $this->l('Note: the currently tracked website is added to this array automatically')
            . '<br />'
            . $this->l('Leave empty to exclude this from the tracking code'),
            'hint' => $this->l('So clicks on links to Alias URLs (eg. x.example.com) will not be counted as "Outlink". (setDomains)'),
            'required' => false
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'switch',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Enable client side DoNotTrack detection'),
            'name' => PKHelper::CPREFIX . 'DNT',
            'desc' => $this->l('So tracking requests will not be sent if visitors do not wish to be tracked.'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled')
                )
            ),
        );
        $image_tracking = PKHelper::getPiwikImageTrackingCode();
        $this->displayErrors(PKHelper::$errors);
        PKHelper::$errors = PKHelper::$error = "";
        $fields_form[0]['form']['input'][] = array(
            'type' => 'html',
            'name' => $this->l('Piwik image tracking code append one of them to field "Extra HTML" this will add images tracking code to all your pages') . "<br>"
            . "<strong>" . $this->l('default') . "</strong>:<br /><i>{$image_tracking['default']}</i><br>"
            . "<strong>" . $this->l('using proxy script') . "</strong>:<br /><i>{$image_tracking['proxy']}</i><br>"
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'textarea',
            'label' => $this->l('Extra HTML'),
            'name' => PKHelper::CPREFIX . 'EXHTML',
            'desc' => $this->l('Some extra HTML code to put after the piwik tracking code, this can be any html of your choice'),
            'rows' => 10,
            'cols' => 50,
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'select',
            'label' => $this->l('Piwik Currency'),
            'name' => PKHelper::CPREFIX . 'DEFAULT_CURRENCY',
            'desc' => sprintf($this->l('Based on your settings in Piwik your default currency is %s'), ($this->piwikSite !== FALSE ? $this->piwikSite[0]->currency : $this->l('unknown'))),
            'options' => array(
                'default' => $this->default_currency,
                'query' => $this->currencies,
                'id' => 'iso_code',
                'name' => 'name'
            ),
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'select',
            'label' => $this->l('Piwik Report date'),
            'name' => PKHelper::CPREFIX . 'DREPDATE',
            'desc' => $this->l('Report date to load by default from "Stats => Piwik Analytics"'),
            'options' => array(
                'default' => array('value' => 'day|today', 'label' => $this->l('Today')),
                'query' => array(
                    array('str' => 'day|today', 'name' => $this->l('Today')),
                    array('str' => 'day|yesterday', 'name' => $this->l('Yesterday')),
                    array('str' => 'range|previous7', 'name' => $this->l('Previous 7 days (not including today)')),
                    array('str' => 'range|previous30', 'name' => $this->l('Previous 30 days (not including today)')),
                    array('str' => 'range|last7', 'name' => $this->l('Last 7 days (including today)')),
                    array('str' => 'range|last30', 'name' => $this->l('Last 30 days (including today)')),
                    array('str' => 'week|today', 'name' => $this->l('Current Week')),
                    array('str' => 'month|today', 'name' => $this->l('Current Month')),
                    array('str' => 'year|today', 'name' => $this->l('Current Year')),
                ),
                'id' => 'str',
                'name' => 'name'
            ),
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik User name'),
            'name' => PKHelper::CPREFIX . 'USRNAME',
            'desc' => $this->l('You can store your Username for Piwik here to make it easy to open your piwik interface from your stats page with automatic login'),
            'required' => false
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'password',
            'label' => $this->l('Piwik User password'),
            'name' => PKHelper::CPREFIX . 'USRPASSWD',
            'desc' => $this->l('You can store your Password for Piwik here to make it easy to open your piwik interface from your stats page with automatic login'),
            'required' => false
        );

        $fields_form[0]['form']['submit'] = array(
            'title' => $this->l('Save'),
        );



        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->displayName . ' ' . $this->l('Advanced'),
                'image' => (_PS_VERSION_ < '1.5' ? $this->_path . 'logo.gif' : $this->_path . 'logo.png')
            ),
            'input' => array(
                array(
                    'type' => 'html',
                    'name' => $this->l('In this section you can modify certain aspects of the way this plugin sends products, searches, category view etc.. to piwik')
                ),
                array(
                    'type' => 'switch',
                    'is_bool' => true, //retro compat 1.5
                    'label' => $this->l('Use HTTPS'),
                    'name' => PKHelper::CPREFIX . 'CRHTTPS',
                    'hint' => $this->l('ONLY enable this feature if your piwik installation is accessible via https'),
                    'desc' => $this->l('use Hypertext Transfer Protocol Secure (HTTPS) in all requests from code to piwik, this only affects how requests are sent from proxy script to piwik, your visitors will still use the protocol they visit your shop with'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'html',
                    'name' => $this->l('in the next few inputs you can set how the product id is passed on to piwik')
                    . '<br />'
                    . $this->l('there are three variables you can use:')
                    . '<br />'
                    . $this->l('{ID} : this variable is replaced with id the product has in prestashop')
                    . '<br />'
                    . $this->l('{REFERENCE} : this variable is replaced with the unique reference you when adding adding/updating a product, this variable is only available in prestashop 1.5 and up')
                    . '<br />'
                    . $this->l('{ATTRID} : this variable is replaced with id the product attribute')
                    . '<br />'
                    . $this->l('in cases where only the product id is available it be parsed as ID and nothing else'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product id V1'),
                    'name' => PKHelper::CPREFIX . 'PRODID_V1',
                    'desc' => $this->l('This template is used in case ALL three values are available ("Product ID", "Product Attribute ID" and "Product Reference")'),
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product id V2'),
                    'name' => PKHelper::CPREFIX . 'PRODID_V2',
                    'desc' => $this->l('This template is used in case only "Product ID" and "Product Reference" are available'),
                    'required' => false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product id V3'),
                    'name' => PKHelper::CPREFIX . 'PRODID_V3',
                    'desc' => $this->l('This template is used in case only "Product ID" and "Product Attribute ID" are available'),
                    'required' => false
                ),
                array(
                    'type' => 'html',
                    'name' => "<strong>{$this->l('Piwik Cookies')}</strong>"
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Session Cookie timeout'),
                    'name' => PKHelper::CPREFIX . 'SESSION_TIMEOUT',
                    'required' => false,
                    'hint' => $this->l('this value must be set in minutes'),
                    'desc' => $this->l('Piwik Session Cookie timeout, the default is 30 minutes'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Visitor Cookie timeout'),
                    'name' => PKHelper::CPREFIX . 'COOKIE_TIMEOUT',
                    'required' => false,
                    'hint' => $this->l('this value must be set in minutes'),
                    'desc' => $this->l('Piwik Visitor Cookie timeout, the default is 13 months (569777 minutes)'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Referral Cookie timeout'),
                    'name' => PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT',
                    'required' => false,
                    'hint' => $this->l('this value must be set in minutes'),
                    'desc' => $this->l('Piwik Referral Cookie timeout, the default is 6 months (262974 minutes)'),
                ),
                array(
                    'type' => 'html',
                    'name' => "<strong>{$this->l('Piwik Proxy Script Authorization? if your piwik is installed behind HTTP Basic Authorization (Both password and username must be filled before the values will be used)')}</strong>"
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Proxy Script Username'),
                    'name' => PKHelper::CPREFIX . 'PAUTHUSR',
                    'required' => false,
                    'desc' => $this->l('this field along with password can be used if your piwik installation is protected by HTTP Basic Authorization'),
                ),
                array(
                    'type' => 'password',
                    'label' => $this->l('Proxy Script Password'),
                    'name' => PKHelper::CPREFIX . 'PAUTHPWD',
                    'required' => false,
                    'desc' => $this->l('this field along with username can be used if your piwik installation is protected by HTTP Basic Authorization'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        $helper->fields_value = $this->getFormFields();
        return $this->_errors . $_html . $helper->generateForm($fields_form);
    }

    protected function getFormFields() {
        $PIWIK_PRODID_V1 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V1');
        $PIWIK_PRODID_V2 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V2');
        $PIWIK_PRODID_V3 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V3');
        $PIWIK_PROXY_SCRIPT = Configuration::get(PKHelper::CPREFIX . 'PROXY_SCRIPT');
        $PIWIK_RCOOKIE_TIMEOUT = (int) Configuration::get(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT');
        $PIWIK_COOKIE_TIMEOUT = (int) Configuration::get(PKHelper::CPREFIX . 'COOKIE_TIMEOUT');
        $PIWIK_SESSION_TIMEOUT = (int) Configuration::get(PKHelper::CPREFIX . 'SESSION_TIMEOUT');
        return array(
            PKHelper::CPREFIX . 'SESSION_TIMEOUT' => ($PIWIK_SESSION_TIMEOUT != 0 ? (int) ($PIWIK_SESSION_TIMEOUT / 60) : (int) (self::PK_SC_TIMEOUT )),
            PKHelper::CPREFIX . 'COOKIE_TIMEOUT' => ($PIWIK_COOKIE_TIMEOUT != 0 ? (int) ($PIWIK_COOKIE_TIMEOUT / 60) : (int) (self::PK_VC_TIMEOUT)),
            PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT' => ($PIWIK_RCOOKIE_TIMEOUT != 0 ? (int) ($PIWIK_RCOOKIE_TIMEOUT / 60) : (int) (self::PK_RC_TIMEOUT)),
            PKHelper::CPREFIX . 'USE_PROXY' => Configuration::get(PKHelper::CPREFIX . 'USE_PROXY'),
            PKHelper::CPREFIX . 'EXHTML' => Configuration::get(PKHelper::CPREFIX . 'EXHTML'),
            PKHelper::CPREFIX . 'CRHTTPS' => Configuration::get(PKHelper::CPREFIX . 'CRHTTPS'),
            PKHelper::CPREFIX . 'DEFAULT_CURRENCY' => Configuration::get("PIWIK_DEFAULT_CURRENCY"),
            PKHelper::CPREFIX . 'PRODID_V1' => (!empty($PIWIK_PRODID_V1) ? $PIWIK_PRODID_V1 : '{ID}-{ATTRID}#{REFERENCE}'),
            PKHelper::CPREFIX . 'PRODID_V2' => (!empty($PIWIK_PRODID_V2) ? $PIWIK_PRODID_V2 : '{ID}#{REFERENCE}'),
            PKHelper::CPREFIX . 'PRODID_V3' => (!empty($PIWIK_PRODID_V3) ? $PIWIK_PRODID_V3 : '{ID}-{ATTRID}'),
            PKHelper::CPREFIX . 'COOKIE_DOMAIN' => Configuration::get(PKHelper::CPREFIX . 'COOKIE_DOMAIN'),
            PKHelper::CPREFIX . 'SET_DOMAINS' => Configuration::get(PKHelper::CPREFIX . 'SET_DOMAINS'),
            PKHelper::CPREFIX . 'DNT' => Configuration::get(PKHelper::CPREFIX . 'DNT'),
            PKHelper::CPREFIX . 'PROXY_SCRIPT' => empty($PIWIK_PROXY_SCRIPT) ? str_replace(array("http://", "https://"), '', self::getModuleLink($this->name, 'piwik')) : $PIWIK_PROXY_SCRIPT,
            PKHelper::CPREFIX . 'USRNAME' => Configuration::get(PKHelper::CPREFIX . 'USRNAME'),
            PKHelper::CPREFIX . 'USRPASSWD' => Configuration::get(PKHelper::CPREFIX . 'USRPASSWD'),
            PKHelper::CPREFIX . 'PAUTHUSR' => Configuration::get(PKHelper::CPREFIX . 'PAUTHUSR'),
            PKHelper::CPREFIX . 'PAUTHPWD' => Configuration::get(PKHelper::CPREFIX . 'PAUTHPWD'),
            PKHelper::CPREFIX . 'DREPDATE' => Configuration::get(PKHelper::CPREFIX . 'DREPDATE'),
        );
    }

    private function processFormsUpdate() {

        $_html = "";
        if (Tools::isSubmit('submitUpdate' . $this->name)) {

            /* setReferralCookieTimeout */
            if (Tools::getIsset(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT')) {
                // the default is 6 months
                $tmp = (int) Tools::getValue(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT', self::PK_RC_TIMEOUT);
                $tmp = (int) ($tmp * 60); //* convert to seconds
                Configuration::updateValue(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT', $tmp);
            }
            /* setVisitorCookieTimeout */
            if (Tools::getIsset(PKHelper::CPREFIX . 'COOKIE_TIMEOUT')) {
                // the default is 13 months
                $tmp = (int) Tools::getValue(PKHelper::CPREFIX . 'COOKIE_TIMEOUT', self::PK_VC_TIMEOUT);
                $tmp = (int) ($tmp * 60); //* convert to seconds
                Configuration::updateValue(PKHelper::CPREFIX . 'COOKIE_TIMEOUT', $tmp);
            }
            /* setSessionCookieTimeout */
            if (Tools::getIsset(PKHelper::CPREFIX . 'SESSION_TIMEOUT')) {
                // the default is 30 minutes
                $tmp = (int) Tools::getValue(PKHelper::CPREFIX . 'SESSION_TIMEOUT', self::PK_SC_TIMEOUT);
                $tmp = (int) ($tmp * 60); //* convert to seconds
                Configuration::updateValue(PKHelper::CPREFIX . 'SESSION_TIMEOUT', $tmp);
            }
            /*
             * @todo VALIDATE!!!, YES VALIDATE!!! thank you ...
             */
            if (Tools::getIsset(PKHelper::CPREFIX . 'USE_PROXY'))
                Configuration::updateValue(PKHelper::CPREFIX . 'USE_PROXY', Tools::getValue(PKHelper::CPREFIX . 'USE_PROXY'));
            if (Tools::getIsset(PKHelper::CPREFIX . 'EXHTML'))
                Configuration::updateValue(PKHelper::CPREFIX . 'EXHTML', Tools::getValue(PKHelper::CPREFIX . 'EXHTML'), TRUE);
            if (Tools::getIsset(PKHelper::CPREFIX . 'COOKIE_DOMAIN'))
                Configuration::updateValue(PKHelper::CPREFIX . 'COOKIE_DOMAIN', Tools::getValue(PKHelper::CPREFIX . 'COOKIE_DOMAIN'));
            if (Tools::getIsset(PKHelper::CPREFIX . 'SET_DOMAINS'))
                Configuration::updateValue(PKHelper::CPREFIX . 'SET_DOMAINS', Tools::getValue(PKHelper::CPREFIX . 'SET_DOMAINS'));
            if (Tools::getIsset(PKHelper::CPREFIX . 'DNT'))
                Configuration::updateValue(PKHelper::CPREFIX . 'DNT', Tools::getValue(PKHelper::CPREFIX . 'DNT', 0));
            if (Tools::getIsset(PKHelper::CPREFIX . 'PROXY_SCRIPT'))
                Configuration::updateValue(PKHelper::CPREFIX . 'PROXY_SCRIPT', str_replace(array("http://", "https://", '//'), '', Tools::getValue(PKHelper::CPREFIX . 'PROXY_SCRIPT')));
            if (Tools::getIsset(PKHelper::CPREFIX . 'CRHTTPS'))
                Configuration::updateValue(PKHelper::CPREFIX . 'CRHTTPS', Tools::getValue(PKHelper::CPREFIX . 'CRHTTPS', 0));
            if (Tools::getIsset(PKHelper::CPREFIX . 'PRODID_V1'))
                Configuration::updateValue(PKHelper::CPREFIX . 'PRODID_V1', Tools::getValue(PKHelper::CPREFIX . 'PRODID_V1', '{ID}-{ATTRID}#{REFERENCE}'));
            if (Tools::getIsset(PKHelper::CPREFIX . 'PRODID_V2'))
                Configuration::updateValue(PKHelper::CPREFIX . 'PRODID_V2', Tools::getValue(PKHelper::CPREFIX . 'PRODID_V2', '{ID}#{REFERENCE}'));
            if (Tools::getIsset(PKHelper::CPREFIX . 'PRODID_V3'))
                Configuration::updateValue(PKHelper::CPREFIX . 'PRODID_V3', Tools::getValue(PKHelper::CPREFIX . 'PRODID_V3', '{ID}#{ATTRID}'));
            if (Tools::getIsset(PKHelper::CPREFIX . 'DEFAULT_CURRENCY'))
                Configuration::updateValue(PKHelper::CPREFIX . "DEFAULT_CURRENCY", Tools::getValue(PKHelper::CPREFIX . 'DEFAULT_CURRENCY', 'EUR'));

            if (Tools::getIsset(PKHelper::CPREFIX . 'USRNAME'))
                Configuration::updateValue(PKHelper::CPREFIX . "USRNAME", Tools::getValue(PKHelper::CPREFIX . 'USRNAME', ''));
            if (Tools::getIsset(PKHelper::CPREFIX . 'USRPASSWD') && Tools::getValue(PKHelper::CPREFIX . 'USRPASSWD', '') != "")
                Configuration::updateValue(PKHelper::CPREFIX . "USRPASSWD", Tools::getValue(PKHelper::CPREFIX . 'USRPASSWD', Configuration::get(PKHelper::CPREFIX . 'USRPASSWD')));

            if (Tools::getIsset(PKHelper::CPREFIX . 'PAUTHUSR'))
                Configuration::updateValue(PKHelper::CPREFIX . "PAUTHUSR", Tools::getValue(PKHelper::CPREFIX . 'PAUTHUSR', ''));
            if (Tools::getIsset(PKHelper::CPREFIX . 'PAUTHPWD') && Tools::getValue(PKHelper::CPREFIX . 'PAUTHPWD', '') != "")
                Configuration::updateValue(PKHelper::CPREFIX . "PAUTHPWD", Tools::getValue(PKHelper::CPREFIX . 'PAUTHPWD', Configuration::get(PKHelper::CPREFIX . 'PAUTHPWD')));

            if (Tools::getIsset(PKHelper::CPREFIX . 'DREPDATE'))
                Configuration::updateValue(PKHelper::CPREFIX . "DREPDATE", Tools::getValue(PKHelper::CPREFIX . 'DREPDATE', 'day|tody'));

            $_html .= $this->displayConfirmation($this->l('Configuration Updated'));
        }
        return $_html;
    }

    /* HOOKs */

    /**
     * PIWIK don't track links on the same site eg. 
     * if product is view in an iframe so we add this and makes sure that it is content only view 
     * @param mixed $param
     * @return string
     */
    public function hookdisplayRightColumnProduct($param) {
        if ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0)
            return "";
        if ((int) Tools::getValue('content_only') > 0 && get_class($this->context->controller) == 'ProductController') { // we also do this in the tpl file.!
            return $this->hookFooter($param);
        }
    }

    /**
     * Search action
     * @param array $param
     */
    public function hookactionSearch($param) {
        if ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0)
            return "";
        $param['total'] = intval($param['total']);
        /* if multi pages in search add page number of current if set! */
        $page = "";
        if (Tools::getIsset('p')) {
            $page = " (" . Tools::getValue('p') . ")";
        }
        // $param['expr'] is not the searched word if lets say search is Snitmøntre then the $param['expr'] will be Snitmontre
        $expr = Tools::getIsset('search_query') ? htmlentities(Tools::getValue('search_query')) : $param['expr'];
        $this->context->smarty->assign(array(
            PKHelper::CPREFIX . 'SITE_SEARCH' => "_paq.push(['trackSiteSearch',\"{$expr}{$page}\",false,{$param['total']}]);"
        ));
    }

    /**
     * only checks that the module is registered in hook "footer", 
     * this why we only appent javescript to the end of the page!
     * @param mixed $params
     */
    public function hookHeader($params) {
        if (!$this->isRegisteredInHook('footer'))
            $this->registerHook('footer');
    }

    public function hookOrderConfirmation($params) {
        if ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0)
            return "";

        $order = $params['objOrder'];
        if (Validate::isLoadedObject($order)) {

            $this->__setConfigDefault();

            $this->context->smarty->assign(PKHelper::CPREFIX . 'ORDER', TRUE);
            $this->context->smarty->assign(PKHelper::CPREFIX . 'CART', FALSE);


            $smarty_ad = array();
            foreach ($params['objOrder']->getProductsDetail() as $value) {
                $smarty_ad[] = array(
                    'SKU' => $this->parseProductSku($value['product_id'], (isset($value['product_attribute_id']) ? $value['product_attribute_id'] : FALSE), (isset($value['product_reference']) ? $value['product_reference'] : FALSE)),
                    'NAME' => $value['product_name'],
                    'CATEGORY' => $this->get_category_names_by_product($value['product_id'], FALSE),
                    'PRICE' => $this->currencyConvertion(
                            array(
                                'price' => (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : 0.00)),
                                'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                            )
                    ),
                    'QUANTITY' => $value['product_quantity'],
                );
            }
            $this->context->smarty->assign(PKHelper::CPREFIX . 'ORDER_PRODUCTS', $smarty_ad);
            if (isset($params['objOrder']->total_paid_tax_incl) && isset($params['objOrder']->total_paid_tax_excl))
                $tax = $params['objOrder']->total_paid_tax_incl - $params['objOrder']->total_paid_tax_excl;
            else if (isset($params['objOrder']->total_products_wt) && isset($params['objOrder']->total_products))
                $tax = $params['objOrder']->total_products_wt - $params['objOrder']->total_products;
            else
                $tax = 0.00;
            $ORDER_DETAILS = array(
                'order_id' => $params['objOrder']->id,
                'order_total' => $this->currencyConvertion(
                        array(
                            'price' => floatval(isset($params['objOrder']->total_paid_tax_incl) ? $params['objOrder']->total_paid_tax_incl : (isset($params['objOrder']->total_paid) ? $params['objOrder']->total_paid : 0.00)),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
                'order_sub_total' => $this->currencyConvertion(
                        array(
                            'price' => floatval($params['objOrder']->total_products_wt),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
                'order_tax' => $this->currencyConvertion(
                        array(
                            'price' => floatval($tax),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
                'order_shipping' => $this->currencyConvertion(
                        array(
                            'price' => floatval((isset($params['objOrder']->total_shipping_tax_incl) ? $params['objOrder']->total_shipping_tax_incl : (isset($params['objOrder']->total_shipping) ? $params['objOrder']->total_shipping : 0.00))),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
                'order_discount' => $this->currencyConvertion(
                        array(
                            'price' => (isset($params['objOrder']->total_discounts_tax_incl) ?
                                    ($params['objOrder']->total_discounts_tax_incl > 0 ?
                                            floatval($params['objOrder']->total_discounts_tax_incl) : false) : (isset($params['objOrder']->total_discounts) ?
                                            ($params['objOrder']->total_discounts > 0 ?
                                                    floatval($params['objOrder']->total_discounts) : false) : 0.00)),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
            );
            $this->context->smarty->assign(PKHelper::CPREFIX . 'ORDER_DETAILS', $ORDER_DETAILS);

            // avoid double tracking on complete order.
            self::$_isOrder = TRUE;
            return $this->display(__FILE__, 'views/templates/hook/jstracking.tpl');
        }
    }

    public function hookFooter($params) {
        if ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0)
            return "";

        if (self::$_isOrder)
            return "";


        if (_PS_VERSION_ < '1.5.6') {
            /* get page name the LAME way :) */
            if (method_exists($this->context->smarty, 'get_template_vars')) { /* smarty_2 */
                $page_name = $this->context->smarty->get_template_vars('page_name');
            } else if (method_exists($this->context->smarty, 'getTemplateVars')) {/* smarty */
                $page_name = $this->context->smarty->getTemplateVars('page_name');
            } else
                $page_name = "";
        }
        $this->__setConfigDefault();
        $this->context->smarty->assign(PKHelper::CPREFIX . 'ORDER', FALSE);

        /* cart tracking */
        if (!$this->context->cookie->PIWIKTrackCartFooter) {
            $this->context->cookie->PIWIKTrackCartFooter = time();
        }
        if (strtotime($this->context->cart->date_upd) >= $this->context->cookie->PIWIKTrackCartFooter) {
            $this->context->cookie->PIWIKTrackCartFooter = strtotime($this->context->cart->date_upd) + 2;
            $smarty_ad = array();

            $Currency = new Currency($this->context->cart->id_currency);
            foreach ($this->context->cart->getProducts() as $key => $value) {
                if (!isset($value['id_product']) || !isset($value['name']) || !isset($value['total_wt']) || !isset($value['quantity'])) {
                    continue;
                }
                $smarty_ad[] = array(
                    'SKU' => $this->parseProductSku($value['id_product'], (isset($value['id_product_attribute']) && $value['id_product_attribute'] > 0 ? $value['id_product_attribute'] : FALSE), (isset($value['reference']) ? $value['reference'] : FALSE)),
                    'NAME' => $value['name'] . (isset($value['attributes']) ? ' (' . $value['attributes'] . ')' : ''),
                    'CATEGORY' => $this->get_category_names_by_product($value['id_product'], FALSE),
                    'PRICE' => $this->currencyConvertion(
                            array(
                                'price' => $value['total_wt'],
                                'conversion_rate' => $Currency->conversion_rate,
                            )
                    ),
                    'QUANTITY' => $value['quantity'],
                );
            }
            if (count($smarty_ad) > 0) {
                $this->context->smarty->assign(PKHelper::CPREFIX . 'CART', TRUE);
                $this->context->smarty->assign(PKHelper::CPREFIX . 'CART_PRODUCTS', $smarty_ad);
                $this->context->smarty->assign(PKHelper::CPREFIX . 'CART_TOTAL', $this->currencyConvertion(
                                array(
                                    'price' => $this->context->cart->getOrderTotal(),
                                    'conversion_rate' => $Currency->conversion_rate,
                                )
                ));
            } else {
                $this->context->smarty->assign(PKHelper::CPREFIX . 'CART', FALSE);
            }
            unset($smarty_ad);
        } else {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'CART', FALSE);
        }

        if (_PS_VERSION_ < '1.5.6')
            $this->_hookFooterPS14($params, $page_name);
        else if (_PS_VERSION_ >= '1.5')
            $this->_hookFooter($params);

        return $this->display(__FILE__, 'views/templates/hook/jstracking.tpl');
    }

    /**
     * add Prestashop !LATEST! specific settings
     * @param mixed $params
     * @since 0.4
     */
    private function _hookFooter($params) {

        /* product tracking */
        if (get_class($this->context->controller) == 'ProductController') {
            $products = array(array('product' => $this->context->controller->getProduct(), 'categorys' => NULL));
            if (isset($products) && isset($products[0]['product'])) {
                $smarty_ad = array();
                foreach ($products as $product) {
                    if (!Validate::isLoadedObject($product['product']))
                        continue;
                    if ($product['categorys'] == NULL)
                        $product['categorys'] = $this->get_category_names_by_product($product['product']->id, FALSE);
                    $smarty_ad[] = array(
                        /* (required) SKU: Product unique identifier */
                        'SKU' => $this->parseProductSku($product['product']->id, FALSE, (isset($product['product']->reference) ? $product['product']->reference : FALSE)),
                        /* (optional) Product name */
                        'NAME' => $product['product']->name,
                        /* (optional) Product category, or array of up to 5 categories */
                        'CATEGORY' => $product['categorys'], //$category->name,
                        /* (optional) Product Price as displayed on the page */
                        'PRICE' => $this->currencyConvertion(
                                array(
                                    'price' => Product::getPriceStatic($product['product']->id, true, false),
                                    'conversion_rate' => $this->context->currency->conversion_rate,
                                )
                        ),
                    );
                }
                $this->context->smarty->assign(array(PKHelper::CPREFIX . 'PRODUCTS' => $smarty_ad));
                unset($smarty_ad);
            }
        }

        /* category tracking */
        if (get_class($this->context->controller) == 'CategoryController') {
            $category = $this->context->controller->getCategory();
            if (Validate::isLoadedObject($category)) {
                $this->context->smarty->assign(array(
                    PKHelper::CPREFIX . 'category' => array('NAME' => $category->name),
                ));
            }
        }
    }

    /* Prestashop 1.4 only HOOKs */

    /**
     * add Prestashop 1.4 specific settings
     * @param mixed $params
     * @since 0.4
     */
    private function _hookFooterPS14($params, $page_name) {
        if (empty($page_name)) {
            /* we can't do any thing use full  */
            return;
        }

        if (strtolower($page_name) == "product" && isset($_GET['id_product']) && Validate::isUnsignedInt($_GET['id_product'])) {
            $product = new Product($_GET['id_product'], false, (isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : NULL));
            if (!Validate::isLoadedObject($product))
                return;
            $product_categorys = $this->get_category_names_by_product($product->id, FALSE);
            $smarty_ad = array(
                array(
                    /* (required) SKU: Product unique identifier */
                    'SKU' => $this->parseProductSku($product->id, FALSE, (isset($product->reference) ? $product->reference : FALSE)),
                    /* (optional) Product name */
                    'NAME' => $product->name,
                    /* (optional) Product category, or array of up to 5 categories */
                    'CATEGORY' => $product_categorys,
                    /* (optional) Product Price as displayed on the page */
                    'PRICE' => $this->currencyConvertion(
                            array(
                                'price' => Product::getPriceStatic($product->id, true, false),
                                'conversion_rate' => false,
                            )
                    ),
                )
            );
            $this->context->smarty->assign(array(PKHelper::CPREFIX . 'PRODUCTS' => $smarty_ad));
            unset($smarty_ad);
        }
        /* category tracking */
        if (strtolower($page_name) == "category" && isset($_GET['id_category']) && Validate::isUnsignedInt($_GET['id_category'])) {
            $category = new Category($_GET['id_category'], (isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : NULL));
            $this->context->smarty->assign(array(
                PKHelper::CPREFIX . 'category' => array('NAME' => $category->name),
            ));
        }
    }

    /**
     * search action
     * @param array $params
     * @since 0.4
     */
    public function hookSearch($params) {
        if ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0)
            return "";
        $this->hookactionSearch($params);
    }
    
    /*
     * 
     * your may add code here if you have some sort af advanched theme that uses iframes for products view
     * if you got iframes for displaying products pages the product will not be tracked by piwik unless you added some code for it.!
     * 
     * hookExtraRight
     * hookProductfooter
     */

    /**
     * Extra Right hook on products page!
     * @param mixed $params
     * @return string
     * @since 0.4
     */
    public function hookExtraRight($params) {
        if ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0)
            return "";
        // $params['cookie'] (OBJECT)
        // $params['cart'] (OBJECT)
        return "";
        // this should be sufficient as long as you add some sort of content only settings
        // return $this->hookFooter($param);
    }

    /**
     * Footer hook on products page!
     * @param mixed $params
     * @return string
     * @since 0.4
     */
    public function hookProductfooter($params) {
        if ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0)
            return "";
        // $params[product] (OBJECT)
        // $params['category'] (OBJECT)
        // $params['cookie'] (OBJECT)
        // $params['cart'] (OBJECT)
        return "";
        // this should be sufficient as long as you add some sort of content only settings
        // return $this->hookFooter($param);
    }

    /* HELPERS */

    private function parseProductSku($id, $attrid = FALSE, $ref = FALSE) {
        if (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== FALSE) && (!empty($ref) && !is_null($ref) && $ref !== FALSE)) {
            $PIWIK_PRODID_V1 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V1');
            return str_replace(array('{ID}', '{ATTRID}', '{REFERENCE}'), array($id, $attrid, $ref), $PIWIK_PRODID_V1);
        } elseif (Validate::isInt($id) && (!empty($ref) && !is_null($ref) && $ref !== FALSE)) {
            $PIWIK_PRODID_V2 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V2');
            return str_replace(array('{ID}', '{REFERENCE}'), array($id, $ref), $PIWIK_PRODID_V2);
        } elseif (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== FALSE)) {
            $PIWIK_PRODID_V3 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V3');
            return str_replace(array('{ID}', '{ATTRID}'), array($id, $attrid), $PIWIK_PRODID_V3);
        } else {
            return $id;
        }
    }

    public function displayErrors($errors) {
        if (!empty($errors)) {
            foreach ($errors as $key => $value) {
                $this->_errors .= $this->displayError($value);
            }
        }
    }

    /**
     * convert into default currentcy used in piwik
     * @param array $params
     * @return float
     * @since 0.4
     */
    private function currencyConvertion($params) {
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

    /**
     * get category names by product id
     * @param integer $id product id
     * @param boolean $array get categories as PHP array (TRUE), or javacript (FAlSE)
     * @return string|array
     */
    private function get_category_names_by_product($id, $array = true) {
        $_categories = Product::getProductCategoriesFull($id, $this->context->cookie->id_lang);
        if (!is_array($_categories)) {
            if ($array)
                return array();
            else
                return "[]";
        }

        if ($array) {
            $categories = array();
            foreach ($_categories as $category) {
                $categories[] = $category['name'];
                if (count($categories) == 5)
                    break;
            }
        } else {
            $categories = '[';
            $c = 0;
            foreach ($_categories as $category) {
                $c++;
                $categories .= '"' . $category['name'] . '",';
                if ($c == 5)
                    break;
            }
            $categories = rtrim($categories, ',');
            $categories .= ']';
        }
        return $categories;
    }

    /**
     * get module link
     * @param string $module
     * @param string $controller
     * @return string
     * @since 0.4
     */
    public static function getModuleLink($module, $controller = 'default') {
        if (_PS_VERSION_ < '1.5')
            return Tools::getShopDomainSsl(true, true) . _MODULE_DIR_ . $module . '/' . $controller . '.php';
        else
            return Context::getContext()->link->getModuleLink($module, $controller);
    }

    private function __setConfigDefault() {

        $this->context->smarty->assign(PKHelper::CPREFIX . 'USE_PROXY', (bool) Configuration::get(PKHelper::CPREFIX . 'USE_PROXY'));

        //* using proxy script?
        if ((bool) Configuration::get(PKHelper::CPREFIX . 'USE_PROXY'))
            $this->context->smarty->assign(PKHelper::CPREFIX . 'HOST', Configuration::get(PKHelper::CPREFIX . 'PROXY_SCRIPT'));
        else
            $this->context->smarty->assign(PKHelper::CPREFIX . 'HOST', Configuration::get(PKHelper::CPREFIX . 'HOST'));

        $this->context->smarty->assign(PKHelper::CPREFIX . 'SITEID', Configuration::get(PKHelper::CPREFIX . 'SITEID'));

        $pkvct = (int) Configuration::get(PKHelper::CPREFIX . 'COOKIE_TIMEOUT'); /* no iset if the same as default */
        if ($pkvct != 0 && $pkvct !== FALSE && ($pkvct != (int) (self::PK_VC_TIMEOUT * 60))) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'COOKIE_TIMEOUT', $pkvct);
        }
        unset($pkvct);

        $pkrct = (int) Configuration::get(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT'); /* no iset if the same as default */
        if ($pkrct != 0 && $pkrct !== FALSE && ($pkrct != (int) (self::PK_RC_TIMEOUT * 60))) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT', $pkrct);
        }
        unset($pkrct);

        $pksct = (int) Configuration::get(PKHelper::CPREFIX . 'SESSION_TIMEOUT'); /* no iset if the same as default */
        if ($pksct != 0 && $pksct !== FALSE && ($pksct != (int) (self::PK_SC_TIMEOUT * 60))) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'SESSION_TIMEOUT', $pksct);
        }
        unset($pksct);

        $this->context->smarty->assign(PKHelper::CPREFIX . 'EXHTML', Configuration::get(PKHelper::CPREFIX . 'EXHTML'));

        $PIWIK_COOKIE_DOMAIN = Configuration::get(PKHelper::CPREFIX . 'COOKIE_DOMAIN');
        $this->context->smarty->assign(PKHelper::CPREFIX . 'COOKIE_DOMAIN', (empty($PIWIK_COOKIE_DOMAIN) ? FALSE : $PIWIK_COOKIE_DOMAIN));

        $PIWIK_SET_DOMAINS = Configuration::get(PKHelper::CPREFIX . 'SET_DOMAINS');
        if (!empty($PIWIK_SET_DOMAINS)) {
            $sdArr = explode(' ', Configuration::get(PKHelper::CPREFIX . 'SET_DOMAINS'));
            if (count($sdArr) > 1)
                $PIWIK_SET_DOMAINS = "['" . trim(implode("','", $sdArr), ",'") . "']";
            else
                $PIWIK_SET_DOMAINS = "'{$sdArr[0]}'";
            $this->context->smarty->assign(PKHelper::CPREFIX . 'SET_DOMAINS', (!empty($PIWIK_SET_DOMAINS) ? $PIWIK_SET_DOMAINS : FALSE));
            unset($sdArr);
        }else {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'SET_DOMAINS', FALSE);
        }
        unset($PIWIK_SET_DOMAINS);

        if ((bool) Configuration::get(PKHelper::CPREFIX . 'DNT')) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'DNT', "_paq.push([\"setDoNotTrack\", true]);");
        }

        if (_PS_VERSION_ < '1.5' && $this->context->cookie->isLogged()) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'UUID', $this->context->cookie->id_customer);
        } else if ($this->context->customer->isLogged()) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'UUID', $this->context->customer->id);
        }
    }

    private function __setCurrencies() {
        $this->default_currency = array('value' => 0, 'label' => $this->l('Choose currency'));
        if (empty($this->currencies)) {
            foreach (Currency::getCurrencies() as $key => $val) {
                $this->currencies[$key] = array(
                    'iso_code' => $val['iso_code'],
                    'name' => "{$val['name']} {$val['iso_code']}",
                );
            }
        }
    }

    private function getConfigFields($form = FALSE) {
        $fields = array(
            PKHelper::CPREFIX . 'USE_PROXY', PKHelper::CPREFIX . 'COOKIE_TIMEOUT',
            PKHelper::CPREFIX . 'SESSION_TIMEOUT', PKHelper::CPREFIX . 'DEFAULT_CURRENCY',
            PKHelper::CPREFIX . 'CRHTTPS', PKHelper::CPREFIX . 'PRODID_V1',
            PKHelper::CPREFIX . 'PRODID_V2', PKHelper::CPREFIX . 'PRODID_V3',
            PKHelper::CPREFIX . 'COOKIE_DOMAIN', PKHelper::CPREFIX . 'SET_DOMAINS',
            PKHelper::CPREFIX . 'DNT', PKHelper::CPREFIX . 'EXHTML',
            PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT', PKHelper::CPREFIX . 'USRNAME',
            PKHelper::CPREFIX . 'USRPASSWD', PKHelper::CPREFIX . 'PAUTHUSR',
            PKHelper::CPREFIX . 'PAUTHPWD', PKHelper::CPREFIX . 'DREPDATE'
        );
        $defaults = array(
            0, self::PK_VC_TIMEOUT, self::PK_SC_TIMEOUT, 'EUR', 0,
            '{ID}-{ATTRID}#{REFERENCE}', '{ID}#{REFERENCE}',
            '{ID}#{ATTRID}', Tools::getShopDomain(), '', 0,
            '', self::PK_RC_TIMEOUT, '', '', '', '', 'day|today'
        );
        $ret = array();
        if ($form)
            foreach ($fields as $key => $value)
                $ret[$value] = Configuration::get($value);
        else
            foreach ($fields as $key => $value)
                $ret[$value] = $defaults[$key];


        return $ret;
    }

    /* INSTALL / UNINSTALL */

    /**
     * Install the module
     * @return boolean false on install error
     */
    public function install() {
        /* default values */
        foreach ($this->getConfigFields(FALSE) as $key => $value) {
            Configuration::updateValue($key, $value);
        }
        if (_PS_VERSION_ < '1.5' && _PS_VERSION_ > '1.3') {
            return (parent::install() && $this->registerHook('header') && $this->registerHook('footer') && $this->registerHook('search') && $this->registerHook('extraRight') && $this->registerHook('productfooter') && $this->registerHook('orderConfirmation'));
        } else if (_PS_VERSION_ >= '1.5') {
            return (parent::install() && $this->registerHook('header') && $this->registerHook('footer') && $this->registerHook('actionSearch') && $this->registerHook('displayRightColumnProduct') && $this->registerHook('orderConfirmation'));
        }
    }

    /**
     * Uninstall the module
     * @return boolean false on uninstall error
     */
    public function uninstall() {
        if (parent::uninstall()) {
            foreach ($this->getConfigFields(FALSE) as $key => $value) {
                Configuration::deleteByName($key);
            }
            try {
                if (_PS_VERSION_ < '1.5') {
                    
                } else if (_PS_VERSION_ >= '1.5') {
                    $tab = Tab::getInstanceFromClassName('PiwikAnalytics');
                    $tab->delete();
                }
            } catch (Exception $ex) {
                
            }
            Configuration::deleteByName(PKHelper::CPREFIX . 'TAPID');
            return true;
        }
        return false;
    }

}
