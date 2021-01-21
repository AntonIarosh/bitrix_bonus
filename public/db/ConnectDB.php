<?php

/**
 * Class ConnectDB - Получение соединения с базой данных
 * @package Numbers
 */
class ConnectDB
{
    private PDO $pdo;

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

}


