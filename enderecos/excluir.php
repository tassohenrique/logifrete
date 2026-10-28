<?php
require __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        conectar()->prepare('DELETE FROM enderecos WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Endereço excluído.');
    } catch (PDOException $erro) {
        flash('danger', 'Não é possível excluir: este endereço é origem ou destino de um frete.');
    }
}
redirecionar('enderecos/index.php');
