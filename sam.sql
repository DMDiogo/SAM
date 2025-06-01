-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2025 at 07:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sam`
--

-- --------------------------------------------------------

--
-- Table structure for table `adm`
--

CREATE TABLE `adm` (
  `id_adm` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` int(11) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `data_admissao` date DEFAULT NULL,
  `nivel_acesso` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `adm`
--

INSERT INTO `adm` (`id_adm`, `nome`, `email`, `senha`, `telefone`, `cargo`, `departamento`, `matricula`, `data_admissao`, `nivel_acesso`) VALUES
(3, 'Diogo Oliveira', 'diogodm1225@gmail.com', '$2y$10$fPubpk27CMUX5Fgb1mLrg.Nx3SostAJfWqbbSJy2FjXmcapDQ2aZi', 2147483647, NULL, NULL, NULL, NULL, NULL),
(4, 'Kimi Carvalho', 'kienukimidecarvalho@gmail.com', '$2y$10$FqSfVOSIAp/gLuQ4V49US.y64.7ffr6F4d0BLDnPUhlhKKH3o5wXC', 2147483647, '', '', '', '2025-05-06', 'Administrador'),
(8, 'Freddy Teca', 'fr3ddyteca@gmail.com', '$2y$10$xIncaDUrTgBrFJfqipP4a.FuhwTXVg.lXb7K4chVAscRQwUGXghfK', 2147483647, NULL, NULL, NULL, NULL, NULL);

--
-- Triggers `adm`
--
DELIMITER $$
CREATE TRIGGER `delete_adm_app` AFTER DELETE ON `adm` FOR EACH ROW BEGIN
    DELETE FROM `app_empresas`.`empresas` WHERE `email` = OLD.email;
    -- A exclusão em cascata vai automaticamente remover os funcionários relacionados
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_adm_app` AFTER UPDATE ON `adm` FOR EACH ROW BEGIN
    -- Se o email foi alterado, precisamos encontrar o registro pelo email antigo
    IF OLD.email != NEW.email THEN
        -- Atualizar a empresa correspondente no app_empresas
        UPDATE `app_empresas`.`empresas` 
        SET `nome` = NEW.nome, 
            `email` = NEW.email
        WHERE `email` = OLD.email;
    ELSE
        -- Se o email não mudou, apenas atualizar outros dados
        UPDATE `app_empresas`.`empresas` 
        SET `nome` = NEW.nome
        WHERE `email` = NEW.email;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `adm_sessions`
--

CREATE TABLE `adm_sessions` (
  `session_id` varchar(255) NOT NULL,
  `adm_id` int(11) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adm_sessions`
--

INSERT INTO `adm_sessions` (`session_id`, `adm_id`, `user_agent`, `ip_address`, `last_activity`) VALUES
('4hqrpp8lfbgvb13p9s0h4es7it', 4, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '::1', '2025-05-30 23:26:10'),
('vv04n3d0n4pe7q54lhj4sc910m', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '::1', '2025-05-30 23:34:10');

-- --------------------------------------------------------

--
-- Table structure for table `ausencias`
--

