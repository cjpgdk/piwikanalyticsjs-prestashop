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
 */
if (!class_exists('PKHelper',false)) {
    require_once dirname(__FILE__).'/PKHelper.php';
}

class PACONF extends PiwikAnalyticsjsConfiguration {
    
}

/**
 * @property string $token Piwik auth token
 */
class PiwikAnalyticsjsConfiguration {

    /** Default Referral Cookie Timeout */
    const PK_RC_TIMEOUT = 262974;

    /** Default Visitor Cookie Timeout */
    const PK_VC_TIMEOUT = 569777;

    /** Default Session Cookie Timeout */
    const PK_SC_TIMEOUT = 30;

    /**  prefix useed for configuration item name */
    const PREFIX = "PIWIK_";

    private $config_fields = array(
        'USE_PROXY' => 0,'HOST' => "",'SITEID' => 0,
        'TOKEN_AUTH' => "",'COOKIE_TIMEOUT' => self::PK_VC_TIMEOUT,
        'SESSION_TIMEOUT' => self::PK_SC_TIMEOUT,'DEFAULT_CURRENCY' => 'EUR',
        'CRHTTPS' => 0,'PRODID_V1' => '{ID}-{ATTRID}#{REFERENCE}',
        'PRODID_V2' => '{ID}#{REFERENCE}','PRODID_V3' => '{ID}-{ATTRID}',
        'COOKIE_DOMAIN' => '','SET_DOMAINS' => "",'DNT' => 1,
        'EXHTML' => "",'RCOOKIE_TIMEOUT' => self::PK_RC_TIMEOUT,
        'USRNAME' => "",'USRPASSWD' => "",'PAUTHUSR' => "",'PAUTHPWD' => "",
        'DREPDATE' => "day|today",'USE_CURL' => 0,'APTURL' => 0,
        'COOKIEPATH' => "",'COOKIEPREFIX' => "",'DHashTag' => 0,'LINKClS' => "",
        'LINKTRACK' => 1,'LINKTTIME' => "",'PROXY_SCRIPT' => 0,
        'PROXY_TIMEOUT' => 5,'SEARCH_QUERY' => "{QUERY} ({PAGE})",
    );

    public function __construct() {
        foreach ($this->config_fields as $key => & $value) {
            if ($_value = Configuration::get(self::PREFIX.$key))
                ;
            $value = $_value;
        }
    }

    public function getAll() {
        $return = array();
        foreach ($this->config_fields as $key => $value) {
            $return[self::PREFIX.$key] = $value;
        }
        return $return;
    }

    public function __get($name) {
        $name = $this->getInternalConfigName($name);
        if (isset($this->config_fields[$name])) {
            return $this->config_fields[$name];
        }
        return FALSE;
    }

    public function __set($name,$value) {
        $name = $this->getInternalConfigName($name);
        if (is_bool($value))
            $value = ($value ? 1 : 0);
        $this->config_fields[$name] = $value;
    }

    public function update($key,$value,$html = false) {
        $key = $this->getInternalConfigName($key);
        if (is_bool($value))
            $value = ($value ? 1 : 0);
        $this->config_fields[$key] = $value;
        Configuration::updateValue($key,$value,$html);
    }

    public function getHooks() {
        $hooks = array(
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
        if (version_compare(substr(_PS_VERSION_,0,3),'1.5','<=')) {
            return $hooks['PS15'];
        }
    }

    public $validate_output = array();

    public function validate($section = 'piwik') {

        switch ($section) {
            case 'piwik':
                $this->validate_output = array(
                    'token' => false,'siteid' => false,'host' => false,
                    'piwik_connection' => array(
                        'result' => false,
                        'errors' => array(),
                    ),
                );
                if ($this->isNullOrEmpty($this->token) || !Validate::isString($this->token)) {
                    return false;
                } else
                    $this->validate_output['token'] = true;
                if ($this->isNullOrEmpty($this->siteid) || !Validate::isInt($this->siteid)) {
                    return false;
                } else
                    $this->validate_output['siteid'] = true;
                if (!$this->isNullOrEmpty($this->host)) {
                    if (PKHelper::isValidUrl('http://'.$this->host)) {
                        if (substr($this->host,-1) != "/") {
                            $this->host .= "/";
                            Configuration::updateValue(self::PREFIX.'HOST',$this->host);
                        }
                        $this->validate_output['host'] = true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
                if ($site = PKHelper::getPiwikSite($this->siteid)) {
                    $this->validate_output['piwik_connection']['result'] = true;
                    return true;
                } else {
                    $this->validate_output['piwik_connection']['errors'] = PKHelper::$errors;
                    PKHelper::$error = null;
                    PKHelper::$errors = array();
                    return false;
                }
                break;
            case 'proxy_script':
                break;

            default:
                if (isset($this->{$section})) {
                    $section = $this->getInternalConfigName($section);
                    switch ($section) {
                        case 'TOKEN_AUTH':
                            if (!isset($this->TOKEN_AUTH) || !Validate::isString($this->TOKEN_AUTH))
                                return false;
                            return true;
                        case 'SITEID':
                            if (!isset($this->SITEID) || !Validate::isInt($this->SITEID))
                                return false;
                            return true;
                        case 'HOST':
                            if (isset($this->HOST) && !empty($this->HOST)) {
                                if (PKHelper::isValidUrl('http://'.$this->HOST)) {
                                    return true;
                                }
                            }
                            return false;
                        default:
                            break;
                    }
                }
                break;
        }
        return false;
    }

    private function isNullOrEmpty($var) {
        return is_null($var) || empty($var);
    }

    private function getInternalConfigName($s) {
        $aliases = array(
            'token' => 'TOKEN_AUTH',
            'site_id' => 'SITEID',
            'use_https' => 'CRHTTPS'
        );
        if (isset($aliases[strtolower($s)]))
            return $aliases[strtolower($s)];
        return str_replace(self::PREFIX,'',strtoupper($s));
    }

}
