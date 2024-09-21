<?php

include_once('/var/www/surfcash-game/architeture/engine/tab/stark/vendor/autoload.php');

class DataSQLControllerLeads
{

    private $connection = null;
    private $database = 'leads';

    public function __construct()
    {
        $this->database = 'leads';
        $this->connection = new mysqli('data-module.mysql.database.azure.com', 'sigma', 'AdlerLopes1', $this->database);
        if ($this->connection->connect_error) {
            die('Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);
        }
    }

    public function insert($data, $table)
    {
        $query = "INSERT INTO $table (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $query .= "$key,";
            $values .= "'$value',";
        }
        $query = substr($query, 0, -1) . ") ";
        $values = substr($values, 0, -1) . ")";
        $query .= $values;

        if ($this->connection->query($query) == true) {
            return true;
        } else {
            return false;
        }
    }

    public function findOne($query, $table)
    {
        $query = "SELECT * FROM $table WHERE $query";
        $result = $this->connection->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function rows($query, $table)
    {
        $query = "SELECT * FROM $table WHERE $query";
        $result = $this->connection->query($query);
        if ($result->num_rows > 0) {
            return $result->num_rows;
        }
        return false;
    }

    public function find($query, $table)
    {
        $query = "SELECT * FROM $table WHERE $query";
        $result = $this->connection->query($query);
        if ($result->num_rows > 0) {
            $rows = array();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        }
        return false;
    }

    public function update($query, $table, $where)
    {
        $query = "UPDATE $table SET $query WHERE $where";
        if ($this->connection->query($query) == true) {
            return true;
        } else {
            return false;
        }
    }
}

class DataSQLControllerSearch
{

    private $connection = null;
    private $database = 'surfcash';

    public function __construct()
    {
        $this->database = 'surfcash';
        $this->connection = new mysqli('data-module.mysql.database.azure.com', 'sigma', 'AdlerLopes@1', $this->database);
        if ($this->connection->connect_error) {
            die('Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);
        }
    }

    public function insert($data, $table)
    {
        $query = "INSERT INTO $table (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $query .= "$key,";
            $values .= "'$value',";
        }
        $query = substr($query, 0, -1) . ") ";
        $values = substr($values, 0, -1) . ")";
        $query .= $values;

        if ($this->connection->query($query) == true) {
            return true;
        } else {
            return false;
        }
    }

    public function findOne($query, $table)
    {
        $query = "SELECT * FROM $table WHERE $query";
        $result = $this->connection->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function rows($query, $table)
    {
        $query = "SELECT * FROM $table WHERE $query";
        $result = $this->connection->query($query);
        if ($result->num_rows > 0) {
            return $result->num_rows;
        }
        return false;
    }

    public function find($query, $table)
    {
        $query = "SELECT * FROM $table WHERE $query";
        $result = $this->connection->query($query);
        if ($result->num_rows > 0) {
            $rows = array();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        }
        return false;
    }

    public function update($query, $table, $where)
    {
        $query = "UPDATE $table SET $query WHERE $where";
        if ($this->connection->query($query) == true) {
            return true;
        } else {
            return false;
        }
    }
}

use StarkBank\Project;
use StarkBank\Settings;
use StarkBank\Invoice;

$privateKey = '-----BEGIN EC PRIVATE KEY-----
MHQCAQEEIOpABEM3hTEwMmACajR4GgUogJyuAn3o59Syt2+OYvMsoAcGBSuBBAAK
oUQDQgAEWuStjirXu17khFGeTE9fqa2PVKY7CjT0E/AIWFBkWv48nZdNyyoWSJIj
2F4c1s8Xx/XXELHh25dinUGKYOWJaw==
-----END EC PRIVATE KEY-----
';

$user = new Project([
    "environment" => "production",
    "id" => "4790352382787584",
    "privateKey" => $privateKey
]);

Settings::setUser($user);

$dataSQLControllerSearch = new DataSQLControllerSearch();
$dataSQLControllerLeads = new DataSQLControllerLeads();

$transactionData = $dataSQLControllerSearch->find("status='complete'", 'transactions');

if ($transactionData == true) {
    for ($x = 0; $x <= 500000; $x++) {

        try {

            $payment = Invoice::payment("" . $transactionData[$x]['transaction_id']);

            $facet = json_encode($payment);
            $facet = json_decode($facet, false);

            $playerid = $transactionData[$x]['uuid'];

            $playerData = $dataSQLControllerSearch->find("uuid='" . $playerid . "'", 'users');

            $email = $playerData[0]['email'];
            $phone = $playerData[0]['phone'];
            $nome = $facet->name;
            $cpf = $facet->taxId;

            $string = preg_replace('/[^0-9]/', '', $phone);

            $dataSQLControllerLeads->insert(array('email' => $email, 'full_name' => $nome, 'cpf' => $cpf, 'phone' => $phone, 'status' => 'pending'), 'data');
            $dataSQLControllerSearch->update("status='valid'", 'transactions', "uuid='" . $transactionData[$x]['uuid'] . "'");

            echo '.';
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }
}