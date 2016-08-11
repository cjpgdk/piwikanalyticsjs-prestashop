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
 */
if (!class_exists('PKHelper', false)){
    require_once dirname(__FILE__).'/PKHelper.php';
}

class PACONF extends PiwikAnalyticsjsConfiguration {
    
}

/**
 *
 * @author Christian M. Jensen
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @since 0.9
 * @access public
 * 
 * @property string $token Piwik auth token, alias of '$TOKEN_AUTH'
 * @property boolean $use_https use HTTPS when talking to Piwik, alias of '$CRHTTPS'
 * @property boolean $usehttps use HTTPS when talking to Piwik, alias of '$CRHTTPS'
 * @property int $site_id Piwik site is, alias of '$SITEID'
 * @property string $host Piwik host url
 * @property string $currency currency to use as default when posting cart/orders to Piwik, alias of '$DEFAULT_CURRENCY'
 * @property boolean $use_proxy use proxy script or not
 * @property boolean $useproxy use proxy script or not, alias of '$USE_PROXY'
 * @property boolean $use_curl use cURL or not
 * @property boolean $usecurl use cURL or not, alias of '$USE_CURL'
 * @property int $proxytimeout proxy script timeout, alias of '$PROXY_TIMEOUT'
 * @property string $proxyscript proxy script url, alias of '$PROXY_SCRIPT'
 * @property string $producttplv1 Product id template v1, alias of '$PRODID_V1'
 * @property string $producttplv2 Product id template v2, alias of '$PRODID_V2'
 * @property string $producttplv3 Product id template v3, alias of '$PRODID_V3'
 * @property string $searchquery Search query template, alias of '$SEARCH_QUERY'
 * @property string $setdomains Search query template, alias of '$SET_DOMAINS'
 * @property boolean $apiurl set Piwik api url or not
 * @property boolean $dhashtag Discard hash tag or not
 * @property string $linkclsignore ignore link classes 
 * @property string $linkcls link classes 
 * @property int $linktime link tracking time
 * @property string $cookiedomain cookie domain, alias of '$COOKIE_DOMAIN'
 * @property string $cookieprefix cookie name prefix
 * @property string $cookiepath cookie path
 * @property string $rcookietimeout Referral Cookie timeout (in seconds), alias of '$RCOOKIE_TIMEOUT'
 * @property string $cookietimeout Visitor Cookie timeout (in seconds), alias of '$COOKIE_TIMEOUT'
 * @property int $sessiontimeout Visitor Cookie timeout (in seconds), alias of '$SESSION_TIMEOUT'
 * 
 *  
 * @method boolean validate_save_token(string $post_key) validate and save TOKEN_AUTH with value from _POST or _GET in that order
 * @method boolean validate_save_siteid(string $post_key) validate and save SITEID with value from _POST or _GET in that order
 * @method boolean validate_save_host(string $post_key) validate and save HOST with value from _POST or _GET in that order
 * @method boolean validate_save_isset_boolean_dnt(string $post_key) validate and save DNT, if isset set as boolean, if isset == true | false
 * @method boolean validate_save_currency(string $post_key) validate and save DEFAULT_CURRENCY with value from _POST or _GET in that order
 * @method boolean validate_save_drepdate(string $post_key) validate and save DREPDATE with value from _POST or _GET in that order
 * @method boolean validate_save_isset_boolean_useproxy(string $post_key) validate and save USE_PROXY, if isset set as boolean, if isset == true | false
 * @method boolean validate_save_isset_boolean_usecurl(string $post_key) validate and save USE_CURL, if isset set as boolean, if isset == true | false
 * @method boolean validate_save_isset_boolean_usehttps(string $post_key) validate and save CRHTTPS, if isset set as boolean, if isset == true | false
 * @method boolean validate_save_isint_proxytimeout(string $post_key, integer $minimum_value, integer $default_value) validate and save PROXY_TIMEOUT with value from _POST or _GET in that order
 * @method boolean validate_save_proxyscript(string $post_key) validate and save PROXY_SCRIPT with value from _POST or _GET in that order
 * @method boolean validate_save_producttplv1(string $post_key) validate and save PRODID_V1 with value from _POST or _GET in that order
 * @method boolean validate_save_producttplv2(string $post_key) validate and save PRODID_V2 with value from _POST or _GET in that order
 * @method boolean validate_save_producttplv3(string $post_key) validate and save PRODID_V3 with value from _POST or _GET in that order
 * @method boolean validate_save_searchquery(string $post_key) validate and save SEARCH_QUERY with value from _POST or _GET in that order
 * @method boolean validate_save_setdomains(string $post_key) validate and save SET_DOMAINS with value from _POST or _GET in that order
 * @method boolean validate_save_isset_boolean_apiurl(string $post_key) validate and save APIURL, if isset set as boolean, if isset == true | false
 * @method boolean validate_save_isset_boolean_dhashtag(string $post_key) validate and save DHashTag, if isset set as boolean, if isset == true | false
 * @method boolean validate_save_exhtml(string $post_key) validate and save EXHTML with value from _POST or _GET in that order
 * @method boolean validate_save_isset_boolean_linktrack(string $post_key) validate and save LINKTRACK, if isset set as boolean, if isset == true | false
 * @method boolean validate_save_linkclsignore(string $post_key) validate and save LINKCLSIGNORE with value from _POST or _GET in that order
 * @method boolean validate_save_linkcls(string $post_key) validate and save LINKCLS with value from _POST or _GET in that order
 * @method boolean validate_save_isint_linktime(string $post_key, integer $minimum_value, integer $default_value) validate and save LINKTIME with value from _POST or _GET in that order
 * @method boolean validate_save_cookiedomain(string $post_key) validate and save COOKIE_DOMAIN with value from _POST or _GET in that order
 * @method boolean validate_save_cookieprefix(string $post_key) validate and save COOKIEPREFIX with value from _POST or _GET in that order
 * @method boolean validate_save_cookiepath(string $post_key) validate and save COOKIEPATH with value from _POST or _GET in that order
 * @method boolean validate_save_isint_rcookietimeout(string $post_key, integer $minimum_value, integer $default_value) validate and save RCOOKIE_TIMEOUT with value from _POST or _GET in that order
 * @method boolean validate_save_isint_cookietimeout(string $post_key, integer $minimum_value, integer $default_value) validate and save COOKIE_TIMEOUT with value from _POST or _GET in that order
 * @method boolean validate_save_isint_sessiontimeout(string $post_key, integer $minimum_value, integer $default_value) validate and save SESSION_TIMEOUT with value from _POST or _GET in that order
 * 
 * 
 */
