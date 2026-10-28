<?php
/**
 * Página inicial (painel) do LogiFrete.
 */
require __DIR__ . '/includes/init.php';

$pdo = conectar();

$totais = [
    'produtos'        => $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn(),
    'enderecos'       => $pdo->query('SELECT COUNT(*) FROM enderecos')->fetchColumn(),
    'transportadores' => $pdo->query('SELECT COUNT(*) FROM transportadores')->fetchColumn(),
    'fretes'          => $pdo->query('SELECT COUNT(*) FROM fretes')->fetchColumn(),
];

$stmt = $pdo->prepare('SELECT COUNT(*) FROM fretes WHERE data_frete = ?');
$stmt->execute([date('Y-m-d')]);
$fretesHoje = $stmt->fetchColumn();

$ultimos = $pdo->query('SELECT f.id, f.data_frete, f.status, f.valor_frete, t.nome AS transportador,
                               o.cidade AS origem, d.cidade AS destino
                        FROM fretes f
                        JOIN transportadores t ON t.id = f.transportador_id
                        JOIN enderecos o ON o.id = f.origem_id
                        JOIN enderecos d ON d.id = f.destino_id
                        ORDER BY f.id DESC LIMIT 5')->fetchAll();

$atalhos = [
    ['produtos',        'Produtos',        'Mercadorias transportadas', 'produtos/index.php'],
    ['enderecos',       'Endereços',       'Origens e destinos',        'enderecos/index.php'],
    ['transportadores', 'Transportadores', 'Motoristas e empresas',     'transportadores/index.php'],
    ['fretes',          'Fretes',          'Total de fretes gerados',   'fretes/index.php'],
];

$titulo = 'Início';
$menu   = 'inicio';
require __DIR__ . '/includes/header.php';
?>

<section class="p-4 mb-4 rounded-3 hero shadow-sm">
    <div class="row align-items-center g-3">
        <div class="col-md-8">
            <h1 class="h2 fw-bold mb-1">Bem-vindo ao LogiFrete</h1>
            <p class="mb-0">Cadastre produtos, endereços e transportadores e gere fretes em poucos cliques.
                Hoje já foram registrados <strong><?= $fretesHoje ?></strong> frete(s).</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= url('fretes/form.php') ?>" class="btn btn-light btn-lg fw-semibold">+ Gerar frete</a>
        </div>
    </div>
</section>

<div class="row g-3 mb-4">
    <?php foreach ($atalhos as [$chave, $nome, $desc, $link]): ?>
        <div class="col-6 col-lg-3">
            <a href="<?= url($link) ?>" class="card card-resumo shadow-sm text-decoration-none h-100">
                <div class="card-body">
                    <div class="text-muted small"><?= $nome ?></div>
                    <div class="display-6 fw-bold text-primary"><?= $totais[$chave] ?></div>
                    <div class="small text-muted"><?= $desc ?></div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Últimos fretes</h2>
        <a href="<?= url('fretes/index.php') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Nº</th><th>Data</th><th>Transportador</th><th>Rota</th><th class="text-end">Valor</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (!$ultimos): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum frete gerado ainda.</td></tr>
            <?php endif; ?>
            <?php foreach ($ultimos as $f): ?>
                <tr>
                    <td><a href="<?= url('fretes/ver.php?id=' . $f['id']) ?>">#<?= str_pad($f['id'], 4, '0', STR_PAD_LEFT) ?></a></td>
                    <td><?= dataBr($f['data_frete']) ?></td>
                    <td><?= e($f['transportador']) ?></td>
                    <td><?= e($f['origem']) ?> &rarr; <?= e($f['destino']) ?></td>
                    <td class="text-end"><?= moeda($f['valor_frete']) ?></td>
                    <td><?= badgeStatus($f['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
