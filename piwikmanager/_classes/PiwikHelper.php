<?php

if (!defined('_PS_VERSION_'))
    exit;

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
class PiwikHelper {

    /**
     * all errors isset by class PiwikHelper
     * @var string[] 
     */
    public static $errors = array();

    /**
     * last isset error by class PiwikHelper
     * @var string
     */
    public static $error = "";

    /**
     * prefix to use for configurations values
     */
    const CPREFIX = "PIWIK_";
    const FAKEUSERAGENT = "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0 (Fake Useragent from CLASS:PiwikHelper.php)";

    // not tested. but should work when caling ajax.php.
    static public $allowed_ajax_methods = array(
//        'method' => array(
//            'required' => array(''),
//            'optional' => array(''),
//            'order' => array(''),
//        )
    );
    protected static $httpAuthUsername = "";
    protected static $httpAuthPassword = "";
    protected static $piwikHost = "";
    protected static $_cachedResults = array();

    /**
     * create a log of all events if set to "1", usefull if tracking not working
     * Log debug == 1
     * DO NOT log == 0
     * log will be saved to [PS ROOT]/log/YYYYMMDD_piwik.debug.log
     */
    private static $DEBUGLOG = 1;

    /** @var FileLogger */
    private static $_debug_logger = NULL;

    /** @var FileLogger */
    private static $_error_logger = NULL;

    /**
     * logs message to [PS ROOT]/log/YYYYMMDD_piwik.error.log
     * @param string $message
     */
    public static function ErrorLogger($message) {
        if (self::$_error_logger == NULL) {
            self::$_error_logger = new FileLogger(FileLogger::ERROR);
            self::$_error_logger->setFilename(_PS_ROOT_DIR_ . '/log/' . date('Ymd') . '_piwik.error.log');
        }
        self::$_error_logger->logError($message);
    }

    /**
     * logs message to [PS ROOT]/log/YYYYMMDD_piwik.debug.log
     * @param string $message
     */
    public static function DebugLogger($message) {
        if (self::$DEBUGLOG != 1)
            return;
        if (self::$_debug_logger == NULL) {
            self::$_debug_logger = new FileLogger(FileLogger::DEBUG);
            self::$_debug_logger->setFilename(_PS_ROOT_DIR_ . '/log/' . date('Ymd') . '_piwik.debug.log');
        }
        self::$_debug_logger->logDebug($message);
    }
    

        /*
         * SitesManager.addSite(
         *     * siteName, 
         *     * urls, 
         *     * ecommerce = '', 
         *     * siteSearch = '', 
         *     * searchKeywordParameters = '',
         *     * searchCategoryParameters = '', 
         *     * excludedIps = '',
         *     * excludedQueryParameters = '', 
         *     * timezone = '', 
         *     * currency = '', 
         *     - group = '', 
         *     - startDate = '', 
         *     * excludedUserAgents = '', 
         *     * keepURLFragments = '', 
         *     - type = '', 
         *     - settings = ''
         * )
         */

