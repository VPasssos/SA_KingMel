<?php 
session_start();
require_once 'conexao.php';
require_once 'funcoes_email.php'; //ARQUIVO COM AS FUNÇÕES QUE GERAM A SENHA E SIMULAM O ENVIO

if($_SERVER["REQUEST_METHOD"] =="POST"){
    $email = $_POST['email'];

    $sql = "SELECT * FROM usuario WHERE email=:email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email',$email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if($usuario){
        //GERA UMA SENHA TEMPORARIA E ALEATORIA

        $senha_temporaria = gerarSenhaTemporaria();
        $senha_hash = password_hash($senha_temporaria,PASSWORD_DEFAULT);

        //ATUALIZA A SENHA DO USUARIO NO BANCO
        $sql = "UPDATE usuario SET senha =:senha,senha_temporaria =TRUE WHERE email = :email";
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam(':senha',$senha_hash);
        $stmt->bindParam(':email',$email);
        $stmt->execute();

        //SIMULA O ENVIO DO EMAIL (GRAVA EM txt)
        EnvioEmail($email,$senha_temporaria);
        echo "<script>alert('Uma senha temporaria foi gerada e enviada(simulação). Verifique o arquivo emails_simulados.txt'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Email não encontrado'); window.location.href='index.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar a senha</title>
    <link rel="stylesheet" type="text/css" href="ESTILOS/ESTILO_GERAL.css" media="all">
    <link rel="stylesheet" type="text/css" href="ESTILOS/ESTILO_LOGIN.css" media="all">
</head>
<body>
    <<form action="recuperar_senha.php" method="POST">
    <div class="main_login">

        <div class="card_login">
            <h1>Enviar senha temporaria</h1>

            <div class="textfield">

                <input type="email" name="email" placeholder="digite o email cadastrado" required>

            </div>
            
            <button class="btn_login" type="submit">Enviar a senha Temporaria</button>

        </div>
            
    </div>
</form>
</body>
</html>