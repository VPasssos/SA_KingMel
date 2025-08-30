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
    $tipo_mel= $_POST["tipo_mel"];
    $data_embalado = $_POST["data_embalado"];
    $Peso = $_POST["Peso"];
    $Preco = $_POST["Preco"];
    $Quantidade = $_POST["Quantidade"];

    $sql = "INSERT INTO produto(tipo_mel, data_embalado, Peso, Preco, Quantidade) VALUES(:tipo_mel,:data_embalado,:Peso,:Preco,:Quantidade)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tipo_mel',$tipo_mel);
    $stmt->bindParam(':data_embalado',$data_embalado);
    $stmt->bindParam(':Peso',$Peso);
    $stmt->bindParam(':Preco',$Preco);
    $stmt->bindParam(':Quantidade',$Quantidade);
    
    if($stmt->execute()){
        echo "<script>alert('Produto cadastrado com sucesso')</script>";
    }else {
        echo "<script>alert('Erro ao cadastrar o produto')</script>";
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

    <h2>Cadastrar produto</h2>
    <form action="cadastrar_produto.php" method="POST">
        
        <label for="tipo_mel">tipo_mel:</label>
        <input type="text" id="tipo_mel" name="tipo_mel" required onkeypress ="mascara(this, nomeM)">
        
        
        <label for="data_embalado">Data em que foi embalado:</label>
        <input type="text" id="data_embalado" name="data_embalado" required>

        <label for="Peso">Peso:</label>
        <input type="text" id="Peso" name="Peso" required >

        <label for="Preco">Preço:</label>
        <input type="text" id="Preco" name="Preco" required >
        
        <label for="Quantidade">Quantidade:</label>
        <input type="text" id="Quantidade" name="Quantidade" required >      

        <button type="submit">Salvar</button>

        <button type="reset">Cancelar</button>
    </form>

    <address>
            Gustavo Wendt /estudante / tecnico em sistemas 
    </address>
</body>
</html>