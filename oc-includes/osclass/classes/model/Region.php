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
 * Model database for Region table
 *
 * @package    Osclass
 * @subpackage Model
 * @since      unknown
 */
class Region extends DAO
{
    /**
     *
     * @var \Region
     */
    private static $instance;

    /**
     * Region constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_region');
        $this->setPrimaryKey('pk_i_id');
        $this->setFields(array('pk_i_id', 'fk_c_country_code', 's_name', 'b_active', 's_slug'));
    }

    /**
     * @return \Region
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Gets all regions from a country
     *
     * @access     public
     *
     * @param $countryId
     *
     * @return array
     * @see        Region::findByCountry
     * @since      unknown
     * @deprecated since 2.3
     */
    public function getByCountry($countryId)
    {
        return $this->findByCountry($countryId);
    }

    /**
     * Gets all regions from a country
     *
     * @access public
     *
     * @param $countryId
     *
     * @return array
     * @since  unknown
     */
    public function findByCountry($countryId)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('fk_c_country_code', $countryId);
        $this->dao->orderBy('s_name', 'ASC');
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }

    /**
     * Find a region by its name and country
     *
     * @access public
     *
     * @param string $name
     * @param string $country
     *
     * @return array
     * @since  unknown
     */
    public function findByName($name, $country = null)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_name', $name);
        if ($country != null) {
            $this->dao->where('fk_c_country_code', $country);
        }
        $this->dao->limit(1);
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->row();
    }

    /**
     * Function to deal with ajax queries
     *
     * @access public
     *
     * @param      $query
     * @param null $country
     *
     * @return array
     * @since  unknown
     *
     */
    public function ajax($query, $country = null)
    {
        $country = trim($country);
        $this->dao->select('a.pk_i_id as id, a.s_name as label, a.s_name as value');
        $this->dao->from($this->getTableName() . ' as a');
        $this->dao->like('a.s_name', $query, 'after');
        if ($country != null) {
            if (strlen($country) == 2) {
                $this->dao->where('a.fk_c_country_code', strtolower($country));
            } else {
                $this->dao->join(
                    Country::newInstance()->getTableName() . ' as aux',
                    'aux.pk_c_code = a.fk_c_country_code',
                    'LEFT'
                );
                $this->dao->where('aux.s_name', $country);
            }
        }
        $this->dao->limit(5);
        $result = $this->dao->get();
        if ($result == false) {
            return array();
        }

        return $result->result();
    }


    /**
     *  Delete a region with its cities and city areas
     *
     * @access public
     *
     * @param $pk
     *
     * @return int number of failed deletions or 0 in case of none
     * @since  3.1
     *
     */
    public function deleteByPrimaryKey($pk)
    {
        $mCities = City::newInstance();
        $aCities = $mCities->findByRegion($pk);
        $result  = 0;
        foreach ($aCities as $city) {
            $result += $mCities->deleteByPrimaryKey($city['pk_i_id']);
        }
        Item::newInstance()->deleteByRegion($pk);
        RegionStats::newInstance()->delete(array('fk_i_region_id' => $pk));
        User::newInstance()->update(array('fk_i_region_id' => null, 's_region' => ''), array('fk_i_region_id' => $pk));
        if (!$this->delete(array('pk_i_id' => $pk))) {
            $result++;
        }

        return $result;
    }

    /**
     * Find a location by its slug
     *
     * @access public
     *
     * @param $slug
     *
     * @return array
     * @since  3.2.1
     */
    public function findBySlug($slug)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_slug', $slug);
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->row();
    }

    /**
     * Find a locations with no slug
     *
     * @access public
     * @return array
     * @since  3.2.1
     */
    public function listByEmptySlug()
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_slug', '');
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }
}

/* file end: ./oc-includes/osclass/model/Region.php */
