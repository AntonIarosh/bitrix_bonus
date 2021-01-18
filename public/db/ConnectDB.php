<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class ConnectDB - Получение соединения с базой данных
 * @package Numbers
 */
class ConnectDB
{
    private PDO $pdo;

    private $allBDTables;

    /**
     * ConnectDB constructor - Конструктор класса
     */
    public function __construct()
    {
        try {
            $this->pdo = new PDO(
                'mysql:host=127.0.0.1:3306;dbname=bonusbase',
                'bonususer',
                'Jhbjy:333',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $query = 'SHOW TABLES;';
            $ver = $this->pdo->query($query);
            $tables = $ver->fetchAll();
            $columns = array_column($tables,'Tables_in_bonusbase');
            $this->allBDTables = $columns;
            print_r($columns);
        } catch (PDOException $e) {
            echo 'Невозможно установить соединение с базой данных ' . $e->getMessage();
        }
    }

    /**
     * Получить соединение с БД
     * @return PDO - соединение с БД
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Получить таблицы БД
     * @return array - все таблицы в бд
     */
    public function getTables()
    {
        return $this->allBDTables;
    }
}