class PiwikAnalyticsjsConfiguration {

    /** Default Referral Cookie Timeout */
    const PK_RC_TIMEOUT=262974;

    /** Default Visitor Cookie Timeout */
    const PK_VC_TIMEOUT=569777;

    /** Default Session Cookie Timeout */
    const PK_SC_TIMEOUT=30;

    /**  prefix useed for configuration item name */
    const PREFIX="PIWIK_";

    private $config_fields=array(
        'USE_PROXY' => 0, 'HOST' => "", 'SITEID' => 0,
        'TOKEN_AUTH' => "", 'COOKIE_TIMEOUT' => self::PK_VC_TIMEOUT,
        'SESSION_TIMEOUT' => self::PK_SC_TIMEOUT, 'DEFAULT_CURRENCY' => 'EUR',
        'CRHTTPS' => 0, 'PRODID_V1' => '{ID}-{ATTRID}#{REFERENCE}',
        'PRODID_V2' => '{ID}#{REFERENCE}', 'PRODID_V3' => '{ID}-{ATTRID}',
        'COOKIE_DOMAIN' => '', 'SET_DOMAINS' => "", 'DNT' => 1,
        'EXHTML' => "", 'RCOOKIE_TIMEOUT' => self::PK_RC_TIMEOUT,
        'USRNAME' => "", 'USRPASSWD' => "", 'PAUTHUSR' => "", 'PAUTHPWD' => "",
        'DREPDATE' => "day|today", 'USE_CURL' => 0, 'APTURL' => 0,
        'COOKIEPATH' => "", 'COOKIEPREFIX' => "", 'DHashTag' => 0, 'LINKCLS' => "",
        'LINKTRACK' => 1, 'LINKTTIME' => "", 'LINKCLSIGNORE' => "", 'PROXY_SCRIPT' => 0,
        'PROXY_TIMEOUT' => 5, 'SEARCH_QUERY' => "{QUERY} ({PAGE})",
    );

