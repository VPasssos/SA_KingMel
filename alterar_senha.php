<?php
session_start();
require 'conexao.php';

//GARANTE QUE O USUÁRIO ESTEJA LOGADO

if(!isset($_SESSION['id_usuario'])){
    echo "<script>alert('Acesso Negado'); window.location.href='login.php';</script>";
    exit();
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $id_usuario= $_SESSION["id_usuario"];
    $nova_senha = $_POST["nova_senha"];
    $confirmar_senha = $_POST['confirmar_senha'];

    if($nova_senha !== $confirmar_senha){
        echo "<script>alert('As senhas não coincidem!'); window.location.href='index.php';</script>";
    }elseif(strlen($nova_senha) < 8){
        echo "<script>alert('A senha deve ter pelo menos 8 caracteres'); window.location.href='alterar_senha.php';</script>";
    } elseif($nova_senha === "temp123"){
        echo "<script>alert('Escolha uma senha diferente de temporaria'); window.location.href='index.php';</script>";
    }else{
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        // ATUALIZA A SENHA E REMOVE O STATUS DE TEMPORARIA

        $sql = "UPDATE usuario SET senha = :senha,senha_temporaria = FALSE WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':senha',$senha_hash);
        $stmt->bindParam(':id',$id_usuario);

        if($stmt->execute()){
            session_destroy(); // FINALIZA A SESSAO
            echo "<script>alert('Senha alterada com sucesso! Faça login novamente'); window.location.href='index.php';</script>";
    } else{
        echo "<script>alert('Erro ao alterar a senha!');</script>";
}
}
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar senha</title>
    <link rel="stylesheet" type="text/css" href="ESTILOS/ESTILO_GERAL.css" media="all">
    <link rel="stylesheet" type="text/css" href="ESTILOS/ESTILO_ALTERAR_SENHA.css" media="all">
</head>
<body>
    <div class="main_login">
        <div class="card_login">
            <h1>Alterar Senha</h1>
            <p>Olá, <strong><?php echo $_SESSION['usuario'];?></strong>. Digite sua nova senha abaixo:</p>

            <form action="alterar_senha.php" method="POST">
                <div class="textfield">
                    <label for="nova_senha">Nova senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" required>
                </div>

                <div class="textfield">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                </div>

                <div class="checkbox_area">
                    <input type="checkbox" onclick="mostrarSenha()">
                    <label for="mostrar">Mostrar senha</label>
                </div>

                <button type="submit" class="btn_login">Salvar nova senha</button>
            </form>
        </div>
    </div>

    <script>
        function mostrarSenha(){
            var senha1 = document.getElementById("nova_senha");
            var senha2 = document.getElementById("confirmar_senha");
            var tipo = senha1.type === "password" ? "text": "password";
            senha1.type=tipo;
            senha2.type=tipo;
        }
    </script>

</body>
</html>