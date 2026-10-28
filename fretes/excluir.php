<?php
require __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Os itens em frete_produtos são apagados junto (ON DELETE CASCADE)
    conectar()->prepare('DELETE FROM fretes WHERE id = ?')->execute([(int) $_POST['id']]);
    flash('success', 'Frete excluído.');
}
redirecionar('fretes/index.php');
