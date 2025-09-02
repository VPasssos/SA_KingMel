<?php
session_start();
include('../conexao.php'); // Inclui a conexão com o banco de dados

// VERIFICA SE O USUARIO TEM PERMISSÃO
if($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2){
    echo "<script>alert('Acesso Negado'); window.location.href='principal.php';</script>";        
    exit();
}

// Função para buscar clientes
function buscarClientes($pdo, $termo = null) {
    $sql = "SELECT * FROM cliente WHERE 1=1";
    
    if ($termo) {
        $sql .= " AND (Nome LIKE :termo OR Email LIKE :termo OR CPF LIKE :termo OR Endereco LIKE :termo)";
    }

    $sql .= " ORDER BY Nome ASC";

    $stmt = $pdo->prepare($sql);

    if ($termo) {
        $stmt->bindValue(':termo', '%' . $termo . '%');
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para excluir um cliente
if (isset($_GET['excluir'])) {
    $id_cliente = $_GET['excluir'];

    $sql = "DELETE FROM cliente WHERE id_cliente = :id_cliente";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<script>alert('Cliente excluído com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao excluir cliente!');</script>";
    }
    
    header("Location: TELA_GERENCIAR_CLIENTES.php");
    exit();
}

// Função para adicionar um novo cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_cliente'])) {
    $Nome = $_POST['Nome'];
    $CPF = $_POST['CPF'];
    $Telefone = $_POST['Telefone'];
    $Email = $_POST['Email'];
    $Data_nascimento = $_POST['Data_nascimento'];
    $Endereco = $_POST['Endereco'];

    $sql = "INSERT INTO cliente (Nome, CPF, Telefone, Email, Data_nascimento, Endereco) 
            VALUES (:Nome, :CPF, :Telefone, :Email, :Data_nascimento, :Endereco)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':Nome', $Nome);
    $stmt->bindParam(':CPF', $CPF);
    $stmt->bindParam(':Telefone', $Telefone);
    $stmt->bindParam(':Email', $Email);
    $stmt->bindParam(':Data_nascimento', $Data_nascimento);
    $stmt->bindParam(':Endereco', $Endereco);
    
    if ($stmt->execute()) {
        echo "<script>alert('Cliente adicionado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao adicionar cliente!');</script>";
    }
    
    header("Location: TELA_GERENCIAR_CLIENTES.php");
    exit();
}

// Função para alterar um cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_cliente'])) {
    $id_cliente = $_POST['id_cliente'];
    $Nome = $_POST['Nome'];
    $CPF = $_POST['CPF'];
    $Telefone = $_POST['Telefone'];
    $Email = $_POST['Email'];
    $Data_nascimento = $_POST['Data_nascimento'];
    $Endereco = $_POST['Endereco'];

    $sql = "UPDATE cliente SET Nome = :Nome, CPF = :CPF, Telefone = :Telefone, 
            Email = :Email, Data_nascimento = :Data_nascimento, Endereco = :Endereco 
            WHERE id_cliente = :id_cliente";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->bindParam(':Nome', $Nome);
    $stmt->bindParam(':CPF', $CPF);
    $stmt->bindParam(':Telefone', $Telefone);
    $stmt->bindParam(':Email', $Email);
    $stmt->bindParam(':Data_nascimento', $Data_nascimento);
    $stmt->bindParam(':Endereco', $Endereco);
    
    if ($stmt->execute()) {
        echo "<script>alert('Cliente alterado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao alterar cliente!');</script>";
    }
    
    header("Location: TELA_GERENCIAR_CLIENTES.php");
    exit();
}

// Verifica se há um termo de busca
$clientes = isset($_POST['busca']) ? buscarClientes($pdo, $_POST['busca']) : buscarClientes($pdo);

