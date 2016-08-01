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
if (!class_exists('PiwikAnalyticsjsConfiguration',false)) {
    require_once dirname(__FILE__).'/../../PiwikAnalyticsjsConfiguration.php';
}
if (!class_exists('PKHelper',false)) {
    require_once dirname(__FILE__).'/../../PKHelper.php';
}

class PiwikAnalytics15Controller extends ModuleAdminController {

    /** @var piwikanalyticsjs */
    private $module = null;

    public function __construct() {
        parent::__construct();
        $this->action = 'view';
        $this->display = 'content';
        $this->template = 'content.tpl';
        $this->tpl_folder = _PS_MODULE_DIR_.'piwikanalyticsjs/views/templates/admin/PiwikAnalytics/';
        $this->tpl_folder_theme = _PS_THEME_DIR_.'modules/piwikanalyticsjs/views/templates/admin/PiwikAnalytics/';

        $this->module = Module::getInstanceByName('piwikanalyticsjs');
        if ($this->module->id) {
            if (version_compare(_PS_VERSION_,'1.5.0.13',"<="))
                PKHelper::$_module = & $this->module;
        }
    }

    private function validateconfiguration() {
        $content = "";
        $ps_version = _PS_VERSION_;
        $hooks = $this->module->config->getHooks();
        $result = array(
            'hooks' => array(),
            'config' => array(),
        );
        if (version_compare($ps_version,'1.5.9999.9999','<=')) {
            foreach ($hooks as $hook_name) {
                $result['hooks'][$hook_name] = false;
                if ($this->module->isRegisteredInHook($hook_name)) {
                    $result['hooks'][$hook_name] = true;
                }
            }
        }
        $this->context->smarty->assign(array(
            'section_hooks' => $result['hooks'],
        ));

        // check piwik connection
        $this->module->config->validate('piwik');
        $result = $this->module->config->validate_output;
        foreach ($result['piwik_connection']['errors'] as $key => & $value) {
            $value = $this->displayError($value);
        }
        $result['piwik_connection']['errors'] = implode('',$result['piwik_connection']['errors']);
        $piwikSite = null;
        if (empty($result['piwik_connection']['errors'])) {
            $piwikSite = PKHelper::getPiwikSite($this->module->config->siteid);
        }
        $this->module->config->validate_output = array();
        $this->context->smarty->assign(array(
            'section_piwik' => $result,
            'pksiteid' => $this->module->config->siteid,
            'pkhost' => $this->module->config->host,
        ));
        // check currency
        $currentcy = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $piwikSitecurrentcy = $this->module->config->CURRENCY_DEFAULT;
        if (isset($piwikSite[0]) && isset($piwikSite[0]->currency)) {
            $piwikSitecurrentcy = $piwikSite[0]->currency;
        }
        $this->context->smarty->assign(array(
            'pkCurrency' => $this->module->config->DEFAULT_CURRENCY,
            'pkShopCurrency' => $currentcy->iso_code,
            'pkSiteCurrency' => $piwikSitecurrentcy,
            'piwikCurrencyMatchesShop' => (bool)((strtolower($currentcy->iso_code) == strtolower($this->module->config->DEFAULT_CURRENCY)) && (strtolower($piwikSitecurrentcy) == strtolower($this->module->config->DEFAULT_CURRENCY))),
        ));
        unset($currentcy);

        // other settings
        $this->context->smarty->assign(array(
            'useHttps' => (bool)$this->module->config->use_https,
            'useProxy' => (bool)$this->module->config->use_proxy,
            'useDnt' => (bool)$this->module->config->dnt,
            'useCurl' => (bool)$this->module->config->use_curl,
        ));

        // check proxy
        if ((bool)$this->module->config->use_proxy) {
            $proxy_script = $this->module->config->proxy_script;
            $http = ((bool)$this->module->config->use_https ? 'https://' : 'http://');
            $http_auth = '';
            $http_user = $this->module->config->PAUTHUSR;
            $http_password = $this->module->config->PAUTHPWD;
            if (!empty($http_user) && !empty($http_password)) {
                $http_auth = "{$http_user}:{$http_password}@";
            }
            if ((bool)$this->module->config->use_curl) {
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$http.$proxy_script);
                curl_setopt($ch,CURLOPT_USERAGENT,(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : PKHelper::FAKEUSERAGENT));
                if (!empty($http_user) && !empty($http_password))
                    curl_setopt($ch,CURLOPT_USERPWD,$http_user.":".$http_password);
                curl_setopt($ch,CURLOPT_TIMEOUT,$this->module->config->timeout);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
                curl_setopt($ch,CURLOPT_FAILONERROR,true);
                $result = "";
                $proxy_script_response_header = false;
                if (($result = curl_exec($ch)) === false)
                    $proxy_script_response_header = curl_error($ch);
                curl_close($ch);


                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$http.$this->module->config->host.'piwik.js');
                curl_setopt($ch,CURLOPT_USERAGENT,(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : PKHelper::FAKEUSERAGENT));
                if (!empty($http_user) && !empty($http_password))
                    curl_setopt($ch,CURLOPT_USERPWD,$http_user.":".$http_password);
                curl_setopt($ch,CURLOPT_TIMEOUT,$this->module->config->timeout);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
                curl_setopt($ch,CURLOPT_FAILONERROR,true);
                $result_piwik = "";
                $piwik_response_header = false;
                if (($result_piwik = curl_exec($ch)) === false)
                    $piwik_response_header = curl_error($ch);
                curl_close($ch);
                $proxy_response_match = false;
                if (strlen($result) == strlen($result_piwik)) {
                    $proxy_response_match = true;
                }
                unset($result,$result_piwik);
            } else {
                $result = file_get_contents($http.$http_auth.$proxy_script);
                $proxy_script_response_header = $http_response_header;
                $result_piwik = file_get_contents($http.$http_auth.$this->module->config->host.'piwik.js');
                $piwik_response_header = $http_response_header;
                $proxy_response_match = false;
                if (strlen($result) == strlen($result_piwik)) {
                    $proxy_response_match = true;
                }
                unset($result,$result_piwik);
            }
            $proxy_script_response_unauthorized = false;
            if (isset($proxy_script_response_header) && is_array($proxy_script_response_header)) {
                foreach ($proxy_script_response_header as $key => & $value) {
                    if ((strpos($value,'Unauthorized') !== false) ||
                            (strpos($value,'Server Error') !== false)) {
                        $proxy_script_response_unauthorized = true;
                    }
                    $value = Tools::truncate($value,50);
                }
            }else if ($proxy_script_response_header !== false){
                 if ((strpos($proxy_script_response_header,'Unauthorized') !== false) ||
                            (strpos($proxy_script_response_header,'Server Error') !== false)) {
                        $proxy_script_response_unauthorized = true;
                    }
            }
            $piwik_response_unauthorized = false;
            if (isset($piwik_response_header) && is_array($piwik_response_header)) {
                foreach ($piwik_response_header as $key => & $value) {
                    if (preg_match("/^HTTP\/.*/i",$value)) {
                        if ((strpos($value,'Unauthorized') !== false) ||
                                (strpos($value,'Server Error') !== false)) {
                            $piwik_response_unauthorized = true;
                        }
                    }
                    $value = Tools::truncate($value,50);
                }
            }else if ($piwik_response_header !== false){
                 if ((strpos($piwik_response_header,'Unauthorized') !== false) ||
                            (strpos($piwik_response_header,'Server Error') !== false)) {
                        $proxy_script_response_unauthorized = true;
                    }
            }

