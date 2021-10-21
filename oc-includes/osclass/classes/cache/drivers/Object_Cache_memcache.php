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
 * Object_Cache_memcache class
 */
class Object_Cache_memcache implements iObject_Cache
{

    /**
     * The amount of times the cache data was already stored in the cache.
     *
     * @since  3.4
     * @access private
     * @var int
     */
    public $cache_hits = 0;
    /**
     * Amount of times the cache did not have the request in cache
     *
     * @var int
     * @access public
     * @since  3.4
     */
    public $cache_misses = 0;
    /**
     * The blog prefix to prepend to keys in non-global groups.
     *
     * @var int
     * @access private
     * @since  3.4
     */
    public $site_prefix;
    public $default_expiration = 60;
    protected $_memcache_conf = array(
        'default' => array(
            'default_host'   => '127.0.0.1',
            'default_port'   => 11211,
            'default_weight' => 1
        )
    );
    /**
     * Holds the memcached object
     *
     * @var array
     * @access private
     * @since  3.4
     */
    private $memcached;
    private $cache;

    /**
     * Sets up object properties; PHP 5 style constructor
     *
     * @since 3.4
     */
    public function __construct()
    {
        $this->site_prefix = '';
        $cache_server      = array();
        global $_cache_config;
        if (!isset($_cache_config) && !is_array($_cache_config)) {
            $_t['hostname'] = $this->_memcache_conf['default']['default_host'];
            $_t['port']     = $this->_memcache_conf['default']['default_port'];
            $_t['weight']   = $this->_memcache_conf['default']['default_weight'];
            $cache_server[] = $_t;
        } else {
            foreach ($_cache_config as $_server) {
                $_array         = array(
                    'hostname' => $_server['default_host'],
                    'port'     => $_server['default_port'],
                    'weight'   => $_server['default_weight']
                );
                $cache_server[] = $_array;
            }
        }

        $this->memcached = new Memcache();
        foreach ($cache_server as $_config) {
            $this->memcached->addServer($_config['hostname'], $_config['port'], $_config['weight']);
        }
    }

    /**
     * Adds data to the cache if it doesn't already exist.
     *
     * @param int|string $key    What to call the contents in the cache
     * @param mixed      $data   The contents to store in the cache
     * @param int        $expire When to expire the cache contents
     *
     * @return bool False if cache key and group already exist, true on success
     * @since 3.4
     *
     */
    public function add($key, $data, $expire = 0)
    {
        $id = $key;

        if (is_object($data)) {
            $data = clone $data;
        }

        $store_data = $data;

        if (is_array($data)) {
            $store_data = new ArrayObject($data);
        }

        $expire = ($expire == 0) ? $this->default_expiration : $expire;
        $result = $this->memcached->add($key, array($store_data, time(), $expire), 0, $expire);
        if (false !== $result) {
            $this->cache[$key] = $data;
        }

        return $result;
    }

    /**
     * Remove the contents of the cache key in the group
     *
     * @param int|string $key What the contents in the cache are called
     *
     * @return bool False if the contents weren't deleted and true on success
     * @since 3.4
     *
     */
    public function delete($key)
    {
        $result = $this->memcached->delete($key);
        if (false !== $result) {
            unset($this->cache[$key]);
        }

        return $result;
    }

    /**
     * Clears the object cache of all data
     *
     * @return bool Always returns true
     * @since 3.4
     *
     */
    public function flush()
    {
        $this->cache = array();

        return $this->memcached->flush();
    }

    /**
     * Retrieves the cache contents, if it exists
     *
     * @param int|string $key   What the contents in the cache are called
     * @param bool       $found if can be retrieved from cache
     *
     * @return bool|mixed False on failure to retrieve contents or the cache
     *      contents on success
     * @since 3.4
     *
     */
    public function get($key, &$found = null)
    {
        $found = false;
        if (isset($this->cache[$key])) {
            $found = true;
            if (is_object($this->cache[$key])) {
                $value = clone $this->cache[$key];
            } else {
                $value = $this->cache[$key];
            }
            ++$this->cache_hits;
            $return = $value;
        } else {
            $found = true;
            $value = $this->memcached->get($key);
            if (is_object($value) && 'ArrayObject' === get_class($value)) {
                $value = $value->getArrayCopy();
            }
            if (null === $value) {
                $found = false;
                $value = false;
            }

            $this->cache[$key] = is_object($value) ? clone $value : $value;
            if ($found) {
                ++$this->cache_hits;
                $return = $this->cache[$key];
            } else {
                ++$this->cache_misses;
                $return = false;
            }
        }

        return $return;
    }

    /**
     * Sets the data contents into the cache
     *
     * @param int|string $key    What to call the contents in the cache
     * @param mixed      $data   The contents to store in the cache
     * @param int        $expire Not Used
     *
     * @return bool Always returns true on success, false on failure
     * @since 3.4
     *
     */
    public function set($key, $data, $expire = 0)
    {
        if (is_object($data)) {
            $data = clone $data;
        }

        $store_data = $data;

        if (is_array($data)) {
            $store_data = new ArrayObject($data);
        }

        $this->cache[$key] = $data;

        $expire = ($expire == 0) ? $this->default_expiration : $expire;

        return $this->memcached->set($key, $store_data, 0, $expire);
    }

    /**
     * Echoes the stats of the caching.
     * Gives the cache hits, and cache misses.
     *
     * @since 3.4
     */
    public function stats()
    {
        echo "<div style='position:absolute;width:200px;top:0px;'>
<div style='float:right;
 margin-right:30px;margin-top:15px;border: 1px red solid;
border-radius: 17px;
padding: 1em;'><h2>Memcache stats</h2>";
        echo '<p>';
        echo "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
        echo "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
        echo '</p>';
        echo '<ul>';
        echo '</ul></div></div>';
    }

    /**
     * is_supported()
     *
     * Check to see if Memcache is available on this system, bail if it isn't.
     */
    public static function is_supported()
    {
        if (!class_exists('Memcache')) {
            error_log('The Memcached Extension must be loaded to use Memcached Cache.');

            return false;
        }

        return true;
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    /**
     * @return string
     */
    public function _get_cache()
    {
        return 'memcache';
    }

    /**
     * Utility function to determine whether a key exists in the cache.
     *
     * @param $key
     *
     * @return bool
     * @since  3.4.0
     *
     * @access protected
     *
     */
    protected function _exists($key)
    {
        return isset($this->cache[$key]);
    }
}
