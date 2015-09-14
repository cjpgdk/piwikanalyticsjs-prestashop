<?php

if (!defined('_PS_VERSION_'))
    exit;

include dirname(__FILE__) . '/../piwikmanager/PKClassLoader.php';
PKClassLoader::LoadStatic(array('PiwikHelper'));
PiwikHelper::initialize();

if (!class_exists('PiwikDashboardHelper', false)) {
    include(dirname(__FILE__) . '/PiwikDashboardHelper.php');
    PiwikDashboardHelper::initialize();
}

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
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class piwikdashboard extends Module {

    private $_default_config_values = array();

    public function __construct($name = null, $context = null) {

        $this->_default_config_values[PiwikHelper::CPREFIX . 'DREPDATE'] = 'day|today';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'USRNAME'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'USRPASSWD'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'DEFAULTVIEW'] = 'iframe';

        $this->dependencies[] = "piwikmanager";
        $this->dependencies[] = "piwikanalytics";

        $this->name = 'piwikdashboard';
        $this->tab = 'analytics_stats';
        $this->version = '1.0';
        $this->author = 'Christian M. Jensen';
        $this->displayName = 'Piwik Analytics Dashboard';
        $this->author_uri = 'http://cmjscripter.net';
        $this->url = 'http://cmjnisse.github.io/piwikanalyticsjs-prestashop/';

        $this->bootstrap = true;

        parent::__construct($name, $context);

        $this->description = $this->l('Adds Piwik Analytics Dashboard in admin under stats');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

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
        return parent::install() &&
                $this->installTabs();
    }

    /**
     * Uninstall the module
     * @return boolean false on uninstall error
     */
    public function uninstall() {
        foreach ($this->_default_config_values as $key => $value) {
            // delete only from the shop wee uninstall
            Configuration::deleteFromContext($key);
        }
        // Tabs
        $idTabs = array();
        $idTabs[] = (int) Tab::getIdFromClassName('PiwikAnalyticsDashboard');
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

        // Dashboard
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = $this->l('Dashboard');
        $tab->class_name = 'PiwikAnalyticsDashboard';
        $tab->id_parent = $parent_tab_id;
        $tab->module = $this->name;
        $tab->add();

        return true;
    }

}
