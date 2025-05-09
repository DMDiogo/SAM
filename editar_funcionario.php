<?php
include 'protect.php';
include 'config.php';

// Pegar o ID do funcionário da URL
$id_fun = $_GET['id'];

// Consulta SQL para buscar os dados atuais do funcionário
$sql = "SELECT num_mecanografico, nome, foto, bi, emissao_bi, validade_bi, 
               data_nascimento, pais, morada, genero, num_agregados, 
               contato_emergencia, nome_contato_emergencia, telemovel, email, estado, 
               cargo, departamento, tipo_trabalhador, 
               num_conta_bancaria, banco, iban, 
               salario_base, num_ss, data_admissao 
        FROM funcionario WHERE id_fun = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_fun);
$stmt->execute();
$result = $stmt->get_result();

// Verificar se encontrou o funcionário
if ($result->num_rows > 0) {
    $dados = $result->fetch_assoc();
} else {
    echo "Funcionário não encontrado!";
    exit();
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coletar dados do formulário
    $nome = $_POST['nome'];
    $bi = $_POST['bi'];
    $emissao_bi = $_POST['emissao_bi'];
    $validade_bi = $_POST['validade_bi'];
    $data_nascimento = $_POST['data_nascimento'];
    $pais = $_POST['pais'];
    $morada = $_POST['morada'];
    $genero = $_POST['genero'];
    $num_agregados = $_POST['num_agregados'];
    $contato_emergencia = $_POST['contato_emergencia'];
    $nome_contato_emergencia = $_POST['nome_contato_emergencia'];
    $telemovel = $_POST['telemovel'];
    $email = $_POST['email'];
    $estado = $_POST['estado'];
    $cargo = $_POST['cargo'];
    $departamento = $_POST['departamento'];
    $tipo_trabalhador = $_POST['tipo_trabalhador'];
    $num_conta_bancaria = $_POST['num_conta_bancaria'];
    $banco = $_POST['banco'];
    $iban = $_POST['iban'];
    $salario_base = $_POST['salario_base'];
    $num_ss = $_POST['num_ss'];
    $data_admissao = $_POST['data_admissao'];

    // Atualizar os dados no banco de dados
    $sql_update = "UPDATE funcionario SET 
                   nome = ?, bi = ?, emissao_bi = ?, validade_bi = ?, 
                   data_nascimento = ?, pais = ?, morada = ?, genero = ?, 
                   num_agregados = ?, contato_emergencia = ?, nome_contato_emergencia = ?, 
                   telemovel = ?, email = ?, estado = ?, cargo = ?, departamento = ?, 
                   tipo_trabalhador = ?, num_conta_bancaria = ?, banco = ?, iban = ?, 
                   salario_base = ?, num_ss = ?, data_admissao = ? 
                   WHERE id_fun = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssssssisssssssssssdsi", 
        $nome, $bi, $emissao_bi, $validade_bi, $data_nascimento, $pais, $morada, $genero, 
        $num_agregados, $contato_emergencia, $nome_contato_emergencia, $telemovel, $email, 
        $estado, $cargo, $departamento, $tipo_trabalhador, $num_conta_bancaria, $banco, 
        $iban, $salario_base, $num_ss, $data_admissao, $id_fun);

    if ($stmt_update->execute()) {
        echo "Dados atualizados com sucesso!";
        // Redirecionar de volta para a página de detalhes
        header("Location: detalhes_funcionario.php?id=$id_fun");
        exit();
    } else {
        echo "Erro ao atualizar os dados: " . $stmt_update->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Detalhes do Funcionário</title>
    <link rel="stylesheet" href="all.css/registro3.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>

        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}


body {
    background-color: #f5f5f5;
}

.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background-color: white;
    padding: 20px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo {
    margin-bottom: 20px;
    padding: 0 10px;
}

.logo img {
    height: 40px;
}

.nav-select {
    width: 100%;
    padding: 8px 12px;
    margin-bottom: 30px;
    border: 1px solid #ddd;
    border-radius: 25px;
    color: #666;
    background: white;
}

.nav-menu {
    list-style: none;
    padding: 0 10px;
}

.nav-menu a {
    text-decoration: none;
}

.nav-menu li {
    padding: 12px 15px;
    margin: 5px 0;
    color: #666;
    cursor: pointer;
    border-radius: 5px;
    display: flex;
    align-items: center;
    font-size: 14px;
}

.nav-menu li::before {
    content: "•";
    color: #70c7b0;
    margin-right: 10px;
    font-size: 20px;
}

