<?php
session_start();
require_once '../telas/conexao.php';


//VERIFICA SE O USUÁRIO TEM PERMISSÃO DE ADM

if($_SESSION['perfil']!= 1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php';</script>";
    exit();
}

//INICIALIZA AS VARIAVEIS
$usuario = null;

//SE O FORMULARIO FOR ENVIADO, BUSCA O USUARIO PELO ID OU PELO NOME
if ($_SERVER["REQUEST_METHOD"]=="POST") {
    if (!empty($_POST['busca_usuario'])) {
        $busca = trim($_POST["busca_usuario"]);

    if(is_numeric($busca)) {
        $sql = "SELECT * FROM usuario WHERE id_usuario = :busca";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM usuario WHERE nome LIKE :busca_nome";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // SE O USUÁRIO NÃO FOR ENCONTRADO, EXIBE UM ALERTA

    if(!$usuario){
        echo "<script>alert('Usuário não encontrado!');</script>";
    }
}
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!empty($_GET['id'])) {
        $busca = trim($_GET["id"]);

    if(is_numeric($busca)) {
        $sql = "SELECT * FROM usuario WHERE id_usuario = :busca";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca' ,$busca,PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM usuario WHERE nome LIKE :busca_nome";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$usuario){
        echo "<script>alert('Usuário não encontrado!');</script>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar usuário</title>
    <link rel="stylesheet" href="style.css">
<!--Certifique-se de que o Java script está sendo carregado corretamente-->
<script src="scripts.js"></script>
<script src="mascaras.js"></script>
</head>
<body>
<h2>Alterar Usuários</h2>
<!--FORMULÁRIO PARA BUSCAR USUARIOS-->
    <form action="alterar_usuario.php" method="POST">
        <label for="busca_usuario">Digite o id ou NOME do usuário:</label>
        <input type="text" id="busca_usuario" name="busca_usuario" required onkeyup="buscarSugestoes()">
        <div id="sugestoes">
        </div>
        <button type="submit">Buscar</button>
    </form>

    <?php if($usuario): ?>
        <form action="processa_alteracao_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?=htmlspecialchars($usuario['id_usuario'])?>">

            <label for="nome">Nome:</label>
            <input type="text" id="nome2" name="nome2" value="<?=htmlspecialchars($usuario['nome'])?>" required onkeypress ="mascara(this, nome)">
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?=htmlspecialchars($usuario['email'])?>" required>

            <label for="id_perfil">Perfil:</label>
            <select id="id_perfil" name="id_perfil">
                <option value="1"<?=$usuario['id_perfil'] == 1 ? 'selected': '' ?>>Administrador</option>
                <option value="2"<?=$usuario['id_perfil'] == 2 ? 'selected': '' ?>>Secretaria</option>
                <option value="3"<?=$usuario['id_perfil'] == 3 ? 'selected': '' ?>>Almoxarife</option>
                <option value="4"<?=$usuario['id_perfil'] == 4 ? 'selected': '' ?>>Cliente</option>
            </select>

            <!--SE O USUÁRIO FOR ad, EXIBIR OPÇÃO DE ALTERAR senha-->

            <?php if($_SESSION['perfil'] == 1): ?>
                <label for="nova_senha">Nova Senha</label>
                <input type="password" id="nova_senha" name="nova_senha">
            <?php endif; ?>

            <button type="submit">Alterar</button>
            <button type="reset">Cancelar</button>
            
        </form>
        <?php endif; ?>

        <address>
            Gustavo Wendt /estudante / tecnico em sistemas 
        </address>
</body>
</html>