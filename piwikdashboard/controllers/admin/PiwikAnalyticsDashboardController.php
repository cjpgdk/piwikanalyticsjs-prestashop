<?php

/*
 * Copyright (C) 2014 Christian Jensen
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
if (!defined('_PS_VERSION_'))
    exit;

include dirname(__FILE__) . '/../../../piwikmanager/PKClassLoader.php';
PKClassLoader::LoadStatic(array('PiwikHelper'));
PiwikHelper::initialize();

if (!class_exists('PiwikDashboardHelper', false)) {
    include(dirname(__FILE__) . '/../../PiwikDashboardHelper.php');
    PiwikDashboardHelper::initialize();
}

class PiwikAnalyticsDashboardController extends ModuleAdminController {

    public $name = "PiwikAnalyticsDashboard";

    public function init() {
        $this->processSubmitConfiguration();

        parent::init();

        $this->bootstrap = true;
        if (Tools::isSubmit('edit' . $this->name)) {
            $this->action = $this->display = 'edit';
        } else {
            $this->action = $this->display = 'view';
        }
        $this->show_page_header_toolbar = true;
    }

    public function initContent() {
        // init settings, edit
        if (!$this->ajax && $this->display == 'edit') {
            $this->toolbar_title = $this->l('Piwik Dashboard Settings', $this->name);
        }
        // init dashboard view
        if (!$this->ajax && $this->display == 'view') {
            
            $this->addCSS($this->module->getPathUri() . 'css/admin.css');
            
            $this->toolbar_title = $this->l('Piwik Dashboard', $this->name);

            $this->tpl_view_vars['protocol'] = ((bool) Configuration::get(PiwikHelper::CPREFIX . 'CRHTTPS') ? 'https://' : 'http://');

            $this->tpl_view_vars['piwik_host'] = Configuration::get(PiwikHelper::CPREFIX . 'HOST');
            $this->tpl_view_vars['piwik_siteid'] = (int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID');
            $this->tpl_view_vars['piwik_token'] = Configuration::get(PiwikHelper::CPREFIX . 'TOKEN_AUTH');

            $this->tpl_view_vars['piwik_currency_prefix'] = '';
            $this->tpl_view_vars['piwik_currency_suffix'] = '';
            $this->tpl_view_vars['piwik_currency_sign'] = '';
            $this->tpl_view_vars['piwik_currency'] = Configuration::get(PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY');
            $currency = new Currency(Currency::getIdByIsoCode($this->tpl_view_vars['piwik_currency']));
            if (Validate::isLoadedObject($currency)) {
                $this->tpl_view_vars['piwik_currency_prefix'] = $currency->prefix;
                $this->tpl_view_vars['piwik_currency_suffix'] = $currency->suffix;
                $this->tpl_view_vars['piwik_currency_sign'] = $currency->sign;
            }

            $DREPDATE = Configuration::get(PiwikHelper::CPREFIX . 'DREPDATE');
            if ($DREPDATE !== FALSE && (strpos($DREPDATE, '|') !== FALSE)) {
                list($period, $date) = explode('|', $DREPDATE);
            } else {
                $period = "day";
                $date = "today";
            }
            $this->tpl_view_vars['piwik_reportdate'] = $period . '|' . $date;
            $this->tpl_view_vars['piwik_date'] = $date;
            $this->tpl_view_vars['piwik_period'] = $period;
            $lng = new Language($this->context->cookie->id_lang);
            $this->tpl_view_vars['iso_code'] = $lng->iso_code;

            // http Auth
            $this->tpl_view_vars['piwik_http_auth'] = $this->tpl_view_vars['piwik_username'] = $this->tpl_view_vars['piwik_password'] = '';
            $this->tpl_view_vars['AuthorizationHeaders'] = "";
            $httpAuthUsername = Configuration::get(PiwikHelper::CPREFIX . 'PAUTHUSR');
            $httpAuthPassword = Configuration::get(PiwikHelper::CPREFIX . 'PAUTHPWD');
            if (
                    ($httpAuthUsername !== false && !empty($httpAuthUsername)) &&
                    ($httpAuthPassword !== false && !empty($httpAuthPassword))
            ) {
                $this->tpl_view_vars['piwik_http_auth'] = $httpAuthUsername . ':' . $httpAuthPassword . '@';
                $this->tpl_view_vars['piwik_username'] = $httpAuthUsername;
                $this->tpl_view_vars['piwik_password'] = $httpAuthPassword;
                $this->tpl_view_vars['AuthorizationHeaders'] = "\"Authorization\": \"Basic " . base64_encode($httpAuthUsername . ':' . $httpAuthPassword) . "\"";
            }

            $this->tpl_view_vars['piwik_dashboard_controller_link'] = Context::getContext()->link->getAdminLink($this->name);
            $this->tpl_view_vars['piwik_customers_controller_link'] = Context::getContext()->link->getAdminLink('AdminCustomers');


            $user = Configuration::get(PiwikHelper::CPREFIX . 'USRNAME');
            $passwd = Configuration::get(PiwikHelper::CPREFIX . 'USRPASSWD');

            if ((!empty($user) && $user !== FALSE) && (!empty($passwd) && $passwd !== FALSE)) {
                $this->page_header_toolbar_btn['stats'] = array(
                    'href' => $this->tpl_view_vars['protocol'] . $this->tpl_view_vars['piwik_host'] . 'index.php?module=Login&action=logme&login=' . $user . '&password=' . md5($passwd) . '&idSite=' . $this->tpl_view_vars['piwik_siteid'],
                    'desc' => $this->l('Piwik'),
                    'target' => true
                );
            } else {
                $this->page_header_toolbar_btn['stats'] = array(
                    'href' => $this->tpl_view_vars['protocol'] . $this->tpl_view_vars['piwik_host'] . 'index.php',
                    'desc' => $this->l('Piwik'),
                    'target' => true
                );
            }
            $this->page_header_toolbar_btn['settings'] = array(
                'href' => Context::getContext()->link->getAdminLink($this->name) . '&edit' . $this->name,
                'desc' => $this->l('Settings'),
                'icon' => 'process-icon-configure',
            );
        }

        parent::initContent();

        $this->context->smarty->assign('help_link', 'https://github.com/cmjnisse/piwikanalyticsjs-prestashop/wiki');
    }

    public function processSubmitConfiguration() {
        if (Tools::isSubmit('submitAddconfiguration')) {

            // default report date
            $drdate = Tools::getValue(PiwikHelper::CPREFIX . 'DREPDATE');
            if ($drdate !== false && !empty($drdate)) {
                if (strpos($drdate, '|') !== false) {
                    Configuration::updateValue(PiwikHelper::CPREFIX . 'DREPDATE', $drdate);
                } else {
                    Configuration::updateValue(PiwikHelper::CPREFIX . 'DREPDATE', 'day|today');
                }
            }

            // view
            $dview = Tools::getValue(PiwikHelper::CPREFIX . 'DEFAULTVIEW');
            if ($dview !== false && !empty($dview)) {
                Configuration::updateValue(PiwikHelper::CPREFIX . 'DEFAULTVIEW', $dview);
            } else {
                Configuration::updateValue(PiwikHelper::CPREFIX . 'DEFAULTVIEW', 'iframe');
            }

            // user name
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'USRNAME')) {
                Configuration::updateValue(PiwikHelper::CPREFIX . 'USRNAME', Tools::getValue(PiwikHelper::CPREFIX . 'USRNAME'));
            }
            // password
            if (Tools::getIsset(PiwikHelper::CPREFIX . 'USRPASSWD')) {
                Configuration::updateValue(PiwikHelper::CPREFIX . 'USRPASSWD', Tools::getValue(PiwikHelper::CPREFIX . 'USRPASSWD'));
            }
        }
    }

    public function renderForm() {
        $this->multiple_fieldsets = true;

        $this->fields_value = array(
            PiwikHelper::CPREFIX . 'DREPDATE' => Configuration::get(PiwikHelper::CPREFIX . 'DREPDATE'),
            PiwikHelper::CPREFIX . 'USRNAME' => Configuration::get(PiwikHelper::CPREFIX . 'USRNAME'),
            PiwikHelper::CPREFIX . 'USRPASSWD' => Configuration::get(PiwikHelper::CPREFIX . 'USRPASSWD'),
            PiwikHelper::CPREFIX . 'DEFAULTVIEW' => Configuration::get(PiwikHelper::CPREFIX . 'DEFAULTVIEW'),
        );
        $views_dir = _PS_MODULE_DIR_ . 'piwikdashboard/views/templates/admin/_configure/helpers/view';
        $views = array();
        if ($handle = opendir($views_dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && (substr($entry, -4) == ".tpl") && substr($entry, 0, -4) != 'iframe') {
                    $views[] = array('value' => substr($entry, 0, -4), 'label' => substr($entry, 0, -4));
                }
            }
            closedir($handle);
        }

        $this->fields_form[0] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Piwik Dashboard Settings', $this->name),
                    'image' => $this->module->getPathUri() . 'logox22.png'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Piwik Report date', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'DREPDATE',
                        'desc' => $this->l('Report date to load by default from \'Piwik Analytics => Dashboard\'', $this->name),
                        'options' => array(
                            'default' => array('value' => 'day|today', 'label' => $this->l('Today', $this->name)),
                            'query' => array(
                                /* array('value' => 'day|today', 'label' => $this->l('Today', $this->name)), */
                                array('value' => 'day|yesterday', 'label' => $this->l('Yesterday', $this->name)),
                                array('value' => 'range|previous7', 'label' => $this->l('Previous 7 days (not including today)', $this->name)),
                                array('value' => 'range|previous30', 'label' => $this->l('Previous 30 days (not including today)', $this->name)),
                                array('value' => 'range|last7', 'label' => $this->l('Last 7 days (including today)', $this->name)),
                                array('value' => 'range|last30', 'label' => $this->l('Last 30 days (including today)', $this->name)),
                                array('value' => 'week|today', 'label' => $this->l('Current Week', $this->name)),
                                array('value' => 'month|today', 'label' => $this->l('Current Month', $this->name)),
                                array('value' => 'year|today', 'label' => $this->l('Current Year', $this->name)),
                            ),
                            'id' => 'value',
                            'name' => 'label'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Dashbord view', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'DEFAULTVIEW',
                        'options' => array(
                            'default' => array('value' => 'iframe', 'label' => 'iframe'),
                            'query' => $views,
                            'id' => 'value',
                            'name' => 'label'
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Piwik User name', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'USRNAME',
                        'desc' => $this->l('You can store your Username for Piwik here to make it easy to open piwik interface from the dashboard page with automatic login', $this->name),
                        'required' => false,
                        'autocomplete' => false,
                    ),
                    array(
                        'type' => 'password',
                        'label' => $this->l('Piwik User password', $this->name),
                        'name' => PiwikHelper::CPREFIX . 'USRPASSWD',
                        'desc' => $this->l('You can store your Password for Piwik here to make it easy to open piwik interface from the dashboard page with automatic login', $this->name),
                        'required' => false,
                        'autocomplete' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save', $this->name),
                    'class' => 'btn btn-default'
                ),
            ),
        );

        return parent::renderForm();
    }

    public function renderView() {

        $helper = new HelperView();

        $this->table = $this->name;
        $this->className = "";
        $this->show_toolbar = true;
        $this->toolbar_scroll = false;
        $this->toolbar_btn = null;
        $this->tpl_folder = null;
        $this->actions = null;
        $this->list_simple_header = true;
        $this->bulk_actions = array();
        $this->list_no_link = false;

        if ((isset($this->_languages) && empty($this->_languages)) || (!isset($this->default_form_language) || $this->default_form_language === FALSE)) {
            $this->getLanguages();
        }


        $this->setHelperDisplay($helper);
        // stop the complaining
        $helper->identifier = "id_module";
        $helper->module = $this->module;

        $helper->tpl_vars = $this->getTemplateViewVars();
        $helper->base_tpl = Configuration::get(PiwikHelper::CPREFIX . 'DEFAULTVIEW') . '.tpl';
        $view = $helper->generateView();

        return $view;
    }

    // print visit details from piwik api "Live.getLastVisitsDetails"
    public function ajaxProcessgetlastvisitsdetails() {
        $DREPDATE = Configuration::get(PiwikHelper::CPREFIX . 'DREPDATE');
        if ($DREPDATE !== FALSE && (strpos($DREPDATE, '|') !== FALSE)) {
            list($period, $date) = explode('|', $DREPDATE);
        } else {
            $period = "day";
            $date = "today";
        }
        // get overide.
        if (Tools::getIsset('date')) {
            $date = Tools::getValue('date');
        }
        if (Tools::getIsset('period')) {
            $period = Tools::getValue('period');
        }
        $countVisitorsToFetch = 10;
        if (Tools::getIsset('countVisitorsToFetch')) {
            $countVisitorsToFetch = Tools::getValue('countVisitorsToFetch');
        }
        $idSite = (int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID');
        $result = PiwikDashboardHelper::getLastVisitsDetails($idSite, $period, $date, '', $countVisitorsToFetch);
        foreach ($result as $key => $value) {
            if (isset($value->visitIp)) {
                $result[$key]->visitIpHost = gethostbyaddr($value->visitIp);
            }
        }
        die(Tools::jsonEncode($result));
        
//        $result = file_get_contents(((bool) Configuration::get(PiwikHelper::CPREFIX . 'CRHTTPS') ? 'https://' : 'http://').Configuration::get(PiwikHelper::CPREFIX . 'HOST').'index.php?date='.$date.'&module=Live&action=getLastVisitsStart&segment=&idSite='.$idSite.'&period='.$period.'&token_auth='.Configuration::get(PiwikHelper::CPREFIX . 'TOKEN_AUTH'));
//        $result = str_replace('plugins/Live/images/', ((bool) Configuration::get(PiwikHelper::CPREFIX . 'CRHTTPS') ? 'https://' : 'http://').Configuration::get(PiwikHelper::CPREFIX . 'HOST').'plugins/Live/images/', $result);
//        $result = str_replace('plugins/UserCountry/images/', ((bool) Configuration::get(PiwikHelper::CPREFIX . 'CRHTTPS') ? 'https://' : 'http://').Configuration::get(PiwikHelper::CPREFIX . 'HOST').'plugins/UserCountry/images/', $result);
//        $result = str_replace('plugins/DevicesDetection/images/', ((bool) Configuration::get(PiwikHelper::CPREFIX . 'CRHTTPS') ? 'https://' : 'http://').Configuration::get(PiwikHelper::CPREFIX . 'HOST').'plugins/DevicesDetection/images/', $result);
//        $result = str_replace('plugins/Morpheus/images/', ((bool) Configuration::get(PiwikHelper::CPREFIX . 'CRHTTPS') ? 'https://' : 'http://').Configuration::get(PiwikHelper::CPREFIX . 'HOST').'plugins/Morpheus/images/', $result);
//        $result = str_replace(array(
//            '<ul id=\'visitsLive\'>', '</ul>',
//            '<script type="text/javascript">',
//            '$(\'#visitsLive\').on(\'click\', \'.visits-live-launch-visitor-profile\', function (e) {',
//            'e.preventDefault();', 'return false;', '});', '</script>',
//            'broadcast.propagateNewPopoverParameter(\'visitorProfile\', $(this).attr(\'data-visitor-id\'));',
//        ), '', $result);
//        die($result);
    }

    // print actions from piwik api 'Actions.getsitesearchnoresultkeywords'
    public function ajaxProcessgetsitesearchnoresultkeywords() {
        $DREPDATE = Configuration::get(PiwikHelper::CPREFIX . 'DREPDATE');
        if ($DREPDATE !== FALSE && (strpos($DREPDATE, '|') !== FALSE)) {
            list($period, $date) = explode('|', $DREPDATE);
        } else {
            $period = "day";
            $date = "today";
        }
        // get overide.
        if (Tools::getIsset('date')) {
            $date = Tools::getValue('date');
        }
        if (Tools::getIsset('period')) {
            $period = Tools::getValue('period');
        }
        $idSite = (int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID');
        $result = PiwikDashboardHelper::getSiteSearchNoResultKeywords($idSite, $period, $date);
        die(Tools::jsonEncode($result));
    }

    // print actions from piwik api 'Actions.get'
    public function ajaxProcessgetactions() {

        $DREPDATE = Configuration::get(PiwikHelper::CPREFIX . 'DREPDATE');
        if ($DREPDATE !== FALSE && (strpos($DREPDATE, '|') !== FALSE)) {
            list($period, $date) = explode('|', $DREPDATE);
        } else {
            $period = "day";
            $date = "today";
        }
        // get overide.
        if (Tools::getIsset('date')) {
            $date = Tools::getValue('date');
        }
        if (Tools::getIsset('period')) {
            $period = Tools::getValue('period');
        }


        $idSite = (int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID');
        $result = PiwikDashboardHelper::getActions($idSite, $period, $date);
        die(Tools::jsonEncode($result));
    }

    // print Live Counters from piwik api "Live.getLiveCounters"
    public function ajaxProcessgetlivecounters() {
        $lastMinutes = 5;
        if (Tools::getIsset('lastMinutes')) {
            $lastMinutes = (int) Tools::getValue('lastMinutes');
        }
        if ($lastMinutes <= 0) {
            $lastMinutes = 5;
        }
        $liveCounters = PiwikDashboardHelper::getLiveCounters((int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID'), $lastMinutes);
        die(Tools::jsonEncode($liveCounters));
    }

}
