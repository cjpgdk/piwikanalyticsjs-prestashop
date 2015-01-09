<?php

/*
 * Copyright (C) 2015 Christian Jensen
 *
 * This file is part of PiwikAnalyticsManager for prestashop.
 * 
 * PiwikAnalyticsManager for prestashop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * PiwikAnalyticsManager for prestashop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with PiwikAnalyticsManager for prestashop.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

class MyHelperClass {

    /** @return \HelperForm */
    public static function GetHelperFormObject($module = NULL, $name_controller = NULL, $identifier = NULL, $token = NULL) {
        $helper = new HelperForm();
        if (_PS_VERSION_ >= '1.5' && _PS_VERSION_ < '1.6') {
            $helper->base_folder = _PS_MODULE_DIR_ . 'piwikanalyticsmanager/views/templates/helpers/form/';
        }
        $helper->languages = self::GetLanguages(FALSE);
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        if ($module !== NULL) {
            $helper->module = $module;
        }
        if ($name_controller !== NULL) {
            $helper->name_controller = $name_controller;
        }
        if ($identifier !== NULL) {
            $helper->identifier = $identifier;
        }
        if ($token !== NULL) {
            $helper->token = $token;
        }
        return $helper;
    }

    /**
     * Return available languages as array
     * @param boolean $active Select only active languages
     * @return array
     */
    public static function GetLanguages($active) {
        $languages = Language::getLanguages($active);
        foreach ($languages as $languages_key => $languages_value) {
            $languages[$languages_key]['is_default'] = ($languages_value['id_lang'] == (int) Configuration::get('PS_LANG_DEFAULT') ? true : false);
        }
    }

}
