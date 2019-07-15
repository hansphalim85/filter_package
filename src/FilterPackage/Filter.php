<?php


namespace FilterPackage;

use FilterPackage\FilterConnection;
use Exception;

class Filter extends FilterConnection
{
    private $_dbConn;
    private $_category;
    private $_subcategory;
    private $_manufacturer;
    private $_productList;

    private $_lastQuery;
    private $_lastParams;


    public function __construct($db, $host, $dbName, $username, $password)
    {
        $exception = new Exception();

        $this->_category = array();
        $this->_subcategory = array();
        $this->_manufacturer = array();

        $this->_buildConnection(
            $db,
            $host,
            $dbName,
            $username,
            $password
        );
    }

    public function buildCategory($tableName, $catIDField, $catNameField)
    {
        $this->_category['tableName'] = $tableName;
        $this->_category['catIDField'] = $catIDField;
        $this->_category['catNameField'] = $catNameField;
    }

    public function getAllCategory($orderBy = array())
    {
        if (empty($this->_category)) {
            // category need to build before use
            return false;
        }

        $select = $this->_getNewSelect();

        $select->cols([
            $this->_category['catIDField'],
            $this->_category['catNameField']
        ]);

        $select->from($this->_category['tableName']);

        if (!empty($orderBy)) {
            $select->orderBy($orderBy);
        }

        $result = $this->_selectAll($select);
        return $result;
    }

    public function buildSubCategory($tableName, $subCatIDField, $subCatNameField, $catIDField)
    {
        $this->_subcategory['tableName'] = $tableName;
        $this->_subcategory['subCatIDField'] = $subCatIDField;
        $this->_subcategory['subCatNameField'] = $subCatNameField;
        $this->_subcategory['catIDField'] = $catIDField;
    }

    public function getAllSubCategory($orderBy = array())
    {
        if (empty($this->_category) || empty($this->_subcategory)) {
            // category && need to build before use
            return false;
        }

        $select = $this->_getNewSelect();

        $select->cols([
            $this->_subcategory['subCatIDField'],
            $this->_category['catNameField'],
            $this->_subcategory['subCatNameField']
        ]);

        $select->from($this->_subcategory['tableName']);

        $select->join(
            'LEFT',
            $this->_category['tableName'],
            $this->_category['catIDField'] . ' = ' . $this->_subcategory['catIDField']
        );

        if (!empty($orderBy)) {
            $select->orderBy($orderBy);
        }

        $result = $this->_selectAll($select);
        return $result;
    }

    public function buildManufacturer($tableName, $manIDField, $manNameField)
    {
        $this->_manufacturer['tableName'] = $tableName;
        $this->_manufacturer['manIDField'] = $manIDField;
        $this->_manufacturer['manNameField'] = $manNameField;
    }

    public function getAllManufacturer($orderBy = array())
    {
        if (empty($this->_manufacturer)) {
            // manufacturer need to build before use
            return false;
        }

        $select = $this->_getNewSelect();

        $select->cols([
            $this->_manufacturer['manIDField'],
            $this->_manufacturer['manNameField']
        ]);

        $select->from($this->_manufacturer['tableName']);

        if (!empty($orderBy)) {
            $select->orderBy($orderBy);
        }

        $result = $this->_selectAll($select);
        return $result;
    }

    public function getFilterFromQuery($query, $parameters = array())
    {
        $this->_lastQuery = $query;
        $this->_lastParams = $parameters;

        $result = array();

        $result['cat_subcat'] = $this->_buildFilterArray(
            array('catid', 'catname', 'subid', 'subname'),
            array('catname', 'subname')
        );

        $result['man'] = $this->_buildFilterArray(
            array('manid', 'manname'),
            array('manname')
        );

        return $result;
    }

    private function _buildFilterArray($groupBy = array(), $orderBy = array())
    {
        if (empty($groupBy)) {
            return false;
        }

        $selects = array();

        $i = 0;
        foreach ($groupBy as $group) {
            $selects['select_' . $i] = $group;
            $i++;
        }

        $lastGroup = count($groupBy) - 1;

        $query = "SELECT ";
        foreach ($selects as $key => $group) {
            $query .= $group . " as ".$key.", ";
        }
        $query .= "COUNT(" . $groupBy[$lastGroup] . ") as `group_count`";
        $query .= "FROM (" . $this->_lastQuery . ") as product_list ";
        $query .= "GROUP BY " . implode(', ', $groupBy);

        if (!empty($orderBy)) {
            $query .= " ORDER BY " . implode(', ', $orderBy);
        }

        $rows = $this->_selectRaw($query, $this->_lastParams);

        $filterList = array();

        if (count($groupBy) == 2) {
            // only category
            foreach ($rows as $row) {
                $filterList[$row['select_0']]['catname'] = $row['select_1'];
                $filterList[$row['select_0']]['catcount'] = $row['group_count'];
            }
        } else {
            // category and subcategory
            $lastCatID = 0;
            $countCatID = 0;
            foreach ($rows as $row) {
                $filterList[$row['select_0']]['catname'] = $row['select_1'];
                $filterList[$row['select_0']][$row['select_2']]['subname'] = $row['select_3'];
                $filterList[$row['select_0']][$row['select_2']]['subcount'] = $row['group_count'];

                if ($lastCatID == 0) {
                    $lastCatID = $row['select_0'];
                    $countCatID = $row['group_count'];
                } else {
                    if ($lastCatID == $row['select_0']) {
                        $countCatID += $row['group_count'];
                    } else {
                        $filterList[$lastCatID]['catcount'] = $countCatID;

                        $lastCatID = $row['select_0'];
                        $countCatID = $row['group_count'];
                    }
                }
            }

            $filterList[$lastCatID]['catcount'] = $countCatID;
        }

        return $filterList;
    }
}