<?php

if (!defined('_PS_VERSION_'))
    exit;

/**
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
 * @author Christian M. Jensen
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * 
 * @todo config wiz, set currency to use.
 */
class piwikanalyticsjs extends Module {

    private static $_isOrder = FALSE;
    protected $_errors = "";
    protected $piwikSite = FALSE;
    protected $default_currency = array();
    protected $currencies = array();

    /** @var float */
    protected $piwikVersion = 0.0;

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

    public function __construct($name = null,$context = null) {
        $this->name = 'piwikanalyticsjs';
        $this->tab = 'analytics_stats';
        $this->version = '0.8.3';
        $this->author = 'Christian M. Jensen';
        $this->displayName = 'Piwik Analytics';
        $this->author_uri = 'https://cmjscripter.net';
        $this->url = 'http://cmjnisse.github.io/piwikanalyticsjs-prestashop/';
        $this->need_instance = 1;

        /* version 1.5 uses invalid compatibility check */
        if (version_compare(_PS_VERSION_,'1.6.0.0','>='))
            $this->ps_versions_compliancy = array('min' => '1.5','max' => _PS_VERSION_);

        $this->bootstrap = true;

        parent::__construct($name,($context instanceof Context ? $context : NULL));

        require_once dirname(__FILE__).'/PKHelper.php';

        //* warnings on module list page
        if ($this->id && !Configuration::get(PKHelper::CPREFIX.'TOKEN_AUTH'))
            $this->warning = (isset($this->warning) && !empty($this->warning) ? $this->warning.',<br/> ' : '').$this->l('You need to configure the auth token');
        if ($this->id && ((int)Configuration::get(PKHelper::CPREFIX.'SITEID') <= 0))
            $this->warning = (isset($this->warning) && !empty($this->warning) ? $this->warning.',<br/> ' : '').$this->l('You have not yet set Piwik Site ID');
        if ($this->id && !Configuration::get(PKHelper::CPREFIX.'HOST'))
            $this->warning = (isset($this->warning) && !empty($this->warning) ? $this->warning.',<br/> ' : '').$this->l('You need to configure the Piwik server url');

        $this->description = $this->l('Integrates Piwik Analytics into your shop');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this plugin ?');

        self::$_isOrder = FALSE;
        PKHelper::$error = "";
        $this->_errors = PKHelper::$errors = array();

        if ($this->id) {
            if (version_compare(_PS_VERSION_,'1.5.0.13',"<="))
                PKHelper::$_module = & $this;
        }
    }