// Buscar cliente para edição se houver ID na URL
$cliente_edicao = null;
if (isset($_GET['editar'])) {
    $id_cliente = $_GET['editar'];
    $sql = "SELECT * FROM cliente WHERE id_cliente = :id_cliente";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $cliente_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GERENCIAR CLIENTES</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_CLIENTES.css">
    <script src="../JS/mascaras.js"></script>

</head>
<body>
    <?php include("MENU.php"); ?>

    <main>
        <h1>GERENCIAR CLIENTES</h1>
        
        <div class="ops_cli">
            <button id="btnAdicionar" onclick="abrirModal('modalAdicionar')">Adicionar Cliente</button>
            <form action="TELA_GERENCIAR_CLIENTES.php" method="POST">
                <input type="text" name="busca" id="busca" placeholder="Pesquisar cliente...">
                <button type="submit">Pesquisar</button>
            </form>
        </div>

        <div class="tabela_cli">
            <?php if (!empty($clientes)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Data Nasc.</th>
                            <th>Endereço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                                <td><?= htmlspecialchars($cliente['Nome']) ?></td>
                                <td><?= htmlspecialchars($cliente['CPF']) ?></td>
                                <td><?= htmlspecialchars($cliente['Telefone']) ?></td>
                                <td><?= htmlspecialchars($cliente['Email']) ?></td>
                                <td><?= htmlspecialchars($cliente['Data_nascimento']) ?></td>
                                <td><?= htmlspecialchars($cliente['Endereco']) ?></td>
                                <td>
                                    <a href="TELA_GERENCIAR_CLIENTES.php?editar=<?= htmlspecialchars($cliente['id_cliente']) ?>">Alterar</a>
                                    <a href="TELA_GERENCIAR_CLIENTES.php?excluir=<?= htmlspecialchars($cliente['id_cliente']) ?>" 
                                       class="excluir" 
                                       onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum cliente encontrado</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal para Adicionar Cliente -->
    <div id="modalAdicionar" class="modal">
        <div class="modal-content">
            <h2>Adicionar Cliente</h2>
            <form method="POST" action="TELA_GERENCIAR_CLIENTES.php">
                <div class="form-row">
                    <div>
                        <label for="Nome">Nome Completo:</label>
                        <input type="text" name="Nome" required onkeypress ="mascara(this, nomeM)">
                    </div>
                    <div>
                        <label for="CPF">CPF:</label>
                        <input type="text" name="CPF" placeholder="000.000.000-00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="Telefone">Telefone:</label>
                        <input type="text" name="Telefone" placeholder="(00) 00000-0000" required>
                    </div>
                    <div>
                        <label for="Email">E-mail:</label>
                        <input type="email" name="Email" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="Data_nascimento">Data de Nascimento:</label>
                        <input type="date" name="Data_nascimento" required>
                    </div>
                </div>

                <label for="Endereco">Endereço Completo:</label>
                <textarea name="Endereco" rows="3" required></textarea>

                <button type="submit" name="adicionar_cliente" class="btn_acao">Adicionar</button>
                <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalAdicionar')">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para Alterar Cliente -->
    <?php if ($cliente_edicao): ?>
    <div id="modalAlterar" class="modal" style="display: flex;">
        <div class="modal-content">
            <h2>Alterar Cliente</h2>
            <form method="POST" action="TELA_GERENCIAR_CLIENTES.php">
                <input type="hidden" name="id_cliente" value="<?= $cliente_edicao['id_cliente'] ?>">
                
                <div class="form-row">
                    <div>
                        <label for="Nome_editar">Nome Completo:</label>
                        <input type="text" name="Nome" id="Nome_editar" value="<?= htmlspecialchars($cliente_edicao['Nome']) ?>" required onkeypress ="mascara(this, nomeM)">
                    </div>
                    <div>
                        <label for="CPF_editar">CPF:</label>
                        <input type="text" name="CPF" id="CPF_editar" value="<?= htmlspecialchars($cliente_edicao['CPF']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="Telefone_editar">Telefone:</label>
                        <input type="text" name="Telefone" id="Telefone_editar" value="<?= htmlspecialchars($cliente_edicao['Telefone']) ?>" required>
                    </div>
                    <div>
                        <label for="Email_editar">E-mail:</label>
                        <input type="email" name="Email" id="Email_editar" value="<?= htmlspecialchars($cliente_edicao['Email']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="Data_nascimento_editar">Data de Nascimento:</label>
                        <input type="date" name="Data_nascimento" id="Data_nascimento_editar" value="<?= htmlspecialchars($cliente_edicao['Data_nascimento']) ?>" required>
                    </div>
                </div>

                <label for="Endereco_editar">Endereço Completo:</label>
                <textarea name="Endereco" id="Endereco_editar" rows="3" required><?= htmlspecialchars($cliente_edicao['Endereco']) ?></textarea>

                <button type="submit" name="alterar_cliente" class="btn_acao">Alterar</button>
                <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalAlterar')">Cancelar</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function abrirModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function fecharModal(id) {
            document.getElementById(id).style.display = 'none';
            window.location.href = 'TELA_GERENCIAR_CLIENTES.php';
        }

        // Fechar modal clicando fora dele
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                window.location.href = 'TELA_GERENCIAR_CLIENTES.php';
            }
        }

        // Se houver parâmetro de edição na URL, abrir o modal de alteração
        <?php if ($cliente_edicao): ?>
            document.addEventListener('DOMContentLoaded', function() {
                abrirModal('modalAlterar');
            });
        <?php endif; ?>

        // Máscaras para os campos
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara para CPF
            const cpfInputs = document.querySelectorAll('input[name="CPF"]');
            cpfInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.slice(0, 11);
                    
                    if (value.length > 9) {
                        value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                    } else if (value.length > 6) {
                        value = value.replace(/^(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
                    } else if (value.length > 3) {
                        value = value.replace(/^(\d{3})(\d{3})/, '$1.$2');
                    }
                    e.target.value = value;
                });
            });

            // Máscara para Telefone
            const telInputs = document.querySelectorAll('input[name="Telefone"]');
            telInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.slice(0, 11);
                    
                    if (value.length > 10) {
                        value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    } else if (value.length > 6) {
                        value = value.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                    } else if (value.length > 2) {
                        value = value.replace(/^(\d{2})(\d{4})/, '($1) $2');
                    } else if (value.length > 0) {
                        value = value.replace(/^(\d{2})/, '($1)');
                    }
                    e.target.value = value;
                });
            });
        });
    </script>
</body>
</html>