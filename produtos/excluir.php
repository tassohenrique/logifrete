<?php
require __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        conectar()->prepare('DELETE FROM produtos WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Produto excluído.');
    } catch (PDOException $erro) {
        flash('danger', 'Não é possível excluir: este produto está vinculado a um frete.');
    }
}
redirecionar('produtos/index.php');
