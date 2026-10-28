<?php
/**
 * Cadastro (sem id) e edição (com id) de produto.
 * GET  -> exibe o formulário
 * POST -> valida e salva no banco
 */
require __DIR__ . '/../includes/init.php';

$pdo = conectar();
$id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$produto = ['nome' => '', 'descricao' => '', 'peso_kg' => '', 'valor' => ''];
$erros   = [];

if ($id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    if (!$produto) {
        flash('danger', 'Produto não encontrado.');
        redirecionar('produtos/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto = [
        'nome'      => trim($_POST['nome'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'peso_kg'   => (float) ($_POST['peso_kg'] ?? 0),
        'valor'     => (float) ($_POST['valor'] ?? 0),
    ];

    if ($produto['nome'] === '')    $erros[] = 'Informe o nome do produto.';
    if ($produto['peso_kg'] <= 0)   $erros[] = 'O peso deve ser maior que zero.';
    if ($produto['valor'] < 0)      $erros[] = 'O valor não pode ser negativo.';

    if (!$erros) {
        $dados = [$produto['nome'], $produto['descricao'], $produto['peso_kg'], $produto['valor']];

        if ($id) {
            $dados[] = $id;
            $pdo->prepare('UPDATE produtos SET nome = ?, descricao = ?, peso_kg = ?, valor = ? WHERE id = ?')
                ->execute($dados);
            flash('success', 'Produto atualizado com sucesso!');
        } else {
            $pdo->prepare('INSERT INTO produtos (nome, descricao, peso_kg, valor) VALUES (?, ?, ?, ?)')
                ->execute($dados);
            flash('success', 'Produto cadastrado com sucesso!');
        }
        redirecionar('produtos/index.php');
    }
}

$titulo = $id ? 'Editar produto' : 'Novo produto';
$menu   = 'produtos';
require __DIR__ . '/../includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Produtos</a></li>
        <li class="breadcrumb-item active"><?= $titulo ?></li>
    </ol>
</nav>

<div class="card shadow-sm form-card">
    <div class="card-header bg-white"><h1 class="h4 mb-0"><?= $titulo ?></h1></div>
    <div class="card-body">
        <?php if ($erros): ?>
            <div class="alert alert-danger"><ul class="mb-0">
                <?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?>
            </ul></div>
        <?php endif; ?>

        <form method="post" class="row g-3">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="col-12">
                <label for="nome" class="form-label">Nome *</label>
                <input type="text" id="nome" name="nome" class="form-control" maxlength="100" required
                       value="<?= e($produto['nome']) ?>" placeholder="Ex.: Notebook Dell Inspiron">
            </div>
            <div class="col-12">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" id="descricao" name="descricao" class="form-control" maxlength="255"
                       value="<?= e($produto['descricao']) ?>" placeholder="Detalhes do produto">
            </div>
            <div class="col-sm-6">
                <label for="peso_kg" class="form-label">Peso (kg) *</label>
                <input type="number" id="peso_kg" name="peso_kg" class="form-control" step="0.01" min="0.01" required
                       value="<?= e($produto['peso_kg']) ?>">
            </div>
            <div class="col-sm-6">
                <label for="valor" class="form-label">Valor unitário (R$) *</label>
                <input type="number" id="valor" name="valor" class="form-control" step="0.01" min="0" required
                       value="<?= e($produto['valor']) ?>">
            </div>
            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
