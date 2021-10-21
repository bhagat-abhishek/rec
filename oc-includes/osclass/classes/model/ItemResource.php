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
 * Model database for ItemResource table
 *
 * @package    Osclass
 * @subpackage Model
 * @since      unknown
 */
class ItemResource extends DAO
{
    /**
     * It references to self object: ItemResource.
     * It is used as a singleton
     *
     * @access private
     * @since  unknown
     * @var ItemResource
     */
    private static $instance;

    /**
     * Set data related to t_item_resource table
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_item_resource');
        $this->setPrimaryKey('pk_i_id');
        $this->setFields(array('pk_i_id', 'fk_i_item_id', 's_name', 's_extension', 's_content_type', 's_path'));
    }

    /**
     * It creates a new ItemResource object class ir if it has been created
     * before, it return the previous object
     *
     * @access public
     * @return ItemResource
     * @since  unknown
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get all resources
     *
     * @access public
     * @return array of resources
     * @since  unknown
     */
    public function getAllResources()
    {
        $this->dao->select('r.*, c.dt_pub_date');
        $this->dao->from($this->getTableName() . ' r');
        $this->dao->join($this->getTableItemName() . ' c', 'c.pk_i_id = r.fk_i_item_id');

        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }

    /**
     * Return table item name
     *
     * @access public
     * @return string table name
     * @since  unknown
     */
    public function getTableItemName()
    {
        return $this->getTablePrefix() . 't_item';
    }

    /**
     * Get all resources belong to an item given its id
     *
     * @access public
     *
     * @param int $itemId Item id
     *
     * @return array of resources
     *
     * @since  2.3.7
     *
     */
    public function getAllResourcesFromItem($itemId)
    {
        $key   = md5(osc_base_url() . 'ItemResource:getAllResourcesFromItem:' . $itemId);
        $found = null;
        $cache = osc_cache_get($key, $found);
        if ($cache === false) {
            $this->dao->select();
            $this->dao->from($this->getTableName());
            $this->dao->where('fk_i_item_id', (int)$itemId);

            $result = $this->dao->get();

            if ($result == false) {
                return array();
            }

            $return = $result->result();
            osc_cache_set($key, $return, OSC_CACHE_TTL);

            return $return;
        }

        return $cache;
    }

    /**
     * Get first resource belong to an item given it id
     *
     * @access public
     *
     * @param int $itemId Item id
     *
     * @return array resource
     * @since  unknown
     */
    public function getResource($itemId)
    {
        $this->dao->select($this->getFields());
        $this->dao->from($this->getTableName());
        $this->dao->where('fk_i_item_id', $itemId);
        $this->dao->limit(1);

        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        if ($result->numRows == 0) {
            return array();
        }

        return $result->row();
    }

    /**
     * Check if resource id and name exist
     *
     * @param int    $resourceId
     * @param string $code
     *
     * @return bool
     * @deprecated since 2.3
     */
    public function getResourceSecure($resourceId, $code)
    {
        return $this->existResource($resourceId, $code);
    }

    /**
     * Check if resource id and name exist
     *
     * @access public
     *
     * @param int    $resourceId
     * @param string $code
     *
     * @return bool
     * @since  unknown
     */
    public function existResource($resourceId, $code)
    {
        $this->dao->select('COUNT(*) AS numrows');
        $this->dao->from($this->getTableName());
        $this->dao->where('pk_i_id', $resourceId);
        $this->dao->where('s_name', $code);

        $result = $this->dao->get();

        if ($result == false) {
            return 0;
        }

        if ($result->numRows() != 1) {
            return 0;
        }

        $row = $result->row();

        return $row['numrows'];
    }

    /**
     * Count resouces belong to item given its id
     *
     * @access public
     *
     * @param int $itemId Item id
     *
     * @return int
     * @since  unknown
     */
    public function countResources($itemId = null)
    {
        $this->dao->select('COUNT(*) AS numrows');
        $this->dao->from($this->getTableName());
        if (null !== $itemId && is_numeric($itemId)) {
            $this->dao->where('fk_i_item_id', $itemId);
        }

        $result = $this->dao->get();

        if ($result == false) {
            return 0;
        }

        if ($result->numRows() != 1) {
            return 0;
        }

        $row = $result->row();

        return $row['numrows'];
    }

    /**
     * Get resources, if $itemId is set return resources belong to an item given its id,
     * can be filtered by $start/$end and ordered by column.
     *
     * @access public
     *
     * @param int    $itemId Item id
     * @param int    $start  beginig
     * @param int    $length ending
     * @param string $order  column order default='pk_i_id'
     * @param string $type   order type [DESC|ASC]
     *
     * @return array of resources
     * @since  unknown
     */
    public function getResources($itemId = null, $start = 0, $length = 10, $order = 'r.pk_i_id', $type = 'DESC')
    {
        if (!in_array($order, array(
            0 => 'r.pk_i_id',
            1 => 'r.pk_i_id',
            2 => 'r.pk_i_id',
            3 => 'r.fk_i_item_id',
            4 => 'c.dt_pub_date'
        ))
        ) {
            // order by is incorrect
            return array();
        }

        if (!in_array(strtoupper($type), array('DESC', 'ASC'))) {
            // order type is incorrect
            return array();
        }

        $this->dao->select('r.*, c.dt_pub_date');
        $this->dao->from($this->getTableName() . ' r');
        $this->dao->join($this->getTableItemName() . ' c', 'c.pk_i_id = r.fk_i_item_id');
        if (null !== $itemId && is_numeric($itemId)) {
            $this->dao->where('r.fk_i_item_id', $itemId);
        }
        $this->dao->orderBy($order, $type);
        $this->dao->limit($start);
        $this->dao->offset($length);
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }

    /**
     * Delete all resources where id is in $ids
     *
     * @param array $ids
     *
     * @return bool|int
     */
    public function deleteResourcesIds($ids)
    {
        $this->dao->whereIn('pk_i_id', $ids);

        return $this->dao->delete($this->getTableName());
    }

    /**
     * Return table description name
     *
     * @access public
     * @return string table description name
     * @since  unknown
     */
    public function getTableItemDescription()
    {
        return $this->getTablePrefix() . 't_item_description';
    }
}

/* file end: ./oc-includes/osclass/model/ItemResource.php */
