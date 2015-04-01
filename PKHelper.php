<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;
if (class_exists('PKHelper', FALSE))
    return;

/**
 * Copyright (C) 2014-2015 Christian Jensen
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
 * @version 0.7.5 PrestaShop? 1.4 Final
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class PKHelper {

    public static $_module = null;

    /**
     * all errors isset by class PKHelper
     * @var string[] 
     */
    public static $errors = array();

    /**
     * last isset error by class PKHelper
     * @var string
     */
    public static $error = "";
    const FAKEUSERAGENT = "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0 (Fake Useragent from CLASS:PKHelper.php)";

    public static function getPiwikSite($idSite = 0) {
        if ($idSite == 0)
            $idSite = (int) Configuration::get('PIWIK_SITEID');
        if (!self::baseTest() || ($idSite <= 0))
            return false;

        $url = self::getBaseURL($idSite);
        $url .= "&method=SitesManager.getSiteFromId&format=JSON";

        $result = self::getAsJsonDecoded($url);

        if (isset($result->result) && $result->result == 'error') {
            self::$error = $result->message;
            self::$errors[] = self::$error;
            return false;
        }
        if (!isset($result[0])) {
            return false;
        }
        return $result;

        return false;
    }
    
    /**
     * get image tracking code for use with or without proxy script
     * @return array
     */
    public static function getPiwikImageTrackingCode() {
        $ret = array(
            'default' => self::l('I need Site ID and Auth Token before i can get your image tracking code'),
            'proxy' => self::l('I need Site ID and Auth Token before i can get your image tracking code')
        );

        $idSite = (int) Configuration::get('PIWIK_SITEID');
        if (!self::baseTest() || ($idSite <= 0))
            return $ret;

        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getImageTrackingCode&format=JSON&actionName=NoJavaScript";
        $url .= "&piwikUrl=" . urlencode(rtrim(Configuration::get('PIWIK_HOST'), '/'));
        
        $result = self::getAsJsonDecoded($url);
            
        if ($result !== FALSE) {
            $ret['default'] = htmlentities('<noscript>' . $result->value . '</noscript>');
            if ((bool) Configuration::get('PS_REWRITING_SETTINGS'))
                $ret['proxy'] = str_replace(Configuration::get('PIWIK_HOST') . 'piwik.php', Configuration::get('PIWIK_PROXY_SCRIPT'), $ret['default']);
            else
                $ret['proxy'] = str_replace(Configuration::get('PIWIK_HOST') . 'piwik.php?', Configuration::get('PIWIK_PROXY_SCRIPT') . '&', $ret['default']);
        }
        return $ret;
    }

    /**
     * get output of api as json decoded object
     * @param string $url the full http(s) url to use for fetching the api result
     * @return boolean
     */
    protected static function getAsJsonDecoded($url) {
        $getF = self::get_http($url);
        if ($getF !== FALSE) {
            return json_decode($getF);
        }
        return FALSE;
    }

    public static function get_http($url, $headers = array()) {
        static $_error2 = FALSE;
        global $cookie;
        if(is_object($cookie) && $cookie instanceof Cookie)
            $language = new Language((int) $cookie->id_lang);
        else{ // not loaded in proxy script.!
            $language = new stdClass();
            $language->iso_code = 'en';
        }
            
        $lng = strtolower((isset($language->iso_code) ? $language->iso_code : 'en'));

        $timeout = 5; // should go in module conf

        $httpauth_usr = Configuration::get('PIWIK_PAUTHUSR');
        $httpauth_pwd = Configuration::get('PIWIK_PAUTHPWD');

        $use_cURL = (bool) Configuration::get('PIWIK_USE_CURL');
        if ($use_cURL === FALSE) {
            
            $httpauth = "";
            if ((!empty($httpauth_usr) && !is_null($httpauth_usr) && $httpauth_usr !== false) && (!empty($httpauth_pwd) && !is_null($httpauth_pwd) && $httpauth_pwd !== false)) {
                $httpauth = "Authorization: Basic " . base64_encode("$httpauth_usr:$httpauth_pwd") . "\r\n";
            }
            $options = array(
                'http' => array(
                    'user_agent' => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : PKHelper::FAKEUSERAGENT),
                    'method' => "GET",
                    'timeout' => $timeout,
                    'header' => (!empty($headers) ? implode('', $headers) : "Accept-language: {$lng}\r\n") . $httpauth
                )
            );
            $context = stream_context_create($options);
            
            $result = @file_get_contents($url, false, $context);
            if ($result === FALSE) {
                $http_response = "";
                if (isset($http_response_header) && is_array($http_response_header)) {
                    foreach ($http_response_header as $value) {
                        if (preg_match("/^HTTP\/.*/i", $value)) {
                            $http_response = ':' . $value;
                        }
                    }
                }
                if (!$_error2) {
                    self::$error = sprintf(self::l('Unable to connect to api%s'), " {$http_response}");
                    self::$errors[] = self::$error;
                    $_error2 = TRUE;
                }
            }
            return $result;
        } else {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                (!empty($headers) ?
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers) :
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-language: {$lng}\r\n"))
                        );
                curl_setopt($ch, CURLOPT_USERAGENT, (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : PKHelper::FAKEUSERAGENT));
                if ((!empty($httpauth_usr) && !is_null($httpauth_usr) && $httpauth_usr !== false) && (!empty($httpauth_pwd) && !is_null($httpauth_pwd) && $httpauth_pwd !== false))
                    curl_setopt($ch, CURLOPT_USERPWD, $httpauth_usr . ":" . $httpauth_pwd);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_HTTPGET, 1); // just to be safe
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                if (($return = curl_exec($ch)) === false) {
                    if (!$_error2) {
                        self::$error = curl_error($ch);
                        self::$errors[] = self::$error;
                        $_error2 = TRUE;
                    }
                    $return = false;
                }
                curl_close($ch);
                return $return;
            } catch (Exception $ex) {
                self::$errors[] = $ex->getMessage();
                return false;
            }
        }
    }

    /**
     * check if the basics are there before we make any piwik requests
     * @return boolean
     */
    protected static function baseTest() {
        static $_error1 = FALSE;
        $pkToken = Configuration::get('PIWIK_TOKEN_AUTH');
        $pkHost = Configuration::get('PIWIK_HOST');
        if (empty($pkToken) || empty($pkHost)) {
            if (!$_error1) {
                self::$error = self::l('Piwik auth token and/or Piwik site id cannot be empty');
                self::$errors[] = self::$error;
                $_error1 = TRUE;
            }
            return false;
        }
        return true;
    }

    private static function l($string, $specific = false) {
        return PKHelper::$_module->l($string, ($specific) ? $specific : 'pkhelper');
        // the following lines are need for the translation to work properly
        // $this->l('Piwik auth token and/or Piwik site id cannot be empty');
        // $this->l('Unable to connect to api%s');
        // $this->l('I need Site ID and Auth Token before i can get your image tracking code')
    }

    /**
     * get the base url for all requests to Piwik
     * @param integer $idSite
     * @param string $pkHost
     * @param boolean $https
     * @param string $pkModule
     * @param string $isoCode
     * @param string $tokenAuth
     * @return string
     */
    protected static function getBaseURL($idSite = NULL, $pkHost = NULL, $https = NULL, $pkModule = 'API', $isoCode = NULL, $tokenAuth = NULL) {
        global $cookie;
        $language = new Language((int) $cookie->id_lang);
        if ($https === NULL)
            $https = (bool) Configuration::get('PIWIK_CRHTTPS');
        if ($pkHost === NULL)
            $pkHost = Configuration::get('PIWIK_HOST');
        if ($isoCode === NULL)
            $isoCode = strtolower((isset($language->iso_code) ? $language->iso_code : 'en'));
        if ($idSite === NULL)
            $idSite = Configuration::get('PIWIK_SITEID');
        if ($tokenAuth === NULL)
            $tokenAuth = Configuration::get('PIWIK_TOKEN_AUTH');
        return ($https ? 'https' : 'http') . "://{$pkHost}index.php?module={$pkModule}&language={$isoCode}&idSite={$idSite}&token_auth={$tokenAuth}";
    }

}
