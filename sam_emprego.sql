-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15-Maio-2025 às 20:30
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
-- Banco de dados: `sam_emprego`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `candidatos`
--

CREATE TABLE `candidatos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `cv_anexo` varchar(255) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `candidaturas`
--

CREATE TABLE `candidaturas` (
  `id` int(11) NOT NULL,
  `candidato_id` int(11) NOT NULL,
  `vaga_id` int(11) NOT NULL,
  `data_candidatura` datetime DEFAULT current_timestamp(),
  `status` enum('Recebida','Em análise','Entrevista','Aprovada','Rejeitada') DEFAULT 'Recebida',
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `empresas_recrutamento`
--

CREATE TABLE `empresas_recrutamento` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `site_empresa_id` int(11) NOT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `vagas`
--

CREATE TABLE `vagas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text NOT NULL,
  `requisitos` text DEFAULT NULL,
  `salario_min` decimal(10,2) DEFAULT NULL,
  `salario_max` decimal(10,2) DEFAULT NULL,
  `tipo_contrato` varchar(50) DEFAULT NULL,
  `empresa_id` int(11) NOT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `data_publicacao` datetime DEFAULT current_timestamp(),
  `data_expiracao` date DEFAULT NULL,
  `status` enum('Aberta','Fechada','Cancelada') DEFAULT 'Aberta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `candidatos`
--
ALTER TABLE `candidatos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `candidaturas`
--
ALTER TABLE `candidaturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `candidato_id` (`candidato_id`,`vaga_id`),
  ADD KEY `vaga_id` (`vaga_id`);

--
-- Índices para tabela `empresas_recrutamento`
--
ALTER TABLE `empresas_recrutamento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `site_empresa_id` (`site_empresa_id`);

--
-- Índices para tabela `vagas`
--
ALTER TABLE `vagas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `candidatos`
--
ALTER TABLE `candidatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `candidaturas`
--
ALTER TABLE `candidaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresas_recrutamento`
--
ALTER TABLE `empresas_recrutamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vagas`
--
ALTER TABLE `vagas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `candidaturas`
--
ALTER TABLE `candidaturas`
  ADD CONSTRAINT `candidaturas_ibfk_1` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`),
  ADD CONSTRAINT `candidaturas_ibfk_2` FOREIGN KEY (`vaga_id`) REFERENCES `vagas` (`id`);

--
-- Limitadores para a tabela `vagas`
--
ALTER TABLE `vagas`
  ADD CONSTRAINT `vagas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas_recrutamento` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
