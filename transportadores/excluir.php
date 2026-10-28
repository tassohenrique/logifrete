<?php
require __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        conectar()->prepare('DELETE FROM transportadores WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Transportador excluído.');
    } catch (PDOException $erro) {
        flash('danger', 'Não é possível excluir: este transportador possui fretes cadastrados.');
    }
}
redirecionar('transportadores/index.php');
