<!DOCTYPE html>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<html lang="pt-br">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINGMEL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_GERAL.css">
    <link rel="stylesheet" href="../ESTILOS/ESTILO_MENU.css">
</head>
<body>
<nav id="dock"> 
    <div id="dock_content">
        <ul id="dock_items">
            <li class="dock-item <?= ($currentPage == 'TELAS/TELA_INICIAL.php') ? 'active' : '' ?>">
                <a href="TELA_INICIAL.php">
                    <i class="fa-solid fa-house"></i>
                </a>
            </li>

            <li class="dock-item <?= ($currentPage == '#') ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa-solid fa-user"></i>
                </a>
            </li>

            <li class="dock-item <?= ($currentPage == '#') ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa-solid fa-user"></i>
                </a>
            </li>

            <li class="dock-item <?= ($currentPage == '#') ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa-solid fa-user"></i>
                </a>
            </li>

            <li class="dock-item <?= ($currentPage == '../logout.php') ? 'active' : '' ?>">
                <a href="../logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>

<script src="../CODIGOS/script.js"></script>
</body>
</html>