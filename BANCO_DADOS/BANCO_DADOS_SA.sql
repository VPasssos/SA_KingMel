-- Banco de dados: king_mel
CREATE DATABASE IF NOT EXISTS king_mel;
USE king_mel;

-- Tabela de perfis de usuário
CREATE TABLE perfil (
    id_perfil INT(11) NOT NULL AUTO_INCREMENT,
    nome_perfil VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_perfil),
    UNIQUE KEY (nome_perfil)
);

-- Tabela de usuários
CREATE TABLE usuario (
    id_usuario INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    id_perfil INT(11) DEFAULT NULL,
    senha_temporaria TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY (email),
    FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil) ON DELETE SET NULL
);

-- Tabela de clientes
CREATE TABLE cliente (
    id_cliente INT(11) NOT NULL AUTO_INCREMENT,
    Nome VARCHAR(150) NOT NULL,
    CPF VARCHAR(14) NOT NULL,
    Telefone VARCHAR(20) DEFAULT NULL,
    Email VARCHAR(100) DEFAULT NULL,
    Data_nascimento DATE DEFAULT NULL,
    Endereco VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id_cliente),
    UNIQUE KEY (CPF)
);

-- Tabela de produtos
CREATE TABLE produto (
    id_produto INT(11) NOT NULL AUTO_INCREMENT,
    Tipo_mel VARCHAR(100) DEFAULT NULL,
    Data_embalado DATE DEFAULT NULL,
    Peso DECIMAL(10,2) DEFAULT NULL,
    Preco DECIMAL(10,2) DEFAULT NULL,
    Quantidade INT(11) DEFAULT NULL,
    PRIMARY KEY (id_produto)
);

-- Tabela de pedidos
CREATE TABLE pedido (
    id_pedido INT(11) NOT NULL AUTO_INCREMENT,
    Numero_pedido VARCHAR(50) NOT NULL,
    Preco DECIMAL(10,2) DEFAULT NULL,
    Id_cliente INT(11) NOT NULL,
    Local_entrega VARCHAR(255) DEFAULT NULL,
    Data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pedido),
    UNIQUE KEY (Numero_pedido),
    FOREIGN KEY (Id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE
);

-- Tabela de itens do pedido
CREATE TABLE item_pedido (
    id_item INT(11) NOT NULL AUTO_INCREMENT,
    id_produto INT(11) NOT NULL,
    qtd_produto INT(11) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    id_pedido INT(11) NOT NULL,
    PRIMARY KEY (id_item),
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE
);

-- Tabela de apiários
CREATE TABLE apiario (
    id_apiario INT(11) NOT NULL AUTO_INCREMENT,
    Nome_apiario VARCHAR(100) NOT NULL,
    CNPJ VARCHAR(20) NOT NULL,
    Quantidade INT(11) DEFAULT NULL,
    Data_inicio DATE DEFAULT NULL,
    Endereco VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id_apiario),
    UNIQUE KEY (CNPJ)
);

-- Tabela de relacionamento apiário-produto
CREATE TABLE apiario_produto (
    id_apiario INT(11) NOT NULL,
    id_produto INT(11) NOT NULL,
    PRIMARY KEY (id_apiario, id_produto),
    FOREIGN KEY (id_apiario) REFERENCES apiario(id_apiario) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto) ON DELETE CASCADE
);

-- Tabela de funcionários
CREATE TABLE funcionario (
    id_funcionario INT(11) NOT NULL AUTO_INCREMENT,
    Nome VARCHAR(150) NOT NULL,
    CPF VARCHAR(14) NOT NULL,
    Data_contratacao DATE DEFAULT NULL,
    Cargo VARCHAR(100) DEFAULT NULL,
    Salario DECIMAL(10,2) DEFAULT NULL,
    Telefone VARCHAR(20) DEFAULT NULL,
    Email VARCHAR(100) DEFAULT NULL,
    PRIMARY KEY (id_funcionario),
    UNIQUE KEY (CPF)
);

-- Tabela de compras
CREATE TABLE compra (
    id_compra INT(11) NOT NULL AUTO_INCREMENT,
    id_produto INT(11) NOT NULL,
    id_funcionario INT(11) NOT NULL,
    Data_compra DATE NOT NULL,
    Quantidade INT(11) NOT NULL,
    Preco_total DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_compra),
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario) ON DELETE CASCADE
);

-- Inserir dados iniciais de perfis
INSERT INTO perfil (id_perfil, nome_perfil) VALUES
(1, 'Adm'),
(2, 'Secretaria'),
(3, 'Almoxarife'),
(4, 'Cliente');

-- Inserir usuário administrador padrão (senha: admin123)
INSERT INTO usuario (id_usuario, nome, senha, email, id_perfil, senha_temporaria) VALUES
(1, 'Administrador', '$2y$10$rIJhd7oXSRM1XbAdQCEsA.PF3n/rxNtIAUqCkcFybzE5J.mLBsq.q', 'admin@admin', 1, 0);