    public function __construct($load=true) {
        if ($load){
            foreach ($this->config_fields as $key => & $value){
                switch ($key){
                    case 'DNT':
                    case 'CRHTTPS':
                    case 'USE_PROXY':
                    case 'USE_CURL':
                    case 'APTURL':
                    /* case 'APIURL': */
                    case 'DHashTag':
                    case 'LINKTRACK':
                    case 'LINKTTIME':
                        /* case 'LINKTIME': */
                        // values 0 or false default will not work
                        $value=Configuration::get(self::PREFIX.$key);
                        break;
                    default:
                        if ($_value=Configuration::get(self::PREFIX.$key))
                            $value=$_value;
                        break;
                }
            }
        }
    }

    public $validate_output=array();

    /**
     * save current configuration or configuration settings by name
     * @param null|string $name if null save all configuration settings, otherwise save the configuration by name
     * @return void no return value
     */
    public function save($name=NULL) {
        if ($name===NULL){
            foreach ($this->config_fields as $key => $value){
                $html=false;
                if ($key=="EXHTML")
                    $html=true;
                Configuration::updateValue(self::PREFIX.$key, $value, $html);
            }
        }else{
            $name=$this->getInternalConfigName($name);
            if (isset($this->config_fields[$name])){
                $html=false;
                if ($name=="EXHTML")
                    $html=true;
                Configuration::updateValue(self::PREFIX.$name, $this->config_fields[$name], $html);
            }
        }
    }

