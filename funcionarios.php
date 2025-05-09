<?php
// Inicia a sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'protect.php'; // Protege a página para usuários autenticados
include 'config.php'; // Conexão com o banco de dados

// Verifica se o usuário está logado e tem um ID válido
if (!isset($_SESSION['id_adm'])) {
    echo "Erro: Usuário não autenticado.";
    exit;
}

// Verifica se o administrador está associado a uma empresa
if (!isset($_SESSION['id_empresa'])) {
    echo "<script>alert('Você precisa criar uma empresa antes de acessar esta página.'); window.location.href='Registro_adm.php';</script>";
    exit;
}

$empresa_id = $_SESSION['id_empresa']; // Recupera o id_empresa da sessão

// Configuração da paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Filtro de busca
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Consulta para contar o total de registros filtrados por empresa_id
$result_total = mysqli_query($conn, "SELECT COUNT(*) AS total FROM funcionario WHERE nome LIKE '%$search%' AND empresa_id = $empresa_id");
$total_registros = mysqli_fetch_assoc($result_total)['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta para recuperar os funcionários filtrados por empresa_id
$sql = "SELECT id_fun, num_mecanografico, nome, foto, bi, emissao_bi, validade_bi, data_nascimento, 
        pais, morada, genero, num_agregados, telemovel, email, estado, cargo, departamento, 
        tipo_trabalhador, num_ss, data_admissao 
        FROM funcionario 
        WHERE nome LIKE '%$search%' AND empresa_id = $empresa_id
        LIMIT $registros_por_pagina OFFSET $offset";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="all.css/registro3.css">
    <link rel="stylesheet" href="all.css/timer.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    .filters {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}

.filter-select {
    background-color: white;
    border: 1px solid #ddd;
    padding: 8px 15px;
    border-radius: 25px;
    color: #000;
    font-size: 14px;
    width: 180px;
}

.search-bar {
    flex-grow: 1;
    max-width: 300px;
    background-color: white;
    border: 1px solid #ddd;
    padding: 0;
    border-radius: 25px;
    display: flex;
    align-items: center;
    height: 40px; /* Altura fixa para reduzir a "grossura" vertical */
    position: relative; /* Para posicionamento das sugestões */
}

.search-bar form {
    display: flex;
    width: 100%;
    align-items: center;
    padding: 0 15px;
}

.search-bar input {
    border: none;
    background: transparent;
    width: 100%;
    outline: none;
    color: #000;
    font-size: 14px;
    height: 100%;
    padding: 0;
}

.search-bar button {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-left: 10px;
    display: flex;
    align-items: center;
}

.search-icon {
    color: #777;
}
/* Container de tabela com rolagem */
.table-container {
    width: 100%;
    overflow-x: auto;
    position: relative;
    background-color: white;
    border-radius: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

/* Barra de rolagem estilizada */
.table-container::-webkit-scrollbar {
    height: 10px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 0 0 8px 8px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #64c2a7;
    border-radius: 10px;
}

/* Tabela de funcionários */
.tabela-funcionarios {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.tabela-funcionarios th {
    background-color: rgb(255, 255, 255);
    color: #333;
    font-weight: 500;
    text-align: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
    position: sticky;
    top: 0;
    z-index: 10;
}

.tabela-funcionarios td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    color: #000;
}

.tabela-funcionarios tr:last-child td {
    border-bottom: none;
}

.tabela-funcionarios tr:hover {
    background-color: #f9f9f9;
}

th, td {
    padding: 10px;
    text-align: center;
    font-size: 15px;
    border-bottom: 1px solid #ccc; 
    border-right: 1px solid #ccc; 
}

tr:nth-child(odd) {
    background-color: #f7f7f7;
}


.tabela-funcionarios tbody tr:hover {
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); 
    border-left: 5px solid #64c2a7; 
    border-radius: 8px;
    transition: transform 0.2s ease, opacity 0.1s ease;
}

.status-ativo {
    color: #2e7d32;
    display: flex;
    align-items: center;
    gap: 5px;
}

.status-inativo {
    color: #ff8f00;
    display: flex;
    align-items: center;
    gap: 5px;
}

.status-terminado {
    color: #c62828;
}

.status-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    background-color: #64c2a7;
    border-radius: 50%;
}

.status-dot-yellow {
    background-color: #ffc107;
}

/* User avatar */
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #64c2a7;
    color: white;
    font-weight: 500;
}

/* Paginação */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    align-items: center;
    gap: 5px;
}

.pagination-item {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    color: #000;
}

.pagination-item.active {
    background-color: #64c2a7;
    color: white;
}

.suggestions-box {
    width: 100%;
    border: 1px solid #ccc;
    max-height: 200px;
    overflow-y: auto;
    position: absolute;
    top: 100%;
    left: 0;
    background-color: white;
    z-index: 1000;
    display: none;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 5px;
}

