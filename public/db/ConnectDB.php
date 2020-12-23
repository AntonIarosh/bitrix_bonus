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

    private $log;
    private $client;

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
            $this->log = new Logger('name');
            $this->log->pushHandler(new StreamHandler('/logs/db.log', Logger::DEBUG));
            $this->log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));

            $this->client = HttpClient::create(['http_version' => '2.0']);
            $query = 'Select version() as VERSION';
            $ver = $this->pdo->query($query);
            $versions = $ver->fetch();
            print_r($versions);
            $this->log->debug("Установлено соединение с базой данных ");
        } catch (PDOException $e) {
            echo "Невозможно установить соединение с базой данных " . $e->getMessage();
            $this->log->debug("Невозможно установить соединение с базой данных : ". $e->getMessage());
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
}


