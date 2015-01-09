<?php

/*
 * Copyright (C) 2015 Christian Jensen
 *
 * This file is part of PiwikAnalyticsManager for prestashop.
 * 
 * PiwikAnalyticsManager for prestashop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * PiwikAnalyticsManager for prestashop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with PiwikAnalyticsManager for prestashop.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* Backward compatibility */
if (_PS_VERSION_ < '1.5') {
    if (version_compare(_PS_VERSION_, '1.4.5.1', '<=')) {
        include _PS_ROOT_DIR_ . '/modules/piwikanalyticsmanager/_classes/backward_compatibility/global.php';
    } else {
        require_once dirname(__FILE__) . '/_classes/backward_compatibility/global.php';
    }
}
require_once dirname(__FILE__) . '/_classes/MyHelperClass.php';
require_once dirname(__FILE__) . '/_classes/PKHelper.php';

class piwikanalyticsmanager extends Module {

    protected $_errors = "";
    protected $piwikSite = "";
    protected $default_currency = array();
    protected $currencies = array();

    public function __construct($name = null, $context = null) {
        $this->name = 'piwikanalyticsmanager';
        $this->tab = 'administration';
        $this->version = '1b';
        $this->author = 'Christian M. Jensen';
        $this->displayName = 'Piwik Analytics Site Manager & Base classes';

        $this->bootstrap = true;
        if (_PS_VERSION_ < '1.5')
            parent::__construct($name);
        /* Prestashop 1.5 and up implements "$context" */
        if (_PS_VERSION_ >= '1.5')
            parent::__construct($name, ($context instanceof Context ? $context : NULL));

        $this->description = $this->l('Piwik Analytics Site Manager & Base classes');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this plugin ?');

        if (_PS_VERSION_ < '1.5')
            PKHelper::$_module = & $this;

        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            if (version_compare(_PS_VERSION_, '1.4.5.1', '<=')) {
                include _PS_ROOT_DIR_ . '/modules/piwikanalyticsmanager/_classes/backward_compatibility/backward.php';
            } else {
                require dirname(__FILE__) . '/_classes/backward_compatibility/backward.php';
            }
        }
        $this->_errors = PKHelper::$errors = PKHelper::$error = "";
        $this->__setCurrencies();
    }

    /**
     * get content to display in the admin area
     * @global string $currentIndex
     * @return string
     */
    public function getContent() {
        if (_PS_VERSION_ < '1.5')
            global $currentIndex;
        if (Tools::getIsset('pkapicall')) {
            $this->__pkapicall();
            die();
        }
        $_html = "";
        $_html .= $this->processFormsUpdate();
        $this->piwikSite = PKHelper::getPiwikSite2();
        $this->displayErrors(PKHelper::$errors);
        PKHelper::$errors = PKHelper::$error = "";

        $helper = MyHelperClass::GetHelperFormObject($this, $this->name, $this->identifier, Tools::getAdminTokenLite('AdminModules'));

        if (_PS_VERSION_ < '1.5')
            $helper->currentIndex = $currentIndex . '&configure=' . $this->name;
        else
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitUpdate' . $this->name;

        $fields_form = array();
        $fields_form[0]['form']['legend'] = array(
            'title' => $this->l('Piwik Analytics - Piwik Url, Token'),
            'image' => (_PS_VERSION_ < '1.5' ? $this->_path . 'logo.gif' : $this->_path . 'logo.png')
        );

        if ($this->piwikSite !== FALSE) {
            $fields_form[0]['form']['input'][] = array(
                'type' => 'html',
                'name' => $this->l('Based on the settings you provided this is the info i get from Piwik!') . "<br>"
                . "<strong>" . $this->l('Name') . "</strong>: <i>{$this->piwikSite[0]->name}</i><br>"
                . "<strong>" . $this->l('Main Url') . "</strong>: <i>{$this->piwikSite[0]->main_url}</i><br>"
            );
        }

        /* FORM FOR TOKEN, PIWIK URL AND SITE ID */
        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik Host'),
            'name' => PKHelper::CPREFIX . 'HOST',
            'desc' => $this->l('Example: www.example.com/piwik/ (without protocol and with / at the end!)'),
            'hint' => $this->l('The host where your piwik is installed.!'),
            'required' => true
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik site id'),
            'name' => PKHelper::CPREFIX . 'SITEID',
            'desc' => $this->l('Example: 10'),
            'hint' => $this->l('You can find your piwik site id by loggin to your piwik installation.'),
            'required' => true
        );

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Piwik token auth'),
            'name' => PKHelper::CPREFIX . 'TOKEN_AUTH',
            'desc' => $this->l('You can find your piwik token by loggin to your piwik installation. under API'),
            'required' => true
        );

        $fields_form[0]['form']['submit'] = array(
            'title' => $this->l('Save'),
        );

        /* FORM FOR PIWIK SITES MANAGEMENT */

        $fields_form[1]['form']['legend'] = array(
            'title' => $this->l('Piwik Analytics - Site Manager'),
            'image' => (_PS_VERSION_ < '1.5' ? $this->_path . 'logo.gif' : $this->_path . 'logo.png')
        );
        if ((_PS_VERSION_ >= '1.5') && ($this->piwikSite !== FALSE)) {
            $this->context->controller->addJqueryPlugin('tagify');
            $this->context->controller->addJqueryUI('autocomplete');

            ## Input select: Piwik Site ##
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

            $fields_form[1]['form']['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Piwik Site'),
                'name' => 'SPKSID',
                'desc' => sprintf($this->l('Based on your settings your default site is %s'), $this->piwikSite[0]->idsite),
                'options' => array(
                    'default' => $pksite_default,
                    'query' => $pksites,
                    'id' => 'pkid',
                    'name' => 'name'
                ),
                'onchange' => 'return ChangePKSiteEdit(this.value)',
            );

            ## Info / hidden siteid field ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'html',
                'name' => $this->l('In this section you can modify your settings in piwik just so you don\'t have to login to Piwik to do this') . "<br>"
                . "<strong>" . $this->l('Currently selected name') . "</strong>: <i id='wnamedsting'>{$this->piwikSite[0]->name}</i><br>"
                . "<input type=\"hidden\" name=\"PKAdminIdSite\" id=\"PKAdminIdSite\" value=\"{$this->piwikSite[0]->idsite}\" />"
            );
            $fields_form[1]['form']['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Piwik Site Name'),
                'name' => 'PKAdminSiteName',
                'desc' => $this->l('Name of this site in Piwik'),
            );

            ## Input site urls. ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'tags',
                'label' => $this->l('Site urls'),
                'name' => 'PKAdminSiteUrls',
                'id' => 'PKAdminSiteUrls',
            );

            ## Ecommerce active ? ##
            $fields_form[1]['form']['input'][] = array(
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
            );

            ## Search active ? ##
            $fields_form[1]['form']['input'][] = array(
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
            );

            ## Search Keyword Parameters ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'tags',
                'label' => $this->l('Search Keyword Parameters'),
                'name' => 'PKAdminSearchKeywordParameters',
            );

            ## Search Category Parameters ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'tags',
                'label' => $this->l('Search Category Parameters'),
                'name' => 'PKAdminSearchCategoryParameters',
            );

            ## Excluded ip addresses ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'tags',
                'label' => $this->l('Excluded ip addresses'),
                'name' => 'PKAdminExcludedIps',
                'desc' => $this->l('ip addresses excluded from tracking, separated by comma ","'),
            );

            ## Excluded Query Parameters ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'tags',
                'label' => $this->l('Excluded Query Parameters'),
                'name' => 'PKAdminExcludedQueryParameters',
                'desc' => $this->l('please read: http://piwik.org/faq/how-to/faq_81/'),
            );

            ## Website group ##
            $pkGroups = PKHelper::getSitesGroups();
            $pkGroup_default = array('value' => "", 'label' => $this->l('Choose Piwik site'));
            $pkGroup = array();
            foreach ($pkGroups as $key => $value) {
                if (!empty($value))
                    $pkGroup[] = array('pkgid' => $key,'pkgname' => $value);
            }
            
            $fields_form[1]['form']['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Website group'),
                'name' => 'PKAdminGroup',
                'desc' => "<a href=\"#\" onclick=\"$('select#PKAdminGroup').after('<input name=\'PKAdminGroup\' id=\'PKAdminGroup\' value=\'\' type=\'text\'>');$('select#PKAdminGroup').remove(); return false;\" title=\"{$this->l('Click here add new website group')}\"><i class=\"icon-plus\"></i></a>&nbsp;-&nbsp;".$this->l('Requires plugin "WebsiteGroups" before it can be used from within Piwik'),
                'options' => array(
                    'default' => $pkGroup_default,
                    'query' => $pkGroup,
                    'id' => 'pkgid',
                    'name' => 'pkgname'
                ),
            );
            unset($pkGroups, $pkGroup_default, $pkGroup);

            ## Piwik Timezone ##

            $pktimezone_default = array('value' => 0, 'label' => $this->l('Choose Timezone'));
            $pktimezones = array();
            $tmp = PKHelper::getTimezonesList();
            $this->displayErrors(PKHelper::$errors);
            PKHelper::$errors = PKHelper::$error = "";
            foreach ($tmp as $key => $pktz) {
                if (!isset($pktimezones[$key]))
                    $pktimezones[$key] = array('name' => $this->l($key), 'query' => array());
                foreach ($pktz as $pktzK => $pktzV) {
                    $pktimezones[$key]['query'][] = array(
                        'tzId' => $pktzK,
                        'tzName' => $pktzV,
                    );
                }
            }
            unset($tmp, $pktz, $pktzV, $pktzK);
            $fields_form[1]['form']['input'][] = array(
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
            );

            ## Currency stting ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Currency'),
                'name' => 'PKAdminCurrency',
                'desc' => sprintf($this->l('Based on your settings in Piwik your default currency is %s'), $this->piwikSite[0]->currency)
                . '<br>' . $this->l('Note: only currencies installed in prestashop is listed here.'),
                'options' => array(
                    'default' => $this->default_currency,
                    'query' => $this->currencies,
                    'id' => 'iso_code',
                    'name' => 'name'
                ),
            );

            ## Excluded User Agents ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'textarea',
                'label' => $this->l('Excluded User Agents'),
                'name' => 'PKAdminExcludedUserAgents',
                'rows' => 10,
                'cols' => 50,
                'desc' => $this->l('please read: http://piwik.org/faq/how-to/faq_17483/'),
            );

            ## Keep URL Fragments ##
            $fields_form[1]['form']['input'][] = array(
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
            );

            ## Javascript / ajax proseccing ##
            $fields_form[1]['form']['input'][] = array(
                'type' => 'html',
                'name' => "<button onclick=\"return submitPiwikSiteAPIUpdate()\" id=\"submitUpdatePiwikAdmSite\" class=\"btn btn-default pull-left\" name=\"submitUpdatePiwikAdmSite\" value=\"1\" type=\"button\"><i class=\"process-icon-save\"></i>" . $this->l('Save') . "</button>"
                . "<script type=\"text/javascript\">"
                . "function submitPiwikSiteAPIUpdate(){\n"
                . "    var idSite = $('#PKAdminIdSite').val();\n"
                . "    var siteName = $('#PKAdminSiteName').val();\n"
                . "    var ecommerce = $('input[name=PKAdminEcommerce]:checked').val();\n"
                . "    var siteSearch = $('input[name=PKAdminSiteSearch]:checked').val();\n"
                . "    if(typeof(jQuery.ui.tagify) !== 'undefined') {\n"
                . "        var urls = $('#PKAdminSiteUrls').tagify('serialize');\n"
                . "        var excludedIps = $('#PKAdminExcludedIps').tagify('serialize');\n"
                . "        var searchKeywordParameters = $('#PKAdminSearchKeywordParameters').tagify('serialize');\n"
                . "        var searchCategoryParameters = $('#PKAdminSearchCategoryParameters').tagify('serialize');\n"
                . "        var excludedQueryParameters = $('#PKAdminExcludedQueryParameters').tagify('serialize');\n"
                . "    } else {"
                . "        var urls = $('#PKAdminSiteUrls').val();\n"
                . "        var excludedIps = $('#PKAdminExcludedIps').val();\n"
                . "        var searchKeywordParameters = $('#PKAdminSearchKeywordParameters').val();\n"
                . "        var searchCategoryParameters = $('#PKAdminSearchCategoryParameters').val();\n"
                . "        var excludedQueryParameters = $('#PKAdminExcludedQueryParameters').val();\n"
                . "    }\n"
                . "    var timezone = $('#PKAdminTimezone').val();\n"
                . "    var currency = $('#PKAdminCurrency').val();\n"
                . "    var group = $('#PKAdminGroup').val();\n"
                . "    /*var startDate = $('#PKAdminStartDate').val();*/\n"
                . "    var excludedUserAgents = $('#PKAdminExcludedUserAgents').val();\n"
                . "    var keepURLFragments = $('input[name=PKAdminKeepURLFragments]:checked').val();\n"
                . "    /*var type = $('#PKAdminSiteType').val();*/\n"
                . "    \n"
                . "    $.ajax({\n"
                . "        type: 'POST',\n"
                . "        url: '" . $helper->currentIndex . "&token=" . $helper->token . "',\n"
                . "        dataType: 'json',\n"
                . "        data: {\n"
                . "            'pkapicall': 'updatePiwikSite',\n"
                . "            'ajax': 1,\n"
                . "            'idSite': idSite,\n"
                . "            'siteName': siteName,\n"
                . "            'urls': urls,\n"
                . "            'ecommerce': ecommerce,\n"
                . "            'siteSearch': siteSearch,\n"
                . "            'searchKeywordParameters': searchKeywordParameters,\n"
                . "            'searchCategoryParameters': searchCategoryParameters,\n"
                . "            'excludedIps': excludedIps,\n"
                . "            'excludedQueryParameters': excludedQueryParameters,\n"
                . "            'timezone': timezone,\n"
                . "            'currency': currency,\n"
                . "            'keepURLFragments': keepURLFragments,\n"
                . "            'group': group,\n"
                . "            'excludedUserAgents': excludedUserAgents,\n"
                . "        },\n"
                . "        beforeSend: function(){\n"
                . "            showLoadingStuff();\n"
                . "        },\n"
                . "        success: function(data) {\n"
                . "                jAlert(data.message);\n"
                . "        },\n"
                . "        error: function(XMLHttpRequest, textStatus, errorThrown){\n"
                . "            jAlert(\"Error while saving Piwik Data\\n\\ntextStatus: '\" + textStatus + \"'\\nerrorThrown: '\" + errorThrown + \"'\\nresponseText:\\n\" + XMLHttpRequest.responseText);\n"
                . "        },\n"
                . "        complete: function(){\n"
                . "            hideLoadingStuff();\n"
                . "        }\n"
                . "    });\n"
                . "    \n"
                . "    return false;\n"
                . "}\n"
                . "\n"
                . ( (_PS_VERSION_ >= '1.5') ?
                        "function hideLoadingStuff() { $('#ajax_running').hide('fast'); clearTimeout(ajax_running_timeout); $.fancybox.helpers.overlay.close(); $.fancybox.hideLoading(); }\n"
                        . "function showLoadingStuff() { showAjaxOverlay(); $.fancybox.helpers.overlay.open({parent: $('body')}); $.fancybox.showLoading(); }\n" :
                        "function hideLoadingStuff() {  }\n"
                        . "function showLoadingStuff() {  }\n"
                )
                . "\n"
                . "function ChangePKSiteEdit(id){\n"
                . "    $.ajax({\n"
                . "        type: 'POST',\n"
                . "        url: '" . $helper->currentIndex . "&token=" . $helper->token . "',\n"
                . "        dataType: 'json',\n"
                . "        data: {\n"
                . "            'pkapicall': 'getPiwikSite2',\n"
                . "            'idSite': id,\n"
                . "        },\n"
                . "        beforeSend: function(){\n"
                . "            showLoadingStuff();\n"
                . "        },\n"
                . "        success: function(data) {\n"
                /* . "            $('#SPKSID').val(data.message[0].idSite);\n" */
                . "            $('#PKAdminIdSite').val(data.message[0].idsite);\n"
                . "            $('#PKAdminSiteName').val(data.message[0].name);\n"
                . "            $('#wnamedsting').text(data.message[0].name);\n"
                . "            if(typeof(jQuery.ui.tagify) !== 'undefined') {\n"
                . "                $('#PKAdminSiteUrls').val(\"\");\n"
                . "                $('#PKAdminSiteUrls').tagify('destroy');\n"
                . "                $('#PKAdminSiteUrls').tagify({delimiters: [13,44], addTagPrompt: '{$this->l('Add URL')}'});\n"
                . "                if(data.message[0].main_url !== '') {\n"
                . "                    var _urls = data.message[0].main_url.split(',')\n"
                . "                    for (var i = 0; i < _urls.length; i++) {\n"
                . "                        $('#PKAdminSiteUrls').tagify('add',_urls[i]);\n"
                . "                    };\n"
                . "                };\n"
                . "            } else {"
                . "                $('#PKAdminSiteUrls').val(data.message[0].main_url);\n"
                . "            }\n"
                . ( (_PS_VERSION_ >= '1.6') ?
                        "            if(data.message[0].ecommerce===1){\n"
                        . "                $('#PKAdminEcommerce_on').prop('checked', true);\n"
                        . "                $('#PKAdminEcommerce_off').prop('checked', false);\n"
                        . "            } else {\n"
                        . "                $('#PKAdminEcommerce_off').prop('checked', true);\n"
                        . "                $('#PKAdminEcommerce_on').prop('checked', false);\n"
                        . "            }\n"
                        . "            if(data.message[0].sitesearch===1){\n"
                        . "                $('#PKAdminSiteSearch_on').prop('checked', true);\n"
                        . "                $('#PKAdminSiteSearch_off').prop('checked', false);\n"
                        . "            } else {\n"
                        . "                $('#PKAdminSiteSearch_off').prop('checked', true);\n"
                        . "                $('#PKAdminSiteSearch_on').prop('checked', false);\n"
                        . "            }\n" :
                        "            if(data.message[0].ecommerce===1){\n"
                        . "                $('input[id=active_on][name=PKAdminEcommerce]').prop('checked', true);\n"
                        . "                $('input[id=active_off][name=PKAdminEcommerce]').prop('checked', false);\n"
                        . "            } else {\n"
                        . "                $('input[id=active_off][name=PKAdminEcommerce]').prop('checked', true);\n"
                        . "                $('input[id=active_on][name=PKAdminEcommerce]').prop('checked', false);\n"
                        . "            }\n"
                        . "            if(data.message[0].sitesearch===1){\n"
                        . "                $('input[id=active_on][name=PKAdminSiteSearch]').prop('checked', true);\n"
                        . "                $('input[id=active_off][name=PKAdminSiteSearch]').prop('checked', false);\n"
                        . "            } else {\n"
                        . "                $('input[id=active_off][name=PKAdminSiteSearch]').prop('checked', true);\n"
                        . "                $('input[id=active_on][name=PKAdminSiteSearch]').prop('checked', false);\n"
                        . "            }\n"
                )
                . "            if(typeof(jQuery.ui.tagify) !== 'undefined') {\n"
                . "                $('#PKAdminSearchKeywordParameters').val(\"\"); $('#PKAdminSearchKeywordParameters').tagify('destroy'); $('#PKAdminSearchKeywordParameters').tagify({delimiters: [13,44], addTagPrompt: '{$this->l('Add Search Keyword')}'});\n"
                . "                if(data.message[0].sitesearch_keyword_parameters !== '') {\n"
                . "                    var _sskp = data.message[0].sitesearch_keyword_parameters.split(',')\n"
                . "                    for (var i = 0; i < _sskp.length; i++) {\n"
                . "                        $('#PKAdminSearchKeywordParameters').tagify('add',_sskp[i]);\n"
                . "                    };\n"
                . "                };\n"
                . "                $('#PKAdminSearchCategoryParameters').val(\"\"); $('#PKAdminSearchCategoryParameters').tagify('destroy'); $('#PKAdminSearchCategoryParameters').tagify({delimiters: [13,44], addTagPrompt: '{$this->l('Add Search Category')}'});\n"
                . "                if(data.message[0].sitesearch_category_parameters !== '') {\n"
                . "                    var _sscp = data.message[0].sitesearch_category_parameters.split(',')\n"
                . "                    for (var i = 0; i < _sscp.length; i++) {\n"
                . "                        $('#PKAdminSearchCategoryParameters').tagify('add',_sscp[i]);\n"
                . "                    };\n"
                . "                };\n"
                . "                $('#PKAdminExcludedIps').val(\"\"); $('#PKAdminExcludedIps').tagify('destroy'); $('#PKAdminExcludedIps').tagify({delimiters: [13,44], addTagPrompt: '{$this->l('Add IP')}'});\n"
                . "                if(data.message[0].excluded_ips !== '') {\n"
                . "                    var _ips = data.message[0].excluded_ips.split(',')\n"
                . "                    for (var i = 0; i < _ips.length; i++) {\n"
                . "                        $('#PKAdminExcludedIps').tagify('add',_ips[i]);\n"
                . "                    };\n"
                . "                };\n"
                . "                $('#PKAdminExcludedQueryParameters').val(\"\"); $('#PKAdminExcludedQueryParameters').tagify('destroy'); $('#PKAdminExcludedQueryParameters').tagify({delimiters: [13,44], addTagPrompt: '{$this->l('Add Excluded Query')}'});\n"
                . "                if(data.message[0].excluded_parameters !== '') {\n"
                . "                    var _eqp = data.message[0].excluded_parameters.split(',')\n"
                . "                    for (var i = 0; i < _eqp.length; i++) {\n"
                . "                        $('#PKAdminExcludedQueryParameters').tagify('add',_eqp[i]);\n"
                . "                    };\n"
                . "                };\n"
                . "            } else {\n"
                . "                $('#PKAdminSearchCategoryParameters').val(data.message[0].sitesearch_category_parameters);\n"
                . "                $('#PKAdminSearchKeywordParameters').val(data.message[0].sitesearch_keyword_parameters);\n"
                . "                $('#PKAdminExcludedIps').val(data.message[0].excluded_ips);\n"
                . "                $('#PKAdminExcludedQueryParameters').val(data.message[0].excluded_parameters);\n"
                . "            }\n"
                . "            $('#PKAdminTimezone').val(data.message[0].timezone);\n"
                . "            $('#PKAdminCurrency').val(data.message[0].currency);\n"
                . "            $('#PKAdminGroup').val(data.message[0].group);\n"
                . "            /*$('#PKAdminStartDate').val(data.message[0].ts_created);*/\n"
                . "            $('#PKAdminExcludedUserAgents').val(data.message[0].excluded_user_agents);\n"
                . ( (_PS_VERSION_ >= '1.6') ?
                        "            if(data.message[0].keep_url_fragment===1){\n"
                        . "                $('#PKAdminKeepURLFragments_on').prop('checked', true);\n"
                        . "                $('#PKAdminKeepURLFragments_off').prop('checked', false);\n"
                        . "            } else {\n"
                        . "                $('#PKAdminKeepURLFragments_off').prop('checked', true);\n"
                        . "                $('#PKAdminKeepURLFragments_on').prop('checked', false);\n"
                        . "            }\n" :
                        "            if(data.message[0].keep_url_fragment===1){\n"
                        . "                $('input[id=active_on][name=PKAdminKeepURLFragments]').prop('checked', true);\n"
                        . "                $('input[id=active_off][name=PKAdminKeepURLFragments]').prop('checked', false);\n"
                        . "            } else {\n"
                        . "                $('input[id=active_off][name=PKAdminKeepURLFragments]').prop('checked', true);\n"
                        . "                $('input[id=active_on][name=PKAdminKeepURLFragments]').prop('checked', false);\n"
                        . "            }\n"
                )
                . "            /*$('#PKAdminSiteType').val(data.message[0].type);*/\n"
                . "        },\n"
                . "        error: function(XMLHttpRequest, textStatus, errorThrown){\n"
                . "            jAlert(\"Error while saving Piwik Data\\n\\ntextStatus: '\" + textStatus + \"'\\nerrorThrown: '\" + errorThrown + \"'\\nresponseText:\\n\" + XMLHttpRequest.responseText);\n"
                . "        },\n"
                . "        complete: function(){\n"
                . "            hideLoadingStuff();\n"
                . "        }\n"
                . "    });\n"
                . "    \n"
                . "}\n"
                . "</script>"
            );
        } else {
            $fields_form[1]['form']['input'][] = array(
                'type' => 'html',
                'name' => $this->l("I'm not able to connect to Piwik API.")
            );
        }

        $helper->fields_value = $this->getFormFields();
        return $this->_errors . $_html . $helper->generateForm($fields_form);
    }

    /**
     * this methods is uses to set and array of errors
     * into variable "$_errors" as string, calling "$this->displayError($value)"
     * for each value in the array
     * @param array $errors
     */
    public function displayErrors($errors) {
        if (!empty($errors)) {
            foreach ($errors as $key => $value) {
                $this->_errors .= $this->displayError($value);
            }
        }
    }

    /**
     * this method is called with the admin form is submitted
     * it validates and saves the inputs
     * @return string
     */
    private function processFormsUpdate() {
        $result = "";
        if (Tools::isSubmit('submitUpdate' . $this->name)) {

            if (Tools::getIsset(PKHelper::CPREFIX . 'HOST')) {
                $tmp = Tools::getValue(PKHelper::CPREFIX . 'HOST', '');
                if (!empty($tmp) && (filter_var($tmp, FILTER_VALIDATE_URL) || filter_var('http://' . $tmp, FILTER_VALIDATE_URL))) {
                    $tmp = str_replace(array('http://', 'https://', '//'), "", $tmp);
                    if (substr($tmp, -1) != "/") {
                        $tmp .= "/";
                    }
                    Configuration::updateValue(PKHelper::CPREFIX . 'HOST', $tmp);
                } else {
                    $result .= $this->displayError($this->l('Piwik host is not valid or is empty'));
                }
            }

            if (Tools::getIsset(PKHelper::CPREFIX . 'SITEID')) {
                $tmp = (int) Tools::getValue(PKHelper::CPREFIX . 'SITEID', 0);
                Configuration::updateValue(PKHelper::CPREFIX . 'SITEID', $tmp);
                if ($tmp <= 0) {
                    $result .= $this->displayError($this->l('Piwik site id is lower or equal to "0"'));
                }
            }

            if (Tools::getIsset(PKHelper::CPREFIX . 'TOKEN_AUTH')) {
                $tmp = Tools::getValue(PKHelper::CPREFIX . 'TOKEN_AUTH', '');
                Configuration::updateValue(PKHelper::CPREFIX . 'TOKEN_AUTH', $tmp);
                if (empty($tmp)) {
                    $result .= $this->displayError($this->l('Piwik auth token is empty'));
                }
            }
        }
        return $result;
    }

    /**
     * Create calls to Piwik api based on methods from class PKHelper
     * Note: this method is to be called from ajax and outputs json encoded values
     */
    private function __pkapicall() {
        $apiMethod = Tools::getValue('pkapicall');
        if (method_exists('PKHelper', $apiMethod) && isset(PKHelper::$acp[$apiMethod])) {
            $required = PKHelper::$acp[$apiMethod]['required'];
            // $optional = PKHelper::$acp[$apiMethod]['optional'];
            $order = PKHelper::$acp[$apiMethod]['order'];
            foreach ($required as $requiredOption) {
                if (!Tools::getIsset($requiredOption)) {
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
            $result = call_user_func_array(array('PKHelper', $apiMethod), $order);
            if ($result === FALSE) {
                die(Tools::jsonEncode(array('error' => TRUE, 'message' => $this->l('Unknown error occurred'))));
            } else {
                if (is_array($result) && isset($result[0])) {
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
     * get form fields
     * @return array
     */
    private function getFormFields() {
        return array(
            PKHelper::CPREFIX . 'HOST' => Configuration::get(PKHelper::CPREFIX . 'HOST'),
            PKHelper::CPREFIX . 'SITEID' => Configuration::get(PKHelper::CPREFIX . 'SITEID'),
            PKHelper::CPREFIX . 'TOKEN_AUTH' => Configuration::get(PKHelper::CPREFIX . 'TOKEN_AUTH'),
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
            'PKAdminSiteUrls' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->main_url : ''),
            'PKAdminExcludedUserAgents' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->excluded_user_agents : ''),
            'PKAdminKeepURLFragments' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->keep_url_fragment : 0),
            'PKAdminSiteType' => ($this->piwikSite !== FALSE ? $this->piwikSite[0]->type : 'website'),
        );
    }

    /**
     * set currencies variables "$currencies" AND "$default_currency"
     * with values of currencies installed in prestashop
     * NOTE: $default_currency is used in select input and is not a currency
     */
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

}