    /**
     * validate configuration
     * @param string $section the section or setting to validate, if section then validation results are saved in $validate_output
     * @return boolean
     */
    public function validate($section='piwik') {
        $this->validate_output=array();
        switch ($section){
            case 'piwik':
                $this->validate_output=array(
                    'token' => false, 'siteid' => false, 'host' => false,
                    'piwik_connection' => array(
                        'result' => false,
                        'errors' => array(),
                    ),
                );
                if (PKHelper::isNullOrEmpty($this->token)||!Validate::isString($this->token)){
                    return false;
                } else
                    $this->validate_output['token']=true;
                if (PKHelper::isNullOrEmpty($this->siteid)||!Validate::isInt($this->siteid)){
                    return false;
                } else
                    $this->validate_output['siteid']=true;
                if (!PKHelper::isNullOrEmpty($this->host)){
                    if (PKHelper::isValidUrl('http://'.$this->host)){
                        if (substr($this->host, -1)!="/"){
                            $this->host .= "/";
                            $this->update('HOST', $this->host);
                        }
                        $this->validate_output['host']=true;
                    } else{
                        return false;
                    }
                } else{
                    return false;
                }
                if ($site=PKHelper::getPiwikSite($this->siteid)){
                    $this->validate_output['piwik_connection']['result']=true;
                    return true;
                } else{
                    $this->validate_output['piwik_connection']['errors']=PKHelper::$errors;
                    PKHelper::$error=null;
                    PKHelper::$errors=array();
                    return false;
                }
                break;
            case 'proxy_script':
                break;

            default:
                $section=$this->getInternalConfigName($section);
                if (isset($this->config_fields[$section])){
                    switch ($section){
                        case 'TOKEN_AUTH':
                            if (PKHelper::isNullOrEmpty($this->TOKEN_AUTH)||!Validate::isString($this->TOKEN_AUTH))
                                return false;
                            return true;
                        case 'SITEID':
                            if (PKHelper::isNullOrEmpty($this->SITEID)||!Validate::isInt($this->SITEID)||((int) $this->SITEID<=0))
                                return false;
                            return true;
                        case 'HOST':
                            $HOST=$this->HOST;
                            if (!PKHelper::isNullOrEmpty($HOST)){
                                $HOST=str_replace(array('http://', 'https://', '//'), "", $HOST);
                                if (substr($HOST, -1)!="/")
                                    $HOST .= "/";
                                if (PKHelper::isValidUrl($HOST)||PKHelper::isValidUrl('http://'.$HOST)){
                                    if ($HOST!=$this->HOST)
                                        $this->update('HOST', $HOST);
                                    return true;
                                }
                            }
                            return false;
                        case 'DNT':
                            return (Validate::isBool($this->DNT));
                        case 'DEFAULT_CURRENCY':
                            if (!PKHelper::isNullOrEmpty($this->DEFAULT_CURRENCY)&&(strlen($this->DEFAULT_CURRENCY)==3))
                                return true;
                            return false;
                        case 'DREPDATE':
                            $DREPDATE=$this->DREPDATE;
                            if (!PKHelper::isNullOrEmpty($DREPDATE)){
                                return (strpos($DREPDATE, '|')!==false);
                            }
                            return false;
                        case 'USE_PROXY':
                            return (Validate::isBool($this->USE_PROXY));
                        case 'USE_CURL':
                            return (Validate::isBool($this->USE_CURL));
                        case 'CRHTTPS':
                            return (Validate::isBool($this->CRHTTPS));
                        case 'PROXY_TIMEOUT':
                            return (Validate::isInt($this->PROXY_TIMEOUT))&&((int) $this->PROXY_TIMEOUT>0);
                        case 'PROXY_SCRIPT':
                            $PROXY_SCRIPT=$this->PROXY_SCRIPT;
                            if (!PKHelper::isNullOrEmpty($PROXY_SCRIPT)){
                                $PROXY_SCRIPT=str_replace(array('http://', 'https://', '//'), "", $PROXY_SCRIPT);
                                if (PKHelper::isValidUrl($PROXY_SCRIPT)||PKHelper::isValidUrl('http://'.$PROXY_SCRIPT)){
                                    if ($PROXY_SCRIPT!=$this->PROXY_SCRIPT)
                                        $this->update('PROXY_SCRIPT', $PROXY_SCRIPT);
                                    return true;
                                }
                            }
                            if (!PKHelper::isNullOrEmpty($this->PROXY_SCRIPT)&&PKHelper::isValidUrl('http://'.$this->PROXY_SCRIPT))
                                return true;
                            return false;
                        case 'PRODID_V1':
                        case 'PRODID_V2':
                        case 'PRODID_V3':
                            $_PRODID_=$this->{$section};
                            if (!preg_match("/{ID}/", $_PRODID_)){
                                $this->validate_output['ID']=1;
                                return false;
                            }
                            if ($section=="PRODID_V1"||$section=="PRODID_V2"){
                                if (!preg_match("/{REFERENCE}/", $_PRODID_)){
                                    $this->validate_output['REFERENCE']=1;
                                    return false;
                                }
                            }
                            if ($section=="PRODID_V1"||$section=="PRODID_V3"){
                                if (!preg_match("/{ATTRID}/", $_PRODID_)){
                                    $this->validate_output['ATTRID']=1;
                                    return false;
                                }
                            }
                            return true;
                        case 'SEARCH_QUERY':
                            $SEARCH_QUERY=$this->SEARCH_QUERY;
                            if (!preg_match("/{QUERY}/", $SEARCH_QUERY)){
                                $this->validate_output['QUERY']=1;
                                return false;
                            }
                            //PAGE not required so only set error
                            if (!preg_match("/{PAGE}/", $SEARCH_QUERY))
                                $this->validate_output['PAGE']=1;
                            return true;
                        case 'SET_DOMAINS':
                            /*
                             * @todo some validating on SET_DOMAINS
                             */
                            return true;
                        case 'DHASHTAG':
                            return (Validate::isBool($this->DHASHTAG));
                        case 'APTURL':
                        case 'APIURL':
                            return (Validate::isBool($this->APIURL));
                        case 'EXHTML':
                            /*
                             * @todo some validating on EXHTML
                             */
                            return true;
                        case 'LINKTRACK':
                            return (Validate::isBool($this->LINKTRACK));
                        case 'LINKCLS':
                            $this->LINKCLS=$this->__mapFilterString($this->LINKCLS, ',', "trim", "strlen");
                            return true;
                        case 'LINKCLSIGNORE':
                            $this->LINKCLSIGNORE=$this->__mapFilterString($this->LINKCLSIGNORE, ',', "trim", "strlen");
                            return true;
                        case 'LINKTTIME':
                        case 'LINKTIME':
                            return (Validate::isInt($this->LINKTTIME))&&((int) $this->LINKTTIME>=0);
                        case 'COOKIE_DOMAIN':/* db name */
                        case 'COOKIEDOMAIN':/* alias name */
                            /*
                             * @todo some validating on COOKIE_DOMAIN, (validate as dns 'CNAME' rec??)
                             */
                            return true;
                        case 'COOKIEPREFIX':
                            /* validation needed, ?maybe to remove invalid cookie name chars? */
                            return true;
                        case 'COOKIEPATH':
                            if (!PKHelper::isNullOrEmpty($this->cookiepath))
                                if (strpos($this->cookiepath, '/')!=0){
                                    $this->validate_output='/';
                                    return false;
                                }
                            return true;
                        case 'RCOOKIE_TIMEOUT':
                        case 'RCOOKIETIMEOUT':
                            if ((Validate::isInt($this->RCOOKIE_TIMEOUT))&&(int) $this->RCOOKIE_TIMEOUT>=0)
                                $this->RCOOKIE_TIMEOUT=$this->RCOOKIE_TIMEOUT*60; //convert to seconds
                            else
                                return false;
                        case 'COOKIE_TIMEOUT':
                        case 'COOKIETIMEOUT':
                            if ((Validate::isInt($this->COOKIE_TIMEOUT))&&(int) $this->COOKIE_TIMEOUT>=0)
                                $this->COOKIE_TIMEOUT=$this->COOKIE_TIMEOUT*60; //convert to seconds
                            else
                                return false;
                        case 'SESSION_TIMEOUT':
                        case 'SESSIONTIMEOUT':
                            if ((Validate::isInt($this->SESSION_TIMEOUT))&&(int) $this->SESSION_TIMEOUT>=0)
                                $this->SESSION_TIMEOUT=$this->SESSION_TIMEOUT*60; //convert to seconds
                            else
                                return false;
                            return true;
                        default:
                            if (isset($this->config_fields[$section])){
                                trigger_error("Config setting '{$section}' exists, but is not validated, something is wrong, maybe you've changed the code", E_USER_WARNING);
                            } else{
                                trigger_error("Config setting '{$section}' do not exists", E_USER_WARNING);
                            }
                            break;
                    }
                } else{
                    trigger_error("Unable to validate unkown section={$section}", E_USER_WARNING);
                }
                break;
        }
        return false;
    }