.nav-menu li:hover,
.nav-menu li.active {
    background-color: rgba(112, 199, 176, 0.1);
    color: #70c7b0;
}

.main-content {
    margin-left: 250px;
    padding: 20px 40px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-buttons,
.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.page-title {
    color: #000000b7;
    font-size: 28px;
    font-weight: 600;
}

.time,
.user-profile {
    background-color: white;
    color: #000;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}

.exit-tag {
    background-color: #FF6B6B;
    border: none;
    color: white;
    cursor: pointer;
    text-decoration: none;
}

.user-profile {
    display: flex;
    gap: 10px;
    cursor: pointer;
    padding: 6px 15px;
}

.user-profile img {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #3EB489;
}

.dropdown-arrow {
    font-size: 12px;
    margin-left: 5px;
}

.search-container {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    justify-content: flex-start;
}

.search-container .search-bar {
    background-color: #ffffff;
    width: 20%;
}

.search-container input {
    width: 140px;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 25px;
    font-size: 14px;
    background: #f5f5f5;
    transition: all 0.3s ease-in-out;
    height: 32px;
}

.search-bar {
    background-color: #3EB489;
}

.search-container input:focus {
    border-color: #70c7b0;
    box-shadow: 0 0 5px rgba(112, 199, 176, 0.5);
    outline: none;
}

.form-wrapper {
    position: relative;
    display: flex;
    margin-bottom: 0px;
    height: 60%;
}

.profile-circle {
    width: 65px;
    height: 65px;
    background-color: white;
    border: 3px solid #70c7b0;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -365%);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}

.profile-circle img {
    width: 30px;
    height: 30px;
}

.form-section {
    flex: 1;
    padding: 45px;
}

.personal-info {
    background-color: #3EB489;
    color: white;
    border-top-left-radius: 25px;
    border-bottom-left-radius: 25px;
}

.professional-info {
    background-color: white;
    border-top-right-radius: 25px;
    border-bottom-right-radius: 25px;
}

.section-title {
    padding: 10px;
    font-size: 20px;
    margin-bottom: 25px;
    gap: 10px;
    font-weight: bold;
    text-align: center;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.form-group {
    margin-bottom: 10px;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-size: 13px;
    color: inherit;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 9px 12px;
    border-radius: 5px;
    font-size: 12px;
}

.personal-info .form-group input,
.personal-info .form-group select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #333;
}

.professional-info .form-group input,
.professional-info .form-group select {
    border: 1px solid #ddd;
    background: white;
    color: #333;
}

.btn-confirm {
    background-color: #3EB489;
    color: white;
    padding: 8px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    float: right;
    margin-top: -10%;
    font-size: 14px;
}

.document-note {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 30px;
    border: 2px dashed #70c7b0;
    border-radius: 25px;
    color: #000000;
    margin-top: 10px;
    height: 200px;
    background-color: #fff;
}

.document-note a {
    color: #70c7b0;
    text-decoration: none;
    font-weight: bold;
}

.container {
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-left: 9px;
    padding: 0 15px 30px;
    position: relative;
    margin-right: -22px;
    height: 10px;
}

.foto-perfil {
    position: absolute;
    top: 50px;
    right: 100px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    background: linear-gradient(to right, #5cbea5, #77d6c1);
}

.foto-perfil img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.secao {
    background-color: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    width: 80%;
    margin-bottom: -10px;
    height: auto;
    max-height: 260px;
}

.teste {
    display: flex;
    justify-content: center; /* Mudado de space-evenly para space-between */
    width: 60%;
    margin-left: 18%;
    gap: 27%; /* Adicionando gap para controlar o espaçamento entre as divs */
}

.teste div {
    margin-right: 0; /* Removendo margens negativas */
    margin-left: 0; /* Removendo margens negativas */
    padding: 0;
    margin-top: -10px;
}

.teste div:first-child {
    margin-left: 0;
}

.teste div:nth-child(2) {
    margin-left: 0;
    margin-right: 0;
}

.teste div:last-child {
    margin-right: 0;
}

.teste div p {
    margin: 5px 0;
    font-size: 13px;
}

.secao input,
.secao select {
    border: 1px solid #ddd;
    padding: 4px 6px;
    border-radius: 5px;
    font-size: 12px;
    width: 110px;
    margin-left: 5px;
}

.juntos {
    display: flex;
    width: 100%;
    justify-content: space-between;
    height: auto;
    max-height: none;
    height: -60%; /* Removed max-height restriction */
    overflow: visible; /* Allow content to be visible */    

}

.m {
    width: 100%;
    margin-bottom: 20px;
    background: linear-gradient(to right, rgb(255, 255, 255) 80%, #82dab7);
    padding: 15px ; /* Increase padding, especially at bottom */
    height: auto;
    margin-top: 10px; /* Add some space at the top */
}

.n{
    width: 49%;
}
.o {
    width: 49%;
}

.secao h2 {
    color: #333;
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: 600;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
}

.secao p {
    margin: 8px 0;
    font-size: 14px;
}

.secao strong {
    font-weight: 600;
    color: #555;
}

.doc-icons {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 30px;
    border: 1px dashed #ccc;
    border-radius: 12px;
    padding: 30px;
}

.doc-icon {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.icon-circle {
    width: 80px;
    height: 80px;
    background-color: rgba(92, 190, 165, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}

.icon-circle i {
    color: #5cbea5;
    font-size: 30px;
}

.doc-icon p {
    font-size: 14px;
    font-weight: 500;
    color: #555;
    margin-bottom: 5px;
}

.doc-icon small {
    font-size: 12px;
    color: #999;
}

.edit-button {
    margin-top: 50px;;
    justify-self: right;
    background-color: #5cbea5;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 500;
    box-shadow: 0 3px 10px rgba(92, 190, 165, 0.3);
}

.edit-button i {
    font-size: 14px;
}

.edit-button1 {
    text-decoration: none;
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
            <h1 class="page-title">Editar Funcionário | #<?php echo $dados['num_mecanografico']; ?></h1>
            <div class="header-buttons">
                <div class="time" id="current-time"></div>
                <a class="exit-tag" href="logout.php">Sair</a>
                <div class="user-profile">
                    <img src="Apresentação1 (1).png" alt="User" width="20">
                    <span><?php echo $_SESSION['nome']; ?></span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
            </div>
        </header>

        <form method="POST" action="editar_funcionario.php?id=<?php echo $id_fun; ?>" onsubmit="return validateForm()">
        <button type="submit" class="edit-button">
                    <i class="fas fa-save"></i> Guardar
        </button>
            <div class="container">
                <!-- Foto -->
                <div class="foto-perfil">
                    <img src="<?php echo !empty($dados['foto']) ? $dados['foto'] : 'icones/icons-sam-18.svg'; ?>" alt="Foto de <?php echo $dados['nome']; ?>">
                </div>

                <!-- Informações Pessoais -->
                <div class="secao m">
                    <h2>Informações Pessoais</h2>
                    <div class="teste">
                        <div style="margin-left:-200px;">
                            <p><strong>Nome:</strong> <input type="text" name="nome" value="<?php echo $dados['nome']; ?>"></p>
                            <p><strong>Nº BI:</strong> <input type="text" name="bi" value="<?php echo $dados['bi']; ?>"></p>
                            <p><strong>Emissão:</strong> <input type="date" name="emissao_bi" value="<?php echo $dados['emissao_bi']; ?>"></p>
                            <p><strong>Validade:</strong> <input type="date" name="validade_bi" value="<?php echo $dados['validade_bi']; ?>"></p>
                            <br>
                            <p style="margin-top:-10px; margin-bottom:10px;"><strong>Telefone:</strong> <input type="text" name="telemovel" value="<?php echo $dados['telemovel']; ?>"></p>
                        </div>
                        <div style="margin-left:-100px;">
                            <p><strong>Nascimento:</strong> <input type="date" name="data_nascimento" value="<?php echo $dados['data_nascimento']; ?>"></p>
                            <p><strong>Nacionalidade:</strong> <input type="text" name="pais" value="<?php echo $dados['pais']; ?>"></p>
                            <p><strong>Morada:</strong> <input type="text" name="morada" value="<?php echo $dados['morada']; ?>"></p>
                            <p><strong>Gênero:</strong> <input type="text" name="genero" value="<?php echo $dados['genero']; ?>"></p>
                            <br>
                            <p style="margin-top:-10px; margin-bottom:10px;"><strong>Email:</strong> <input type="email" name="email" value="<?php echo $dados['email']; ?>"></p>
                        </div>
                        <div style="margin-left:-130px;">
                            <p><strong>Nº de Agregados:</strong> <input type="number" name="num_agregados" value="<?php echo $dados['num_agregados']; ?>"></p>
                            <p><strong>Salário Base:</strong> <input type="number" step="0.01" name="salario_base" value="<?php echo $dados['salario_base']; ?>"></p>
                            <p><strong>Nº da SS:</strong> <input type="text" name="num_ss" value="<?php echo $dados['num_ss']; ?>"></p>
                        </div>
                    </div>
                </div>

                <div class="juntos">
                    <!-- Informações Profissionais -->  
                    <div class="secao n">
                        <h2>Informações Profissionais</h2>
                        <p><strong>Nº Mecanográfico:</strong> <input type="text" name="num_mecanografico" value="<?php echo $dados['num_mecanografico']; ?>"readonly></p>
                        </span>
                        <p><strong>Cargo:</strong>
                            <select name="cargo" id="cargo" required>
                                <option value="">Selecione um cargo</option>
                                <option value="Administrador" <?php echo ($dados['cargo'] == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="Analista Financeiro" <?php echo ($dados['cargo'] == 'Analista Financeiro') ? 'selected' : ''; ?>>Analista Financeiro</option>
                                <option value="Assistente Administrativo" <?php echo ($dados['cargo'] == 'Assistente Administrativo') ? 'selected' : ''; ?>>Assistente Administrativo</option>
                                <option value="Assistente de Recursos Humanos" <?php echo ($dados['cargo'] == 'Assistente de Recursos Humanos') ? 'selected' : ''; ?>>Assistente de Recursos Humanos</option>
                                <option value="Atendente Comercial" <?php echo ($dados['cargo'] == 'Atendente Comercial') ? 'selected' : ''; ?>>Atendente Comercial</option>
                                <option value="Auditor" <?php echo ($dados['cargo'] == 'Auditor') ? 'selected' : ''; ?>>Auditor</option>
                                <option value="Contabilista" <?php echo ($dados['cargo'] == 'Contabilista') ? 'selected' : ''; ?>>Contabilista</option>
                                <option value="Coordenador de Projetos" <?php echo ($dados['cargo'] == 'Coordenador de Projetos') ? 'selected' : ''; ?>>Coordenador de Projetos</option>
                                <option value="Diretor Comercial" <?php echo ($dados['cargo'] == 'Diretor Comercial') ? 'selected' : ''; ?>>Diretor Comercial</option>
                                <option value="Diretor de Recursos Humanos" <?php echo ($dados['cargo'] == 'Diretor de Recursos Humanos') ? 'selected' : ''; ?>>Diretor de Recursos Humanos</option>
                                <option value="Engenheiro Civil" <?php echo ($dados['cargo'] == 'Engenheiro Civil') ? 'selected' : ''; ?>>Engenheiro Civil</option>
                                <option value="Engenheiro Informático" <?php echo ($dados['cargo'] == 'Engenheiro Informático') ? 'selected' : ''; ?>>Engenheiro Informático</option>
                                <option value="Especialista em Marketing" <?php echo ($dados['cargo'] == 'Especialista em Marketing') ? 'selected' : ''; ?>>Especialista em Marketing</option>
                                <option value="Gerente de Contas" <?php echo ($dados['cargo'] == 'Gerente de Contas') ? 'selected' : ''; ?>>Gerente de Contas</option>
                                <option value="Gestor de Projetos" <?php echo ($dados['cargo'] == 'Gestor de Projetos') ? 'selected' : ''; ?>>Gestor de Projetos</option>
                                <option value="Jurista" <?php echo ($dados['cargo'] == 'Jurista') ? 'selected' : ''; ?>>Jurista</option>
                                <option value="Operador de Caixa" <?php echo ($dados['cargo'] == 'Operador de Caixa') ? 'selected' : ''; ?>>Operador de Caixa</option>
                                <option value="Operador de Máquinas" <?php echo ($dados['cargo'] == 'Operador de Máquinas') ? 'selected' : ''; ?>>Operador de Máquinas</option>
                                <option value="Programador" <?php echo ($dados['cargo'] == 'Programador') ? 'selected' : ''; ?>>Programador</option>
                                <option value="Rececionista" <?php echo ($dados['cargo'] == 'Rececionista') ? 'selected' : ''; ?>>Rececionista</option>
                                <option value="Secretário Executivo" <?php echo ($dados['cargo'] == 'Secretário Executivo') ? 'selected' : ''; ?>>Secretário Executivo</option>
                                <option value="Supervisor de Vendas" <?php echo ($dados['cargo'] == 'Supervisor de Vendas') ? 'selected' : ''; ?>>Supervisor de Vendas</option>
                                <option value="Técnico de Manutenção" <?php echo ($dados['cargo'] == 'Técnico de Manutenção') ? 'selected' : ''; ?>>Técnico de Manutenção</option>
                                <option value="Técnico de Suporte" <?php echo ($dados['cargo'] == 'Técnico de Suporte') ? 'selected' : ''; ?>>Técnico de Suporte</option>
                                <option value="Vendedor" <?php echo ($dados['cargo'] == 'Vendedor') ? 'selected' : ''; ?>>Vendedor</option>
                            </select>
                        </p>

                        <p><strong>Departamento:</strong>
                            <select name="departamento" id="departamento" required>
                                <option value="">Selecione um departamento</option>
                                <option value="administrativo" <?php echo ($dados['departamento'] == 'administrativo') ? 'selected' : ''; ?>>Administrativo</option>
                                <option value="financeiro" <?php echo ($dados['departamento'] == 'financeiro') ? 'selected' : ''; ?>>Financeiro</option>
                                <option value="rh" <?php echo ($dados['departamento'] == 'rh') ? 'selected' : ''; ?>>Recursos Humanos</option>
                                <option value="tecnologia" <?php echo ($dados['departamento'] == 'tecnologia') ? 'selected' : ''; ?>>Tecnologia da Informação</option>
                                <option value="marketing" <?php echo ($dados['departamento'] == 'marketing') ? 'selected' : ''; ?>>Marketing</option>
                                <option value="vendas" <?php echo ($dados['departamento'] == 'vendas') ? 'selected' : ''; ?>>Vendas</option>
                                <option value="juridico" <?php echo ($dados['departamento'] == 'juridico') ? 'selected' : ''; ?>>Jurídico</option>
                                <option value="logistica" <?php echo ($dados['departamento'] == 'logistica') ? 'selected' : ''; ?>>Logística</option>
                                <option value="operacional" <?php echo ($dados['departamento'] == 'operacional') ? 'selected' : ''; ?>>Operacional</option>
                            </select>
                        </p>
                        <p><strong>Tipo:</strong>
                            <select name="tipo_trabalhador" id="tipo_trabalhador" required>
                                <option value="">Selecione um tipo de trabalhador</option>
                                <option value="efetivo" <?php echo ($dados['tipo_trabalhador'] == 'efetivo') ? 'selected' : ''; ?>>Trabalhador Efetivo</option>
                                <option value="temporario" <?php echo ($dados['tipo_trabalhador'] == 'temporario') ? 'selected' : ''; ?>>Trabalhador Temporário</option>
                                <option value="estagiario" <?php echo ($dados['tipo_trabalhador'] == 'estagiario') ? 'selected' : ''; ?>>Trabalhador Estagiário</option>
                                <option value="autonomo" <?php echo ($dados['tipo_trabalhador'] == 'autonomo') ? 'selected' : ''; ?>>Trabalhador Autônomo</option>
                                <option value="freelancer" <?php echo ($dados['tipo_trabalhador'] == 'freelancer') ? 'selected' : ''; ?>>Trabalhador Freelancer</option>
                                <option value="terceirizado" <?php echo ($dados['tipo_trabalhador'] == 'terceirizado') ? 'selected' : ''; ?>>Trabalhador Terceirizado</option>
                                <option value="intermitente" <?php echo ($dados['tipo_trabalhador'] == 'intermitente') ? 'selected' : ''; ?>>Trabalhador Intermitente</option>
                                <option value="voluntario" <?php echo ($dados['tipo_trabalhador'] == 'voluntario') ? 'selected' : ''; ?>>Trabalhador Voluntário</option>
                            </select>
                        </p>
                        <p><strong>Estado:</strong> 
                        <select name="estado">
                            <option value="Ativo" <?php echo ($dados['estado'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo ($dados['estado'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="Terminado" <?php echo ($dados['estado'] == 'Terminado') ? 'selected' : ''; ?>>Terminado</option>
                        </select>
                    </p>
                        <p><strong>Data de Admissão:</strong> <input type="date" name="data_admissao" value="<?php echo $dados['data_admissao']; ?>"></p>
                    </div>

                    <!-- Informações Bancárias -->
                    <div class="secao o">
                        <h2>Informações Bancárias</h2>
                        <p><strong>Nº Conta:</strong> <input type="text" name="num_conta_bancaria" value="<?php echo $dados['num_conta_bancaria']; ?>"></p>
                        <p><strong>Banco:</strong>
                            <select name="banco" id="banco" required>
                                <option value="">Selecione um banco</option>
                                <option value="BAI" <?php echo ($dados['banco'] == 'BAI') ? 'selected' : ''; ?>>BAI</option>
                                <option value="BIC" <?php echo ($dados['banco'] == 'BIC') ? 'selected' : ''; ?>>BIC</option>
                            </select>
                        </p>
                        <p><strong>IBAN:</strong> <input type="text" name="iban" value="<?php echo $dados['iban']; ?>"></p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
        }

        // Initial update
        updateTime();
        
        // Update every second
        setInterval(updateTime, 1000);
    </script>
    <script>
                // Function to add lock icon to readonly fields
        function addLockIcon() {
        // Find the num_mecanografico input
        const numMecInput = document.querySelector('input[name="num_mecanografico"]');
        
        if (numMecInput) {
            // Create wrapper div to hold both input and icon
            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block';
            wrapper.style.width = 'calc(55% - 130px)'; // Leave space for label
            
            // Get the parent paragraph that contains the label and input
            const parentP = numMecInput.parentElement;
            
            // Insert wrapper after the label but before the input
            numMecInput.parentNode.insertBefore(wrapper, numMecInput);
            
            // Move input inside wrapper
            wrapper.appendChild(numMecInput);
            
            // Style the input
            numMecInput.style.width = '100%';
            numMecInput.style.backgroundColor = '#f0f0f0';
            numMecInput.style.cursor = 'not-allowed';
            numMecInput.style.paddingRight = '30px';
            numMecInput.readOnly = true;
            
            // Create lock icon
            const lockIcon = document.createElement('i');
            lockIcon.className = 'fas fa-lock';
            lockIcon.style.position = 'absolute';
            lockIcon.style.right = '10px';
            lockIcon.style.top = '50%';
            lockIcon.style.transform = 'translateY(-50%)';
            lockIcon.style.color = '#888';
            lockIcon.style.pointerEvents = 'none';
            
            // Add icon to wrapper
            wrapper.appendChild(lockIcon);
        }
        
        // Increase height of bottom containers
        const professionalSection = document.querySelector('.secao.n');
        const bankingSection = document.querySelector('.secao.o');
        
        if (professionalSection) {
            professionalSection.style.minHeight = '320px';
        }
        
        if (bankingSection) {
            bankingSection.style.minHeight = '320px';
        }
        }

        // Execute when document is fully loaded
        document.addEventListener('DOMContentLoaded', addLockIcon);
    </script>
<script>
    function validateForm() {
        const dataNascimento = document.querySelector('input[name="data_nascimento"]').value;
        const emissaoBi = document.querySelector('input[name="emissao_bi"]').value;
        const validadeBi = document.querySelector('input[name="validade_bi"]').value;
        const hoje = new Date();
        
        // Verificar data de nascimento
        if (dataNascimento) {
            const nascimento = new Date(dataNascimento);
            const anoNascimento = nascimento.getFullYear();
            const anoAtual = hoje.getFullYear();

            // Verificar se a data de nascimento é realista
            if (nascimento > hoje) {
                alert('Data de nascimento não pode ser no futuro!');
                return false; // Impede o envio do formulário
            }
            if (anoNascimento < (anoAtual - 120)) {
                alert('Data de nascimento muito antiga!');
                return false; // Impede o envio do formulário
            }
        }

        // Verificar data de emissão do BI
        if (emissaoBi) {
            const emissao = new Date(emissaoBi);
            const anoEmissao = emissao.getFullYear();

            // Verificar se a data de emissão é no futuro
            if (emissao > hoje) {
                alert('Data de emissão não pode ser no futuro!');
                return false; // Impede o envio do formulário
            }

            // Verificar se a data de emissão é muito antiga
            if (anoEmissao < 1900) {
                alert('Data de emissão do BI não pode ser anterior a 1900!');
                return false; // Impede o envio do formulário
            }
        }

        // Verificar data de validade do BI
        if (validadeBi) {
            const validade = new Date(validadeBi);
            if (validade < new Date(emissaoBi)) {
                alert('A data de validade deve ser posterior à data de emissão!');
                return false; // Impede o envio do formulário
            }
            if (validade < hoje) {
                alert('A data de validade não pode ser no passado!');
                return false; // Impede o envio do formulário
            }
        }

        // Se todas as validações passarem
        return true; // Permite o envio do formulário
    }
</script>
</body>
</html>