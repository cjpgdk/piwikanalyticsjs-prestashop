<?php

if (!defined('_PS_VERSION_'))
    exit;
if (!class_exists('PKClassLoader', false))
    include dirname(__FILE__) . '/../piwikmanager/PKClassLoader.php';
PKClassLoader::LoadStatic(array('PiwikHelper'));

class PiwikDashboardHelper extends PiwikHelper {
    /*
      - Live.getVisitorProfile (idSite, visitorId = '', segment = '') [ Example in XML, Json, Tsv (Excel) ]
      - Live.getMostRecentVisitorId (idSite, segment = '') [ Example in XML, Json, Tsv (Excel) ]
     */
    public static function getLastVisitsDetails($idSite, $period = 'day', $date = 'today', $segment = '', $countVisitorsToFetch = 10, $minTimestamp = '', $flat = '', $doNotFetchActions = '') {
        self::initialize();

        if (!self::baseTest())
            return false;

        if ((int) $idSite == 0 || $idSite == NULL)
            $idSite = (int) Configuration::get(self::CPREFIX . 'SITEID');

        if (((int) $idSite == $idSite) && ((int) $idSite > 0)) {

            $url = self::getBaseURL($idSite, NULL, NULL, 'API', NULL, NULL);
            $url .= "&method=Live.getLastVisitsDetails&format=JSON";
            if (!empty($period))
                $url .= "&period={$period}";
            if (!empty($date))
                $url .= "&date={$date}";
            if (!empty($segment))
                $url .= "&segment={$segment}";
            if ((int)$countVisitorsToFetch > 0)
                $url .= "&countVisitorsToFetch={$countVisitorsToFetch}";
            if (!empty($minTimestamp))
                $url .= "&minTimestamp={$minTimestamp}";
            if (!empty($flat))
                $url .= "&flat={$flat}";
            if (!empty($doNotFetchActions))
                $url .= "&doNotFetchActions={$doNotFetchActions}";
            
            $md5Url = md5($url);
            if (!Cache::isStored('PiwikDashboardHelper' . $md5Url)) {
                if ($result = self::getAsJsonDecoded($url))
                    Cache::store('PiwikDashboardHelper' . $md5Url, $result);
                else
                    Cache::store('PiwikDashboardHelper' . $md5Url, false);
            }
            return Cache::retrieve('PiwikDashboardHelper' . $md5Url);
        } else {
            self::$error = "PiwikDashboardHelper::getLastVisitsDetails(): site id is not valid";
            self::$errors[] = self::$error;
            return false;
        }
    }
    
    public static function getSiteSearchNoResultKeywords($idSite, $period = 'day', $date = 'today', $segment = '') {
        self::initialize();

        if (!self::baseTest())
            return false;

        if ((int) $idSite == 0 || $idSite == NULL)
            $idSite = (int) Configuration::get(self::CPREFIX . 'SITEID');

        if (((int) $idSite == $idSite) && ((int) $idSite > 0)) {
            
            $url = self::getBaseURL($idSite, NULL, NULL, 'API', NULL, NULL);
            $url .= "&method=Actions.getSiteSearchNoResultKeywords&format=JSON";
            if (!empty($period))
                $url .= "&period={$period}";
            if (!empty($date))
                $url .= "&date={$date}";
            if (!empty($segment))
                $url .= "&segment={$segment}";

            $md5Url = md5($url);
            if (!Cache::isStored('PiwikDashboardHelper' . $md5Url)) {
                if ($result = self::getAsJsonDecoded($url))
                    Cache::store('PiwikDashboardHelper' . $md5Url, $result);
                else
                    Cache::store('PiwikDashboardHelper' . $md5Url, false);
            }
            return Cache::retrieve('PiwikDashboardHelper' . $md5Url);
        } else {
            self::$error = "PiwikDashboardHelper::getVisitInformationPerLocalTime(): site id is not valid";
            self::$errors[] = self::$error;
            return false;
        }
    }

