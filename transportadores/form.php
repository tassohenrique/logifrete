<?php
/**
 * Cadastro e edição de transportador.
 */
require __DIR__ . '/../includes/init.php';

$pdo = conectar();
$id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$campos        = ['nome', 'cpf_cnpj', 'telefone', 'email', 'veiculo', 'placa'];
$transportador = array_fill_keys($campos, '');
$erros         = [];

if ($id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM transportadores WHERE id = ?');
    $stmt->execute([$id]);
    $transportador = $stmt->fetch();
    if (!$transportador) {
        flash('danger', 'Transportador não encontrado.');
        redirecionar('transportadores/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($campos as $c) {
        $transportador[$c] = trim($_POST[$c] ?? '');
    }
    // Transformação dos dados antes de salvar: CPF/CNPJ e telefone só com números, placa em maiúsculas
    $transportador['cpf_cnpj'] = soDigitos($transportador['cpf_cnpj']);
    $transportador['telefone'] = soDigitos($transportador['telefone']);
    $transportador['placa']    = strtoupper(str_replace('-', '', $transportador['placa']));

    if ($transportador['nome'] === '') $erros[] = 'Informe o nome.';
    if (!in_array(strlen($transportador['cpf_cnpj']), [11, 14], true)) $erros[] = 'O CPF deve ter 11 dígitos ou o CNPJ 14 dígitos.';
    if ($transportador['email'] !== '' && !filter_var($transportador['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if ($transportador['veiculo'] === '') $erros[] = 'Informe o veículo.';
    if (!preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $transportador['placa'])) $erros[] = 'Placa inválida (ex.: ABC1234 ou ABC1D23).';

    if (!$erros) {
        $dados = array_map(fn($c) => $transportador[$c] === '' ? null : $transportador[$c], $campos);

        if ($id) {
            $dados[] = $id;
            $pdo->prepare('UPDATE transportadores SET nome = ?, cpf_cnpj = ?, telefone = ?, email = ?, veiculo = ?, placa = ?
                           WHERE id = ?')->execute($dados);
            flash('success', 'Transportador atualizado com sucesso!');
        } else {
            $pdo->prepare('INSERT INTO transportadores (nome, cpf_cnpj, telefone, email, veiculo, placa)
                           VALUES (?, ?, ?, ?, ?, ?)')->execute($dados);
            flash('success', 'Transportador cadastrado com sucesso!');
        }
        redirecionar('transportadores/index.php');
    }
}

$titulo = $id ? 'Editar transportador' : 'Novo transportador';
$menu   = 'transportadores';
require __DIR__ . '/../includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Transportadores</a></li>
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

            <div class="col-md-8">
                <label for="nome" class="form-label">Nome / Razão social *</label>
                <input type="text" id="nome" name="nome" class="form-control" maxlength="100" required
                       value="<?= e($transportador['nome']) ?>">
            </div>
            <div class="col-md-4">
                <label for="cpf_cnpj" class="form-label">CPF ou CNPJ *</label>
                <input type="text" id="cpf_cnpj" name="cpf_cnpj" class="form-control" maxlength="18" required data-mascara="cpfcnpj"
                       value="<?= e(formatarCpfCnpj($transportador['cpf_cnpj'])) ?>" placeholder="Somente números">
            </div>
            <div class="col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" id="telefone" name="telefone" class="form-control" maxlength="15" data-mascara="telefone"
                       value="<?= e(formatarTelefone($transportador['telefone'])) ?>" placeholder="(45) 99999-9999">
            </div>
            <div class="col-md-8">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" maxlength="100"
                       value="<?= e($transportador['email']) ?>" placeholder="contato@empresa.com.br">
            </div>
            <div class="col-md-8">
                <label for="veiculo" class="form-label">Veículo *</label>
                <input type="text" id="veiculo" name="veiculo" class="form-control" maxlength="50" required
                       value="<?= e($transportador['veiculo']) ?>" placeholder="Ex.: Caminhão Truck, Van, Carreta">
            </div>
            <div class="col-md-4">
                <label for="placa" class="form-label">Placa *</label>
                <input type="text" id="placa" name="placa" class="form-control text-uppercase" maxlength="8" required
                       value="<?= e($transportador['placa']) ?>" placeholder="ABC1D23">
            </div>
            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
