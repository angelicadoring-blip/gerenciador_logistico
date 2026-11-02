# Gerenciador Logístico

**Instituição:** UNIPAR EAD  
**Acadêmica:** Angélica Doring  
**RA:** 09053255

## Descrição do projeto

O Gerenciador Logístico é um sistema web desenvolvido como atividade acadêmica, com o objetivo de gerenciar produtos, endereços, transportadores e fretes.

O sistema permite cadastrar e editar informações, gerar fretes contendo vários produtos e consultar relatórios por data.

## Tecnologias utilizadas

- PHP
- MySQL
- PDO
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- Git e GitHub

## Funcionalidades

- Cadastro e edição de produtos.
- Cadastro e edição de endereços de origem e destino.
- Cadastro e edição de transportadores.
- Geração de fretes com vários produtos e quantidades.
- Inclusão dinâmica de produtos utilizando JavaScript.
- Consulta de fretes por data.
- Download de relatórios em formato CSV.

## Estrutura do projeto

- `index.php` — Página inicial.
- `produtos.php` — Gerenciamento de produtos.
- `enderecos.php` — Gerenciamento de endereços.
- `transportadores.php` — Gerenciamento de transportadores.
- `fretes.php` — Geração de fretes.
- `relatorios.php` — Consulta de relatórios.
- `baixar_relatorio.php` — Download de relatórios CSV.
- `config/conexao.php` — Conexão com o banco de dados.

## Como executar

1. Instalar o XAMPP com PHP e MySQL.
2. Colocar a pasta do projeto dentro de `htdocs`.
3. Iniciar os serviços Apache e MySQL.
4. Criar o banco de dados `gerenciador_logistico` no phpMyAdmin e importar o arquivo SQL do projeto.
5. Configurar a conexão no arquivo `config/conexao.php`, conforme o ambiente local.
6. Acessar `http://localhost/gerenciador_logistico/`.

## Autoria

Angélica Doring  
RA: 09053255  
UNIPAR EAD
