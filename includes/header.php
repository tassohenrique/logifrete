<?php
/**
 * Cabeçalho padrão com menu de navegação.
 * Variáveis esperadas: $titulo (título da página) e $menu (item ativo do menu).
 */
$titulo = $titulo ?? 'LogiFrete';
$menu   = $menu ?? '';

$itensMenu = [
    'inicio'          => ['Início', 'index.php'],
    'produtos'        => ['Produtos', 'produtos/index.php'],
    'enderecos'       => ['Endereços', 'enderecos/index.php'],
    'transportadores' => ['Transportadores', 'transportadores/index.php'],
    'fretes'          => ['Fretes', 'fretes/index.php'],
    'relatorios'      => ['Relatórios', 'relatorios/index.php'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> | LogiFrete</title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100">

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= url('index.php') ?>">
                <span class="logo-box">LF</span> LogiFrete
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                    aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto">
                    <?php foreach ($itensMenu as $chave => [$nome, $link]): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $menu === $chave ? 'active fw-semibold' : '' ?>"
                               href="<?= url($link) ?>"><?= $nome ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="container py-4 flex-grow-1">
    <?php mostrarFlash(); ?>
