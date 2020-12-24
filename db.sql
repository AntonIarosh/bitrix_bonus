# таблица Процент Скидки
CREATE TABLE `bonusbase`.`discound_persentage` (
    `id_discound_persentage` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `persent` INT UNSIGNED NULL,
    PRIMARY KEY (`id_discound_persentage`));

ALTER TABLE `bonusbase`.`discound_persentage`
    CHANGE COLUMN `persent` `max_persent` INT(10) UNSIGNED NULL DEFAULT NULL ;

# таблица Бонус
CREATE TABLE `bonusbase`.`bonus` (
    `id_bonus` INT(20) UNSIGNED NOT NULL,
    `id_person` INT(20) UNSIGNED NULL,
    `bonus_discount` INT(20) NULL,
    `id_discound_persentage` INT UNSIGNED NULL,
        PRIMARY KEY (`id_bonus`),
        INDEX `fk_bonus_1_idx` (`id_discound_persentage` ASC),
        CONSTRAINT `fk_bonus_persent`
        FOREIGN KEY (`id_discound_persentage`)
        REFERENCES `bonusbase`.`discound_persentage` (`id_discound_persentage`)
        ON DELETE CASCADE
        ON UPDATE CASCADE);

ALTER TABLE `bonusbase`.`bonus`
    CHANGE COLUMN `id_bonus` `id_bonus` INT(20) UNSIGNED NOT NULL AUTO_INCREMENT ;

# Таблица Этап
CREATE TABLE `bonusbase`.`stage` (
    `id_stage` INT(20) UNSIGNED NOT NULL,
    `id_deal` INT(20) NOT NULL,
    `bonus_stage` VARCHAR(200) NULL,
     PRIMARY KEY (`id_stage`));
ALTER TABLE `bonusbase`.`stage`
    CHANGE COLUMN `id_stage` `id_stage` INT(20) UNSIGNED NOT NULL AUTO_INCREMENT ;

# Таблицы Дата и Время действия
CREATE TABLE `bonusbase`.`date` (
    `id_date` INT(20) NOT NULL,
    `id_person` INT(20) NOT NULL,
    `type_action` VARCHAR(200) NULL,
    `date_action` DATETIME NULL,
    PRIMARY KEY (`id_date`));

ALTER TABLE `bonusbase`.`date`
    CHANGE COLUMN `type_action` `type_action` VARCHAR(500) NULL DEFAULT NULL ;
ALTER TABLE `bonusbase`.`date`
    CHANGE COLUMN `id_date` `id_date` INT(20) NOT NULL AUTO_INCREMENT ;

# Таблица бонусное правило
CREATE TABLE `bonusbase`.`discaunt_rule` (
    `id_discaunt_rule` INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(500) NULL,
    `persent` INT(20) NOT NULL,
    PRIMARY KEY (`id_discaunt_rule`));


# Вставка данных в таблицы
INSERT INTO `bonusbase`.`discound_persentage` (`max_persent`) VALUES ('30');
INSERT INTO `bonusbase`.`discaunt_rule` (`name`, `persent`) VALUES ('default', '5');






