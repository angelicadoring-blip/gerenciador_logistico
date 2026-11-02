
<?php
require_once __DIR__ . "/config/conexao.php";

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$erro = "";

// CARREGAR DADOS CADASTRADOS
$produtos = $pdo->query(
    "SELECT id, nome FROM produtos ORDER BY nome"
)->fetchAll();

$enderecos = $pdo->query(
    "SELECT * FROM enderecos ORDER BY cidade"
)->fetchAll();

$transportadores = $pdo->query(
    "SELECT id, nome FROM transportadores ORDER BY nome"
)->fetchAll();

// SALVAR FRETE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $transportadorId = filter_input(
        INPUT_POST, "transportador_id", FILTER_VALIDATE_INT
    );
    $origemId = filter_input(
        INPUT_POST, "origem_id", FILTER_VALIDATE_INT
    );
    $destinoId = filter_input(
        INPUT_POST, "destino_id", FILTER_VALIDATE_INT
    );
    $dataFrete = $_POST["data_frete"] ?? "";
    $valorFrete = str_replace(
        ",", ".", trim($_POST["valor_frete"] ?? "")
    );

    $produtosIds = $_POST["produto_id"] ?? [];
    $quantidades = $_POST["quantidade"] ?? [];

    $data = DateTimeImmutable::createFromFormat(
        "!Y-m-d", $dataFrete
    );

    if (
        !$transportadorId ||
        !$origemId ||
        !$destinoId ||
        $origemId === $destinoId ||
        !$data ||
        $data->format("Y-m-d") !== $dataFrete ||
        !is_numeric($valorFrete) ||
        (float)$valorFrete < 0 ||
        !is_array($produtosIds) ||
        !is_array($quantidades) ||
        count($produtosIds) === 0 ||
        count($produtosIds) !== count($quantidades)
    ) {
        $erro = "Verifique os dados do frete.";
    } else {
        $itens = [];

        foreach ($produtosIds as $indice => $produtoId) {
            $produtoId = filter_var(
                $produtoId, FILTER_VALIDATE_INT
            );
            $quantidade = filter_var(
                $quantidades[$indice], FILTER_VALIDATE_INT
            );

            if (!$produtoId || !$quantidade || $quantidade < 1) {
                $erro = "Informe produtos e quantidades válidos.";
                break;
            }

            if (isset($itens[$produtoId])) {
                $erro = "Não selecione o mesmo produto duas vezes.";
                break;
            }

            $itens[$produtoId] = $quantidade;
        }

        if (!$erro) {
            try {
                $pdo->beginTransaction();

                $sql = "INSERT INTO fretes
                    (transportador_id, origem_id, destino_id,
                     data_frete, valor_frete)
                    VALUES (?, ?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $transportadorId,
                    $origemId,
                    $destinoId,
                    $dataFrete,
                    $valorFrete
                ]);

                $freteId = $pdo->lastInsertId();

                $stmtItem = $pdo->prepare(
                    "INSERT INTO frete_produtos
                     (frete_id, produto_id, quantidade)
                     VALUES (?, ?, ?)"
                );

                foreach ($itens as $produtoId => $quantidade) {
                    $stmtItem->execute([
                        $freteId, $produtoId, $quantidade
                    ]);
                }

                $pdo->commit();

                header("Location: fretes.php?sucesso=1");
                exit;

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log($e->getMessage());
                $erro = "Não foi possível salvar o frete.";
            }
        }
    }
}

// LISTAR FRETES
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
        GROUP BY f.id, f.data_frete, f.valor_frete,
                 t.nome, eo.cidade, ed.cidade
        ORDER BY f.id DESC";

