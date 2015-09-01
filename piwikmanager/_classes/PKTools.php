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
class PKTools {

    /**
     * validate an url
     * @param string $url
     * @return boolean
     * @author Tim Groeneveld
     * @link http://stackoverflow.com/users/2143004/timgws
     * @link http://stackoverflow.com/a/21872143
     */
    public static function is_valid_url($url) {
        // First check: is the url just a domain name? (allow a slash at the end)
        $_domain_regex = "|^[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,})/?$|";
        if (preg_match($_domain_regex, $url)) {
            return true;
        }

        // Second: Check if it's a url with a scheme and all
        $_regex = '#^([a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))$#';
        if (preg_match($_regex, $url, $matches)) {
            // pull out the domain name, and make sure that the domain is valid.
            $_parts = parse_url($url);
            if (!in_array($_parts['scheme'], array('http', 'https')))
                return false;

            // Check the domain using the regex, stops domains like "-example.com" passing through
            if (!preg_match($_domain_regex, $_parts['host']))
                return false;

            // This domain looks pretty valid. Only way to check it now is to download it!
            return true;
        }

        return false;
    }

}