    /**
     * update a piwik site.
     * NOTE* make sure all values are url encoded, before caling this method
     * @param int $idSite
     * @param type $siteName
     * @param string $urls a comma seperated string of urls, were the first url is the mail url for the site.
     * @param type $ecommerce
     * @param type $siteSearch
     * @param type $searchKeywordParameters
     * @param type $searchCategoryParameters
     * @param type $excludedIps
     * @param type $excludedQueryParameters
     * @param type $timezone
     * @param type $currency
     * @param type $group
     * @param type $startDate
     * @param type $excludedUserAgents
     * @param type $keepURLFragments
     * @param type $type
     * @return boolean
     */
    public static function updatePiwikSite($idSite, $siteName = NULL, $urls = NULL, $ecommerce = NULL, $siteSearch = NULL, $searchKeywordParameters = NULL, $searchCategoryParameters = NULL, $excludedIps = NULL, $excludedQueryParameters = NULL, $timezone = NULL, $currency = NULL, $group = NULL, $startDate = NULL, $excludedUserAgents = NULL, $keepURLFragments = NULL, $type = NULL) {
        if (!self::baseTest() || ($idSite <= 0))
            return false;
        $url = self::getBaseURL($idSite);
        $url .= "&method=SitesManager.updateSite&format=JSON&";

        $url_params = array();

        if ($siteName !== NULL)
            $url_params['siteName'] = $siteName;
        //    $url .= "&siteName=" . urlencode($siteName);
        if ($urls !== NULL) {
            foreach (explode(',', $urls) as $value) {
                $url_params['urls'][] = trim($value);
                //$url .= "&urls[]=" . urlencode(trim($value));
            }
        }
        if ($ecommerce !== NULL)
            $url_params['ecommerce'] = ($ecommerce == 1 ? 1 : 0);
        //    $url .= "&ecommerce=" . urlencode($ecommerce);
        if ($siteSearch !== NULL)
            $url_params['siteSearch'] = ($siteSearch == 1 ? 1 : 0);
        //    $url .= "&siteSearch=" . urlencode($siteSearch);
        if ($searchKeywordParameters !== NULL)
            $url_params['searchKeywordParameters'] = $searchKeywordParameters;
        //    $url .= "&searchKeywordParameters=" . urlencode($searchKeywordParameters);
        if ($searchCategoryParameters !== NULL)
            $url_params['searchCategoryParameters'] = $searchCategoryParameters;
        //    $url .= "&searchCategoryParameters=" . urlencode($searchCategoryParameters);
        if ($excludedIps !== NULL)
            $url_params['excludedIps'] = $excludedIps;
        //    $url .= "&excludedIps=" . urlencode($excludedIps);
        if ($excludedQueryParameters !== NULL)
            $url_params['excludedQueryParameters'] = $excludedQueryParameters;
        //    $url .= "&excludedQueryParameters=" . urlencode($excludedQueryParameters);
        if ($timezone !== NULL)
            $url_params['timezone'] = $timezone;
        //    $url .= "&timezone=" . urlencode($timezone);
        if ($currency !== NULL)
            $url_params['currency'] = $currency;
        //    $url .= "&currency=" . urlencode($currency);
        if ($group !== NULL)
            $url_params['group'] = $group;
        //    $url .= "&group=" . urlencode($group);
        if ($startDate !== NULL)
            $url_params['startDate'] = $startDate;
        //    $url .= "&startDate=" . urlencode($startDate);
        if ($excludedUserAgents !== NULL)
            $url_params['excludedUserAgents'] = $excludedUserAgents;
        //    $url .= "&excludedUserAgents=" . urlencode($excludedUserAgents);
        if ($keepURLFragments !== NULL)
            $url_params['keepURLFragments'] = ($keepURLFragments == 1 ? 1 : 0);
        //    $url .= "&keepURLFragments=" . urlencode($keepURLFragments);
        if ($type !== NULL)
            $url_params['type'] = urlencode($type);
        //    $url .= "&type=" . urlencode($type);

        if ($result = self::getAsJsonDecoded($url . http_build_query($url_params))) {
            $url2 = self::getBaseURL($idSite) . "&method=SitesManager.getSiteFromId&format=JSON";
            unset(self::$_cachedResults[md5($url2)]); // Clear cache for updated site
            return ($result->result == 'success' && $result->message == 'ok' ? TRUE : ($result->result != 'success' ? $result->message : FALSE));
        } else
            return FALSE;
    }