    private function getInternalConfigName($s) {
        $aliases=array(
            'token' => 'TOKEN_AUTH', 'site_id' => 'SITEID',
            'use_https' => 'CRHTTPS', 'currency' => 'DEFAULT_CURRENCY',
            'useproxy' => 'USE_PROXY', 'usehttps' => 'CRHTTPS',
            'usecurl' => 'USE_CURL', 'proxytimeout' => 'PROXY_TIMEOUT',
            'proxyscript' => 'PROXY_SCRIPT', 'producttplv1' => 'PRODID_V1',
            'producttplv2' => 'PRODID_V2', 'producttplv3' => 'PRODID_V3',
            'searchquery' => 'SEARCH_QUERY', 'setdomains' => 'SET_DOMAINS',
            'apiurl' => 'APTURL', 'linktime' => 'LINKTTIME',
            'cookiedomain' => 'COOKIE_DOMAIN', 'rcookietimeout' => 'RCOOKIE_TIMEOUT',
            'cookietimeout' => 'COOKIE_TIMEOUT', 'sessiontimeout' => 'SESSION_TIMEOUT',
        );
        if (isset($aliases[strtolower($s)]))
            return $aliases[strtolower($s)];
        return str_replace(self::PREFIX, '', strtoupper($s));
    }

    /**
     * get module default hooks for the current version of PrestaShop
     * @return array
     */
    public function getHooks() {
        $hooks=array(
            'defaults' => array(
            /* hooks with same name on all supported ps versions */
            ),
            'PS15' => array(
                /* 'header', */
                'displayHeader',
                /* 'footer', */
                'displayFooter',
                'actionSearch',
                'displayRightColumnProduct',
                /* 'orderConfirmation', */
                'displayOrderConfirmation',
                'displayMaintenance'
            ),
        );
        if (version_compare(substr(_PS_VERSION_, 0, 3), '1.5', '<=')){
            return array_merge($hooks['defaults'], $hooks['PS15']);
        }
        return $hooks['defaults'];
    }

