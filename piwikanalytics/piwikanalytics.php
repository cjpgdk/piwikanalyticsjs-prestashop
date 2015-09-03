<?php

if (!defined('_PS_VERSION_'))
    exit;

include dirname(__FILE__) . '/../piwikmanager/PKClassLoader.php';
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
 * along with PiwikAnalytics for prestashop.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @author Christian M. Jensen
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class piwikanalytics extends Module {

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

    private $_default_config_values = array();
    private static $isOrder = FALSE;
    // ||DEV|OPTION|REMOVE||
    private $_hooks = array(
        'displayHeader',
        'displayFooter'
    );

    public function __construct($name = null, $context = null) {

        $this->_default_config_values[PiwikHelper::CPREFIX . 'COOKIE_DOMAIN'] = Tools::getShopDomain();
        $this->_default_config_values[PiwikHelper::CPREFIX . 'COOKIE_TIMEOUT'] = self::PK_VC_TIMEOUT;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'SESSION_TIMEOUT'] = self::PK_SC_TIMEOUT;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'RCOOKIE_TIMEOUT'] = self::PK_RC_TIMEOUT;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'USE_PROXY'] = 0;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY'] = 'EUR';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'PRODID_V1'] = '{ID}-{ATTRID}#{REFERENCE}';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'PRODID_V2'] = '{ID}#{REFERENCE}';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'PRODID_V3'] = '{ID}#{ATTRID}';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'SET_DOMAINS'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'DNT'] = 0;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'EXHTML'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'COOKIE_PATH'] = '/';

