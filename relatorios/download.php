<?php
/**
 * Gera o arquivo CSV com os fretes do dia para download.
 * O arquivo usa ";" como separador e BOM UTF-8 para abrir corretamente no Excel em português.
 */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/relatorio.php';

$data = $_GET['data'] ?? date('Y-m-d');
if (!DateTime::createFromFormat('Y-m-d', $data)) {
    exit('Data inválida.');
}

$fretes = fretesDoDia($data);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="fretes_' . $data . '.csv"');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF"); // BOM para o Excel reconhecer acentos

fputcsv($saida, ['Relatório de fretes - ' . dataBr($data)], ';');
fputcsv($saida, [], ';');
fputcsv($saida, ['Nº', 'Data', 'Transportador', 'Placa', 'Origem', 'Destino', 'Produtos',
                 'Peso total (kg)', 'Valor da carga (R$)', 'Valor do frete (R$)', 'Status', 'Observação'], ';');

$totalFrete = $totalPeso = 0;
foreach ($fretes as $f) {
    fputcsv($saida, [
        $f['numero'],
        dataBr($f['data_frete']),
        $f['transportador'],
        $f['placa'],
        $f['origem'] . ' (' . $f['origem_cidade'] . '/' . $f['origem_uf'] . ')',
        $f['destino'] . ' (' . $f['destino_cidade'] . '/' . $f['destino_uf'] . ')',
        $f['produtos'],
        numeroBr($f['peso_total']),
        numeroBr($f['valor_carga']),
        numeroBr($f['valor_frete']),
        $f['status'],
        $f['observacao'],
    ], ';');
    $totalFrete += $f['valor_frete'];
    $totalPeso  += $f['peso_total'];
}

fputcsv($saida, [], ';');
fputcsv($saida, ['', '', '', '', '', '', 'TOTAL (' . count($fretes) . ' fretes)', numeroBr($totalPeso), '', numeroBr($totalFrete)], ';');
fclose($saida);
