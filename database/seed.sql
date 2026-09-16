-- seed.sql
-- Arquivo destinado a carga inicial de dados de exemplo do projeto Pet Vida.
-- Executar depois do schema.sql.

USE `petvida_db`;

-- ----------------------------------------------------------------------
-- Dados da tabela `usuarios`
-- ----------------------------------------------------------------------
INSERT INTO `usuarios` (`id_usuario`, `nome`, `idade`, `email`, `senha`, `telefone`, `cpf`, `data_nascimento`, `cidade`, `estado`, `endereco`, `perfil`) VALUES
(1, 'Gabriel', 18, 'gabriel123@gmail.com', '$2y$10$HaxMnifdzoJhUzuUZ9k78O0Rh//xiK0aCWgTV2Extn4ioO.EC7nZK', '479851632577', '26457981335', '2008-03-10', 'Joinville', 'SC', 'Rua do Samba 123', 'admin'),
(2, 'Wellington da Silva Serafim', 18, 'welli@serafi', '$2y$10$POJg17lrILxUQmPRfyfO/.GvXovC3S/ns7Z9sBUNMz2kU0efycp.O', '453535256256', '103104333908', '2000-12-28', 'joinville', 'SC', 'alfaville', 'user'),
(3, 'Lais Vitória Meris', 20, 'lais@gmail.com', '$2y$10$PmX4/HM2zTWlzEKPZqju.OqNAK34jJSVPVYToFwwoFvQHDi0vMRo2', '(47) 99999-9999', '11111111111', '2006-07-20', 'Joinville', 'SC', 'Rua da Alegria', 'user'),
(5, 'Lais Vitória Meris', 18, 'laisvitoria@gmail.com', '$2y$12$5fCOlsVXjEuOhvWZfM1YhOQ4ucyTWbS1VEDBQl2zt5sSjT4jgVNs.', '47922455168', '16547985447', '2008-05-10', 'Joinville', 'SC', 'Rua Inventada 23', 'user'),
(6, 'Daniel dos Santos Costa', 1000, 'danielpica@bom.com', '$2y$10$/GKr4.l1pSy5ehGyM4pAUuWfW1rugledRBeXYrN5VKqZuwLBlEv1u', '479999999999', '99999999999', '2026-09-16', 'Piedade', 'SP', 'Rua dos Pintos', 'user');

-- ----------------------------------------------------------------------
-- Dados da tabela `abrigos`
-- ----------------------------------------------------------------------
INSERT INTO `abrigos` (`id`, `nome`, `cnpj`, `localizacao`, `cep`, `descricao`) VALUES
(1, 'Abrigo Patinhas Felizes', '12.345.678/0001-01', 'Rua XV de Novembro, 1450 - América, Joinville - SC', '89201-120', 'Abrigo voltado ao resgate de cães e gatos abandonados, oferecendo atendimento veterinário, castração e adoção responsável.'),
(2, 'Instituto Amor Animal', '23.456.789/0001-12', 'Rua Blumenau, 890 - Anita Garibaldi, Joinville - SC', '89203-410', 'Organização sem fins lucrativos dedicada ao acolhimento de animais vítimas de maus-tratos e abandono.'),
(3, 'Casa dos Bichinhos', '34.567.890/0001-23', 'Rua Albano Schmidt, 550 - Boa Vista, Joinville - SC', '89218-500', 'Abrigo com capacidade para aproximadamente 80 animais, promovendo campanhas de adoção e vacinação.'),
(4, 'Instituto Novo Lar', '90.123.456/0001-89', 'Rua Graciosa, 275 - Guanabara, Joinville - SC', '89210-310', 'Desenvolve projetos sociais voltados à adoção consciente e ao bem-estar animal.');

