<?php

if (!defined('_PS_VERSION_'))
    exit;

include(dirname(__FILE__) . '/PKClassLoader.php');
PKClassLoader::LoadStatic('PiwikHelper');
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

class piwikmanager extends Module {

    private $_default_config_values = array();
    
    public function __construct($name = null, $context = null) {
        
        $this->_default_config_values[PiwikHelper::CPREFIX . 'CRHTTPS'] = 0;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'DEBUG'] = 0;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'USE_CURL'] = 0;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'PAUTHUSR'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'PAUTHPWD'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'TOKEN_AUTH'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'HOST'] = '';

        $this->name = 'piwikmanager';
        $this->tab = 'administration';
        $this->version = '1.0-dev60';
        $this->author = 'Christian M. Jensen';
        $this->displayName = $this->l('Piwik Site Manager');
        $this->author_uri = 'http://cmjscripter.net';
        $this->url = 'http://cmjnisse.github.io/piwikanalyticsjs-prestashop/';

        $this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => '1.6.999.999');
        
        $this->bootstrap = true;

        parent::__construct($name, $context);

        $this->description = $this->l('Piwik Analytics Site Manager and Base classes');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        //* warnings on module list page
        if ($this->id && !Configuration::get(PiwikHelper::CPREFIX . 'TOKEN_AUTH'))
            $this->warning = (isset($this->warning) && !empty($this->warning) ? $this->warning . ',<br/> ' : '') . $this->l('You need to configure the auth token');
        if ($this->id && !Configuration::get(PiwikHelper::CPREFIX . 'HOST'))
            $this->warning = (isset($this->warning) && !empty($this->warning) ? $this->warning . ',<br/> ' : '') . $this->l('You need to configure the Piwik server url');
        
    }
    public function getIdentifier() {
        return $this->identifier;
    }

    public function getContent() {
        header('Location: index.php?controller=PiwikAnalyticsSiteManager&token=' .  Tools::getAdminTokenLite('PiwikAnalyticsSiteManager'));
        exit;
    }

    public function disable($force_all = false) {
        // check if module piwikanalytics is installed
        if (Module::isInstalled('piwikanalytics')) {
            $this->_errors[] = Tools::displayError(sprintf($this->l('Can not disable  %s, Module \'piwikanalytics\' depends on this module'), $this->displayName));
            return false;
        }

        // check if module piwikdashboard is installed
        if (Module::isInstalled('piwikdashboard')) {
            $this->_errors[] = Tools::displayError(sprintf($this->l('Can not disable  %s, Module \'piwikdashboard\' depends on this module'), $this->displayName));
            return false;
        }
        return parent::disable($force_all);
    }

    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        foreach ($this->_default_config_values as $key => $value) {
            /* only set if not isset, compatibility with module 'piwikanalyticsjs' */
            if (Configuration::getGlobalValue($key) === false)
                Configuration::updateGlobalValue($key, $value);
        }
        return parent::install() &&
                $this->installTabs() &&
                $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall() {

        // check if module piwikanalytics is installed
        if (Module::isInstalled('piwikanalytics')) {
            $this->_errors[] = Tools::displayError($this->l('Can not uninstall ' . $this->displayName . ", Module 'piwikanalytics' depends on this module"));
            return false;
        }

        // check if module piwikdashboard is installed
        if (Module::isInstalled('piwikdashboard')) {
            $this->_errors[] = Tools::displayError($this->l('Can not uninstall ' . $this->displayName . ", Module 'piwikdashboard' depends on this module"));
            return false;
        }

        if (!parent::uninstall()) {
            return false;
        }
        
        foreach ($this->_default_config_values as $key => $value) {
            Configuration::deleteByName($key);
        }

        // Tabs
        $idTabs = array();
        $idTabs[] = (int) Tab::getIdFromClassName('PiwikAnalytics');
        $idTabs[] = (int) Tab::getIdFromClassName('PiwikAnalyticsSiteManager');
        foreach ($idTabs as $idTab) {
            if ($idTab > 0) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }
        return TRUE;
    }

    public function hookDisplayBackOfficeHeader() {
        if (method_exists($this->context->controller, 'addCSS'))
            $this->context->controller->addCSS($this->_path . 'css/admin.css', 'all');
    }

    private function installTabs() {
        // Parent
        $parent_tab = new Tab();
        $parent_tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $parent_tab->name[(int) $lang['id_lang']] = $this->l('Piwik Analytics');
        $parent_tab->class_name = 'PiwikAnalytics';
        $parent_tab->id_parent = 0;
        $parent_tab->module = $this->name;
        $parent_tab->add();


        // Manager
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = $this->l('Site Manager');

        $tab->class_name = 'PiwikAnalyticsSiteManager';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();

        return true;
    }

}
