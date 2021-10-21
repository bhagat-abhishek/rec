<?php
/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Class Params
 */
class Params
{
    private static $HTMLPurifier;
    private static $request;
    private static $server;

    public static function init()
    {
        self::$request = array_merge($_GET, $_POST);
        self::$server  = $_SERVER;
    }

    /**
     * Return HTMLPurified param
     *
     * @param      $param
     * @param bool $html_encode
     * @param bool $xss_check
     * @param bool $quotes_encode
     *
     * @return mixed
     */
    public static function getParam($param, $html_encode = false, $xss_check = true, $quotes_encode = true)
    {
        if ($param === '') {
            return '';
        }
        if (!isset(self::$request[$param])) {
            return '';
        }

        return self::purify(self::$request[$param], $html_encode, $xss_check, $quotes_encode);
    }

    /**
     * Function to purify given string or array
     * Should be moved to separate class
     *
     * @param      $value
     *
     * @param bool $html_encode
     * @param bool $xss_check
     * @param bool $quotes_encode
     *
     * @return string
     */
    private static function purify($value, $html_encode = false, $xss_check = true, $quotes_encode = true)
    {
        if ($html_encode === false && $xss_check === false && $quotes_encode === false) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => &$v) {
                $v = self::purify($v, $html_encode, $xss_check, $quotes_encode); // recursive
            }
        } else {
            if ($xss_check === true) {
                if (self::$HTMLPurifier === null) {
                    $purifier_config = HTMLPurifier_Config::createDefault();
                    $purifier_config->set('HTML.Allowed', '');
                    $purifier_config->set('Cache.SerializerPath', osc_uploads_path());
                    self::$HTMLPurifier = new HTMLPurifier($purifier_config);
                }

                $value = self::$HTMLPurifier->purify($value);
            }

            if ($html_encode === true) {
                if ($quotes_encode === true) {
                    return htmlspecialchars(stripslashes($value), ENT_QUOTES);
                }

                return htmlspecialchars(stripslashes($value), ENT_NOQUOTES);
            }
        }

        return $value;
    }

    /**
     * @param $param
     *
     * @return bool
     */
    public static function existParam($param)
    {
        if ($param === '') {
            return false;
        }
        if (!isset(self::$request[$param])) {
            return false;
        }

        return true;
    }

    /**
     * Return REQUEST_URI from $_SERVER params
     *
     * @param bool $html_encode
     * @param bool $xss_check
     * @param bool $quotes_encode
     *
     * @return string|string[]|null
     */
    public static function getRequestURI($html_encode = false, $xss_check = true, $quotes_encode = true)
    {
        if (self::existServerParam('REQUEST_URI')) {
            $raw_request_uri = self::getServerParam('REQUEST_URI', $html_encode, $xss_check, $quotes_encode);

            //make this to osclass installation specific
            return preg_replace('|^' . REL_WEB_URL . '|', '', $raw_request_uri);
        }

        return '';
    }

    /**
     * @param $param
     *
     * @return bool
     */
    public static function existServerParam($param)
    {
        if ($param === '') {
            return false;
        }
        if (!isset(self::$server[$param])) {
            return false;
        }

        return true;
    }

    /**
     * @param      $param
     * @param bool $html_encode
     * @param bool $xss_check
     * @param bool $quotes_encode
     *
     * @return string
     */
    public static function getServerParam($param, $html_encode = false, $xss_check = true, $quotes_encode = true)
    {
        if ($param === '') {
            return '';
        }
        if (!isset(self::$server[$param])) {
            return '';
        }

        $value = self::$server[$param];

        $value = self::purify($value, $html_encode, $xss_check, $quotes_encode);


        return $value;
    }

    /**
     * @param bool $xss_check
     *
     * @return string
     */
    public static function getServerParamsAsArray($xss_check = true)
    {
        $value = self::$server;

        $value = self::purify($value, false, $xss_check, false);

        return $value;
    }

    /**
     * @param $param
     *
     * @return array
     */
    public static function getFiles($param)
    {
        if (isset($_FILES[$param])) {
            return $_FILES[$param];
        }

        return array();
    }

    /**
     * @param $key
     * @param $value
     */
    public static function setParam($key, $value)
    {
        self::$request[$key] = $value;
    }

    /**
     * @param $key
     */
    public static function unsetParam($key)
    {
        unset(self::$request[$key]);
    }

    /**
     * Will be removed do not use this
     *
     * @deprecated 4.0
     * return void
     */
    public static function _view()
    {
        print_r(self::getParamsAsArray());
    }

    /**
     * @param string $what
     * @param bool   $xss_check
     *
     * @return array|string
     */
    public static function getParamsAsArray($what = '', $xss_check = true)
    {
        switch ($what) {
            case ('get'):
                $value = $_GET;
                break;
            case ('post'):
                $value = $_POST;
                break;
            case ('cookie'):
                return $_COOKIE;
                break;
            case ('files'):
                return $_FILES;
                break;
            case ('request'): // This should not be called, as it depends on server's configuration
                return $_REQUEST;
                break;
            default:
                $value = self::$request;
                break;
        }

        $value = self::purify($value, false, $xss_check, false); // $xss_check, $quotes_encode );

        return $value;
    }
}