    public static function getVisitInformationPerLocalTime($idSite, $period = 'day', $date = 'today', $segment = '') {
        self::initialize();

        if (!self::baseTest())
            return false;

        if ((int) $idSite == 0 || $idSite == NULL)
            $idSite = (int) Configuration::get(self::CPREFIX . 'SITEID');

        if (((int) $idSite == $idSite) && ((int) $idSite > 0)) {

            $url = self::getBaseURL($idSite, NULL, NULL, 'API', NULL, NULL);
            $url .= "&method=VisitTime.getVisitInformationPerLocalTime&format=JSON";
            if (!empty($period))
                $url .= "&period={$period}";
            if (!empty($date))
                $url .= "&date={$date}";
            if (!empty($segment))
                $url .= "&segment={$segment}";

            $md5Url = md5($url);
            if (!Cache::isStored('PiwikDashboardHelper' . $md5Url)) {
                if ($result = self::getAsJsonDecoded($url))
                    Cache::store('PiwikDashboardHelper' . $md5Url, $result);
                else
                    Cache::store('PiwikDashboardHelper' . $md5Url, false);
            }
            return Cache::retrieve('PiwikDashboardHelper' . $md5Url);
        } else {
            self::$error = "PiwikDashboardHelper::getVisitInformationPerLocalTime(): site id is not valid";
            self::$errors[] = self::$error;
            return false;
        }
    }
    
    

    /**
     * get action counters for selected date range
     * @param type $idSite
     * @param type $period
     * @param type $date
     * @param type $segment
     * @param type $columns
     * @return boolean
     */
    public static function getActions($idSite, $period = 'day', $date = 'today', $segment = '', $columns = '') {
        self::initialize();

        if (!self::baseTest())
            return false;

        if ((int) $idSite == 0 || $idSite == NULL)
            $idSite = (int) Configuration::get(self::CPREFIX . 'SITEID');

        if (((int) $idSite == $idSite) && ((int) $idSite > 0)) {

            $url = self::getBaseURL($idSite, NULL, NULL, 'API', NULL, NULL);
            $url .= "&method=Actions.get&format=JSON";
            if (!empty($period))
                $url .= "&period={$period}";
            if (!empty($date))
                $url .= "&date={$date}";
            if (!empty($segment))
                $url .= "&segment={$segment}";
            if (!empty($columns))
                $url .= "&columns={$columns}";

            $md5Url = md5($url);
            if (!Cache::isStored('PiwikDashboardHelper' . $md5Url)) {
                if ($result = self::getAsJsonDecoded($url))
                    Cache::store('PiwikDashboardHelper' . $md5Url, $result);
                else
                    Cache::store('PiwikDashboardHelper' . $md5Url, false);
            }
            return Cache::retrieve('PiwikDashboardHelper' . $md5Url);
        } else {
            self::$error = "PiwikDashboardHelper::getActions(): site id is not valid";
            self::$errors[] = self::$error;
            return false;
        }
    }

    /**
     * get live visit counters for last x minutes
     * @param type $idSite
     * @param type $lastMinutes
     * @param type $segment
     * @param type $showColumns
     * @param type $hideColumns
     * @return boolean
     */
    public static function getLiveCounters($idSite, $lastMinutes = 5, $segment = '', $showColumns = array(), $hideColumns = array()) {
        self::initialize();

        if (!self::baseTest())
            return false;

        if ((int) $idSite == 0 || $idSite == NULL)
            $idSite = (int) Configuration::get(self::CPREFIX . 'SITEID');

        if (((int) $idSite == $idSite) && ((int) $idSite > 0)) {
            $url = self::getBaseURL($idSite, NULL, NULL, 'API', NULL, NULL);
            $url .= "&method=Live.getCounters&format=JSON";
            if ((int) $lastMinutes == $lastMinutes && ((int) $lastMinutes > 0))
                $url .= "&lastMinutes={$lastMinutes}";
            else
                $url .= "&lastMinutes=5";
            if (!empty($segment))
                $url .= "&segment={$segment}";
            if (!empty($showColumns))
                foreach ($showColumns as $value)
                    $url .= "&showColumns[]={$value}";
            if (!empty($hideColumns))
                foreach ($hideColumns as $value)
                    $url .= "&hideColumns[]={$value}";

            $md5Url = md5($url);
            if (!Cache::isStored('PiwikDashboardHelper' . $md5Url)) {
                if ($result = self::getAsJsonDecoded($url))
                    Cache::store('PiwikDashboardHelper' . $md5Url, $result);
                else
                    Cache::store('PiwikDashboardHelper' . $md5Url, false);
            }
            return Cache::retrieve('PiwikDashboardHelper' . $md5Url);
        } else {
            self::$error = "PiwikDashboardHelper::getLiveCounters(): site id is not valid";
            self::$errors[] = self::$error;
            return false;
        }
    }

}
