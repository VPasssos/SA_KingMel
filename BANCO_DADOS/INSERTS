-- Estrutura do banco `sa_kingmel` ajustada para UTF-8 completo

CREATE DATABASE IF NOT EXISTS `sa_kingmel`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `sa_kingmel`;

-- Tabela: apiario
CREATE TABLE `apiario` (
  `id_apiario` INT(11) NOT NULL AUTO_INCREMENT,
  `Nome_apiario` VARCHAR(100) NOT NULL,
  `CNPJ` VARCHAR(20) NOT NULL,
  `Quantidade` INT(11) DEFAULT NULL,
  `Data_inicio` DATE DEFAULT NULL,
  `Endereco` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_apiario`),
  UNIQUE KEY `CNPJ` (`CNPJ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: apiario_produto
CREATE TABLE `apiario_produto` (
  `id_apiario` INT(11) NOT NULL,
  `id_produto` INT(11) NOT NULL,
  PRIMARY KEY (`id_apiario`, `id_produto`),
  KEY `id_produto` (`id_produto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: carrinho
CREATE TABLE `carrinho` (
  `id_carrinho` INT(11) NOT NULL AUTO_INCREMENT,
  `id_produto` INT(11) DEFAULT NULL,
  `qtd_produto` INT(11) DEFAULT NULL,
  `preco_unitario` DECIMAL(10,2) DEFAULT NULL,
  `id_apiario` INT(11) DEFAULT NULL,
  `id_usuario` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id_carrinho`),
  KEY `fk_carrinho_produto` (`id_produto`),
  KEY `fk_carrinho_apiario` (`id_apiario`),
  KEY `fk_carrinho_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: cliente
CREATE TABLE `cliente` (
  `id_cliente` INT(11) NOT NULL AUTO_INCREMENT,
  `Nome` VARCHAR(150) NOT NULL,
  `CPF` VARCHAR(14) NOT NULL,
  `Telefone` VARCHAR(20) DEFAULT NULL,
  `Email` VARCHAR(100) DEFAULT NULL,
  `Data_nascimento` DATE DEFAULT NULL,
  `Endereco` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `CPF` (`CPF`),
  KEY `idx_cliente_nome` (`Nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: compra
CREATE TABLE `compra` (
  `id_compra` INT(11) NOT NULL AUTO_INCREMENT,
  `id_produto` INT(11) NOT NULL,
  `id_funcionario` INT(11) NOT NULL,
  `Data_compra` DATE NOT NULL,
  `Quantidade` INT(11) NOT NULL,
  `Preco_total` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id_compra`),
  KEY `id_produto` (`id_produto`),
  KEY `id_funcionario` (`id_funcionario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: compra_carrinho
CREATE TABLE `compra_carrinho` (
  `id_compra_carrinho` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `data_compra` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `preco_total` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pendente','pago','enviado','cancelado') DEFAULT 'pendente',
  PRIMARY KEY (`id_compra_carrinho`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: compra_carrinho_produto
CREATE TABLE `compra_carrinho_produto` (
  `id_compra_carinho_produto` INT(11) NOT NULL AUTO_INCREMENT,
  `id_compra_carrinho` INT(11) NOT NULL,
  `id_produto` INT(11) NOT NULL,
  `qtd_produto` INT(11) NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id_compra_carinho_produto`),
  KEY `id_compra_carrinho` (`id_compra_carrinho`),
  KEY `id_produto` (`id_produto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: funcionario
CREATE TABLE `funcionario` (
  `id_funcionario` INT(11) NOT NULL AUTO_INCREMENT,
  `Nome` VARCHAR(150) NOT NULL,
  `CPF` VARCHAR(14) NOT NULL,
  `Data_contratacao` DATE DEFAULT NULL,
  `Cargo` VARCHAR(100) DEFAULT NULL,
  `Salario` DECIMAL(10,2) DEFAULT NULL,
  `Telefone` VARCHAR(20) DEFAULT NULL,
  `Email` VARCHAR(100) DEFAULT NULL,
  `nome_foto` VARCHAR(255) NOT NULL,
  `tipo_foto` VARCHAR(255) NOT NULL,
  `foto` LONGBLOB DEFAULT NULL,
  PRIMARY KEY (`id_funcionario`),
  UNIQUE KEY `CPF` (`CPF`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: item_pedido
CREATE TABLE `item_pedido` (
  `id_item` INT(11) NOT NULL AUTO_INCREMENT,
  `id_produto` INT(11) NOT NULL,
  `qtd_produto` INT(11) NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  `id_pedido` INT(11) NOT NULL,
  PRIMARY KEY (`id_item`),
  KEY `idx_item_pedido_produto` (`id_produto`),
  KEY `idx_item_pedido_pedido` (`id_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: pedido
CREATE TABLE `pedido` (
  `id_pedido` INT(11) NOT NULL AUTO_INCREMENT,
  `Numero_pedido` VARCHAR(50) NOT NULL,
  `Preco` DECIMAL(10,2) DEFAULT NULL,
  `Id_cliente` INT(11) NOT NULL,
  `Local_entrega` VARCHAR(255) DEFAULT NULL,
  `Data_pedido` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_pedido`),
  UNIQUE KEY `Numero_pedido` (`Numero_pedido`),
  KEY `idx_pedido_cliente` (`Id_cliente`),
  KEY `idx_pedido_data` (`Data_pedido`),
  KEY `idx_pedido_numero` (`Numero_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: perfil
CREATE TABLE `perfil` (
  `id_perfil` INT(11) NOT NULL AUTO_INCREMENT,
  `nome_perfil` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_perfil`),
  UNIQUE KEY `nome_perfil` (`nome_perfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: produto
CREATE TABLE `produto` (
  `id_produto` INT(11) NOT NULL AUTO_INCREMENT,
  `Tipo_mel` VARCHAR(100) DEFAULT NULL,
  `Data_embalado` DATE DEFAULT NULL,
  `Peso` DECIMAL(10,2) DEFAULT NULL,
  `Preco` DECIMAL(10,2) DEFAULT NULL,
  `Quantidade` INT(11) DEFAULT NULL,
  `nome_foto` VARCHAR(255) NOT NULL,
  `tipo_foto` VARCHAR(255) NOT NULL,
  `foto` LONGBLOB DEFAULT NULL,
  PRIMARY KEY (`id_produto`),
  KEY `idx_produto_quantidade` (`Quantidade`),
  KEY `idx_produto_tipo` (`Tipo_mel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: usuario
CREATE TABLE `usuario` (
  `id_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `id_perfil` INT(11) DEFAULT NULL,
  `senha_temporaria` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_usuario_perfil` (`id_perfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Definindo chaves estrangeiras relacionando as tabelas

ALTER TABLE `apiario_produto`
  ADD CONSTRAINT `apiario_produto_ibfk_1` FOREIGN KEY (`id_apiario`) REFERENCES `apiario` (`id_apiario`) ON DELETE CASCADE,
  ADD CONSTRAINT `apiario_produto_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE;

ALTER TABLE `carrinho`
  ADD CONSTRAINT `fk_carrinho_apiario` FOREIGN KEY (`id_apiario`) REFERENCES `apiario` (`id_apiario`),
  ADD CONSTRAINT `fk_carrinho_produto` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`),
  ADD CONSTRAINT `fk_carrinho_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE,
  ADD CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`) ON DELETE CASCADE;

ALTER TABLE `compra_carrinho`
  ADD CONSTRAINT `compra_carrinho_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

ALTER TABLE `compra_carrinho_produto`
  ADD CONSTRAINT `compra_carrinho_produto_ibfk_1` FOREIGN KEY (`id_compra_carrinho`) REFERENCES `compra_carrinho` (`id_compra_carrinho`),
  ADD CONSTRAINT `compra_carrinho_produto_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`);

ALTER TABLE `item_pedido`
  ADD CONSTRAINT `item_pedido_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_pedido_ibfk_2` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE;

ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`Id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE;

ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `perfil` (`id_perfil`) ON DELETE SET NULL;
            