    /**
     * check if a plugin is installed and active in piwik
     * @param string $name name of the plugin
     * @return boolean
     */
    public static function isPluginActive($name = "CustomOptOut") {
        if (!self::baseTest())
            return array();
        $url = self::getBaseURL();
        $url .= "&method=API.isPluginActivated&pluginName={$name}&format=JSON";
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = ((isset($result->value) && $result->value === false) ? false : true);
            else
                self::$_cachedResults[$md5Url] = false;
        }
        return self::$_cachedResults[$md5Url];
    }

    /**
     * get all supported currencies from piwik
     * @return array
     */
    public static function getCurrencyList() {
        if (!self::baseTest())
            return array();
        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getCurrencyList&format=JSON";
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = $result;
            else
                self::$_cachedResults[$md5Url] = array();
        }
        return self::$_cachedResults[$md5Url];
    }

    /**
     * get all supported time zones from piwik
     * @return array
     */
    public static function getTimezonesList() {
        if (!self::baseTest())
            return array();
        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getTimezonesList&format=JSON";
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = $result;
            else
                self::$_cachedResults[$md5Url] = array();
        }
        return self::$_cachedResults[$md5Url];
    }

    /**
     * get Piwik site by id
     * @param int $idSite
     * @return stdClass[]|boolean
     */
    public static function getPiwikSite($idSite = 0) {
        if ($idSite == 0)
            $idSite = (int) Configuration::get(PKHelper::CPREFIX . 'SITEID');
        if (!self::baseTest() || ($idSite <= 0))
            return false;

        $url = self::getBaseURL($idSite);
        $url .= "&method=SitesManager.getSiteFromId&format=JSON";
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = $result;
            else
                self::$_cachedResults[$md5Url] = false;
        }
        if (self::$_cachedResults[$md5Url] !== FALSE) {
            if (isset(self::$_cachedResults[$md5Url]->result) && self::$_cachedResults[$md5Url]->result == 'error') {
                self::$error = self::$_cachedResults[$md5Url]->message;
                self::$errors[] = self::$error;
                return false;
            }
            if (!isset(self::$_cachedResults[$md5Url][0])) {
                return false;
            }
            if (((bool) self::$_cachedResults[$md5Url][0]->ecommerce === false) || self::$_cachedResults[$md5Url][0]->ecommerce == 0) {
                self::$error = self::l('E-commerce is not active for your site in piwik!, you can enable it in the advanced settings on this page');
                self::$errors[] = self::$error;
            }
            if (((bool) self::$_cachedResults[$md5Url][0]->sitesearch) === false || self::$_cachedResults[$md5Url][0]->sitesearch == 0) {
                self::$error = self::l('Site search is not active for your site in piwik!, you can enable it in the advanced settings on this page');
                self::$errors[] = self::$error;
            }
            return self::$_cachedResults[$md5Url];
        }
        return false;
    }

    /**
     * get Piwik site by id including all urls for the site
     * @param int $idSite
     * @return stdClass[]|boolean
     */
    public static function getPiwikSite2($idSite = 0) {
        if ($idSite == 0)
            $idSite = (int) Configuration::get(PKHelper::CPREFIX . 'SITEID');
        if ($result = self::getPiwikSite($idSite)) {
            $url = self::getBaseURL($idSite);
            $url .= "&method=SitesManager.getSiteUrlsFromId&format=JSON";
            if ($resultUrls = self::getAsJsonDecoded($url)) {
                $result[0]->main_url = implode(',', $resultUrls);
            }
            return $result;
        }
        return false;
    }

    /**
     * get all Piwik sites the current authentication token has admin access to
     * @param boolean $fetchAliasUrls
     * @param array $getBaseURLParams optional array of values to use for PiwikHelper::getBaseURL, all 6 parameters must be present if used.
     * @return stdClass[]
     */
    public static function getSitesWithAdminAccess($fetchAliasUrls = false, $getBaseURLParams = NULL) {
        if (!self::baseTest())
            return array();
        if ($getBaseURLParams == NULL && !is_array($getBaseURLParams) || !(is_array($getBaseURLParams) && count($getBaseURLParams) == 6))
            $url = self::getBaseURL();
        else {
            extract($getBaseURLParams, EXTR_OVERWRITE);
            $url = self::getBaseURL($idSite, $pkHost, $https, $pkModule, $isoCode, $tokenAuth);
        }
        $url .= "&method=SitesManager.getSitesWithAdminAccess&format=JSON" . ($fetchAliasUrls ? '&fetchAliasUrls=1' : '');
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = $result;
            else
                self::$_cachedResults[$md5Url] = array();
        }
        return self::$_cachedResults[$md5Url];
    }

    private static $require_initialize = true;

    public static function initialize() {
        if (self::$require_initialize) {
            self::$DEBUGLOG = Configuration::get(self::CPREFIX . 'DEBUG');

            self::$require_initialize = false;
        }
    }

    /**
     * get output of api as json decoded object
     * @param string $url the full http(s) url to use for fetching the api result
     * @return boolean
     */
    protected static function getAsJsonDecoded($url) {
        static $_error2 = FALSE;
        $use_cURL = (bool) Configuration::get(self::CPREFIX . 'USE_CURL');

        $getF = self::get_http($url);
        if ($getF !== FALSE) {
            return Tools::jsonDecode($getF);
        }
        return FALSE;
    }

    public static function get_http($url, $headers = array()) {
        static $_error2 = FALSE;
        self::DebugLogger('START: PiwikHelper::get_http(' . $url . ',' . print_r($headers, true) . ')');
        // class: Context is not loaded when using piwik.php proxy on prestashop 1.4
        if (class_exists('Context', FALSE))
            $lng = strtolower((isset(Context::getContext()->language->iso_code) ? Context::getContext()->language->iso_code : 'en'));
        else
            $lng = 'en';

        $timeout = 5; // should go in module conf

        if (self::$httpAuthUsername == "" || self::$httpAuthUsername === false)
            self::$httpAuthUsername = Configuration::get(self::CPREFIX . 'PAUTHUSR');
        if (self::$httpAuthPassword == "" || self::$httpAuthPassword === false)
            self::$httpAuthPassword = Configuration::get(self::CPREFIX . 'PAUTHPWD');

        $httpauth_usr = self::$httpAuthUsername;
        $httpauth_pwd = self::$httpAuthPassword;

        $use_cURL = (bool) Configuration::get(self::CPREFIX . 'USE_CURL');
        if ($use_cURL === FALSE) {
            self::DebugLogger('Using \'file_get_contents\' to fetch remote');
            $httpauth = "";
            if ((!empty($httpauth_usr) && !is_null($httpauth_usr) && $httpauth_usr !== false) && (!empty($httpauth_pwd) && !is_null($httpauth_pwd) && $httpauth_pwd !== false)) {
                $httpauth = "Authorization: Basic " . base64_encode("$httpauth_usr:$httpauth_pwd") . "\r\n";
            }
            $options = array(
                'http' => array(
                    'user_agent' => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : self::FAKEUSERAGENT),
                    'method' => "GET",
                    'timeout' => $timeout,
                    'header' => (!empty($headers) ? implode('', $headers) : "Accept-language: {$lng}\r\n") . $httpauth
                )
            );
            $context = stream_context_create($options);
            self::DebugLogger('Calling: ' . $url . (!empty($httpauth) ? "\n\t- With Http auth" : ""));
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
                self::DebugLogger('request returned ERROR: http response: ' . $http_response);
                if (isset($http_response_header))
                    self::DebugLogger('$http_response_header: ' . print_r($http_response_header, true));
                if (!$_error2) {
                    self::$error = sprintf(self::l('Unable to connect to api%s'), " {$http_response}");
                    self::$errors[] = self::$error;
                    $_error2 = TRUE;
                    self::DebugLogger('Last error message: ' . self::$error);
                }
            } else {
                self::DebugLogger('request returned OK');
            }
            self::DebugLogger('END: PiwikHelper::get_http(): OK');
            return $result;
        } else {
            self::DebugLogger('Using \'cURL\' to fetch remote');
            try {
                $ch = curl_init();
                self::DebugLogger("\t: \$ch = curl_init()");
                curl_setopt($ch, CURLOPT_URL, $url);
                self::DebugLogger("\t: curl_setopt(\$ch, CURLOPT_URL, $url)");
                // @TODO make this work, but how to filter out the headers from returned result??
                //curl_setopt($ch, CURLOPT_HEADER, 1);
                (!empty($headers) ?
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers) :
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-language: {$lng}\r\n"))
                        );
                self::DebugLogger("\t: curl_setopt(\$ch, CURLOPT_HTTPHEADER, array(...))");
                curl_setopt($ch, CURLOPT_USERAGENT, (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : self::FAKEUSERAGENT));
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
                self::DebugLogger('END: PiwikHelper::get_http(): OK');
                return $return;
            } catch (Exception $ex) {
                self::$errors[] = $ex->getMessage();
                self::DebugLogger('Exception: ' . $ex->getMessage());
                self::DebugLogger('END: PiwikHelper::get_http(): ERROR');
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
        $pkToken = Configuration::get(self::CPREFIX . 'TOKEN_AUTH');
        $pkHost = Configuration::get(self::CPREFIX . 'HOST');
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
        if ($https === NULL)
            $https = (bool) Configuration::get(self::CPREFIX . 'CRHTTPS');

        if (self::$piwikHost == "" || self::$piwikHost === false)
            self::$piwikHost = Configuration::get(self::CPREFIX . 'HOST');

        if ($pkHost === NULL)
            $pkHost = self::$piwikHost;
        if ($isoCode === NULL)
            $isoCode = strtolower((isset(Context::getContext()->language->iso_code) ? Context::getContext()->language->iso_code : 'en'));
        if ($idSite === NULL)
            $idSite = Configuration::get(self::CPREFIX . 'SITEID');
        if ($tokenAuth === NULL)
            $tokenAuth = Configuration::get(self::CPREFIX . 'TOKEN_AUTH');

        $url_params = array();
        if ($pkModule !== FALSE)
            $url_params['module'] = $pkModule;
        if ($isoCode !== FALSE)
            $url_params['language'] = $isoCode;
        if ($idSite !== FALSE)
            $url_params['idSite'] = $idSite;
        if ($tokenAuth !== FALSE)
            $url_params['token_auth'] = $tokenAuth;

        return ($https ? 'https' : 'http') . "://{$pkHost}index.php?" . http_build_query($url_params);
    }

    /**
     * @see Module::l
     */
    private static function l($string, $specific = false) {
        return Translate::getModuleTranslation('piwikmanager', $string, ($specific) ? $specific : 'piwikhelper');
        // the following lines are need for the translation to work properly
        // $this->l('I need Site ID and Auth Token before i can get your image tracking code')
        // $this->l('E-commerce is not active for your site in piwik!, you can enable it in the advanced settings on this page')
        // $this->l('Site search is not active for your site in piwik!, you can enable it in the advanced settings on this page')
        // $this->l('Unable to connect to api %s')
        // $this->l('E-commerce is not active for your site in piwik!')
        // $this->l('Site search is not active for your site in piwik!')
        // $this->l('A password is required for method PKHelper::getTokenAuth()!')
    }

}
