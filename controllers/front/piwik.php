<?php

/*
 *
 * ***** THIS FILE USES CODE FROM PIWIK PROXY SCRIPT *****
 * 
 * Piwik - free/libre analytics platform
 * Piwik Proxy Hide URL
 *
 * @link http://piwik.org/faq/how-to/#faq_132
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * 
 * ***** THIS FILE USES CODE FROM PIWIK PROXY SCRIPT *****
 * 
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
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

if (!defined('_PS_VERSION_'))
    exit;

if (!class_exists('PKHelper', FALSE))
    require dirname(__FILE__) . '/../../PKHelper.php';

class PiwikAnalyticsJSPiwikModuleFrontController extends ModuleFrontController {

    public function __construct() {

        // Edit the line below, and replace http://your-piwik-domain.example.org/piwik/
        // with your Piwik URL ending with a slash.
        // This URL will never be revealed to visitors or search engines.
        $PIWIK_URL = ((bool) Configuration::get('PIWIK_CRHTTPS') ? 'https://' : 'http://') . Configuration::get('PIWIK_HOST');

        // Edit the line below, and replace xyz by the token_auth for the user "UserTrackingAPI"
        // which you created when you followed instructions above.
        $TOKEN_AUTH = Configuration::get('PIWIK_TOKEN_AUTH');

        // 1) PIWIK.JS PROXY: No _GET parameter, we serve the JS file
        // @todo change to post, starting to get errors on server#a7 with to long request length
        if (
                (count($_GET) == 3 && Tools::getIsset('module') && Tools::getIsset('controller') && Tools::getIsset('fc')) ||
                (count($_GET) == 4 && Tools::getIsset('module') && Tools::getIsset('controller') && Tools::getIsset('fc') && Tools::getIsset('id_lang')) ||
                (count($_GET) == 5 && Tools::getIsset('module') && Tools::getIsset('controller') && Tools::getIsset('fc') && Tools::getIsset('id_lang') && Tools::getIsset('isolang'))
        ) {
            $modifiedSince = false;
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
                // strip any trailing data appended to header
                if (false !== ($semicolon = strpos($modifiedSince, ';'))) {
                    $modifiedSince = substr($modifiedSince, 0, $semicolon);
                }
                $modifiedSince = strtotime($modifiedSince);
            }
            // Re-download the piwik.js once a day maximum
            $lastModified = time() - 86400;
            // set HTTP response headers
            $this->sendHeader('Vary: Accept-Encoding');

            // Returns 304 if not modified since
            if (!empty($modifiedSince) && $modifiedSince < $lastModified) {
                $this->sendHeader(sprintf("%s 304 Not Modified", $_SERVER['SERVER_PROTOCOL']));
            } else {
                $this->sendHeader('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                $this->sendHeader('Content-Type: application/javascript; charset=UTF-8');
                $exHeaders = array(sprintf("Accept-Language: %s\r\n", @str_replace(array("\n", "\t", "\r"), "", $this->arrayValue($_SERVER, 'HTTP_ACCEPT_LANGUAGE', ''))));
                if ($piwikJs = PKHelper::get_http($PIWIK_URL . 'piwik.js', $exHeaders)) {
                    die($piwikJs);
                } else {
                    if (!empty(PKHelper::$errors)) {
                        foreach (PKHelper::$errors as $value) {
                            PKHelper::ErrorLogger($value);
                        }
                    }
                    $this->sendHeader($_SERVER['SERVER_PROTOCOL'] . '505 Internal server error');
                }
            }
            die();
        }
        // 2) PIWIK.PHP PROXY: GET parameters found, this is a tracking request, we redirect it to Piwik
        $url = sprintf("%spiwik.php?cip=%s&token_auth=%s&", $PIWIK_URL, $this->getVisitIp(), $TOKEN_AUTH);

        foreach ($_GET as $key => $value) {
            $url .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $this->sendHeader("Content-Type: image/gif");
        $content = PKHelper::get_http($url . (version_compare(PHP_VERSION, '5.3.0', '<') ? '&send_image=1' /* PHP 5.2 force returning */ : ''), array(sprintf("Accept-Language: %s\r\n", @str_replace(array("\n", "\t", "\r"), "", $this->arrayValue($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '')))));

        // Forward the HTTP response code
        // not for cURL, working on it. (@todo cURL response_header [piwik.php])
        if (!headers_sent() && isset($http_response_header[0])) {
            header($http_response_header[0]);
        }else if($content === false){
            sendHeader($_SERVER['SERVER_PROTOCOL'] . '505 Internal server error');
            $content = '/* there was an error loading piwik.js */';
        }
        die($content);
    }

    private function getVisitIp() {
        $matchIp = '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/';
        $ipKeys = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_CF_CONNECTING_IP',
        );
        foreach ($ipKeys as $ipKey) {
            if (isset($_SERVER[$ipKey]) && preg_match($matchIp, $_SERVER[$ipKey])) {
                return $_SERVER[$ipKey];
            }
        }
        return $this->arrayValue($_SERVER, 'REMOTE_ADDR');
    }

    private function sendHeader($header, $replace = true) {
        headers_sent() || header($header, $replace);
    }

    private function arrayValue($array, $key, $value = null) {
        if (!empty($array[$key])) {
            $value = $array[$key];
        }
        return $value;
    }

}