-- ----------------------------------------------------------------------
-- Dados da tabela `animais_adocao`
-- ----------------------------------------------------------------------
INSERT INTO `animais_adocao` (`id_animal`, `nome`, `idade`, `especie`, `raca`, `sexo`, `porte`, `descricao`, `deficiencia`, `status_adocao`, `castrado`, `vacinado`, `peso`, `abrigo`, `data_cadastro`, `id_abrigo`) VALUES
(5, 'Thor', '1 ano', 'Cachorro', 'Pastor Alemão', 'Macho', 'Grande', '', NULL, 'Em processo', 1, 1, 12.00, NULL, '2026-07-29', 4),
(6, 'Mel', '2 anos', 'Cachorro', 'Shih Tzu', 'Fêmea', 'Pequeno', 'Calma, carinhosa e gosta de ficar no colo.', 'Nenhuma', 'Em processo', 1, 1, 6.10, NULL, '2026-07-30', 4),
(7, 'Simba', '1 ano', 'Gato', 'Siamês', 'Macho', 'Pequeno', 'Curioso, brincalhão e muito sociável.', 'Nenhuma', 'Em processo', 1, 1, 4.20, NULL, '2026-07-30', 3),
(8, 'Luna', '5 anos', 'Gato', 'Persa', 'Fêmea', 'Pequeno', 'Tranquila, adora locais silenciosos e carinho.', 'Visão reduzida do olho esquerdo', 'Disponível', 1, 1, 5.00, NULL, '2026-07-30', 2),
(9, 'Max', '3 anos', 'Cachorro', 'Border Collie', 'Macho', 'Médio', 'Muito inteligente, aprende comandos rapidamente.', 'Nenhuma', 'Disponível', 1, 1, 18.40, NULL, '2026-07-30', 1),
(10, 'Fred', '7 anos', 'Cachorro', 'Vira-lata', 'Macho', 'Médio', 'Muito companheiro e tranquilo durante passeios.', 'Surdez parcial', 'Disponível', 1, 1, 19.60, NULL, '2026-07-30', 3),
(11, 'Nina', '2 anos', 'Gato', 'SRD', 'Fêmea', 'Pequeno', 'Carinhosa e gosta de brincar com bolinhas.', 'Nenhuma', 'Disponível', 1, 1, 3.80, NULL, '2026-07-30', 2),
(12, 'Bob', '6 anos', 'Cachorro', 'Golden Retriever', 'Macho', 'Grande', 'Extremamente amigável e obediente.', 'Nenhuma', 'Em processo', 1, 1, 34.10, NULL, '2026-07-30', 1);

-- ----------------------------------------------------------------------
-- Dados da tabela `foto_animal`
-- ----------------------------------------------------------------------
INSERT INTO `foto_animal` (`id_foto`, `id_animal`, `ds_img`) VALUES
(7, 5, '6a6a1520f0e823.05744413_6a488dfb73f199.08615140_pastor1.jpg'),
(8, 5, '6a6a1520f11132.03822894_6a488dfb743d48.14010178_pastor3.jpg'),
(9, 5, '6a6a1520f14278.19429396_6a488dfb7417c3.25111970_pastor2.jpg'),
(10, 6, '6a6a9dd02b49e9.83620452_Shih-tzu3.jpg'),
(11, 6, '6a6a9dd02c7635.25203277_Shih-Tzu2.jpg'),
(12, 6, '6a6a9dd02c8b67.95030079_shih-tzu1.jpg'),
(13, 7, '6a6a9f1dd3a980.98044235_Gato-siames-3.jpg'),
(14, 7, '6a6a9f1dd49711.75459791_siames.jpg'),
(15, 7, '6a6a9f1dd51a29.20658976_siames2.jpg'),
(16, 8, '6a6a9fb95ae3b9.27554255_persa1.jpg'),
(17, 8, '6a6a9fb95b9c54.00772105_persa2.jpg'),
(18, 8, '6a6a9fb95bd516.59077428_persa3.jpg'),
(19, 9, '6a6aa0bd799aa5.00677260_border3.jpeg'),
(20, 9, '6a6aa0bd7a4729.12824761_border2.jpg'),
(21, 9, '6a6aa0bd7afd70.03810384_border1.jpeg'),
(22, 10, '6a6aa1f52c1d69.57243924_caramelo1.jpg'),
(23, 10, '6a6aa1f52cbde2.16800727_caramelo2.jpeg'),
(24, 10, '6a6aa1f52cf209.30814669_caramelo3.jpg'),
(25, 11, '6a6aa2997c77e7.05512857_gato1.avif'),
(26, 11, '6a6aa2997d73f1.75855201_gato2.png'),
(27, 11, '6a6aa2997dadb1.76720558_gato3.jpg'),
(28, 12, '6a6aa32c3a2767.74875190_gol.jpg'),
(29, 12, '6a6aa32c3ac389.70996302_golden1.jpg'),
(30, 12, '6a6aa32c3b0b75.05975976_golden2.jpg');

-- ----------------------------------------------------------------------
-- Dados da tabela `adocao`
-- ----------------------------------------------------------------------
INSERT INTO `adocao` (`id_adocao`, `id_usuario`, `id_animal`, `data_solicitacao`, `status`) VALUES
(1, 1, 5, '2026-07-29', 'Pendente'),
(2, 1, 6, '2026-09-14', 'Pendente'),
(3, 6, 12, '2026-09-15', 'Pendente'),
(4, 6, 7, '2026-09-15', 'Pendente');

-- ----------------------------------------------------------------------
-- Tabela `doacao` (sem dados iniciais)
-- ----------------------------------------------------------------------