$fretes = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Fretes - Gerenciador Logístico</title>

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
    <h2>Gerenciamento de Fretes</h2>
    <p class="text-muted">
        Crie fretes com vários produtos.
    </p>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= escapar($erro) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["sucesso"])): ?>
        <div class="alert alert-success">
            Frete cadastrado com sucesso!
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5>Novo Frete</h5>

            <form method="POST" id="formFrete">

                <div class="mb-3">
                    <label class="form-label">Transportador</label>
                    <select name="transportador_id"
                            class="form-select" required>
                        <option value="">Selecione</option>
                        <?php foreach ($transportadores as $t): ?>
                            <option value="<?= (int)$t["id"] ?>">
                                <?= escapar($t["nome"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Origem</label>
                        <select name="origem_id"
                                class="form-select" required>
                            <option value="">Selecione</option>
                            <?php foreach ($enderecos as $e): ?>
                                <option value="<?= (int)$e["id"] ?>">
                                    <?= escapar(
                                        $e["cidade"] . " - " .
                                        $e["logradouro"] . ", " .
                                        $e["numero"]
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Destino</label>
                        <select name="destino_id"
                                class="form-select" required>
                            <option value="">Selecione</option>
                            <?php foreach ($enderecos as $e): ?>
                                <option value="<?= (int)$e["id"] ?>">
                                    <?= escapar(
                                        $e["cidade"] . " - " .
                                        $e["logradouro"] . ", " .
                                        $e["numero"]
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Data do Frete</label>
                        <input type="date" name="data_frete"
                               class="form-control"
                               value="<?= date("Y-m-d") ?>"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valor do Frete (R$)</label>
                        <input type="number" name="valor_frete"
                               class="form-control"
                               min="0" step="0.01" required>
                    </div>
                </div>

                <h5>Produtos do Frete</h5>

                <div id="listaProdutos">
                    <div class="row g-2 mb-2 item-produto">
                        <div class="col-md-7">
                            <select name="produto_id[]"
                                    class="form-select" required>
                                <option value="">Selecione o produto</option>
                                <?php foreach ($produtos as $p): ?>
                                    <option value="<?= (int)$p["id"] ?>">
                                        <?= escapar($p["nome"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <input type="number" name="quantidade[]"
                                   class="form-control"
                                   min="1" value="1" required>
                        </div>

                        <div class="col-md-2">
                            <button type="button"
                                    class="btn btn-outline-danger w-100"
                                    onclick="removerProduto(this)">
                                Remover
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-primary"
                        onclick="adicionarProduto()">
                    + Adicionar Produto
                </button>

                <hr>

                <button type="submit" class="btn btn-success">
                    Gerar Frete
                </button>
            </form>
        </div>
    </div>

    <h4>Fretes Cadastrados</h4>

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
                    <td><?= date("d/m/Y", strtotime($f["data_frete"])) ?></td>
                    <td><?= escapar($f["transportador"]) ?></td>
                    <td><?= escapar($f["origem"]) ?></td>
                    <td><?= escapar($f["destino"]) ?></td>
                    <td><?= escapar($f["produtos"]) ?></td>
                    <td>
                        R$ <?= number_format(
                            (float)$f["valor_frete"], 2, ",", "."
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($fretes)): ?>
                <tr>
                    <td colspan="7" class="text-center">
                        Nenhum frete cadastrado.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function adicionarProduto() {
    const lista = document.getElementById("listaProdutos");
    const modelo = lista.querySelector(".item-produto");
    const novo = modelo.cloneNode(true);

    novo.querySelector("select").value = "";
    novo.querySelector('input[name="quantidade[]"]').value = 1;

    lista.appendChild(novo);
}

function removerProduto(botao) {
    const lista = document.getElementById("listaProdutos");

    if (lista.querySelectorAll(".item-produto").length > 1) {
        botao.closest(".item-produto").remove();
    } else {
        alert("O frete precisa ter pelo menos um produto.");
    }
}

document.getElementById("formFrete").addEventListener("submit", function(event) {
    const origem = this.querySelector('[name="origem_id"]').value;
    const destino = this.querySelector('[name="destino_id"]').value;

    if (origem === destino) {
        event.preventDefault();
        alert("Origem e destino devem ser diferentes.");
    }
});
</script>

</body>
</html>
