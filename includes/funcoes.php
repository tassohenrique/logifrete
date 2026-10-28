<?php
/**
 * Funções auxiliares de formatação e transformação de dados (feitas em PHP).
 */

/** Escapa texto antes de exibir no HTML (evita XSS). */
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** URL base do sistema (funciona em qualquer pasta dentro do htdocs). */
function url(string $caminho = ''): string
{
    $raiz    = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $base    = rtrim(substr($raiz, strlen($docRoot)), '/');

    return $base . '/' . ltrim($caminho, '/');
}

function redirecionar(string $caminho): void
{
    header('Location: ' . url($caminho));
    exit;
}

/** Guarda uma mensagem para ser exibida na próxima página. */
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function mostrarFlash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert alert-' . e($f['tipo']) . ' alert-dismissible fade show" role="alert">'
           . e($f['mensagem'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button></div>';
        unset($_SESSION['flash']);
    }
}

/** Mantém apenas os números de um texto (CPF, CNPJ, CEP, telefone). */
function soDigitos(?string $texto): string
{
    return preg_replace('/\D/', '', (string) $texto);
}

/** 1234.5 => "R$ 1.234,50" */
function moeda($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

/** 1234.5 => "1.234,50" */
function numeroBr($valor, int $casas = 2): string
{
    return number_format((float) $valor, $casas, ',', '.');
}

/** "2026-10-28" => "28/10/2026" */
function dataBr(?string $data): string
{
    return $data ? date('d/m/Y', strtotime($data)) : '';
}

/** Formata CPF (11 dígitos) ou CNPJ (14 dígitos). */
function formatarCpfCnpj(?string $doc): string
{
    $d = soDigitos($doc);

    if (strlen($d) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d);
    }
    if (strlen($d) === 14) {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d);
    }
    return $d;
}

/** "85810010" => "85810-010" */
function formatarCep(?string $cep): string
{
    $d = soDigitos($cep);
    return strlen($d) === 8 ? substr($d, 0, 5) . '-' . substr($d, 5) : $d;
}

/** "45999990000" => "(45) 99999-0000" */
function formatarTelefone(?string $tel): string
{
    $d = soDigitos($tel);

    if (strlen($d) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $d);
    }
    if (strlen($d) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $d);
    }
    return $d;
}

/** Monta o endereço em uma linha. */
function enderecoCompleto(array $e): string
{
    $linha = $e['logradouro'] . ', ' . $e['numero'];
    if (!empty($e['complemento'])) {
        $linha .= ' - ' . $e['complemento'];
    }
    return $linha . ' - ' . $e['bairro'] . ', ' . $e['cidade'] . '/' . $e['uf'] . ' - CEP ' . formatarCep($e['cep']);
}

/** Lista de status possíveis de um frete. */
function statusFrete(): array
{
    return ['Pendente', 'Em trânsito', 'Entregue', 'Cancelado'];
}

/** Badge colorido do Bootstrap de acordo com o status. */
function badgeStatus(string $status): string
{
    $cores = [
        'Pendente'    => 'warning',
        'Em trânsito' => 'info',
        'Entregue'    => 'success',
        'Cancelado'   => 'secondary',
    ];
    $cor = $cores[$status] ?? 'secondary';
    return '<span class="badge text-bg-' . $cor . '">' . e($status) . '</span>';
}
