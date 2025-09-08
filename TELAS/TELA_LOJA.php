<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja Virtual - Sistema de Mel</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_LOJA.css">
    </style>
</head>
<body>
        <?php include("MENU.php"); ?>

    <header>
        <h1>Loja de Mel</h1>
        <div class="user-info">Olá, Apicultor</div>
    </header>

    <div class="container">
        <div class="main-content">
            <div class="filtros">
                <input type="text" id="busca-produto" placeholder="Pesquisar produto">
                <select id="filtro-produto">
                    <option value="">Ordenar por</option>
                    <option value="preco_desc">Maior preço</option>
                    <option value="preco_asc">Menor preço</option>
                    <option value="nome_asc">Nome (A-Z)</option>
                    <option value="nome_desc">Nome (Z-A)</option>
                </select>
                <button type="button" id="btn-pesquisar">Pesquisar</button>
            </div>

            <div id="mensagem" class="mensagem"></div>

            <table class="tabela-produtos">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Tipo</th>
                        <th>Peso</th>
                        <th>Preço</th>
                        <th>Apiário</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-produtos-body">
                    <!-- Os produtos serão inseridos aqui via JavaScript -->
                </tbody>
            </table>
        </div>

        <div class="carrinho-lateral">
            <h2>🛒 Meu Carrinho</h2>
            
            <div class="carrinho-itens" id="carrinho-itens">
                <div class="carrinho-vazio">Seu carrinho está vazio</div>
            </div>
            
            <div class="carrinho-total" id="carrinho-total" style="display: none;">
                <span>Total:</span>
                <span id="total-carrinho">R$ 0,00</span>
            </div>
            
            <button class="btn-comprar" id="btn-finalizar-compra" style="display: none;">Finalizar Compra</button>
            <button class="btn-limpar" id="btn-limpar-carrinho" style="display: none;">Limpar Carrinho</button>
        </div>
    </div>

    <!-- Modal para adicionar produto -->
    <div class="modal" id="modal-produto">
        <div class="modal-content">
            <h2>Adicionar ao Carrinho</h2>
            <form id="form-adicionar-produto">
                <input type="hidden" id="produto-id">
                <div>
                    <label>Produto:</label>
                    <input type="text" id="produto-nome" readonly>
                </div>
                <div>
                    <label>Preço unitário:</label>
                    <input type="text" id="produto-preco" readonly>
                </div>
                <div>
                    <label>Quantidade:</label>
                    <input type="number" id="produto-quantidade" min="1" value="1" required>
                </div>
                <div>
                    <button type="submit" class="btn_acao">Adicionar</button>
                    <button type="button" class="btn_acao btn_cancelar" id="btn-cancelar-modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Dados de exemplo para os produtos
        const produtos = [
            { id: 1, nome: "Mel de Eucalipto", tipo: "Eucalipto", peso: "500g", preco: 25.90, apiario: "Apiário Central", imagem: "https://via.placeholder.com/60x60/e0a500/ffffff?text=M" },
            { id: 2, nome: "Mel Silvestre", tipo: "Silvestre", peso: "1kg", preco: 48.50, apiario: "Apiário Norte", imagem: "https://via.placeholder.com/60x60/e0a500/ffffff?text=M" },
            { id: 3, nome: "Mel de Laranjeira", tipo: "Laranjeira", peso: "250g", preco: 15.90, apiario: "Apiário Sul", imagem: "https://via.placeholder.com/60x60/e0a500/ffffff?text=M" },
            { id: 4, nome: "Mel de Assa-peixe", tipo: "Assa-peixe", peso: "500g", preco: 27.50, apiario: "Apiário Oeste", imagem: "https://via.placeholder.com/60x60/e0a500/ffffff?text=M" },
            { id: 5, nome: "Mel de Jataí", tipo: "Jataí", peso: "300g", preco: 32.00, apiario: "Apiário Central", imagem: "https://via.placeholder.com/60x60/e0a500/ffffff?text=M" },
            { id: 6, nome: "Mel de Cipó-uva", tipo: "Cipó-uva", peso: "400g", preco: 29.90, apiario: "Apiário Leste", imagem: "https://via.placeholder.com/60x60/e0a500/ffffff?text=M" }
        ];

        // Carrinho de compras
        let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];

        // Elementos do DOM
        const tabelaProdutos = document.getElementById('tabela-produtos-body');
        const carrinhoItens = document.getElementById('carrinho-itens');
        const carrinhoTotal = document.getElementById('carrinho-total');
        const totalCarrinho = document.getElementById('total-carrinho');
        const btnFinalizar = document.getElementById('btn-finalizar-compra');
        const btnLimpar = document.getElementById('btn-limpar-carrinho');
        const modal = document.getElementById('modal-produto');
        const formAdicionar = document.getElementById('form-adicionar-produto');
        const btnCancelarModal = document.getElementById('btn-cancelar-modal');
        const mensagem = document.getElementById('mensagem');
        const buscaProduto = document.getElementById('busca-produto');
        const filtroProduto = document.getElementById('filtro-produto');
        const btnPesquisar = document.getElementById('btn-pesquisar');

        // Função para exibir mensagens
        function exibirMensagem(texto, tipo) {
            mensagem.textContent = texto;
            mensagem.className = `mensagem ${tipo}`;
            mensagem.style.display = 'block';
            
            setTimeout(() => {
                mensagem.style.display = 'none';
            }, 3000);
        }

        // Função para formatar preço
        function formatarPreco(preco) {
            return `R$ ${preco.toFixed(2).replace('.', ',')}`;
        }

        // Função para renderizar produtos
        function renderizarProdutos(produtosParaRenderizar = produtos) {
            tabelaProdutos.innerHTML = '';
            
            produtosParaRenderizar.forEach(produto => {
                const tr = document.createElement('tr');
                
                tr.innerHTML = `
                    <td><img src="${produto.imagem}" alt="${produto.nome}" class="produto-img"></td>
                    <td>${produto.nome}</td>
                    <td>${produto.peso}</td>
                    <td>${formatarPreco(produto.preco)}</td>
                    <td>${produto.apiario}</td>
                    <td>
                        <button class="btn-adicionar" data-id="${produto.id}">Adicionar</button>
                    </td>
                `;
                
                tabelaProdutos.appendChild(tr);
            });
            
            // Adicionar event listeners aos botões
            document.querySelectorAll('.btn-adicionar').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.target.getAttribute('data-id'));
                    const produto = produtos.find(p => p.id === id);
                    
                    if (produto) {
                        document.getElementById('produto-id').value = produto.id;
                        document.getElementById('produto-nome').value = produto.nome;
                        document.getElementById('produto-preco').value = formatarPreco(produto.preco);
                        document.getElementById('produto-quantidade').value = 1;
                        modal.style.display = 'flex';
                    }
                });
            });
        }

        // Função para atualizar carrinho
        function atualizarCarrinho() {
            carrinhoItens.innerHTML = '';
            
            if (carrinho.length === 0) {
                carrinhoItens.innerHTML = '<div class="carrinho-vazio">Seu carrinho está vazio</div>';
                carrinhoTotal.style.display = 'none';
                btnFinalizar.style.display = 'none';
                btnLimpar.style.display = 'none';
            } else {
                let total = 0;
                
                carrinho.forEach(item => {
                    const produto = produtos.find(p => p.id === item.id);
                    if (produto) {
                        const subtotal = produto.preco * item.quantidade;
                        total += subtotal;
                        
                        const div = document.createElement('div');
                        div.className = 'carrinho-item';
                        div.innerHTML = `
                            <div class="carrinho-item-info">
                                <div class="carrinho-item-nome">${produto.nome}</div>
                                <div class="carrinho-item-preco">${formatarPreco(produto.preco)}</div>
                            </div>
                            <div class="carrinho-item-quantidade">
                                <button onclick="alterarQuantidade(${item.id}, ${item.quantidade - 1})">-</button>
                                <span>${item.quantidade}</span>
                                <button onclick="alterarQuantidade(${item.id}, ${item.quantidade + 1})">+</button>
                            </div>
                        `;
                        
                        carrinhoItens.appendChild(div);
                    }
                });
                
                totalCarrinho.textContent = formatarPreco(total);
                carrinhoTotal.style.display = 'flex';
                btnFinalizar.style.display = 'block';
                btnLimpar.style.display = 'block';
            }
            
            // Salvar carrinho no localStorage
            localStorage.setItem('carrinho', JSON.stringify(carrinho));
        }

        // Função para alterar quantidade
        function alterarQuantidade(id, novaQuantidade) {
            if (novaQuantidade < 1) {
                // Remover item se a quantidade for zero
                carrinho = carrinho.filter(item => item.id !== id);
            } else {
                // Atualizar quantidade
                const item = carrinho.find(item => item.id === id);
                if (item) {
                    item.quantidade = novaQuantidade;
                }
            }
            
            atualizarCarrinho();
        }

        // Função para filtrar produtos
        function filtrarProdutos() {
            const termo = buscaProduto.value.toLowerCase();
            const filtro = filtroProduto.value;
            
            let produtosFiltrados = produtos.filter(produto => 
                produto.nome.toLowerCase().includes(termo) || 
                produto.tipo.toLowerCase().includes(termo) ||
                produto.apiario.toLowerCase().includes(termo)
            );
            
            // Aplicar ordenação
            switch (filtro) {
                case 'preco_desc':
                    produtosFiltrados.sort((a, b) => b.preco - a.preco);
                    break;
                case 'preco_asc':
                    produtosFiltrados.sort((a, b) => a.preco - b.preco);
                    break;
                case 'nome_asc':
                    produtosFiltrados.sort((a, b) => a.nome.localeCompare(b.nome));
                    break;
                case 'nome_desc':
                    produtosFiltrados.sort((a, b) => b.nome.localeCompare(a.nome));
                    break;
            }
            
            renderizarProdutos(produtosFiltrados);
        }

        // Event Listeners
        formAdicionar.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const id = parseInt(document.getElementById('produto-id').value);
            const quantidade = parseInt(document.getElementById('produto-quantidade').value);
            
            // Verificar se o produto já está no carrinho
            const itemExistente = carrinho.find(item => item.id === id);
            
            if (itemExistente) {
                itemExistente.quantidade += quantidade;
            } else {
                carrinho.push({ id, quantidade });
            }
            
            atualizarCarrinho();
            modal.style.display = 'none';
            exibirMensagem('Produto adicionado ao carrinho!', 'sucesso');
        });

        btnCancelarModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        btnFinalizar.addEventListener('click', () => {
            if (carrinho.length > 0) {
                exibirMensagem('Compra finalizada com sucesso!', 'sucesso');
                carrinho = [];
                atualizarCarrinho();
            }
        });

        btnLimpar.addEventListener('click', () => {
            carrinho = [];
            atualizarCarrinho();
            exibirMensagem('Carrinho limpo!', 'sucesso');
        });

        btnPesquisar.addEventListener('click', filtrarProdutos);
        
        buscaProduto.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                filtrarProdutos();
            }
        });

        // Inicializar a página
        renderizarProdutos();
        atualizarCarrinho();
    </script>
</body>
</html>