//        $this->_default_config_values[PiwikHelper::CPREFIX . 'DREPDATE'] = 'day|today';
//        $this->_default_config_values[PiwikHelper::CPREFIX . 'USRNAME'] = '';
//        $this->_default_config_values[PiwikHelper::CPREFIX . 'USRPASSWD'] = '';

        $this->dependencies[] = "piwikmanager";

        $this->name = 'piwikanalytics';
        $this->tab = 'analytics_stats';
        $this->version = '1.0-dev15';
        $this->author = 'Christian M. Jensen';
        $this->displayName = 'Piwik Analytics Tracking';
        $this->author_uri = 'http://cmjscripter.net';
        $this->url = 'http://cmjnisse.github.io/piwikanalyticsjs-prestashop/';

        $this->bootstrap = true;

        // list front controlers
        $this->controllers = array('piwik');

        parent::__construct($name, $context);

        $this->description = $this->l('Adds Piwik Analytics JavaScript Tracking code to your shop');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        self::$isOrder = false;

        if (is_object($this->context->smarty) && (!is_object($this->smarty) || !($this->smarty instanceof Smarty_Data))) {
            $this->smarty = $this->context->smarty->createData($this->context->smarty);
        }

        foreach ($this->_hooks as $hook) {
            if (!$this->isRegisteredInHook($hook))
                $this->registerHook($hook);
        }
    }

    public function getContent() {
        header('Location: index.php?controller=PiwikAnalyticsTrackingConfig&token=' . Tools::getAdminTokenLite('PiwikAnalyticsTrackingConfig'));
        exit;
    }

    /* ## HOOKs ## */

    /**
     * only checks that the module is registered in hook "footer", 
     * this why we only appent javescript to the end of the page!
     * @param mixed $params
     */
    public function hookdisplayHeader($params) {
        if (!$this->isRegisteredInHook('footer') && !$this->isRegisteredInHook('displayFooter'))
            $this->registerHook('displayFooter');
    }

    public function hookdisplayFooter($params) {

        $piwikAsync = true;
        // short code the config prefix
        $CPREFIX = PiwikHelper::CPREFIX;

        // get all the required data from db config
        $dbConfigKeys = array(
            $CPREFIX . 'SITEID',
            $CPREFIX . 'USE_PROXY',
            $CPREFIX . 'PROXY_SCRIPT',
            $CPREFIX . 'HOST',
            $CPREFIX . 'COOKIE_DOMAIN',
            $CPREFIX . 'SET_DOMAINS',
            $CPREFIX . 'COOKIE_TIMEOUT',
            $CPREFIX . 'RCOOKIE_TIMEOUT',
            $CPREFIX . 'SESSION_TIMEOUT',
        );
        $dbConfigValues = Configuration::getMultiple($dbConfigKeys);

        // if site id is wrong, 
        if ((int) $dbConfigValues[$CPREFIX . 'SITEID'] <= 0)
            return "";
        // if is order.
        if (self::$isOrder)
            return "";

        // get http protocol
        $protocol = Tools::getShopProtocol();

        // set config variables for piwik tracking tamplet
        
        $this->smarty->assign('protocol', $protocol);
        $this->smarty->assign('isOrder', FALSE);
        $this->smarty->assign('idSite', (int) $dbConfigValues[$CPREFIX . 'SITEID']);
        $this->smarty->assign('useProxy', (boolean) $dbConfigValues[$CPREFIX . 'USE_PROXY']);
        $this->smarty->assign('piwikCookieDomain', $dbConfigValues[$CPREFIX . 'COOKIE_DOMAIN']);

        // setDomains
        $piwikSetDomains = $dbConfigValues[$CPREFIX . 'SET_DOMAINS'];
        if (!empty($piwikSetDomains)) {
            $sdArr = explode(',', $piwikSetDomains);
            if (count($sdArr) > 1)
                $piwikSetDomains = "['" . trim(implode("','", $sdArr), ",'") . "']";
            else
                $piwikSetDomains = "'{$sdArr[0]}'";

            $this->smarty->assign('piwikSetDomains', $piwikSetDomains);

            unset($sdArr);
        }

        // setVisitorCookieTimeout
        $pkvct = (int) $dbConfigValues[$CPREFIX . 'COOKIE_TIMEOUT'];
        if ($pkvct > 0 && $pkvct !== FALSE && ($pkvct != (int) (self::PK_VC_TIMEOUT))) {
            $this->smarty->assign('piwikVisitorCookieTimeout', ($pkvct * 60));
        }
        unset($pkvct);

        // setReferralCookieTimeout
        $pkrct = (int) $dbConfigValues[$CPREFIX . 'RCOOKIE_TIMEOUT'];
        if ($pkrct > 0 && $pkrct !== FALSE && ($pkrct != (int) (self::PK_RC_TIMEOUT))) {
            $this->smarty->assign('piwikReferralCookieTimeout', ($pkrct * 60));
        }
        unset($pkrct);

        // setSessionCookieTimeout
        $pksct = (int) $dbConfigValues[$CPREFIX . 'SESSION_TIMEOUT'];
        if ($pksct > 0 && $pksct !== FALSE && ($pksct != (int) (self::PK_SC_TIMEOUT))) {
            $this->smarty->assign('piwikSessionCookieTimeout', ($pksct * 60));
        }
        unset($pksct);

        // piwik url
        if ((bool) $dbConfigValues[$CPREFIX . 'USE_PROXY']) {
            $this->smarty->assign('piwikHost', $dbConfigValues[$CPREFIX . 'PROXY_SCRIPT']);
        } else {
            $this->smarty->assign('piwikHost', $dbConfigValues[$CPREFIX . 'HOST']);
        }
        
        // customer id
        if ($this->context->customer->isLogged()) {
            $this->context->smarty->assign('userId', $this->context->customer->id);
        }

        // return the template for piwik tracking.
        if ($piwikAsync)
            return $this->display(__FILE__, 'piwikAsync.tpl');
        else
            return $this->display(__FILE__, 'jstracking.tpl');
    }

    /* ## Install/Uninstall/Enable/Disable ## */

    /**
     * Activate module.
     * @param bool $force_all If true, enable module for all shop
     */
    public function enable($force_all = false) {
        if (!Module::isInstalled('piwikmanager') || !Module::isEnabled('piwikmanager')) {
            $this->_errors[] = Tools::displayError(sprintf($this->l('Can not enable %s, depends on module piwikmanager'), $this->displayName));
            return false;
        }
        return parent::enable($force_all);
    }

    /**
     * Install the module
     * @return boolean false on install error
     */
    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        /* default values */
        foreach ($this->_default_config_values as $key => $value) {
            /* only set if not isset, compatibility with module 'piwikanalyticsjs' */
            if (Configuration::getGlobalValue($key) === false)
                Configuration::updateGlobalValue($key, $value);
        }
        return parent::install() && $this->installTabs();
    }

    /**
     * Uninstall the module
     * @return boolean false on uninstall error
     */
    public function uninstall() {
        foreach ($this->_default_config_values as $key => $value) {
            Configuration::deleteByName($key);
        }
        // Tabs
        $idTabs = array();
        $idTabs[] = (int) Tab::getIdFromClassName('PiwikAnalyticsTrackingConfig');
        foreach ($idTabs as $idTab) {
            if ($idTab > 0) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }
        return parent::uninstall();
    }

    private function installTabs() {
        // Parent
        $parent_tab_id = Tab::getIdFromClassName('PiwikAnalytics');
        if ($parent_tab_id === false)
            $parent_tab_id = 0;

        // Tracking Config
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = $this->l('Tracking Config');
        $tab->class_name = 'PiwikAnalyticsTrackingConfig';
        $tab->id_parent = $parent_tab_id;
        $tab->module = $this->name;
        $tab->add();

        return true;
    }

}