CREATE TABLE `ausencias` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `tipo_ausencia` varchar(50) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `dias_uteis` int(11) NOT NULL,
  `justificacao` varchar(100) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bancos_ativos`
--

CREATE TABLE `bancos_ativos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `banco_nome` varchar(100) NOT NULL,
  `banco_codigo` varchar(10) NOT NULL,
  `ativo` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bancos_ativos`
--

INSERT INTO `bancos_ativos` (`id`, `empresa_id`, `banco_nome`, `banco_codigo`, `ativo`) VALUES
(2, 2, 'Banco BIC', 'BIC', 0),
(3, 2, 'Banco Caixa Geral Angola', 'BCGA', 0),
(4, 2, 'Banco Comercial Angolano (BCA)', 'BCA', 0),
(6, 2, 'Banco de Desenvolvimento de Angola (BDA)', 'BDA', 0),
(7, 2, 'Banco de Poupança e Crédito (BPC)', 'BPC', 0),
(8, 2, 'Banco Económico', 'BE', 0),
(9, 2, 'Banco Fomento Angola (BFA)', 'BFA', 0),
(10, 2, 'Banco Millennium Atlântico', 'BMA', 1),
(11, 2, 'Banco Sol', 'SOL', 0),
(12, 2, 'Banco Valor', 'VALOR', 0),
(13, 2, 'Banco Yetu', 'YETU', 0),
(14, 2, 'Banco VTB África', 'VTB', 1),
(15, 2, 'Banco Angolano de Investimentos (BAI)', 'BAI', 1),
(17, 2, 'Banco de Poupança do Kimi', 'BPK', 0);

-- --------------------------------------------------------

--
-- Table structure for table `beneficios`
--

CREATE TABLE `beneficios` (
  `id_beneficio` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `fun_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `salario_base` decimal(10,2) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cargos`
--

INSERT INTO `cargos` (`id`, `nome`, `departamento_id`, `salario_base`, `empresa_id`, `created_at`) VALUES
(1, 'Programador', 1, 333000.00, 2, '2025-05-30 21:03:34'),
(2, 'Vendas', 2, 220000.00, 2, '2025-05-30 21:08:09'),
(3, 'NNC', 3, 300000.00, 2, '2025-05-30 21:38:49'),
(4, 'Programadores', 1, 50001.00, 2, '2025-05-30 22:07:48'),
(5, 'Backend', 4, 222000.00, 2, '2025-05-30 22:22:15');

-- --------------------------------------------------------

--
-- Table structure for table `configuracoes_seguranca`
--

CREATE TABLE `configuracoes_seguranca` (
  `id` int(11) NOT NULL,
  `adm_id` int(11) NOT NULL,
  `dois_fatores` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `configuracoes_seguranca`
--

INSERT INTO `configuracoes_seguranca` (`id`, `adm_id`, `dois_fatores`) VALUES
(1, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departamentos`
--

