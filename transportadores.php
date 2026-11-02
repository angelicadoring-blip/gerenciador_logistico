
<?php
require_once __DIR__ . "/config/conexao.php";

$erro = "";
$mensagem = "";
$transportadorEditar = null;

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

// CADASTRAR OU ATUALIZAR TRANSPORTADOR
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $nome = trim($_POST["nome"] ?? "");
    $documento = trim($_POST["documento"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $veiculo = trim($_POST["veiculo"] ?? "");

    if ($nome === "") {
        $erro = "Informe o nome do transportador.";
    } else {
        try {
            if ($id) {
                $sql = "UPDATE transportadores
                        SET nome=?, documento=?, telefone=?, veiculo=?
                        WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nome, $documento, $telefone, $veiculo, $id
                ]);
            } else {
                $sql = "INSERT INTO transportadores
                        (nome, documento, telefone, veiculo)
                        VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nome, $documento, $telefone, $veiculo
                ]);
            }

            header("Location: transportadores.php?sucesso=1");
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erro = "Erro ao salvar o transportador.";
        }
    }
}

// BUSCAR TRANSPORTADOR PARA EDIÇÃO
if (isset($_GET["editar"])) {
    $idEditar = filter_input(INPUT_GET, "editar", FILTER_VALIDATE_INT);

    if ($idEditar) {
        $stmt = $pdo->prepare(
            "SELECT * FROM transportadores WHERE id = ?"
        );
        $stmt->execute([$idEditar]);
        $transportadorEditar = $stmt->fetch();
    }
}

// LISTAR TRANSPORTADORES
$stmt = $pdo->query(
    "SELECT * FROM transportadores ORDER BY id DESC"
);
$transportadores = $stmt->fetchAll();

if (isset($_GET["sucesso"])) {
    $mensagem = "Transportador salvo com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transportadores - Gerenciador Logístico</title>

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

    <h2>Cadastro de Transportadores</h2>
    <p class="text-muted">
        Cadastre e gerencie os transportadores do sistema.
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
                <?= $transportadorEditar
                    ? "Editar Transportador"
                    : "Novo Transportador" ?>
            </h5>

            <form method="POST" action="transportadores.php">

                <input type="hidden" name="id"
                    value="<?= escapar($transportadorEditar["id"] ?? "") ?>">

                <div class="mb-3">
                    <label class="form-label">
                        Nome do Transportador
                    </label>
                    <input type="text" name="nome"
                        class="form-control"
                        maxlength="150" required
                        value="<?= escapar($transportadorEditar["nome"] ?? "") ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">CPF ou CNPJ</label>
                    <input type="text" name="documento"
                        class="form-control"
                        maxlength="18"
                        placeholder="CPF ou CNPJ"
                        value="<?= escapar($transportadorEditar["documento"] ?? "") ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone"
                        class="form-control"
                        maxlength="20"
                        placeholder="(00) 00000-0000"
                        value="<?= escapar($transportadorEditar["telefone"] ?? "") ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Veículo</label>
                    <input type="text" name="veiculo"
                        class="form-control"
                        maxlength="100"
                        placeholder="Ex.: Caminhão Baú"
                        value="<?= escapar($transportadorEditar["veiculo"] ?? "") ?>">
                </div>

                <button type="submit" class="btn btn-success">
                    Salvar Transportador
                </button>

                <a href="transportadores.php" class="btn btn-secondary">
                    Limpar
                </a>
            </form>
        </div>
    </div>

    <h4>Transportadores Cadastrados</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CPF/CNPJ</th>
                    <th>Telefone</th>
                    <th>Veículo</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($transportadores as $transportador): ?>
                <tr>
                    <td><?= escapar($transportador["id"]) ?></td>
                    <td><?= escapar($transportador["nome"]) ?></td>
                    <td><?= escapar($transportador["documento"]) ?></td>
                    <td><?= escapar($transportador["telefone"]) ?></td>
                    <td><?= escapar($transportador["veiculo"]) ?></td>
                    <td>
                        <a href="transportadores.php?editar=<?= (int)$transportador["id"] ?>"
                           class="btn btn-warning btn-sm">
                            Editar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($transportadores)): ?>
                <tr>
                    <td colspan="6" class="text-center">
                        Nenhum transportador cadastrado.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
