<?php
require __DIR__ . '/../includes/init.php';

$filtroData   = $_GET['data'] ?? '';
$filtroStatus = $_GET['status'] ?? '';

$sql = 'SELECT f.*, t.nome AS transportador, o.cidade AS origem_cidade, o.uf AS origem_uf,
               d.cidade AS destino_cidade, d.uf AS destino_uf,
               COUNT(fp.id) AS qtd_itens, COALESCE(SUM(fp.quantidade * p.peso_kg), 0) AS peso_total
        FROM fretes f
        JOIN transportadores t ON t.id = f.transportador_id
        JOIN enderecos o       ON o.id = f.origem_id
        JOIN enderecos d       ON d.id = f.destino_id
        LEFT JOIN frete_produtos fp ON fp.frete_id = f.id
        LEFT JOIN produtos p        ON p.id = fp.produto_id
        WHERE 1 = 1';
$params = [];

if ($filtroData !== '') {
    $sql .= ' AND f.data_frete = ?';
    $params[] = $filtroData;
}
if ($filtroStatus !== '') {
    $sql .= ' AND f.status = ?';
    $params[] = $filtroStatus;
}
$sql .= ' GROUP BY f.id, f.transportador_id, f.origem_id, f.destino_id, f.data_frete, f.valor_frete, f.status,
                   f.observacao, f.criado_em, t.nome, o.cidade, o.uf, d.cidade, d.uf
          ORDER BY f.data_frete DESC, f.id DESC';

$stmt = conectar()->prepare($sql);
$stmt->execute($params);
$fretes = $stmt->fetchAll();

$titulo = 'Fretes';
$menu   = 'fretes';
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Fretes</h1>
        <p class="text-muted mb-0">Cada frete liga um transportador a vários produtos, com origem e destino.</p>
    </div>
    <a href="form.php" class="btn btn-primary">+ Gerar frete</a>
</div>

<form class="row g-2 mb-3" method="get">
    <div class="col-sm-4 col-md-3">
        <input type="date" name="data" class="form-control" value="<?= e($filtroData) ?>" aria-label="Filtrar por data">
    </div>
    <div class="col-sm-4 col-md-3">
        <select name="status" class="form-select" aria-label="Filtrar por status">
            <option value="">Todos os status</option>
            <?php foreach (statusFrete() as $s): ?>
                <option <?= $filtroStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary">Filtrar</button>
        <a href="index.php" class="btn btn-link">Limpar</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nº</th>
                    <th>Data</th>
                    <th>Transportador</th>
                    <th>Origem &rarr; Destino</th>
                    <th class="text-center">Itens</th>
                    <th class="text-end">Peso (kg)</th>
                    <th class="text-end">Valor frete</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$fretes): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">Nenhum frete encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($fretes as $f): ?>
                <tr>
                    <td class="fw-semibold">#<?= str_pad($f['id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td><?= dataBr($f['data_frete']) ?></td>
                    <td><?= e($f['transportador']) ?></td>
                    <td><?= e($f['origem_cidade'] . '/' . $f['origem_uf']) ?> &rarr; <?= e($f['destino_cidade'] . '/' . $f['destino_uf']) ?></td>
                    <td class="text-center"><?= $f['qtd_itens'] ?></td>
                    <td class="text-end"><?= numeroBr($f['peso_total']) ?></td>
                    <td class="text-end"><?= moeda($f['valor_frete']) ?></td>
                    <td><?= badgeStatus($f['status']) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="ver.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                        <a href="form.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="excluir.php" method="post" class="d-inline form-excluir">
                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
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
