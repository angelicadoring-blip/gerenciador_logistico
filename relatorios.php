
<?php
require_once __DIR__ . "/config/conexao.php";

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$dataSelecionada = $_GET["data"] ?? date("Y-m-d");

$data = DateTimeImmutable::createFromFormat(
    "!Y-m-d", $dataSelecionada
);

$dataValida = $data &&
    $data->format("Y-m-d") === $dataSelecionada;

$fretes = [];
$erro = "";

if (!$dataValida) {
    $erro = "Selecione uma data válida.";
} else {
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
}

$total = array_sum(array_column($fretes, "valor_frete"));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Gerenciador Logístico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Gerenciador Logístico
        </a>
        <a href="index.php" class="btn btn-light btn-sm">
            Voltar ao início
        </a>
    </div>
</nav>

<div class="container mt-4">

    <h2>Relatório de Fretes</h2>
    <p class="text-muted">
        Consulte e baixe os fretes de uma determinada data.
    </p>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form method="GET" action="relatorios.php">
                <div class="row align-items-end">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Selecione a Data
                        </label>
                        <input type="date" name="data"
                               class="form-control"
                               value="<?= escapar($dataSelecionada) ?>"
                               required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <button type="submit"
                                class="btn btn-primary w-100">
                            Consultar
                        </button>
                    </div>

                    <div class="col-md-3 mb-3">
                        <?php if ($dataValida): ?>
                            <a href="baixar_relatorio.php?data=<?= urlencode($dataSelecionada) ?>"
                               class="btn btn-success w-100">
                                Baixar CSV
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= escapar($erro) ?>
        </div>
    <?php else: ?>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Total de Fretes</h6>
                        <h3><?= count($fretes) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Valor Total dos Fretes</h6>
                        <h3>
                            R$ <?= number_format(
                                $total, 2, ",", "."
                            ) ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <h4>Fretes Encontrados</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Transportador</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Produtos</th>
                        <th>Valor</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($fretes as $f): ?>
                    <tr>
                        <td><?= escapar($f["id"]) ?></td>
                        <td>
                            <?= date(
                                "d/m/Y",
                                strtotime($f["data_frete"])
                            ) ?>
                        </td>
                        <td><?= escapar($f["transportador"]) ?></td>
                        <td><?= escapar($f["origem"]) ?></td>
                        <td><?= escapar($f["destino"]) ?></td>
                        <td><?= escapar($f["produtos"]) ?></td>
                        <td>
                            R$ <?= number_format(
                                $f["valor_frete"], 2, ",", "."
                            ) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($fretes)): ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            Nenhum frete encontrado nesta data.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
