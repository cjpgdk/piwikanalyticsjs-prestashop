<?php

if (!defined('_PS_VERSION_'))
    exit;

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
 * 
 * @todo config wiz, set currency to use.
 */
class piwikanalyticsjs extends Module {

    private static $_isOrder = FALSE;
    protected $_errors = "";
    protected $piwikSite = FALSE;
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
        $this->name = 'piwikanalyticsjs';
        $this->tab = 'analytics_stats';
        $this->version = '0.8.3';
        $this->author = 'Christian M. Jensen';
        $this->displayName = 'Piwik Analytics';
        $this->author_uri = 'https://cmjscripter.net';
        $this->url = 'http://cmjnisse.github.io/piwikanalyticsjs-prestashop/';
        $this->need_instance = 1;

        /* version 1.5 uses invalid compatibility check */
        if (version_compare(_PS_VERSION_, '1.6.0.0', '>='))
            $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);

        $this->bootstrap = true;

        parent::__construct($name, ($context instanceof Context ? $context : NULL));

        require_once dirname(__FILE__) . '/PKHelper.php';

        //* warnings on module list page
        if ($this->id && !Configuration::get(PKHelper::CPREFIX . 'TOKEN_AUTH'))
            $this->warning = (isset($this->warning) && !empty($this->warning) ? $this->warning . ',<br/> ' : '') . $this->l('You need to configure the auth token');
        if ($this->id && ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0))
            $this->warning = (isset($this->warning) && !empty($this->warning) ? $this->warning . ',<br/> ' : '') . $this->l('You have not yet set Piwik Site ID');
        if ($this->id && !Configuration::get(PKHelper::CPREFIX . 'HOST'))
            $this->warning = (isset($this->warning) && !empty($this->warning) ? $this->warning . ',<br/> ' : '') . $this->l('You need to configure the Piwik server url');

        $this->description = $this->l('Piwik Analytics');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this plugin ?');

        self::$_isOrder = FALSE;
        PKHelper::$error = "";
        $this->_errors = PKHelper::$errors = array();

        if ($this->id) {
            if (version_compare(_PS_VERSION_, '1.5.0.13', "<="))
                PKHelper::$_module = & $this;
        }
    }

    public function generateWizardForm($step = 1, & $fields_form, & $helperform) {
        $this->__wizardDefaults();

        PiwikWizardHelper::setUsePiwikSite($helperform);
        PiwikWizardHelper::getFormValuesInternal();
        // get and set errors "PiwikWizardHelper::$errors"
        foreach (PiwikWizardHelper::$errors as $value)
            $this->_errors[] = $value;
        PiwikWizardHelper::$errors = array();

        $pkToken = false;
        $pkSites = array();

        // use step 2 ??
        PiwikWizardHelper::pkws2($step, $pkToken, $pkSites, $fields_form, $helperform);
        // get and set errors "PiwikWizardHelper::$errors"
        foreach (PiwikWizardHelper::$errors as $value)
            $this->_errors[] = $this->displayError($value);
        PiwikWizardHelper::$errors = array();

        $fields_form[0]['form']['input'][] = array(
            'type' => 'html',
            'name' => "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'STEP_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'STEP_WIZARD' . "\" value=\"{$step}\" />"
        );
        $fields_form[0]['form']['legend']['title'] = $helperform->title . " " . sprintf($this->l("[Step %s/2]"), $step);
        if ($step != 1) {
            $fields_form[0]['form']['input'][] = array(
                'type' => 'html',
                'name' => "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'HOST_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'HOST_WIZARD' . "\" value=\"" . PiwikWizardHelper::$piwikhost . "\" />"
                . "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'USRNAME_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'USRNAME_WIZARD' . "\" value=\"" . PiwikWizardHelper::$username . "\" />"
                . "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'USRPASSWD_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'USRPASSWD_WIZARD' . "\" value=\"" . PiwikWizardHelper::$password . "\" />"
                . "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'PAUTHUSR_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'PAUTHUSR_WIZARD' . "\" value=\"" . PiwikWizardHelper::$usernamehttp . "\" />"
                . "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'PAUTHPWD_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'PAUTHPWD_WIZARD' . "\" value=\"" . PiwikWizardHelper::$passwordhttp . "\" />"
            );
        }
        // use step 1 ??
        PiwikWizardHelper::pkws1($step, $fields_form, $helperform);

        $helperform->show_cancel_button = false; // link is wrong and can end up in an annoying loop
        $helperform->fields_value = array(
            PKHelper::CPREFIX . 'USRNAME_WIZARD' => (PiwikWizardHelper::$username !== false ? PiwikWizardHelper::$username : ''),
            PKHelper::CPREFIX . 'USRPASSWD_WIZARD' => (PiwikWizardHelper::$password !== false ? PiwikWizardHelper::$password : ''),
            PKHelper::CPREFIX . 'HOST_WIZARD' => (PiwikWizardHelper::$piwikhost !== false ? PiwikWizardHelper::$piwikhost : ''),
            PKHelper::CPREFIX . 'SAVE_USRPWD_WIZARD' => 0,
            PKHelper::CPREFIX . 'PAUTHUSR_WIZARD' => (PiwikWizardHelper::$usernamehttp !== false ? PiwikWizardHelper::$usernamehttp : ''),
            PKHelper::CPREFIX . 'PAUTHPWD_WIZARD' => (PiwikWizardHelper::$passwordhttp !== false ? PiwikWizardHelper::$passwordhttp : ''),
        );
    }

    public function generateCreateNewSiteForm($step, & $fields_form, & $helperform) {

        $this->__wizardDefaults();

        PKHelper::$httpAuthUsername = (PiwikWizardHelper::$usernamehttp !== false ? PiwikWizardHelper::$usernamehttp : '');
        PKHelper::$httpAuthPassword = (PiwikWizardHelper::$passwordhttp !== false ? PiwikWizardHelper::$passwordhttp : '');
        PKHelper::$piwikHost = str_replace(array('http://', 'https://'), '', PiwikWizardHelper::$piwikhost);
        $pkToken = PKHelper::getTokenAuth(PiwikWizardHelper::$username, PiwikWizardHelper::$password);

        $helperform->submit_action = 'submitPKNewSiteForm' . $this->name;
        $fields_form[0]['form']['input'][] = array(
            'type' => 'html',
            'name' => "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'STEP_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'STEP_WIZARD' . "\" value=\"{$step}\" />"
        );
        $fields_form[0]['form']['legend']['title'] = $helperform->title . " " . sprintf($this->l("[Step %s/2]"), $step);

        $fields_form[0]['form']['input'][] = array(
            'type' => 'html',
            'name' => "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'HOST_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'HOST_WIZARD' . "\" value=\"" . PiwikWizardHelper::$piwikhost . "\" />"
            . "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'USRNAME_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'USRNAME_WIZARD' . "\" value=\"" . PiwikWizardHelper::$username . "\" />"
            . "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'USRPASSWD_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'USRPASSWD_WIZARD' . "\" value=\"" . PiwikWizardHelper::$password . "\" />"
            . "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'PAUTHUSR_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'PAUTHUSR_WIZARD' . "\" value=\"" . PiwikWizardHelper::$usernamehttp . "\" />"
            . "<input type=\"hidden\" name=\"" . PKHelper::CPREFIX . 'PAUTHPWD_WIZARD' . "\" id=\"" . PKHelper::CPREFIX . 'PAUTHPWD_WIZARD' . "\" value=\"" . PiwikWizardHelper::$passwordhttp . "\" />"
        );

        if (!empty(PKHelper::$error) || ($pkToken === false)) {
            foreach (PKHelper::$errors as $value)
                $this->_errors[] = $this->displayError($value);
            $fields_form[0]['form']['input'][] = array('type' => 'html', 'name' => "<strong>" . str_replace(array('{link}', '{/link}'), array('<a href="' . AdminController::$currentIndex . '&configure=' . $this->name . '&pkwizard&token=' . Tools::getAdminTokenLite('AdminModules') . '">', '</a>'), $this->l('can\'t continue see above errors.. {link}Back to previous page{/link}')) . "</strong>");
            if ($pkToken === false)
                $fields_form[0]['form']['input'][] = array('type' => 'html', 'name' => "<strong>" . str_replace(array('{link}', '{/link}'), array('<a href="' . AdminController::$currentIndex . '&configure=' . $this->name . '&pkwizard&token=' . Tools::getAdminTokenLite('AdminModules') . '">', '</a>'), $this->l('Unable to get your auth token please check your username and password and try again.. {link}Back to previous page{/link}')) . "</strong>");
        } else {
            PiwikWizardHelper::pkns($fields_form, $helperform, $this->currencies, $this->default_currency);
        }
    }
    
    /**
     * get content to display in the admin area
     * @return string
     */
    public function getContent() {
        if (Tools::getIsset('pkapicall')) {
            $this->__pkapicall();
            die();
        }

        if (version_compare(_PS_VERSION_, '1.5.0.4', "<=")) {
            $this->context->controller->addJquery(_PS_JQUERY_VERSION_);
            $this->context->controller->addJs($this->_path . 'js/jquery.alerts.js');
            $this->context->controller->addCss($this->_path . 'js/jquery.alerts.css');
        }

        if (version_compare(_PS_VERSION_, '1.5.2.999', "<="))
            $this->context->controller->addJqueryPlugin('fancybox', _PS_JS_DIR_ . 'jquery/plugins/');

        if (version_compare(_PS_VERSION_, '1.6', "<")) {
            $this->context->controller->addJqueryUI(array(
                'ui.core',
                'ui.widget',
            ));
        }
        if (version_compare(_PS_VERSION_, '1.5', ">="))
            $this->context->controller->addJqueryPlugin('tagify', _PS_JS_DIR_ . 'jquery/plugins/');


        $_html = "";
        $this->processFormsUpdate();
        if (Tools::isSubmit('submitUpdateWizardForm' . $this->name)) {
            $this->processWizardFormUpdate();
        }
        $this->piwikSite = false;
        if (!$this->isWizardRequest()) { /* do not try to connect when using wizard */
            if (Configuration::get(PKHelper::CPREFIX . 'TOKEN_AUTH') !== false)
                $this->piwikSite = PKHelper::getPiwikSite();
        }
        $this->displayErrors(PKHelper::$errors);
        PKHelper::$errors = PKHelper::$error = "";
        $this->__setCurrencies();

        //* warnings on module configure page
        if (!$this->isWizardRequest()) { /* do not show them if we are using the  wizard */
            if ($this->id && !Configuration::get(PKHelper::CPREFIX . 'TOKEN_AUTH') && !Tools::getIsset(PKHelper::CPREFIX . 'TOKEN_AUTH')) /* avoid the same error message twice */
                $this->_errors[] = $this->displayError($this->l('Piwik auth token is empty'));
            if ($this->id && ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0) && !Tools::getIsset(PKHelper::CPREFIX . 'SITEID')) /* avoid the same error message twice */
                $this->_errors[] = $this->displayError($this->l('Piwik site id is lower or equal to "0"'));
            if ($this->id && !Configuration::get(PKHelper::CPREFIX . 'HOST'))
                $this->_errors[] = $this->displayError($this->l('Piwik host cannot be empty'));
        }

        $fields_form = array();

        $languages = Language::getLanguages(FALSE);
        foreach ($languages as $languages_key => $languages_value) {
            $languages[$languages_key]['is_default'] = ($languages_value['id_lang'] == (int) Configuration::get('PS_LANG_DEFAULT') ? true : false);
        }
        $helper = new HelperForm();
        if (version_compare(_PS_VERSION_, '1.5.0.13', "<"))
            $helper->base_folder = _PS_MODULE_DIR_ . 'piwikanalyticsjs/views/templates/helpers/form/';

        $helper->languages = $languages;
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;

        $fields_form[0]['form']['legend'] = array(
            'title' => $this->displayName,
            'image' => $this->_path . 'logox22.png'
        );

        if ($this->isWizardRequest()) {
            require_once dirname(__FILE__) . '/PiwikWizard.php';
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&pkwizard';
            $step = 1;
            if (empty($this->_errors) && empty($_html)) {
                if (Tools::getIsset(PKHelper::CPREFIX . 'STEP_WIZARD')) {
                    $step = Tools::getValue(PKHelper::CPREFIX . 'STEP_WIZARD', 0);
                    $step++;
                }
            }
            $helper->title = $this->displayName . ' - ' . $this->l('Configuration Wizard');
            $helper->submit_action = 'submitUpdateWizardForm' . $this->name;

            if (Tools::isSubmit('createnewsite'))
                $this->generateCreateNewSiteForm($step, $fields_form, $helper);
            else
                $this->generateWizardForm($step, $fields_form, $helper);

            $this->context->smarty->assign(array(
                'psversion' => _PS_VERSION_,
                'hf_currentIndex' => $helper->currentIndex,
                'pstoken' => Tools::getAdminTokenLite('AdminModules'),
            ));
            if (is_array($this->_errors))
                $_html = implode('', $this->_errors) . $_html;
            else
                $_html = $this->_errors . $_html;
            return $_html
                    . $helper->generateForm($fields_form)
                    . $this->display(__FILE__, 'views/templates/admin/jsfunctions.tpl')
                    . $this->display(__FILE__, 'views/templates/admin/piwik_site_lookup.tpl');
        }


        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitUpdate' . $this->name;


        if ($this->piwikSite !== FALSE) {
            $fields_form[0]['form']['input'][] = array(
                'type' => 'html',
                'name' => "<div class=\"nav nav-pills pull-right\">"
                . "<i class=\"icon-wrench\" style=\"padding-right: 5px;\"></i>"
                . "<a href='?controller=AdminModules&token=" . Tools::getAdminTokenLite('AdminModules') . "&configure=piwikanalyticsjs&tab_module=analytics_stats&module_name=piwikanalyticsjs&pkwizard' title='{$this->l('Click here to open piwik site lookup wizard')}' data-html='true' data-toggle='tooltip' data-original-title='{$this->l('Click here to open piwik site lookup wizard')}' class='label-tooltip'>{$this->l('Configuration Wizard')}</a>"
                . "</div>"
                . $this->l('Based on the settings you provided this is the info i get from Piwik!') . "<br>"
                . "<strong>" . $this->l('Name') . "</strong>: <i>{$this->piwikSite[0]->name}</i><br>"
                . "<strong>" . $this->l('Main Url') . "</strong>: <i>{$this->piwikSite[0]->main_url}</i><br>"
            );
        } else {
            $fields_form[0]['form']['input'][] = array(
                'type' => 'html',
                'name' => "<div class=\"nav nav-pills pull-right\">"
                . "<i class=\"icon-wrench\" style=\"padding-right: 5px;\"></i>"
                . "<a href='?controller=AdminModules&token=" . Tools::getAdminTokenLite('AdminModules') . "&configure=piwikanalyticsjs&tab_module=analytics_stats&module_name=piwikanalyticsjs&pkwizard' title='{$this->l('Click here to open piwik site lookup wizard')}' data-html='true' data-toggle='tooltip' data-original-title='{$this->l('Click here to open piwik site lookup wizard')}' class='label-tooltip'>{$this->l('Configuration Wizard')}</a>"
                . "</div>"
            );
        }

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik Host'),
            'name' => PKHelper::CPREFIX . 'HOST',
            'desc' => $this->l('Example: www.example.com/piwik/ (without protocol and with / at the end!)'),
            'hint' => $this->l('The host where piwik is installed.!'),
            'required' => true
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik site id'),
            'name' => PKHelper::CPREFIX . 'SITEID',
            'desc' => $this->l('Example: 10'),
            'hint' => $this->l('You can find piwik site id by loggin to piwik installation.'),
            'required' => true
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik token auth'),
            'name' => PKHelper::CPREFIX . 'TOKEN_AUTH',
            'desc' => $this->l('You can find piwik token by loggin to piwik installation. under API'),
            'required' => true
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
            'type' => (version_compare(_PS_VERSION_, '1.5', '>=') ? 'tags' : 'text'),
            'label' => $this->l('Hide known alias URLs'),
            'name' => PKHelper::CPREFIX . 'SET_DOMAINS',
            'desc' => $this->l('In the "Outlinks" report, hide clicks to known alias URLs, Example: *.example.com')
            . '<br />'
            . $this->l('Note: to add multiple domains you must separate them with comma ","')
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

        if (Configuration::get(PKHelper::CPREFIX . 'TOKEN_AUTH') !== false)
            $image_tracking = PKHelper::getPiwikImageTrackingCode();
        else
            $image_tracking = array(
                'default' => $this->l('I need Site ID and Auth Token before i can get your image tracking code'),
                'proxy' => $this->l('I need Site ID and Auth Token before i can get your image tracking code')
            );
        $this->displayErrors(PKHelper::$errors);
        PKHelper::$errors = PKHelper::$error = "";
        $fields_form[0]['form']['input'][] = array(
            'type' => 'html',
            'name' => $this->l('Piwik image tracking code append one of them to field "Extra HTML" this will add images tracking code to all your pages') . "<br>"
            . "<strong>" . $this->l('default') . "</strong>:<br /><i>{$image_tracking['default']}</i><br>"
            . "<strong>" . $this->l('using proxy script') . "</strong>:<br /><i>{$image_tracking['proxy']}</i><br>"
            . (version_compare(_PS_VERSION_, '1.6.0.7', '>=') ?
                    "<br><strong>{$this->l("Before you add the image tracking code make sure the HTMLPurifier library isn't in use, check the settings in 'Preferences => General', you can enable the HTMLPurifier again after you made your changes")}</strong>" :
                    ""
            )
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'textarea',
            'label' => $this->l('Extra HTML'),
            'name' => PKHelper::CPREFIX . 'EXHTML',
            'desc' => $this->l('Some extra HTML code to put after Piwik tracking code, this can be any html of your choice'),
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
                'id' => 'str', 'name' => 'name'
            ),
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik User name'),
            'name' => PKHelper::CPREFIX . 'USRNAME',
            'desc' => $this->l('You can store your Username for Piwik here to make it easy to open piwik interface from your stats page with automatic login'),
            'required' => false, 'autocomplete' => false,
        );
        $fields_form[0]['form']['input'][] = array(
            'type' => 'password',
            'label' => $this->l('Piwik User password'),
            'name' => PKHelper::CPREFIX . 'USRPASSWD',
            'desc' => $this->l('You can store your Password for Piwik here to make it easy to open piwik interface from your stats page with automatic login'),
            'required' => false, 'autocomplete' => false,
        );

        $fields_form[0]['form']['submit'] = array(
            'title' => $this->l('Save'),
            'class' => 'btn btn-default'
        );

        define('PIWIK_AUTHORIZED_ADV_FORM', TRUE);
        require dirname(__FILE__) . '/_piwik_config_adv_form.php';

        if ($this->piwikSite !== FALSE) {
            $tmp = PKHelper::getMyPiwikSites(TRUE);
            $this->displayErrors(PKHelper::$errors);
            PKHelper::$errors = PKHelper::$error = "";
            $pksite_default = array('value' => 0, 'label' => $this->l('Choose Piwik site'));
            $pksites = array();
            foreach ($tmp as $pksid) {
                $pksites[] = array(
                    'pkid' => $pksid->idsite,
                    'name' => "{$pksid->name} #{$pksid->idsite}",
                );
            }
            unset($tmp, $pksid);

            $pktimezone_default = array('value' => 0, 'label' => $this->l('Choose Timezone'));
            $pktimezones = array();
            $tmp = PKHelper::getTimezonesList();
            $this->displayErrors(PKHelper::$errors);
            PKHelper::$errors = PKHelper::$error = "";
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
            unset($tmp, $pktz, $pktzV, $pktzK);
            $fields_form[2]['form'] = array(
                'legend' => array(
                    'title' => $this->displayName . ' ' . $this->l('Advanced') . ' - ' . $this->l('Edit Piwik site'),
                    'image' => $this->_path . 'logox22.png'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Piwik Site'),
                        'name' => 'SPKSID',
                        'desc' => sprintf($this->l('Based on your settings in Piwik your default site is %s'), $this->piwikSite[0]->idsite),
                        'options' => array(
                            'default' => $pksite_default,
                            'query' => $pksites,
                            'id' => 'pkid',
                            'name' => 'name'
                        ),
                        'onchange' => 'return ChangePKSiteEdit(this.value)',
                    ),
                    array(
                        'type' => 'html',
                        'name' => $this->l('In this section you can modify your settings in piwik just so you don\'t have to login to Piwik to do this') . "<br>"
                        . "<strong>" . $this->l('Currently selected name') . "</strong>: <i id='wnamedsting'>{$this->piwikSite[0]->name}</i><br>"
                        . "<input type=\"hidden\" name=\"PKAdminIdSite\" id=\"PKAdminIdSite\" value=\"{$this->piwikSite[0]->idsite}\" />"
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Piwik Site Name'),
                        'name' => 'PKAdminSiteName',
                        'desc' => $this->l('Name of this site in Piwik'),
                    ),
//                    array(
//                        'type' => 'text',
//                        'label' => $this->l('Site urls'),
//                        'name' => 'PKAdminSiteUrls',
//                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Ecommerce'),
                        'name' => 'PKAdminEcommerce',
                        'desc' => $this->l('Is this site an ecommerce site?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Site Search'),
                        'name' => 'PKAdminSiteSearch',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.5', '>=') ? 'tags' : 'text'),
                        'label' => $this->l('Search Keyword Parameters'),
                        'name' => 'PKAdminSearchKeywordParameters',
                        'desc' => $this->l('the following keyword parameters must be excluded to avoid normal page views to be interpreted as searches (the tracking code will see them and make the required postback to Piwik if it is a real search), if you are only using PrestaShop with this site setting this to empty, will be sufficient')
                        . "<br><br><strong>tag</strong> and <strong>search_query</strong>",
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.5', '>=') ? 'tags' : 'text'),
                        'label' => $this->l('Search Category Parameters'),
                        'name' => 'PKAdminSearchCategoryParameters',
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.5', '>=') ? 'tags' : 'text'),
                        'label' => $this->l('Excluded ip addresses'),
                        'name' => 'PKAdminExcludedIps',
                        'desc' => $this->l('ip addresses excluded from tracking, separated by comma ","'),
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.5', '>=') ? 'tags' : 'text'),
                        'label' => $this->l('Excluded Query Parameters'),
                        'name' => 'PKAdminExcludedQueryParameters',
                        'desc' => $this->l('please read: http://piwik.org/faq/how-to/faq_81/'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Timezone'),
                        'name' => 'PKAdminTimezone',
                        'desc' => sprintf($this->l('Based on your settings in Piwik your default timezone is %s'), $this->piwikSite[0]->timezone),
                        'options' => array(
                            'default' => $pktimezone_default,
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
                        'label' => $this->l('Currency'),
                        'name' => 'PKAdminCurrency',
                        'desc' => sprintf($this->l('Based on your settings in Piwik your default currency is %s'), $this->piwikSite[0]->currency),
                        'options' => array(
                            'default' => $this->default_currency,
                            'query' => $this->currencies,
                            'id' => 'iso_code',
                            'name' => 'name'
                        ),
                    ),
//                    array(
//                        'type' => 'text',
//                        'label' => $this->l('Website group'),
//                        'name' => 'PKAdminGroup',
//                        'desc' => sprintf('Requires plugin "WebsiteGroups" before it can be used from within Piwik'),
//                    ),
//                    array(
//                        'type' => 'text',
//                        'label' => $this->l('Website start date'),
//                        'name' => 'PKAdminStartDate',
//                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Excluded User Agents'),
                        'name' => 'PKAdminExcludedUserAgents',
                        'rows' => 10,
                        'cols' => 50,
                        'desc' => $this->l('please read: http://piwik.org/faq/how-to/faq_17483/'),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Keep URL Fragments'),
                        'name' => 'PKAdminKeepURLFragments',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
//                    array(
//                        'type' => 'text',
//                        'label' => $this->l('Site Type'),
//                        'name' => 'PKAdminSiteType',
//                    ),
                    array('type' => 'html', 'name' => "
<button onclick=\"return submitPiwikSiteAPIUpdate()\" 
        id=\"submitUpdatePiwikAdmSite\" class=\"btn btn-default pull-left\" 
        name=\"submitUpdatePiwikAdmSite\" value=\"1\" type=\"button\">
    <i class=\"process-icon-save\"></i>{$this->l('Save')}</button>"),
                ),
            );
        }

        $helper->fields_value = $this->getFormFields();
        $this->context->smarty->assign(array(
            'psversion' => _PS_VERSION_,
            /* piwik_site_manager */
            'psm_currentIndex' => $helper->currentIndex,
            'psm_token' => $helper->token,
        ));
        if (is_array($this->_errors))
            $_html = implode('', $this->_errors) . $_html;
        else
            $_html = $this->_errors . $_html;
        return $_html
                . $helper->generateForm($fields_form)
                . $this->display(__FILE__, 'views/templates/admin/jsfunctions.tpl')
                . $this->display(__FILE__, 'views/templates/admin/piwik_site_manager.tpl');
    }

    /**
     * Method used when making ajax calls to Piwik API,
     * this method outputs json data.
     * 
     * NOTE: only methods defiend in "PKHelper::$acp" can be called
     */
    private function __pkapicall() {
        $apiMethod = Tools::getValue('pkapicall');
        if (method_exists('PKHelper', $apiMethod) && isset(PKHelper::$acp[$apiMethod])) {
            $required = PKHelper::$acp[$apiMethod]['required'];
            // $optional = PKHelper::$acp[$apiMethod]['optional'];
            $order = PKHelper::$acp[$apiMethod]['order'];
            foreach ($required as $requiredOption) {
                if (!Tools::getIsset($requiredOption)) {
                    PKHelper::DebugLogger("__pkapicall():\n\t- Required parameter \"" . $requiredOption . '" is missing');
                    die(Tools::jsonEncode(array('error' => true, 'message' => sprintf($this->l('Required parameter "%s" is missing'), $requiredOption))));
                }
            }
            foreach ($order as & $value) {
                if (Tools::getIsset($value)) {
                    $value = Tools::getValue($value);
                } else {
                    $value = NULL;
                }
            }

            if (Tools::getIsset('httpUser'))
                PKHelper::$httpAuthUsername = Tools::getValue('httpUser');
            if (Tools::getIsset('httpPasswd'))
                PKHelper::$httpAuthPassword = Tools::getValue('httpPasswd');
            if (Tools::getIsset('piwikhost'))
                PKHelper::$piwikHost = Tools::getValue('piwikhost');

            PKHelper::DebugLogger("__pkapicall():\n\t- Call PKHelper::" . $apiMethod);
            $result = call_user_func_array(array('PKHelper', $apiMethod), $order);
            if ($result === FALSE) {
                $lastError = "";
                if (!empty(PKHelper::$errors))
                    $lastError = "\n" . PKHelper::$error;
                die(Tools::jsonEncode(array('error' => TRUE, 'message' => sprintf($this->l('Unknown error occurred%s'), $lastError))));
            } else {
                PKHelper::DebugLogger("__pkapicall():\n\t- All good");
                if (is_array($result) && isset($result[0])) {
                    $message = $result;
                } else if (is_object($result)) {
                    $message = $result;
                } else
                    $message = (is_string($result) && !is_bool($result) ? $result : (is_array($result) ? implode(', ', $result) : TRUE));

                if (is_bool($message)) {
                    die(Tools::jsonEncode(array('error' => FALSE, 'message' => $this->l('Successfully Updated'))));
                } else {
                    die(Tools::jsonEncode(array('error' => FALSE, 'message' => $message)));
                }
            }
        } else {
            die(Tools::jsonEncode(array('error' => true, 'message' => sprintf($this->l('Method "%s" dos not exists in class PKHelper'), $apiMethod))));
        }
    }

    /**
     * returns a form helper formatted array of default input data for use with piwikanalyticsjs::getContent()
     * @return array
     */
    protected function getFormFields() {
        $PIWIK_PRODID_V1 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V1');
        $PIWIK_PRODID_V2 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V2');
        $PIWIK_PRODID_V3 = Configuration::get(PKHelper::CPREFIX . 'PRODID_V3');
        $PIWIK_PROXY_SCRIPT = Configuration::get(PKHelper::CPREFIX . 'PROXY_SCRIPT');
        $PIWIK_RCOOKIE_TIMEOUT = (int) Configuration::get(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT');
        $PIWIK_COOKIE_TIMEOUT = (int) Configuration::get(PKHelper::CPREFIX . 'COOKIE_TIMEOUT');
        $PIWIK_SESSION_TIMEOUT = (int) Configuration::get(PKHelper::CPREFIX . 'SESSION_TIMEOUT');
        return array(
            PKHelper::CPREFIX . 'HOST' => Configuration::get(PKHelper::CPREFIX . 'HOST'),
            PKHelper::CPREFIX . 'SITEID' => Configuration::get(PKHelper::CPREFIX . 'SITEID'),
            PKHelper::CPREFIX . 'TOKEN_AUTH' => Configuration::get(PKHelper::CPREFIX . 'TOKEN_AUTH'),
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
            PKHelper::CPREFIX . 'USE_CURL' => Configuration::get(PKHelper::CPREFIX . 'USE_CURL'),
            PKHelper::CPREFIX . 'SEARCH_QUERY' => Configuration::get(PKHelper::CPREFIX . "SEARCH_QUERY"),
            PKHelper::CPREFIX . 'PROXY_TIMEOUT' => Configuration::get(PKHelper::CPREFIX . "PROXY_TIMEOUT"),
            /* stuff thats isset by ajax calls to Piwik API ---(here to avoid not isset warnings..!)--- */
            'PKAdminSiteName' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->name : ''),
            'PKAdminEcommerce' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->ecommerce : ''),
            'PKAdminSiteSearch' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch : ''),
            'PKAdminSearchKeywordParameters' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch_keyword_parameters : ''),
            'PKAdminSearchCategoryParameters' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->sitesearch_category_parameters : ''),
            'SPKSID' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->idsite : Configuration::get(PKHelper::CPREFIX . 'SITEID')),
            'PKAdminExcludedIps' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_ips : ''),
            'PKAdminExcludedQueryParameters' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_parameters : ''),
            'PKAdminTimezone' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->timezone : ''),
            'PKAdminCurrency' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->currency : ''),
            'PKAdminGroup' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->group : ''),
            'PKAdminStartDate' => '',
            'PKAdminSiteUrls' => '',
            'PKAdminExcludedUserAgents' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_user_agents : ''),
            'PKAdminKeepURLFragments' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->keep_url_fragment : 0),
            'PKAdminSiteType' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->type : 'website'),
        );
    }

    private function processWizardFormUpdate() {
        $username = $password = $piwikhost = $saveusrpwd = $usernamehttp = $passwordhttp = false;
        if (Tools::getIsset(PKHelper::CPREFIX . 'USRNAME_WIZARD'))
            $username = Tools::getValue(PKHelper::CPREFIX . 'USRNAME_WIZARD');
        if (Tools::getIsset(PKHelper::CPREFIX . 'USRPASSWD_WIZARD'))
            $password = Tools::getValue(PKHelper::CPREFIX . 'USRPASSWD_WIZARD');
        if (Tools::getIsset(PKHelper::CPREFIX . 'HOST_WIZARD'))
            $piwikhost = Tools::getValue(PKHelper::CPREFIX . 'HOST_WIZARD');
        if (Tools::getIsset(PKHelper::CPREFIX . 'SAVE_USRPWD_WIZARD'))
            $saveusrpwd = (bool) Tools::getValue(PKHelper::CPREFIX . 'SAVE_USRPWD_WIZARD', 0);
        if (Tools::getIsset(PKHelper::CPREFIX . 'PAUTHUSR_WIZARD'))
            $usernamehttp = Tools::getValue(PKHelper::CPREFIX . 'PAUTHUSR_WIZARD', 0);
        if (Tools::getIsset(PKHelper::CPREFIX . 'PAUTHPWD_WIZARD'))
            $passwordhttp = Tools::getValue(PKHelper::CPREFIX . 'PAUTHPWD_WIZARD', 0);
        if ($piwikhost !== false) {
            $tmp = $piwikhost;
            if (!empty($tmp)) {
                if (Validate::isUrl($tmp) || Validate::isUrl('http://' . $tmp)) {
                    $tmp = str_replace(array('http://', 'https://', '//'), "", $tmp);
                    if (substr($tmp, -1) != "/") {
                        $tmp .= "/";
                    }
                    Configuration::updateValue(PKHelper::CPREFIX . 'HOST', $tmp);
                } else {
                    $this->_errors[] = $this->displayError($this->l('Piwik host url is not valid'));
                }
            } else {
                $this->_errors[] = $this->displayError($this->l('Piwik host cannot be empty'));
            }
        } else {
            $this->_errors[] = $this->displayError($this->l('Piwik host cannot be empty'));
        }
        if (($username !== false && strlen($username) > 2) && ($password !== false && strlen($password) > 2)) {
            if ($saveusrpwd == 1 || $saveusrpwd !== false) {
                Configuration::updateValue(PKHelper::CPREFIX . "USRPASSWD", $password);
                Configuration::updateValue(PKHelper::CPREFIX . "USRNAME", $username);
            }
        } else {
            $this->_errors[] = $this->displayError($this->l('Username and/or password is missing/to short'));
        }
        if (($usernamehttp !== false && strlen($usernamehttp) > 0) && ($passwordhttp !== false && strlen($passwordhttp) > 0)) {
            Configuration::updateValue(PKHelper::CPREFIX . "PAUTHUSR", $usernamehttp);
            Configuration::updateValue(PKHelper::CPREFIX . "PAUTHPWD", $passwordhttp);
        }
    }

    /**
     * handles the configuration form update
     * @return void
     */
    private function processFormsUpdate() {
        if (Tools::isSubmit('submitUpdate' . $this->name)) {
            if (Tools::getIsset(PKHelper::CPREFIX . 'HOST')) {
                $tmp = Tools::getValue(PKHelper::CPREFIX . 'HOST', '');
                if (!empty($tmp)) {
                    if (Validate::isUrl($tmp) || Validate::isUrl('http://' . $tmp)) {
                        $tmp = str_replace(array('http://', 'https://', '//'), "", $tmp);
                        if (substr($tmp, -1) != "/") {
                            $tmp .= "/";
                        }
                        Configuration::updateValue(PKHelper::CPREFIX . 'HOST', $tmp);
                    } else {
                        $this->_errors[] = $this->displayError($this->l('Piwik host url is not valid'));
                    }
                } else {
                    $this->_errors[] = $this->displayError($this->l('Piwik host cannot be empty'));
                }
            }
            if (Tools::getIsset(PKHelper::CPREFIX . 'SITEID')) {
                $tmp = (int) Tools::getValue(PKHelper::CPREFIX . 'SITEID', 0);
                Configuration::updateValue(PKHelper::CPREFIX . 'SITEID', $tmp);
                if ($tmp <= 0) {
                    $this->_errors[] = $this->displayError($this->l('Piwik site id is lower or equal to "0"'));
                }
            }
            if (Tools::getIsset(PKHelper::CPREFIX . 'TOKEN_AUTH')) {
                $tmp = Tools::getValue(PKHelper::CPREFIX . 'TOKEN_AUTH', '');
                Configuration::updateValue(PKHelper::CPREFIX . 'TOKEN_AUTH', $tmp);
                if (empty($tmp)) {
                    $this->_errors[] = $this->displayError($this->l('Piwik auth token is empty'));
                }
            }
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
            // proxy script timeout
            if (Tools::getIsset(PKHelper::CPREFIX . 'PROXY_TIMEOUT')) {
                $tmp = (int) Tools::getValue(PKHelper::CPREFIX . 'PROXY_TIMEOUT', 5);
                if ($tmp <= 0) {
                    $this->_errors[] = $this->displayError($this->l('Piwik proxy timeout must be an integer and larger than 0 (zero)'));
                    $tmp = 5;
                }
                Configuration::updateValue(PKHelper::CPREFIX . 'PROXY_TIMEOUT', $tmp);
            }
            /*
             * @todo VALIDATE!!!, YES VALIDATE!!! thank you ...
             */
            if (Tools::getIsset(PKHelper::CPREFIX . 'USE_PROXY'))
                Configuration::updateValue(PKHelper::CPREFIX . 'USE_PROXY', Tools::getValue(PKHelper::CPREFIX . 'USE_PROXY'));
            if (Tools::getIsset(PKHelper::CPREFIX . 'USE_CURL'))
                Configuration::updateValue(PKHelper::CPREFIX . 'USE_CURL', Tools::getValue(PKHelper::CPREFIX . 'USE_CURL'));
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
                Configuration::updateValue(PKHelper::CPREFIX . "DREPDATE", Tools::getValue(PKHelper::CPREFIX . 'DREPDATE', 'day|today'));

            if (Tools::getIsset(PKHelper::CPREFIX . 'SEARCH_QUERY'))
                Configuration::updateValue(PKHelper::CPREFIX . "SEARCH_QUERY", Tools::getValue(PKHelper::CPREFIX . 'SEARCH_QUERY', '{QUERY} ({PAGE})'));

            $this->_errors[] = $this->displayConfirmation($this->l('Configuration Updated'));
        }
    }

    /* HOOKs */

    public function hookActionProductCancel($params) {
        /*
         * @todo research [ps 1.6]
         * admin hook, wonder if this can be implemented
         * remove a product from the cart in Piwik
         * 
         * $params = array('order' => obj [Order], 'id_order_detail' => int)
         * 
         * if (version_compare(_PS_VERSION_, '1.5', '>=')
         *     $this->registerHook('actionProductCancel')
         */
    }

    public function hookProductFooter($params) {
        /**
         * @todo research
         * use for product views, keeping hookFooter as simple as possible
         * $params = array('product' => $product, 'category' => $category)
         * displayFooterProduct ?? [array('product' => obj, 'category' => obj)]
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
        if (!isset($this->context->cart))
            return;

        $cart = array(
            'controller' => Tools::getValue('controller'),
            'addAction' => Tools::getValue('add') ? 'add' : '',
            'removeAction' => Tools::getValue('delete') ? 'delete' : '',
            'extraAction' => Tools::getValue('op'),
            'qty' => (int) Tools::getValue('qty', 1)
        );
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
        if ((int) Configuration::get(PKHelper::CPREFIX . 'SITEID') <= 0)
            return "";
        if ((int) Tools::getValue('content_only') > 0 && get_class($this->context->controller) == 'ProductController') {
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
        // $param['expr'] is not the searched word if lets say search is Snitmøntre then the $param['expr'] will be Snitmontre
        $expr = Tools::getIsset('search_query') ? htmlentities(Tools::getValue('search_query')) : $param['expr'];
        /* if multi pages in search add page number of current if set! */
        $search_tpl = Configuration::get(PKHelper::CPREFIX . 'SEARCH_QUERY');
        if ($search_tpl === false)
            $search_tpl = "{QUERY} ({PAGE})";
        if (Tools::getIsset('p')) {
            $search_tpl = str_replace('{QUERY}', $expr, $search_tpl);
            $expr = str_replace('{PAGE}', Tools::getValue('p'), $search_tpl);
        }

        $this->context->smarty->assign(array(
            PKHelper::CPREFIX . 'SITE_SEARCH' => "_paq.push(['trackSiteSearch',\"{$expr}\",false,{$param['total']}]);"
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

    /**
     * add Prestashop 1.4 to 1.5.6 specific settings
     * @param mixed $params
     * @since 0.4
     */
    private function _hookFooterPS14($params, $page_name) {
        if (empty($page_name)) {
            /* we can't do any thing use full  */
            return;
        }

        if (strtolower($page_name) == "product" && isset($_GET['id_product']) && Validate::isUnsignedInt($_GET['id_product'])) {
            $product = new Product($_GET['id_product'], false, (isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (isset($this->context->cookie->id_lang) ? $this->context->cookie->id_lang : NULL)));
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
            $category = new Category($_GET['id_category'], (isset($_GET['id_lang']) && Validate::isUnsignedInt($_GET['id_lang']) ? $_GET['id_lang'] : (isset($this->context->cookie->id_lang) ? $this->context->cookie->id_lang : NULL)));
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

    /* HELPERS */

    /**
     * returns true if request is wizard
     * @return boolean
     * @since 0.8.4
     */
    private function isWizardRequest() {
        return Tools::getIsset('pkwizard');
    }

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
                $this->_errors[] = $this->displayError($value);
            }
        }
    }

    /**
     * convert into default currency used in Piwik
     * @param array $params
     * @return float
     * @since 0.4
     */
    private function currencyConvertion($params) {
        $pkc = Configuration::get(PKHelper::CPREFIX . "DEFAULT_CURRENCY");
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
     * @param boolean $array get categories as PHP array (TRUE), or javascript (FAlSE)
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
        if (version_compare(_PS_VERSION_, '1.5.0.13', "<="))
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

        $pkvct = (int) Configuration::get(PKHelper::CPREFIX . 'COOKIE_TIMEOUT'); /* no isset if the same as default */
        if ($pkvct != 0 && $pkvct !== FALSE && ($pkvct != (int) (self::PK_VC_TIMEOUT * 60))) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'COOKIE_TIMEOUT', $pkvct);
        }
        unset($pkvct);

        $pkrct = (int) Configuration::get(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT'); /* no isset if the same as default */
        if ($pkrct != 0 && $pkrct !== FALSE && ($pkrct != (int) (self::PK_RC_TIMEOUT * 60))) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT', $pkrct);
        }
        unset($pkrct);

        $pksct = (int) Configuration::get(PKHelper::CPREFIX . 'SESSION_TIMEOUT'); /* no isset if the same as default */
        if ($pksct != 0 && $pksct !== FALSE && ($pksct != (int) (self::PK_SC_TIMEOUT * 60))) {
            $this->context->smarty->assign(PKHelper::CPREFIX . 'SESSION_TIMEOUT', $pksct);
        }
        unset($pksct);

        $this->context->smarty->assign(PKHelper::CPREFIX . 'EXHTML', Configuration::get(PKHelper::CPREFIX . 'EXHTML'));

        $PIWIK_COOKIE_DOMAIN = Configuration::get(PKHelper::CPREFIX . 'COOKIE_DOMAIN');
        $this->context->smarty->assign(PKHelper::CPREFIX . 'COOKIE_DOMAIN', (empty($PIWIK_COOKIE_DOMAIN) ? FALSE : $PIWIK_COOKIE_DOMAIN));

        $PIWIK_SET_DOMAINS = Configuration::get(PKHelper::CPREFIX . 'SET_DOMAINS');
        if (!empty($PIWIK_SET_DOMAINS)) {
            $sdArr = explode(',', Configuration::get(PKHelper::CPREFIX . 'SET_DOMAINS'));
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
            PKHelper::CPREFIX . 'USE_PROXY', PKHelper::CPREFIX . 'HOST',
            PKHelper::CPREFIX . 'SITEID', PKHelper::CPREFIX . 'TOKEN_AUTH',
            PKHelper::CPREFIX . 'COOKIE_TIMEOUT', PKHelper::CPREFIX . 'SESSION_TIMEOUT',
            PKHelper::CPREFIX . 'DEFAULT_CURRENCY', PKHelper::CPREFIX . 'CRHTTPS',
            PKHelper::CPREFIX . 'PRODID_V1', PKHelper::CPREFIX . 'PRODID_V2',
            PKHelper::CPREFIX . 'PRODID_V3', PKHelper::CPREFIX . 'COOKIE_DOMAIN',
            PKHelper::CPREFIX . 'SET_DOMAINS', PKHelper::CPREFIX . 'DNT',
            PKHelper::CPREFIX . 'EXHTML', PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT',
            PKHelper::CPREFIX . 'USRNAME', PKHelper::CPREFIX . 'USRPASSWD',
            PKHelper::CPREFIX . 'PAUTHUSR', PKHelper::CPREFIX . 'PAUTHPWD',
            PKHelper::CPREFIX . 'DREPDATE', PKHelper::CPREFIX . 'USE_CURL'
        );
        $defaults = array(
            0, "", 0, "", self::PK_VC_TIMEOUT, self::PK_SC_TIMEOUT, 'EUR', 0,
            '{ID}-{ATTRID}#{REFERENCE}', '{ID}#{REFERENCE}',
            '{ID}#{ATTRID}', Tools::getShopDomain(), '', 0,
            '', self::PK_RC_TIMEOUT, '', '', '', '', 'day|today', 0
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
        PiwikWizardHelper::$strings['821dc1363c6c3a23185ea0cf3bee5261'] = $this->l("Select a site.");
        PiwikWizardHelper::$strings['4ddd9129714e7146ed2215bcbd559335'] = $this->l("I encountered an unknown error while trying to get the selected site, id#%s");
        PiwikWizardHelper::$strings['e41246ca9fd83a123022c5c5b7a6f866'] = $this->l("I'm unable, to get admin access to the selected site id #%s");
        PiwikWizardHelper::$strings['ea4788705e6873b424c65e91c2846b19'] = $this->l("Cancel");
        PiwikWizardHelper::$strings['00617256bf279d54780075598d7e958c'] = $this->l('Create new Site');
        PiwikWizardHelper::$strings['3c68005461c0d81c1626c01c9aa400e0'] = $this->l("Unable to get a list of websites from Piwik, if you dont have any sites in piwik yet click 'Create new Site' button.");
        PiwikWizardHelper::$strings['10ac3d04253ef7e1ddc73e6091c0cd55'] = $this->l('Next');
        PiwikWizardHelper::$strings['11552a6e511d4ab1ee43f2e0ab9d623f'] = $this->l('this field along with username can be used if piwik installation is protected by HTTP Basic Authorization');
        PiwikWizardHelper::$strings['e4a1b909bb1918a40f18d8dfb013fd28'] = $this->l('HTTP Auth Password');
        PiwikWizardHelper::$strings['4b3e9319a9c0c328221080116e0d5104'] = $this->l('HTTP Auth Username');
        PiwikWizardHelper::$strings['7965ca87322fb45ebc60071041580e8f'] = $this->l("HTTP Basic Authorization");
        PiwikWizardHelper::$strings['b9f5c797ebbf55adccdd8539a65a0241'] = $this->l('Disabled');
        PiwikWizardHelper::$strings['00d23a76e43b46dae9ec7aa9dcbebb32'] = $this->l('Enabled');
        PiwikWizardHelper::$strings['3b6761cfe4215c632072f87259970d84'] = $this->l('Whether or not to save the username and password, saving the username and password will enable quick(automatic) login to piwik from the integrated stats page');
        PiwikWizardHelper::$strings['ea88860b951ee567e988d794ef0ca090'] = $this->l('Save username and password');
        PiwikWizardHelper::$strings['47f896968366f1688c401fece093c2d1'] = $this->l('Enter your password for Piwik, we need this in order to fetch your api authentication token');
        PiwikWizardHelper::$strings['3eb1a362b1cfc065415c6f31730bfd84'] = $this->l('Piwik User password');
        PiwikWizardHelper::$strings['8e70216e1e56d8d2f7e3cd229171ba1f'] = $this->l('Enter your username for Piwik, we need this in order to fetch your api authentication token');
        PiwikWizardHelper::$strings['4081cf3a3e78277c30f0acd948082cb8'] = $this->l('Piwik User name');
        PiwikWizardHelper::$strings['6fbd6e012c9a1a4b2f0796196d060e6d'] = $this->l('The full url to your piwik installation.!');
        PiwikWizardHelper::$strings['6752ab12af9a9878bf9d08c751ac2aa5'] = $this->l('Example: http://www.example.com/piwik/');
        PiwikWizardHelper::$strings['3c6805325f65f0ee32244920e46aac39'] = $this->l('Piwik Host');
        PiwikWizardHelper::$strings['b817abd7e8364a16b7edfcc78e74558e'] = $this->l('Piwik Site Name');
        PiwikWizardHelper::$strings['2cbae2cc76d6994fee1bb84712069eb7'] = $this->l('Main Url');
        PiwikWizardHelper::$strings['82419044af129bcd8894f7d208f4dd2b'] = $this->l('Addtional Urls');
        PiwikWizardHelper::$strings['34eea1731773212b3234ef8048dbee1e'] = $this->l('Is this site an ecommerce site?');
        PiwikWizardHelper::$strings['53ef2022ee91ccf50dd8b63da5a563b9'] = $this->l('Ecommerce');
        PiwikWizardHelper::$strings['93cba07454f06a4a960172bbd6e2a435'] = $this->l('Yes');
        PiwikWizardHelper::$strings['bafd7322c6e97d25b6299b5d6fe8920b'] = $this->l('No');
        PiwikWizardHelper::$strings['871e94256265ecc5d2ca1f9b42f861ac'] = $this->l('Site Search');
        PiwikWizardHelper::$strings['0eccdaa003c737691fe1153ea0a4550f'] = $this->l('Search Keyword Parameters');
        PiwikWizardHelper::$strings['28235d8369c0f9b740f83e25c4fe2f1d'] = $this->l('keyword parameters must be excluded to avoid normal page views to be interpreted as searches (the tracking code will see them and make the required postback to Piwik if it is a real search), if you are only using PrestaShop with this site setting this to empty, will be sufficient');
        PiwikWizardHelper::$strings['f4e283ffb009bdda02a06737c25bd93c'] = $this->l('Search Category Parameters');
        PiwikWizardHelper::$strings['dc1317a4a93a700507570dfd69c757d9'] = $this->l('Excluded ip addresses');
        PiwikWizardHelper::$strings['3443f50780c66a77394a10925b76bed7'] = $this->l('ip addresses excluded from tracking, separated by comma ","');
        PiwikWizardHelper::$strings['510940abcdfeff622ba993e36f47519f'] = $this->l('Excluded Query Parameters');
        PiwikWizardHelper::$strings['0040b52be769bb81e1e5d2051b7f6652'] = $this->l('please read: http://piwik.org/faq/how-to/faq_81/');
        PiwikWizardHelper::$strings['236df51bb0e6416236e255b528346fca'] = $this->l('Timezone');
        PiwikWizardHelper::$strings['63ce9117e223ae7871044b39e2ca28be'] = $this->l('The timezone for this site');
        PiwikWizardHelper::$strings['6ba290764cb95fbe109e7f3b317865ad'] = $this->l('Choose Timezone');
        PiwikWizardHelper::$strings['386c339d37e737a436499d423a77df0c'] = $this->l('Currency');
        PiwikWizardHelper::$strings['70cd4d21141d3d3198c8a606303d454b'] = $this->l('The currency for this site');
        PiwikWizardHelper::$strings['a7b3bae411492841bb245cee3ddcc599'] = $this->l('Excluded User Agents');
        PiwikWizardHelper::$strings['7a42168f46cbbe0357bcc36205123080'] = $this->l('please read: http://piwik.org/faq/how-to/faq_17483/');
        PiwikWizardHelper::$strings['a9d005356a04262c95dc815b96a65038'] = $this->l('Keep URL Fragments');
        PiwikWizardHelper::$strings['686e697538050e4664636337cc3b834f'] = $this->l('Create');
        PiwikWizardHelper::$strings['157c966cf06d25578931a8c74298c332'] = $this->l('Name of this site in Piwik');
        // posted username and passwd etc..
        PiwikWizardHelper::getFormValuesInternal();
    }

    /* INSTALL / UNINSTALL */

    /**
     * Reset the module configuration
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
                    DELETE FROM `' . _DB_PREFIX_ . 'configuration_lang`
                    WHERE `id_configuration` IN (
                            SELECT `id_configuration`
                            FROM `' . _DB_PREFIX_ . 'configuration`
                            WHERE `name` = "' . pSQL($key) . '" AND `id_shop` = "' . pSQL($id_shop) . '"
                    )');
                Db::getInstance()->execute('
                    DELETE FROM `' . _DB_PREFIX_ . 'configuration`
                    WHERE `name` = "' . pSQL($key) . '" AND `id_shop` = "' . pSQL($id_shop) . '"');
            }
        }
        return true;
    }

    /**
     * Install the module
     * @return boolean false on install error
     */
    public function install() {
        /* create complete new page tab */
        $tab = new Tab();
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = 'Piwik Analytics';
        }
        $tab->module = 'piwikanalyticsjs';
        $tab->active = TRUE;

        if (method_exists('Tab', 'getInstanceFromClassName')) {
            if (version_compare(_PS_VERSION_, '1.5.0.5', ">=") && version_compare(_PS_VERSION_, '1.5.3.999', "<=")) {
                $tab->class_name = 'PiwikAnalytics15';
            } else if (version_compare(_PS_VERSION_, '1.5.0.13', "<=")) {
                $tab->class_name = 'AdminPiwikAnalytics';
            } else {
                $tab->class_name = 'PiwikAnalytics';
            }
            $AdminParentStats = TabCore::getInstanceFromClassName('AdminStats');
            if ($AdminParentStats == null || !($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) || $AdminParentStats->id == 0)
                $AdminParentStats = TabCore::getInstanceFromClassName('AdminParentStats');
        } else if (method_exists('Tab', 'getIdFromClassName')) {
            if (version_compare(_PS_VERSION_, '1.5.0.5', ">=") && version_compare(_PS_VERSION_, '1.5.3.999', "<=")) {
                $tab->class_name = 'PiwikAnalytics15';
            } else if (version_compare(_PS_VERSION_, '1.5.0.13', "<=")) {
                $tab->class_name = 'AdminPiwikAnalytics';
            } else {
                $tab->class_name = 'PiwikAnalytics';
            }
            $tmpId = TabCore::getIdFromClassName('AdminStats');
            if ($tmpId != null && $tmpId > 0)
                $AdminParentStats = new Tab($tmpId);
            else {
                $tmpId = TabCore::getIdFromClassName('AdminParentStats');
                if ($tmpId != null && $tmpId > 0)
                    $AdminParentStats = new Tab($tmpId);
            }
        }

        $tab->id_parent = (isset($AdminParentStats) && ($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? $AdminParentStats->id : -1);
        if ($tab->add())
            Configuration::updateValue(PKHelper::CPREFIX . 'TAPID', (int) $tab->id);
        else {
            $this->_errors[] = sprintf($this->l('Unable to create new tab "Piwik Analytics", Please forward tthe following info to the developer %s'), "<br/>"
                    . (isset($AdminParentStats) ? "isset(\$AdminParentStats): True" : "isset(\$AdminParentStats): False")
                    . "<br/>"
                    . "Type of \$AdminParentStats: " . gettype($AdminParentStats)
                    . "<br/>"
                    . "Class name of \$AdminParentStats: " . get_class($AdminParentStats)
                    . "<br/>"
                    . (($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats instanceof Tab: True" : "\$AdminParentStats instanceof Tab: False")
                    . "<br/>"
                    . (($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->id: " . $AdminParentStats->id : "\$AdminParentStats->id: ?0?")
                    . "<br/>"
                    . (($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->name: " . $AdminParentStats->name : "\$AdminParentStats->name: ?0?")
                    . "<br/>"
                    . (($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->class_name: " . $AdminParentStats->class_name : "\$AdminParentStats->class_name: ?0?")
                    . "<br/>"
                    . "Prestashop version: " . _PS_VERSION_
                    . "<br/>"
                    . "PHP version: " . PHP_VERSION
            );
        }

        /* default values */
        foreach ($this->getConfigFields(FALSE) as $key => $value) {
            Configuration::updateValue($key, $value);
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
                if (method_exists('Tab', 'getInstanceFromClassName')) {
                    $AdminParentStats = Tab::getInstanceFromClassName('PiwikAnalytics15');
                    if (!isset($AdminParentStats) || !Validate::isLoadedObject($AdminParentStats))
                        $AdminParentStats = Tab::getInstanceFromClassName('AdminPiwikAnalytics');
                    if (!isset($AdminParentStats) || !Validate::isLoadedObject($AdminParentStats))
                        $AdminParentStats = Tab::getInstanceFromClassName('PiwikAnalytics');
                } else if (method_exists('Tab', 'getIdFromClassName')) {
                    $tmpId = TabCore::getIdFromClassName('PiwikAnalytics15');
                    if (!isset($tmpId) || !((bool) $tmpId) || ((int) $tmpId < 1))
                        $tmpId = Tab::getIdFromClassName('AdminPiwikAnalytics');
                    if (!isset($tmpId) || !((bool) $tmpId) || ((int) $tmpId < 1))
                        $tmpId = Tab::getIdFromClassName('PiwikAnalytics');
                    if (!isset($tmpId) || !((bool) $tmpId) || ((int) $tmpId < 1))
                        $AdminParentStats = new Tab($tmpId);
                }
                if (isset($AdminParentStats) && ($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore)) {
                    $AdminParentStats->delete();
                }
            } catch (Exception $ex) {
                
            }
            Configuration::deleteByName(PKHelper::CPREFIX . 'TAPID');
            return true;
        }
        return false;
    }

}
