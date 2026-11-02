
<?php
require_once __DIR__ . "/config/conexao.php";

$erro = "";
$mensagem = "";
$produtoEditar = null;

// CADASTRAR OU ATUALIZAR PRODUTO
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $peso = str_replace(",", ".", trim($_POST["peso"] ?? ""));
    $valor = str_replace(",", ".", trim($_POST["valor"] ?? ""));

    if (
        $nome === "" ||
        !is_numeric($peso) ||
        !is_numeric($valor) ||
        (float)$peso <= 0 ||
        (float)$valor < 0
    ) {
        $erro = "Preencha os campos corretamente.";
    } else {
        try {
            if ($id) {
                $sql = "UPDATE produtos
                        SET nome = ?, descricao = ?, peso = ?, valor = ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $descricao, $peso, $valor, $id]);
            } else {
                $sql = "INSERT INTO produtos
                        (nome, descricao, peso, valor)
                        VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $descricao, $peso, $valor]);
            }

            header("Location: produtos.php?sucesso=1");
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erro = "Erro ao salvar o produto.";
        }
    }
}

// BUSCAR PRODUTO PARA EDIÇÃO
if (isset($_GET["editar"])) {
    $idEditar = filter_input(INPUT_GET, "editar", FILTER_VALIDATE_INT);

    if ($idEditar) {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$idEditar]);
        $produtoEditar = $stmt->fetch();
    }
}

// LISTAR PRODUTOS
$stmt = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
$produtos = $stmt->fetchAll();

if (isset($_GET["sucesso"])) {
    $mensagem = "Produto salvo com sucesso!";
}

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Gerenciador Logístico</title>

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

    <h2>Cadastro de Produtos</h2>
    <p class="text-muted">
        Cadastre e gerencie os produtos do sistema.
    </p>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= escapar($erro) ?>
        </div>
    <?php endif; ?>

    <?php if ($mensagem): ?>
        <div class="alert alert-success">
            <?= escapar($mensagem) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <h5>
                <?= $produtoEditar ? "Editar Produto" : "Novo Produto" ?>
            </h5>

            <form method="POST" action="produtos.php">

                <input type="hidden" name="id"
                    value="<?= escapar($produtoEditar["id"] ?? "") ?>">

                <div class="mb-3">
                    <label class="form-label">Nome do Produto</label>
                    <input type="text" name="nome"
                        class="form-control"
                        maxlength="100"
                        required
                        value="<?= escapar($produtoEditar["nome"] ?? "") ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao"
                        class="form-control"
                        rows="3"><?= escapar($produtoEditar["descricao"] ?? "") ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Peso (kg)</label>
                        <input type="number" name="peso"
                            class="form-control"
                            step="0.01" min="0.01" required
                            value="<?= escapar($produtoEditar["peso"] ?? "") ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valor (R$)</label>
                        <input type="number" name="valor"
                            class="form-control"
                            step="0.01" min="0" required
                            value="<?= escapar($produtoEditar["valor"] ?? "") ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    Salvar Produto
                </button>

                <a href="produtos.php" class="btn btn-secondary">
                    Limpar
                </a>
            </form>
        </div>
    </div>

    <h4>Produtos Cadastrados</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Peso</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?= escapar($produto["id"]) ?></td>
                    <td><?= escapar($produto["nome"]) ?></td>
                    <td><?= escapar($produto["descricao"]) ?></td>
                    <td><?= escapar($produto["peso"]) ?> kg</td>
                    <td>
                        R$ <?= number_format((float)$produto["valor"], 2, ",", ".") ?>
                    </td>
                    <td>
                        <a href="produtos.php?editar=<?= (int)$produto["id"] ?>"
                           class="btn btn-warning btn-sm">
                            Editar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($produtos)): ?>
                <tr>
                    <td colspan="6" class="text-center">
                        Nenhum produto cadastrado.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
