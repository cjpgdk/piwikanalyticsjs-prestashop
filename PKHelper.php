<?php

if (!defined('_PS_VERSION_'))
    exit;

if (class_exists('PKHelper',FALSE))
    return;

/*
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
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

class PKHelper {

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
    protected static $_cachedResults = array();

    /**
     * for Prestashop 1.4 translation
     * @var piwikanalyticsjs
     */
    public static $_module = null;

    /**
     * prefix to use for configurations values
     */
    const CPREFIX = "PIWIK_";
    const FAKEUSERAGENT = "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0 (Fake Useragent from CLASS:PKHelper.php)";

    public static $httpAuthUsername = "";
    public static $httpAuthPassword = "";
    public static $piwikHost = "";

    /**
     * create a log of all events if set to "1", usefull if tracking not working
     * Log debug == 1
     * DO NOT log == 0
     * log will be saved to [PS ROOT]/log/YYYYMMDD_piwik.debug.log
     */
    const DEBUGLOG = 0;

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
            self::$_error_logger->setFilename(_PS_ROOT_DIR_.'/log/'.date('Ymd').'_piwik.error.log');
        }
        self::$_error_logger->logError($message);
    }

    /**
     * logs message to [PS ROOT]/log/YYYYMMDD_piwik.debug.log
     * @param string $message
     */
    public static function DebugLogger($message) {
        if (PKHelper::DEBUGLOG != 1)
            return;
        if (self::$_debug_logger == NULL) {
            self::$_debug_logger = new FileLogger(FileLogger::DEBUG);
            self::$_debug_logger->setFilename(_PS_ROOT_DIR_.'/log/'.date('Ymd').'_piwik.debug.log');
        }
        self::$_debug_logger->logDebug($message);
    }

    /**
     * validate IPv4
     * @param string $ip
     * @return boolean
     * @since 0.8.4
     */
    public static function isIPv4($ip) {
        $_part = explode(".",$ip);
        if (count($_part) != 4)  return false;
        else {
            for ($n = 0; $n < 4; $n++) {
                $q = $_part[$n];
                if (!is_numeric($q) || (int)$q < 0 || (int)$q > 255 || trim($q) !== $q)
                    return false;
            }
        }
        $_ip = (int)$_part[0].".".(int)$_part[1].".".(int)$_part[2].".".(int)$_part[3];
        if ($ip != $_ip) return false;
        return true;
    }
    /**
     * checks if $ip contains ANY invalid chars not allowd in IPv6 addresses
     * @param string $ip
     * @return boolean
     * @since 0.8.4
     */
    public static function isIPv6($ip) {
        $valid_chars = "ABCDEFabcdef1234567890:";
        if (strspn($ip,$valid_chars) != strlen($ip))
            return false;
        return true;
    }

    /**
     * adds a new site to piwik
     * @param type $siteName
     * @param type $urls
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
     * @param type $settings
     * @return int|boolean boolean false on error, new siteid on success
     */
    public static function addPiwikSite($siteName,$urls,$ecommerce = 1,$siteSearch = 1,$searchKeywordParameters = '',$searchCategoryParameters = '',$excludedIps = '',$excludedQueryParameters = '',$timezone = 'UTC',$currency = '',$group = '',$startDate = '',$excludedUserAgents = '',$keepURLFragments = 0,$type = 'website',$settings = '') {
        if (!self::baseTest())
            return false;
        if (class_exists('PiwikWizardHelper',FALSE) && !empty(PiwikWizardHelper::$username) && !empty(PiwikWizardHelper::$password)) {
            // get new token
            // @todo PiwikWizardHelper::createNewSite() needs to handle this so wee do not mix helper classes that are suppose to be seperated.
            $token = self::getTokenAuth(PiwikWizardHelper::$username,PiwikWizardHelper::$password);
            Configuration::updateValue(PKHelper::CPREFIX.'TOKEN_AUTH',$token);
            $url = self::getBaseURL(0,NULL,NULL,'API',NULL,$token);
        } else
            $url = self::getBaseURL(0); // use token from saved configuration


        $url .= "&method=SitesManager.addSite&format=JSON&";

        $url_params = array();
        if ($siteName !== NULL)
            $url_params['siteName'] = $siteName;
        if ($urls !== NULL) {
            foreach (explode(',',$urls) as $value) {
                if (!empty($value))
                    $url_params['urls'][] = trim($value);
            }
        }
        if ($ecommerce !== NULL && !empty($ecommerce))
            $url_params['ecommerce'] = intval($ecommerce) > 0 ? 1 : 0;
        if ($siteSearch !== NULL && !empty($siteSearch))
            $url_params['siteSearch'] = intval($siteSearch) > 0 ? 1 : 0;
        if ($searchKeywordParameters !== NULL && !empty($searchKeywordParameters))
            $url_params['searchKeywordParameters'] = $searchKeywordParameters;
        if ($searchCategoryParameters !== NULL && !empty($searchCategoryParameters))
            $url_params['searchCategoryParameters'] = $searchCategoryParameters;
        if ($excludedIps !== NULL && !empty($excludedIps))
            $url_params['excludedIps'] = $excludedIps;
        if ($excludedQueryParameters !== NULL && !empty($excludedQueryParameters))
            $url_params['excludedQueryParameters'] = $excludedQueryParameters;
        if ($timezone !== NULL && !empty($timezone))
            $url_params['timezone'] = $timezone;
        if ($currency !== NULL && !empty($currency))
            $url_params['currency'] = $currency;
        if ($group !== NULL && !empty($group))
            $url_params['group'] = $group;
        if ($startDate !== NULL && !empty($startDate))
            $url_params['startDate'] = $startDate;
        if ($excludedUserAgents !== NULL && !empty($excludedUserAgents))
            $url_params['excludedUserAgents'] = $excludedUserAgents;
        if ($keepURLFragments !== NULL && !empty($keepURLFragments))
            $url_params['keepURLFragments'] = $keepURLFragments;
        if ($type !== NULL && !empty($type))
            $url_params['type'] = $type;
        if ($settings !== NULL && !empty($settings))
            $url_params['settings'] = $settings;

        if ($result = self::getAsJsonDecoded($url.http_build_query($url_params))) {
            if (self::DEBUGLOG == 1)
                self::DebugLogger(serialize($result));
            if (isset($result->result)) {
                self::$error = self::l(sprintf('Unknown response from Piwik API: [%s]',$result->result)).' - message: '.isset($result->message) ? $result->message : '';
                self::DebugLogger(self::$error);
                self::$errors[] = self::$error;
            } else if (isset($result->value)) {
                return $result->value;
            }
        }
        return FALSE;
    }

    /**
     * update Piwik site
     * @param type $idSite
     * @param type $siteName
     * @param array|string $urls if string all urls must be seperated by ',' (comma)
     * @param type $ecommerce
     * @param type $siteSearch
     * @param type $searchKeywordParameters
     * @param type $searchCategoryParameters
     * @param array|string $excludedIps if string all ip addresses must be seperated by ',' (comma)
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
    public static function updatePiwikSite($idSite,$siteName = NULL,$urls = NULL,$ecommerce = NULL,$siteSearch = NULL,$searchKeywordParameters = NULL,$searchCategoryParameters = NULL,$excludedIps = NULL,$excludedQueryParameters = NULL,$timezone = NULL,$currency = NULL,$group = NULL,$startDate = NULL,$excludedUserAgents = NULL,$keepURLFragments = NULL,$type = NULL) {
        if (!self::baseTest() || ($idSite <= 0))
            return false;
        $url = self::getBaseURL($idSite);
        $url .= "&method=SitesManager.updateSite&format=JSON";
        if ($siteName !== NULL)
            $url .= "&siteName=".urlencode($siteName);

        if ($urls !== NULL) {
            if (!is_array($urls)) {
                $urls=explode(',',$urls);
            }
            foreach ($urls as $value) {
                $url .= "&urls[]=".urlencode(trim($value));
            }
        }
        if ($ecommerce !== NULL)
            $url .= "&ecommerce=".urlencode($ecommerce);
        if ($siteSearch !== NULL)
            $url .= "&siteSearch=".urlencode($siteSearch);
        if ($searchKeywordParameters !== NULL)
            $url .= "&searchKeywordParameters=".urlencode($searchKeywordParameters);
        if ($searchCategoryParameters !== NULL)
            $url .= "&searchCategoryParameters=".urlencode($searchCategoryParameters);
        if ($excludedIps !== NULL) {
            if (is_array($excludedIps)) {
                $url .= "&excludedIps=";
                foreach ($excludedIps as $value)
                    $url .= urlencode(trim($value)).',';
                $url = trim($url,',');
            } else
                $url .= "&excludedIps=".urlencode($excludedIps);
        }
        if ($excludedQueryParameters !== NULL)
            $url .= "&excludedQueryParameters=".urlencode($excludedQueryParameters);
        if ($timezone !== NULL)
            $url .= "&timezone=".urlencode($timezone);
        if ($currency !== NULL)
            $url .= "&currency=".urlencode($currency);
        if ($group !== NULL)
            $url .= "&group=".urlencode($group);
        if ($startDate !== NULL)
            $url .= "&startDate=".urlencode($startDate);
        if ($excludedUserAgents !== NULL)
            $url .= "&excludedUserAgents=".urlencode($excludedUserAgents);
        if ($keepURLFragments !== NULL)
            $url .= "&keepURLFragments=".urlencode($keepURLFragments);
        if ($type !== NULL)
            $url .= "&type=".urlencode($type);
        if ($result = self::getAsJsonDecoded($url)) {
            $url2 = self::getBaseURL($idSite)."&method=SitesManager.getSiteFromId&format=JSON";
            unset(self::$_cachedResults[md5($url2)]); // Clear cache for updated site
            if ($result->result == 'error') {
                self::$error = $result->message;
                self::$errors[] = self::$error;
                return FALSE;
            }
            return ($result->result == 'success' && $result->message == 'ok' ? TRUE : ($result->result != 'success' ? $result->message : FALSE));
            
        } else
            return FALSE;
    }

    /**
     * get Piwik version
     * @return boolean|float boolean false on error
     */
    public static function getPiwikVersion() {
        if (!self::baseTest())
            return FALSE;
        $url = self::getBaseURL();
        $url .= "&method=API.getPiwikVersion&format=JSON";
        if ($result = self::getAsJsonDecoded($url))
            return (float)(isset($result->value) ? $result->value : 0.0);
        else
            return FALSE;
    }

    /**
     * get all website groups
     * @return array|boolean
     */
    public static function getSitesGroups() {
        if (!self::baseTest())
            return FALSE;
        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getSitesGroups&format=JSON";
        if ($result = self::getAsJsonDecoded($url))
            return $result;
        else
            return FALSE;
    }

    /**
     * get users token auth from Piwik
     * NOTE: password is required either an md5 encoded password or a normal string
     * @param string $userLogin the user name
     * @param string $password password in clear text
     * @param string $md5Password md5 encoded password
     * @return string|boolean
     */
    public static function getTokenAuth($userLogin,$password = NULL,$md5Password = NULL) {
        if ($password === null || empty($password)) {
            $password = $md5Password;
            if ($md5Password === NULL || empty($md5Password)) {
                self::$error = self::l('A password is required for method PKHelper::getTokenAuth()!');
                self::$errors[] = self::$error;
                return FALSE;
            }
        } else
            $password = md5($password);

        $url = self::getBaseURL(0,NULL,NULL,'API',NULL,'');
        $url .= "&method=UsersManager.getTokenAuth&userLogin={$userLogin}&md5Password={$password}&format=JSON";
        if ($result = self::getAsJsonDecoded($url)) {
            if (isset($result->result)) {
                self::$error = $result->message;
                self::$errors[] = self::$error;
            }
            return isset($result->value) ? $result->value : FALSE;
        } else
            return FALSE;
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

        $idSite = (int)Configuration::get(PKHelper::CPREFIX.'SITEID');
        if (!self::baseTest() || ($idSite <= 0))
            return $ret;

        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getImageTrackingCode&format=JSON&actionName=NoJavaScript";
        $url .= "&piwikUrl=".urlencode(rtrim(Configuration::get(PKHelper::CPREFIX.'HOST'),'/'));
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = $result;
            else
                self::$_cachedResults[$md5Url] = false;
        }
        if (self::$_cachedResults[$md5Url] !== FALSE) {
            $ret['default'] = htmlentities('<noscript>'.self::$_cachedResults[$md5Url]->value.'</noscript>');
            if ((bool)Configuration::get('PS_REWRITING_SETTINGS'))
                $ret['proxy'] = str_replace(Configuration::get(PKHelper::CPREFIX.'HOST').'piwik.php',Configuration::get(PKHelper::CPREFIX.'PROXY_SCRIPT'),$ret['default']);
            else
                $ret['proxy'] = str_replace(Configuration::get(PKHelper::CPREFIX.'HOST').'piwik.php?',Configuration::get(PKHelper::CPREFIX.'PROXY_SCRIPT').'&',$ret['default']);
        }
        return $ret;
    }

    /**
     * get Piwik site based on the current settings in the configuration
     * @return stdClass[]
     */
    public static function getPiwikSite($idSite = 0) {
        if ($idSite == 0)
            $idSite = (int)Configuration::get(PKHelper::CPREFIX.'SITEID');
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
            if (((bool)self::$_cachedResults[$md5Url][0]->ecommerce === false) || self::$_cachedResults[$md5Url][0]->ecommerce == 0) {
                self::$error = self::l('E-commerce is not active for your site in piwik!, you can enable it in the advanced settings on this page');
                self::$errors[] = self::$error;
            }
            if (((bool)self::$_cachedResults[$md5Url][0]->sitesearch) === false || self::$_cachedResults[$md5Url][0]->sitesearch == 0) {
                self::$error = self::l('Site search is not active for your site in piwik!, you can enable it in the advanced settings on this page');
                self::$errors[] = self::$error;
            }
            return self::$_cachedResults[$md5Url];
        }
        return false;
    }

    public static function getPiwikSite2($idSite = 0) {
        if ($idSite == 0)
            $idSite = (int)Configuration::get(PKHelper::CPREFIX.'SITEID');
        if ($result = self::getPiwikSite($idSite)) {
            $url = self::getBaseURL($idSite);
            $url .= "&method=SitesManager.getSiteUrlsFromId&format=JSON";
            if ($resultUrls = self::getAsJsonDecoded($url)) {
                $result[0]->main_url = implode(',',$resultUrls);
            }
            return $result;
        }
        return false;
    }

    /**
     * Get site urls by site id
     * @param int $idSite if 0 or lower gets the site id from configuration
     * @return boolean|array boolean false on error
     * @since 0.8.4
     */
    public static function getSiteUrlsFromId($idSite = 0) {
        if ($idSite <= 0)
            $idSite = (int)Configuration::get(PKHelper::CPREFIX.'SITEID');
        $url = self::getBaseURL($idSite);
        $url .= "&method=SitesManager.getSiteUrlsFromId&format=JSON";
        if ($resultUrls = self::getAsJsonDecoded($url)) {
            return $resultUrls;
        }
        return false;
    }

    /**
     * get all supported time zones from piwik
     * @param boolean $formlist true if return value is to be grouped for select input.
     * @param string $authtoken set null to use token from saved configuration
     * @param string $piwikhost set null to use host from saved configuration
     * @return array
     */
    public static function getTimezonesList($formlist = false,$authtoken = null,$piwikhost = null) {
        if (!self::baseTest($authtoken,$piwikhost))
            return array();
        $url = self::getBaseURL(0);
        $url .= "&method=SitesManager.getTimezonesList&format=JSON";
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = $result;
            else
                self::$_cachedResults[$md5Url] = array();
        }
        if ($formlist) {
            $pktimezones = array();
            foreach ((array)self::$_cachedResults[$md5Url] as $key => $pktz) {
                if (!isset($pktimezones[$key]))
                    $pktimezones[$key] = array('name' => $key,'query' => array());
                foreach ($pktz as $pktzK => $pktzV)
                    $pktimezones[$key]['query'][] = array('tzId' => $pktzK,'tzName' => $pktzV);
            }
            return $pktimezones;
        } else
            return self::$_cachedResults[$md5Url];
    }

    public static function getSitesWithViewAccess() {
        if (!self::baseTest())
            return array();
        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getSitesWithViewAccess&format=JSON";
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
     * Alias of PKHelper::getSitesWithAdminAccess()
     * get all Piwik sites the current authentication token has admin access to
     * @param boolean $fetchAliasUrls
     * @return stdClass[]
     */
    public static function getMyPiwikSites($fetchAliasUrls = false) {
        return self::getSitesWithAdminAccess($fetchAliasUrls);
    }

    /**
     * get all Piwik sites the current authentication token has admin access to
     * @param boolean $fetchAliasUrls
     * @param array $getBaseURLParams an array that contains the key=>value pair to use for method self::baseTest and self::getBaseURL
     * @return type
     */
    public static function getSitesWithAdminAccess($fetchAliasUrls = false,$getBaseURLParams = NULL) {
        if ($getBaseURLParams == NULL && !is_array($getBaseURLParams) || !(is_array($getBaseURLParams) && count($getBaseURLParams) == 6)) {
            if (!self::baseTest())
                return array();
            $url = self::getBaseURL(0);
        } else {
            extract($getBaseURLParams,EXTR_OVERWRITE);
            if (!self::baseTest($tokenAuth,$pkHost))
                return array();
            $url = self::getBaseURL($idSite,$pkHost,$https,$pkModule,$isoCode,$tokenAuth);
        }
        $url .= "&method=SitesManager.getSitesWithAdminAccess&format=JSON".($fetchAliasUrls ? '&fetchAliasUrls=1' : '');
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url."2"])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = $result;
            else
                self::$_cachedResults[$md5Url] = array();
        }
        return self::$_cachedResults[$md5Url];
    }

    /**
     * get all Piwik site IDs the current authentication token has admin access to
     * @return array
     */
    public static function getMyPiwikSiteIds() {
        if (!self::baseTest())
            return array();
        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getSitesIdWithAdminAccess&format=JSON";
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
     * get the base url for all requests to Piwik
     * @param integer $idSite
     * @param string $pkHost
     * @param boolean $https
     * @param string $pkModule
     * @param string $isoCode
     * @param string $tokenAuth
     * @return string
     */
    protected static function getBaseURL($idSite = NULL,$pkHost = NULL,$https = NULL,$pkModule = 'API',$isoCode = NULL,$tokenAuth = NULL) {
        if ($https === NULL)
            $https = (bool)Configuration::get(PKHelper::CPREFIX.'CRHTTPS');


        if (self::$piwikHost == "" || self::$piwikHost === false)
            self::$piwikHost = Configuration::get(PKHelper::CPREFIX.'HOST');

        if ($pkHost === NULL)
            $pkHost = self::$piwikHost;
        if ($isoCode === NULL)
            $isoCode = strtolower((isset(Context::getContext()->language->iso_code) ? Context::getContext()->language->iso_code : 'en'));
        if ($idSite === NULL)
            $idSite = Configuration::get(PKHelper::CPREFIX.'SITEID');
        if ($tokenAuth === NULL)
            $tokenAuth = Configuration::get(PKHelper::CPREFIX.'TOKEN_AUTH');

        $idSite_param = "&idSite={$idSite}";
        if ($idSite <= 0) {
            /* 0 or lower is not valid so we do not add it and
             * let the api tell us it's wrong if it is. */
            $idSite_param = "";
        }

        return ($https ? 'https' : 'http')."://{$pkHost}index.php?module={$pkModule}&language={$isoCode}{$idSite_param}&token_auth={$tokenAuth}";
    }

    /**
     * check if the basics are there before we make any piwik requests
     * @param string $authtoken set to null to use token from saved configuration
     * @param string $piwikhost set to null to use host from saved configuration
     * @return boolean
     */
    protected static function baseTest($authtoken = null,$piwikhost = null) {
        static $_error1 = FALSE;
        if ($authtoken === null)
            $authtoken = Configuration::get(PKHelper::CPREFIX.'TOKEN_AUTH');
        if ($piwikhost === null)
            $piwikhost = Configuration::get(PKHelper::CPREFIX.'HOST');
        if (empty($authtoken) || empty($piwikhost)) {
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
     * get output of api as json decoded object
     * @param string $url the full http(s) url to use for fetching the api result
     * @return boolean
     */
    protected static function getAsJsonDecoded($url) {
        $getF = self::get_http($url);
        if ($getF !== FALSE) {
            return Tools::jsonDecode($getF);
        }
        return FALSE;
    }

    public static function get_http($url,$headers = array()) {
        static $_error2 = FALSE;
        if (self::DEBUGLOG == 1)
            PKHelper::DebugLogger('START: PKHelper::get_http('.$url.',array(*extra headers*))');
        // class: Context is not loaded when using piwik.php proxy on prestashop 1.4
        if (class_exists('Context',FALSE))
            $lng = strtolower((isset(Context::getContext()->language->iso_code) ? Context::getContext()->language->iso_code : 'en'));
        else
            $lng = 'en';

        $timeout = 0;
        if (Configuration::hasKey(PKHelper::CPREFIX.'PROXY_TIMEOUT'))
            $timeout = Configuration::get(PKHelper::CPREFIX.'PROXY_TIMEOUT');
        if ((int)$timeout <= 0)
            $timeout = 5;

        if (self::$httpAuthUsername == "" || self::$httpAuthUsername === false)
            self::$httpAuthUsername = Configuration::get(PKHelper::CPREFIX.'PAUTHUSR');
        if (self::$httpAuthPassword == "" || self::$httpAuthPassword === false)
            self::$httpAuthPassword = Configuration::get(PKHelper::CPREFIX.'PAUTHPWD');

        $httpauth_usr = self::$httpAuthUsername;
        $httpauth_pwd = self::$httpAuthPassword;

        $use_cURL = (bool)Configuration::get(PKHelper::CPREFIX.'USE_CURL');
        if ($use_cURL === FALSE) {
            PKHelper::DebugLogger('Using \'file_get_contents\' to fetch remote');
            $httpauth = "";
            if ((!empty($httpauth_usr) && !is_null($httpauth_usr) && $httpauth_usr !== false) &&
                    (!empty($httpauth_pwd) && !is_null($httpauth_pwd) && $httpauth_pwd !== false)) {
                $httpauth = "Authorization: Basic ".base64_encode("$httpauth_usr:$httpauth_pwd")."\r\n";
            }
            $options = array(
                'http' => array(
                    'user_agent' => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : PKHelper::FAKEUSERAGENT),
                    'method' => "GET",
                    'timeout' => $timeout,
                    'header' => (!empty($headers) ? implode('',$headers) : "Accept-language: {$lng}\r\n").$httpauth
                )
            );
            $context = stream_context_create($options);

            if (self::DEBUGLOG == 1)
                PKHelper::DebugLogger('Calling: '.(!empty($httpauth) ? "With Http auth: " : "").$url);
            $result = @file_get_contents($url,false,$context);
            if ($result === FALSE) {
                $http_response = "";
                if (isset($http_response_header) && is_array($http_response_header)) {
                    foreach ($http_response_header as $value) {
                        if (preg_match("/^HTTP\/.*/i",$value)) {
                            $http_response = ':'.$value;
                        }
                    }
                }
                if (self::DEBUGLOG == 1) {
                    PKHelper::DebugLogger('request returned ERROR: http response: '.$http_response);
                    if (isset($http_response_header))
                        PKHelper::DebugLogger('$http_response_header: '.print_r($http_response_header,true));
                }
                if (!$_error2) {
                    self::$error = sprintf(self::l('Unable to connect to api%s')," {$http_response}");
                    self::$errors[] = self::$error;
                    $_error2 = TRUE;
                    if (self::DEBUGLOG == 1)
                        PKHelper::DebugLogger('Last error message: '.self::$error);
                }
            } else if (self::DEBUGLOG == 1) {
                PKHelper::DebugLogger('request returned OK');
            }
            if (self::DEBUGLOG == 1)
                PKHelper::DebugLogger('END: PKHelper::get_http(): OK');
            return $result;
        } else {
            PKHelper::DebugLogger('Using \'cURL\' to fetch remote');
            try {
                $ch = curl_init();
                PKHelper::DebugLogger("\t: \$ch = curl_init()");
                curl_setopt($ch,CURLOPT_URL,$url);
                PKHelper::DebugLogger("\t: curl_setopt(\$ch, CURLOPT_URL, $url)");
                // @TODO make this work, but how to filter out the headers from returned result??
                //curl_setopt($ch, CURLOPT_HEADER, 1);
                (!empty($headers) ?
                                curl_setopt($ch,CURLOPT_HTTPHEADER,$headers) :
                                curl_setopt($ch,CURLOPT_HTTPHEADER,array("Accept-language: {$lng}\r\n"))
                        );
                PKHelper::DebugLogger("\t: curl_setopt(\$ch, CURLOPT_HTTPHEADER, array(...))");
                curl_setopt($ch,CURLOPT_USERAGENT,(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : PKHelper::FAKEUSERAGENT));
                if ((!empty($httpauth_usr) && !is_null($httpauth_usr) && $httpauth_usr !== false) &&
                        (!empty($httpauth_pwd) && !is_null($httpauth_pwd) && $httpauth_pwd !== false))
                    curl_setopt($ch,CURLOPT_USERPWD,$httpauth_usr.":".$httpauth_pwd);
                curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
                curl_setopt($ch,CURLOPT_HTTPGET,1); // just to be safe
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
                curl_setopt($ch,CURLOPT_FAILONERROR,true);
                if (($return = curl_exec($ch)) === false) {
                    if (!$_error2) {
                        self::$error = curl_error($ch);
                        self::$errors[] = self::$error;
                        $_error2 = TRUE;
                    }
                    $return = false;
                }
                curl_close($ch);
                PKHelper::DebugLogger('END: PKHelper::get_http(): OK');
                return $return;
            } catch (Exception $ex) {
                self::$errors[] = $ex->getMessage();
                PKHelper::DebugLogger('Exception: '.$ex->getMessage());
                PKHelper::DebugLogger('END: PKHelper::get_http(): ERROR');
                return false;
            }
        }
    }

    /**
     * @see Module::l
     */
    private static function l($string,$specific = false) {
        if (version_compare(_PS_VERSION_,'1.5.0.13',"<="))
            return PKHelper::$_module->l($string,($specific) ? $specific : 'pkhelper');
        return Translate::getModuleTranslation('piwikanalyticsjs',$string,($specific) ? $specific : 'pkhelper');
        // the following lines are need for the translation to work properly
        // $this->l('I need Site ID and Auth Token before i can get your image tracking code')
        // $this->l('E-commerce is not active for your site in piwik!, you can enable it in the advanced settings on this page')
        // $this->l('Site search is not active for your site in piwik!, you can enable it in the advanced settings on this page')
        // $this->l('Unable to connect to api %s')
        // $this->l('E-commerce is not active for your site in piwik!')
        // $this->l('Site search is not active for your site in piwik!')
        // $this->l('A password is required for method PKHelper::getTokenAuth()!')
    }

    /**
     * get websites by group
     * NOTE: Not tested not in use by this module but here for the future, and may be removed.!
     * @param string $group
     * @return array|boolean
     */
    public static function getSitesFromGroup($group) {
        if (!self::baseTest())
            return FALSE;
        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getSitesFromGroup&format=JSON&group=".urlencode($group);
        if ($result = self::getAsJsonDecoded($url))
            return $result;
        else
            return FALSE;
    }

    /**
     * rename websites group
     * NOTE: Not tested not in use by this module but here for the future, and may be removed.!
     * @param string $oldGroupName
     * @param string $newGroupName
     * @return array|boolean
     */
    public static function renameGroup($oldGroupName,$newGroupName) {
        if (!self::baseTest())
            return FALSE;
        $url = self::getBaseURL();
        $url .= "&method=SitesManager.getSitesFromGroup&format=JSON"
                ."&oldGroupName=".urlencode($oldGroupName)
                ."&newGroupName=".urlencode($newGroupName);
        if ($result = self::getAsJsonDecoded($url))
            return $result;
        else
            return FALSE;
    }

}