    /**
     * get content to display in admin area
     * @return string
     */
    public function getContent() {
        $_html = "";
        $this->piwikVersion = PKHelper::getPiwikVersion();
        $this->setMedia();
        $this->processFormsUpdate();
        if (Tools::isSubmit('submitPiwikAnalyticsjsWizard')) {
            $this->processWizardFormUpdate();
        }
        
        $this->piwikSite = false;
        if (Configuration::get(PKHelper::CPREFIX.'TOKEN_AUTH') !== false && !$this->isWizardRequest())
            $this->piwikSite = PKHelper::getPiwikSite();
        
        $currencies = array();
        foreach (Currency::getCurrencies() as $key => $val) {
            $currencies[$key] = array(
                'iso_code' => $val['iso_code'],
                'name' => "{$val['name']} {$val['iso_code']}",
            );
        }

        // warnings on module configure page
        if (!$this->isWizardRequest()) {
            if ($this->id && !Configuration::get(PKHelper::CPREFIX.'TOKEN_AUTH') && !Tools::getIsset(PKHelper::CPREFIX.'TOKEN_AUTH'))
                $this->_errors[] = $this->displayError($this->l('Piwik auth token is empty'));
            if ($this->id && ((int)Configuration::get(PKHelper::CPREFIX.'SITEID') <= 0) && !Tools::getIsset(PKHelper::CPREFIX.'SITEID'))
                $this->_errors[] = $this->displayError($this->l('Piwik site id is lower or equal to "0"'));
            if ($this->id && !Configuration::get(PKHelper::CPREFIX.'HOST'))
                $this->_errors[] = $this->displayError($this->l('Piwik host cannot be empty'));
        }
        
        $_currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules');

        // defaults
        $this->context->smarty->assign(array(
            'psversion' => _PS_VERSION_,
            'pscurrentIndex' => $_currentIndex,
            'pstoken' => Tools::getAdminTokenLite('AdminModules'),
            'piwik_module_dir' => __PS_BASE_URI__.'modules/'.$this->name,
            'pkCPREFIX' => PKHelper::CPREFIX,
        ));
        if (version_compare(_PS_VERSION_,'1.5.0.5',">=") && version_compare(_PS_VERSION_,'1.5.3.999',"<=")) {
            $this->context->smarty->assign(array('piwikAnalyticsControllerLink' => $this->context->link->getAdminLink('PiwikAnalytics15')));
        } else if (version_compare(_PS_VERSION_,'1.5.0.13',"<=")) {
            $this->context->smarty->assign(array('piwikAnalyticsControllerLink' => $this->context->link->getAdminLink('AdminPiwikAnalytics')));
        } else {
            $this->context->smarty->assign(array('piwikAnalyticsControllerLink' => $this->context->link->getAdminLink('PiwikAnalytics')));
        }
        
        $this->runWizard($_currentIndex, $_html, $currencies);
        
        $config_wizard_link = $this->context->link->getAdminLink('AdminModules').
                "&configure={$this->name}&tab_module=analytics_stats&module_name={$this->name}&pkwizard";

        // Piwik image tracking
        $image_tracking = array(
            'default' => false,
            'proxy' => false
        );
        if (version_compare($this->piwikVersion,'2.0','>')) {
            if (Configuration::get(PKHelper::CPREFIX.'TOKEN_AUTH') !== false)
                $image_tracking = PKHelper::getPiwikImageTrackingCode();
            else
                $image_tracking = array(
                    'default' => $this->l('I need Site ID and Auth Token before i can get your image tracking code'),
                    'proxy' => $this->l('I need Site ID and Auth Token before i can get your image tracking code')
                );
        }
        $this->displayErrorsPiwik();

        // get cookie settings
        $PIWIK_RCOOKIE_TIMEOUT = (int)Configuration::get(PKHelper::CPREFIX.'RCOOKIE_TIMEOUT');
        $PIWIK_COOKIE_TIMEOUT = (int)Configuration::get(PKHelper::CPREFIX.'COOKIE_TIMEOUT');
        $PIWIK_SESSION_TIMEOUT = (int)Configuration::get(PKHelper::CPREFIX.'SESSION_TIMEOUT');

        $pktimezones = $this->getTimezonesList();
        

        $this->context->smarty->assign(array(
            'piwikVersion' => $this->piwikVersion,
            'piwikSite' => $this->piwikSite !== FALSE && isset($this->piwikSite[0]),
            'piwikSiteId' => (int)Configuration::get(PKHelper::CPREFIX.'SITEID'),
            'piwikSiteName' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->name : $this->l('unknown')),
            'piwikExcludedIps' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_ips : ''),
            'piwikMainUrl' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->main_url : $this->l('unknown')),
            'config_wizard_link' => $config_wizard_link,
            'tab_defaults_file' => $this->_get_theme_file("tab_configure_defaults.tpl","views/templates/admin"),
            'tab_proxyscript_file' => $this->_get_theme_file("tab_configure_proxy_script.tpl","views/templates/admin"),
            'tab_extra_file' => $this->_get_theme_file("tab_configure_extra.tpl","views/templates/admin"),
            'tab_html_file' => $this->_get_theme_file("tab_configure_html.tpl","views/templates/admin"),
            'tab_cookies_file' => $this->_get_theme_file("tab_configure_cookies.tpl","views/templates/admin"),
            'tab_site_manager_file' => $this->_get_theme_file("tab_site_manager.tpl","views/templates/admin"),
            /* Form values */
            /** tab|Site Manager * */
            'pktimezones' => $pktimezones,
            'PKAdminSiteName' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->name : ''),
            'PKAdminEcommerce' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->ecommerce : ''),
            'PKAdminSiteSearch' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch : ''),
            'PKAdminSearchKeywordParameters' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch_keyword_parameters : ''),
            'PKAdminSearchCategoryParameters' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch_category_parameters : ''),
            'SPKSID' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->idsite : Configuration::get(PKHelper::CPREFIX.'SITEID')),
            'PKAdminExcludedIps' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_ips : ''),
            'PKAdminExcludedQueryParameters' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_parameters : ''),
            'PKAdminTimezone' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->timezone : ''),
            'PKAdminCurrency' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->currency : ''),
            'PKAdminGroup' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->group : ''),
            'PKAdminStartDate' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->ts_created : ''),
            'PKAdminSiteUrls' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->main_url : ''),
            'PKAdminExcludedUserAgents' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_user_agents : ''),
            'PKAdminKeepURLFragments' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->keep_url_fragment : 0),
            'PKAdminSiteType' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->type : 'website'),
            /** tab|Defaults * */
            'pkfvHOST' => Configuration::get(PKHelper::CPREFIX.'HOST'),
            'pkfvSITEID' => Configuration::get(PKHelper::CPREFIX.'SITEID'),
            'pkfvTOKEN_AUTH' => Configuration::get(PKHelper::CPREFIX.'TOKEN_AUTH'),
            'pkfvUSE_CURL' => Configuration::get(PKHelper::CPREFIX.'USE_CURL'),
            'pkfvUSRNAME' => Configuration::get(PKHelper::CPREFIX.'USRNAME'),
            'pkfvUSRPASSWD' => Configuration::get(PKHelper::CPREFIX.'USRPASSWD'),
            'pkfvDNT' => Configuration::get(PKHelper::CPREFIX.'DNT'),
            'pkfvDEFAULT_CURRENCY' => Configuration::get(PKHelper::CPREFIX.'DEFAULT_CURRENCY'),
            'pkfvCurrencies' => $currencies,
            'pkfvCURRENCY_DEFAULT' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->currency : $this->l('unknown')),
            'pkfvDREPDATE' => Configuration::get(PKHelper::CPREFIX.'DREPDATE'),
            /** tab|Proxy Script * */
            'pkfvPROXY_TIMEOUT' => Configuration::get(PKHelper::CPREFIX.'PROXY_TIMEOUT'),
            'pkfvUSE_PROXY' => Configuration::get(PKHelper::CPREFIX.'USE_PROXY'),
            'pkfvPROXY_SCRIPTPlaceholder' => str_replace(array("http://","https://"),'',self::getModuleLink($this->name,'piwik')),
            'pkfvPROXY_SCRIPTBuildIn' => str_replace(array("http://","https://"),'',self::getModuleLink($this->name,'piwik')),
            'pkfvPROXY_SCRIPT' => Configuration::get(PKHelper::CPREFIX.'PROXY_SCRIPT'),
            'has_cURL' => (!function_exists('curl_version') /* FIX:4 my Nginx (php-fpm) */ && !function_exists('curl_init')/* //FIX */) ? false : true,
            'pkfvPAUTHUSR' => Configuration::get(PKHelper::CPREFIX.'PAUTHUSR'),
            'pkfvPAUTHPWD' => Configuration::get(PKHelper::CPREFIX.'PAUTHPWD'),
            'pkfvCRHTTPS' => Configuration::get(PKHelper::CPREFIX.'CRHTTPS'),
            /** tab|Extra * */
            'pkfvPRODID_V1' => $this->getProductIdTemplate(1),
            'pkfvPRODID_V2' => $this->getProductIdTemplate(2),
            'pkfvPRODID_V3' => $this->getProductIdTemplate(3),
            'pkfvSEARCH_QUERY' => Configuration::get(PKHelper::CPREFIX."SEARCH_QUERY"),
            'pkfvSET_DOMAINS' => Configuration::get(PKHelper::CPREFIX.'SET_DOMAINS'),
            'pkfvDHashTag' => Configuration::get(PKHelper::CPREFIX.'DHashTag'),
            'pkfvAPTURL' => Configuration::get(PKHelper::CPREFIX.'APTURL'),
            /** tab|HTML * */
            'pkfvEXHTML' => Configuration::get(PKHelper::CPREFIX."EXHTML"),
            'pkfvEXHTML_ImageTracker' => $image_tracking['default'],
            'pkfvEXHTML_ImageTrackerProxy' => $image_tracking['proxy'],
            'pkfvEXHTML_Warning' => (version_compare(_PS_VERSION_,'1.6.0.7','>=') ? "<br><strong>{$this->l("Before you edit/add html code to this field make sure the HTMLPurifier library isn't in use if HTMLPurifier is enabled, all html code will be stripd from the field when saving, check the settings in 'Preferences=>General', you can enable HTMLPurifier again after you made your changes")}</strong>" : ""),
            'pkfvLINKTRACK' => Configuration::get(PKHelper::CPREFIX.'LINKTRACK'),
            'pkfvLINKClS' => Configuration::get(PKHelper::CPREFIX.'LINKClS'),
            'LINKClSIGNORE' => Configuration::get(PKHelper::CPREFIX.'LINKClSIGNORE'),
            'pkfvLINKTTIME' => (int)Configuration::get(PKHelper::CPREFIX.'LINKTTIME'),
            /** tab|Cookies * */
            'pkfvSESSION_TIMEOUT' => ($PIWIK_SESSION_TIMEOUT != 0 ? (int)($PIWIK_SESSION_TIMEOUT / 60) : (int)(self::PK_SC_TIMEOUT )),
            'pkfvCOOKIE_TIMEOUT' => ($PIWIK_COOKIE_TIMEOUT != 0 ? (int)($PIWIK_COOKIE_TIMEOUT / 60) : (int)(self::PK_VC_TIMEOUT)),
            'pkfvRCOOKIE_TIMEOUT' => ($PIWIK_RCOOKIE_TIMEOUT != 0 ? (int)($PIWIK_RCOOKIE_TIMEOUT / 60) : (int)(self::PK_RC_TIMEOUT)),
            'pkfvCOOKIE_DOMAIN' => Configuration::get(PKHelper::CPREFIX.'COOKIE_DOMAIN'),
            'pkfvCOOKIEPREFIX' => Configuration::get(PKHelper::CPREFIX.'COOKIEPREFIX'),
            'pkfvCOOKIEPATH' => Configuration::get(PKHelper::CPREFIX.'COOKIEPATH'),
        ));
        $_html .= $this->display(__FILE__,'views/templates/admin/configure_tabs.tpl');

        if (is_array($this->_errors))
            $_html = implode('',$this->_errors).$_html;
        else
            $_html = $this->_errors.$_html;

        return $_html.$this->display(__FILE__,'views/templates/admin/jsfunctions.tpl');
    }
    
    private function runWizard($_currentIndex, $_html, $currencies) {
        if ($this->isWizardRequest()) {
            require_once dirname(__FILE__).'/PiwikWizard.php';
            PiwikWizardHelper::setUsePiwikSite($_currentIndex);
            PiwikWizardHelper::createNewSite();
            $step = 1;
            if (empty($this->_errors) && empty($_html)) {
                if (Tools::getIsset(PKHelper::CPREFIX.'STEP_WIZARD')) {
                    $step = (int)Tools::getValue(PKHelper::CPREFIX.'STEP_WIZARD',0);
                    if (!Tools::isSubmit('createnewsite'))
                        $step++;
                    else
                        $step = 2;
                }
            }

            $pkToken = null;
            if ($step == 2) {
                PiwikWizardHelper::getFormValuesInternal(true);
                $pkToken = PKHelper::getTokenAuth(PiwikWizardHelper::$username,PiwikWizardHelper::$password);
                if (!empty(PKHelper::$error)) {
                    $step = 1;
                } else {
                    if ($pkToken !== false) {
                        if (Tools::isSubmit('createnewsite')) {
                            $step = 99;
                        } else {
                            if ($_pkSites = PKHelper::getSitesWithAdminAccess(false,array(
                                        'idSite' => 0,'pkHost' => PKHelper::$piwikHost,
                                        'https' => (strpos(PiwikWizardHelper::$piwikhost,'https://') !== false),
                                        'pkModule' => 'API','isoCode' => NULL,'tokenAuth' => $pkToken))) {
                                if (!empty($_pkSites)) {
                                    $pkSites = array();
                                    foreach ($_pkSites as $value) {
                                        $pkSites[] = array(
                                            'idsite' => $value->idsite,
                                            'name' => $value->name,
                                            'main_url' => $value->main_url,
                                        );
                                    }
                                    $this->context->smarty->assign(array('pkSites' => $pkSites));
                                }
                                unset($_pkSites);
                            }
                            if (!empty(PKHelper::$error)) {
                                $step = 1;
                            }
                        }
                    } else {
                        $step = 1;
                        $this->_errors[] = $this->displayError($this->l("Unknown error, Piwik auth token returned NULL, check your username and password"));
                    }
                }
            }
            $saved_piwik_host = Configuration::get(PKHelper::CPREFIX.'HOST');
            if (!empty($saved_piwik_host)) {
                $saved_piwik_host = "http://".$saved_piwik_host;
            }
            $this->context->smarty->assign(array(
                'pscurrentIndex' => $_currentIndex.'&pkwizard',
                'pscurrentIndexLnk' => $_currentIndex,
                'wizardStep' => $step,
                'pkfvHOST' => $saved_piwik_host,
            ));
            if ($step >= 2) {
                $this->context->smarty->assign(array(
                    'pkfvHOST_WIZARD' => PiwikWizardHelper::$piwikhost,
                    'pkfvUSRNAME_WIZARD' => PiwikWizardHelper::$username,
                    'pkfvUSRPASSWD_WIZARD' => PiwikWizardHelper::$password,
                    'pkfvPAUTHUSR_WIZARD' => PiwikWizardHelper::$usernamehttp,
                    'pkfvPAUTHPWD_WIZARD' => PiwikWizardHelper::$passwordhttp,
                ));
                if ($step == 99)
                    $Currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                if (!is_object($Currency)) {
                    $Currency = new stdClass();
                    $Currency->iso_code = 'EUR';
                }
                $this->context->smarty->assign(array(
                    'PKNewSiteName' => Configuration::get('PS_SHOP_NAME'),'PKNewAddtionalUrls' => '','PKNewEcommerce' => 1,
                    'PKNewSiteSearch' => 1,'PKNewKeepURLFragments' => 0,'PKNewTimezone' => 'UTC',
                    'PKNewSearchKeywordParameters' => '','PKNewExcludedUserAgents' => '','PKNewSearchCategoryParameters' => '',
                    'PKNewCurrency' => Context::getContext()->currency->iso_code,'PKNewExcludedQueryParameters' => '',
                    'PKNewMainUrl' => Tools::getShopDomainSsl(true,true).Context::getContext()->shop->getBaseURI(),
                    'PKNewExcludedIps' => Configuration::get('PS_MAINTENANCE_IP'),
                    'pkfvMyIPis' => $_SERVER['REMOTE_ADDR'],
                    'pkfvTimezoneList' => $this->getTimezonesList($pkToken, PiwikWizardHelper::$piwikhost),
                    'pkfvCurrencies' => $currencies,
                    'PKNewCurrency' => $Currency->iso_code,
                ));
                unset($Currency);
            }
            $this->displayErrorsPiwik();
            $this->displayErrorsPiwik2();
            if (is_array($this->_errors))
                $_html = implode('',$this->_errors).$_html;
            else
                $_html = $this->_errors.$_html;
            return $_html
                    .$this->display(__FILE__,'views/templates/admin/piwik_wizard.tpl')
                    .$this->display(__FILE__,'views/templates/admin/jsfunctions.tpl')
                    .$this->display(__FILE__,'views/templates/admin/piwik_site_lookup.tpl');
        }
    }
    
    private function processWizardFormUpdate() {
        $KEY_PREFIX = PKHelper::CPREFIX;
        $username = $password = $piwikhost = $saveusrpwd = $usernamehttp = $passwordhttp = false;
        if (Tools::getIsset($KEY_PREFIX.'USRNAME_WIZARD'))
            $username = Tools::getValue($KEY_PREFIX.'USRNAME_WIZARD');
        if (Tools::getIsset($KEY_PREFIX.'USRPASSWD_WIZARD'))
            $password = Tools::getValue($KEY_PREFIX.'USRPASSWD_WIZARD');
        if (Tools::getIsset($KEY_PREFIX.'HOST_WIZARD'))
            $piwikhost = Tools::getValue($KEY_PREFIX.'HOST_WIZARD');
        if (Tools::getIsset($KEY_PREFIX.'SAVE_USRPWD_WIZARD'))
            $saveusrpwd = (bool)Tools::getValue($KEY_PREFIX.'SAVE_USRPWD_WIZARD',false);
        if (Tools::getIsset($KEY_PREFIX.'PAUTHUSR_WIZARD'))
            $usernamehttp = Tools::getValue($KEY_PREFIX.'PAUTHUSR_WIZARD',"");
        if (Tools::getIsset($KEY_PREFIX.'PAUTHPWD_WIZARD'))
            $passwordhttp = Tools::getValue($KEY_PREFIX.'PAUTHPWD_WIZARD',"");
        if ($piwikhost !== false && !empty($piwikhost)) {
            $tmp = $piwikhost;
            if (Validate::isUrl($tmp) || Validate::isUrl('http://'.$tmp)) {
                if (preg_match("/https:/i",$tmp))
                    Configuration::updateValue($KEY_PREFIX.'CRHTTPS',1);
                $tmp = str_ireplace(array('http://','https://','//'),"",$tmp);
                if (substr($tmp,-1) != "/") {
                    $tmp .= "/";
                }
                Configuration::updateValue($KEY_PREFIX.'HOST',$tmp);
            } else {
                $this->_errors[] = $this->displayError($this->l('Piwik host url is not valid'));
            }
        } else {
            $this->_errors[] = $this->displayError($this->l('Piwik host cannot be empty'));
        }
        if (($username !== false && strlen($username) > 2) && ($password !== false && strlen($password) > 2)) {
            if ($saveusrpwd == 1 || $saveusrpwd !== false) {
                Configuration::updateValue($KEY_PREFIX."USRPASSWD",$password);
                Configuration::updateValue($KEY_PREFIX."USRNAME",$username);
            }
        } else {
            $this->_errors[] = $this->displayError($this->l('Username and/or password is missing/to short'));
        }
        if (($usernamehttp !== false && strlen($usernamehttp) > 0) && ($passwordhttp !== false && strlen($passwordhttp) > 0)) {
            Configuration::updateValue($KEY_PREFIX."PAUTHUSR",$usernamehttp);
            Configuration::updateValue($KEY_PREFIX."PAUTHPWD",$passwordhttp);
        }
    }

    /**
     * handles the configuration form update
     * @return void
     */
    private function processFormsUpdate() {
        $isPost = false;
        $KEY_PREFIX = PKHelper::CPREFIX;
        // handle submission from defaults tab
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsjsDefaults')) {
            $isPost = true;
            // [PIWIK_HOST] Piwik host URL
            if (Tools::getIsset($KEY_PREFIX.'HOST')) {
                $tmp = Tools::getValue($KEY_PREFIX.'HOST','');
                if (!empty($tmp)) {
                    if (Validate::isUrl($tmp) || Validate::isUrl('http://'.$tmp)) {
                        $tmp = str_replace(array('http://','https://','//'),"",$tmp);
                        if (substr($tmp,-1) != "/") {
                            $tmp .= "/";
                        }
                        Configuration::updateValue($KEY_PREFIX.'HOST',$tmp);
                    } else {
                        $this->_errors[] = $this->displayError($this->l('Piwik host url is not valid'));
                    }
                } else {
                    $this->_errors[] = $this->displayError($this->l('Piwik host cannot be empty'));
                }
            }
            // [PIWIK_SITEID] Piwik site id
            if (Tools::getIsset($KEY_PREFIX.'SITEID')) {
                $tmp = (int)Tools::getValue($KEY_PREFIX.'SITEID',0);
                Configuration::updateValue($KEY_PREFIX.'SITEID',$tmp);
                if ($tmp <= 0) {
                    $this->_errors[] = $this->displayError($this->l('Piwik site id is lower or equal to "0"'));
                }
            }
            // [PIWIK_TOKEN_AUTH] Piwik authentication token
            if (Tools::getIsset($KEY_PREFIX.'TOKEN_AUTH')) {
                $tmp = Tools::getValue($KEY_PREFIX.'TOKEN_AUTH','');
                Configuration::updateValue($KEY_PREFIX.'TOKEN_AUTH',$tmp);
                if (empty($tmp)) {
                    $this->_errors[] = $this->displayError($this->l('Piwik auth token is empty'));
                }
            }
            // [PIWIK_DNT] 
            if (Tools::getIsset($KEY_PREFIX.'DNT')) {
                Configuration::updateValue($KEY_PREFIX.'DNT',1);
            } else {
                Configuration::updateValue($KEY_PREFIX.'DNT',0);
            }
            // [PIWIK_DEFAULT_CURRENCY]
            if (Tools::getIsset($KEY_PREFIX.'DEFAULT_CURRENCY'))
                Configuration::updateValue($KEY_PREFIX."DEFAULT_CURRENCY",Tools::getValue($KEY_PREFIX.'DEFAULT_CURRENCY','EUR'));
            // [PIWIK_DREPDATE] (default report date)
            if (Tools::getIsset($KEY_PREFIX.'DREPDATE'))
                Configuration::updateValue($KEY_PREFIX."DREPDATE",Tools::getValue($KEY_PREFIX.'DREPDATE','day|today'));
            // [PIWIK_USRNAME] 
            if (Tools::getIsset('username_changed') && (((int)Tools::getValue('username_changed')) == 1) && Tools::getIsset($KEY_PREFIX.'USRNAME')) {
                Configuration::updateValue($KEY_PREFIX."USRNAME",Tools::getValue($KEY_PREFIX.'USRNAME',''));
            }
            // [PIWIK_USRPASSWD] 
            if (Tools::getIsset('password_changed') && (((int)Tools::getValue('password_changed')) == 1) && Tools::getIsset($KEY_PREFIX.'USRPASSWD')) {
                Configuration::updateValue($KEY_PREFIX."USRPASSWD",Tools::getValue($KEY_PREFIX.'USRPASSWD',''));
            }
        }
        // handle submission from proxy tab
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsjsProxyScript')) {
            $isPost = true;
            // [PIWIK_USE_PROXY] 
            if (Tools::getIsset($KEY_PREFIX.'USE_PROXY')) {
                Configuration::updateValue($KEY_PREFIX.'USE_PROXY',1);
            } else {
                Configuration::updateValue($KEY_PREFIX.'USE_PROXY',0);
            }
            // [PIWIK_USE_CURL] 
            if (Tools::getIsset($KEY_PREFIX.'USE_CURL')) {
                Configuration::updateValue($KEY_PREFIX.'USE_CURL',1);
            } else {
                Configuration::updateValue($KEY_PREFIX.'USE_CURL',0);
            }
            // [PIWIK_CRHTTPS] 
            if (Tools::getIsset($KEY_PREFIX.'CRHTTPS')) {
                Configuration::updateValue($KEY_PREFIX.'CRHTTPS',1);
            } else {
                Configuration::updateValue($KEY_PREFIX.'CRHTTPS',0);
            }
            // [PIWIK_PROXY_TIMEOUT] 
            if (Tools::getIsset($KEY_PREFIX.'PROXY_TIMEOUT')) {
                $tmp = (int)Tools::getValue($KEY_PREFIX.'PROXY_TIMEOUT',5);
                if ($tmp <= 0) {
                    $this->_errors[] = $this->displayError($this->l('Piwik proxy timeout must be an integer and larger than 0 (zero)'));
                    $tmp = 5;
                }
                Configuration::updateValue($KEY_PREFIX.'PROXY_TIMEOUT',$tmp);
            }
            // [PIWIK_PROXY_TIMEOUT] 
            if (Tools::getIsset($KEY_PREFIX.'PROXY_SCRIPT'))
                Configuration::updateValue($KEY_PREFIX.'PROXY_SCRIPT',str_replace(array("http://","https://",'//'),'',Tools::getValue($KEY_PREFIX.'PROXY_SCRIPT')));
            // [PIWIK_PAUTHUSR] 
            if (Tools::getIsset('pusername_changed') && (((int)Tools::getValue('pusername_changed')) == 1) && Tools::getIsset($KEY_PREFIX.'PAUTHUSR')) {
                Configuration::updateValue($KEY_PREFIX."PAUTHUSR",Tools::getValue($KEY_PREFIX.'PAUTHUSR',''));
            }
            // [PIWIK_PAUTHPWD] 
            if (Tools::getIsset('ppassword_changed') && (((int)Tools::getValue('ppassword_changed')) == 1) && Tools::getIsset($KEY_PREFIX.'PAUTHPWD')) {
                Configuration::updateValue($KEY_PREFIX."PAUTHPWD",Tools::getValue($KEY_PREFIX.'PAUTHPWD',''));
            }
        }
        // handle submission from extra tab
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsjsExtra')) {
            $isPost = true;
            // [PIWIK_PRODID_V1] 
            if (Tools::getIsset($KEY_PREFIX.'PRODID_V1')) {
                $tmp = Tools::getValue($KEY_PREFIX.'PRODID_V1','{ID}-{ATTRID}#{REFERENCE}');
                if (!preg_match("/{ID}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Product id V1: missing variable {ID}'));
                if (!preg_match("/{ATTRID}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Product id V1: missing variable {ATTRID}'));
                if (!preg_match("/{REFERENCE}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Product id V1: missing variable {REFERENCE}'));
                Configuration::updateValue($KEY_PREFIX.'PRODID_V1',$tmp);
            }
            // [PIWIK_PRODID_V2] 
            if (Tools::getIsset($KEY_PREFIX.'PRODID_V2')) {
                $tmp = Tools::getValue($KEY_PREFIX.'PRODID_V2','{ID}#{REFERENCE}');
                if (!preg_match("/{ID}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Product id V2: missing variable {ID}'));
                if (!preg_match("/{REFERENCE}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Product id V2: missing variable {REFERENCE}'));
                Configuration::updateValue($KEY_PREFIX.'PRODID_V2',Tools::getValue($KEY_PREFIX.'PRODID_V2','{ID}#{REFERENCE}'));
            }
            // [PIWIK_PRODID_V3] 
            if (Tools::getIsset($KEY_PREFIX.'PRODID_V3')) {
                $tmp = Tools::getValue($KEY_PREFIX.'PRODID_V3','{ID}-{ATTRID}');
                if (!preg_match("/{ID}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Product id V3: missing variable {ID}'));
                if (!preg_match("/{ATTRID}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Product id V3: missing variable {ATTRID}'));
                Configuration::updateValue($KEY_PREFIX.'PRODID_V3',Tools::getValue($KEY_PREFIX.'PRODID_V3','{ID}-{ATTRID}'));
            }
            // [PIWIK_SEARCH_QUERY]
            if (Tools::getIsset($KEY_PREFIX.'SEARCH_QUERY')) {
                $tmp = Tools::getValue($KEY_PREFIX.'SEARCH_QUERY','{QUERY} ({PAGE})');
                if (!preg_match("/{QUERY}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Searche template: missing variable {QUERY}'));
                if (!preg_match("/{PAGE}/",$tmp))
                    $this->_errors[] = $this->displayError($this->l('Searche template: missing variable {PAGE}'));
                Configuration::updateValue($KEY_PREFIX."SEARCH_QUERY",$tmp);
            }
            // [PIWIK_SET_DOMAINS]
            if (Tools::getIsset($KEY_PREFIX.'SET_DOMAINS')) {
                $tmp = Tools::getValue($KEY_PREFIX.'SET_DOMAINS');
//                //this may result in unwanted removal of correct domains like "*.domain.tld" so commented for now
//                foreach (explode(',', $tmp) as $sUrl) {
//                    if (!Validate::isUrl($sUrl) && !Validate::isUrl('http://'.$sUrl)){
//                        $tmp = str_ireplace($sUrl, '', $tmp);
//                        $this->_errors[] = $this->displayError(sprintf($this->l('known alias URL \'%s\' is not valid an has been removed'), $sUrl));
//                    }
//                }
                Configuration::updateValue($KEY_PREFIX.'SET_DOMAINS',trim(str_replace(',,',',',$tmp),','));
            }
            // [PIWIK_DHashTag] 
            if (Tools::getIsset($KEY_PREFIX.'DHashTag')) {
                Configuration::updateValue($KEY_PREFIX.'DHashTag',1);
            } else {
                Configuration::updateValue($KEY_PREFIX.'DHashTag',0);
            }
            // [PIWIK_APTURL] 
            if (Tools::getIsset($KEY_PREFIX.'APTURL')) {
                Configuration::updateValue($KEY_PREFIX.'APTURL',1);
            } else {
                Configuration::updateValue($KEY_PREFIX.'APTURL',0);
            }
        }
        // handle submission from html tab
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsjsHTML')) {
            $isPost = true;
            // [PIWIK_EXHTML] 
            if (Tools::getIsset($KEY_PREFIX.'EXHTML'))
                Configuration::updateValue($KEY_PREFIX.'EXHTML',Tools::getValue($KEY_PREFIX.'EXHTML'),TRUE);
            // [PIWIK_LINKTRACK] 
            if (Tools::getIsset($KEY_PREFIX.'LINKTRACK')) {
                Configuration::updateValue($KEY_PREFIX.'LINKTRACK',1);
            } else {
                Configuration::updateValue($KEY_PREFIX.'LINKTRACK',0);
            }
            // [PIWIK_LINKClS] 
            if (Tools::getIsset($KEY_PREFIX.'LINKClS'))
                Configuration::updateValue($KEY_PREFIX.'LINKClS',Tools::getValue($KEY_PREFIX.'LINKClS', ''));
            // [PIWIK_LINKClSIGNORE] 
            if (Tools::getIsset($KEY_PREFIX.'LINKClSIGNORE'))
                Configuration::updateValue($KEY_PREFIX.'LINKClSIGNORE',Tools::getValue($KEY_PREFIX.'LINKClSIGNORE', ''));
            // [PIWIK_LINKTTIME] 
            if (Tools::getIsset($KEY_PREFIX.'LINKTTIME'))
                Configuration::updateValue($KEY_PREFIX.'LINKTTIME',Tools::getValue($KEY_PREFIX.'LINKTTIME', ''));
        }
        // handle submission from cookies tab
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsjsCookies')) {
            $isPost = true;

            // [PIWIK_COOKIE_DOMAIN]
            if (Tools::getIsset($KEY_PREFIX.'COOKIE_DOMAIN'))
                Configuration::updateValue($KEY_PREFIX.'COOKIE_DOMAIN',Tools::getValue($KEY_PREFIX.'COOKIE_DOMAIN'));
            // [PIWIK_COOKIEPREFIX]
            if (Tools::getIsset($KEY_PREFIX.'COOKIEPREFIX'))
                Configuration::updateValue($KEY_PREFIX.'COOKIEPREFIX',Tools::getValue($KEY_PREFIX.'COOKIEPREFIX'));
            // [PIWIK_COOKIEPATH]
            if (Tools::getIsset($KEY_PREFIX.'COOKIEPATH'))
                Configuration::updateValue($KEY_PREFIX.'COOKIEPATH',Tools::getValue($KEY_PREFIX.'COOKIEPATH'));
            // [PIWIK_RCOOKIE_TIMEOUT]
            if (Tools::getIsset($KEY_PREFIX.'RCOOKIE_TIMEOUT')) {
                $tmp = (int)Tools::getValue($KEY_PREFIX.'RCOOKIE_TIMEOUT',self::PK_RC_TIMEOUT);
                $tmp = ($tmp * 60); //* convert to seconds
                Configuration::updateValue($KEY_PREFIX.'RCOOKIE_TIMEOUT',(int)$tmp);
            }
            // [PIWIK_COOKIE_TIMEOUT]
            if (Tools::getIsset($KEY_PREFIX.'COOKIE_TIMEOUT')) {
                $tmp = (int)Tools::getValue($KEY_PREFIX.'COOKIE_TIMEOUT',self::PK_VC_TIMEOUT);
                $tmp = ($tmp * 60); //* convert to seconds
                Configuration::updateValue($KEY_PREFIX.'COOKIE_TIMEOUT',(int)$tmp);
            }
            // [PIWIK_SESSION_TIMEOUT]
            if (Tools::getIsset($KEY_PREFIX.'SESSION_TIMEOUT')) {
                $tmp = (int)Tools::getValue($KEY_PREFIX.'SESSION_TIMEOUT',self::PK_SC_TIMEOUT);
                $tmp = ($tmp * 60); //* convert to seconds
                Configuration::updateValue($KEY_PREFIX.'SESSION_TIMEOUT',(int)$tmp);
            }
        }
        // handle submission from site manager tab
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsjsSiteManager')) {
            $isPost = true;
            $PKAdminIdSite = (int)Configuration::get(PKHelper::CPREFIX.'SITEID');
            $PKAdminGroup = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->group : '');
            //$PKAdminStartDate = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->ts_created : '');
            $PKAdminStartDate = NULL;
            //$PKAdminSiteUrls = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->main_url : '');
            $PKAdminSiteUrls = PKHelper::getSiteUrlsFromId($PKAdminIdSite);
            $PKAdminSiteName = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->name : $this->l('unknown'));
            $PKAdminEcommerce = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->ecommerce : '');
            $PKAdminSiteSearch = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch : '');
            $PKAdminSearchKeywordParameters = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch_keyword_parameters : '');
            $PKAdminSearchCategoryParameters = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch_category_parameters : '');
            $PKAdminExcludedIps = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_ips : '');
            $PKAdminExcludedQueryParameters = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_parameters : '');
            $PKAdminTimezone = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->timezone : '');
            $PKAdminCurrency = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->currency : '');
            $PKAdminExcludedUserAgents = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_user_agents : '');
            $PKAdminKeepURLFragments = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->keep_url_fragment : 0);
            $PKAdminSiteType = ($this->piwikSite !== FALSE ? $this->piwikSite[0]->type : 'website');
            // [PKAdminGroup]
//            if (Tools::getIsset('PKAdminGroup')) {
//                $PKAdminGroup = Tools::getValue('PKAdminGroup',$PKAdminGroup);
//            }
            // [PKAdminStartDate]
//            if (Tools::getIsset('PKAdminStartDate')) {
//                $PKAdminStartDate = Tools::getValue('PKAdminStartDate',$PKAdminStartDate);
//            }
            // [PKAdminSiteUrls]
//            if (Tools::getIsset('PKAdminSiteUrls')) {
//                $PKAdminSiteUrls = Tools::getValue('PKAdminSiteUrls',$PKAdminSiteUrls);
//            }
            // [PKAdminSiteType]
//            if (Tools::getIsset('PKAdminSiteType')) {
//                $PKAdminSiteType = Tools::getValue('PKAdminSiteType',$PKAdminSiteType);
//            }
            // [PKAdminSiteName]
            if (Tools::getIsset('PKAdminSiteName')) {
                $PKAdminSiteName = Tools::getValue('PKAdminSiteName',$PKAdminSiteName);
                if (!Validate::isString($PKAdminSiteName) || empty($PKAdminSiteName))
                    $this->_errors[] = $this->displayError($this->l('SiteName is not valid'));
            }
            // [PKAdminEcommerce]
            if (Tools::getIsset('PKAdminEcommerce')) {
                $PKAdminEcommerce = true;
            } else {
                $PKAdminEcommerce = false;
            }
            // [PKAdminSiteSearch]
            if (Tools::getIsset('PKAdminSiteSearch')) {
                $PKAdminSiteSearch = true;
            } else {
                $PKAdminSiteSearch = false;
            }
            // [$PKAdminSearchKeywordParameters]
            if (Tools::getIsset('PKAdminSearchKeywordParameters')) {
                $PKAdminSearchKeywordParameters = Tools::getValue('PKAdminSearchKeywordParameters',$PKAdminSearchKeywordParameters);
            }
            // [PKAdminSearchCategoryParameters]
            if (Tools::getIsset('PKAdminSearchCategoryParameters')) {
                $PKAdminSearchCategoryParameters = Tools::getValue('PKAdminSearchCategoryParameters',$PKAdminSearchCategoryParameters);
            }
            // [PKAdminExcludedIps]
            if (Tools::getIsset('PKAdminExcludedIps')) {
                $PKAdminExcludedIps = "";
                $tmp = Tools::getValue('PKAdminExcludedIps',$PKAdminExcludedIps);
                foreach (explode(',',$tmp) as $value) {
                    if (PKHelper::isIPv4($value) || PKHelper::isIPv6($value))
                        $PKAdminExcludedIps .= $value.',';
                    else
                        $this->_errors[] = $this->displayError(sprintf($this->l('Error excluded ip "%s" is not valid'),$value));
                }
                $PKAdminExcludedIps = trim($PKAdminExcludedIps,',');
            }
            // [PKAdminExcludedQueryParameters]
            if (Tools::getIsset('PKAdminExcludedQueryParameters')) {
                $PKAdminExcludedQueryParameters = Tools::getValue('PKAdminExcludedQueryParameters',$PKAdminExcludedQueryParameters);
            }
            // [PKAdminTimezone]
            if (Tools::getIsset('PKAdminTimezone')) {
                $PKAdminTimezone = Tools::getValue('PKAdminTimezone',$PKAdminTimezone);
            }
            // [PKAdminCurrency]
            if (Tools::getIsset('PKAdminCurrency')) {
                $PKAdminCurrency = Tools::getValue('PKAdminCurrency',$PKAdminCurrency);
            }
            // [PKAdminExcludedUserAgents]
            if (Tools::getIsset('PKAdminExcludedUserAgents')) {
                $PKAdminExcludedUserAgents = Tools::getValue('PKAdminExcludedUserAgents',$PKAdminExcludedUserAgents);
            }
            // [PKAdminKeepURLFragments]
            if (Tools::getIsset('PKAdminKeepURLFragments')) {
                $PKAdminKeepURLFragments = true;
            } else {
                $PKAdminKeepURLFragments = false;
            }
            if ($result = PKHelper::updatePiwikSite(
                    $PKAdminIdSite,$PKAdminSiteName,$PKAdminSiteUrls,
                    $PKAdminEcommerce,$PKAdminSiteSearch,
                    $PKAdminSearchKeywordParameters,
                    $PKAdminSearchCategoryParameters,
                    $PKAdminExcludedIps,$PKAdminExcludedQueryParameters,
                    $PKAdminTimezone,$PKAdminCurrency,$PKAdminGroup,
                    $PKAdminStartDate,$PKAdminExcludedUserAgents,
                    $PKAdminKeepURLFragments,$PKAdminSiteType)) {
                /*
                 *  all is good 
                 * @todo minimize efter propper testing, "left over"
                 */
            } else {
                $this->displayErrors(PKHelper::$errors);
                PKHelper::$errors = PKHelper::$error = "";
            }
        }
        if ($isPost) {
            if (count($this->_errors))
                $this->_errors[] = $this->displayConfirmation($this->l('Configuration updated, but contains errors/warnings'));
            else
                $this->_errors[] = $this->displayConfirmation($this->l('Configuration updated successfully'));
        }
    }

    /* HOOKs */

    public function hookActionProductCancel($params) {
        /*
         * @todo research [ps 1.6]
         * admin hook, wonder if this can be implemented
         * remove a product from the cart in Piwik
         * 
         * $params = array('order'=>obj [Order], 'id_order_detail'=>int)
         * 
         * if (version_compare(_PS_VERSION_, '1.5', '>=')
         *     $this->registerHook('actionProductCancel')
         */
    }

    public function hookProductFooter($params) {
        /**
         * @todo research
         * use for product views, keeping hookFooter as simple as possible
         * $params = array('product'=>$product, 'category'=>$category)
         * displayFooterProduct ?? [array('product'=>obj, 'category'=>obj)]
         * 
         * $this->registerHook('productfooter')
         */
    }

    public function hookActionCartSave() {
        /*
         * @todo research [ps 1.6]
         * hmm, called on cart add and update
         * 
         * if (version_compare(_PS_VERSION_, '1.5', '>=')
         *     $this->registerHook('actionCartSave')
         */
//        if (!isset($this->context->cart))
//            return;
//
//        $cart = array(
//            'controller' => Tools::getValue('controller'),
//            'addAction' => Tools::getValue('add') ? 'add' : '',
//            'removeAction' => Tools::getValue('delete') ? 'delete' : '',
//            'extraAction' => Tools::getValue('op'),
//            'qty' => (int)Tools::getValue('qty',1)
//        );
    }

    /**
     * hook into maintenance page.
     * @param array $params empty array
     * @return string
     * @since 0.8
     */
    public function hookdisplayMaintenance($params) {
        return $this->hookFooter($params);
    }

    /**
     * PIWIK don't track links on the same site eg. 
     * if product is view in an iframe so we add this and makes sure that it is content only view 
     * @param mixed $param
     * @return string
     */
    public function hookdisplayRightColumnProduct($param) {
        if ((int)Configuration::get(PKHelper::CPREFIX.'SITEID') <= 0)
            return "";
        if ((int)Tools::getValue('content_only') > 0 && get_class($this->context->controller) == 'ProductController') {
            return $this->hookFooter($param);
        }
    }

    /**
     * Search action
     * @param array $param
     */
    public function hookactionSearch($param) {
        if ((int)Configuration::get(PKHelper::CPREFIX.'SITEID') <= 0)
            return "";
        $param['total'] = intval($param['total']);
        // $param['expr'] is not the searched word if lets say search is Snitmøntre then the $param['expr'] will be Snitmontre
        $expr = Tools::getIsset('search_query') ? htmlentities(Tools::getValue('search_query')) : $param['expr'];
        /* if multi pages in search add page number of current if set! */
        $search_tpl = Configuration::get(PKHelper::CPREFIX.'SEARCH_QUERY');
        if ($search_tpl === false)
            $search_tpl = "{QUERY} ({PAGE})";
        if (Tools::getIsset('p')) {
            $search_tpl = str_replace('{QUERY}',$expr,$search_tpl);
            $expr = str_replace('{PAGE}',Tools::getValue('p'),$search_tpl);
        }

        $this->context->smarty->assign(array(
            PKHelper::CPREFIX.'SITE_SEARCH' => "_paq.push(['trackSiteSearch',\"{$expr}\",false,{$param['total']}]);"
        ));
    }

    /**
     * only checks that the module is registered in hook "footer", 
     * this way we only append javescript to the end of the page!
     * @param mixed $params
     */
    public function hookHeader($params) {
        if (!$this->isRegisteredInHook('footer'))
            $this->registerHook('footer');
    }

    public function hookOrderConfirmation($params) {
        if ((int)Configuration::get(PKHelper::CPREFIX.'SITEID') <= 0)
            return "";

        $order = $params['objOrder'];
        if (Validate::isLoadedObject($order)) {

            $this->__setConfigDefault();

            $this->context->smarty->assign(PKHelper::CPREFIX.'ORDER',TRUE);
            $this->context->smarty->assign(PKHelper::CPREFIX.'CART',FALSE);


            $smarty_ad = array();
            foreach ($params['objOrder']->getProductsDetail() as $value) {
                $smarty_ad[] = array(
                    'SKU' => $this->parseProductSku($value['product_id'],(isset($value['product_attribute_id']) ? $value['product_attribute_id'] : FALSE),(isset($value['product_reference']) ? $value['product_reference'] : FALSE)),
                    'NAME' => $value['product_name'],
                    'CATEGORY' => $this->get_category_names_by_product($value['product_id'],FALSE),
                    'PRICE' => $this->currencyConvertion(
                            array(
                                'price' => (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : 0.00)),
                                'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                            )
                    ),
                    'QUANTITY' => $value['product_quantity'],
                );
            }
            $this->context->smarty->assign(PKHelper::CPREFIX.'ORDER_PRODUCTS',$smarty_ad);
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
            $this->context->smarty->assign(PKHelper::CPREFIX.'ORDER_DETAILS',$ORDER_DETAILS);

            // avoid double tracking on complete order.
            self::$_isOrder = TRUE;
            return $this->display(__FILE__,'views/templates/hook/jstracking.tpl');
        }
    }

    public function hookFooter($params) {
        if ((int)Configuration::get(PKHelper::CPREFIX.'SITEID') <= 0)
            return "";

        if (self::$_isOrder)
            return "";

        if (_PS_VERSION_ < '1.5.6') {
            /* get page name the LAME way :) */
            if (method_exists($this->context->smarty,'get_template_vars')) { /* smarty_2 */
                $page_name = $this->context->smarty->get_template_vars('page_name');
            } else if (method_exists($this->context->smarty,'getTemplateVars')) {/* smarty */
                $page_name = $this->context->smarty->getTemplateVars('page_name');
            } else
                $page_name = "";
        }
        $this->__setConfigDefault();
        $this->context->smarty->assign(PKHelper::CPREFIX.'ORDER',FALSE);

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
                    'SKU' => $this->parseProductSku($value['id_product'],(isset($value['id_product_attribute']) && $value['id_product_attribute'] > 0 ? $value['id_product_attribute'] : FALSE),(isset($value['reference']) ? $value['reference'] : FALSE)),
                    'NAME' => $value['name'].(isset($value['attributes']) ? ' ('.$value['attributes'].')' : ''),
                    'CATEGORY' => $this->get_category_names_by_product($value['id_product'],FALSE),
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
                $this->context->smarty->assign(PKHelper::CPREFIX.'CART',TRUE);
                $this->context->smarty->assign(PKHelper::CPREFIX.'CART_PRODUCTS',$smarty_ad);
                $this->context->smarty->assign(PKHelper::CPREFIX.'CART_TOTAL',$this->currencyConvertion(
                                array(
                                    'price' => $this->context->cart->getOrderTotal(),
                                    'conversion_rate' => $Currency->conversion_rate,
                                )
                ));
            } else {
                $this->context->smarty->assign(PKHelper::CPREFIX.'CART',FALSE);
            }
            unset($smarty_ad);
        } else {
            $this->context->smarty->assign(PKHelper::CPREFIX.'CART',FALSE);
        }

        $is404 = false;
        if (!empty($this->context->controller->errors)) {
            foreach ($this->context->controller->errors as $key => $value) {
                if ($value == Tools::displayError('Product not found'))
                    $is404 = true;
                if ($value == Tools::displayError('This product is no longer available.'))
                    $is404 = true;
            }
        }
        if (
                (strtolower(get_class($this->context->controller)) == 'pagenotfoundcontroller') ||
                (isset($this->context->controller->php_self) && ($this->context->controller->php_self == '404')) ||
                (isset($this->context->controller->page_name) && (strtolower($this->context->controller->page_name) == 'pagenotfound'))
        ) {
            $is404 = true;
        }

        $this->context->smarty->assign(array("PK404" => $is404));

        if (_PS_VERSION_ < '1.5.6')
            $this->_hookFooterPS14($params,$page_name);
        else if (_PS_VERSION_ >= '1.5')
            $this->_hookFooter($params);

        return $this->display(__FILE__,'views/templates/hook/jstracking.tpl');
    }

    /**
     * add Prestashop !LATEST! specific settings
     * @param mixed $params
     * @since 0.4
     */
    private function _hookFooter($params) {
        /* product tracking */
        if (get_class($this->context->controller) == 'ProductController') {
            $products = array(array('product' => $this->context->controller->getProduct(),'categorys' => NULL));
            if (isset($products) && isset($products[0]['product'])) {
                $smarty_ad = array();
                foreach ($products as $product) {
                    if (!Validate::isLoadedObject($product['product']))
                        continue;
                    if ($product['categorys'] == NULL)
                        $product['categorys'] = $this->get_category_names_by_product($product['product']->id,FALSE);
                    $smarty_ad[] = array(
                        /* (required) SKU: Product unique identifier */
                        'SKU' => $this->parseProductSku($product['product']->id,FALSE,(isset($product['product']->reference) ? $product['product']->reference : FALSE)),
                        /* (optional) Product name */
                        'NAME' => $product['product']->name,
                        /* (optional) Product category, or array of up to 5 categories */
                        'CATEGORY' => $product['categorys'],//$category->name,
                        /* (optional) Product Price as displayed on the page */
                        'PRICE' => $this->currencyConvertion(
                                array(
                                    'price' => Product::getPriceStatic($product['product']->id,true,false),
                                    'conversion_rate' => $this->context->currency->conversion_rate,
                                )
                        ),
                    );
                }
                $this->context->smarty->assign(array(PKHelper::CPREFIX.'PRODUCTS' => $smarty_ad));
                unset($smarty_ad);
            }
        }

        /* category tracking */
        if (get_class($this->context->controller) == 'CategoryController') {
            $category = $this->context->controller->getCategory();
            if (Validate::isLoadedObject($category)) {
                $this->context->smarty->assign(array(
                    PKHelper::CPREFIX.'category' => array('NAME' => $category->name),
                ));
            }
        }
    }

    /**
     * add Prestashop 1.4 to 1.5.6 specific settings
     * @param mixed $params
     * @since 0.4
     */
    private function _hookFooterPS14($params,$page_name) {
        if (empty($page_name)) {
            /* we can't do any thing use full  */
            return;
        }

        if (strtolower($page_name) == "product" && isset($_GET['id_product']) && Validate::isUnsignedInt($_GET['id_product'])) {
            $product = new Product($_GET['id_product'],false,(isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (isset($this->context->cookie->id_lang) ? $this->context->cookie->id_lang : NULL)));
            if (!Validate::isLoadedObject($product))
                return;
            $product_categorys = $this->get_category_names_by_product($product->id,FALSE);
            $smarty_ad = array(
                array(
                    /* (required) SKU: Product unique identifier */
                    'SKU' => $this->parseProductSku($product->id,FALSE,(isset($product->reference) ? $product->reference : FALSE)),
                    /* (optional) Product name */
                    'NAME' => $product->name,
                    /* (optional) Product category, or array of up to 5 categories */
                    'CATEGORY' => $product_categorys,
                    /* (optional) Product Price as displayed on the page */
                    'PRICE' => $this->currencyConvertion(
                            array(
                                'price' => Product::getPriceStatic($product->id,true,false),
                                'conversion_rate' => false,
                            )
                    ),
                )
            );
            $this->context->smarty->assign(array(PKHelper::CPREFIX.'PRODUCTS' => $smarty_ad));
            unset($smarty_ad);
        }
        /* category tracking */
        if (strtolower($page_name) == "category" && isset($_GET['id_category']) && Validate::isUnsignedInt($_GET['id_category'])) {
            $category = new Category($_GET['id_category'],(isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (isset($this->context->cookie->id_lang) ? $this->context->cookie->id_lang : NULL)));
            $this->context->smarty->assign(array(
                PKHelper::CPREFIX.'category' => array('NAME' => $category->name),
            ));
        }
    }

    /**
     * search action
     * @param array $params
     * @since 0.4
     */
    public function hookSearch($params) {
        if ((int)Configuration::get(PKHelper::CPREFIX.'SITEID') <= 0)
            return "";
        $this->hookactionSearch($params);
    }

    /* HELPERS */

    
    /**
     * get template for product id
     * @param int $v
     * @return string
     */
    private function getProductIdTemplate($v = 1) {
        switch ($v) {
            case 1:
                $PIWIK_PRODID_V1 = Configuration::get(PKHelper::CPREFIX.'PRODID_V1');
                return !empty($PIWIK_PRODID_V1) ? $PIWIK_PRODID_V1 : '{ID}-{ATTRID}#{REFERENCE}';
            case 2:
                $PIWIK_PRODID_V2 = Configuration::get(PKHelper::CPREFIX.'PRODID_V2');
                return !empty($PIWIK_PRODID_V2) ? $PIWIK_PRODID_V2 : '{ID}#{REFERENCE}';
            case 3:
                $PIWIK_PRODID_V3 = Configuration::get(PKHelper::CPREFIX.'PRODID_V3');
                return !empty($PIWIK_PRODID_V3) ? $PIWIK_PRODID_V3 : '{ID}-{ATTRID}';
        }
        return '{ID}';
    }

    /**
     * get timezone list
     * @return array
     */
    private function getTimezonesList($authtoken=null,$piwikhost=null) {
        $pktimezones = array();
        $tmp = PKHelper::getTimezonesList(false,$authtoken,$piwikhost);
        $this->displayErrorsPiwik();
        foreach ($tmp as $key => $pktz) {
            if (!isset($pktimezones[$key])) {
                $pktimezones[$key] = array(
                    'name' => $key,
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
        unset($tmp,$pktz,$pktzV,$pktzK);
        return $pktimezones;
    }

    /**
     * get the correct template file to use
     * check for templates in current active shop theme falls back to default shipped with this module
     * @param string $file template.tpl
     * @param string $path relative path from root
     * @return string full path to the template file
     */
    private function _get_theme_file($file,$path = "views/templates/admin") {
        $pk_templates_dir = dirname(__FILE__)."/".$path;
        $pk_templates_dir_theme = _PS_THEME_DIR_.'modules/'.$this->name."/".$path;
        if (file_exists($pk_templates_dir_theme."/".$file))
            return $pk_templates_dir_theme."/".$file;
        return $pk_templates_dir."/".$file;
    }
    /**
     * set css and javascript used within admin
     * @return void
     * @since 0.8.4
     */
    protected function setMedia() {
        $this->context->controller->addCss($this->_path.'css/styles.css');
        if (version_compare(_PS_VERSION_,'1.5.0.4',"<=")) {
            $this->context->controller->addJquery(_PS_JQUERY_VERSION_);
            $this->context->controller->addJs($this->_path.'js/jquery.alerts.js');
            $this->context->controller->addCss($this->_path.'js/jquery.alerts.css');
        }
        if (version_compare(_PS_VERSION_,'1.5.2.999',"<="))
            $this->context->controller->addJqueryPlugin('fancybox',_PS_JS_DIR_.'jquery/plugins/');
        if (version_compare(_PS_VERSION_,'1.6',"<"))
            $this->context->controller->addJqueryUI(array('ui.core','ui.widget'));
        if (version_compare(_PS_VERSION_,'1.5',">="))
            $this->context->controller->addJqueryPlugin('tagify',_PS_JS_DIR_.'jquery/plugins/');
    }

    /**
     * returns true if request is wizard
     * @return boolean
     * @since 0.8.4
     */
    private function isWizardRequest() {
        return Tools::getIsset('pkwizard');
    }

    private function parseProductSku($id,$attrid = FALSE,$ref = FALSE) {
        if (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== FALSE) && (!empty($ref) && !is_null($ref) && $ref !== FALSE)) {
            $PIWIK_PRODID_V1 = Configuration::get(PKHelper::CPREFIX.'PRODID_V1');
            return str_replace(array('{ID}','{ATTRID}','{REFERENCE}'),array($id,$attrid,$ref),$PIWIK_PRODID_V1);
        } elseif (Validate::isInt($id) && (!empty($ref) && !is_null($ref) && $ref !== FALSE)) {
            $PIWIK_PRODID_V2 = Configuration::get(PKHelper::CPREFIX.'PRODID_V2');
            return str_replace(array('{ID}','{REFERENCE}'),array($id,$ref),$PIWIK_PRODID_V2);
        } elseif (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== FALSE)) {
            $PIWIK_PRODID_V3 = Configuration::get(PKHelper::CPREFIX.'PRODID_V3');
            return str_replace(array('{ID}','{ATTRID}'),array($id,$attrid),$PIWIK_PRODID_V3);
        } else {
            return $id;
        }
    }
    
    public function displayErrorsPiwik2() {
            $this->displayErrors(PiwikWizardHelper::$errors);
            PiwikWizardHelper::$errors = "";
    }
    public function displayErrorsPiwik() {
        $this->displayErrors(PKHelper::$errors);
        PKHelper::$errors = PKHelper::$error = "";
    }

    /**
     * Makes a call to '$this->displayError' for each error contained within the array and puts the returned values into variable '$this->_errors' as an array
     * @param array|object $errors if object a call to method 'getErrors()' is made followed by a call to 'clearErrors()' if the method exists
     * @return void
     * @changed in version '0.8.4' to allow the use of object methods getErrors() and clearErrors(), this is to minimize the need to collect errors from objects into new single variables
     */
    public function displayErrors($errors) {
        $_errors = array();
        if (!empty($errors) && is_array($errors)) {
            $_errors = $errors;
        } else if (is_object($errors) && method_exists($errors,'getErrors')) {
            $_errors = $errors->getErrors();
            if (method_exists($errors,'clearErrors')) {
                $errors->clearErrors();
            }
        }
        foreach ($_errors as $key => $value) {
            $this->_errors[] = $this->displayError($value);
        }
    }

    /**
     * convert into default currency used in Piwik
     * @param array $params
     * @return float
     * @since 0.4
     */
    private function currencyConvertion($params) {
        $pkc = Configuration::get(PKHelper::CPREFIX."DEFAULT_CURRENCY");
        if (empty($pkc))
            return (float)$params['price'];
        if ($params['conversion_rate'] === FALSE || $params['conversion_rate'] == 0.00 || $params['conversion_rate'] == 1.00) {
            // shop default
            return Tools::convertPrice((float)$params['price'],Currency::getCurrencyInstance((int)(Currency::getIdByIsoCode($pkc))));
        } else {
            $_shop_price = (float)((float)$params['price'] / (float)$params['conversion_rate']);
            return Tools::convertPrice($_shop_price,Currency::getCurrencyInstance((int)(Currency::getIdByIsoCode($pkc))));
        }
        return (float)$params['price'];
    }

    /**
     * get category names by product id
     * @param integer $id product id
     * @param boolean $array get categories as PHP array (TRUE), or javascript (FALSE)
     * @return string|array
     */
    private function get_category_names_by_product($id,$array = true) {
        $_categories = Product::getProductCategoriesFull($id,$this->context->cookie->id_lang);
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
                $categories .= '"'.addcslashes($category['name'],'"').'",';
                if ($c == 5)
                    break;
            }
            $categories = rtrim($categories,',').']';
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
    public static function getModuleLink($module,$controller = 'default') {
        if (version_compare(_PS_VERSION_,'1.5.0.13',"<="))
            return Tools::getShopDomainSsl(true,true)._MODULE_DIR_.$module.'/'.$controller.'.php';
        else
            return Context::getContext()->link->getModuleLink($module,$controller);
    }

    private function __setConfigDefault() {
        $key_prefix = PKHelper::CPREFIX;
        $keys = array(
            $key_prefix.'EXHTML', $key_prefix.'DHashTag',
            $key_prefix.'SET_DOMAINS', $key_prefix.'COOKIE_DOMAIN',
            $key_prefix.'DNT', $key_prefix.'SESSION_TIMEOUT',
            $key_prefix.'RCOOKIE_TIMEOUT', $key_prefix.'COOKIE_TIMEOUT',
            $key_prefix.'SITEID', $key_prefix.'USE_PROXY',
            $key_prefix.'HOST', $key_prefix.'PROXY_SCRIPT',
            $key_prefix.'LINKTRACK', $key_prefix.'LINKClS',
            $key_prefix.'LINKTTIME', $key_prefix.'COOKIEPREFIX',
            $key_prefix.'COOKIEPATH', $key_prefix.'LINKClSIGNORE',
            $key_prefix.'APTURL',
        );
        $configuration = Configuration::getMultiple($keys);

        $this->context->smarty->assign($key_prefix.'EXHTML',$configuration["{$key_prefix}EXHTML"]);
        $this->context->smarty->assign($key_prefix.'COOKIE_DOMAIN',(empty($configuration["{$key_prefix}COOKIE_DOMAIN"]) ? FALSE : $configuration["{$key_prefix}COOKIE_DOMAIN"]));
        $this->context->smarty->assign($key_prefix.'COOKIEPREFIX',(empty($configuration["{$key_prefix}COOKIEPREFIX"]) ? FALSE : $configuration["{$key_prefix}COOKIEPREFIX"]));
        $this->context->smarty->assign($key_prefix.'COOKIEPATH',(empty($configuration["{$key_prefix}COOKIEPATH"]) ? FALSE : $configuration["{$key_prefix}COOKIEPATH"]));
        $this->context->smarty->assign($key_prefix.'SITEID',$configuration["{$key_prefix}SITEID"]);
        $this->context->smarty->assign(PKHelper::CPREFIX.'VER',$this->piwikVersion);
        $this->context->smarty->assign(PKHelper::CPREFIX.'USE_PROXY',(bool)$configuration["{$key_prefix}USE_PROXY"]);
        $this->context->smarty->assign($key_prefix.'DHashTag',(bool)$configuration[$key_prefix.'DHashTag']);
        $this->context->smarty->assign($key_prefix.'APTURL',(bool)$configuration[$key_prefix.'APTURL']);
        $this->context->smarty->assign($key_prefix.'HOSTAPI',$configuration["{$key_prefix}HOST"]);
        $this->context->smarty->assign($key_prefix.'LINKTRACK',(bool)$configuration[$key_prefix.'LINKTRACK']);
        $this->context->smarty->assign($key_prefix.'DNT',(bool)$configuration["{$key_prefix}DNT"]);
        
        // using proxy script?
        if ((bool)$configuration["{$key_prefix}USE_PROXY"])
            $this->context->smarty->assign($key_prefix.'HOST',$configuration["{$key_prefix}PROXY_SCRIPT"]);
        else
            $this->context->smarty->assign($key_prefix.'HOST',$configuration["{$key_prefix}HOST"]);
            
        // timeout
        $pkvct = (int)$configuration["{$key_prefix}COOKIE_TIMEOUT"];
        if ($pkvct != 0 && $pkvct !== FALSE && ($pkvct != (int)(self::PK_VC_TIMEOUT * 60))) {
            $this->context->smarty->assign($key_prefix.'COOKIE_TIMEOUT',$pkvct);
        }
        unset($pkvct);
        $pkrct = (int)$configuration["{$key_prefix}RCOOKIE_TIMEOUT"];
        if ($pkrct != 0 && $pkrct !== FALSE && ($pkrct != (int)(self::PK_RC_TIMEOUT * 60))) {
            $this->context->smarty->assign($key_prefix.'RCOOKIE_TIMEOUT',$pkrct);
        }
        unset($pkrct);
        $pksct = (int)$configuration["{$key_prefix}SESSION_TIMEOUT"];
        if ($pksct != 0 && $pksct !== FALSE && ($pksct != (int)(self::PK_SC_TIMEOUT * 60))) {
            $this->context->smarty->assign($key_prefix.'SESSION_TIMEOUT',$pksct);
        }
        unset($pksct);
        // domains
        if (!empty($configuration["{$key_prefix}SET_DOMAINS"])) {
            $sdArr = explode(',',$configuration["{$key_prefix}SET_DOMAINS"]);
            if (count($sdArr) > 1)
                $PIWIK_SET_DOMAINS = "['".trim(implode("','",$sdArr),",'")."']";
            else
                $PIWIK_SET_DOMAINS = "'{$sdArr[0]}'";
            $this->context->smarty->assign($key_prefix.'SET_DOMAINS',(!empty($PIWIK_SET_DOMAINS) ? $PIWIK_SET_DOMAINS : FALSE));
            unset($sdArr);
        }else {
            $this->context->smarty->assign($key_prefix.'SET_DOMAINS',FALSE);
        }
        unset($PIWIK_SET_DOMAINS);
        // link classes
        if (!empty($configuration["{$key_prefix}LINKClS"])) {
            $sdArr = explode(',',$configuration["{$key_prefix}LINKClS"]);
            if (count($sdArr) > 1)
                $PIWIK_LINKClS = "['".trim(implode("','",$sdArr),",'")."']";
            else
                $PIWIK_LINKClS = "'{$sdArr[0]}'";
            $this->context->smarty->assign($key_prefix.'LINKClS',(!empty($PIWIK_LINKClS) ? $PIWIK_LINKClS : FALSE));
            unset($sdArr);
        }else {
            $this->context->smarty->assign($key_prefix.'LINKClS',FALSE);
        }
        unset($PIWIK_LINKClS);
        // link ignore classes
        if (!empty($configuration["{$key_prefix}LINKClSIGNORE"])) {
            $sdArr = explode(',',$configuration["{$key_prefix}LINKClSIGNORE"]);
            if (count($sdArr) > 1)
                $PIWIK_LINKClSIGNORE = "['".trim(implode("','",$sdArr),",'")."']";
            else
                $PIWIK_LINKClSIGNORE = "'{$sdArr[0]}'";
            $this->context->smarty->assign($key_prefix.'LINKClSIGNORE',(!empty($PIWIK_LINKClSIGNORE) ? $PIWIK_LINKClSIGNORE : FALSE));
            unset($sdArr);
        }else {
            $this->context->smarty->assign($key_prefix.'LINKClSIGNORE',FALSE);
        }
        unset($PIWIK_LINKClSIGNORE);
        // link track time
        $tmp = $configuration["{$key_prefix}LINKTTIME"];
        if ($tmp != 0 && $tmp !== FALSE) {
            $this->context->smarty->assign($key_prefix.'LINKTTIME',(int)($tmp*60));
        }
        
        
        if (version_compare(_PS_VERSION_,'1.5', '<') && $this->context->cookie->isLogged()) {
            $this->context->smarty->assign($key_prefix.'UUID',$this->context->cookie->id_customer);
        } else if ($this->context->customer->isLogged()) {
            $this->context->smarty->assign($key_prefix.'UUID',$this->context->customer->id);
        }
    }
    
    /** @todo revise this method it has never been used as intended ;> */
    private function getConfigFields($form = FALSE) {
        $fields = array(
            PKHelper::CPREFIX.'USE_PROXY',PKHelper::CPREFIX.'HOST',
            PKHelper::CPREFIX.'SITEID',PKHelper::CPREFIX.'TOKEN_AUTH',
            PKHelper::CPREFIX.'COOKIE_TIMEOUT',PKHelper::CPREFIX.'SESSION_TIMEOUT',
            PKHelper::CPREFIX.'DEFAULT_CURRENCY',PKHelper::CPREFIX.'CRHTTPS',
            PKHelper::CPREFIX.'PRODID_V1',PKHelper::CPREFIX.'PRODID_V2',
            PKHelper::CPREFIX.'PRODID_V3',PKHelper::CPREFIX.'COOKIE_DOMAIN',
            PKHelper::CPREFIX.'SET_DOMAINS',PKHelper::CPREFIX.'DNT',
            PKHelper::CPREFIX.'EXHTML',PKHelper::CPREFIX.'RCOOKIE_TIMEOUT',
            PKHelper::CPREFIX.'USRNAME',PKHelper::CPREFIX.'USRPASSWD',
            PKHelper::CPREFIX.'PAUTHUSR',PKHelper::CPREFIX.'PAUTHPWD',
            PKHelper::CPREFIX.'DREPDATE',PKHelper::CPREFIX.'USE_CURL'
        );
        $defaults = array(
            0,"",0,"",self::PK_VC_TIMEOUT,self::PK_SC_TIMEOUT,'EUR',0,
            '{ID}-{ATTRID}#{REFERENCE}','{ID}#{REFERENCE}',
            '{ID}-{ATTRID}',Tools::getShopDomain(),'',0,
            '',self::PK_RC_TIMEOUT,'','','','','day|today',0
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

    private function __wizardDefaults() {
        // set translations
        PiwikWizardHelper::$strings['4ddd9129714e7146ed2215bcbd559335'] = $this->l("I encountered an unknown error while trying to get the selected site, id #%s");
        PiwikWizardHelper::$strings['e41246ca9fd83a123022c5c5b7a6f866'] = $this->l("I'm unable, to get admin access to the selected site id #%s");
        PiwikWizardHelper::$strings['8a7d6b386e97596cb28878e9be5804b8'] = $this->l("Piwik sitename is missing");
        PiwikWizardHelper::$strings['7948cb754538ab57b44c956c22aa5517'] = $this->l("Piwik main url is missing");
        PiwikWizardHelper::$strings['0f30ab07916f20952f2e6ef70a91d364'] = $this->l("Piwik currency is missing");
        PiwikWizardHelper::$strings['f7472169e468dd1fd901720f4bae1957'] = $this->l("Piwik timezone is missing");
        PiwikWizardHelper::$strings['75e263846f84003f0180137e79542d38'] = $this->l("Error while creating site in piwik please check the following messages for clues");

        // posted username and passwd etc..
        PiwikWizardHelper::getFormValuesInternal();
    }

    /* INSTALL / UNINSTALL */

    /**
     * Reset module configuration
     */
    public function reset() {
        foreach ($this->getConfigFields(FALSE) as $key => $value) {
            if (Shop::getContext() == Shop::CONTEXT_ALL) {
                // delete for all shops.!
                Configuration::deleteByName($key);
            } else {
                // delete only for current shop.!
                if (!Validate::isConfigName($key))
                    continue;
                $id_shop = Shop::getContextShopID(true);
                Db::getInstance()->execute('
                    DELETE FROM `'._DB_PREFIX_.'configuration_lang`
                    WHERE `id_configuration` IN (
                            SELECT `id_configuration`
                            FROM `'._DB_PREFIX_.'configuration`
                            WHERE `name` = "'.pSQL($key).'" AND `id_shop` = "'.pSQL($id_shop).'"
                    )');
                Db::getInstance()->execute('
                    DELETE FROM `'._DB_PREFIX_.'configuration`
                    WHERE `name` = "'.pSQL($key).'" AND `id_shop` = "'.pSQL($id_shop).'"');
            }
        }
        return true;
    }

    /**
     * Install the module
     * @return boolean false on install error
     * @todo revise the heavy use of code used to get tabs properly installed (perhaps simplify the controller class)
     */
    public function install() {
        /* create complete new page tab */
        $tab = new Tab();
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int)$lang['id_lang']] = 'Piwik Analytics';
        }
        $tab->module = 'piwikanalyticsjs';
        $tab->active = TRUE;

        if (method_exists('Tab','getInstanceFromClassName')) {
            if (version_compare(_PS_VERSION_,'1.5.0.5',">=") && version_compare(_PS_VERSION_,'1.5.3.999',"<=")) {
                $tab->class_name = 'PiwikAnalytics15';
            } else if (version_compare(_PS_VERSION_,'1.5.0.13',"<=")) {
                $tab->class_name = 'AdminPiwikAnalytics';
            } else {
                $tab->class_name = 'PiwikAnalytics';
            }
            $AdminParentStats = Tab::getInstanceFromClassName('AdminStats');
            if ($AdminParentStats == null || !($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) || $AdminParentStats->id == 0)
                $AdminParentStats = Tab::getInstanceFromClassName('AdminParentStats');
        } else if (method_exists('Tab','getIdFromClassName')) {
            if (version_compare(_PS_VERSION_,'1.5.0.5',">=") && version_compare(_PS_VERSION_,'1.5.3.999',"<=")) {
                $tab->class_name = 'PiwikAnalytics15';
            } else if (version_compare(_PS_VERSION_,'1.5.0.13',"<=")) {
                $tab->class_name = 'AdminPiwikAnalytics';
            } else {
                $tab->class_name = 'PiwikAnalytics';
            }
            $tmpId = Tab::getIdFromClassName('AdminStats');
            if ($tmpId != null && $tmpId > 0)
                $AdminParentStats = new Tab($tmpId);
            else {
                $tmpId = Tab::getIdFromClassName('AdminParentStats');
                if ($tmpId != null && $tmpId > 0)
                    $AdminParentStats = new Tab($tmpId);
            }
        }

        $tab->id_parent = (isset($AdminParentStats) && ($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? $AdminParentStats->id : -1);
        if ($tab->add())
            Configuration::updateValue(PKHelper::CPREFIX.'TAPID',(int)$tab->id);
        else {
            $this->_errors[] = sprintf($this->l('Unable to create new tab "Piwik Analytics", Please forward tthe following info to the developer %s'),"<br/>"
                    .(isset($AdminParentStats) ? "isset(\$AdminParentStats): True" : "isset(\$AdminParentStats): False")
                    ."<br/>"
                    ."Type of \$AdminParentStats: ".gettype($AdminParentStats)
                    ."<br/>"
                    ."Class name of \$AdminParentStats: ".get_class($AdminParentStats)
                    ."<br/>"
                    .(($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats instanceof Tab: True" : "\$AdminParentStats instanceof Tab: False")
                    ."<br/>"
                    .(($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->id: ".$AdminParentStats->id : "\$AdminParentStats->id: ?0?")
                    ."<br/>"
                    .(($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->name: ".$AdminParentStats->name : "\$AdminParentStats->name: ?0?")
                    ."<br/>"
                    .(($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->class_name: ".$AdminParentStats->class_name : "\$AdminParentStats->class_name: ?0?")
                    ."<br/>"
                    ."Prestashop version: "._PS_VERSION_
                    ."<br/>"
                    ."PHP version: ".PHP_VERSION
            );
        }

        /* default values */
        foreach ($this->getConfigFields(FALSE) as $key => $value) {
            Configuration::updateValue($key,$value);
        }

//  properly not needed only here as a reminder, 
//  if management of carts in Piwik becomes available
//  
//        if (!Db::getInstance()->Execute('
//			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'piwikanalytics` (
//				`id_pk_analytics` int(11) NOT NULL AUTO_INCREMENT,
//				`id_order` int(11) NOT NULL,
//				`id_customer` int(10) NOT NULL,
//				`id_shop` int(11) NOT NULL,
//				`sent` tinyint(1) DEFAULT NULL,
//				`date_add` datetime DEFAULT NULL,
//				PRIMARY KEY (`id_google_analytics`),
//				KEY `id_order` (`id_order`),
//				KEY `sent` (`sent`)
//			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'))
        return (parent::install() &&
                $this->registerHook('header') &&
                $this->registerHook('footer') &&
                $this->registerHook('actionSearch') &&
                $this->registerHook('displayRightColumnProduct') &&
                $this->registerHook('orderConfirmation') &&
                $this->registerHook('displayMaintenance'));
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
                if (method_exists('Tab','getInstanceFromClassName')) {
                    $AdminParentStats = Tab::getInstanceFromClassName('PiwikAnalytics15');
                    if (!isset($AdminParentStats) || !Validate::isLoadedObject($AdminParentStats))
                        $AdminParentStats = Tab::getInstanceFromClassName('AdminPiwikAnalytics');
                    if (!isset($AdminParentStats) || !Validate::isLoadedObject($AdminParentStats))
                        $AdminParentStats = Tab::getInstanceFromClassName('PiwikAnalytics');
                } else if (method_exists('Tab','getIdFromClassName')) {
                    $tmpId = Tab::getIdFromClassName('PiwikAnalytics15');
                    if (!isset($tmpId) || !((bool)$tmpId) || ((int)$tmpId < 1))
                        $tmpId = Tab::getIdFromClassName('AdminPiwikAnalytics');
                    if (!isset($tmpId) || !((bool)$tmpId) || ((int)$tmpId < 1))
                        $tmpId = Tab::getIdFromClassName('PiwikAnalytics');
                    if (!isset($tmpId) || !((bool)$tmpId) || ((int)$tmpId < 1))
                        $AdminParentStats = new Tab($tmpId);
                }
                if (isset($AdminParentStats) && ($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore)) {
                    $AdminParentStats->delete();
                }
            } catch (Exception $ex) {
                
            }
            Configuration::deleteByName(PKHelper::CPREFIX.'TAPID');
            return true;
        }
        return false;
    }

}
