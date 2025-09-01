<?php   
$currentPage = basename($_SERVER['PHP_SELF']);

// Definir permissões baseadas no perfil
$permissoes = [];

if (isset($_SESSION['perfil'])) {
    $id_perfil = $_SESSION['perfil'];
    
    // Administrador (perfil 1) - Acesso completo
    if ($id_perfil == 1) {
        $permissoes = [
            'TELA_INICIAL.php' => true,
            'TELA_GERENCIAR_PRODUTOS.php' => true,
            'TELA_GERENCIAR_APIARIOS.php' => true,
            'TELA_GERENCIAR_USUARIOS.php' => true,
            'TELA_GERENCIAR_CLIENTES.php' => true,
            'TELA_GERENCIAR_FUNCIONARIOS.php' => true,
            'TELA_RELATORIOS.php' => true
        ];
    }
    // Secretária (perfil 2) - Acesso limitado
    elseif ($id_perfil == 2) {
        $permissoes = [
            'TELA_INICIAL.php' => true,
            'TELA_GERENCIAR_CLIENTES.php' => true,
            'TELA_RELATORIOS.php' => true
        ];
    }
    // Almoxarife (perfil 3) - Acesso a produtos e apiários
    elseif ($id_perfil == 3) {
        $permissoes = [
            'TELA_INICIAL.php' => true,
            'TELA_GERENCIAR_PRODUTOS.php' => true,
            'TELA_GERENCIAR_APIARIOS.php' => true
        ];
    }
    // Cliente (perfil 4) - Acesso apenas à tela inicial
    elseif ($id_perfil == 4) {
        $permissoes = [
            'TELA_INICIAL.php' => true
        ];
    }
}

// Função para verificar se o usuário tem acesso à página
function temAcesso($pagina) {
    global $permissoes;
    return isset($permissoes[$pagina]) && $permissoes[$pagina];
}

// Nome do perfil para exibição
$nomes_perfis = [
    1 => 'Administrador',
    2 => 'Secretária', 
    3 => 'Almoxarife',
    4 => 'Cliente'
];

$nome_perfil = isset($_SESSION['perfil']) ? $nomes_perfis[$_SESSION['perfil']] : 'Visitante';
?>
<!DOCTYPE html>
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

<?php if (isset($_SESSION['usuario'])): ?>
<div class="user-info">
    <?= htmlspecialchars($_SESSION['usuario']) ?> - <?= $nome_perfil ?>
</div>
<?php endif; ?>

<nav id="dock"> 
    <div id="dock_content">
        <ul id="dock_items">
            <!-- Tela Inicial - Disponível para todos os perfis logados -->
            <li class="dock-item <?= ($currentPage == 'TELA_INICIAL.php') ? 'active' : '' ?>">
                <a href="TELA_INICIAL.php">
                    <i class="fa-solid fa-house"></i>
                    <span class="tooltip">Início</span>
                </a>
            </li>

            <!-- Gerenciar Produtos - Admin e Almoxarife -->
            <li class="dock-item <?= temAcesso('TELA_GERENCIAR_PRODUTOS.php') ? '' : 'disabled' ?> <?= ($currentPage == 'TELA_GERENCIAR_PRODUTOS.php') ? 'active' : '' ?>">
                <a href="<?= temAcesso('TELA_GERENCIAR_PRODUTOS.php') ? 'TELA_GERENCIAR_PRODUTOS.php' : '#' ?>">
                    <i class="fa-solid fa-box-archive"></i>
                    <span class="tooltip"><?= temAcesso('TELA_GERENCIAR_PRODUTOS.php') ? 'Produtos' : 'Acesso negado' ?></span>
                </a>
            </li>

            <!-- Gerenciar Apiários - Admin e Almoxarife -->
            <li class="dock-item <?= temAcesso('TELA_GERENCIAR_APIARIOS.php') ? '' : 'disabled' ?> <?= ($currentPage == 'TELA_GERENCIAR_APIARIOS.php') ? 'active' : '' ?>">
                <a href="<?= temAcesso('TELA_GERENCIAR_APIARIOS.php') ? 'TELA_GERENCIAR_APIARIOS.php' : '#' ?>">
                    <i class="fa-solid fa-truck"></i>
                    <span class="tooltip"><?= temAcesso('TELA_GERENCIAR_APIARIOS.php') ? 'Apiários' : 'Acesso negado' ?></span>
                </a>
            </li>
            
            <!-- Gerenciar Usuários - Apenas Admin -->
            <li class="dock-item <?= temAcesso('TELA_GERENCIAR_USUARIOS.php') ? '' : 'disabled' ?> <?= ($currentPage == 'TELA_GERENCIAR_USUARIOS.php') ? 'active' : '' ?>">
                <a href="<?= temAcesso('TELA_GERENCIAR_USUARIOS.php') ? 'TELA_GERENCIAR_USUARIOS.php' : '#' ?>">
                    <i class="fa-solid fa-users"></i>
                    <span class="tooltip"><?= temAcesso('TELA_GERENCIAR_USUARIOS.php') ? 'Usuários' : 'Acesso negado' ?></span>
                </a>
            </li>

            <!-- Gerenciar Clientes - Admin e Secretária -->
            <li class="dock-item <?= temAcesso('TELA_GERENCIAR_CLIENTES.php') ? '' : 'disabled' ?> <?= ($currentPage == 'TELA_GERENCIAR_CLIENTES.php') ? 'active' : '' ?>">
                <a href="<?= temAcesso('TELA_GERENCIAR_CLIENTES.php') ? 'TELA_GERENCIAR_CLIENTES.php' : '#' ?>">
                    <i class="fa-solid fa-address-book"></i>
                    <span class="tooltip"><?= temAcesso('TELA_GERENCIAR_CLIENTES.php') ? 'Clientes' : 'Acesso negado' ?></span>
                </a>
            </li>

            <!-- Relatórios - Admin e Secretária -->
            <li class="dock-item <?= temAcesso('TELA_RELATORIOS.php') ? '' : 'disabled' ?> <?= ($currentPage == 'TELA_RELATORIOS.php') ? 'active' : '' ?>">
                <a href="<?= temAcesso('TELA_RELATORIOS.php') ? 'TELA_RELATORIOS.php' : '#' ?>">
                    <i class="fa-solid fa-chart-bar"></i>
                    <span class="tooltip"><?= temAcesso('TELA_RELATORIOS.php') ? 'Relatórios' : 'Acesso negado' ?></span>
                </a>
            </li>

            <!-- Logout - Disponível para todos os perfis logados -->
            <li class="dock-item <?= ($currentPage == '../logout.php') ? 'active' : '' ?>">
                <a href="../logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="tooltip">Sair</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<script src="../CODIGOS/script.js"></script>
</body>
</html>