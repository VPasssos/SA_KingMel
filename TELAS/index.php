<?php
session_start();
require_once 'conexao.php';

if($_SERVER["REQUEST_METHOD"] =="POST"){
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuario WHERE email=:email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email',$email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if($usuario && password_verify($senha,$usuario['senha'])){
        //LOGIN BEM SUCEDIDO, DEFINE VARIAVEIS DE SESSÃO
        $_SESSION['usuario'] = $usuario['nome'];
        $_SESSION['perfil'] = $usuario['id_perfil'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];

        // VERIFICA SE A SENHA É TEMPORARIA
        if($usuario['senha_temporaria']){
            // REDIRECIONA PARA A PAGINA "senha_temporaria"
            header("Location: alterar_senha.php");
            exit();
        } else {
            //REDIRECIONA PARA A PAGINA PRINCIPAL
            header("Location: TELA_INICIAL.php");
            exit();
        }
    }else{
        //LOGIN INVALIDO
        echo "<script>alert('E-mail ou senha incorretos'); window.location.href='index.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../ESTILOS/ESTILO_GERAL.css" media="all">
    <link rel="stylesheet" type="text/css" href="../ESTILOS/ESTILO_LOGIN.css" media="all">
    <title>Login</title>
</head>
<body>
<form action="index.php" method="POST">
    <div class="main_login">

        <div class="card_login">
            <h1>LOGIN</h1>

            <div class="textfield">

                <input type="email" name="email" placeholder="Usuário" required>

            </div>
            <div class="textfield">

                <input type="password" name="senha" placeholder="Senha" required>

            </div>
            
            <button class="btn_login" type="submit">Login</button>

            <p><a href="recuperar_senha.php">Esqueci minha senha</a>

        </div>
            
    </div>
</form>
</body>
</html>