            $this->context->smarty->assign(array(
                'proxy_script_url' => $http.$proxy_script,
                'proxy_script_response_header' => isset($proxy_script_response_header) ? (is_array($proxy_script_response_header) ? implode('<br/>',$proxy_script_response_header) : $proxy_script_response_header) : '',
                'piwik_url' => $http.$this->module->config->host.'piwik.js',
                'piwik_response_header' => isset($piwik_response_header) ? (is_array($piwik_response_header) ? implode('<br/>',$piwik_response_header) : $piwik_response_header) : '',
                'proxy_response_match' => $proxy_response_match,
                'http_auth' => !empty($http_auth),
                'piwik_response_unauthorized' => $piwik_response_unauthorized,
                'proxy_script_response_unauthorized' => $proxy_script_response_unauthorized,
            ));
        }

        if (file_exists($this->tpl_folder_theme.'validateconfiguration.tpl'))
            $content .= $this->context->smarty->fetch($this->tpl_folder_theme.'validateconfiguration.tpl');
        else
            $content .= $this->context->smarty->fetch($this->tpl_folder.'validateconfiguration.tpl');
        die($content);
    }

    public function init() {
        if (Tools::getValue('ajax'))
            $this->ajax = true;

        /* Server Params */
        $protocol_link = (Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
        $protocol_content = (isset($useSSL) && $useSSL && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
        $this->context->link = new Link($protocol_link,$protocol_content);

        $this->timerStart = microtime(true);


        if ($this->ajax && Tools::getIsset('action')) {
            $action = strtolower(Tools::getValue('action'));
            if ($action == "lookupauthtoken") {
                $this->lookupauthtoken();
            } else if ($action == "validateconfiguration") {
                $this->validateconfiguration();
            }
        }


        $http = $this->module->config->CRHTTPS;
        $PIWIK_HOST = $this->module->config->HOST;
        $PIWIK_SITEID = (int)$this->module->config->SITEID;

        $this->context->smarty->assign('help_link','https://github.com/cmjnisse/piwikanalyticsjs-prestashop/wiki');
        $user = $this->module->config->USRNAME;
        $passwd = $this->module->config->USRPASSWD;
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


        $PIWIK_TOKEN_AUTH = $this->module->config->token;
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

            $DREPDATE = $this->module->config->DREPDATE;
            if ($DREPDATE !== FALSE && (strpos($DREPDATE,'|') !== FALSE)) {
                list($period,$date) = explode('|',$DREPDATE);
            } else {
                $period = "day";
                $date = "today";
            }

            $http_auth = "";
            $http_user = $this->module->config->PAUTHUSR;
            $http_password = $this->module->config->PAUTHPWD;
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
                $this->module->config->update('USRNAME',Tools::getValue('PKLOOKUPTOKENUSRNAME',''));
                $this->module->config->update('USRPASSWD',Tools::getValue('PKLOOKUPTOKENUSRPASSWD',''));
            }

            PKHelper::$httpAuthUsername = Tools::getValue('PKLOOKUPTOKENPAUTHUSR','');
            PKHelper::$httpAuthPassword = Tools::getValue('PKLOOKUPTOKENPAUTHPWD','');
            $this->module->config->update('PAUTHUSR',PKHelper::$httpAuthUsername);
            $this->module->config->update('PAUTHPWD',PKHelper::$httpAuthPassword);

            if ($token = PKHelper::getTokenAuth(Tools::getValue('PKLOOKUPTOKENUSRNAME'),Tools::getValue('PKLOOKUPTOKENUSRPASSWD'),NULL)) {
                $this->module->config->update('TOKEN_AUTH',$token);
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

}
