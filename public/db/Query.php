<?php

declare(strict_types=1);

namespace db;

include dirname(__DIR__) . './../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Query - Выполнение запросов к бд
 * @package Numbers
 */
class Query
{
    private $conection;
    private $log;
    private $client;

    const BONUS_FOR_NEW_OWNER = 200;

    /**
     * ParseNewOrder constructor - Конструктор класса
     * @param $conection - соединение с базой данных
     */
    public function __construct($conection)
    {
        $this->conection = $conection;
        $this->log = new Logger('name');
        $this->log->pushHandler(new StreamHandler('/logs/webhook.log', Logger::DEBUG));
        $this->log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));

        $this->client = HttpClient::create(['http_version' => '2.0']);
    }

    /**
     * Проверяет есть ли такой заказчик в системе.
     * @param $idOwner - идентификатор заказачика
     * @return mixed - ответ проверки
     */
    public function isOwnerRegistred($idOwner)
    {
        try {
            $query = "SELECT COUNT(*) AS 'exist' FROM `bonusbase`.`bonus` WHERE 'id_person'=:idOwner";
            $response = $this->conection->prepare($query);
            $response->execute(['idOwner' => $idOwner]);
            return $response->fetch()['exist'];
        } catch (PDOException $e) {
            $this->log->debug("Ошибка выполнения запроса : ". $e->getMessage(). "Идентификатор пользователя : ".$idOwner);
        } finally {
            return null;
        }
    }

    /**
     * @param $idOwner
     * @return false|mixed|null
     */
    public function addOwner($idOwner)
    {
        try {
            $query = "INSERT INTO `bonusbase`.`bonus` (`id_person`,`bonus_discount`,`id_discound_persentage`) VALUES ('id_person'=:idOwner, 'bonus_discount'=:bonus_discount,'2');";
            $response = $this->conection->prepare($query);
            $response->execute(['idOwner' => $idOwner, 'bonus_discount' => self::BONUS_FOR_NEW_OWNER]);

            if($response !== false) {
                $this->log->debug("Пользователь добавлен : ". "Идентификатор пользователя : ".$idOwner);
            } else {
                $this->log->debug("Пользователь НЕ добавлен : ". "Идентификатор пользователя : ".$idOwner);
            }
            return $response;
        } catch (PDOException $e) {
            $this->log->debug("Ошибка выполнения запроса : ". $e->getMessage(). "Идентификатор пользователя : ".$idOwner);
        } finally {
            return null;
        }
    }
}