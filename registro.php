<?php
// Verifica se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('protect.php');
include('config.php'); // Conexão com o banco
include_once('includes/sync_app.php'); // Incluir arquivo de sincronização

if (!isset($conn)) {
    die("Erro: Conexão com o banco de dados não estabelecida.");
}

// Obter o id_empresa do administrador
$admin_id = $_SESSION['id_adm'];
$sql_admin = "SELECT e.id_empresa FROM empresa e WHERE e.adm_id = ?";
$stmt_admin = $conn->prepare($sql_admin);

if (!$stmt_admin) {
    die("Erro na preparação da consulta admin: " . $conn->error);
}

$stmt_admin->bind_param("i", $admin_id);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
$admin = $result_admin->fetch_assoc();

if ($admin) {
    $empresa_id = $admin['id_empresa'];
} else {
    die("Erro: Nenhuma empresa cadastrada para este administrador.");
}

$stmt_admin->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar os dados do formulário
    $nome = $_POST['nome'] ?? '';
    $bi = $_POST['bi'] ?? '';
    $emissao_bi = $_POST['emissao_bi'] ?? '';
    $validade_bi = $_POST['validade_bi'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $morada = $_POST['morada'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $num_agregados = $_POST['num_agregados'] ?? 0;
    $contato_emergencia = $_POST['contato_emergencia'] ?? '';
    $nome_contato_emergencia = $_POST['nome_contato_emergencia'] ?? '';
    $telemovel = $_POST['telemovel'] ?? '';
    $email = $_POST['email'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $departamento = $_POST['departamento'] ?? '';
    $tipo_trabalhador = $_POST['tipo_trabalhador'] ?? '';
    $num_conta_bancaria = $_POST['num_conta_bancaria'] ?? '';
    $banco = $_POST['banco'] ?? '';
    $iban = $_POST['iban'] ?? '';
    $salario_base = $_POST['salario_base'] ?? 0.00;
    $num_ss = $_POST['num_ss'] ?? '';

    // Verificar se "Outro" foi selecionado e usar o valor do campo adicional
    /* Removido:
    if ($banco === 'OUTRO') {
        $banco = $_POST['outro_banco'] ?? '';
    }
    */

    // Pegando o ID do admin logado
    if (!isset($_SESSION['id_adm'])) {
        die("Erro: Sessão expirada ou admin não autenticado.");
    }
    $id_adm = $_SESSION['id_adm'];

    // Buscar o id_empresa da empresa do admin logado
    $sql_empresa = "SELECT id_empresa FROM empresa WHERE adm_id = ?";
    $stmt_empresa = $conn->prepare($sql_empresa);
    
    if (!$stmt_empresa) {
        die("Erro na preparação da consulta da empresa: " . $conn->error);
    }

    $stmt_empresa->bind_param("i", $id_adm);
    $stmt_empresa->execute();
    $result_empresa = $stmt_empresa->get_result();

    if ($result_empresa->num_rows > 0) {
        $empresa = $result_empresa->fetch_assoc();
        $empresa_id = $empresa['id_empresa']; 
    } else {
        die("Erro: Nenhuma empresa cadastrada para este administrador.");
    }

    // Diretório para armazenar imagens
    $uploadDir = "fotos/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fotoFinal = NULL; // Por padrão, deixa o campo NULL no banco

    // Processar upload da foto se o usuário enviou
    if (!empty($_FILES["foto"]["name"])) {
        $ext = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $fotoNome = uniqid("func_") . "." . $ext;
        $fotoCaminho = $uploadDir . $fotoNome;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $fotoCaminho)) {
            $fotoFinal = $fotoCaminho; // Guarda o caminho da foto no banco
        }
    }

    // Iniciar transação para garantir integridade dos dados
    $conn->begin_transaction();

    try {
        // Preparar a query SQL para inserir funcionário
        $sql = "INSERT INTO funcionario 
            (nome, foto, bi, emissao_bi, validade_bi, data_nascimento, pais, morada, genero, num_agregados, 
            contato_emergencia, nome_contato_emergencia, telemovel, email, estado, cargo, departamento, 
            tipo_trabalhador, num_conta_bancaria, banco, iban, salario_base, num_ss, empresa_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erro na preparação da consulta: " . $conn->error);
        }

        $stmt->bind_param("sssssssssissssssssssdsii", 
            $nome, $fotoFinal, $bi, $emissao_bi, $validade_bi, $data_nascimento, 
            $pais, $morada, $genero, $num_agregados, $contato_emergencia, 
            $nome_contato_emergencia, $telemovel, $email, $estado, $cargo, 
            $departamento, $tipo_trabalhador, $num_conta_bancaria, $banco, 
            $iban, $salario_base, $num_ss, $empresa_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao cadastrar funcionário: " . $stmt->error);
        }

        // Obter o ID do funcionário recém-cadastrado
        $funcionario_id = $stmt->insert_id;
        $stmt->close();

        // Sincronizar com o aplicativo
        sincronizarFuncionarioSiteParaApp($funcionario_id, $empresa_id);

        // Commit da transação se tudo ocorrer bem
        $conn->commit();

        // Redirecionar com mensagem de sucesso
        $_SESSION['mensagem'] = "Funcionário cadastrado com sucesso!";
        header("Location: funcionarios.php");
        exit;

    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        
        // Excluir a foto se foi enviada mas ocorreu erro depois
        if ($fotoFinal && file_exists($fotoFinal)) {
            unlink($fotoFinal);
        }
        
        die("Erro: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="all.css/registro3.css">
    <link rel="stylesheet" href="./all.css/timer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAM - Novo Funcionário</title>
    <style>
        .nav-menu a {
            text-decoration: none;
        }
        .exit-tag {
        text-decoration: none;
        }

        .profile-circle {
            border-radius: 50%; /* Garante que a borda seja totalmente circular */
            overflow: hidden; /* Corta qualquer parte da imagem que sair do círculo */
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ccc; /* Cor de fundo caso não tenha imagem */
            cursor: pointer;
            margin-left: 10px;
            margin-top: 20px;
        }

        .profile-circle img {
            width: 100%; /* Faz a imagem ocupar toda a largura */
            height: 100%; /* Faz a imagem ocupar toda a altura */
            object-fit: cover; /* Garante que a imagem cubra todo o círculo */
        }

        

        .btn-confirm {
        background-color: #3EB489;
        color: white;   
        padding: 8px 25px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        float: right;
        margin-top: -49px;
        font-size: 14px;
        }
        /* Estilos para campos somente leitura */
        input[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
            color: #555;
            border: 1px solid #ddd;
        }

        .readonly-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        /* Estilo para o ícone de cadeado */
        .lock-icon {
            position: absolute;
            right: 10px;
            color: #666;
            font-size: 14px;
        }

        /* Estilos específicos para o campo Estado */
        input[placeholder="Ativo*"][readonly] {
            font-weight: 500;
        }

        /* Estilos específicos para o campo Salário base */
        input[placeholder="Ad. automaticamente*"][readonly] {
            font-weight: 500;
            color: #ff0000d5 !important; /* Garantir que a fonte seja vermelha */
        }

/* Media Queries for Responsive Design */

@media (max-width: 1200px) {
    /* Adjust form grid for medium sized screens */
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    /* Slightly reduce sidebar width */
    .sidebar {
        width: 220px;
    }
    
    .main-content {
        margin-left: 220px;
    }
}

@media (max-width: 992px) {
    /* Reduce sidebar width */
    .sidebar {
        width: 200px;
    }
    
    .main-content {
        margin-left: 200px;
        padding: 15px 25px;
    }
    
    /* Adjust form wrapper */
    .form-wrapper {
        flex-direction: column;
        height: auto;
    }
    
    /* Adjust sections for vertical stacking */
    .form-section {
        padding: 30px;
    }
    
    /* Adjust border radius for vertical stacking */
    .personal-info {
        border-radius: 25px 25px 0 0;
    }
    
    .professional-info {
        border-radius: 0 0 25px 25px;
    }
    
    /* Reposition profile circle for vertical layout */
    .profile-circle {
        top: 0;
        transform: translate(-50%, -50%);
        margin-top: 86.5%;
    }
    
    /* Adjust button position */
    .btn-confirm {
        margin-top: 20px;
        float: right;
    }
}

@media (max-width: 768px) {
    /* Reduce sidebar width further */
    .sidebar {
        width: 180px;
    }
    
    .main-content {
        margin-left: 180px;
        padding: 15px 20px;
    }
    
    .profile-circle{
        margin-top: 161vh;
    }
    /* Single column for form grid on tablets */
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    /* Adjust header for smaller screens */
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .header-buttons {
        width: 100%;
        justify-content: space-between;
    }
    
    /* Adjust search container */
    .search-container {
        flex-wrap: wrap;
    }
    
    .search-container input {
        width: 100%;
    }
    
    .search-container .search-bar {
        width: 100%;
    }
}

@media (max-width: 576px) {
    /* Sidebar adjustments for mobile */
    .sidebar {
        width: 160px;
    }
    
    .main-content {
        margin-left: 160px;
        padding: 10px 15px;
    }
    
    /* Reduce padding for form sections */
    .form-section {
        padding: 25px 15px;
    }
    
    /* Adjust section titles */
    .section-title {
        font-size: 18px;
        margin-bottom: 20px;
    }
    
    /* Simplify user profile display */
    .user-profile span {
        display: none;
    }
    
    /* Make document note more compact */
    .document-note {
        height: auto;
        padding: 20px;
    }
    
    /* Adjust button size */
    .btn-confirm {
        width: 100%;
        margin-top: 15px;
    }
}

@media (max-width: 480px) {
    /* Full width layout for very small screens */
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 15px;
    }
    
    .main-content {
        margin-left: 0;
        padding: 15px;
    }
    
    .nav-menu {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 5px;
    }
    
    .nav-menu li {
        padding: 8px 12px;
        font-size: 12px;
    }
    
    /* Adjust form sections for full width */
    .form-section {
        border-radius: 25px;
        margin-bottom: 20px;
    }
    
    .personal-info, .professional-info {
        border-radius: 25px;
    }
    
    /* Center profile circle between sections */
    .profile-circle {
        position: relative;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        margin: -32.5px 0;
    }
    
    /* Simplify header elements */
    .time, .exit-tag, .user-profile {
        padding: 6px 10px;
        font-size: 12px;
    }
}


/* Dark Mode Styles */
body.dark {
        background-color: #1A1A1A;
        color: #e0e0e0;
    }

    body.dark .sidebar {
        background-color: #1E1E1E;
        border-right: 1px solid #333;
    }

    body.dark .header-buttons {
        background-color: #2C2C2C;
    }

    body.dark .user-profile {
        background-color: #2C2C2C; 
        color: #ffffff;
    }

    body.dark #current-time {
        background-color: #2C2C2C;
        color: #ffffff;
    }

    body.dark .logo img {
        filter: brightness(0.8) contrast(1.2);
    }

    body.dark .nav-menu {
        background-color: #1E1E1E;
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

    body.dark .time, 
    body.dark .exit-tag, 
    body.dark .user-profile {
        color: #e0e0e0;
    }

    body.dark .search-container input {
        background-color: #2C2C2C;
        color: #e0e0e0;
        border: 1px solid #444;
    }

    body.dark .form-section {
        background-color: #1E1E1E;
        border: 1px solid #333;
    }

    body.dark .personal-info{
        background-color: #3EB489;
        
    }

    body.dark .personal-info .form-group label{
        color: white;

    }

    body.dark .document-note {
    display: flex;
    align-items: center;
    justify-content: center; 
    text-align: center;
    padding: 30px;
    border: 2px dashed #70c7b0;
    border-radius: 25px;
    color: white; 
    margin-top: 10px;
    height: 200px;
    background-color: #1E1E1E;
    }

    body.dark .document-note a {
        color: #70c7b0;
        text-decoration: none;
        font-weight: bold;
    }

    body.dark .personal-info .form-group input,
    body.dark .personal-info .form-group select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #333;
    }

    body.dark .section-title {
        color: #e0e0e0;
    }

    body.dark .form-group label {
        color: #b0b0b0;
    }

    body.dark .form-group input,
    body.dark .form-group select {
        background-color: #2C2C2C;
        color: #e0e0e0;
        border: 1px solid #444;
    }

    body.dark .form-group input::placeholder {
        color: #888;
    }

    body.dark .profile-circle {
        background-color: #2C2C2C;
        border: 2px solid #444;
    }

    body.dark .btn-confirm {
        background-color: #64c2a7;
        color: #121212;
    }

    body.dark input[readonly] {
        background-color: #2C2C2C;
        color: #888;
        border: 1px solid #444;
    }

    /* Scrollbar Styles */
    body.dark ::-webkit-scrollbar-track {
        background: #2C2C2C;
    }

    body.dark ::-webkit-scrollbar-thumb {
        background: #64c2a7;
    }

    /* Estilo para esconder a seta do select quando desabilitado */
    select:disabled {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: none !important;
    }

    /* Ajuste do container do select com cadeado */
    .readonly-container select {
        padding-right: 30px;
        width: 100%;
    }

    .readonly-container .lock-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #666;
    }

    /* Estilo para o tooltip do cargo bloqueado */
    .readonly-container[title] {
        position: relative;
        cursor: not-allowed;
    }

    .readonly-container[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 5px 10px;
        background-color: #333;
        color: white;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        margin-bottom: 5px;
    }

    .readonly-container[title]:hover::before {
        content: '';
        position: absolute;
        bottom: calc(100% - 5px);
        left: 50%;
        transform: translateX(-50%);
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
        z-index: 1000;
    }

    </style>
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
            <a href="funcionarios.php"><li>Funcionários</li></a>
            <a href="registro.php"><li class="active">Novo Funcionário</li></a>
            <li>Processamento Salarial</li>
            <a href="docs.php"><li>Documentos</li></a>
            <a href="registro_ponto.php"><li>Registro de Ponto</li></a>
            <a href="ausencias.php"><li>Ausências</li></a>
            <a href="recrutamento.php"><li>Recrutamento</li></a>
        </ul>
    </div>

   
    <div class="main-content">
    <header class="header">
            <h1 class="page-title">Registro Funcionário</h1>
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

        <div class="search-container">
            <input type="text" placeholder="">
            <input type="text" placeholder="">
            <input type="text" placeholder="Pesquisar..." class="search-bar">
        </div>

    <form action="registro.php" method="POST" enctype="multipart/form-data">
        <div class="form-wrapper">
            <div class="form-section personal-info">
                <h2 class="section-title">
                    <img src="path-to-personal-icon.svg" alt="">
                    Identificação e Relações Pessoais
                </h2>
                <div class="form-grid">

                    <div class="form-group">
                        <label>Nome do funcionário</label>
                        <input type="text" id="nome" name="nome" placeholder="Digite aqui" required>
                    </div>

                    <div class="form-group">
                        <label>Nº do BI</label>
                        <input type="text" id="bi" name="bi" placeholder="Digite aqui" required>
                    </div>
                    <div class="form-group">
                        <label>Emissão do BI</label>
                        <input type="date" id="emissao_bi" name="emissao_bi"e placeholder="Digite aqui" required>
                    </div>
                    <div class="form-group">
                        <label>Validade do BI</label>
                        <input type="date" id="validade_bi" name="validade_bi" placeholder="Digite aqui" required>
                    </div>
                    <div class="form-group">
                        <label>Data de Nascimento</label style="white-space: nowrap;">
                        <input type="date" id="data_nascimento" name="data_nascimento" placeholder="Digite aqui" required>
                    </div>
                    <div class="form-group">
                        <label>País</label>
                        <select name="pais" id="pais" required>
                            <option value="">Selecione um país</option>
                            <option value="angola">Angola</option>
                            <option value="argentina">Argentina</option>
                            <option value="brasil">Brasil</option>
                            <option value="canada">Canadá</option>
                            <option value="chile">Chile</option>
                            <option value="china">China</option>
                            <option value="colombia">Colômbia</option>
                            <option value="espanha">Espanha</option>
                            <option value="estados_unidos">Estados Unidos</option>
                            <option value="franca">França</option>
                            <option value="alemanha">Alemanha</option>
                            <option value="italia">Itália</option>
                            <option value="japao">Japão</option>
                            <option value="mexico">México</option>
                            <option value="moçambique">Moçambique</option>
                            <option value="portugal">Portugal</option>
                            <option value="reino_unido">Reino Unido</option>
                            <option value="russia">Rússia</option>
                            <option value="africa_do_sul">África do Sul</option>
                            <option value="australia">Austrália</option>
                            <option value="coreia_do_sul">Coreia do Sul</option>
                            <option value="india">Índia</option>
                            <option value="indonesia">Indonésia</option>
                            <option value="nigeria">Nigéria</option>
                            <option value="venezuela">Venezuela</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Morada</label>
                        <input type="text" id="morada" name="morada" placeholder="Digite aqui" required>
                    </div>
                    <div class="form-group">
                        <label>Gênero</label>
                        <select name="genero" id="genero" class="date" required>
                            <option value="masculino">Masculino</option>
                            <option value="femininoo">Feminino</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nº de agregados</label>
                        <input type="number" name="num_agregados" id="num_agregados" placeholder="Digite aqui" required>
                    </div>
                    <div class="form-group">
                        <label>Contato de emergência</label>
                        <input type="tel" id="contato_emergencia" name="contato_emergencia" placeholder="Digite o número de telefone" required>
                    </div>
                    <div class="form-group">
                        <label>Nome do contato emergência</label>
                        <input type="text" name="nome_contato_emergencia" id="nome_contato_emergencia" placeholder="Digite aqui" required>
                    </div>

                </div>
            </div>

            <div class="profile-circle" onclick="document.getElementById('foto').click();">
                <img id="preview" src="icones/icons-sam-18.svg" alt="Inserir">
            </div>

            <!-- Input escondido para upload da foto -->
            <input type="file" name="foto" id="foto" accept="image/*" style="display: none;" onchange="previewImage(event)">


            <script>
            function previewImage(event) {
                var reader = new FileReader();
                reader.onload = function() {
                    document.getElementById('preview').src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }
            </script>

            <div class="form-section professional-info">
                <h2 class="section-title">
                    Dados Profissionais e Financeiros
                </h2>
                <div class="form-grid">

                <div class="form-group">
                    <label>Telemóvel</label>
                    <input type="tel" id="telemovel" name="telemovel" placeholder="Digite o número de telefone" required>
                </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" name="email" id="email" placeholder="Digite aqui"required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <input type="text" name="estado" id="estado" placeholder="Ativo*" readonly>
                    </div>
                    <div class="form-group">
                        <label for="departamento">Departamento</label>
                        <select id="departamento" name="departamento" required>
                            <option value="">Selecione o Departamento</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cargo">Cargo</label>
                        <div class="readonly-container" title="Selecione o Departamento primeiro para prosseguir">
                            <select id="cargo" name="cargo" required disabled>
                                <option value="">Selecione o Cargo</option>
                            </select>
                            <i class="fas fa-lock lock-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="salario_base">Salário Base</label>
                        <div class="readonly-container" title="Salário Calculado automaticamente">
                            <input type="number" id="salario_base" name="salario_base" step="0.01" readonly>
                            <i class="fas fa-lock lock-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo</label>
                        <select size="1" name="tipo_trabalhador" id="tipo_trabalhador" required>
                            <option value="">Selecione um tipo de trabalhador</option>
                            <option value="efetivo">Trabalhador Efetivo</option>
                            <option value="temporario">Trabalhador Temporário</option>
                            <option value="estagiario">Trabalhador Estagiário</option>
                            <option value="autonomo">Trabalhador Autônomo</option>
                            <option value="freelancer">Trabalhador Freelancer</option>
                            <option value="terceirizado">Trabalhador Terceirizado</option>
                            <option value="intermitente">Trabalhador Intermitente</option>
                            <option value="voluntario">Trabalhador Voluntário</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Nº conta banco</label>
                        <input type="number" name="num_conta_bancaria" id="num_conta_bancaria" title="Número da conta bancária" placeholder="Digite aqui" required>
                    </div>
                    <div class="form-group">
                        <label>Banco</label>
                        <select size="1" name="banco" id="banco" required>
                            <option value="">Selecione um banco</option>
                            <?php
                            // Buscar bancos ativos
                            $sql_bancos = "SELECT banco_nome, banco_codigo FROM bancos_ativos WHERE empresa_id = ? AND ativo = 1 ORDER BY banco_nome";
                            $stmt_bancos = $conn->prepare($sql_bancos);
                            
                            if (!$stmt_bancos) {
                                die("Erro na preparação da consulta: " . $conn->error);
                            }
                            
                            $stmt_bancos->bind_param("i", $empresa_id);
                            
                            if (!$stmt_bancos->execute()) {
                                die("Erro ao executar a consulta: " . $stmt_bancos->error);
                            }
                            
                            $result_bancos = $stmt_bancos->get_result();
                            
                            if ($result_bancos->num_rows > 0) {
                                while($banco = $result_bancos->fetch_assoc()) {
                                    echo "<option value='".$banco['banco_codigo']."'>".$banco['banco_nome']."</option>";
                                }
                            } else {
                                echo "<!-- Nenhum banco ativo encontrado para a empresa ID: " . $empresa_id . " -->";
                            }
                            
                            $stmt_bancos->close();
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="IBAN">IBAN</label>
                        <input type="text" id="iban" name="iban" placeholder="Digite aqui" required>
                    </div>
                    <div class="form-group">
                        <label>Nº SS</label>
                        <input type="text" name="num_ss" id="num_ss" placeholder="Digite aqui" required>
                    </div>

                </div>
                <button type="submit" class="btn-confirm">confirmar</button>
            </div>
        </div>
    </form>

        <div class="document-note">
            Clique no retângulo para abrir o&nbsp;<a href="#"> Gestor de documentos</a>&nbsp; do funcionário
        </div>
    </div>

    <script src="UI.js"></script>
    <script>
// Definir os salários base para cada cargo (em KZs)
const salariosPorCargo = {
    "Administrador": "220.000,00",
    "Analista Financeiro": "180.000,00",
    "Assistente Administrativo": "90.000,00",
    "Assistente de Recursos Humanos": "100.000,00",
    "Atendente Comercial": "85.000,00",
    "Auditor": "200.000,00",
    "Contabilista": "170.000,00",
    "Coordenador de Projetos": "210.000,00",
    "Diretor Comercial": "300.000,00",
    "Diretor de Recursos Humanos": "300.000,00",
    "Engenheiro Civil": "250.000,00",
    "Engenheiro Informático": "240.000,00",
    "Especialista em Marketing": "150.000,00",
    "Gerente de Contas": "180.000,00",
    "Gestor de Projetos": "200.000,00",
    "Jurista": "230.000,00",
    "Operador de Caixa": "80.000,00",
    "Operador de Máquinas": "90.000,00",
    "Programador": "200.000,00",
    "Rececionista": "85.000,00",
    "Secretário Executivo": "120.000,00",
    "Supervisor de Vendas": "160.000,00",
    "Técnico de Manutenção": "110.000,00",
    "Técnico de Suporte": "120.000,00",
    "Vendedor": "95.000,00"
};

// Função para adicionar ícone de bloqueio a um campo
function adicionarIconeBloqueio(input, isSalario = false) {
    // Criar o contêiner para o campo e o ícone
    const container = document.createElement('div');
    container.className = 'readonly-container';
    container.style.position = 'relative';
    container.style.display = 'flex';
    container.style.alignItems = 'center';
    container.style.width = '100%';
    
    // Obter o elemento pai do input
    const parentElement = input.parentElement;
    
    // Substituir o input original pelo contêiner
    parentElement.appendChild(container);
    
    // Adicionar estilo ao input
    input.style.paddingRight = '30px'; // Espaço para o ícone
    input.style.backgroundColor = '#f0f0f0'; // Cor de fundo mais clara para indicar que está desativado
    input.style.width = '100%';
    input.readOnly = true; // Tornar o campo somente leitura
    
    // Aplicar cor vermelha se for campo de salário
    if (isSalario) {
        input.style.color = '#FF0000';
    }
    
    // Mover o input para o contêiner
    container.appendChild(input);
    
    // Criar o ícone de bloqueio com Font Awesome
    const lockIcon = document.createElement('i');
    lockIcon.className = 'fas fa-lock lock-icon';
    lockIcon.style.position = 'absolute';
    lockIcon.style.right = '10px';
    lockIcon.style.pointerEvents = 'none'; // Evita que o ícone receba eventos de mouse
    lockIcon.style.color = '#666'; // Cor cinza para o ícone
    
    // Adicionar o ícone ao contêiner
    container.appendChild(lockIcon);
}

// Buscar os elementos DOM após o carregamento da página
document.addEventListener('DOMContentLoaded', function() {
    // Buscar os elementos relevantes
    const selectCargo = document.querySelector('.form-group select[size="1"]:not([name=""])'); // Seletor mais específico para o campo de cargo
    const inputSalario = document.querySelector('input[placeholder="Ad. automaticamente*"]');
    const inputEstado = document.querySelector('input[placeholder="Ativo*"]');
    
    // Verificar se os elementos necessários existem
    if (inputEstado) {
        // Definir o valor padrão para o campo Estado
        inputEstado.value = "Ativo";
        
        // Modificar o elemento de entrada Estado para incluir o ícone de bloqueio
        adicionarIconeBloqueio(inputEstado, false);
    }
    
    if (selectCargo && inputSalario) {
        // Modificar o elemento de entrada Salário para incluir o ícone de bloqueio
        adicionarIconeBloqueio(inputSalario, true); // true indica que é um campo de salário (para aplicar cor vermelha)
        
        // Função para atualizar o salário base quando o cargo for selecionado
        function atualizarSalarioBase() {
            const cargoSelecionado = selectCargo.value;
            
            if (cargoSelecionado && salariosPorCargo[cargoSelecionado]) {
                inputSalario.value = salariosPorCargo[cargoSelecionado] + " KZs";
            } else {
                inputSalario.value = "";
            }
        }
        
        // Adicionar listener para mudanças no select de cargo
        selectCargo.addEventListener('change', atualizarSalarioBase);
        
        // Executar uma vez para inicializar o valor do salário se o cargo já estiver selecionado
        if (selectCargo.value) {
            atualizarSalarioBase();
        }
    } else {
        console.error("Elementos não encontrados: selectCargo ou inputSalario");
        if (!selectCargo) console.error("selectCargo não encontrado");
        if (!inputSalario) console.error("inputSalario não encontrado");
    }
});


document.addEventListener('DOMContentLoaded', function() {
        // Inicializar o intl-tel-input
        const input = document.querySelector("#telemovel");
        window.intlTelInput(input, {
            initialCountry: "ao", // Código do país inicial (Angola)
            separateDialCode: true, // Mostrar o código do país separadamente
            showFlags: false, // Oculta as bandeiras
            preferredCountries: ["ao", "pt", "br", "us"], // Países preferenciais
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" // Utilitários necessários
        });
    });

        document.addEventListener('DOMContentLoaded', function() {
            const inputEmergencia = document.querySelector("#contato_emergencia");
            window.intlTelInput(inputEmergencia, {
                initialCountry: "ao", // Código do país inicial (Angola)
                separateDialCode: true, // Mostrar o código do país separadamente
                preferredCountries: ["ao", "pt", "br", "us"], // Países preferenciais
                showFlags: false, // Oculta as bandeiras
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" // Utilitários necessários
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataNascimento = document.getElementById('data_nascimento');
    const emissaoBi = document.getElementById('emissao_bi');
    const validadeBi = document.getElementById('validade_bi');
    const errorMessage = document.getElementById('error-message'); // Elemento para exibir mensagens de erro

    if (dataNascimento && emissaoBi && validadeBi) {
        // Get current date
        const hoje = new Date();
        const anoAtual = hoje.getFullYear();
        const mesAtual = String(hoje.getMonth() + 1).padStart(2, '0');
        const diaAtual = String(hoje.getDate()).padStart(2, '0');
        const dataAtual = `${anoAtual}-${mesAtual}-${diaAtual}`;
        
        // Set min/max constraints for data_nascimento (birth date)
        const minAnoNascimento = anoAtual - 120;
        dataNascimento.min = `${minAnoNascimento}-01-01`;
        dataNascimento.max = dataAtual;
        
        // BI emission date (can't be in the future)
        emissaoBi.max = dataAtual;
        
        // BI validity date (must be future date)
        validadeBi.min = dataAtual;
        
        // Custom validation for data_nascimento
        dataNascimento.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const selectedYear = selectedDate.getFullYear();
            
            if (selectedYear < minAnoNascimento) {
                errorMessage.innerText = 'Data de nascimento inválida.';
                this.value = '';
            } else if (selectedDate > hoje) {
                errorMessage.innerText = 'Data de nascimento inválida!';
                this.value = '';
            } else {
                errorMessage.innerText = ''; // Limpa a mensagem de erro
            }
        });
        
        // Custom validation for emission date
        emissaoBi.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            
            if (selectedDate > hoje) {
                errorMessage.innerText = 'A data de emissão inválida!';
                this.value = '';
            } else {
                errorMessage.innerText = ''; // Limpa a mensagem de erro
            }
        });
        
        // Custom validation for validity date
        validadeBi.addEventListener('change', function() {
            // Só valida se o campo estiver completamente preenchido (10 caracteres, formato YYYY-MM-DD)
            if (this.value.length === 10) {
                const emissaoDate = new Date(emissaoBi.value);
                const selectedDate = new Date(this.value);
                
                if (emissaoBi.value && selectedDate <= emissaoDate) {
                    errorMessage.innerText = 'A data de validade deve ser posterior à data de emissão!';
                    this.value = '';
                } else {
                    errorMessage.innerText = ''; // Limpa a mensagem de erro
                }
            }
        });
    }
});
</script>
    <script src="./js/theme.js"></script>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const departamentoSelect = document.getElementById('departamento');
    const cargoSelect = document.getElementById('cargo');
    const salarioBaseInput = document.getElementById('salario_base');

    departamentoSelect.addEventListener('change', function() {
        const departamentoId = this.value;
        
        // Limpar o select de cargos
        cargoSelect.innerHTML = '<option value="">Selecione o Cargo</option>';
        salarioBaseInput.value = '';
        
        if (departamentoId) {
            // Fazer a requisição AJAX para buscar os cargos
            fetch(`get_cargos.php?departamento_id=${departamentoId}`)
                .then(response => response.json())
                .then(cargos => {
                    cargos.forEach(cargo => {
                        const option = document.createElement('option');
                        option.value = cargo.id;
                        option.textContent = cargo.nome;
                        option.dataset.salario = cargo.salario_base;
                        cargoSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar cargos:', error);
                });
        }
    });

    cargoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.salario) {
            salarioBaseInput.value = selectedOption.dataset.salario;
        } else {
            salarioBaseInput.value = '';
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carregar departamentos e cargos ao iniciar a página
    carregarDepartamentosECargos();

    // Função para carregar departamentos e cargos
    function carregarDepartamentosECargos() {
        fetch('configuracoes_sam/get_cargos.php')
            .then(response => response.json())
            .then(data => {
                // Preencher o select de departamentos
                const selectDepartamento = document.getElementById('departamento');
                selectDepartamento.innerHTML = '<option value="">Selecione o Departamento</option>';
                data.departamentos.forEach(depto => {
                    selectDepartamento.innerHTML += `<option value="${depto.id}">${depto.nome}</option>`;
                });

                // Preencher o select de cargos
                const selectCargo = document.getElementById('cargo');
                selectCargo.innerHTML = '<option value="">Selecione o Cargo</option>';
                data.cargos.forEach(cargo => {
                    selectCargo.innerHTML += `<option value="${cargo.id}" data-salario="${cargo.salario_base}">${cargo.nome} (${cargo.departamento_nome})</option>`;
                });

                // Adicionar evento para atualizar salário base quando selecionar cargo
                selectCargo.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const salarioBase = selectedOption.getAttribute('data-salario');
                    document.getElementById('salario_base').value = salarioBase || '';
                });

                // Adicionar evento para habilitar/desabilitar o select de cargo
                selectDepartamento.addEventListener('change', function() {
                    const selectCargo = document.getElementById('cargo');
                    const lockIcon = selectCargo.parentElement.querySelector('.lock-icon');
                    if (this.value) {
                        selectCargo.disabled = false;
                        lockIcon.style.display = 'none';
                    } else {
                        selectCargo.disabled = true;
                        selectCargo.value = '';
                        document.getElementById('salario_base').value = '';
                        lockIcon.style.display = 'block';
                    }
                });
            })
            .catch(error => console.error('Erro ao carregar dados:', error));
    }
});
</script>
</body>
</html>