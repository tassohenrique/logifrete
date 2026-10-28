<?php
require __DIR__ . '/../includes/init.php';

$busca = trim($_GET['busca'] ?? '');

$sql = 'SELECT * FROM produtos';
$params = [];
if ($busca !== '') {
    $sql .= ' WHERE nome LIKE ?';
    $params[] = '%' . $busca . '%';
}
$sql .= ' ORDER BY nome';

$stmt = conectar()->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

$titulo = 'Produtos';
$menu   = 'produtos';
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Produtos</h1>
        <p class="text-muted mb-0">Mercadorias que podem ser incluídas nos fretes.</p>
    </div>
    <a href="form.php" class="btn btn-primary">+ Novo produto</a>
</div>

<form class="row g-2 mb-3" method="get">
    <div class="col-sm-8 col-md-6">
        <input type="search" name="busca" class="form-control" placeholder="Buscar pelo nome..." value="<?= e($busca) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary">Buscar</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th class="text-end">Peso (kg)</th>
                    <th class="text-end">Valor</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$produtos): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum produto encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td class="fw-semibold"><?= e($p['nome']) ?></td>
                    <td><?= e($p['descricao']) ?></td>
                    <td class="text-end"><?= numeroBr($p['peso_kg']) ?></td>
                    <td class="text-end"><?= moeda($p['valor']) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="excluir.php" method="post" class="d-inline form-excluir">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
