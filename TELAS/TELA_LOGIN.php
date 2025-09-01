<?php
session_start();
require 'conexao.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt -> bindParam(":email", $email);
    $stmt -> execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if($usuario && password_verify($senha, $usuario['senha'])){
        // LOGIN BEM SUCEDIDO, DEFINE VARIÁVEIS DE SESSÃO
        $_SESSION['usuario'] = $usuario['nome'];
        $_SESSION['perfil'] = $usuario['id_perfil'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];

        // VERIFICA SE A SENHA É TEMPORÁRIA
        if($usuario['senha_temporaria']){
            // REDIRECIONA PARA A TROCA DA SENHA TEMPORÁRIA
            header("Location: alterar_senha.php");
            exit();
        } else{
            // REDIRECIONA PARA A PÁGINA PRINCIPAL
            header("Location: TELAS/TELA_INICIAL.php");
            exit();
        }
    } else {
        // LOGIN INVÁLIDO
        $erro_login = "E-mail ou senha incorretos";
    }
} 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - King Mel</title>
    <link rel="stylesheet" type="text/css" href="ESTILOS/ESTILO_GERAL.css" media="all">
    <link rel="stylesheet" type="text/css" href="ESTILOS/ESTILO_LOGIN.css" media="all">

</head>
<body>

<div class="main_login">
    <div class="card_login">
        <h1>LOGIN</h1>
        
        <?php if(isset($erro_login)): ?>
            <div class="erro">
                <?php echo $erro_login; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="textfield">
                <input type="email" name="email" placeholder="E-mail" required>
            </div>
            
            <div class="textfield">
                <input type="password" name="senha" placeholder="Senha" required>
            </div>
            
            <button type="submit" class="btn_login">Entrar</button>
        </form>

        <div class="links">
            <a href="recuperar_senha.php">Esqueci minha senha</a>
        </div>
    </div>
</div>

<script>
    // Adicionar validação básica do formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const email = document.querySelector('input[name="email"]');
        const senha = document.querySelector('input[name="senha"]');
        
        if (!email.value || !senha.value) {
            e.preventDefault();
            alert('Por favor, preencha todos os campos.');
        }
    });
</script>

</body>
</html>