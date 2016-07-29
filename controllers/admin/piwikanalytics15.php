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
 * 
 * Used for Prestashop version
 *  - 1.5.0.5
 *  - 1.5.0.13
 */
if (!class_exists('PKHelper',false)) {
    require_once dirname(__FILE__).'/../../PKHelper.php';
}

class PiwikAnalytics15Controller extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->action = 'view';
        $this->display = 'content';
        $this->template = 'content.tpl';
        $this->tpl_folder = _PS_MODULE_DIR_.'piwikanalyticsjs/views/templates/admin/PiwikAnalytics/';
        $this->tpl_folder_theme = _PS_THEME_DIR_.'modules/piwikanalyticsjs/views/templates/admin/PiwikAnalytics/';
        
        $_module = Module::getInstanceByName('piwikanalyticsjs');
        if ($_module->id) {
            if (version_compare(_PS_VERSION_,'1.5.0.13',"<="))
                PKHelper::$_module = $_module;
        }
    }

    public function initToolbar() {
        /* remove toolbar */
    }

    public function displayError($error) {
        $output = '
		<div class="module_error alert error">
			<img src="'._PS_IMG_.'admin/warning.gif" alt="" title="" /> '.$error.'
		</div>';
        return $output;
    }

    private function lookupauthtoken() {
        $content = "";
        $error = "";
        if (Tools::getIsset('PKLOOKUPTOKENHOST') &&
                Tools::getIsset('PKLOOKUPTOKENUSRNAME') &&
                Tools::getIsset('PKLOOKUPTOKENUSRPASSWD')) {
            PKHelper::$piwikHost = Tools::getValue('PKLOOKUPTOKENHOST');

            if (Tools::getIsset('PKLOOKUPTOKENSAVEUSRPWD',false)) {
                Configuration::updateValue(PKHelper::CPREFIX.'USRNAME',Tools::getValue('PKLOOKUPTOKENUSRNAME',''));
                Configuration::updateValue(PKHelper::CPREFIX.'USRPASSWD',Tools::getValue('PKLOOKUPTOKENUSRPASSWD',''));
            }

            PKHelper::$httpAuthUsername = Tools::getValue('PKLOOKUPTOKENPAUTHUSR','');
            PKHelper::$httpAuthPassword = Tools::getValue('PKLOOKUPTOKENPAUTHPWD','');
            Configuration::updateValue(PKHelper::CPREFIX.'PAUTHUSR',PKHelper::$httpAuthUsername);
            Configuration::updateValue(PKHelper::CPREFIX.'PAUTHPWD',PKHelper::$httpAuthPassword);

            if ($token = PKHelper::getTokenAuth(Tools::getValue('PKLOOKUPTOKENUSRNAME'),Tools::getValue('PKLOOKUPTOKENUSRPASSWD'),NULL)) {
                Configuration::updateValue(PKHelper::CPREFIX.'TOKEN_AUTH',$token);
                $this->context->smarty->assign(array('piwikToken' => $token));
            } else {
                foreach (PKHelper::$errors as $key => $value) {
                    $error .= $this->displayError($value);
                }
            }
        }

        $this->context->smarty->assign(array(
            'piwik_host' => Tools::getValue('piwik_host',Tools::getValue('PKLOOKUPTOKENHOST')),
            'piwik_user' => Tools::getValue('piwik_user',Tools::getValue('PKLOOKUPTOKENUSRNAME','')),
            'piwik_passwd' => Tools::getValue('piwik_user',Tools::getValue('PKLOOKUPTOKENUSRPASSWD','')),
            'piwik_auser' => Tools::getValue('PKLOOKUPTOKENPAUTHUSR',''),
            'piwik_apasswd' => Tools::getValue('PKLOOKUPTOKENPAUTHPWD',''),
        ));
        if (version_compare(_PS_VERSION_,'1.5.0.5',">=") && version_compare(_PS_VERSION_,'1.5.3.999',"<=")) {
            $this->context->smarty->assign(array('piwikAnalyticsControllerLink' => $this->context->link->getAdminLink('PiwikAnalytics15')));
        } else {
            $this->context->smarty->assign(array('piwikAnalyticsControllerLink' => $this->context->link->getAdminLink('AdminPiwikAnalytics')));
        }

        if (file_exists($this->tpl_folder_theme.'lookupauthtoken.tpl'))
            $content .= $this->context->smarty->fetch($this->tpl_folder_theme.'lookupauthtoken.tpl');
        else
            $content .= $this->context->smarty->fetch($this->tpl_folder.'lookupauthtoken.tpl');

        die($content.$error);
    }

    public function init() {
        if (Tools::getValue('ajax'))
            $this->ajax = '1';

        /* Server Params */
        $protocol_link = (Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
        $protocol_content = (isset($useSSL) && $useSSL && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
        $this->context->link = new Link($protocol_link,$protocol_content);

        $this->timerStart = microtime(true);


        if ($this->ajax && Tools::getIsset('action')) {
            $action = Tools::getIsset('action');
            if ($action == "lookupauthtoken") {
                $this->lookupauthtoken();
            }
        }


        $http = ((bool)Configuration::get(PKHelper::CPREFIX.'CRHTTPS') ? 'https://' : 'http://');
        $PIWIK_HOST = Configuration::get(PKHelper::CPREFIX.'HOST');
        $PIWIK_SITEID = (int)Configuration::get(PKHelper::CPREFIX.'SITEID');

        $this->context->smarty->assign('help_link','https://github.com/cmjnisse/piwikanalyticsjs-prestashop/wiki');
        $user = Configuration::get(PKHelper::CPREFIX.'USRNAME');
        $passwd = Configuration::get(PKHelper::CPREFIX.'USRPASSWD');
        if ((!empty($user) && $user !== FALSE) && (!empty($passwd) && $passwd !== FALSE)) {
            $this->page_header_toolbar_btn['stats'] = array(
                'href' => $http.$PIWIK_HOST.'index.php?module=Login&action=logme&login='.$user.'&password='.md5($passwd).'&idSite='.$PIWIK_SITEID,
                'desc' => $this->l('Piwik'),
                'target' => true
            );
        } else {
            $this->page_header_toolbar_btn['stats'] = array(
                'href' => $http.$PIWIK_HOST.'index.php',
                'desc' => $this->l('Piwik'),
                'target' => true
            );
        }

        // Some controllers use the view action without an object
        if ($this->className)
            $this->loadObject(true);


        $PIWIK_TOKEN_AUTH = Configuration::get(PKHelper::CPREFIX.'TOKEN_AUTH');
        if ((empty($PIWIK_HOST) || $PIWIK_HOST === FALSE) ||
                ($PIWIK_SITEID <= 0 || $PIWIK_SITEID === FALSE) ||
                (empty($PIWIK_TOKEN_AUTH) || $PIWIK_TOKEN_AUTH === FALSE)) {

            $this->content .= "<h3 style=\"padding: 90px;\">{$this->l("You need to set 'Piwik host url', 'Piwik token auth' and 'Piwik site id', and save them before the dashboard can be shown here")}</h3>";
        } else {
            $this->content .= <<< EOF
<script type="text/javascript">
  function WidgetizeiframeDashboardLoaded() {
      var w = $('#content').width();
      var h = $('body').height();
      $('#WidgetizeiframeDashboard').width('100%');
      $('#WidgetizeiframeDashboard').height(h);
  }
</script>   
EOF;
            $lng = new LanguageCore($this->context->cookie->id_lang);

            if (_PS_VERSION_ < '1.6')
                $this->content .= '<h3><a target="_blank" href="'.$this->page_header_toolbar_btn['stats']['href'].'">'.$this->page_header_toolbar_btn['stats']['desc'].'</a> | <a target="_blank" href="https://github.com/cmjnisse/piwikanalyticsjs-prestashop/wiki">'.$this->l('Help').'</a></h3>';

            $DREPDATE = Configuration::get(PKHelper::CPREFIX.'DREPDATE');
            if ($DREPDATE !== FALSE && (strpos($DREPDATE,'|') !== FALSE)) {
                list($period,$date) = explode('|',$DREPDATE);
            } else {
                $period = "day";
                $date = "today";
            }

            $http_auth = "";
            $http_user = Configuration::get(PKHelper::CPREFIX.'PAUTHUSR');
            $http_password = Configuration::get(PKHelper::CPREFIX.'PAUTHPWD');
            if ((!empty($http_user) && strlen($http_user) > 1) &&
                    (!empty($http_password) && strlen($http_password) > 1))
                $http_auth = "{$http_user}:{$http_password}@";

            $this->content .= ''
                    .'<iframe id="WidgetizeiframeDashboard"  onload="WidgetizeiframeDashboardLoaded();" '
                    .'src="'.$http.$http_auth
                    .$PIWIK_HOST.'index.php'
                    .'?module=Widgetize'
                    .'&action=iframe'
                    .'&moduleToWidgetize=Dashboard'
                    .'&actionToWidgetize=index'
                    .'&idSite='.$PIWIK_SITEID
                    .'&period='.$period
                    .'&token_auth='.$PIWIK_TOKEN_AUTH
                    .'&language='.$lng->iso_code
                    .'&date='.$date
                    .'" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="550px"></iframe>';
        }

        $this->context->smarty->assign(array(
            'content' => $this->content,
            'show_page_header_toolbar' => (isset($this->show_page_header_toolbar) ? $this->show_page_header_toolbar : ''),
            'page_header_toolbar_title' => (isset($this->page_header_toolbar_title) ? $this->page_header_toolbar_title : ''),
            'page_header_toolbar_btn' => (isset($this->page_header_toolbar_btn) ? $this->page_header_toolbar_btn : ''),
        ));
    }

}
