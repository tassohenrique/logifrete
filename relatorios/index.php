<?php
/**
 * Relatório de fretes de um determinado dia.
 * Permite baixar em CSV (Excel) ou imprimir / salvar em PDF pelo navegador.
 */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/relatorio.php';

$data = $_GET['data'] ?? date('Y-m-d');
if (!DateTime::createFromFormat('Y-m-d', $data)) {
    $data = date('Y-m-d');
}

$fretes     = fretesDoDia($data);
$totalFrete = array_sum(array_column($fretes, 'valor_frete'));
$totalPeso  = array_sum(array_column($fretes, 'peso_total'));

$titulo = 'Relatórios';
$menu   = 'relatorios';
require __DIR__ . '/../includes/header.php';
?>

<div class="mb-3">
    <h1 class="h3 mb-0">Relatório de fretes do dia</h1>
    <p class="text-muted mb-0">Escolha uma data para ver e baixar os fretes realizados.</p>
</div>

<div class="card shadow-sm mb-3 no-print">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get">
            <div class="col-sm-4 col-md-3">
                <label for="data" class="form-label">Data</label>
                <input type="date" id="data" name="data" class="form-control" value="<?= e($data) ?>" required>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Gerar relatório</button>
            </div>
            <div class="col-auto ms-sm-auto d-flex gap-2">
                <a href="download.php?data=<?= e($data) ?>" class="btn btn-success <?= $fretes ? '' : 'disabled' ?>">Baixar CSV (Excel)</a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()" <?= $fretes ? '' : 'disabled' ?>>Imprimir / PDF</button>
            </div>
        </form>
    </div>
</div>

<h2 class="h5">Fretes de <?= dataBr($data) ?></h2>

<div class="row g-3 mb-3">
    <div class="col-sm-4"><div class="card card-resumo shadow-sm"><div class="card-body">
        <div class="text-muted small">Fretes</div><div class="fs-4 fw-bold"><?= count($fretes) ?></div>
    </div></div></div>
    <div class="col-sm-4"><div class="card card-resumo shadow-sm"><div class="card-body">
        <div class="text-muted small">Peso transportado</div><div class="fs-4 fw-bold"><?= numeroBr($totalPeso) ?> kg</div>
    </div></div></div>
    <div class="col-sm-4"><div class="card card-resumo shadow-sm"><div class="card-body">
        <div class="text-muted small">Total em fretes</div><div class="fs-4 fw-bold"><?= moeda($totalFrete) ?></div>
    </div></div></div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nº</th>
                    <th>Transportador</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Produtos</th>
                    <th class="text-end">Peso</th>
                    <th class="text-end">Frete</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$fretes): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Nenhum frete nesta data.</td></tr>
            <?php endif; ?>
            <?php foreach ($fretes as $f): ?>
                <tr>
                    <td>#<?= $f['numero'] ?></td>
                    <td><?= e($f['transportador']) ?><br><small class="text-muted"><?= e($f['placa']) ?></small></td>
                    <td><?= e($f['origem_cidade'] . '/' . $f['origem_uf']) ?></td>
                    <td><?= e($f['destino_cidade'] . '/' . $f['destino_uf']) ?></td>
                    <td class="small"><?= e($f['produtos']) ?></td>
                    <td class="text-end text-nowrap"><?= numeroBr($f['peso_total']) ?> kg</td>
                    <td class="text-end text-nowrap"><?= moeda($f['valor_frete']) ?></td>
                    <td><?= badgeStatus($f['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
