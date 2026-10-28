<?php
/**
 * Cadastro e edição de endereço (origem ou destino).
 * O preenchimento automático pelo CEP é feito em JavaScript (assets/js/app.js) usando a API ViaCEP.
 */
require __DIR__ . '/../includes/init.php';

$pdo = conectar();
$id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$campos   = ['descricao', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
$endereco = array_fill_keys($campos, '');
$erros    = [];

$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];

if ($id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM enderecos WHERE id = ?');
    $stmt->execute([$id]);
    $endereco = $stmt->fetch();
    if (!$endereco) {
        flash('danger', 'Endereço não encontrado.');
        redirecionar('enderecos/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($campos as $c) {
        $endereco[$c] = trim($_POST[$c] ?? '');
    }
    $endereco['cep'] = soDigitos($endereco['cep']);
    $endereco['uf']  = strtoupper($endereco['uf']);

    if ($endereco['descricao'] === '')    $erros[] = 'Informe uma descrição para o endereço.';
    if (strlen($endereco['cep']) !== 8)   $erros[] = 'O CEP deve ter 8 dígitos.';
    foreach (['logradouro' => 'o logradouro', 'numero' => 'o número', 'bairro' => 'o bairro', 'cidade' => 'a cidade'] as $c => $nome) {
        if ($endereco[$c] === '') $erros[] = "Informe $nome.";
    }
    if (!in_array($endereco['uf'], $ufs, true)) $erros[] = 'Selecione uma UF válida.';

    if (!$erros) {
        $dados = array_map(fn($c) => $endereco[$c] === '' && $c === 'complemento' ? null : $endereco[$c], $campos);

        if ($id) {
            $dados[] = $id;
            $pdo->prepare('UPDATE enderecos SET descricao = ?, cep = ?, logradouro = ?, numero = ?, complemento = ?,
                           bairro = ?, cidade = ?, uf = ? WHERE id = ?')->execute($dados);
            flash('success', 'Endereço atualizado com sucesso!');
        } else {
            $pdo->prepare('INSERT INTO enderecos (descricao, cep, logradouro, numero, complemento, bairro, cidade, uf)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute($dados);
            flash('success', 'Endereço cadastrado com sucesso!');
        }
        redirecionar('enderecos/index.php');
    }
}

$titulo = $id ? 'Editar endereço' : 'Novo endereço';
$menu   = 'enderecos';
require __DIR__ . '/../includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Endereços</a></li>
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
                <label for="descricao" class="form-label">Descrição *</label>
                <input type="text" id="descricao" name="descricao" class="form-control" maxlength="100" required
                       value="<?= e($endereco['descricao']) ?>" placeholder="Ex.: Centro de Distribuição Cascavel">
            </div>
            <div class="col-sm-4">
                <label for="cep" class="form-label">CEP *</label>
                <input type="text" id="cep" name="cep" class="form-control" maxlength="9" required data-mascara="cep"
                       value="<?= e(formatarCep($endereco['cep'])) ?>" placeholder="00000-000">
                <div class="form-text" id="cep-status">Digite o CEP para preencher o endereço automaticamente.</div>
            </div>
            <div class="col-sm-8">
                <label for="logradouro" class="form-label">Logradouro *</label>
                <input type="text" id="logradouro" name="logradouro" class="form-control" maxlength="150" required
                       value="<?= e($endereco['logradouro']) ?>" placeholder="Rua, avenida...">
            </div>
            <div class="col-sm-3">
                <label for="numero" class="form-label">Número *</label>
                <input type="text" id="numero" name="numero" class="form-control" maxlength="10" required
                       value="<?= e($endereco['numero']) ?>">
            </div>
            <div class="col-sm-9">
                <label for="complemento" class="form-label">Complemento</label>
                <input type="text" id="complemento" name="complemento" class="form-control" maxlength="100"
                       value="<?= e($endereco['complemento']) ?>" placeholder="Galpão, sala, bloco...">
            </div>
            <div class="col-sm-5">
                <label for="bairro" class="form-label">Bairro *</label>
                <input type="text" id="bairro" name="bairro" class="form-control" maxlength="100" required
                       value="<?= e($endereco['bairro']) ?>">
            </div>
            <div class="col-sm-5">
                <label for="cidade" class="form-label">Cidade *</label>
                <input type="text" id="cidade" name="cidade" class="form-control" maxlength="100" required
                       value="<?= e($endereco['cidade']) ?>">
            </div>
            <div class="col-sm-2">
                <label for="uf" class="form-label">UF *</label>
                <select id="uf" name="uf" class="form-select" required>
                    <option value="">--</option>
                    <?php foreach ($ufs as $uf): ?>
                        <option <?= $endereco['uf'] === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
