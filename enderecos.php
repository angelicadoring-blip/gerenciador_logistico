
<?php
require_once __DIR__ . "/config/conexao.php";

$erro = "";
$mensagem = "";
$enderecoEditar = null;

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

// CADASTRAR OU ATUALIZAR ENDEREÇO
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $logradouro = trim($_POST["logradouro"] ?? "");
    $numero = trim($_POST["numero"] ?? "");
    $bairro = trim($_POST["bairro"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");
    $estado = strtoupper(trim($_POST["estado"] ?? ""));
    $cep = trim($_POST["cep"] ?? "");

    if (
        $logradouro === "" ||
        $numero === "" ||
        $bairro === "" ||
        $cidade === "" ||
        !preg_match('/^[A-Z]{2}$/', $estado) ||
        !preg_match('/^\d{5}-?\d{3}$/', $cep)
    ) {
        $erro = "Preencha os campos corretamente.";
    } else {
        try {
            if ($id) {
                $sql = "UPDATE enderecos
                        SET logradouro=?, numero=?, bairro=?,
                            cidade=?, estado=?, cep=?
                        WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $logradouro, $numero, $bairro,
                    $cidade, $estado, $cep, $id
                ]);
            } else {
                $sql = "INSERT INTO enderecos
                        (logradouro, numero, bairro, cidade, estado, cep)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $logradouro, $numero, $bairro,
                    $cidade, $estado, $cep
                ]);
            }

            header("Location: enderecos.php?sucesso=1");
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erro = "Erro ao salvar o endereço.";
        }
    }
}

// BUSCAR ENDEREÇO PARA EDIÇÃO
if (isset($_GET["editar"])) {
    $idEditar = filter_input(INPUT_GET, "editar", FILTER_VALIDATE_INT);

    if ($idEditar) {
        $stmt = $pdo->prepare("SELECT * FROM enderecos WHERE id = ?");
        $stmt->execute([$idEditar]);
        $enderecoEditar = $stmt->fetch();
    }
}

// LISTAR ENDEREÇOS
$stmt = $pdo->query("SELECT * FROM enderecos ORDER BY id DESC");
$enderecos = $stmt->fetchAll();

if (isset($_GET["sucesso"])) {
    $mensagem = "Endereço salvo com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Endereços - Gerenciador Logístico</title>

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

    <h2>Cadastro de Endereços</h2>
    <p class="text-muted">
        Cadastre endereços para origem e destino dos fretes.
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
                <?= $enderecoEditar ? "Editar Endereço" : "Novo Endereço" ?>
            </h5>

            <form method="POST" action="enderecos.php">

                <input type="hidden" name="id"
                    value="<?= escapar($enderecoEditar["id"] ?? "") ?>">

                <div class="mb-3">
                    <label class="form-label">Logradouro (Rua/Avenida)</label>
                    <input type="text" name="logradouro"
                        class="form-control" maxlength="150" required
                        value="<?= escapar($enderecoEditar["logradouro"] ?? "") ?>">
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Número</label>
                        <input type="text" name="numero"
                            class="form-control" maxlength="20" required
                            value="<?= escapar($enderecoEditar["numero"] ?? "") ?>">
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="bairro"
                            class="form-control" maxlength="100" required
                            value="<?= escapar($enderecoEditar["bairro"] ?? "") ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="cidade"
                            class="form-control" maxlength="100" required
                            value="<?= escapar($enderecoEditar["cidade"] ?? "") ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Estado (UF)</label>
                        <input type="text" name="estado"
                            class="form-control" maxlength="2"
                            placeholder="PR" required
                            value="<?= escapar($enderecoEditar["estado"] ?? "") ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">CEP</label>
                        <input type="text" name="cep"
                            class="form-control" maxlength="9"
                            placeholder="00000-000" required
                            value="<?= escapar($enderecoEditar["cep"] ?? "") ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    Salvar Endereço
                </button>

                <a href="enderecos.php" class="btn btn-secondary">
                    Limpar
                </a>
            </form>
        </div>
    </div>

    <h4>Endereços Cadastrados</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Logradouro</th>
                    <th>Número</th>
                    <th>Bairro</th>
                    <th>Cidade</th>
                    <th>UF</th>
                    <th>CEP</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($enderecos as $endereco): ?>
                <tr>
                    <td><?= escapar($endereco["id"]) ?></td>
                    <td><?= escapar($endereco["logradouro"]) ?></td>
                    <td><?= escapar($endereco["numero"]) ?></td>
                    <td><?= escapar($endereco["bairro"]) ?></td>
                    <td><?= escapar($endereco["cidade"]) ?></td>
                    <td><?= escapar($endereco["estado"]) ?></td>
                    <td><?= escapar($endereco["cep"]) ?></td>
                    <td>
                        <a href="enderecos.php?editar=<?= (int)$endereco["id"] ?>"
                           class="btn btn-warning btn-sm">
                            Editar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($enderecos)): ?>
                <tr>
                    <td colspan="8" class="text-center">
                        Nenhum endereço cadastrado.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