.suggestion-item {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item:hover {
    background-color: #f5f5f5;
}

/*Darkmode*/ 
body.dark {
    background-color: #1A1A1A;
    color: #e0e0e0;
}

body.dark .sidebar {
    background-color: #1E1E1E;
    border-right: 1px solid #333;
}

body.dark .nav-menu a {
    color: #b0b0b0;
}

body.dark .nav-menu a:hover,
body.dark .nav-menu a.active {
    color: #64c2a7;
    background-color: rgba(100, 194, 167, 0.1);
}

body.dark .nav-select {
    background-color: #2C2C2C;
    color: #e0e0e0;
    border-color: #444;
}

body.dark .main-content {
    background-color: #1A1A1A;
}



body.dark .page-title {
    color: #e0e0e0;
}

body.dark .filters {
    background-color: transparent;
}

body.dark .filter-select {
    background-color: #2C2C2C;
    color: #e0e0e0;
    border: 1px solid #444;
}

body.dark .search-bar {
    background-color: #2C2C2C;
    border: 1px solid #444;
}

body.dark .search-bar input {
    color: #e0e0e0;
}


body.dark .search-icon {
    color: #999;
}

body.dark .table-container {
    background-color: #1E1E1E;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

body.dark .tabela-funcionarios {
    background-color: #1E1E1E;
}

body.dark .tabela-funcionarios th {
    background-color: #2C2C2C;
    color: #e0e0e0;
    border-bottom: 1px solid #444;
}

body.dark .tabela-funcionarios td {
    color: #d0d0d0;
    border-bottom: 1px solid #333;
    border-right: 1px solid #333;
}

body.dark .tabela-funcionarios tr:nth-child(odd) {
    background-color: #222;
}

body.dark .tabela-funcionarios tr:hover {
    background-color: rgba(100, 194, 167, 0.1);
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
}

body.dark .status-ativo {
    color: #81c784;
}

body.dark .status-inativo {
    color: #ffb74d;
}

body.dark .status-terminado {
    color: #e57373;
}

body.dark .user-avatar {
    background-color: rgba(100, 194, 167, 0.2);
}

body.dark .pagination-item {
    color: #e0e0e0;
}

body.dark .pagination-item.active {
    background-color: #64c2a7;
    color: #121212;
}

body.dark .suggestions-box {
    background-color: #2C2C2C;
    border-color: #444;
}

body.dark .suggestion-item {
    border-bottom: 1px solid #333;
    color: #e0e0e0;
}

body.dark .suggestion-item:hover {
    background-color: rgba(100, 194, 167, 0.1);
}

body.dark ::-webkit-scrollbar-track {
    background: #2C2C2C;
}

body.dark ::-webkit-scrollbar-thumb {
    background: #64c2a7;
}
</style>
    <title>SAM - Funcionários</title>
</head>
<body>
<div class="sidebar">
        <div class="logo">
            <a href="UI.php">
                <img src="img/sam2logo-32.png" alt="SAM Logo">
            </a>
        </div>
        <select class="nav-select">
            <option>sam</option>
        </select>
        <ul class="nav-menu">           
            <a href="funcionarios.php"><li class="active">Funcionários</li></a>
            <a href="registro.php"><li>Novo Funcionário</li></a>
            <li>Processamento Salarial</li>
            <a href="docs.php"><li>Documentos</li></a>
            <a href="registro_ponto.php"><li>Registro de Ponto</li></a>
            <a href="ausencias.php"><li>Ausências</li></a>
            <a href="recrutamento.php"><li>Recrutamento</li></a>
        </ul>
    </div>

    <div class="main-content">
        <header class="header">
            <h1 class="page-title">Funcionários</h1>
            <div class="header-buttons">
                <div class="time" id="current-time"></div>
                <a class="exit-tag" href="logout.php">Sair</a>
                <a href="./configuracoes_sam/perfil_adm.php" class="perfil_img">                
                    <div class="user-profile">
                        <img src="icones/icons-sam-18.svg" alt="User" width="20">
                        <span><?php echo $_SESSION['nome']; ?></span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                 </a>
            </div>
        </header>

        <div class="filters">
        <select class="filter-select">
                <option value="" disabled selected>Selecione o departamento</option>
                            <option value="administrativo">Administrativo</option>
                            <option value="financeiro">Financeiro</option>
                            <option value="rh">Recursos Humanos</option>
                            <option value="tecnologia">Tecnologia da Informação</option>
                            <option value="marketing">Marketing</option>
                            <option value="vendas">Vendas</option>
                            <option value="juridico">Jurídico</option>
                            <option value="logistica">Logística</option>
                            <option value="operacional">Operacional</option>
            </select>
            <select class="filter-select">
                <option value="" disabled selected>Selecione o tipo de trabalhador</option>
                            <option value="efetivo">Trabalhador Efetivo</option>
                            <option value="temporario">Trabalhador Temporário</option>
                            <option value="estagiario">Trabalhador Estagiário</option>
                            <option value="autonomo">Trabalhador Autônomo</option>
                            <option value="freelancer">Trabalhador Freelancer</option>
                            <option value="terceirizado">Trabalhador Terceirizado</option>
                            <option value="intermitente">Trabalhador Intermitente</option>
                            <option value="voluntario">Trabalhador Voluntário</option>
            </select>

            <div class="search-bar">
    <form method="GET" action="funcionarios.php">
        <input type="text" name="search" id="search-input" placeholder="Pesquisar..." autocomplete="off">
        <button type="submit"><i class="fas fa-search search-icon"></i></button>
    </form>
    <div id="suggestions" class="suggestions-box"></div>
</div>
        </div>
        
        <div class="table-container">
            <table class="tabela-funcionarios">
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>nº</th>
                        <th>email</th>
                        <th>data</th>
                        <th>Estado</th>
                        <th>BI</th>
                        <th>Emissão BI</th>
                        <th>Validade BI</th>
                        <th>País</th>
                        <th>Morada</th>
                        <th>Gênero</th>
                        <th>Nº Agregados</th>
                        <th>Telemóvel</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Tipo Trabalhador</th>
                        <th>Nº Segurança Social</th>
                        <th>Data Admissão</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { 
                        // Formatar as datas
                        $data_nascimento = !empty($row['data_nascimento']) ? date('d/m/Y', strtotime($row['data_nascimento'])) : '';
                        $emissao_bi = !empty($row['emissao_bi']) ? date('d/m/Y', strtotime($row['emissao_bi'])) : '';
                        $validade_bi = !empty($row['validade_bi']) ? date('d/m/Y', strtotime($row['validade_bi'])) : '';
                        $data_admissao = !empty($row['data_admissao']) ? date('d/m/Y', strtotime($row['data_admissao'])) : '';
                        
                        // Determinar a classe CSS para o estado
                        $estado_class = '';
                        $estado_texto = $row['estado'];
                        
                        if ($estado_texto == 'Ativo') {
                            $estado_class = 'status-ativo';
                        } else if ($estado_texto == 'Inativo') {
                            $estado_class = 'status-inativo';
                        } else if (strpos($estado_texto, 'Terminado') !== false) {
                            $estado_class = 'status-terminado';
                        }
                    ?>
                    <tr onclick="window.location.href='detalhes_funcionario.php?id=<?php echo $row['id_fun']; ?>'">
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px; margin-left:5px;">
                                <div class="user-avatar">
                                <?php if (!empty($row['foto']) && file_exists($row['foto'])): ?>
                                    <img src="<?php echo $row['foto']; ?>" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <img src="icones/icons-sam-18.svg" alt="Avatar Padrão" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                <?php endif; ?>

                                </div>
                                <strong style="margin-left:10px; margin-right:10px;"><?php echo $row['nome']; ?></strong>
                            
                                </div>
                        </td>
                        <td><?php echo str_pad($row['num_mecanografico'], 3, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $data_nascimento; ?></td>
                        <td>
                            <span class="<?php echo $estado_class; ?>">
                                <?php echo $estado_texto; ?>
                                <?php if (strpos($estado_texto, 'Terminado') !== false): ?>
                                    <span>(22d)</span>
                                <?php else: ?>
                                    <?php if ($estado_texto == 'Ativo'): ?>
                                        <span class="status-dot"></span>
                                    <?php elseif ($estado_texto == 'Inativo'): ?>
                                        <span class="status-dot status-dot-yellow"></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td><?php echo $row['bi']; ?></td>
                        <td><?php echo $emissao_bi; ?></td>
                        <td><?php echo $validade_bi; ?></td>
                        <td><?php echo $row['pais']; ?></td>
                        <td><?php echo $row['morada']; ?></td>
                        <td><?php echo $row['genero']; ?></td>
                        <td><?php echo $row['num_agregados']; ?></td>
                        <td><?php echo $row['telemovel']; ?></td>
                        <td><?php echo $row['cargo']; ?></td>
                        <td><?php echo $row['departamento']; ?></td>
                        <td><?php echo $row['tipo_trabalhador']; ?></td>
                        <td><?php echo $row['num_ss']; ?></td>
                        <td><?php echo $data_admissao; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="scroll-indicator"></div>
            
        </div>
        
        <div class="pagination">
    <?php if ($pagina_atual > 1): ?>
        <a href="?pagina=<?php echo $pagina_atual - 1; ?>" class="pagination-item">
            <i class="fas fa-chevron-left"></i>
        </a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <a href="?pagina=<?php echo $i; ?>" class="pagination-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php if ($pagina_atual < $total_paginas): ?>
        <a href="?pagina=<?php echo $pagina_atual + 1; ?>" class="pagination-item">
            <i class="fas fa-chevron-right"></i>
        </a>
    <?php endif; ?>
</div>

    </div>
    <script>
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
        }
        updateTime();

        setInterval(updateTime, 1000);
    </script>
    <script src="sugestoes.js"></script>
    <script src="./js/theme.js"></script>
</body>
</html>