    /**
     * get all configuration items including value
     * @param bool $prefix include configuration prefix (PACONF::PREFIX) in configuration name
     * @return array
     */
    public function getAll($prefix=true) {
        $return=array();
        foreach ($this->config_fields as $key => $value){
            $return[($prefix?self::PREFIX:'').$key]=$value;
        }
        return $return;
    }

    /**
     * get url that points to piwik including http(s) based on the configuration
     * @return string
     */
    public function getPiwikUrl() {
        return ((bool) $this->use_https?'https://':'http://').$this->host;
    }

    /**
     * Get the value of configuration item by name
     * @param string $name
     * @return boolean|mixed boolean false if configuration setting is not isset
     */
    public function __get($name) {
        if (strtoupper($name)=='APTURL'){
            trigger_error("The use of {$name} is deprecated please use 'APIURL', wrongly named", E_USER_DEPRECATED);
        }
        if (strtoupper($name)=='LINKTTIME'){
            trigger_error("The use of {$name} is deprecated please use 'LINKTIME', wrongly named", E_USER_DEPRECATED);
        }
        $name=$this->getInternalConfigName($name);
        if (isset($this->config_fields[$name])){
            return $this->config_fields[$name];
        }
        return FALSE;
    }

    /**
     * set the current loaded configuration value of $name
     * @param string $name Key
     * @param mixed $value
     */
    public function __set($name, $value) {
        if (strtoupper($name)=='APTURL'){
            trigger_error("The use of {$name} is deprecated please use 'APIURL', wrongly named", E_USER_DEPRECATED);
        }
        if (strtoupper($name)=='LINKTTIME'){
            trigger_error("The use of {$name} is deprecated please use 'LINKTIME', wrongly named", E_USER_DEPRECATED);
        }
        $name=$this->getInternalConfigName($name);
        if (is_bool($value))
            $value=($value?1:0);
        $this->config_fields[$name]=$value;
    }

    /**
     * Update the configuration value both in database and loaded config
     * @param string $key Key
     * @param mixed $values $values is an array if the configuration is multilingual, a single string else.
     * @param boolean $html Specify if html is authorized in value
     */
    public function update($key, $value, $html=false) {
        $key=$this->getInternalConfigName($key);
        if (is_bool($value))
            $value=($value?1:0);
        $this->config_fields[$key]=$value;
        Configuration::updateValue(self::PREFIX.$key, $value, $html);
    }

