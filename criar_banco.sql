-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema seminario
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `seminario` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `seminario` ;

-- -----------------------------------------------------
-- Table `seminario`.`usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `seminario`.`usuario` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NULL DEFAULT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `telefone` VARCHAR(20) NULL DEFAULT NULL,
  `endereco` TEXT NULL DEFAULT NULL,
  `senha` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `email` (`email` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `seminario`.`item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `seminario`.`item` (
  `id_item` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `tipo` ENUM('doar', 'trocar') NOT NULL,
  `descricao` TEXT NOT NULL,
  `categoria` VARCHAR(50) NOT NULL,
  `condicao` ENUM('Novo', 'Usado', 'Velho') NOT NULL,
  `endereco` VARCHAR(255) NOT NULL,
  `observacao` TEXT NULL DEFAULT NULL,
  `imagem` VARCHAR(255) NULL DEFAULT NULL,
  `data_cadastro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(20) NULL DEFAULT 'disponivel',
  PRIMARY KEY (`id_item`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `item_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `seminario`.`usuario` (`id_usuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `seminario`.`solicitacao`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `seminario`.`solicitacao` (
  `id_solicitacao` INT NOT NULL AUTO_INCREMENT,
  `id_item` INT NOT NULL,
  `id_solicitante` INT NOT NULL,
  `mensagem` TEXT NULL DEFAULT NULL,
  `id_item_proposto` INT NULL DEFAULT NULL,
  `status` ENUM('pendente', 'aceito', 'recusado') NULL DEFAULT 'pendente',
  `data_solicitacao` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `resposta` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_solicitacao`),
  INDEX `id_item` (`id_item` ASC) VISIBLE,
  INDEX `id_solicitante` (`id_solicitante` ASC) VISIBLE,
  CONSTRAINT `solicitacao_ibfk_1`
    FOREIGN KEY (`id_item`)
    REFERENCES `seminario`.`item` (`id_item`)
    ON DELETE CASCADE,
  CONSTRAINT `solicitacao_ibfk_2`
    FOREIGN KEY (`id_solicitante`)
    REFERENCES `seminario`.`usuario` (`id_usuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
