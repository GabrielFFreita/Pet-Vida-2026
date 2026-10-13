
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `abrigos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `abrigos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `localizacao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `abrigos` WRITE;
/*!40000 ALTER TABLE `abrigos` DISABLE KEYS */;
INSERT INTO `abrigos` VALUES (1,'Abrigo Patinhas Felizes','12.345.678/0001-01','Rua XV de Novembro, 1450 - AmÃ©rica, Joinville - SC','89201-120','Abrigo voltado ao resgate de cÃ£es e gatos abandonados, oferecendo atendimento veterinÃ¡rio, castraÃ§Ã£o e adoÃ§Ã£o responsÃ¡vel.'),(2,'Instituto Amor Animal','23.456.789/0001-12','Rua Blumenau, 890 - Anita Garibaldi, Joinville - SC','89203-410','OrganizaÃ§Ã£o sem fins lucrativos dedicada ao acolhimento de animais vÃ­timas de maus-tratos e abandono.'),(3,'Casa dos Bichinhos','34.567.890/0001-23','Rua Albano Schmidt, 550 - Boa Vista, Joinville - SC','89218-500','Abrigo com capacidade para aproximadamente 80 animais, promovendo campanhas de adoÃ§Ã£o e vacinaÃ§Ã£o.'),(4,'Instituto Novo Lar','90.123.456/0001-89','Rua Graciosa, 275 - Guanabara, Joinville - SC','89210-310','Desenvolve projetos sociais voltados Ã  adoÃ§Ã£o consciente e ao bem-estar animal.');
/*!40000 ALTER TABLE `abrigos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `animais_adocao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `animais_adocao` (
  `id_animal` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idade` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especie` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `raca` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexo` enum('Macho','FÃªmea') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `porte` enum('Pequeno','MÃ©dio','Grande') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `deficiencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_adocao` enum('DisponÃ­vel','Em processo','Adotado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DisponÃ­vel',
  `castrado` tinyint(1) DEFAULT NULL,
  `vacinado` tinyint(1) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `abrigo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_cadastro` date DEFAULT NULL,
  `id_abrigo` int NOT NULL,
  PRIMARY KEY (`id_animal`),
  KEY `fk_animal_abrigo` (`id_abrigo`),
  CONSTRAINT `fk_animal_abrigo` FOREIGN KEY (`id_abrigo`) REFERENCES `abrigos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `animais_adocao` WRITE;
/*!40000 ALTER TABLE `animais_adocao` DISABLE KEYS */;
INSERT INTO `animais_adocao` VALUES (5,'Thor','1 ano','Cachorro','Pastor AlemÃ£o','Macho','Grande','',NULL,'Em processo',1,1,12.00,NULL,'2026-07-29',4),(6,'Mel','2 anos','Cachorro','Shih Tzu','FÃªmea','Pequeno','Calma, carinhosa e gosta de ficar no colo.','Nenhuma','Em processo',1,1,6.10,NULL,'2026-07-30',4),(7,'Simba','1 ano','Gato','SiamÃªs','Macho','Pequeno','Curioso, brincalhÃ£o e muito sociÃ¡vel.','Nenhuma','Em processo',1,1,4.20,NULL,'2026-07-30',3),(8,'Luna','5 anos','Gato','Persa','FÃªmea','Pequeno','Tranquila, adora locais silenciosos e carinho.','VisÃ£o reduzida do olho esquerdo','DisponÃ­vel',1,1,5.00,NULL,'2026-07-30',2),(9,'Max','3 anos','Cachorro','Border Collie','Macho','MÃ©dio','Muito inteligente, aprende comandos rapidamente.','Nenhuma','DisponÃ­vel',1,1,18.40,NULL,'2026-07-30',1),(10,'Fred','7 anos','Cachorro','Vira-lata','Macho','MÃ©dio','Muito companheiro e tranquilo durante passeios.','Surdez parcial','DisponÃ­vel',1,1,19.60,NULL,'2026-07-30',3),(11,'Nina','2 anos','Gato','SRD','FÃªmea','Pequeno','Carinhosa e gosta de brincar com bolinhas.','Nenhuma','DisponÃ­vel',1,1,3.80,NULL,'2026-07-30',2),(12,'Bob','6 anos','Cachorro','Golden Retriever','Macho','Grande','Extremamente amigÃ¡vel e obediente.','Nenhuma','Em processo',1,1,34.10,NULL,'2026-07-30',1);
/*!40000 ALTER TABLE `animais_adocao` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

