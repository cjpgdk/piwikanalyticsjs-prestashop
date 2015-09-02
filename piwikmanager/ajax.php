<?php

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
 * 
 * 
 * 
 * 
 * UNTESTED, NOT SURE IF NEEDED..
 */
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

include(dirname(__FILE__) . '/PKClassLoader.php');

if (Tools::getIsset('pkapicall')) {
    // Call to Piwik api.
    // Method to call in PiwikHelper class
    $apiMethod = Tools::getValue('pkapicall');

    // The token used to authenticate the ajax call
    $apiAjaxToken = Tools::getValue('pkapitoken');

    // load PiwikHelper class
    PKClassLoader::LoadStatic('PiwikHelper');
    PiwikHelper::initialize();

    if (method_exists('PiwikHelper', $apiMethod) && isset(PiwikHelper::$allowed_ajax_methods[$apiMethod])) {
        $required = PiwikHelper::$allowed_ajax_methods[$apiMethod]['required'];
        // $optional = PiwikHelper::$allowed_ajax_methods[$apiMethod]['optional'];
        $order = PiwikHelper::$allowed_ajax_methods[$apiMethod]['order'];
        foreach ($required as $requiredOption) {
            if (!Tools::getIsset($requiredOption)) {
                die(Tools::jsonEncode(array('error' => true, 'message' => sprintf($this->l('Required parameter "%s" is missing'), $requiredOption))));
            }
        }
        foreach ($order as & $value) {
            if (Tools::getIsset($value)) {
                $value = Tools::getValue($value);
            } else {
                $value = NULL;
            }
        }
        $result = call_user_func_array(array('PiwikHelper', $apiMethod), $order);
        if ($result === FALSE) {
            die(Tools::jsonEncode(array('error' => TRUE, 'message' => $this->l('Unknown error occurred'))));
        } else {
            if (is_array($result) && isset($result[0])) {
                $message = $result;
            } else
                $message = (is_string($result) && !is_bool($result) ? $result : (is_array($result) ? implode(', ', $result) : TRUE));
            if (is_bool($message)) {
                die(Tools::jsonEncode(array('error' => FALSE, 'message' => $this->l('Successfully Updated'))));
            } else {
                die(Tools::jsonEncode(array('error' => FALSE, 'message' => $message)));
            }
        }
    } else {
        die(Tools::jsonEncode(array('error' => true, 'message' => sprintf($this->l('Method "%s" dos not exists in class PiwikHelper'), $apiMethod))));
    }
}