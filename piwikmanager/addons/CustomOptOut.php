<?php

if (!defined('_PS_VERSION_'))
    exit;

include dirname(__FILE__) . '/../PKClassLoader.php';
PKClassLoader::LoadStatic(array('PiwikHelper', 'PiwikPluginCustomOptOut'));
PiwikHelper::initialize();

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
class CustomOptOut {

    private $module;
    private $controller;

    public function initialize($param = array()) {
        if (isset($param['controller']) && is_object($param['controller'])) {
            $this->controller = $param['controller'];
            if (property_exists($this->controller, 'module')) {
                $this->module = $this->controller->module;
            }
        }
    }

    public function CreateSiteSubmitForm() {
        if (Tools::isSubmit('submitAddPiwikAnalyticsSite')) {
            // not when the site is being created
        }
    }

    public function CreateSiteForm(& $helper, & $fields_form) {
        if (PiwikHelper::isPluginActive('CustomOptOut')) {
            $fields_form[0]['form']['input'][] = array(
                'type' => 'html',
                'name' => "<strong>{$this->l('Piwik Plugin \'CustomOptOut\', is available in site update, after the site has been created')}</strong>",
            );
        }
    }

    public function SiteEditSubmitForm() {
        if (Tools::isSubmit('submitUpdatePiwikAnalyticsSite')) {


            if ((Tools::getIsset('PKCustomCssFile') || Tools::getIsset('PKCustomCss')) && Tools::getIsset('CustomOptOut')) {
                PiwikPluginCustomOptOut::initialize();

                $customCssFile = Tools::getValue('PKCustomCssFile');
                $customCss = Tools::getValue('PKCustomCss');
                $siteId = Tools::getValue('idSite');
                $result = PiwikPluginCustomOptOut::SaveSite($siteId, $customCss, $customCssFile);
                //die('<pre>'.print_r($result, true));
            }
        }
    }

    public function SiteEditForm($idSite, & $helper, & $fields_form) {

        // add suport for Piwik plugin 'CustomOptOut'
        if (PiwikHelper::isPluginActive('CustomOptOut')) {
            PiwikPluginCustomOptOut::initialize();

            $PiwikPluginCustomOptOut = PiwikPluginCustomOptOut::GetSiteDataId($idSite);
            $PiwikPluginCustomOptOut = isset($PiwikPluginCustomOptOut[0]) &&
                    ( (is_array($PiwikPluginCustomOptOut[0]) && !empty($PiwikPluginCustomOptOut[0])) ||
                    (is_object($PiwikPluginCustomOptOut[0]) && !empty($PiwikPluginCustomOptOut[0])) ) ? (array) $PiwikPluginCustomOptOut[0] : array();

            if (!empty($PiwikPluginCustomOptOut)) {

                $this->controller->addCSS($this->module->getPathUri() . 'css/3party/CodeMirror/codemirror.css');
                $this->controller->addCSS($this->module->getPathUri() . 'css/3party/CodeMirror/show-hint.css');
                $this->controller->addJS($this->module->getPathUri() . 'js/3party/CodeMirror/codemirror.js');
                $this->controller->addJS($this->module->getPathUri() . 'js/3party/CodeMirror/css.js');
                $this->controller->addJS($this->module->getPathUri() . 'js/3party/CodeMirror/show-hint.js');
                $this->controller->addJS($this->module->getPathUri() . 'js/3party/CodeMirror/css-hint.js');

                if (isset($PiwikPluginCustomOptOut['custom_css']) || isset($PiwikPluginCustomOptOut['custom_css_file'])) {

                    $helper->fields_value['CustomOptOut'] = 1;
                    $fields_form[0]['form']['input'][] = array(
                        'type' => 'hidden',
                        'name' => 'CustomOptOut',
                    );
                    $fields_form[0]['form']['input'][] = array(
                        'type' => 'html',
                        'name' => "<strong>{$this->l('Piwik Plugin \'CustomOptOut\'')}</strong>"
                        . '<script>
$(document).ready(function () {
    var CodeMirrorEditor = CodeMirror.fromTextArea(document.getElementById("PKCustomCss"), {
        extraKeys: {"Ctrl-Space": "autocomplete"},
    });
});
</script>
<style>.CodeMirror {background: #f8f8f8;}</style>'
                    );
                }
                if (isset($PiwikPluginCustomOptOut['custom_css'])) {

                    $helper->fields_value['PKCustomCss'] = $PiwikPluginCustomOptOut['custom_css'];

                    $fields_form[0]['form']['input'][] = array(
                        'type' => 'textarea',
                        'rows' => 10,
                        'cols' => 50,
                        'label' => $this->l('Custom CSS'),
                        'name' => 'PKCustomCss',
                        'desc' => $this->l('Custom css'),
                        'required' => false,
                    );
                }
                if (isset($PiwikPluginCustomOptOut['custom_css_file'])) {

                    $helper->fields_value['PKCustomCssFile'] = $PiwikPluginCustomOptOut['custom_css_file'];

                    $fields_form[0]['form']['input'][] = array(
                        'type' => 'text',
                        'label' => $this->l('Custom CSS File'),
                        'name' => 'PKCustomCssFile',
                        'desc' => $this->l('Custom css file'),
                        'required' => false,
                        'autocomplete' => false,
                    );
                }
            }
        }
    }

    protected function l($string, $class = 'CustomOptOut', $addslashes = false, $htmlentities = true) {
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }

}
