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
 * BanRule DAO
 */
class BanRule extends DAO
{
    /**
     *
     * @var \BanRule
     */
    private static $instance;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_ban_rule');
        $this->setPrimaryKey('pk_i_id');
        $array_fields = array(
            'pk_i_id',
            's_name',
            's_ip',
            's_email'
        );
        $this->setFields($array_fields);
    }

    /**
     * @return \BanRule
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Return list of ban rules
     *
     * @access public
     *
     * @param int    $start
     * @param int    $end
     * @param string $order_column
     * @param string $order_direction
     * @param string $name
     *
     * @return array
     * @parma  string $name
     * @since  3.1
     *
     */
    public function search($start = 0, $end = 10, $order_column = 'pk_i_id', $order_direction = 'DESC', $name = '')
    {
        // SET data, so we always return a valid object
        $rules                  = array();
        $rules['rows']          = 0;
        $rules['total_results'] = 0;
        $rules['rules']         = array();

        $this->dao->select('SQL_CALC_FOUND_ROWS *');
        $this->dao->from($this->getTableName());
        $this->dao->orderBy($order_column, $order_direction);
        $this->dao->limit($start, $end);
        if ($name != '') {
            $this->dao->like('s_name', $name);
        }
        $rs = $this->dao->get();

        if ($rs == false) {
            return $rules;
        }

        $rules['rules'] = $rs->result();

        $rsRows = $this->dao->query('SELECT FOUND_ROWS() as total');
        $data   = $rsRows->row();
        if ($data['total']) {
            $rules['total_results'] = $data['total'];
        }

        $rsTotal = $this->dao->query('SELECT COUNT(*) as total FROM ' . $this->getTableName());
        $data    = $rsTotal->row();
        if ($data['total']) {
            $rules['rows'] = $data['total'];
        }

        return $rules;
    }

    /**
     * Return number of ban rules
     *
     * @return int
     * @since 3.1
     */
    public function countRules()
    {
        $this->dao->select('COUNT(*) as i_total');
        $this->dao->from($this->getTableName());

        $result = $this->dao->get();

        if ($result == false || $result->numRows() == 0) {
            return 0;
        }

        $row = $result->row();

        return $row['i_total'];
    }
}
