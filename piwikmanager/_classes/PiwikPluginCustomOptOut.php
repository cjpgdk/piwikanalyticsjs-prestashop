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
class PiwikPluginCustomOptOut extends PiwikHelper {
    /*
     * Not needed
      - CustomOptOut.isCssEditorEnabled()
      - CustomOptOut.getEditorTheme()
     */

    /**
     * get data from CustomOptOut related to $siteId
     * @param int $siteId
     * @return boolean
     */
    public static function GetSiteDataId($siteId) {
        if (!self::baseTest())
            return false;
        if (((int) $siteId == $siteId) && ((int) $siteId > 0)) {
            $url = self::getBaseURL($siteId, NULL, NULL, 'API', NULL, NULL);
            $url .= "&method=CustomOptOut.getSiteDataId&format=JSON";
            $md5Url = md5($url);
            if (!isset(self::$_cachedResults[$md5Url])) {
                if ($result = self::getAsJsonDecoded($url))
                    self::$_cachedResults[$md5Url] = $result;
                else
                    self::$_cachedResults[$md5Url] = false;
            }
            return self::$_cachedResults[$md5Url];
        } else {
            self::$error = "PiwikPluginCustomOptOut::GetSiteDataId(): site id is not valid";
            self::$errors[] = self::$error;
            return false;
        }
    }

    /**
     * Save the changes to CustomOptOut plugin for $siteId
     * @param int $siteId
     * @param string $customCss
     * @param string $customFile
     * @return mixed
     */
    public static function SaveSite($siteId, $customCss = "", $customFile = "") {
        if (!self::baseTest())
            return false;
        if (((int) $siteId == $siteId) && ((int) $siteId > 0)) {
            $url = self::getBaseURL($siteId, NULL, NULL, 'API', NULL, NULL);
            $url .= "&method=CustomOptOut.saveSite&format=JSON"
                    . "&siteId=" . urlencode($siteId)
                    . "&customCss=" . urlencode($customCss)
                    . "&customFile=" . urlencode($customFile);
        } else {
            self::$error = "PiwikPluginCustomOptOut::SaveSite(): site id is not valid";
            self::$errors[] = self::$error;
            return false;
        }
        $md5Url = md5($url);
        if (!isset(self::$_cachedResults[$md5Url])) {
            if ($result = self::getAsJsonDecoded($url))
                self::$_cachedResults[$md5Url] = $result;
            else
                self::$_cachedResults[$md5Url] = false;
        }
        if (self::$_cachedResults[$md5Url] !== false && isset(self::$_cachedResults[$md5Url]->result) && self::$_cachedResults[$md5Url]->result == 'error') {
            self::$error = (isset(self::$_cachedResults[$md5Url]->message) ? 'PiwikPluginCustomOptOut::SaveSite(): ' . self::$_cachedResults[$md5Url]->message : 'Unkown error from PiwikPluginCustomOptOut::SaveSite();');
            self::$errors[] = self::$error;
        }
        return self::$_cachedResults[$md5Url];
    }

}
