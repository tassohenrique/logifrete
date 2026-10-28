<?php
/**
 * Detalhes de um frete: transportador, origem, destino e todos os produtos.
 */
require __DIR__ . '/../includes/init.php';

$pdo = conectar();
$id  = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT f.*, t.nome AS t_nome, t.cpf_cnpj, t.telefone, t.email, t.veiculo, t.placa
                       FROM fretes f JOIN transportadores t ON t.id = f.transportador_id WHERE f.id = ?');
$stmt->execute([$id]);
$frete = $stmt->fetch();

if (!$frete) {
    flash('danger', 'Frete não encontrado.');
    redirecionar('fretes/index.php');
}

$stmtEnd = $pdo->prepare('SELECT * FROM enderecos WHERE id = ?');
$stmtEnd->execute([$frete['origem_id']]);
$origem = $stmtEnd->fetch();
$stmtEnd->execute([$frete['destino_id']]);
$destino = $stmtEnd->fetch();

$stmt = $pdo->prepare('SELECT p.nome, p.peso_kg, p.valor, fp.quantidade
                       FROM frete_produtos fp JOIN produtos p ON p.id = fp.produto_id
                       WHERE fp.frete_id = ? ORDER BY fp.id');
$stmt->execute([$id]);
$itens = $stmt->fetchAll();

// Totais calculados no PHP
$totalItens = $totalPeso = $totalValor = 0;
foreach ($itens as $item) {
    $totalItens += $item['quantidade'];
    $totalPeso  += $item['quantidade'] * $item['peso_kg'];
    $totalValor += $item['quantidade'] * $item['valor'];
}

$numero = str_pad($frete['id'], 4, '0', STR_PAD_LEFT);
$titulo = 'Frete #' . $numero;
$menu   = 'fretes';
require __DIR__ . '/../includes/header.php';
?>

<nav aria-label="breadcrumb" class="no-print">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Fretes</a></li>
        <li class="breadcrumb-item active"><?= $titulo ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Frete #<?= $numero ?> <?= badgeStatus($frete['status']) ?></h1>
        <p class="text-muted mb-0">Data do frete: <?= dataBr($frete['data_frete']) ?></p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Imprimir</button>
        <a href="form.php?id=<?= $frete['id'] ?>" class="btn btn-primary">Editar</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted">Transportador</h2>
                <p class="fw-semibold mb-1"><?= e($frete['t_nome']) ?></p>
                <p class="mb-1 small">CPF/CNPJ: <?= formatarCpfCnpj($frete['cpf_cnpj']) ?></p>
                <p class="mb-1 small"><?= e($frete['veiculo']) ?> &middot; <span class="placa"><?= e($frete['placa']) ?></span></p>
                <p class="mb-0 small"><?= formatarTelefone($frete['telefone']) ?> <?= e($frete['email']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted">Origem</h2>
                <p class="fw-semibold mb-1"><?= e($origem['descricao']) ?></p>
                <p class="mb-0 small"><?= e(enderecoCompleto($origem)) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted">Destino</h2>
                <p class="fw-semibold mb-1"><?= e($destino['descricao']) ?></p>
                <p class="mb-0 small"><?= e(enderecoCompleto($destino)) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white"><h2 class="h5 mb-0">Produtos transportados</h2></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Qtd.</th>
                    <th class="text-end">Peso unit.</th>
                    <th class="text-end">Peso total</th>
                    <th class="text-end">Valor unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= e($item['nome']) ?></td>
                    <td class="text-center"><?= $item['quantidade'] ?></td>
                    <td class="text-end"><?= numeroBr($item['peso_kg']) ?> kg</td>
                    <td class="text-end"><?= numeroBr($item['peso_kg'] * $item['quantidade']) ?> kg</td>
                    <td class="text-end"><?= moeda($item['valor']) ?></td>
                    <td class="text-end"><?= moeda($item['valor'] * $item['quantidade']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light fw-semibold">
                <tr>
                    <td>Total</td>
                    <td class="text-center"><?= $totalItens ?></td>
                    <td></td>
                    <td class="text-end"><?= numeroBr($totalPeso) ?> kg</td>
                    <td></td>
                    <td class="text-end"><?= moeda($totalValor) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <?php if ($frete['observacao']): ?>
            <div class="alert alert-light border mb-0"><strong>Observação:</strong> <?= e($frete['observacao']) ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-end">
                <div class="small text-uppercase">Valor do frete</div>
                <div class="fs-3 fw-bold"><?= moeda($frete['valor_frete']) ?></div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
