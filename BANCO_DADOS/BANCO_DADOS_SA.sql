-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/08/2025 às 19:52
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `king_mel`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `apiario`
--

CREATE TABLE `apiario` (
  `Nome_apiario` varchar(100) NOT NULL,
  `id_apiario` int(11) NOT NULL,
  `CNPJ` varchar(20) NOT NULL,
  `Quantidade` int(11) DEFAULT NULL,
  `Data_inicio` date DEFAULT NULL,
  `Endereco` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `apiario`
--

INSERT INTO `apiario` (`Nome_apiario`, `id_apiario`, `CNPJ`, `Quantidade`, `Data_inicio`, `Endereco`) VALUES
('Apiário Doce Vida', 3, '12.345.678/0001-90', 200, '2020-05-10', 'Estrada Rural, nº 123 - Interior'),
('Apiário Flor de Mel', 4, '98.765.432/0001-55', 150, '2021-03-22', 'Sítio das Abelhas, nº 45 - Colônia');

-- --------------------------------------------------------

--
-- Estrutura para tabela `apiario_produto`
--

CREATE TABLE `apiario_produto` (
  `id_apiario` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `Nome` varchar(150) NOT NULL,
  `CPF` varchar(14) NOT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Data_nascimento` date DEFAULT NULL,
  `Endereco` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `Nome`, `CPF`, `Telefone`, `Email`, `Data_nascimento`, `Endereco`) VALUES
(1, 'João Silva', '111.222.333-44', '47999999999', 'joao.silva@email.com', '1990-06-15', 'Rua das Flores, 45'),
(2, 'Maria Oliveira', '555.666.777-88', '47988888888', 'maria.oliveira@email.com', '1985-09-25', 'Av. Central, 100'),
(3, 'Carlos Pereira', '999.000.111-22', '47977777777', 'carlos.pereira@email.com', '2000-01-10', 'Rua do Comércio, 12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `compra`
--

CREATE TABLE `compra` (
  `id_compra` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `Data_compra` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `compra`
--

INSERT INTO `compra` (`id_compra`, `id_produto`, `id_funcionario`, `Data_compra`) VALUES
(1, 1, 2, '2025-08-10'),
(2, 3, 2, '2025-08-15'),
(3, 2, 1, '2025-08-20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `id_funcionario` int(11) NOT NULL,
  `Nome` varchar(150) NOT NULL,
  `CPF` varchar(14) NOT NULL,
  `Data_contratacao` date DEFAULT NULL,
  `Cargo` varchar(100) DEFAULT NULL,
  `Salario` decimal(10,2) DEFAULT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionario`
--

INSERT INTO `funcionario` (`id_funcionario`, `Nome`, `CPF`, `Data_contratacao`, `Cargo`, `Salario`, `Telefone`, `Email`) VALUES
(1, 'Fernanda Costa', '222.333.444-55', '2023-01-10', 'Atendente', 2500.00, '47966666666', 'fernanda.costa@empresa.com'),
(2, 'Rafael Gomes', '333.444.555-66', '2022-11-05', 'Estoquista', 2700.00, '47955555555', 'rafael.gomes@empresa.com');

-- --------------------------------------------------------

--
-- Estrutura para tabela `item_pedido`
--

CREATE TABLE `item_pedido` (
  `id_item` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `qtd_produto` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `id_pedido` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `item_pedido`
--

INSERT INTO `item_pedido` (`id_item`, `id_produto`, `qtd_produto`, `preco_unitario`, `id_pedido`) VALUES
(1, 1, 2, 30.00, 1),
(2, 2, 1, 18.00, 2),
(3, 3, 1, 40.00, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `Numero_pedido` varchar(50) NOT NULL,
  `Preco` decimal(10,2) DEFAULT NULL,
  `Id_cliente` int(11) NOT NULL,
  `Local_entrega` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `Numero_pedido`, `Preco`, `Id_cliente`, `Local_entrega`) VALUES
(1, 'PED-001', 60.00, 1, 'Rua das Flores, 45'),
(2, 'PED-002', 40.00, 2, 'Av. Central, 100');

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfil`
--

CREATE TABLE `perfil` (
  `id_perfil` int(11) NOT NULL,
  `nome_perfil` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `perfil`
--

INSERT INTO `perfil` (`id_perfil`, `nome_perfil`) VALUES
(1, 'Adm'),
(3, 'Almoxarife'),
(4, 'Cliente'),
(2, 'Secretaria');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

CREATE TABLE `produto` (
  `id_produto` int(11) NOT NULL,
  `Tipo_mel` varchar(100) DEFAULT NULL,
  `Data_embalado` date DEFAULT NULL,
  `Peso` decimal(10,2) DEFAULT NULL,
  `Preco` decimal(10,2) DEFAULT NULL,
  `Quantidade` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produto`
--

INSERT INTO `produto` (`id_produto`, `Tipo_mel`, `Data_embalado`, `Peso`, `Preco`, `Quantidade`) VALUES
(1, 'Mel Silvestre', '2025-08-01', 1.00, 30.00, 50),
(2, 'Mel de Eucalipto', '2025-07-20', 0.50, 18.00, 100),
(3, 'Própolis Verde', '2025-08-15', 0.10, 40.00, 30);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_perfil` int(11) DEFAULT NULL,
  `senha_temporaria` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome`, `senha`, `email`, `id_perfil`, `senha_temporaria`) VALUES
(1, 'Administrador', '$2y$10$rIJhd7oXSRM1XbAdQCEsA.PF3n/rxNtIAUqCkcFybzE5J.mLBsq.q', 'admin@admin', 1, 0),
(2, 'Sergio Luiz da Silveira', '$2y$10$AKaq2b1ZyNzZs5u6ueiJq.t5xj02aj0aroz4IjHDPhdAsrhZL8MO.', 'sergio@sergio', 1, 1),
(6, 'Maria Souza', '$2y$10$RRDyLe.N/SHniQ03fG3mnuRN84K/D4wVS3BkftU7nUUFEqyOhwFDu', 'maria@empresa.com', 2, 0),
(7, 'Carlos Mendes', '$2y$10$RRDyLe.N/SHniQ03fG3mnuRN84K/D4wVS3BkftU7nUUFEqyOhwFDu', 'carlos@empresa.com', 3, 0),
(8, 'Ana Pereira', '$2y$10$xaWdXzOzYETic/DhbeHV2OZCAgBaOJzqo9j38DeAEKV2.grcV.L3u', 'ana@empresa.com', 4, 0),
(9, 'Joao Vitor', '$2y$10$2nzDym9SuKZba3OcGeUWKu7RB3CRhpVb1v.LXb9kYxBWVh1/dAG22', 'vitor@vitor', 1, 0),
(12, 'Grace Van Pelt', '$2y$10$g5h1LI20ufnY/p6062h5r.ezKU7eFlhhwRCSkuKTJiYUYulPIQjxq', 'grace@grace', 4, 0),
(13, 'Xavier', '$2y$10$ErMocH1x.avm4asmRnKzeOUF30fi4ZO33C/9H6D2opvlFZ6zEorR.', 'xavier@xavier', 1, 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `apiario`
--
ALTER TABLE `apiario`
  ADD PRIMARY KEY (`id_apiario`),
  ADD UNIQUE KEY `CNPJ` (`CNPJ`);

--
-- Índices de tabela `apiario_produto`
--
ALTER TABLE `apiario_produto`
  ADD PRIMARY KEY (`id_apiario`,`id_produto`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `CPF` (`CPF`);

--
-- Índices de tabela `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `id_produto` (`id_produto`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices de tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id_funcionario`),
  ADD UNIQUE KEY `CPF` (`CPF`);

--
-- Índices de tabela `item_pedido`
--
ALTER TABLE `item_pedido`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `id_produto` (`id_produto`),
  ADD KEY `id_pedido` (`id_pedido`);

--
-- Índices de tabela `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD UNIQUE KEY `Numero_pedido` (`Numero_pedido`),
  ADD KEY `Id_cliente` (`Id_cliente`);

--
-- Índices de tabela `perfil`
--
ALTER TABLE `perfil`
  ADD PRIMARY KEY (`id_perfil`),
  ADD UNIQUE KEY `nome_perfil` (`nome_perfil`);

--
-- Índices de tabela `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`id_produto`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_perfil` (`id_perfil`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `apiario`
--
ALTER TABLE `apiario`
  MODIFY `id_apiario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `compra`
--
ALTER TABLE `compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `item_pedido`
--
ALTER TABLE `item_pedido`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `perfil`
--
ALTER TABLE `perfil`
  MODIFY `id_perfil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id_produto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `apiario_produto`
--
ALTER TABLE `apiario_produto`
  ADD CONSTRAINT `apiario_produto_ibfk_1` FOREIGN KEY (`id_apiario`) REFERENCES `apiario` (`id_apiario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `apiario_produto_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `item_pedido`
--
ALTER TABLE `item_pedido`
  ADD CONSTRAINT `item_pedido_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_pedido_ibfk_2` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`Id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
