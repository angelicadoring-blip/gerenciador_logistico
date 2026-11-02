
<?php
require_once __DIR__ . "/config/conexao.php";

$dataSelecionada = $_GET["data"] ?? "";

$data = DateTimeImmutable::createFromFormat(
    "!Y-m-d", $dataSelecionada
);

if (!$data || $data->format("Y-m-d") !== $dataSelecionada) {
    http_response_code(400);
    exit("Data inválida.");
}

$sql = "SELECT f.id, f.data_frete, f.valor_frete,
               t.nome AS transportador,
               eo.cidade AS origem,
               ed.cidade AS destino,
               GROUP_CONCAT(
                   CONCAT(p.nome, ' (', fp.quantidade, ')')
                   ORDER BY p.nome SEPARATOR ', '
               ) AS produtos
        FROM fretes f
        INNER JOIN transportadores t
            ON t.id = f.transportador_id
        INNER JOIN enderecos eo
            ON eo.id = f.origem_id
        INNER JOIN enderecos ed
            ON ed.id = f.destino_id
        LEFT JOIN frete_produtos fp
            ON fp.frete_id = f.id
        LEFT JOIN produtos p
            ON p.id = fp.produto_id
        WHERE f.data_frete = ?
        GROUP BY f.id, f.data_frete, f.valor_frete,
                 t.nome, eo.cidade, ed.cidade
        ORDER BY f.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$dataSelecionada]);
$fretes = $stmt->fetchAll();

$nomeArquivo = "relatorio_fretes_" . $dataSelecionada . ".csv";

header("Content-Type: text/csv; charset=UTF-8");
header(
    'Content-Disposition: attachment; filename="' .
    $nomeArquivo . '"'
);

$saida = fopen("php://output", "w");

// BOM para compatibilidade com Excel e WPS
fwrite($saida, "\xEF\xBB\xBF");

fputcsv($saida, [
    "ID", "Data", "Transportador", "Origem",
    "Destino", "Produtos", "Valor do Frete"
], ";", '"', "");

$total = 0;

foreach ($fretes as $frete) {
    $total += (float)$frete["valor_frete"];

    // Evita interpretação de textos como fórmulas
    // por aplicativos de planilhas.
    $seguro = function ($valor) {
        $texto = (string)($valor ?? "");
        if (preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $texto)) {
            return "'" . $texto;
        }
        return $texto;
    };

    fputcsv($saida, [
        $frete["id"],
        date("d/m/Y", strtotime($frete["data_frete"])),
        $seguro($frete["transportador"]),
        $seguro($frete["origem"]),
        $seguro($frete["destino"]),
        $seguro($frete["produtos"]),
        number_format(
            (float)$frete["valor_frete"], 2, ",", "."
        )
    ], ";", '"', "");
}

fputcsv($saida, [
    "", "", "", "", "", "TOTAL",
    number_format($total, 2, ",", ".")
], ";", '"', "");

fclose($saida);
exit;
