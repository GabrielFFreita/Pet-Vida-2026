-- schema.sql
-- Script de criação da estrutura do banco de dados do projeto Pet Vida.
-- Executar primeiro, depois o seed.sql.

DROP DATABASE IF EXISTS `petvida_db`;
CREATE DATABASE IF NOT EXISTS `petvida_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `petvida_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- Ordem de exclusão respeitando as dependências (filhas primeiro).
DROP TABLE IF EXISTS `doacao`;
DROP TABLE IF EXISTS `adocao`;
DROP TABLE IF EXISTS `foto_animal`;
DROP TABLE IF EXISTS `animais_adocao`;
DROP TABLE IF EXISTS `abrigos`;
DROP TABLE IF EXISTS `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------
-- Tabela `usuarios`
-- ----------------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `idade` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `endereco` varchar(150) DEFAULT NULL,
  `perfil` enum('user','admin') DEFAULT 'user',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Tabela `abrigos`
-- ----------------------------------------------------------------------
CREATE TABLE `abrigos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `localizacao` varchar(255) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `descricao` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Tabela `animais_adocao`
-- ----------------------------------------------------------------------
CREATE TABLE `animais_adocao` (
  `id_animal` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `idade` varchar(45) NOT NULL,
  `especie` varchar(50) NOT NULL,
  `raca` varchar(50) DEFAULT NULL,
  `sexo` enum('Macho','Fêmea') DEFAULT NULL,
  `porte` enum('Pequeno','Médio','Grande') DEFAULT NULL,
  `descricao` text,
  `deficiencia` varchar(255) DEFAULT NULL,
  `status_adocao` enum('Disponível','Em processo','Adotado') NOT NULL DEFAULT 'Disponível',
  `castrado` tinyint(1) DEFAULT NULL,
  `vacinado` tinyint(1) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `abrigo` varchar(250) DEFAULT NULL,
  `data_cadastro` date DEFAULT NULL,
  `id_abrigo` int NOT NULL,
  PRIMARY KEY (`id_animal`),
  KEY `fk_animal_abrigo` (`id_abrigo`),
  CONSTRAINT `fk_animal_abrigo` FOREIGN KEY (`id_abrigo`) REFERENCES `abrigos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Tabela `foto_animal`
-- ----------------------------------------------------------------------
CREATE TABLE `foto_animal` (
  `id_foto` int NOT NULL AUTO_INCREMENT,
  `id_animal` int NOT NULL,
  `ds_img` varchar(180) NOT NULL,
  PRIMARY KEY (`id_foto`),
  UNIQUE KEY `ds_img` (`ds_img`),
  KEY `id_animal` (`id_animal`),
  CONSTRAINT `foto_animal_ibfk_1` FOREIGN KEY (`id_animal`) REFERENCES `animais_adocao` (`id_animal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Tabela `adocao`
-- ----------------------------------------------------------------------
CREATE TABLE `adocao` (
  `id_adocao` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_animal` int NOT NULL,
  `data_solicitacao` date DEFAULT NULL,
  `status` enum('Pendente','Aprovada','Rejeitada') DEFAULT 'Pendente',
  PRIMARY KEY (`id_adocao`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_animal` (`id_animal`),
  CONSTRAINT `adocao_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `adocao_ibfk_2` FOREIGN KEY (`id_animal`) REFERENCES `animais_adocao` (`id_animal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- Tabela `doacao`
-- ----------------------------------------------------------------------
CREATE TABLE `doacao` (
  `id_doacao` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `tipo_doacao` enum('Dinheiro','Ração','Roupa','Brinquedo','Medicamento','Outro') NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `status` enum('Pendente','Aprovada','Recebida','Recusada') DEFAULT 'Pendente',
  `data_doacao` date DEFAULT NULL,
  PRIMARY KEY (`id_doacao`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `doacao_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;