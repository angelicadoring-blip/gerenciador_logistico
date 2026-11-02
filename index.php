
<?php
$titulo = "Gerenciador Logístico";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $titulo; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Gerenciador Logístico
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal"
                aria-expanded="false"
                aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="produtos.php">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="enderecos.php">Endereços</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="transportadores.php">Transportadores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="fretes.php">Fretes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="relatorios.php">Relatórios</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">

    <div class="text-center mb-5">
        <h1>Bem-vindo ao Gerenciador Logístico</h1>
        <p class="text-muted">
            Sistema de gerenciamento de produtos, endereços,
            transportadores e fretes.
        </p>
    </div>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Produtos</h5>
                    <p class="card-text">Cadastre e gerencie produtos.</p>
                    <a href="produtos.php" class="btn btn-primary">
                        Acessar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Endereços</h5>
                    <p class="card-text">Gerencie origens e destinos.</p>
                    <a href="enderecos.php" class="btn btn-primary">
                        Acessar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Transportadores</h5>
                    <p class="card-text">Cadastre os transportadores.</p>
                    <a href="transportadores.php" class="btn btn-primary">
                        Acessar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Fretes</h5>
                    <p class="card-text">Crie e acompanhe os fretes.</p>
                    <a href="fretes.php" class="btn btn-success">
                        Gerenciar Fretes
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Relatórios</h5>
                    <p class="card-text">Consulte os fretes por data.</p>
                    <a href="relatorios.php" class="btn btn-success">
                        Ver Relatórios
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<footer class="text-center mt-5 py-4 text-muted">
    <p>Gerenciador Logístico - UNIPAR EAD</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

