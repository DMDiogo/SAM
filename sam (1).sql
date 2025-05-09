-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03-Maio-2025 às 16:51
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sam`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `adm`
--

CREATE TABLE `adm` (
  `id_adm` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `adm`
--

INSERT INTO `adm` (`id_adm`, `nome`, `email`, `senha`, `telefone`) VALUES
(2, 'Diogo Oliveira', 'maguinhomast2005@gmail.com', '$2y$10$Tj8bn.VURqaA404emnRkkOl/s9GwYpfUcRO6bktWKrf9GVnvf8GDa', 2147483647);

-- --------------------------------------------------------

--
-- Estrutura da tabela `adm_sessions`
--

CREATE TABLE `adm_sessions` (
  `session_id` varchar(128) NOT NULL,
  `adm_id` int(11) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `adm_sessions`
--

INSERT INTO `adm_sessions` (`session_id`, `adm_id`, `user_agent`, `ip_address`, `last_activity`) VALUES
('q6loca3uqtv1bqfscb58j1m02r', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '::1', '2025-05-03 13:33:26');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ausencias`
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
-- Estrutura da tabela `beneficios`
--

CREATE TABLE `beneficios` (
  `id_beneficio` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `fun_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `documentos`
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
-- Estrutura da tabela `empresa`
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
-- Extraindo dados da tabela `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nome`, `nipc`, `endereco`, `email_corp`, `telefone`, `setor_atuacao`, `num_fun`, `data_cadastro`, `adm_id`) VALUES
(2, 'Diogo Oliveira', '43653467', 'Mutamba', 'SAM@gmail.com', '922608606', 'saude', 1, '2025-05-03', 2);

--
-- Acionadores `empresa`
--
DELIMITER $$
CREATE TRIGGER `delete_empresa_app` AFTER DELETE ON `empresa` FOR EACH ROW BEGIN    
    -- Excluir a empresa correspondente no app_empresas
    DELETE FROM `app_empresas`.`empresas` WHERE `site_empresa_id` = OLD.id_empresa;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `falta`
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
-- Estrutura da tabela `funcionario`
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
-- Extraindo dados da tabela `funcionario`
--

INSERT INTO `funcionario` (`id_fun`, `num_mecanografico`, `nome`, `foto`, `bi`, `emissao_bi`, `validade_bi`, `data_nascimento`, `pais`, `morada`, `genero`, `num_agregados`, `contato_emergencia`, `nome_contato_emergencia`, `telemovel`, `email`, `estado`, `cargo`, `departamento`, `tipo_trabalhador`, `num_conta_bancaria`, `banco`, `iban`, `salario_base`, `num_ss`, `data_admissao`, `empresa_id`, `status`) VALUES
(1, 'EMP-0001', 'Silvestre Luís', NULL, '234234', '2005-12-12', '2025-12-12', '2000-12-12', 'estados_unidos', 'Mutamba', '', 12, '922608606', 'Jorge', '922456789', 'sam@gmail.com', 'Ativo', 'Engenheiro Civil', 'rh', 'Efetivo', '99999', 'BAI', '123', 250.00, '123', '2025-05-03', 2, 'pendente_biometria');

--
-- Acionadores `funcionario`
--
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

-- --------------------------------------------------------

--
-- Estrutura da tabela `redefinicao_senha`
--

CREATE TABLE `redefinicao_senha` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `data_expiracao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `adm`
--
ALTER TABLE `adm`
  ADD PRIMARY KEY (`id_adm`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- Índices para tabela `adm_sessions`
--
ALTER TABLE `adm_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `adm_id` (`adm_id`);

--
-- Índices para tabela `ausencias`
--
ALTER TABLE `ausencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices para tabela `beneficios`
--
ALTER TABLE `beneficios`
  ADD PRIMARY KEY (`id_beneficio`),
  ADD KEY `fk_beneficios_funcionario1_idx` (`fun_id`);

--
-- Índices para tabela `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `num_funcionario` (`num_funcionario`);

--
-- Índices para tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`,`adm_id`),
  ADD UNIQUE KEY `nipc_UNIQUE` (`nipc`),
  ADD KEY `fk_empresa_adm_idx` (`adm_id`);

--
-- Índices para tabela `falta`
--
ALTER TABLE `falta`
  ADD PRIMARY KEY (`id_falta`),
  ADD KEY `fk_falta_funcionario1_idx` (`fun_id`);

--
-- Índices para tabela `funcionario`
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
-- Índices para tabela `redefinicao_senha`
--
ALTER TABLE `redefinicao_senha`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `adm`
--
ALTER TABLE `adm`
  MODIFY `id_adm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `ausencias`
--
ALTER TABLE `ausencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `beneficios`
--
ALTER TABLE `beneficios`
  MODIFY `id_beneficio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `falta`
--
ALTER TABLE `falta`
  MODIFY `id_falta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id_fun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `redefinicao_senha`
--
ALTER TABLE `redefinicao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `adm_sessions`
--
ALTER TABLE `adm_sessions`
  ADD CONSTRAINT `adm_sessions_ibfk_1` FOREIGN KEY (`adm_id`) REFERENCES `adm` (`id_adm`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `ausencias`
--
ALTER TABLE `ausencias`
  ADD CONSTRAINT `ausencias_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id_fun`),
  ADD CONSTRAINT `ausencias_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`);

--
-- Limitadores para a tabela `beneficios`
--
ALTER TABLE `beneficios`
  ADD CONSTRAINT `beneficios_ibfk_1` FOREIGN KEY (`fun_id`) REFERENCES `funcionario` (`id_fun`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`num_funcionario`) REFERENCES `funcionario` (`id_fun`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `fk_empresa_adm` FOREIGN KEY (`adm_id`) REFERENCES `adm` (`id_adm`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `falta`
--
ALTER TABLE `falta`
  ADD CONSTRAINT `falta_ibfk_1` FOREIGN KEY (`fun_id`) REFERENCES `funcionario` (`id_fun`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id_empresa`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
