<?php

/*
 * dev notes.
 * 
 * hooks:
 * 
 * - executed from smarty template, can be used by other modules to extend the piwik tracking code
 * 
 * piwikTrackerStart      : executed after getTracker() method call
 * piwikTrackerEnd        : executed before trackPageView() and before trackSiteSearch()
 * piwikTrackerPageView   : executed before trackPageView() method call 
 * piwikTrackerSiteSearch : executed before trackSiteSearch() method call 
 */

if (!defined('_PS_VERSION_'))
    exit;

include dirname(__FILE__) . '/../piwikmanager/PKClassLoader.php';
PKClassLoader::LoadStatic(array('PiwikHelper', 'PKTools'));
PiwikHelper::initialize();

/**
 * Copyright (C) 2015 Christian Jensen
 *
 * This file is part of PiwikAnalytics for prestashop.
 * 
 * PiwikAnalytics for prestashop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * PiwikAnalytics for prestashop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with PiwikAnalytics for prestashop.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @author Christian M. Jensen
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class piwikanalytics extends Module {

    /**
     * setReferralCookieTimeout
     */
    const PK_RC_TIMEOUT = 262974;

    /**
     * setVisitorCookieTimeout
     */
    const PK_VC_TIMEOUT = 569777;

    /**
     * setSessionCookieTimeout
     */
    const PK_SC_TIMEOUT = 30;

    private $_default_config_values = array();
    private static $isOrder = FALSE;

    /**
     * the default hooks to install
     * @var array
     */
    public $piwik_hooks = array(
        'displayHeader',
        'displayFooter',
        'actionSearch',
        'displayRightColumnProduct',
        'displayMaintenance',
        /* 'orderConfirmation', ///retro name */
        'displayOrderConfirmation',
    );

    public function __construct($name = null, $context = null) {

        $this->_default_config_values[PiwikHelper::CPREFIX . 'COOKIE_DOMAIN'] = Tools::getShopDomain();
        $this->_default_config_values[PiwikHelper::CPREFIX . 'COOKIE_TIMEOUT'] = self::PK_VC_TIMEOUT;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'SESSION_TIMEOUT'] = self::PK_SC_TIMEOUT;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'RCOOKIE_TIMEOUT'] = self::PK_RC_TIMEOUT;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'USE_PROXY'] = 0;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY'] = 'EUR';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'PRODID_V1'] = '{ID}-{ATTRID}#{REFERENCE}';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'PRODID_V2'] = '{ID}#{REFERENCE}';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'PRODID_V3'] = '{ID}#{ATTRID}';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'SET_DOMAINS'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'DNT'] = 0;
        $this->_default_config_values[PiwikHelper::CPREFIX . 'EXHTML'] = '';
        $this->_default_config_values[PiwikHelper::CPREFIX . 'COOKIE_PATH'] = '/';

        $this->dependencies[] = "piwikmanager";

        $this->name = 'piwikanalytics';
        $this->tab = 'analytics_stats';
        $this->version = '1.0';
        $this->author = 'Christian M. Jensen';
        $this->displayName = 'Piwik Analytics Tracking';
        $this->author_uri = 'http://cmjscripter.net';
        $this->url = 'http://cmjnisse.github.io/piwikanalyticsjs-prestashop/';

        $this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => '1.6.999.999');

        $this->bootstrap = true;

        // list front controlers
        $this->controllers = array('piwik');

        parent::__construct($name, $context);

        $this->description = $this->l('Adds Piwik Analytics JavaScript Tracking code to your shop');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        self::$isOrder = false;

        if (is_object($this->context->smarty) && (!is_object($this->smarty) || !($this->smarty instanceof Smarty_Data))) {
            $this->smarty = $this->context->smarty->createData($this->context->smarty);
        }

        if (Configuration::getGlobalValue('PIWIKHOOKSFAIL')) {
            Configuration::deleteByName('PIWIKHOOKSFAIL');
            if (!$this->installHooks())
                $this->warning .= $this->l('some or all hooks faild to register..');
        }
    }

    public function getContent() {
        header('Location: index.php?controller=PiwikAnalyticsTrackingConfig&token=' . Tools::getAdminTokenLite('PiwikAnalyticsTrackingConfig'));
        exit;
    }

    /* ## HOOKs ## */

    public function hookdisplayOrderConfirmation($params) {
        return $this->hookOrderConfirmation($params);
    }

    // retro name
    public function hookOrderConfirmation($params) {
        // short code the config prefix
        $CPREFIX = PiwikHelper::CPREFIX;
        if ((int) Configuration::get($CPREFIX . 'SITEID') <= 0)
            return "";

        $order = $params['objOrder'];
        if (Validate::isLoadedObject($order)) {
            // get all the required data from db config
            $dbConfigKeys = array(
                $CPREFIX . 'SITEID',
                $CPREFIX . 'USE_PROXY',
                $CPREFIX . 'PROXY_SCRIPT',
                $CPREFIX . 'HOST',
                $CPREFIX . 'COOKIE_DOMAIN',
                $CPREFIX . 'SET_DOMAINS',
                $CPREFIX . 'COOKIE_TIMEOUT',
                $CPREFIX . 'RCOOKIE_TIMEOUT',
                $CPREFIX . 'SESSION_TIMEOUT',
                $CPREFIX . 'DNT',
                $CPREFIX . 'EXHTML',
            );
            $dbConfigValues = Configuration::getMultiple($dbConfigKeys);

            // get http protocol
            $protocol = Tools::getShopProtocol();

            $this->assignDefaults($dbConfigValues, $protocol);

            $this->smartyAssign('IsOrder', TRUE);
            $this->smartyAssign('IsCart', FALSE);


            $smarty_ad = array();
            foreach ($params['objOrder']->getProductsDetail() as $value) {
                $smarty_ad[] = array(
                    'sku' => $this->parseProductSku($value['product_id'], (isset($value['product_attribute_id']) ? $value['product_attribute_id'] : FALSE), (isset($value['product_reference']) ? $value['product_reference'] : FALSE)),
                    'name' => $value['product_name'],
                    'category' => $this->getCategoriesByProductId($value['product_id'], FALSE),
                    'price' => $this->currencyConvertion(
                            array(
                                'price' => (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : 0.00)),
                                'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                            )
                    ),
                    'quantity' => $value['product_quantity'],
                );
            }
            $this->smartyAssign('OrderProducts', $smarty_ad);

            if (isset($params['objOrder']->total_paid_tax_incl) && isset($params['objOrder']->total_paid_tax_excl))
                $tax = floatval($params['objOrder']->total_paid_tax_incl - $params['objOrder']->total_paid_tax_excl);
            else if (isset($params['objOrder']->total_products_wt) && isset($params['objOrder']->total_products))
                $tax = floatval($params['objOrder']->total_products_wt - $params['objOrder']->total_products);
            else
                $tax = 0.00;

            $ORDER_DETAILS = array(
                'id' => $params['objOrder']->id,
                'total' => $this->currencyConvertion(
                        array(
                            'price' => floatval(isset($params['objOrder']->total_paid_tax_incl) ? $params['objOrder']->total_paid_tax_incl : (isset($params['objOrder']->total_paid) ? $params['objOrder']->total_paid : 0.00)),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
                'sub_total' => $this->currencyConvertion(
                        /*
                         * 
                         * .... to any one, reading this ....
                         * 
                         * sub total: 
                         * the total amount of all products including tax 
                         * (prestashop removes taxes automaticly if the configuration says so.)
                         *
                         * were total is the total of the order, product + tax + shipping - discount etc.. 
                         * ... 
                         * 
                         * if i'm wrong about that, please enlighten me.
                         * 
                         */
                        array(
                            'price' => floatval($params['objOrder']->total_products_wt),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
                'tax' => $this->currencyConvertion(
                        array(
                            'price' => floatval($tax),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
                'shipping' => $this->currencyConvertion(
                        array(
                            'price' => floatval((isset($params['objOrder']->total_shipping_tax_incl) ? $params['objOrder']->total_shipping_tax_incl : (isset($params['objOrder']->total_shipping) ? $params['objOrder']->total_shipping : 0.00))),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
                'discount' => $this->currencyConvertion(
                        array(
                            'price' => (isset($params['objOrder']->total_discounts_tax_incl) ?
                                    ($params['objOrder']->total_discounts_tax_incl > 0 ?
                                            floatval($params['objOrder']->total_discounts_tax_incl) : false) : (isset($params['objOrder']->total_discounts) ?
                                            ($params['objOrder']->total_discounts > 0 ?
                                                    floatval($params['objOrder']->total_discounts) : false) : 0.00)),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        )
                ),
            );
            $this->smartyAssign('OrderDetails', $ORDER_DETAILS);

            // avoid double tracking on complete order.
            self::$isOrder = TRUE;

            // return the template for piwik tracking.
            return $this->display(__FILE__, 'piwikAsync.tpl');
        }
    }

    /**
     * hook into maintenance page.
     * @param array $params
     * @return string
     */
    public function hookdisplayMaintenance($params) {
        $this->smartyAssign('MaintenanceTitle', $this->l('Maintenance mode'));
        return $this->hookdisplayFooter($params);
    }

    /**
     * if product is view in content only mode footer is not called
     * @param mixed $param
     * @return string $this->hookdisplayFooter($param);
     */
    public function hookdisplayRightColumnProduct($param) {
        if ((int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID') <= 0)
            return "";
        if ((int) Tools::getValue('content_only') > 0 && get_class($this->context->controller) == 'ProductController') {
            return $this->hookdisplayFooter($param);
        }
    }

    /**
     * Search action
     * @param array $param
     */
    public function hookactionSearch($param) {
        if ((int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID') <= 0)
            return "";
        $param['total'] = intval($param['total']);
        /* if multi pages in search add page number of current if set!
         * @todo maby add this as an option in config. like product id.!
         */
        $page = "";
        if (Tools::getIsset('p')) {
            $page = " (" . Tools::getValue('p') . ")";
        }
        // $param['expr'] is not the searched word if lets say search is 
        // 'æøåü' then the value of $param['expr'] will be 'aeoau'
        $expr = Tools::getIsset('search_query') ? htmlentities(Tools::getValue('search_query')) : $param['expr'];
        $this->smartyAssign('IsSearch', true);
        $this->smartyAssign('SearchWord', $expr . $page);
        $this->smartyAssign('SearchTotal', $param['total']);
    }

    /**
     * only checks that the module is registered in hook "footer", 
     * this why we only appent javescript to the end of the page!
     * @param mixed $params
     */
    public function hookdisplayHeader($params) {
        if (!$this->isRegisteredInHook('footer') && !$this->isRegisteredInHook('displayFooter'))
            $this->registerHook('displayFooter');
    }

    /**
     * add Piwik tracking code to the footer
     * @param type $params
     * @return string html string for the footer
     */
    public function hookdisplayFooter($params) {

        // short code the config prefix
        $CPREFIX = PiwikHelper::CPREFIX;

        // get all the required data from db config
        $dbConfigKeys = array(
            $CPREFIX . 'SITEID',
            $CPREFIX . 'USE_PROXY',
            $CPREFIX . 'PROXY_SCRIPT',
            $CPREFIX . 'HOST',
            $CPREFIX . 'COOKIE_DOMAIN',
            $CPREFIX . 'SET_DOMAINS',
            $CPREFIX . 'COOKIE_TIMEOUT',
            $CPREFIX . 'RCOOKIE_TIMEOUT',
            $CPREFIX . 'SESSION_TIMEOUT',
            $CPREFIX . 'DNT',
            $CPREFIX . 'EXHTML',
        );
        $dbConfigValues = Configuration::getMultiple($dbConfigKeys);

        // if site id is wrong, 
        if ((int) $dbConfigValues[$CPREFIX . 'SITEID'] <= 0)
            return "";
        // if is order.
        if (self::$isOrder)
            return "";

        // get http protocol
        $protocol = Tools::getShopProtocol();

        $this->assignDefaults($dbConfigValues, $protocol);
        $this->smartyAssign('IsOrder', FALSE);

        // do 404 check.
        $this->assign404();

        // product view ??
        $this->assignProductView();

        /* category view ?? */
        if (get_class($this->context->controller) == 'CategoryController') {
            $category = $this->context->controller->getCategory();
            if (Validate::isLoadedObject($category)) {
                $this->smartyAssign('Category', $category->name);
            }
        }

        /* cart tracking */
        $this->assignCart();

        // return the template for piwik tracking.
        return $this->display(__FILE__, 'piwikAsync.tpl');
    }

    /* ## Helpers ## */

    private function assignDefaults($dbConfigValues, $protocol) {
        $CPREFIX = PiwikHelper::CPREFIX;

        // set config variables for piwik tracking template

        $this->smartyAssign('Protocol', $protocol);
        $this->smartyAssign('IdSite', (int) $dbConfigValues[$CPREFIX . 'SITEID']);
        $this->smartyAssign('UseProxy', (boolean) $dbConfigValues[$CPREFIX . 'USE_PROXY']);
        $this->smartyAssign('CookieDomain', $dbConfigValues[$CPREFIX . 'COOKIE_DOMAIN']);

        // do not track
        if ((bool) $dbConfigValues[$CPREFIX . 'DNT']) {
            $this->smartyAssign('DNT', true);
        } else {
            $this->smartyAssign('DNT', false);
        }

        // setDomains
        $piwikSetDomains = $dbConfigValues[$CPREFIX . 'SET_DOMAINS'];
        if (!empty($piwikSetDomains)) {
            $sdArr = explode(',', $piwikSetDomains);
            if (count($sdArr) > 1)
                $piwikSetDomains = "['" . trim(implode("','", $sdArr), ",'") . "']";
            else
                $piwikSetDomains = "'{$sdArr[0]}'";

            $this->smartyAssign('SetDomains', $piwikSetDomains);

            unset($sdArr);
        }

        // setVisitorCookieTimeout
        $pkvct = (int) $dbConfigValues[$CPREFIX . 'COOKIE_TIMEOUT'];
        if ($pkvct > 0 && $pkvct !== FALSE && ($pkvct != (int) (self::PK_VC_TIMEOUT))) {
            $this->smartyAssign('VisitorCookieTimeout', ($pkvct * 60));
        }
        unset($pkvct);

        // setReferralCookieTimeout
        $pkrct = (int) $dbConfigValues[$CPREFIX . 'RCOOKIE_TIMEOUT'];
        if ($pkrct > 0 && $pkrct !== FALSE && ($pkrct != (int) (self::PK_RC_TIMEOUT))) {
            $this->smartyAssign('ReferralCookieTimeout', ($pkrct * 60));
        }
        unset($pkrct);

        // setSessionCookieTimeout
        $pksct = (int) $dbConfigValues[$CPREFIX . 'SESSION_TIMEOUT'];
        if ($pksct > 0 && $pksct !== FALSE && ($pksct != (int) (self::PK_SC_TIMEOUT))) {
            $this->smartyAssign('SessionCookieTimeout', ($pksct * 60));
        }
        unset($pksct);

        // piwik url
        if ((bool) $dbConfigValues[$CPREFIX . 'USE_PROXY']) {
            $this->smartyAssign('Host', $dbConfigValues[$CPREFIX . 'PROXY_SCRIPT']);
        } else {
            $this->smartyAssign('Host', $dbConfigValues[$CPREFIX . 'HOST']);
        }

        // customer id
        if ($this->context->customer->isLogged()) {
            $this->smartyAssign('UserId', $this->context->customer->id);
        }

        // extra html.
        if (!empty($dbConfigValues[$CPREFIX . 'EXHTML'])) {
            $this->smartyAssign('EXHTML', $dbConfigValues[$CPREFIX . 'EXHTML']);
        }
    }

    /**
     * Track cart updates
     */
    private function assignCart() {
        if (!$this->context->cookie->PiwikCartUpdateTime) {
            $this->context->cookie->PiwikCartUpdateTime = strtotime($this->context->cart->date_upd) - 1;
            $this->context->cookie->PiwikCartUProductsCount = 0;
        }
        if (strtotime($this->context->cart->date_upd) >= $this->context->cookie->PiwikCartUpdateTime) {
            $this->context->cookie->PiwikCartUpdateTime = strtotime($this->context->cart->date_upd) + 1;
            $smarty_ad = array();
            $Currency = new Currency($this->context->cart->id_currency);
            foreach ($this->context->cart->getProducts() as $product) {
                if (!isset($product['id_product']) || !isset($product['name']) || !isset($product['total_wt']) || !isset($product['quantity'])) {
                    continue;
                }
                $smarty_ad[] = array(
                    'sku' => $this->parseProductSku($product['id_product'], (isset($product['id_product_attribute']) && $product['id_product_attribute'] > 0 ? $product['id_product_attribute'] : FALSE), (isset($product['reference']) ? $product['reference'] : FALSE)),
                    /*
                     * @todo give product name the same options as 'sku'
                     */
                    'name' => $product['name'] . (isset($product['attributes']) ? ' (' . $product['attributes'] . ')' : ''),
                    'category' => $this->getCategoriesByProductId($product['id_product'], FALSE),
                    'price' => $this->currencyConvertion(
                            array(
                                'price' => $product['total_wt'],
                                'conversion_rate' => $Currency->conversion_rate,
                            )
                    ),
                    'quantity' => $product['quantity'],
                );
            }
            if (count($smarty_ad) > 0) {
                $this->context->cookie->PiwikCartUProductsCount = count($smarty_ad);
                $this->smartyAssign('IsCart', TRUE);
                $this->smartyAssign('CartProducts', $smarty_ad);
                $this->smartyAssign('CartTotal', $this->currencyConvertion(array(
                            'price' => $this->context->cart->getOrderTotal(),
                            'conversion_rate' => $Currency->conversion_rate,
                )));
            } else {
                if ($this->context->cookie->PiwikCartUProductsCount > 0) {
                    $this->context->cookie->PiwikCartUProductsCount = 0;
                    // user deleted the entire cart, lets report this to piwik
                    $this->smartyAssign('IsCart', TRUE);
                    $this->smartyAssign('CartProducts', array());
                    $this->smartyAssign('CartTotal', 0.00);
                } else {
                    $this->smartyAssign('IsCart', FALSE);
                }
            }
        } else {
            if ($this->context->cookie->PiwikCartUProductsCount > 0) {
                $this->context->cookie->PiwikCartUProductsCount = 0;
                // user deleted the entire cart, lets report this to piwik
                $this->smartyAssign('IsCart', TRUE);
                $this->smartyAssign('CartProducts', array());
                $this->smartyAssign('CartTotal', 0.00);
            } else {
                $this->smartyAssign('IsCart', FALSE);
            }
        }
    }

    /**
     * check if its a product page view page and assign the variables to smarty
     */
    private function assignProductView() {
        if (get_class($this->context->controller) == 'ProductController') {
            $smarty_ad = array();
            $product = $this->context->controller->getProduct();
            if (!empty($product) && $product !== false && Validate::isLoadedObject($product)) {
                $categories = $this->getCategoriesByProductId($product->id, FALSE);
                $smarty_ad[] = array(
                    'sku' => $this->parseProductSku($product->id, FALSE, (isset($product->reference) ? $product->reference : FALSE)),
                    /*
                     * @todo give product name the same options as 'sku'
                     */
                    'name' => $product->name,
                    'category' => $categories,
                    'price' => $this->currencyConvertion(
                            array(
                                'price' => Product::getPriceStatic($product->id, true, false),
                                'conversion_rate' => $this->context->currency->conversion_rate,
                            )
                    ),
                );
                $this->smartyAssign('Products', $smarty_ad);
            }
        }
    }

    /**
     * check if its a 404 page and assign the 404 variable for smarty
     */
    private function assign404() {

        $is404 = false;
        if (!empty($this->context->controller->errors)) {
            foreach ($this->context->controller->errors as $key => $value) {
                if ($value == Tools::displayError('Product not found'))
                    $is404 = true;
                if ($value == Tools::displayError('This product is no longer available.'))
                    $is404 = true;
            }
        }
        if (
                (strtolower(get_class($this->context->controller)) == 'pagenotfoundcontroller') ||
                (isset($this->context->controller->php_self) && ($this->context->controller->php_self == '404')) ||
                (isset($this->context->controller->page_name) && (strtolower($this->context->controller->page_name) == 'pagenotfound'))
        ) {
            $is404 = true;
        }

        $this->smartyAssign("404", $is404);
    }

    /**
     * convert into default currentcy used in piwik
     * @param array $params
     * @return float
     */
    private function currencyConvertion($params) {
        $pkc = Configuration::get(PiwikHelper::CPREFIX . 'DEFAULT_CURRENCY');
        if (empty($pkc))
            return (float) $params['price'];
        if ($params['conversion_rate'] === FALSE || $params['conversion_rate'] == 0.00 || $params['conversion_rate'] == 1.00) {
            //* shop default
            return Tools::convertPrice((float) $params['price'], Currency::getCurrencyInstance((int) (Currency::getIdByIsoCode($pkc))));
        } else {
            $_shop_price = (float) ((float) $params['price'] / (float) $params['conversion_rate']);
            return Tools::convertPrice($_shop_price, Currency::getCurrencyInstance((int) (Currency::getIdByIsoCode($pkc))));
        }
        return (float) $params['price'];
    }

    /**
     * get category names by product id
     * @param integer $id product id
     * @param boolean $array get categories as PHP array (TRUE), or javascript (FAlSE)
     * @return string|array
     */
    private function getCategoriesByProductId($id, $array = true) {
        $_categories = Product::getProductCategoriesFull($id, $this->context->cookie->id_lang);
        if (!is_array($_categories)) {
            if ($array)
                return array();
            else
                return "[]";
        }

        if ($array) {
            $categories = array();
            foreach ($_categories as $category) {
                $categories[] = $category['name'];
                if (count($categories) == 5)
                    break;
            }
        } else {
            $categories = '[';
            $c = 0;
            foreach ($_categories as $category) {
                $c++;
                $categories .= '"' . $category['name'] . '",';
                if ($c == 5)
                    break;
            }
            $categories = rtrim($categories, ',');
            $categories .= ']';
        }
        return $categories;
    }

    private function parseProductSku($id, $attrid = FALSE, $ref = FALSE) {
        if (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== FALSE) && (!empty($ref) && !is_null($ref) && $ref !== FALSE)) {
            $PIWIK_PRODID_V1 = Configuration::get(PiwikHelper::CPREFIX . 'PRODID_V1');
            return str_replace(array('{ID}', '{ATTRID}', '{REFERENCE}'), array($id, $attrid, $ref), $PIWIK_PRODID_V1);
        } elseif (Validate::isInt($id) && (!empty($ref) && !is_null($ref) && $ref !== FALSE)) {
            $PIWIK_PRODID_V2 = Configuration::get(PiwikHelper::CPREFIX . 'PRODID_V2');
            return str_replace(array('{ID}', '{REFERENCE}'), array($id, $ref), $PIWIK_PRODID_V2);
        } elseif (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== FALSE)) {
            $PIWIK_PRODID_V3 = Configuration::get(PiwikHelper::CPREFIX . 'PRODID_V3');
            return str_replace(array('{ID}', '{ATTRID}'), array($id, $attrid), $PIWIK_PRODID_V3);
        } else {
            return $id;
        }
    }

    /**
     * used to prefix all smarty variables with 'piwik', to avoid overriding any other variables from other modules
     * @param string $key
     * @param mixed $value
     */
    private function smartyAssign($key, $value) {
        $smarty_prefix = "piwik";
        $this->smarty->assign($smarty_prefix . $key, $value);
    }

    /* ## Install/Uninstall/Enable/Disable ## */

    /**
     * Activate module.
     * @param bool $force_all If true, enable module for all shop
     */
    public function enable($force_all = false) {
        if (!Module::isInstalled('piwikmanager') || !Module::isEnabled('piwikmanager')) {
            $this->_errors[] = Tools::displayError(sprintf($this->l('Can not enable %s, depends on module piwikmanager'), $this->displayName));
            return false;
        }
        return parent::enable($force_all);
    }

    public function isConfigured() {
        if (!Module::isInstalled('piwikmanager') || !Module::isEnabled('piwikmanager')) {
            return false;
        }
        $piwik_sites = PiwikHelper::getSitesWithAdminAccess();
        if (empty($piwik_sites)) {
            return false;
        }
        if ((int) Configuration::get(PiwikHelper::CPREFIX . 'SITEID') <= 0)
            return false;
        return true;
    }

    /**
     * Install the module
     * @return boolean false on install error
     */
    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        /* default values */
        foreach ($this->_default_config_values as $key => $value) {
            /* only set if not isset, compatibility with module 'piwikanalyticsjs' */
            if (Configuration::getGlobalValue($key) === false)
                Configuration::updateGlobalValue($key, $value);
        }

        // we try here, if fail we try again in construct when loaded,
        // if still not in the required hooks we isset a warning
        if (!$this->installHooks())
            Configuration::updateGlobalValue('PIWIKHOOKSFAIL', 1);

        return parent::install() && $this->installTabs();
    }

    /**
     * check if all hooks are installed, (if not set to ignore)
     * @return boolean
     */
    public function checkHooks() {
        $ret = true;
        foreach ($this->piwik_hooks as $key => $value)
            if (!$this->isRegisteredInHook($value) && !Configuration::get(PiwikHelper::CPREFIX . 'IGNORE' . $key))
                $ret = false;
        return $ret;
    }

    /**
     * intall/register hooks
     * @return boolean
     */
    public function installHooks() {
        $ret = true;
        foreach ($this->piwik_hooks as $value)
            if (!$this->registerHook($value))
                $ret = false;
        return $ret;
    }

    /**
     * Uninstall the module
     * @return boolean false on uninstall error
     */
    public function uninstall() {
        foreach ($this->_default_config_values as $key => $value) {
            // delete only from the shop wee uninstall
            Configuration::deleteFromContext($key);
        }
        // Tabs
        $idTabs = array();
        $idTabs[] = (int) Tab::getIdFromClassName('PiwikAnalyticsTrackingConfig');
        foreach ($idTabs as $idTab) {
            if ($idTab > 0) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }
        return parent::uninstall();
    }

    private function installTabs() {
        // Parent
        $parent_tab_id = Tab::getIdFromClassName('PiwikAnalytics');
        if ($parent_tab_id === false)
            $parent_tab_id = 0;

        // Tracking Config
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = $this->l('Tracking Config');
        $tab->class_name = 'PiwikAnalyticsTrackingConfig';
        $tab->id_parent = $parent_tab_id;
        $tab->module = $this->name;
        $tab->add();

        return true;
    }

}
