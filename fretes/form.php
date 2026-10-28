<?php
/**
 * Geração (cadastro) e edição de frete.
 * Um frete tem UM transportador, UMA origem, UM destino e VÁRIOS produtos (tabela frete_produtos).
 * As linhas de produtos são adicionadas/removidas dinamicamente com JavaScript (assets/js/app.js).
 */
require __DIR__ . '/../includes/init.php';

$pdo = conectar();
$id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

// Dados para os campos de seleção
$transportadores = $pdo->query('SELECT id, nome, veiculo, placa FROM transportadores ORDER BY nome')->fetchAll();
$enderecos       = $pdo->query('SELECT * FROM enderecos ORDER BY descricao')->fetchAll();
$produtos        = $pdo->query('SELECT id, nome, peso_kg, valor FROM produtos ORDER BY nome')->fetchAll();

$frete = [
    'transportador_id' => '',
    'origem_id'        => '',
    'destino_id'       => '',
    'data_frete'       => date('Y-m-d'),
    'valor_frete'      => '',
    'status'           => 'Pendente',
    'observacao'       => '',
];
$itens = [];   // [['produto_id' => 1, 'quantidade' => 2], ...]
$erros = [];

if ($id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM fretes WHERE id = ?');
    $stmt->execute([$id]);
    $frete = $stmt->fetch();
    if (!$frete) {
        flash('danger', 'Frete não encontrado.');
        redirecionar('fretes/index.php');
    }
    $stmt = $pdo->prepare('SELECT produto_id, quantidade FROM frete_produtos WHERE frete_id = ? ORDER BY id');
    $stmt->execute([$id]);
    $itens = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frete = [
        'transportador_id' => (int) ($_POST['transportador_id'] ?? 0),
        'origem_id'        => (int) ($_POST['origem_id'] ?? 0),
        'destino_id'       => (int) ($_POST['destino_id'] ?? 0),
        'data_frete'       => $_POST['data_frete'] ?? '',
        'valor_frete'      => (float) ($_POST['valor_frete'] ?? 0),
        'status'           => $_POST['status'] ?? 'Pendente',
        'observacao'       => trim($_POST['observacao'] ?? ''),
    ];

    // Junta os produtos enviados; se o mesmo produto aparecer duas vezes, soma as quantidades
    $agrupado = [];
    foreach ($_POST['produto_id'] ?? [] as $i => $produtoId) {
        $produtoId  = (int) $produtoId;
        $quantidade = (int) ($_POST['quantidade'][$i] ?? 0);
        if ($produtoId > 0 && $quantidade > 0) {
            $agrupado[$produtoId] = ($agrupado[$produtoId] ?? 0) + $quantidade;
        }
    }
    foreach ($agrupado as $produtoId => $quantidade) {
        $itens[] = ['produto_id' => $produtoId, 'quantidade' => $quantidade];
    }

    if (!$frete['transportador_id']) $erros[] = 'Selecione o transportador.';
    if (!$frete['origem_id'])        $erros[] = 'Selecione o endereço de origem.';
    if (!$frete['destino_id'])       $erros[] = 'Selecione o endereço de destino.';
    if ($frete['origem_id'] && $frete['origem_id'] === $frete['destino_id']) $erros[] = 'Origem e destino devem ser diferentes.';
    if (!DateTime::createFromFormat('Y-m-d', $frete['data_frete'])) $erros[] = 'Informe uma data válida.';
    if ($frete['valor_frete'] < 0)   $erros[] = 'O valor do frete não pode ser negativo.';
    if (!in_array($frete['status'], statusFrete(), true)) $erros[] = 'Status inválido.';
    if (!$itens)                     $erros[] = 'Adicione pelo menos um produto ao frete.';

    if (!$erros) {
        $dados = [
            $frete['transportador_id'], $frete['origem_id'], $frete['destino_id'], $frete['data_frete'],
            $frete['valor_frete'], $frete['status'], $frete['observacao'] ?: null,
        ];

        // Transação: grava o frete e seus itens juntos (ou nada é gravado)
        $pdo->beginTransaction();
        try {
            if ($id) {
                $dados[] = $id;
                $pdo->prepare('UPDATE fretes SET transportador_id = ?, origem_id = ?, destino_id = ?, data_frete = ?,
                               valor_frete = ?, status = ?, observacao = ? WHERE id = ?')->execute($dados);
                $pdo->prepare('DELETE FROM frete_produtos WHERE frete_id = ?')->execute([$id]);
            } else {
                $pdo->prepare('INSERT INTO fretes (transportador_id, origem_id, destino_id, data_frete, valor_frete, status, observacao)
                               VALUES (?, ?, ?, ?, ?, ?, ?)')->execute($dados);
                $id = (int) $pdo->lastInsertId();
            }

            $stmtItem = $pdo->prepare('INSERT INTO frete_produtos (frete_id, produto_id, quantidade) VALUES (?, ?, ?)');
            foreach ($itens as $item) {
                $stmtItem->execute([$id, $item['produto_id'], $item['quantidade']]);
            }

            $pdo->commit();
            flash('success', 'Frete #' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' salvo com sucesso!');
            redirecionar('fretes/ver.php?id=' . $id);
        } catch (PDOException $erro) {
            $pdo->rollBack();
            $erros[] = 'Erro ao salvar o frete: ' . $erro->getMessage();
        }
    }
}

// Pelo menos uma linha de produto vazia no formulário
if (!$itens) {
    $itens[] = ['produto_id' => '', 'quantidade' => 1];
}

$faltaCadastro = !$transportadores || count($enderecos) < 2 || !$produtos;

$titulo = $id ? 'Editar frete' : 'Gerar frete';
$menu   = 'fretes';
require __DIR__ . '/../includes/header.php';

/** Gera as <option> de produto (com peso e valor em data-attributes para o JavaScript). */
function opcoesProduto(array $produtos, $selecionado): string
{
    $html = '<option value="">Selecione o produto...</option>';
    foreach ($produtos as $p) {
        $html .= '<option value="' . $p['id'] . '" data-peso="' . $p['peso_kg'] . '" data-valor="' . $p['valor'] . '"'
               . ((int) $selecionado === (int) $p['id'] ? ' selected' : '') . '>'
               . e($p['nome']) . ' (' . numeroBr($p['peso_kg']) . ' kg)</option>';
    }
    return $html;
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Fretes</a></li>
        <li class="breadcrumb-item active"><?= $titulo ?></li>
    </ol>
</nav>

<?php if ($faltaCadastro): ?>
    <div class="alert alert-warning">
        Para gerar um frete é preciso ter pelo menos <strong>um transportador</strong>,
        <strong>dois endereços</strong> (origem e destino) e <strong>um produto</strong> cadastrados.
        <div class="mt-2 d-flex gap-2 flex-wrap">
            <a href="../transportadores/form.php" class="btn btn-sm btn-outline-dark">Cadastrar transportador</a>
            <a href="../enderecos/form.php" class="btn btn-sm btn-outline-dark">Cadastrar endereço</a>
            <a href="../produtos/form.php" class="btn btn-sm btn-outline-dark">Cadastrar produto</a>
        </div>
    </div>
<?php endif; ?>

<?php if ($erros): ?>
    <div class="alert alert-danger"><ul class="mb-0">
        <?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<form method="post" id="form-frete">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><h2 class="h5 mb-0">1. Dados do frete</h2></div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label for="transportador_id" class="form-label">Transportador *</label>
                        <select id="transportador_id" name="transportador_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($transportadores as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= (int) $frete['transportador_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                                    <?= e($t['nome']) ?> &ndash; <?= e($t['veiculo']) ?> (<?= e($t['placa']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="origem_id" class="form-label">Origem (coleta) *</label>
                        <select id="origem_id" name="origem_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($enderecos as $end): ?>
                                <option value="<?= $end['id'] ?>" <?= (int) $frete['origem_id'] === (int) $end['id'] ? 'selected' : '' ?>>
                                    <?= e($end['descricao']) ?> &ndash; <?= e($end['cidade']) ?>/<?= e($end['uf']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="destino_id" class="form-label">Destino (entrega) *</label>
                        <select id="destino_id" name="destino_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($enderecos as $end): ?>
                                <option value="<?= $end['id'] ?>" <?= (int) $frete['destino_id'] === (int) $end['id'] ? 'selected' : '' ?>>
                                    <?= e($end['descricao']) ?> &ndash; <?= e($end['cidade']) ?>/<?= e($end['uf']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="data_frete" class="form-label">Data *</label>
                        <input type="date" id="data_frete" name="data_frete" class="form-control" required value="<?= e($frete['data_frete']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="valor_frete" class="form-label">Valor do frete (R$) *</label>
                        <input type="number" id="valor_frete" name="valor_frete" class="form-control" step="0.01" min="0" required
                               value="<?= e($frete['valor_frete']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <?php foreach (statusFrete() as $s): ?>
                                <option <?= $frete['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="observacao" class="form-label">Observação</label>
                        <textarea id="observacao" name="observacao" class="form-control" rows="2" maxlength="255"
                                  placeholder="Instruções de coleta ou entrega"><?= e($frete['observacao']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">2. Produtos transportados</h2>
                    <button type="button" class="btn btn-sm btn-success text-nowrap" id="btn-add-produto">+ Adicionar</button>
                </div>
                <div class="card-body">
                    <div id="lista-produtos">
                        <?php foreach ($itens as $item): ?>
                            <div class="row g-2 mb-2 linha-produto">
                                <div class="col-7">
                                    <select name="produto_id[]" class="form-select form-select-sm sel-produto" aria-label="Produto">
                                        <?= opcoesProduto($produtos, $item['produto_id']) ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="number" name="quantidade[]" class="form-control form-control-sm inp-qtd" min="1"
                                           value="<?= (int) $item['quantidade'] ?>" aria-label="Quantidade">
                                </div>
                                <div class="col-2 d-grid">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remover" title="Remover">&times;</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Modelo usado pelo JavaScript para criar novas linhas -->
                    <template id="modelo-produto">
                        <div class="row g-2 mb-2 linha-produto">
                            <div class="col-7">
                                <select name="produto_id[]" class="form-select form-select-sm sel-produto" aria-label="Produto">
                                    <?= opcoesProduto($produtos, null) ?>
                                </select>
                            </div>
                            <div class="col-3">
                                <input type="number" name="quantidade[]" class="form-control form-control-sm inp-qtd" min="1" value="1" aria-label="Quantidade">
                            </div>
                            <div class="col-2 d-grid">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remover" title="Remover">&times;</button>
                            </div>
                        </div>
                    </template>

                    <hr>
                    <div class="resumo-frete">
                        <div class="d-flex justify-content-between"><span>Itens:</span><strong id="total-itens">0</strong></div>
                        <div class="d-flex justify-content-between"><span>Peso total:</span><strong id="total-peso">0,00 kg</strong></div>
                        <div class="d-flex justify-content-between"><span>Valor da carga:</span><strong id="total-valor">R$ 0,00</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end mt-4">
        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4" <?= $faltaCadastro ? 'disabled' : '' ?>>Salvar frete</button>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
