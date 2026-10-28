<?php
require __DIR__ . '/../includes/init.php';

$enderecos = conectar()->query('SELECT * FROM enderecos ORDER BY descricao')->fetchAll();

$titulo = 'Endereços';
$menu   = 'enderecos';
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Endereços</h1>
        <p class="text-muted mb-0">Locais de coleta (origem) e de entrega (destino).</p>
    </div>
    <a href="form.php" class="btn btn-primary">+ Novo endereço</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Descrição</th>
                    <th>Endereço</th>
                    <th>Cidade/UF</th>
                    <th>CEP</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$enderecos): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum endereço cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($enderecos as $end): ?>
                <tr>
                    <td><?= $end['id'] ?></td>
                    <td class="fw-semibold"><?= e($end['descricao']) ?></td>
                    <td><?= e($end['logradouro'] . ', ' . $end['numero']) ?>
                        <?= $end['complemento'] ? '<small class="text-muted">(' . e($end['complemento']) . ')</small>' : '' ?></td>
                    <td><?= e($end['cidade'] . '/' . $end['uf']) ?></td>
                    <td><?= formatarCep($end['cep']) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="form.php?id=<?= $end['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="excluir.php" method="post" class="d-inline form-excluir">
                            <input type="hidden" name="id" value="<?= $end['id'] ?>">
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
