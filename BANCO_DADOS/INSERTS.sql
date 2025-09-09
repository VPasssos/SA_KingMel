-- Inserir perfis
INSERT INTO `perfil` (`nome_perfil`) VALUES 
('Administrador'),
('Funcionario'),
('Cliente');

-- Inserir usuários
INSERT INTO `usuario` (`nome`, `senha`, `email`, `id_perfil`, `senha_temporaria`) VALUES 
('Admin Master', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kingmel.com', 1, 0),
('João Silva', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'joao.silva@kingmel.com', 2, 0),
('Maria Santos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maria.santos@kingmel.com', 2, 0),
('Carlos Oliveira', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'carlos.oliveira@email.com', 3, 0),
('Ana Costa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ana.costa@email.com', 3, 0);

-- Inserir apiários
INSERT INTO `apiario` (`Nome_apiario`, `CNPJ`, `Quantidade`, `Data_inicio`, `Endereco`) VALUES 
('Apiário Flor do Campo', '12.345.678/0001-90', 50, '2020-03-15', 'Rodovia BR-101, Km 25, Campos Verdes - SP'),
('Apiário Mel Dourado', '98.765.432/0001-10', 35, '2021-05-20', 'Estrada Municipal, S/N, Zona Rural - MG'),
('Apiário Abelhas Rainhas', '55.444.333/0001-22', 60, '2019-11-10', 'Fazenda Esperança, Rodovia MS-156, Km 45 - MS');

-- Inserir produtos
INSERT INTO `produto` (`Tipo_mel`, `Data_embalado`, `Peso`, `Preco`, `Quantidade`, `nome_foto`, `tipo_foto`, 'foto') VALUES 
('Mel de Eucalipto', '2024-01-15', 500.00, 25.90, 100, 'mel_eucalipto.jpg', 'image/jpeg'),
('Mel Silvestre', '2024-01-20', 500.00, 22.50, 150, 'mel_silvestre.jpg', 'image/jpeg'),
('Mel de Laranjeira', '2024-01-18', 500.00, 28.75, 80, 'mel_laranjeira.jpg', 'image/jpeg'),
('Mel de Assa-peixe', '2024-01-22', 500.00, 30.00, 60, 'mel_assapeixe.jpg', 'image/jpeg'),
('Mel de Jataí', '2024-01-25', 250.00, 35.90, 40, 'mel_jatai.jpg', 'image/jpeg');

-- Relacionar apiários com produtos
INSERT INTO `apiario_produto` (`id_apiario`, `id_produto`) VALUES 
(1, 1),
(1, 2),
(2, 3),
(2, 4),
(3, 5),
(3, 1);

-- Inserir funcionários
INSERT INTO `funcionario` (`Nome`, `CPF`, `Data_contratacao`, `Cargo`, `Salario`, `Telefone`, `Email`, `nome_foto`, `tipo_foto`) VALUES 
('Pedro Almeida', '123.456.789-00', '2020-02-10', 'Apicultor', 2500.00, '(11) 99999-8888', 'pedro.almeida@kingmel.com', 'pedro.jpg', 'image/jpeg'),
('Fernanda Lima', '987.654.321-00', '2021-03-15', 'Administrativo', 3200.00, '(11) 98888-7777', 'fernanda.lima@kingmel.com', 'fernanda.jpg', 'image/jpeg'),
('Ricardo Souza', '456.789.123-00', '2022-01-20', 'Vendedor', 2800.00, '(11) 97777-6666', 'ricardo.souza@kingmel.com', 'ricardo.jpg', 'image/jpeg');

-- Inserir clientes
INSERT INTO `cliente` (`Nome`, `CPF`, `Telefone`, `Email`, `Data_nascimento`, `Endereco`) VALUES 
('Carlos Oliveira', '111.222.333-44', '(11) 95555-4444', 'carlos.oliveira@email.com', '1985-07-12', 'Rua das Flores, 123 - São Paulo/SP'),
('Ana Costa', '222.333.444-55', '(11) 94444-3333', 'ana.costa@email.com', '1990-12-25', 'Av. Principal, 456 - Campinas/SP'),
('Paulo Mendes', '333.444.555-66', '(11) 93333-2222', 'paulo.mendes@email.com', '1978-03-30', 'Rua dos Coqueiros, 789 - Santos/SP'),
('Juliana Ramos', '444.555.666-77', '(11) 92222-1111', 'juliana.ramos@email.com', '1992-08-15', 'Alameda Santos, 1010 - São Paulo/SP');

-- Inserir pedidos
INSERT INTO `pedido` (`Numero_pedido`, `Preco`, `Id_cliente`, `Local_entrega`, `Data_pedido`) VALUES 
('PED2024001', 78.70, 1, 'Rua das Flores, 123 - São Paulo/SP', '2024-01-10 14:30:00'),
('PED2024002', 45.00, 2, 'Av. Principal, 456 - Campinas/SP', '2024-01-12 10:15:00'),
('PED2024003', 115.60, 3, 'Rua dos Coqueiros, 789 - Santos/SP', '2024-01-15 16:45:00');

-- Inserir itens dos pedidos
INSERT INTO `item_pedido` (`id_produto`, `qtd_produto`, `preco_unitario`, `id_pedido`) VALUES 
(1, 2, 25.90, 1),
(2, 1, 22.50, 1),
(3, 1, 28.75, 2),
(4, 2, 30.00, 3),
(5, 1, 35.90, 3);

-- Inserir compras (compras de insumos pelos funcionários)
INSERT INTO `compra` (`id_produto`, `id_funcionario`, `Data_compra`, `Quantidade`, `Preco_total`) VALUES 
(1, 1, '2024-01-05', 50, 1295.00),
(2, 2, '2024-01-08', 30, 675.00),
(3, 3, '2024-01-12', 20, 575.00);

-- Inserir carrinho de compras
INSERT INTO `carrinho` (`id_produto`, `qtd_produto`, `preco_unitario`, `id_apiario`, `id_usuario`) VALUES 
(1, 2, 25.90, 1, 4),
(3, 1, 28.75, 2, 4),
(5, 1, 35.90, 3, 5);

-- Inserir compras do carrinho
INSERT INTO `compra_carrinho` (`id_usuario`, `preco_total`, `status`) VALUES 
(4, 80.55, 'pago'),
(5, 35.90, 'pendente');

-- Inserir produtos das compras do carrinho
INSERT INTO `compra_carrinho_produto` (`id_compra_carrinho`, `id_produto`, `qtd_produto`, `preco_unitario`) VALUES 
(1, 1, 2, 25.90),
(1, 3, 1, 28.75),
(2, 5, 1, 35.90);