-- Inserir alguns clientes de exemplo
INSERT INTO cliente (id_cliente, Nome, CPF, Telefone, Email, Data_nascimento, Endereco) VALUES
(1, 'João Silva', '111.222.333-44', '47999999999', 'joao.silva@email.com', '1990-06-15', 'Rua das Flores, 45'),
(2, 'Maria Oliveira', '555.666.777-88', '47988888888', 'maria.oliveira@email.com', '1985-09-25', 'Av. Central, 100'),
(3, 'Carlos Pereira', '999.000.111-22', '47977777777', 'carlos.pereira@email.com', '2000-01-10', 'Rua do Comércio, 12');

-- Inserir alguns produtos de exemplo
INSERT INTO produto (id_produto, Tipo_mel, Data_embalado, Peso, Preco, Quantidade) VALUES
(1, 'Mel Silvestre', '2025-08-01', 1.00, 30.00, 50),
(2, 'Mel de Eucalipto', '2025-07-20', 0.50, 18.00, 100),
(3, 'Própolis Verde', '2025-08-15', 0.10, 40.00, 30),
(4, 'Mel de Laranjeira', '2025-08-10', 0.50, 22.00, 75),
(5, 'Mel de Assa-peixe', '2025-08-05', 1.00, 35.00, 40);

-- Inserir alguns apiários de exemplo
INSERT INTO apiario (id_apiario, Nome_apiario, CNPJ, Quantidade, Data_inicio, Endereco) VALUES
(1, 'Apiário Doce Vida', '12.345.678/0001-90', 200, '2020-05-10', 'Estrada Rural, nº 123 - Interior'),
(2, 'Apiário Flor de Mel', '98.765.432/0001-55', 150, '2021-03-22', 'Sítio das Abelhas, nº 45 - Colônia'),
(3, 'Apiário Rainha', '11.222.333/0001-44', 180, '2019-11-15', 'Rodovia Principal, km 12');

-- Inserir alguns funcionários de exemplo
INSERT INTO funcionario (id_funcionario, Nome, CPF, Data_contratacao, Cargo, Salario, Telefone, Email) VALUES
(1, 'Fernanda Costa', '222.333.444-55', '2023-01-10', 'Atendente', 2500.00, '47966666666', 'fernanda.costa@empresa.com'),
(2, 'Rafael Gomes', '333.444.555-66', '2022-11-05', 'Estoquista', 2700.00, '47955555555', 'rafael.gomes@empresa.com'),
(3, 'Patrícia Santos', '444.555.666-77', '2023-03-20', 'Vendedora', 2800.00, '47944444444', 'patricia.santos@empresa.com');

-- Inserir relacionamentos apiário-produto
INSERT INTO apiario_produto (id_apiario, id_produto) VALUES
(1, 1),
(1, 2),
(2, 3),
(2, 4),
(3, 5);

-- Inserir algumas compras de exemplo
INSERT INTO compra (id_produto, id_funcionario, Data_compra, Quantidade, Preco_total) VALUES
(1, 2, '2025-08-10', 10, 300.00),
(3, 2, '2025-08-15', 5, 200.00),
(2, 1, '2025-08-20', 8, 144.00);

-- Inserir alguns pedidos de exemplo
INSERT INTO pedido (Numero_pedido, Preco, Id_cliente, Local_entrega, Data_pedido) VALUES
('PED-20250801-1001', 60.00, 1, 'Rua das Flores, 45', '2025-08-01 10:30:00'),
('PED-20250801-1002', 40.00, 2, 'Av. Central, 100', '2025-08-01 14:15:00'),
('PED-20250802-1003', 90.00, 3, 'Rua do Comércio, 12', '2025-08-02 09:45:00');

-- Inserir itens dos pedidos
INSERT INTO item_pedido (id_produto, qtd_produto, preco_unitario, id_pedido) VALUES
(1, 2, 30.00, 1),
(2, 1, 18.00, 2),
(3, 1, 40.00, 2),
(1, 3, 30.00, 3);

-- Criar índices para melhor performance
CREATE INDEX idx_usuario_perfil ON usuario(id_perfil);
CREATE INDEX idx_pedido_cliente ON pedido(Id_cliente);
CREATE INDEX idx_pedido_data ON pedido(Data_pedido);
CREATE INDEX idx_item_pedido_produto ON item_pedido(id_produto);
CREATE INDEX idx_item_pedido_pedido ON item_pedido(id_pedido);
CREATE INDEX idx_produto_quantidade ON produto(Quantidade);
CREATE INDEX idx_cliente_nome ON cliente(Nome);
CREATE INDEX idx_produto_tipo ON produto(Tipo_mel);
CREATE INDEX idx_pedido_numero ON pedido(Numero_pedido);

-- Criar usuário para a aplicação (ajuste conforme necessário)
CREATE USER IF NOT EXISTS 'kingmel_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON king_mel.* TO 'kingmel_user'@'localhost';
FLUSH PRIVILEGES;

-- Visualizar a estrutura criada
SHOW TABLES;

-- Verificar os dados inseridos
SELECT 'Perfis:' AS '';
SELECT * FROM perfil;

SELECT 'Usuários:' AS '';
SELECT * FROM usuario;

SELECT 'Clientes:' AS '';
SELECT * FROM cliente;

SELECT 'Produtos:' AS '';
SELECT * FROM produto;

SELECT 'Pedidos:' AS '';
SELECT * FROM pedido;

SELECT 'Itens Pedido:' AS '';
SELECT * FROM item_pedido;