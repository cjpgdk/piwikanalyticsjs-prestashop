<?php

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

/**
 * 
 * @param piwikanalyticsjs $module
 * @return boolean
 */
function upgrade_module_0_8_4($module) { //* @todo not actually in version 0.8.4 any more, more like 0.9  !
    if (!Configuration::hasKey('PIWIK_SEARCH_QUERY'))
        Configuration::updateValue('PIWIK_SEARCH_QUERY', '{QUERY} ({PAGE})');
    if (!Configuration::hasKey('PIWIK_PROXY_TIMEOUT'))
        Configuration::updateValue('PROXY_TIMEOUT', 5);

    if (Configuration::hasKey('PIWIK_PRODID_V3')){
        $tmp=Configuration::get('PIWIK_PRODID_V3');
        if ($tmp=="{ID}#{ATTRID}") // old value
            Configuration::updateValue('PIWIK_PRODID_V3', "{ID}-{ATTRID}"); // new value
    }
    return true;
}
