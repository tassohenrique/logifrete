<?php
require __DIR__ . '/../includes/init.php';

$transportadores = conectar()->query('SELECT * FROM transportadores ORDER BY nome')->fetchAll();

$titulo = 'Transportadores';
$menu   = 'transportadores';
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Transportadores</h1>
        <p class="text-muted mb-0">Motoristas e transportadoras responsáveis pelos fretes.</p>
    </div>
    <a href="form.php" class="btn btn-primary">+ Novo transportador</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>CPF/CNPJ</th>
                    <th>Contato</th>
                    <th>Veículo</th>
                    <th>Placa</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$transportadores): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Nenhum transportador cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($transportadores as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td class="fw-semibold"><?= e($t['nome']) ?></td>
                    <td><?= formatarCpfCnpj($t['cpf_cnpj']) ?></td>
                    <td><?= formatarTelefone($t['telefone']) ?><br><small class="text-muted"><?= e($t['email']) ?></small></td>
                    <td><?= e($t['veiculo']) ?></td>
                    <td><span class="placa"><?= e($t['placa']) ?></span></td>
                    <td class="text-end text-nowrap">
                        <a href="form.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="excluir.php" method="post" class="d-inline form-excluir">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
