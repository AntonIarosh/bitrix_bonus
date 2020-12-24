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

 //   private $log;

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
          /*$this->log = new Logger('name');
            $this->log->pushHandler(new StreamHandler('logs/webhook.log', Logger::DEBUG));
            $this->log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));*/

            $query = 'SHOW TABLES;';
            //$query = 'Select version() as VERSION';;
            $ver = $this->pdo->query($query);

            $tables = $ver->fetchAll();
            $columns = array_column($tables,'Tables_in_bonusbase');
            $this->allBDTables = $columns;
            print_r($columns);
           // $this->log->debug("Установлено соединение с базой данных ");
        } catch (PDOException $e) {
            echo "Невозможно установить соединение с базой данных " . $e->getMessage();
          //  $this->log->debug("Невозможно установить соединение с базой данных : ". $e->getMessage());
        }
    }

    /**
     * Получить соединение с БД
     * @return - соединение с БД
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Получить таблицы БД
     * @return - все таблицы в бд
     */
    public function getTables()
    {
        return $this->allBDTables;
    }
}


