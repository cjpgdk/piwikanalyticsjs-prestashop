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

// ---
// this file contains the form definition for
// the advanced section of the configuration
// 
// i desided to move parts of the configuration form into seperated files
// this makes the module file smaller and easier to read,
// no other reason than that
// ---

if (!defined('_PS_VERSION_'))
    exit;

// primitive authentication to allow inclusion of this file
if (!defined('PIWIK_AUTHORIZED_ADV_FORM') || PIWIK_AUTHORIZED_ADV_FORM !== TRUE)
    die("Your are not authorized view this form [adv form]");



$fields_form[1]['form'] = array(
    'legend' => array(
        'title' => $this->displayName . ' ' . $this->l('Advanced'),
        'image' => $this->_path . 'logox22.png'
    ),
    'input' => array(
        array(
            'type' => 'html',
            'name' => $this->l('In this section you can modify certain aspects of the way this plugin sends products, searches, category view etc.. to piwik')
        ),
        array(
            'type' => 'switch',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Use HTTPS'),
            'name' => PKHelper::CPREFIX . 'CRHTTPS',
            'hint' => $this->l('ONLY enable this feature if piwik installation is accessible via https'),
            'desc' => $this->l('use Hypertext Transfer Protocol Secure (HTTPS) in all requests from code to piwik, this only affects how requests are sent from proxy script to piwik, your visitors will still use the protocol they visit your shop with'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled')
                )
            ),
        ),
        array(
            'type' => 'html',
            'name' => $this->l('in the next few inputs you can set how the product id is passed on to piwik')
            . '<br />'
            . $this->l('there are three variables you can use:')
            . '<br />'
            . $this->l('{ID} : this variable is replaced with id the product has in prestashop')
            . '<br />'
            . $this->l('{REFERENCE} : this variable is replaced with the unique reference you when adding adding/updating a product, this variable is only available in prestashop 1.5 and up')
            . '<br />'
            . $this->l('{ATTRID} : this variable is replaced with id the product attribute')
            . '<br />'
            . $this->l('in cases where only the product id is available it be parsed as ID and nothing else'),
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Product id V1'),
            'name' => PKHelper::CPREFIX . 'PRODID_V1',
            'desc' => $this->l('This template is used in case ALL three values are available ("Product ID", "Product Attribute ID" and "Product Reference")'),
            'required' => false
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Product id V2'),
            'name' => PKHelper::CPREFIX . 'PRODID_V2',
            'desc' => $this->l('This template is used in case only "Product ID" and "Product Reference" are available'),
            'required' => false
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Product id V3'),
            'name' => PKHelper::CPREFIX . 'PRODID_V3',
            'desc' => $this->l('This template is used in case only "Product ID" and "Product Attribute ID" are available'),
            'required' => false
        ),
        array(
            'type' => 'html',
            'name' => "<strong>{$this->l('Searches')}</strong>"
            . '<br />'
            . $this->l('the following input is used when a search is made with the page selection in use.')
            . '<br />'
            . $this->l('You can use the following variables')
            . '<br />'
            . '<strong>' . $this->l('{QUERY}') . '</strong> ' . $this->l('is replaced with the search query')
            . '<br />'
            . '<strong>' . $this->l('{PAGE}') . '</strong> ' . $this->l('is replaced with the page number'),
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Searches'),
            'name' => PKHelper::CPREFIX . 'SEARCH_QUERY',
            'desc' => $this->l('Template to use when a multipage search is made'),
            'required' => false
        ),
        array(
            'type' => 'html',
            'name' => "<strong>{$this->l('Proxy script')}</strong>"
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Timeout'),
            'name' => PKHelper::CPREFIX . 'PROXY_TIMEOUT',
            'desc' => $this->l('the maximum time in seconds to wait for proxied request to piwik'),
            'required' => false
        ),
        array(
            'type' => 'switch',
            'is_bool' => true, //retro compat 1.5
            'label' => $this->l('Use proxy script'),
            'name' => PKHelper::CPREFIX . 'USE_PROXY',
            'desc' => $this->l('Whether or not to use the proxy insted of Piwik Host'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled')
                )
            ),
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Proxy script'),
            'name' => PKHelper::CPREFIX . 'PROXY_SCRIPT',
            'hint' => $this->l('Example: www.example.com/pkproxy.php'),
            'desc' => sprintf($this->l('the FULL path to proxy script to use, build-in: [%s]'), self::getModuleLink($this->name, 'piwik')),
            'required' => false
        ),
        array(
            'type' => 'switch',
            'is_bool' => true,
            'label' => $this->l('Use cURL'),
            'name' => PKHelper::CPREFIX . 'USE_CURL',
            'desc' => $this->l('Whether or not to use cURL in Piwik API and proxy requests?'),
            'disabled' => (function_exists('curl_version')) ? false : true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled'),
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled'),
                )
            ),
        ), array(
            'type' => 'html',
            'name' => "<strong>{$this->l('Piwik Cookies')}</strong>"
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Piwik Session Cookie timeout'),
            'name' => PKHelper::CPREFIX . 'SESSION_TIMEOUT',
            'required' => false,
            'hint' => $this->l('this value must be set in minutes'),
            'desc' => $this->l('Piwik Session Cookie timeout, the default is 30 minutes'),
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Piwik Visitor Cookie timeout'),
            'name' => PKHelper::CPREFIX . 'COOKIE_TIMEOUT',
            'required' => false,
            'hint' => $this->l('this value must be set in minutes'),
            'desc' => $this->l('Piwik Visitor Cookie timeout, the default is 13 months (569777 minutes)'),
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Piwik Referral Cookie timeout'),
            'name' => PKHelper::CPREFIX . 'RCOOKIE_TIMEOUT',
            'required' => false,
            'hint' => $this->l('this value must be set in minutes'),
            'desc' => $this->l('Piwik Referral Cookie timeout, the default is 6 months (262974 minutes)'),
        ),
        array(
            'type' => 'html',
            'name' => "<strong>{$this->l('Piwik Proxy Script Authorization? if piwik is installed behind HTTP Basic Authorization (Both password and username must be filled before the values will be used)')}</strong>"
        ),
        array(
            'type' => 'text',
            'label' => $this->l('Proxy Script Username'),
            'name' => PKHelper::CPREFIX . 'PAUTHUSR',
            'required' => false,
            'autocomplete' => false,
            'desc' => $this->l('this field along with password can be used if piwik installation is protected by HTTP Basic Authorization'),
        ),
        array(
            'type' => 'password',
            'label' => $this->l('Proxy Script Password'),
            'name' => PKHelper::CPREFIX . 'PAUTHPWD',
            'required' => false,
            'autocomplete' => false,
            'desc' => $this->l('this field along with username can be used if piwik installation is protected by HTTP Basic Authorization'),
        ),
    ),
    'submit' => array(
        'title' => $this->l('Save'),
        'class' => 'btn btn-default'
    )
);
