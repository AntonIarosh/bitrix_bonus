<?php

declare(strict_types=1);

namespace db;

include dirname(__DIR__) . './../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;

use function PHPUnit\Framework\isEmpty;

/**
 * Class Query - Выполнение запросов к бд
 * @package Numbers
 */
class Query
{
    private PDO $conection;
    private $log;

    /**
     * ParseNewOrder constructor - Конструктор класса
     * @param $conection - соединение с базой данных
     */
    public function __construct(PDO $conection, $log)
    {
        $this->conection = $conection;
        $this->log = $log;
    }

    /**
     * Проверяет есть ли такой заказчик в системе.
     *
     * @param $idOwner - идентификатор заказачика
     * @return mixed - ответ проверки
     */
    public function isOwnerRegistred($idOwner)
    {
        try {
            $query = "SELECT COUNT(*) AS 'exist' FROM bonusbase.bonus WHERE id_person=:idOwner";
            $response = $this->conection->prepare($query);
            $response->execute(['idOwner' => $idOwner]);
            $data = $response->fetch()['exist'];
            return $data;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Добавление пользователя в бонусную систему
     *
     * @param $idOwner - идентификатор пользователя
     * @param $bonusForNewOwner - бонусы для нового клиента
     * @return false|mixed|null  - результат записи
     */
    public function addOwner($idOwner, $bonusForNewOwner)
    {
        try {
            $query = "INSERT INTO `bonusbase`.`bonus` (`id_person`,`bonus_discount`,`id_discound_persentage`) VALUES (:idOwner, :bonus_discount, '2');";
            $response = $this->conection->prepare($query);
            $response->execute(['idOwner' => $idOwner, 'bonus_discount' => $bonusForNewOwner]);
            if ($response) {
                return 'Добавлено';
            } else {
                return 'Не добавлено';
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Запись в таблицу о изменениях количества бонусов
     *
     * @param $idOwner - идентификатор пользователя
     * @param $typeAction - тип действия, сообщение о действии.
     * @return string - результат записи
     */
    public function writeDate($idOwner, $typeAction)
    {
        $today = date('Y-m-d H:i:s');
        try {
            $query = 'INSERT INTO `bonusbase`.`date` (`id_person`,`type_action`,`date_action`) VALUES (:idOwner, :typeAction, :date);';
            $response = $this->conection->prepare($query);
            $response->execute(['idOwner' => $idOwner, 'typeAction' => $typeAction, 'date' => $today]);
            if ($response) {
                return 'Добавлено';
            } else {
                return 'Не добавлено';
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }


    /**
     * Получение количества бонусов пользователя
     *
     * @param $idOwner - идентификатор пользователя
     * @return mixed|string - количество бонусов
     */
    public function getBonusCount($idOwner)
    {
        try {
            $query = "SELECT bonus_discount AS 'bonus' FROM bonusbase.bonus WHERE id_person=:idOwner";
            $response = $this->conection->prepare($query);
            $response->execute(['idOwner' => $idOwner]);
            return $response->fetch()['bonus'];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Получение правил бонусной программы
     *
     * @param $nameRule - название правила
     * @return  - процент скидки
     */
    public function getRule(string $nameRule)
    {
        try {
            $query = "SELECT persent AS 'rule' FROM bonusbase.discaunt_rule WHERE name=:nameRule";
            $response = $this->conection->prepare($query);
            $response->execute(['nameRule' => $nameRule]);
            return $response->fetch()['rule'];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Получение этапа сделки из таблицы.
     *
     * @param $id_deal - идентификатор сделки
     * @return mixed|string - этап сделки
     */
    public function getStage($id_deal)
    {
        try {
            $query = "SELECT bonus_stage AS 'stage' FROM bonusbase.stage WHERE id_deal=:id_deal";
            $response = $this->conection->prepare($query);
            $response->execute(['id_deal' => $id_deal]);
            return $response->fetch()['stage'];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Задать этап сделки, для контроля выполнения этапа оплаты бонусами.
     * @param $id_deal - идентификатор сделки
     * @param $stage - этап сделки
     * @return string - результат
     */
    public function setStage($id_deal, $stage)
    {
        try {
            $query = 'INSERT INTO `bonusbase`.`stage` (`id_deal`,`bonus_stage`) VALUES (:id_deal, :stage);';
            $response = $this->conection->prepare($query);
            $response->execute(['id_deal' => $id_deal, 'stage' => $stage]);
            if ($response) {
                return 'Добавлено';
            } else {
                return 'Не добавлено';
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }


    /**
     * Начисление бонусов (нового значения бонусов) на счёт пользователя
     * @param $id_owner - идентификатор пользователя
     * @param $bonuses - велечина бонусов после начисления, старое значение бонусов
     * @return string - значение увеличенных бонусов
     */
    public function accrualBonuses($id_owner, $bonuses)
    {
        try {
            $query = 'UPDATE `bonusbase`.`bonus` SET bonus_discount =:new_value WHERE id_person =:id_person ;';
            $response = $this->conection->prepare($query);
            $response->execute(['new_value' => $bonuses, 'id_person' => $id_owner]);
            if ($response) {
                return $bonuses;
            } else {
                return 'Не удалось записать бонусы';
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Получение максимальной скидки для пользователя
     *
     * @param $id_owner - идентификатор пользователя
     * @return mixed|string - максимальная скидка
     */
    public function getMaxDiscauntPersent($id_owner)
    {
        try {
            $query = "SELECT max_persent AS 'persent' FROM bonusbase.bonus JOIN bonusbase.discound_persentage ON `bonus`.`id_discound_persentage` = `discound_persentage`.`id_discound_persentage`  WHERE id_person=:id_owner";
            $response = $this->conection->prepare($query);
            $response->execute(['id_owner' => $id_owner]);
            return $response->fetch()['persent'];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Выполнение записи остатка бонусов в систему после списания
     * @param $id_owner - идентификатор пользователя
     * @param $bonuses - остаток бонусов
     * @return string - новое количество оставшихся бонусов
     */
    public function writeRemainsBonuses($id_owner, $bonuses)
    {
        try {
            $query = 'UPDATE `bonusbase`.`bonus` SET bonus_discount =:new_value WHERE id_person =:id_person ;';
            $response = $this->conection->prepare($query);
            $response->execute(['new_value' => $bonuses, 'id_person' => $id_owner]);
            if ($response) {
                return $bonuses;
            } else {
                return 'Не удалось записать бонусы';
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}