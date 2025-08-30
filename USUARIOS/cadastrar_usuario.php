<?php
session_start();
require_once '../telas/conexao.php';

//VERIFICA SE O USUÁRIO TENHA PERMISSÃO
//SUPONDO QUE O PERFIL "1" SEJA O ADM


if($_SESSION['perfil']!= 1){
    echo "Acesso Negado";
    exit();
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $nome= $_POST["nome2"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $id_perfil = $_POST["id_perfil"];

    $sql = "INSERT INTO usuario(nome, email, senha, id_perfil) VALUES(:nome,:email,:senha,:id_perfil)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome',$nome);
    $stmt->bindParam(':email',$email);
    $stmt->bindParam(':senha',$senha);
    $stmt->bindParam(':id_perfil',$id_perfil);
    
    if($stmt->execute()){
        echo "<script>alert('Usuário cadastrado com sucesso')</script>";
    }else {
        echo "<script>alert('Erro ao cadastrar o usuário')</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar</title>
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <script src="mascaras.js"></script>
</head>
<body>

<?php include("../TELAS/MENU.php"); ?>

    <h2>Cadastrar usuário</h2>
    <form action="cadastrar_usuario.php" method="POST">
        
        <label for="nome">Nome:</label>
        <input type="text" id="nome2" name="nome2" required onkeypress ="mascara(this, nome)">
        
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required onkeypress ="mascara(this, email)">

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required >

        <label for="id_perfil">Perfil:</label>
        <select id="id_perfil" name="id_perfil">
            <option value="1">Administrador(ADM)</option>
            <option value="2">Secretaria(ADM)</option>
            <option value="3">Almoxarife(ADM)</option>
            <option value="4">Cliente(ADM)</option>
        </select>

        <button type="submit">Salvar</button>

        <button type="reset">Cancelar</button>
    </form>

    <address>
            Gustavo Wendt /estudante / tecnico em sistemas 
    </address>
</body>
</html>