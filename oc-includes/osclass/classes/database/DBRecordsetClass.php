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
 * Database recordset object
 *
 * @package    Osclass
 * @subpackage Database
 * @since      2.3
 */
class DBRecordsetClass
{
    /**
     * Database connection object to Osclass database
     *
     * @access public
     * @since  2.3
     * @var mysqli
     */
    public $connId;
    /**
     * Database result object
     *
     * @access public
     * @since  2.3
     * @var MySQLi_Result
     */
    public $resultId;
    /**
     * Result array
     *
     * @access private
     * @since  2.3
     * @var array
     */
    public $resultArray;
    /**
     * Result object
     *
     * @access private
     * @since  2.3
     * @var object
     */
    public $resultObject;
    /**
     * Number of rows
     *
     * @access public
     * @since  2.3
     * @var int
     */
    public $numRows;
    /**
     * Current row
     *
     * @access private
     * @since  2.3
     * @var int
     */
    protected $currentRow;

    /**
     * Initialize Recordset Class
     *
     * @param mysqli        $connId
     * @param MySQLi_Result $resultId
     */
    public function __construct($connId = null, $resultId = null)
    {
        $this->connId       = $connId;
        $this->resultId     = $resultId;
        $this->resultArray  = array();
        $this->resultObject = array();
        $this->currentRow   = 0;
        $this->numRows      = 0;
    }

    /**
     * Get a result row as an array or object
     *
     * @param int    $n
     * @param string $type
     *
     * @return array|object
     */
    public function row($n = 0, $type = 'array')
    {
        if (!is_numeric($n)) {
            $n = 0;
        }

        if ($type === 'array') {
            return $this->rowArray($n);
        }

        return $this->rowObject($n);
    }

    /**
     * Get a result row as an array
     *
     * @access public
     *
     * @param int $n
     *
     * @return array
     * @since  2.3
     */
    public function rowArray($n = 0)
    {
        $result = $this->resultArray();

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->currentRow && isset($result[$n])) {
            $this->currentRow = $n;
        }

        return $result[$this->currentRow];
    }

    /**
     * Get the results of MySQLi_Result object in array format
     *
     * @access public
     * @return array
     * @since  2.3
     */
    public function resultArray()
    {
        if (count($this->resultArray) > 0) {
            return $this->resultArray;
        }

        $this->_dataSeek();
        while ($row = $this->_fetchArray()) {
            $this->resultArray[] = $row;
        }

        return $this->resultArray;
    }

    /**
     * Adjust resultId pointer to the selected row
     *
     * @access private
     *
     * @param int $offset Must be between zero and the total number of rows minus one
     *
     * @return bool true on success or false on failure
     * @since  2.3
     */
    public function _dataSeek($offset = 0)
    {
        return $this->resultId->data_seek($offset);
    }

    /**
     * Returns the current row of a result set as an array
     *
     * @access private
     * @return array
     * @since  2.3
     */
    public function _fetchArray()
    {
        return $this->resultId->fetch_assoc();
    }

    /**
     * Get a result row as an object
     *
     * @access public
     *
     * @param int $n
     *
     * @return object
     * @since  2.3
     */
    public function rowObject($n = 0)
    {
        $result = $this->resultObject();

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->currentRow && isset($result[$n])) {
            $this->currentRow = $n;
        }

        return $result[$this->currentRow];
    }

    /**
     * Get the results of MySQLi_Result object in object format
     *
     * @access public
     * @return array|object
     * @since  2.3
     */
    public function resultObject()
    {
        if (count($this->resultObject) > 0) {
            return $this->resultObject;
        }

        $this->_dataSeek();
        while ($row = $this->_fetchObject()) {
            $this->resultObject[] = $row;
        }

        return $this->resultObject;
    }

    /**
     * Returns the current row of a result set as an object
     *
     * @access private
     * @return object
     * @since  2.3
     */
    public function _fetchObject()
    {
        return $this->resultId->fetch_object();
    }

    /**
     * Get the first row as an array or object
     *
     * @access public
     *
     * @param string $type
     *
     * @return mixed
     * @since  2.3
     */
    public function firstRow($type = 'array')
    {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        return $result[0];
    }

    /**
     * Get the results of MySQLi_Result object
     *
     * @access public
     *
     * @param string $type
     *
     * @return array | object It can be an array or an object
     * @since  2.3
     */
    public function result($type = 'array')
    {
        if ($type === 'array') {
            return $this->resultArray();
        }

        return $this->resultObject();
    }

    /**
     * Get the last row as an array or object
     *
     * @access public
     *
     * @param string $type
     *
     * @return mixed
     * @since  2.3
     */
    public function lastRow($type = 'array')
    {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        return $result[count($result) - 1];
    }

    /**
     * Get next row as an array or object
     *
     * @access public
     *
     * @param string $type
     *
     * @return mixed
     * @since  2.3
     */
    public function nextRow($type = 'array')
    {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        if (isset($result[$this->currentRow + 1])) {
            $this->currentRow++;
        }

        return $result[$this->currentRow];
    }

    /**
     * Get previous row as an array or object
     *
     * @access public
     *
     * @param string $type
     *
     * @return mixed
     * @since  2.3
     */
    public function previousRow($type = 'array')
    {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        if (isset($result[$this->currentRow - 1])) {
            $this->currentRow--;
        }

        return $result[$this->currentRow];
    }

    /**
     * Get number of rows
     *
     * @access public
     * @return int
     * @since  2.3
     */
    public function numRows()
    {
        return $this->resultId->num_rows;
    }

    /**
     * Get the number of fields in a result
     *
     * @access public
     * @return int
     * @since  2.3
     */
    public function numFields()
    {
        return $this->resultId->field_count;
    }

    /**
     * Get the name of the fields in an array
     *
     * @access public
     * @return array
     * @since  2.3
     */
    public function listFields()
    {
        $fieldNames = array();
        while ($field = $this->resultId->fetch_field()) {
            $fieldNames[] = $field->name;
        }

        return $fieldNames;
    }
}

/* file end: ./oc-includes/osclass/classes/database/DBRecordsetClass.php */
