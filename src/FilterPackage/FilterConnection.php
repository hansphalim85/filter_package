<?php


namespace FilterPackage;

chdir(__DIR__);
require ('../../vendor/autoload.php');

use Aura\SqlQuery\QueryFactory;
use Aura\Sql\ExtendedPdo;
use mysql_xdevapi\Exception;


class FilterConnection
{

    protected $_pdo;
    private $_queryFactory;

    /*public function __construct()
    {
    }

    public function __destruct()
    {
        $this->_pdo->disconnect();
    }*/

    protected function _buildConnection($db, $host, $dbName, $username, $password)
    {
        try {
            $this->_pdo = new ExtendedPdo(
                'mysql:host='.$host.';dbname='.$dbName,
                $username,
                $password,
                [], // driver attributes/options as key-value pairs
                []  // queries to execute after connection
            );

            $this->_queryFactory = new QueryFactory($db);

        } catch (Exception $e) {
            //return $e->getMessage();
        }
    }

    protected function _getNewSelect()
    {
        return $this->_queryFactory->newSelect();
    }

    protected function _selectAll($select)
    {
        // prepare the statment
        $sth = $this->_pdo->prepare($select->getStatement());

        // bind the values and execute
        $sth->execute($select->getBindValues());

        // get the results back as an associative array
        $result = $sth->fetchAll();


        return $result;
    }

    protected function _selectRaw($query, $parameters = array())
    {
        $sth = $this->_pdo->prepare($query);
        if (!empty($parameters)) {
            $sth->execute($parameters);
        } else {
            $sth->execute();
        }
        $result = $sth->fetchAll();

        return $result;
    }

}