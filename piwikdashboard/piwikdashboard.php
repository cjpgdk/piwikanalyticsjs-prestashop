<?php

if (!defined('_PS_VERSION_'))
    exit;

/*
 * Copyright (C) 2015 Christian Jensen
 *
 * This file is part of piwikdashboard for prestashop.
 * 
 * piwikdashboard for prestashop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * piwikdashboard for prestashop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with piwikdashboard for prestashop.  If not, see <http://www.gnu.org/licenses/>.
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

class piwikdashboard extends Module {

    public function __construct($name = null, $context = null) {

        $this->dependencies[] = "piwikmanager";
        $this->dependencies[] = "piwikanalytics";

        $this->name = 'piwikdashboard';
        $this->tab = 'analytics_stats';
        $this->version = '1b';
        $this->author = 'Christian M. Jensen';
        $this->displayName = 'Piwik Analytics Dashboard';
        $this->bootstrap = true;
        if (_PS_VERSION_ < '1.5')
            parent::__construct($name);
        /* Prestashop 1.5 and up implements "$context" */
        if (_PS_VERSION_ >= '1.5')
            parent::__construct($name, ($context instanceof Context ? $context : NULL));

        $this->description = $this->l('Adds Piwik Analytics Dashboard in admin under stats');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this plugin ?');

        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            if (version_compare(_PS_VERSION_, '1.4.5.1', '<=')) {
                include _PS_ROOT_DIR_ . '/modules/piwikmanager/_classes/backward_compatibility/backward.php';
            } else {
                require dirname(__FILE__) . '/../piwikmanager/_classes/backward_compatibility/backward.php';
            }
        }
    }

    /**
     * hook into admin stats page on prestashop version 1.4
     * @param array $params
     * @return string
     */
    public function hookAdminStatsModules($params) {
        $http = ((bool) Configuration::get(PKHelper::CPREFIX . 'CRHTTPS') ? 'https://' : 'http://');
        $PIWIK_HOST = Configuration::get(PKHelper::CPREFIX . 'HOST');
        $PIWIK_SITEID = (int) Configuration::get(PKHelper::CPREFIX . 'SITEID');
        $PIWIK_TOKEN_AUTH = Configuration::get(PKHelper::CPREFIX . 'TOKEN_AUTH');
        if ((empty($PIWIK_HOST) || $PIWIK_HOST === FALSE) || ($PIWIK_SITEID <= 0 || $PIWIK_SITEID === FALSE) || (empty($PIWIK_TOKEN_AUTH) || $PIWIK_TOKEN_AUTH === FALSE))
            return "<h3>{$this->l("You need to set 'Piwik host url', 'Piwik token auth' and 'Piwik site id', and save them before the dashboard can be shown here")}</h3>";
        $lng = new Language($params['cookie']->id_lang);

        $user = Configuration::get(PKHelper::CPREFIX . 'USRNAME');
        $passwd = Configuration::get(PKHelper::CPREFIX . 'USRPASSWD');
        if ((!empty($user) && $user !== FALSE) && (!empty($passwd) && $passwd !== FALSE))
            $PKUILINK = $http . $PIWIK_HOST . 'index.php?module=Login&action=logme&login=' . $user . '&password=' . md5($passwd) . '&idSite=' . $PIWIK_SITEID;
        else
            $PKUILINK = $http . $PIWIK_HOST . 'index.php';

        $DREPDATE = Configuration::get(PKHelper::CPREFIX . 'DREPDATE');
        if ($DREPDATE !== FALSE && (strpos($DREPDATE, '|') !== FALSE)) {
            list($period, $date) = explode('|', $DREPDATE);
        } else {
            $period = "day";
            $date = "today";
        }

        $html = '<script type="text/javascript">function WidgetizeiframeDashboardLoaded() {var w = $(\'#content\').width();var h = $(\'body\').height();$(\'#WidgetizeiframeDashboard\').width(\'100%\');$(\'#WidgetizeiframeDashboard\').height(h);}</script>'
                . '<fieldset class="width3">'
                . '<legend><img src="../modules/' . $this->name . '/logo.gif" /> ' . $this->displayName . ''
                . ' | <a target="_blank" href="' . $PKUILINK . '">' . $this->l('Piwik') . '</a>'
                . ' | <a target="_blank" href="https://github.com/cmjnisse/piwikanalytics-prestashop/wiki">' . $this->l('Help') . '</a>'
                . '</legend>'
                . '<iframe id="WidgetizeiframeDashboard"  onload="WidgetizeiframeDashboardLoaded();" '
                . 'src="' . $http . $PIWIK_HOST . 'index.php'
                . '?module=Widgetize'
                . '&action=iframe'
                . '&moduleToWidgetize=Dashboard'
                . '&actionToWidgetize=index'
                . '&idSite=' . $PIWIK_SITEID
                . '&period=' . $period
                . '&language=' . $lng->iso_code
                . '&token_auth=' . $PIWIK_TOKEN_AUTH
                . '&date=' . $date
                . '" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="550px"></iframe>'
                . '</fieldset>';
        return $html;
    }

    /**
     * Install the module
     * @return boolean false on install error
     */
    public function install() {
        if (_PS_VERSION_ >= '1.5') {
            /* create complete new page tab */
            $tab = new Tab();
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = 'Piwik Dashboard';
            }
            $tab->module = 'piwikdashboard';
            $tab->active = TRUE;
            $tab->class_name = 'PiwikAnalyticsDashboard';
            $AdminParentStats = Tab::getInstanceFromClassName('AdminParentStats');
            $tab->id_parent = ($AdminParentStats instanceof Tab ? $AdminParentStats->id : -1);
            if ($tab->add())
                Configuration::updateValue(PKHelper::CPREFIX . 'TAPID', (int) $tab->id);
        }
        if (_PS_VERSION_ < '1.5') {
            return (parent::install() && $this->registerHook('AdminStatsModules'));
        } else if (_PS_VERSION_ >= '1.5') {
            return (parent::install());
        }
    }

    /**
     * Uninstall the module
     * @return boolean false on uninstall error
     */
    public function uninstall() {
        if (parent::uninstall()) {
            try {
                if (_PS_VERSION_ >= '1.5') {
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
