<?php

if (!defined('_PS_VERSION_'))
    exit;


if (!defined('PIWIK_CLASSES_PATH'))
    define("PIWIK_CLASSES_PATH", dirname(__FILE__) . '/_classes/');

if (!class_exists('PKClassLoader', FALSE)) {

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
    class PKClassLoader {

        static private $_loaded = array();

        public static function LoadAddons($initializeparams = array()) {
            $returnObj = array();
            $addons_dir = realpath(dirname(__FILE__) . '/addons/');
            if ($handle = opendir($addons_dir)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != ".." && (substr($entry, -4) == ".php")) {
                        include_once $addons_dir . '/' . $entry;
                        $class = substr($entry, 0, -4);
                        if (class_exists($class, false)) {
                            $obj = new $class();
                            if (method_exists($obj, 'initialize'))
                                $obj->initialize($initializeparams);
                            $returnObj[] = $obj;
                        }
                    }
                }
                closedir($handle);
            }
            return $returnObj;
        }

        /**
         * loads one or more classes as objects<br>
         * all classes must be named exactly the same as the file name<br>
         * for instance to load class 'PiwikHelper' the file must be named 'PiwikHelper.php'<br>
         * and be pressent in 'modules/piwikmanager/_classes/'
         * 
         * <pre><code>
         * PKClassLoader::Load(array('PiwikHelper', 'PKTools'));
         * 
         * $PKTools = PKClassLoader::Get('PKTools');
         * 
         * if(!$PKTools)
         *     $PKTools = PKClassLoader::Load('PKTools');
         * 
         * $PKTools->is_valid_url("http://piwik.org/");
         * 
         * $PiwikHelper = PKClassLoader::Get('PiwikHelper');
         * 
         * </code></pre>
         * @param array|string $classes
         * @param boolean $new if set to true the class will be reinitialized
         * @return object|void if $classes is a string the loaded class object is returned, otherwise nothing is returned
         * @throws Exception if the class or file to load is not found.
         */
        public static function Load($classes, $new = FALSE) {
            if (is_array($classes)) {
                // array, of classes to load
                foreach ($classes as $class) {
                    // load class if we can find it.
                    if (file_exists(PIWIK_CLASSES_PATH . $class . '.php')) {
                        if (!class_exists($class, FALSE))
                            include_once PIWIK_CLASSES_PATH . $class . '.php';
                    } else {
                        throw new Exception("Class, {$class} not found in '" . PIWIK_CLASSES_PATH . "'");
                    }
                    if (!class_exists($class, FALSE))
                        throw new Exception("Class, {$class} not found in '" . PIWIK_CLASSES_PATH . $class . ".php'");

                    if (!isset(self::$_loaded[$class])) {
                        self::$_loaded[$class] = new $class();
                    } else if (isset(self::$_loaded[$class]) && $new) {
                        // overide loaded object.
                        unset(self::$_loaded[$class]);
                        self::$_loaded[$class] = new $class();
                    }
                }
            } else {
                // load class if we can find it.
                if (file_exists(PIWIK_CLASSES_PATH . $classes . '.php')) {
                    if (!class_exists($classes, FALSE))
                        include_once PIWIK_CLASSES_PATH . $classes . '.php';
                } else {
                    throw new Exception("Class, {$classes} not found in '" . PIWIK_CLASSES_PATH . "'");
                }
                if (!class_exists($classes, FALSE))
                    throw new Exception("Class, {$classes} not found in '" . PIWIK_CLASSES_PATH . $classes . ".php'");

                if (!isset(self::$_loaded[$classes])) {
                    self::$_loaded[$classes] = new $classes();
                } else if (isset(self::$_loaded[$classes]) && $new) {
                    // overide loaded object.
                    unset(self::$_loaded[$classes]);
                    self::$_loaded[$classes] = new $classes();
                }
                return self::$_loaded[$classes];
            }
        }

        /**
         * load classes intended for static use only.
         * @param array|string $classes
         */
        public static function LoadStatic($classes) {
            if (is_array($classes)) {
                foreach ($classes as $class) {
                    self::Load($class);
                    unset(self::$_loaded[$class]);
                }
            } else {
                self::Load($classes);
                unset(self::$_loaded[$classes]);
            }
        }

        public static function Get($name) {
            if (isset(self::$_loaded[$name]))
                return self::$_loaded[$name];
            else
                return false;
        }
        
        public function __get($name) {
            if (isset(self::$_loaded[$name]))
                return self::$_loaded[$name];
            else
                return new stdClass();
        }

    }

}