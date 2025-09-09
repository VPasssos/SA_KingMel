<?php
session_start();
include('../conexao.php'); // Inclui a conexão com o banco de dados

// VERIFICA SE O USUARIO TEM PERMISSÃO
if($_SESSION['perfil'] != 1){
    $_SESSION['mensagem_erro'] = 'Acesso Negado';
    header('Location: principal.php');
    exit();        
    exit();
}

// Função para buscar usuários
function buscarUsuarios($pdo, $nome = null) {
    $sql = "SELECT u.id_usuario, u.nome, u.email, u.id_perfil, p.nome_perfil 
            FROM usuario u
            LEFT JOIN perfil p ON u.id_perfil = p.id_perfil";
    
    if ($nome) {
        $sql .= " WHERE u.nome LIKE :nome OR u.email LIKE :nome";
    }

    $stmt = $pdo->prepare($sql);

    if ($nome) {
        $stmt->bindValue(':nome', '%' . $nome . '%');
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para excluir um usuário
if (isset($_GET['excluir'])) {
    $id_usuario = $_GET['excluir'];

    $sql = "DELETE FROM usuario WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: TELA_GERENCIAR_USUARIOS.php"); // Redireciona após a exclusão
    exit();
}

// Função para adicionar um novo usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_usuario'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
    $perfil = $_POST['perfil'];

    $sql = "INSERT INTO usuario (nome, email, senha, id_perfil) VALUES (:nome, :email, :senha, :perfil)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    $stmt->bindParam(':perfil', $perfil);
    $stmt->execute();

    $_SESSION['mensagem_sucesso'] = 'Usuario adicionado com sucesso!';
    header('Location: TELA_GERENCIAR_USUARIOS.php');
    exit();
    exit();
}

// Função para alterar um usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $perfil = $_POST['perfil'];
    $nova_senha = !empty($_POST['nova_senha']) ? password_hash($_POST['nova_senha'], PASSWORD_BCRYPT) : null;

    if ($nova_senha) {
        $sql = "UPDATE usuario SET nome = :nome, email = :email, id_perfil = :perfil, senha = :senha WHERE id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':senha', $nova_senha);
    } else {
        $sql = "UPDATE usuario SET nome = :nome, email = :email, id_perfil = :perfil WHERE id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sql);
    }
    
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':perfil', $perfil);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();

    $_SESSION['mensagem_sucesso'] = 'Usuario alterado com sucesso!';
    header('Location: TELA_GERENCIAR_USUARIOS.php');
    exit();
    exit();
}

// Verifica se há um termo de busca
$usuarios = isset($_POST['busca']) ? buscarUsuarios($pdo, $_POST['busca']) : buscarUsuarios($pdo);

// Buscar perfis para os selects
$sql_perfis = "SELECT * FROM perfil ORDER BY nome_perfil";
$stmt_perfis = $pdo->query($sql_perfis);
$perfis = $stmt_perfis->fetchAll(PDO::FETCH_ASSOC);

// Buscar usuário para edição se houver ID na URL
$usuario_edicao = null;
if (isset($_GET['editar'])) {
    $id_usuario = $_GET['editar'];
    $sql = "SELECT * FROM usuario WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GERENCIAR USUÁRIOS</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERENCIAR_USUARIOS.css">
    <script src="../JS/mascaras.js"></script>
    
</head>
<body>

    <?php include("MENU.php"); ?>

    <main>
        <h1>GERENCIAR USUÁRIOS</h1>
        
        <?php
        if (isset($_SESSION['mensagem_sucesso'])) {
            echo '<div class="mensagem sucesso">' . $_SESSION['mensagem_sucesso'] . '</div>';
            unset($_SESSION['mensagem_sucesso']);
        }
        if (isset($_SESSION['mensagem_erro'])) {
            echo '<div class="mensagem erro">' . $_SESSION['mensagem_erro'] . '</div>';
            unset($_SESSION['mensagem_erro']);
        }
        ?>

        <div class="ops_usu">
            <button id="btnAdicionar" onclick="abrirModal('modalAdicionar')">Adicionar</button>
            <form action="TELA_GERENCIAR_USUARIOS.php" method="POST">
                <input type="text" name="busca" id="busca" placeholder="Pesquisar usuário">
                <button type="submit">Pesquisar</button>
            </form>
        </div>

        <div class="tabela_usu">
            <?php if (!empty($usuarios)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['nome_perfil']) ?></td>
                                <td>
                                    <a href="TELA_GERENCIAR_USUARIOS.php?editar=<?= htmlspecialchars($usuario['id_usuario']) ?>">Alterar</a>
                                    <a href="TELA_GERENCIAR_USUARIOS.php?excluir=<?= htmlspecialchars($usuario['id_usuario']) ?>" 
                                       class="excluir" 
                                       onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum usuário encontrado</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal para Adicionar Usuário -->
    <div id="modalAdicionar" class="modal">
        <div class="modal-content">
            <h2>Adicionar Usuário</h2>
            <form method="POST" action="TELA_GERENCIAR_USUARIOS.php">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" required onkeypress ="mascara(this, nomeM)">

                <label for="email">E-mail:</label>
                <input type="email" name="email" required>

                <label for="senha">Senha:</label>
                <input type="password" name="senha" required>

                <label for="perfil">Perfil:</label>
                <select name="perfil" required>
                    <?php foreach ($perfis as $perfil): ?>
                        <option value="<?= $perfil['id_perfil'] ?>"><?= htmlspecialchars($perfil['nome_perfil']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="adicionar_usuario" class="btn_acao">Adicionar</button>
                <button type="button" class="btn_acao btn_cancelar" onclick="fecharModal('modalAdicionar')">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para Alterar Usuário -->
    <?php if ($usuario_edicao): ?>
    <div id="modalAlterar" class="modal" style="display: flex;">
        <div class="modal-content">
            <h2>Alterar Usuário</h2>
            <form method="POST" action="TELA_GERENCIAR_USUARIOS.php">
                <input type="hidden" name="id_usuario" value="<?= $usuario_edicao['id_usuario'] ?>">
                
                <label for="nome_editar">Nome:</label>
                <input type="text" name="nome" id="nome_editar" value="<?= htmlspecialchars($usuario_edicao['nome']) ?>" required onkeypress ="mascara(this, nomeM)">

                <label for="email_editar">E-mail:</label>
                <input type="email" name="email" id="email_editar" value="<?= htmlspecialchars($usuario_edicao['email']) ?>" required>

                <label for="perfil_editar">Perfil:</label>
                <select name="perfil" id="perfil_editar" required>
                    <?php foreach ($perfis as $perfil): ?>
                        <option value="<?= $perfil['id_perfil'] ?>" 
                            <?= $perfil['id_perfil'] == $usuario_edicao['id_perfil'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($perfil['nome_perfil']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="nova_senha">Nova Senha (deixe em branco para manter a atual):</label>
                <input type="password" name="nova_senha" id="nova_senha">

                <button type="submit" name="alterar_usuario" class="btn_acao">Alterar</button>
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
            // Redirecionar para a mesma página sem parâmetros de edição
            window.location.href = 'TELA_GERENCIAR_USUARIOS.php';
        }

        // Fechar modal clicando fora dele
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                // Redirecionar para a mesma página sem parâmetros de edição
                window.location.href = 'TELA_GERENCIAR_USUARIOS.php';
            }
        }

        // Se houver parâmetro de edição na URL, abrir o modal de alteração
        <?php if ($usuario_edicao): ?>
            document.addEventListener('DOMContentLoaded', function() {
                abrirModal('modalAlterar');
            });
        <?php endif; ?>
    </script>
</body>
</html>