    /**
     * supports<br>
     * validate_save_{CONFIG-NAME}("POST|GET-VAR_NAME"): Validate and save from post/get values
     * @param string $name
     * @param mixed $arguments values for what ever function your are trying to call 
     * @return mixed
     */
    public function __call($name, $arguments) {
        $name=explode('_', strtolower($name));
        if (!isset($name[0]))
            return false;
        switch ($name[0]){
            case 'validate':
                // START: 'validate' Overload method
                if (!isset($name[1]))
                    return false;
                switch ($name[1]){
                    // START: 'validate_save_' Overload method
                    case 'save':
                        if (!isset($name[2]))
                            return false;
                        if (!isset($arguments[0])||is_null($arguments[0]))
                            return false;
                        if ($name[2]=="isset"){
                            // START: 'validate_save_isset_' Overload method
                            if (!isset($name[3])) // type, eg. boolean
                                return false;
                            if (!isset($name[4])) // config name
                                return false;
                            switch ($name[3]){
                                // START: 'validate_save_isset_boolean' Overload method
                                case 'bool':
                                case 'boolean':
                                    $validate_key=$this->getInternalConfigName($name[4]);
                                    if (Tools::getIsset(self::PREFIX.$arguments[0]))
                                        $this->{$validate_key}=1;
                                    else if (Tools::getIsset($arguments[0]))
                                        $this->{$validate_key}=1;
                                    else
                                        $this->{$validate_key}=0;
                                    return $this->validateSaveInternal($validate_key);
                                    // END: 'validate_save_isset_boolean' Overload method
                                    break;
                            }
                            // END: 'validate_save_isset_' Overload method
                        } else if ($name[2]=="isint"){
                            // START: 'validate_save_isint_' Overload method
                            if (!isset($name[3]))
                                return false;
                            $is_error=false;
                            $validate_key=$this->getInternalConfigName($name[3]);
                            if (Tools::getIsset(self::PREFIX.$arguments[0]))
                                $this->{$validate_key}=(int) Tools::getValue(self::PREFIX.$arguments[0], 0);
                            else if (Tools::getIsset($arguments[0]))
                                $this->{$validate_key}=(int) Tools::getValue(self::PREFIX.$arguments[0], 0);
                            else{ /* defaults to $default_value ($arguments[2]) if isset else $minimum_value */
                                $is_error=true;
                                $this->{$validate_key}=(isset($arguments[2])?(int) $arguments[2]:(isset($arguments[1])?(int) $arguments[1]:0));
                            }

                            if (isset($arguments[1])&&isset($arguments[2])&&($this->{$validate_key}<(int) $arguments[1])){
                                $this->{$validate_key}=(int) $arguments[2];
                                $is_error=true;
                            } else if (isset($arguments[1])&&($this->{$validate_key}<(int) $arguments[1])){
                                $this->{$validate_key}=(int) $arguments[1];
                                $is_error=true;
                            }
                            $validate_key_error=$this->validateSaveInternal($validate_key);
                            return (($is_error===false)?$validate_key_error:($is_error===false));
                            // END: 'validate_save_isint_' Overload method
                        } else{
                            $validate_key=$this->getInternalConfigName($name[2]);
                            if (Tools::getIsset(self::PREFIX.$arguments[0]))
                                $this->{$validate_key}=Tools::getValue(self::PREFIX.$arguments[0], '');
                            else if (Tools::getIsset($arguments[0]))
                                $this->{$validate_key}=Tools::getValue($arguments[0], '');
                            else{
                                trigger_error("{$name[2]} is not isset, so we cannot validate!", E_USER_NOTICE);
                                return false;
                            }
                            return $this->validateSaveInternal($validate_key);
                        }
                        // END: 'validate_save_' Overload method
                        break;
                }
                // END: 'validate' Overload method
                break;
        }
        return false;
    }

    private function validateSaveInternal($validate_key) {
        if ($this->validate($validate_key)){
            $this->save($validate_key);
            return true;
        } else{
            return false;
        }
    }

    private function __mapFilterString($string, $delimiter, $map="trim", $filter="strlen") {
        $result=explode($delimiter, $string);
        $result=array_map($map, $result);
        $result=array_filter($result, $filter);
        return implode(',', $result);
    }

}
