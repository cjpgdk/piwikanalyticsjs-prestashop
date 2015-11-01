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
class SampleAddon {

    private $module;
    private $controller;

    /**
     * Called when the class is loaded,
     * use this method to initialize the object
     * @param array $param
     */
    public function initialize($param = array()) {

        return; // we don't want to load samples

        if (isset($param['controller']) && is_object($param['controller'])) {
            $this->controller = & $param['controller'];
            if (property_exists($this->controller, 'module')) {
                $this->module = $this->controller->module;
            }
        }
    }
    
    /**
     * Called on create new site form is displayed.
     * @param Helper|HelperList $helper one of the " Helper* " clases found in "classes/helper"
     * @param array $fields_form the current array of form fields
     */
    public function CreateSiteForm(& $helper, & $fields_form) {
        return; // we don't want to load samples
        
        // same rules as in 'SiteEditForm', append/override your requirements
    }
    

    /**
     * Called when create new site form is submitted, before the site details are sent to piwik
     * after default create method
     */
    public function CreateSiteSubmitForm() {
        return; // we don't want to load samples
        
        if (Tools::isSubmit('submitAddPiwikAnalyticsSite')) {
            
            // @see SiteEditSubmitForm
            /*
             * to override data before we send the data to piwik
             */
            //[PKNewSiteName] => Webshop - Prestashop '1.6.0.1'
            //[PKNewMainUrl] => my-domain.tld
            //[PKNewAddtionalUrls] => catalog.my-domain.tld
            //[PKNewEcommerce] => 1 --- MUST BE '1' or '0'
            //[PKNewSiteSearch] => 1 --- MUST BE '1' or '0'
            //[PKNewSearchKeywordParameters] => search_query2,tag2
            //[PKNewSearchCategoryParameters] => cParameter,cParameter2,cParameter3
            //[PKNewExcludedIps] => 192.168.1.32,54.32.84.56
            //[PKNewExcludedQueryParameters] => qParam,qParam2,qParam3
            //[PKNewTimezone] => UTC
            //[PKNewCurrency] => EUR
            //[PKNewExcludedUserAgents] => 
            //[PKNewKeepURLFragments] => 0 --- MUST BE '1' or '0'
            //
            // NOT ISSET BUT SUPPORTED
            // set them via $_POST eg. "$_POST['PKNewSiteGroup'] = 'SampleAddonGroup';"
            // 
            //  - PKNewSiteGroup
            //  - PKNewSiteStartDate
            //  - PKNewSiteSettings
            //  - PKNewSiteType         : defaults to website if not isset
            //
            $_POST['PKNewSiteName'] = $_POST['PKNewSiteName'] . ' (Modyfied by SampleAddon)';
            
            /*
             * i do not set a group name from my code 
             * how ever if your addon makes use of groups you can set the group like this
             */
            $_POST['PKNewSiteGroup'] = 'SampleAddonGroup';
            
            
        }
    }
    // same as above but after the site is created in piwik
    public function CreateSiteSubmitFormAfter($idSite, $result) {
        return; // we don't want to load samples
        if ($result == 'OK') {
            // new site created with id $idSite
        }else if ($result == 'ERROR') {
            // no new site created or responce from api was unknown ($idSite == 0)
        }
    }

    /**
     * Called when Piwik site edit is submitted
     * after default update method
     */
    public function SiteEditSubmitForm() {
        return; // we don't want to load samples

        
        // use the tools and classes provided by prestashop core.

        // make sure this is a site edit submit..
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsSite')) {
            
            
            if (Tools::getIsset('MyCustomFormField')){
                $value = Tools::getValue('MyCustomFormField');
                Configuration::updateValue('MyCustomFormField', $value);
            }
        }
    }

    /**
     * Called when Piwik site edit is shown.
     * @param int $idSite the current Piwik site id being edited
     * @param Helper|HelperList $helper one of the " Helper* " clases found in "classes/helper"
     * @param array $fields_form the current array of form fields
     */
    public function SiteEditForm($idSite, & $helper, & $fields_form) {

        return; // we don't want to load samples

        /*
         * ONE RULE... 
         * YOU CANNOT change 
         * 
         * $helper->submit_action
         * 
         * !!well unless you know what you are doing!!
         */

        if ((int) $idSite <= 0)
            return;

        // do some fancy piwik lookups... 
        // @see CustomOptOut class
        $myPiwikApiResult = new stdClass();


        // append a few fields to main form

        $fields_form[0]['form']['input'][] = array(
            'type' => 'html',
            'name' => "<strong>{$this->l('Piwik Plugin \'SampleAddon\'')}</strong>",
        );

        $helper->fields_value['PKSampleAddonExtraField01'] = "Current value of PKSampleAddonExtraField01";

        $fields_form[0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Sample Addon Extra Field 01'),
            'name' => 'PKSampleAddonExtraField01',
            'desc' => $this->l('Sample Addon Extra Field 01'),
            'required' => false,
            'autocomplete' => false,
        );

        // etc.... as many fields as you like





        // create a new form

        $form_count = count($fields_form);

        $helper->fields_value['PKSiteName'] = "PK Site Name Field Value";
        $fields_form[$form_count]['form'] = array(
            'legend' => array(
                'title' => sprintf($this->l('Edit Piwik site (%s #%s)'), $helper->fields_value['PKSiteName'], $idSite),
                'image' => $this->module->getPathUri() . 'logox22.png'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Piwik Site Name'),
                    'name' => 'PKSiteName',
                    'desc' => $this->l('Name of this site in Piwik', 'PiwikAnalyticsSiteManager'),
                ),
            /*
              all inputs here
             */
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default'
            ),
            'reset' => array(
                'title' => $this->l('Reset'),
                'class' => 'btn btn-default',
                'icon' => 'process-icon-reset',
            ),
        );






        // override fields in the main form
        foreach ($fields_form[$form_count]['form']['input'] as $key => & $value) {

            if ($value['name'] == "PKSiteName") {
                // Piwik site name need a class
                $_curent_classes = "";
                if (isset($value['class']))
                    $_curent_classes = $value['class'];

                $value['class'] = "SampleAddon " . $_curent_classes;
                // new css must also be included
                $this->controller->addCSS($this->module->getPathUri() . 'addons/SampleAddon/SampleAddon.css');
            }
        }
        
        // etc.. you get the idea
    }

    /**
     * Non-static method which uses AdminController::translate()
     *
     * @param string  $string Term or expression in english
     * @param string|null $class Name of the class
     * @param bool $addslashes If set to true, the return value will pass through addslashes(). Otherwise, stripslashes().
     * @param bool $htmlentities If set to true(default), the return value will pass through htmlentities($string, ENT_QUOTES, 'utf-8')
     * @return string The translation if available, or the english default text.
     */
    protected function l($string, $class = 'SampleAddon', $addslashes = false, $htmlentities = true) {
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }

}
