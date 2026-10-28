<?php
/**
 * Busca os fretes de um dia e já monta os dados transformados para o relatório.
 * Usado pela tela de relatório (relatorios/index.php) e pelo download (relatorios/download.php).
 */
function fretesDoDia(string $data): array
{
    $pdo = conectar();

    $stmt = $pdo->prepare('SELECT f.*, t.nome AS transportador, t.placa,
                                  o.descricao AS origem, o.cidade AS origem_cidade, o.uf AS origem_uf,
                                  d.descricao AS destino, d.cidade AS destino_cidade, d.uf AS destino_uf
                           FROM fretes f
                           JOIN transportadores t ON t.id = f.transportador_id
                           JOIN enderecos o       ON o.id = f.origem_id
                           JOIN enderecos d       ON d.id = f.destino_id
                           WHERE f.data_frete = ?
                           ORDER BY f.id');
    $stmt->execute([$data]);
    $fretes = $stmt->fetchAll();

    $stmtItens = $pdo->prepare('SELECT p.nome, p.peso_kg, p.valor, fp.quantidade
                                FROM frete_produtos fp JOIN produtos p ON p.id = fp.produto_id
                                WHERE fp.frete_id = ? ORDER BY fp.id');

    foreach ($fretes as &$f) {
        $stmtItens->execute([$f['id']]);
        $itens = $stmtItens->fetchAll();

        $f['numero']      = str_pad($f['id'], 4, '0', STR_PAD_LEFT);
        $f['itens']       = $itens;
        $f['peso_total']  = array_sum(array_map(fn($i) => $i['peso_kg'] * $i['quantidade'], $itens));
        $f['valor_carga'] = array_sum(array_map(fn($i) => $i['valor'] * $i['quantidade'], $itens));
        $f['produtos']    = implode(', ', array_map(fn($i) => $i['quantidade'] . 'x ' . $i['nome'], $itens));
    }
    unset($f);

    return $fretes;
}