INSERT INTO `departamentos` (`id`, `nome`, `empresa_id`, `created_at`) VALUES
(1, 'TI', 2, '2025-05-30 21:02:27'),
(2, 'Marketings', 2, '2025-05-30 21:07:41'),
(3, 'ORG', 2, '2025-05-30 21:12:58'),
(4, 'TI2', 2, '2025-05-30 22:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `dispositivos_confiaveis`
--

CREATE TABLE `dispositivos_confiaveis` (
  `id` int(11) NOT NULL,
  `adm_id` int(11) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acesso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dispositivos_confiaveis`
--

INSERT INTO `dispositivos_confiaveis` (`id`, `adm_id`, `user_agent`, `ip_address`, `data_criacao`, `ultimo_acesso`) VALUES
(1, 4, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '::1', '2025-05-30 15:02:19', '2025-05-31 15:30:44'),
(4, 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '::1', '2025-05-30 23:32:40', '2025-05-30 23:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `documentos`
--

CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `data` date NOT NULL,
  `descricao` text DEFAULT NULL,
  `anexo` varchar(255) NOT NULL,
  `num_funcionario` int(11) NOT NULL,
  `folder` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `nipc` varchar(20) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `email_corp` varchar(255) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `setor_atuacao` varchar(100) NOT NULL,
  `num_fun` int(11) NOT NULL,
  `data_cadastro` date NOT NULL DEFAULT curdate(),
  `adm_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nome`, `nipc`, `endereco`, `email_corp`, `telefone`, `setor_atuacao`, `num_fun`, `data_cadastro`, `adm_id`) VALUES
(2, 'Carvalho Lda', '18041959', 'rua Pedro de Castro Van-Dunem Loy, Casa 4, Vila Ecocampo', 'kienukimidecarvalho@gmail.com', '924135515', 'servicos', 1, '2025-05-27', 4),
(4, 'Lil Teca SA', '12345', 'Nova Vida', 'fr3ddyteca@gmail.com', '975851987', 'tecnologia', 1, '2025-05-31', 8);

--
-- Triggers `empresa`
--
DELIMITER $$
CREATE TRIGGER `delete_empresa_app` AFTER DELETE ON `empresa` FOR EACH ROW BEGIN    
    -- Excluir a empresa correspondente no app_empresas
    DELETE FROM `app_empresas`.`empresas` WHERE `site_empresa_id` = OLD.id_empresa;
    -- A exclusão em cascata vai automaticamente remover os funcionários relacionados
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_empresa_app` AFTER INSERT ON `empresa` FOR EACH ROW BEGIN
    -- Inserir nova empresa no app_empresas quando criada no site
    INSERT INTO `app_empresas`.`empresas` 
    (`nome`, `email`, `senha`, `data_cadastro`, `site_empresa_id`) 
    VALUES 
    (NEW.nome, NEW.email_corp, '$2y$10$gVkC1tSsNFcgkuHgWA8Y0esHFKcuNWbljVEAyWjzSWl/UdfKVSERy', NOW(), NEW.id_empresa);
    -- Nota: A senha é um placeholder, deverá ser definida via API ou outro meio
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `falta`
--

CREATE TABLE `falta` (
  `id_falta` int(11) NOT NULL,
  `data` date NOT NULL,
  `motivo` text NOT NULL,
  `justificada` enum('Sim','Não') NOT NULL,
  `fun_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `funcionario`
--

CREATE TABLE `funcionario` (
  `id_fun` int(11) NOT NULL,
  `num_mecanografico` varchar(20) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `bi` varchar(14) NOT NULL,
  `emissao_bi` date NOT NULL,
  `validade_bi` date NOT NULL,
  `data_nascimento` date NOT NULL,
  `pais` varchar(50) NOT NULL,
  `morada` varchar(255) NOT NULL,
  `genero` enum('Masculino','Feminino') NOT NULL,
  `num_agregados` int(11) NOT NULL DEFAULT 0,
  `contato_emergencia` varchar(20) NOT NULL,
  `nome_contato_emergencia` varchar(100) NOT NULL,
  `telemovel` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `estado` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `cargo` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `tipo_trabalhador` enum('Efetivo','Temporário','Estagiário','Autônomo','Freelancer','Terceirizado','Intermitente','Voluntário') NOT NULL,
  `num_conta_bancaria` varchar(30) NOT NULL,
  `banco` enum('BAI','BIC') NOT NULL,
  `iban` varchar(35) NOT NULL,
  `salario_base` decimal(10,2) NOT NULL DEFAULT 0.00,
  `num_ss` varchar(30) NOT NULL,
  `data_admissao` date NOT NULL DEFAULT curdate(),
  `empresa_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pendente_biometria'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `funcionario`
--

INSERT INTO `funcionario` (`id_fun`, `num_mecanografico`, `nome`, `foto`, `bi`, `emissao_bi`, `validade_bi`, `data_nascimento`, `pais`, `morada`, `genero`, `num_agregados`, `contato_emergencia`, `nome_contato_emergencia`, `telemovel`, `email`, `estado`, `cargo`, `departamento`, `tipo_trabalhador`, `num_conta_bancaria`, `banco`, `iban`, `salario_base`, `num_ss`, `data_admissao`, `empresa_id`, `status`) VALUES
(1, 'EMP-0001', 'Kimi Carvalho', NULL, '32432432423', '2025-05-06', '2025-05-30', '2025-04-29', 'angola', 'rua Pedro de Castro Van-Dunem Loy, Casa 4, Vila Ecocampo', 'Masculino', 6, '', '', '924135515', 'kienukimidecarvalho@gmail.com', 'Ativo', 'Analista Financeiro', 'tecnologia', 'Temporário', '1111', 'BAI', '678786', 6666.00, '2432432432', '2025-05-28', 2, 'pendente_biometria'),
(7, 'EMP-0002', 'Jorge Mundula', NULL, '32432432423322', '2025-05-05', '2025-06-05', '2025-05-06', 'angola', 'Fubu', 'Masculino', 2, '924135515', 'Kimi Carvalho', '924135515', 'jorgemundula@gmail.com', 'Ativo', '2', '2', 'Efetivo', '111111154234', 'BAI', '432432432425', 220000.00, '243243243243123123324', '2025-05-31', 2, 'pendente_biometria');

--
-- Triggers `funcionario`
--
DELIMITER $$
CREATE TRIGGER `delete_funcionario_app` AFTER DELETE ON `funcionario` FOR EACH ROW BEGIN
    -- Excluir funcionário no app_empresas
    DELETE FROM `app_empresas`.`employees` WHERE id = OLD.num_mecanografico;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `gerar_num_mecanografico` BEFORE INSERT ON `funcionario` FOR EACH ROW BEGIN
    DECLARE ultimo_num INT;
    DECLARE novo_num VARCHAR(20);

    -- Busca o último número mecanográfico cadastrado
    SELECT IFNULL(MAX(CAST(SUBSTRING(num_mecanografico, 5, 4) AS UNSIGNED)), 0) + 1 
    INTO ultimo_num FROM funcionario;

    -- Formata o novo número mecanográfico no padrão EMP-000X
    SET novo_num = CONCAT('EMP-', LPAD(ultimo_num, 4, '0'));

    -- Atribui o número mecanográfico ao novo funcionário
    SET NEW.num_mecanografico = novo_num;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `sync_funcionario_app` AFTER INSERT ON `funcionario` FOR EACH ROW BEGIN
    DECLARE app_empresa_id INT;
    
    -- Encontrar o ID da empresa no app_empresas
    SELECT id INTO app_empresa_id 
    FROM `app_empresas`.`empresas` 
    WHERE site_empresa_id = NEW.empresa_id
    LIMIT 1;
    
    IF app_empresa_id IS NOT NULL THEN
        -- Inserir funcionário no app_empresas
        INSERT INTO `app_empresas`.`employees` 
        (`id`, `name`, `position`, `department`, `digital_signature`, `empresa_id`) 
        VALUES 
        (NEW.num_mecanografico, NEW.nome, NEW.cargo, NEW.departamento, 0, app_empresa_id);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_funcionario_app` AFTER UPDATE ON `funcionario` FOR EACH ROW BEGIN
    -- Atualizar funcionário no app_empresas
    UPDATE `app_empresas`.`employees` 
    SET `name` = NEW.nome, 
        `position` = NEW.cargo, 
        `department` = NEW.departamento
    WHERE id = NEW.num_mecanografico;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `log_atividades`
--

CREATE TABLE `log_atividades` (
  `id` int(11) NOT NULL,
  `adm_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_atividades`
--

INSERT INTO `log_atividades` (`id`, `adm_id`, `acao`, `ip_address`, `data_hora`) VALUES
(1, 4, 'Atualização de Perfil', '::1', '2025-05-30 16:31:24'),
(2, 4, 'Login Efetuado', '::1', '2025-05-30 17:05:31'),
(3, 4, 'Login Efetuado', '::1', '2025-05-30 19:31:03'),
(4, 4, 'Login Efetuado', '::1', '2025-05-30 19:32:59'),
(5, 4, 'Login Efetuado', '::1', '2025-05-30 23:26:10'),
(6, 8, 'Login Efetuado', '::1', '2025-05-30 23:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `politicas_trabalho`
--

CREATE TABLE `politicas_trabalho` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `tipo` enum('horario','homeoffice','vestimenta') NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text NOT NULL,
  `valor` varchar(255) NOT NULL,
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `redefinicao_senha`
--

CREATE TABLE `redefinicao_senha` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `data_expiracao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registros_ponto`
--

CREATE TABLE `registros_ponto` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `entrada` datetime DEFAULT NULL,
  `saida` datetime DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `turnos_trabalho`
--

CREATE TABLE `turnos_trabalho` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `dias_semana` varchar(100) NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_saida` time NOT NULL,
  `almoco_inicio` time NOT NULL,
  `almoco_fim` time NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adm`
--
ALTER TABLE `adm`
  ADD PRIMARY KEY (`id_adm`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- Indexes for table `adm_sessions`
--
ALTER TABLE `adm_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `unique_session` (`adm_id`,`user_agent`,`ip_address`);

--
-- Indexes for table `ausencias`
--
ALTER TABLE `ausencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indexes for table `bancos_ativos`
--
ALTER TABLE `bancos_ativos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indexes for table `beneficios`
--
ALTER TABLE `beneficios`
  ADD PRIMARY KEY (`id_beneficio`),
  ADD KEY `fk_beneficios_funcionario1_idx` (`fun_id`);

--
-- Indexes for table `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `departamento_id` (`departamento_id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indexes for table `configuracoes_seguranca`
--
ALTER TABLE `configuracoes_seguranca`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adm_id` (`adm_id`);

--
-- Indexes for table `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indexes for table `dispositivos_confiaveis`
--
ALTER TABLE `dispositivos_confiaveis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dispositivo` (`adm_id`,`user_agent`,`ip_address`);

--
-- Indexes for table `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `num_funcionario` (`num_funcionario`);

--
-- Indexes for table `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`,`adm_id`),
  ADD UNIQUE KEY `nipc_UNIQUE` (`nipc`),
  ADD KEY `fk_empresa_adm_idx` (`adm_id`);

--
-- Indexes for table `falta`
--
ALTER TABLE `falta`
  ADD PRIMARY KEY (`id_falta`),
  ADD KEY `fk_falta_funcionario1_idx` (`fun_id`);

--
-- Indexes for table `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id_fun`),
  ADD UNIQUE KEY `bi` (`bi`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `num_conta_bancaria` (`num_conta_bancaria`),
  ADD UNIQUE KEY `iban` (`iban`),
  ADD UNIQUE KEY `num_ss` (`num_ss`),
  ADD UNIQUE KEY `num_mecanografico` (`num_mecanografico`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indexes for table `log_atividades`
--
ALTER TABLE `log_atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adm_id` (`adm_id`);

--
-- Indexes for table `politicas_trabalho`
--
ALTER TABLE `politicas_trabalho`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indexes for table `redefinicao_senha`
--
ALTER TABLE `redefinicao_senha`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registros_ponto`
--
ALTER TABLE `registros_ponto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `funcionario_id` (`funcionario_id`,`data`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `data` (`data`);

--
-- Indexes for table `turnos_trabalho`
--
ALTER TABLE `turnos_trabalho`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adm`
--
ALTER TABLE `adm`
  MODIFY `id_adm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ausencias`
--
ALTER TABLE `ausencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bancos_ativos`
--
ALTER TABLE `bancos_ativos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `beneficios`
--
ALTER TABLE `beneficios`
  MODIFY `id_beneficio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `configuracoes_seguranca`
--
ALTER TABLE `configuracoes_seguranca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dispositivos_confiaveis`
--
ALTER TABLE `dispositivos_confiaveis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `falta`
--
ALTER TABLE `falta`
  MODIFY `id_falta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id_fun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `log_atividades`
--
ALTER TABLE `log_atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `politicas_trabalho`
--
ALTER TABLE `politicas_trabalho`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `redefinicao_senha`
--
ALTER TABLE `redefinicao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registros_ponto`
--
ALTER TABLE `registros_ponto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `turnos_trabalho`
--
ALTER TABLE `turnos_trabalho`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adm_sessions`
--
ALTER TABLE `adm_sessions`
  ADD CONSTRAINT `adm_sessions_ibfk_1` FOREIGN KEY (`adm_id`) REFERENCES `adm` (`id_adm`) ON DELETE CASCADE;

--
-- Constraints for table `ausencias`
--
ALTER TABLE `ausencias`
  ADD CONSTRAINT `ausencias_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id_fun`),
  ADD CONSTRAINT `ausencias_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`);

--
-- Constraints for table `bancos_ativos`
--
ALTER TABLE `bancos_ativos`
  ADD CONSTRAINT `bancos_ativos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`);

--
-- Constraints for table `beneficios`
--
ALTER TABLE `beneficios`
  ADD CONSTRAINT `beneficios_ibfk_1` FOREIGN KEY (`fun_id`) REFERENCES `funcionario` (`id_fun`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `cargos`
--
ALTER TABLE `cargos`
  ADD CONSTRAINT `cargos_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`),
  ADD CONSTRAINT `cargos_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`);

--
-- Constraints for table `configuracoes_seguranca`
--
ALTER TABLE `configuracoes_seguranca`
  ADD CONSTRAINT `configuracoes_seguranca_ibfk_1` FOREIGN KEY (`adm_id`) REFERENCES `adm` (`id_adm`) ON DELETE CASCADE;

--
-- Constraints for table `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`);

--
-- Constraints for table `dispositivos_confiaveis`
--
ALTER TABLE `dispositivos_confiaveis`
  ADD CONSTRAINT `dispositivos_confiaveis_ibfk_1` FOREIGN KEY (`adm_id`) REFERENCES `adm` (`id_adm`) ON DELETE CASCADE;

--
-- Constraints for table `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`num_funcionario`) REFERENCES `funcionario` (`id_fun`) ON DELETE CASCADE;

--
-- Constraints for table `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `fk_empresa_adm` FOREIGN KEY (`adm_id`) REFERENCES `adm` (`id_adm`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `falta`
--
ALTER TABLE `falta`
  ADD CONSTRAINT `falta_ibfk_1` FOREIGN KEY (`fun_id`) REFERENCES `funcionario` (`id_fun`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `log_atividades`
--
ALTER TABLE `log_atividades`
  ADD CONSTRAINT `log_atividades_ibfk_1` FOREIGN KEY (`adm_id`) REFERENCES `adm` (`id_adm`) ON DELETE CASCADE;

--
-- Constraints for table `politicas_trabalho`
--
ALTER TABLE `politicas_trabalho`
  ADD CONSTRAINT `politicas_trabalho_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`);

--
-- Constraints for table `registros_ponto`
--
ALTER TABLE `registros_ponto`
  ADD CONSTRAINT `registros_ponto_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id_fun`);

--
-- Constraints for table `turnos_trabalho`
--
ALTER TABLE `turnos_trabalho`
  ADD CONSTRAINT `turnos_trabalho_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;