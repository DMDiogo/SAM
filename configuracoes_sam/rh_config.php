<?php
session_start();
include('../config.php');
include('../protect.php');

if (!isset($_SESSION['id_adm'])) {
    die("Acesso negado");
}

// Debug: Verificar se o id_adm está na sessão
echo "<!-- Debug: id_adm = " . $_SESSION['id_adm'] . " -->";

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

// Debug: Verificar se encontrou o empresa_id
if ($admin) {
    $empresa_id = $admin['id_empresa'];
    echo "<!-- Debug: empresa_id = " . $empresa_id . " -->";
} else {
    echo "<!-- Debug: Não encontrou empresa_id para o admin " . $admin_id . " -->";
}

// Lista de bancos de Angola
$bancos = [
    ['nome' => 'Banco Angolano de Investimentos (BAI)', 'codigo' => 'BAI', 'logo' => 'bai.png'],
    ['nome' => 'Banco BIC', 'codigo' => 'BIC', 'logo' => 'bic.png'],
    ['nome' => 'Banco Caixa Geral Angola', 'codigo' => 'BCGA', 'logo' => 'bcga.png'],
    ['nome' => 'Banco Comercial Angolano (BCA)', 'codigo' => 'BCA', 'logo' => 'bca.png'],
    ['nome' => 'Banco de Desenvolvimento de Angola (BDA)', 'codigo' => 'BDA', 'logo' => 'bda.png'],
    ['nome' => 'Banco de Poupança e Crédito (BPC)', 'codigo' => 'BPC', 'logo' => 'bpc.png'],
    ['nome' => 'Banco Económico', 'codigo' => 'BE', 'logo' => 'be.png'],
    ['nome' => 'Banco Fomento Angola (BFA)', 'codigo' => 'BFA', 'logo' => 'bfa.png'],
    ['nome' => 'Banco Millennium Atlântico', 'codigo' => 'BMA', 'logo' => 'bma.png'],
    ['nome' => 'Banco Sol', 'codigo' => 'SOL', 'logo' => 'sol.png'],
    ['nome' => 'Banco Valor', 'codigo' => 'VALOR', 'logo' => 'valor.png'],
    ['nome' => 'Banco Yetu', 'codigo' => 'YETU', 'logo' => 'yetu.png'],
    ['nome' => 'Banco VTB África', 'codigo' => 'VTB', 'logo' => 'vtb.png']
];

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($admin) {
        $empresa_id = $admin['id_empresa'];

        // Adicionar departamento
        if (isset($_POST['add_departamento']) && !empty($_POST['nome_departamento'])) {
            $nome = $_POST['nome_departamento'];
            $sql = "INSERT INTO departamentos (nome, empresa_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta de inserção: " . $conn->error);
            }
            $stmt->bind_param("si", $nome, $empresa_id);
            if (!$stmt->execute()) {
                die("Erro ao inserir departamento: " . $stmt->error);
            }
        }

        // Excluir departamento
        if (isset($_POST['delete_departamento']) && !empty($_POST['departamento_id'])) {
            $departamento_id = $_POST['departamento_id'];
            // Primeiro excluir os cargos do departamento
            $sql = "DELETE FROM cargos WHERE departamento_id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta de exclusão de cargos: " . $conn->error);
            }
            $stmt->bind_param("ii", $departamento_id, $empresa_id);
            if (!$stmt->execute()) {
                die("Erro ao excluir cargos: " . $stmt->error);
            }
            // Depois excluir o departamento
            $sql = "DELETE FROM departamentos WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta de exclusão de departamento: " . $conn->error);
            }
            $stmt->bind_param("ii", $departamento_id, $empresa_id);
            if (!$stmt->execute()) {
                die("Erro ao excluir departamento: " . $stmt->error);
            }
        }

        // Adicionar cargo
        if (isset($_POST['add_cargo']) && !empty($_POST['nome_cargo']) && !empty($_POST['departamento_id'])) {
            $nome = $_POST['nome_cargo'];
            $departamento_id = $_POST['departamento_id'];
            $salario_base = $_POST['salario_base'];
            $sql = "INSERT INTO cargos (nome, departamento_id, salario_base, empresa_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta de inserção de cargo: " . $conn->error);
            }
            $stmt->bind_param("sidi", $nome, $departamento_id, $salario_base, $empresa_id);
            if (!$stmt->execute()) {
                die("Erro ao inserir cargo: " . $stmt->error);
            }
        }

        // Excluir cargo
        if (isset($_POST['delete_cargo']) && !empty($_POST['cargo_id'])) {
            $cargo_id = $_POST['cargo_id'];
            $sql = "DELETE FROM cargos WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta de exclusão de cargo: " . $conn->error);
            }
            $stmt->bind_param("ii", $cargo_id, $empresa_id);
            if (!$stmt->execute()) {
                die("Erro ao excluir cargo: " . $stmt->error);
            }
        }

        // Processar formulário de política
        if (isset($_POST['salvar_politica'])) {
            $tipo = $_POST['tipo'];
            $titulo = $_POST['titulo'];
            $descricao = $_POST['descricao'];
            $valor = $_POST['valor'];

            // Verificar se já existe uma política deste tipo
            $sql_check = "SELECT id FROM politicas_trabalho WHERE empresa_id = ? AND tipo = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("is", $empresa_id, $tipo);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Atualizar política existente
                $sql = "UPDATE politicas_trabalho SET titulo = ?, descricao = ?, valor = ? WHERE empresa_id = ? AND tipo = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssis", $titulo, $descricao, $valor, $empresa_id, $tipo);
            } else {
                // Inserir nova política
                $sql = "INSERT INTO politicas_trabalho (empresa_id, tipo, titulo, descricao, valor) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issss", $empresa_id, $tipo, $titulo, $descricao, $valor);
            }

            if (!$stmt->execute()) {
                die("Erro ao salvar política: " . $stmt->error);
            }

            // Redirecionar para evitar reenvio do formulário
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        // Processar atualizações dos bancos
        if (isset($_POST['salvar_bancos'])) {
            // Primeiro, desativar todos os bancos
            $sql_desativar = "UPDATE bancos_ativos SET ativo = 0 WHERE empresa_id = ?";
            $stmt_desativar = $conn->prepare($sql_desativar);
            $stmt_desativar->bind_param("i", $empresa_id);
            $stmt_desativar->execute();

            // Ativar os bancos padrão que foram selecionados
            foreach ($bancos as $banco) {
                if (isset($_POST['banco_' . $banco['codigo']])) {
                    // Verificar se o banco já existe
                    $sql_check = "SELECT id FROM bancos_ativos WHERE empresa_id = ? AND banco_codigo = ?";
                    $stmt_check = $conn->prepare($sql_check);
                    $stmt_check->bind_param("is", $empresa_id, $banco['codigo']);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows > 0) {
                        // Atualizar
                        $sql = "UPDATE bancos_ativos SET ativo = 1 WHERE empresa_id = ? AND banco_codigo = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("is", $empresa_id, $banco['codigo']);
                    } else {
                        // Inserir
                        $sql = "INSERT INTO bancos_ativos (empresa_id, banco_nome, banco_codigo, ativo) VALUES (?, ?, ?, 1)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iss", $empresa_id, $banco['nome'], $banco['codigo']);
                    }
                    $stmt->execute();
                }
            }

            // Ativar os bancos personalizados que foram selecionados
            $sql_outros_bancos = "SELECT id, banco_codigo FROM bancos_ativos WHERE empresa_id = ? AND banco_codigo NOT IN (";
            $placeholders = array_fill(0, count($bancos), '?');
            $sql_outros_bancos .= implode(',', $placeholders) . ')';
            
            $stmt_outros_bancos = $conn->prepare($sql_outros_bancos);
            $params = array_merge([$empresa_id], array_column($bancos, 'codigo'));
            $types = 'i' . str_repeat('s', count($bancos));
            $stmt_outros_bancos->bind_param($types, ...$params);
            $stmt_outros_bancos->execute();
            $result_outros_bancos = $stmt_outros_bancos->get_result();

            while($outro_banco = $result_outros_bancos->fetch_assoc()) {
                if (isset($_POST['banco_' . $outro_banco['banco_codigo']])) {
                    $sql = "UPDATE bancos_ativos SET ativo = 1 WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $outro_banco['id']);
                    $stmt->execute();
                }
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#bancos");
            exit();
        }

        // Processar adição de novo banco
        if (isset($_POST['add_novo_banco']) && !empty($_POST['novo_banco_nome']) && !empty($_POST['novo_banco_codigo'])) {
            $nome = $_POST['novo_banco_nome'];
            $codigo = $_POST['novo_banco_codigo'];

            // Verificar se o banco já existe (pelo código)
            $sql_check = "SELECT id FROM bancos_ativos WHERE empresa_id = ? AND banco_codigo = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("is", $empresa_id, $codigo);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows == 0) {
                // Inserir novo banco
                $sql_insert = "INSERT INTO bancos_ativos (empresa_id, banco_nome, banco_codigo, ativo) VALUES (?, ?, ?, 1)"; // Ativo por padrão
                $stmt_insert = $conn->prepare($sql_insert);
                if (!$stmt_insert) {
                    die("Erro na preparação da consulta de inserção de novo banco: " . $conn->error);
                }
                $stmt_insert->bind_param("iss", $empresa_id, $nome, $codigo);
                if (!$stmt_insert->execute()) {
                    die("Erro ao inserir novo banco: " . $stmt_insert->error);
                }
            } else {
                // Opcional: Tratar caso o banco já exista (ex: exibir mensagem)
                // echo "<!-- Banco com código " . $codigo . " já existe. -->";
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#bancos");
            exit();
        }

        // Processar edição de banco
        if (isset($_POST['editar_banco']) && !empty($_POST['banco_id'])) {
            $banco_id = $_POST['banco_id'];
            $novo_nome = $_POST['edit_banco_nome'];
            $novo_codigo = $_POST['edit_banco_codigo'];
            
            $sql = "UPDATE bancos_ativos SET banco_nome = ?, banco_codigo = ? WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("ssii", $novo_nome, $novo_codigo, $banco_id, $empresa_id);
            
            if (!$stmt->execute()) {
                die("Erro ao atualizar banco: " . $stmt->error);
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#bancos");
            exit();
        }

        // Processar exclusão de banco
        if (isset($_POST['excluir_banco']) && !empty($_POST['banco_id'])) {
            $banco_id = $_POST['banco_id'];
            
            $sql = "DELETE FROM bancos_ativos WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("ii", $banco_id, $empresa_id);
            
            if (!$stmt->execute()) {
                die("Erro ao excluir banco: " . $stmt->error);
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#bancos");
            exit();
        }

        // Processar edição de departamento
        if (isset($_POST['editar_departamento']) && !empty($_POST['departamento_id'])) {
            $departamento_id = $_POST['departamento_id'];
            $novo_nome = $_POST['edit_departamento_nome'];
            
            $sql = "UPDATE departamentos SET nome = ? WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("sii", $novo_nome, $departamento_id, $empresa_id);
            
            if (!$stmt->execute()) {
                die("Erro ao atualizar departamento: " . $stmt->error);
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#departamentos");
            exit();
        }

        // Processar edição de cargo
        if (isset($_POST['editar_cargo']) && !empty($_POST['cargo_id'])) {
            $cargo_id = $_POST['cargo_id'];
            $novo_nome = $_POST['edit_cargo_nome'];
            $novo_salario = $_POST['edit_cargo_salario'];
            $novo_departamento = $_POST['edit_cargo_departamento'];
            
            $sql = "UPDATE cargos SET nome = ?, salario_base = ?, departamento_id = ? WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("sdiis", $novo_nome, $novo_salario, $novo_departamento, $cargo_id, $empresa_id);
            
            if (!$stmt->execute()) {
                die("Erro ao atualizar cargo: " . $stmt->error);
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#cargos");
            exit();
        }

        // Processar adição de novo subsídio personalizado
        if (isset($_POST['add_novo_subsidio']) && !empty($_POST['novo_subsidio_nome']) && !empty($_POST['novo_subsidio_tipo'])) {
            $nome = $_POST['novo_subsidio_nome'];
            $tipo = $_POST['novo_subsidio_tipo'];
            $valor_padrao = isset($_POST['novo_subsidio_valor_padrao']) ? $_POST['novo_subsidio_valor_padrao'] : null;
            $unidade = '';

            // Validar se valor_padrao foi fornecido para tipos que precisam
            if ($tipo === 'valor_fixo' && $valor_padrao === null) {
                $valor_padrao = 0;
            } else if ($tipo === 'percentagem' && $valor_padrao === null) {
                $valor_padrao = 0;
            }

            // Definir a unidade com base no tipo e na base de cálculo (se valor_fixo)
            if ($tipo === 'valor_fixo') {
                $base_calculo = isset($_POST['novo_subsidio_unidade_valor_fixo']) ? $_POST['novo_subsidio_unidade_valor_fixo'] : '/dia útil';
                $unidade = 'Kz' . $base_calculo;
            } else if ($tipo === 'percentagem') {
                $unidade = '%';
            }

            $sql_insert = "INSERT INTO subsidios_personalizados (empresa_id, nome, tipo, valor_padrao, unidade) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            if (!$stmt_insert) {
                die("Erro na preparação da consulta de inserção de novo subsídio: " . $conn->error);
            }
            $stmt_insert->bind_param("issss", $empresa_id, $nome, $tipo, $valor_padrao, $unidade);
            if (!$stmt_insert->execute()) {
                die("Erro ao inserir novo subsídio: " . $stmt_insert->error);
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#subsidios");
            exit();
        }

        // Processar edição de subsídio personalizado
        if (isset($_POST['edit_subsidio']) && !empty($_POST['edit_subsidio_nome']) && !empty($_POST['edit_subsidio_tipo'])) {
            $id = $_POST['edit_subsidio_id'];
            $nome = $_POST['edit_subsidio_nome'];
            $tipo = $_POST['edit_subsidio_tipo'];
            $valor_padrao = isset($_POST['edit_subsidio_valor_padrao']) ? $_POST['edit_subsidio_valor_padrao'] : null;
            $unidade = '';

            // Validar se valor_padrao foi fornecido para tipos que precisam
            if ($tipo === 'valor_fixo' && $valor_padrao === null) {
                $valor_padrao = 0;
            } else if ($tipo === 'percentagem' && $valor_padrao === null) {
                $valor_padrao = 0;
            }

            // Definir a unidade com base no tipo e na base de cálculo (se valor_fixo)
            if ($tipo === 'valor_fixo') {
                $base_calculo = isset($_POST['edit_subsidio_unidade_valor_fixo']) ? $_POST['edit_subsidio_unidade_valor_fixo'] : '/dia útil';
                $unidade = 'Kz' . $base_calculo;
            } else if ($tipo === 'percentagem') {
                $unidade = '%';
            }

            $sql_update = "UPDATE subsidioss_personalizados SET nome = ?, tipo = ?, valor_padrao = ?, unidade = ? WHERE id = ? AND empresa_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            if (!$stmt_update) {
                die("Erro na preparação da consulta de atualização: " . $conn->error);
            }
            $stmt_update->bind_param("ssssii", $nome, $tipo, $valor_padrao, $unidade, $id, $empresa_id);
            if (!$stmt_update->execute()) {
                die("Erro ao atualizar subsídio: " . $stmt_update->error);
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#subsidios");
            exit();
        }

        // Processar exclusão de subsídio personalizado
        if (isset($_POST['delete_subsidio']) && !empty($_POST['delete_subsidio_id'])) {
            $id = $_POST['delete_subsidio_id'];
            
            $sql_delete = "DELETE FROM subsidioss_personalizados WHERE id = ? AND empresa_id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            if (!$stmt_delete) {
                die("Erro na preparação da consulta de exclusão: " . $conn->error);
            }
            $stmt_delete->bind_param("ii", $id, $empresa_id);
            if (!$stmt_delete->execute()) {
                die("Erro ao excluir subsídio: " . $stmt_delete->error);
            }
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#subsidios");
            exit();
        }
    }

    // Redirecionar para evitar reenvio do formulário
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Buscar políticas existentes
$sql_politicas = "SELECT * FROM politicas_trabalho WHERE empresa_id = ?";
$stmt_politicas = $conn->prepare($sql_politicas);
$stmt_politicas->bind_param("i", $empresa_id);
$stmt_politicas->execute();
$result_politicas = $stmt_politicas->get_result();

$politicas = array();
while ($row = $result_politicas->fetch_assoc()) {
    $politicas[$row['tipo']] = $row;
}

// Buscar bancos ativos
$sql_ativos = "SELECT banco_codigo FROM bancos_ativos WHERE empresa_id = ? AND ativo = 1";
$stmt_ativos = $conn->prepare($sql_ativos);
$stmt_ativos->bind_param("i", $empresa_id);
$stmt_ativos->execute();
$result_ativos = $stmt_ativos->get_result();
$bancos_ativos = [];
while ($row = $result_ativos->fetch_assoc()) {
    $bancos_ativos[] = $row['banco_codigo'];
}

// Consultar subsídios personalizados
$sql_subsidios = "SELECT * FROM subsidios_personalizados WHERE empresa_id = ? ORDER BY nome";
$stmt_subsidios = $conn->prepare($sql_subsidios);
if (!$stmt_subsidios) {
    die("Erro na preparação da consulta de subsídios: " . $conn->error);
}
$stmt_subsidios->bind_param("i", $empresa_id);
if (!$stmt_subsidios->execute()) {
    die("Erro ao consultar subsídios: " . $stmt_subsidios->error);
}
$result_subsidios = $stmt_subsidios->get_result();

$subsidios_personalizados = [];
while ($row = $result_subsidios->fetch_assoc()) {
    $subsidios_personalizados[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Configurações de RH - Dashboard RH</title>
    <link rel="stylesheet" href="../all.css/registro3.css">
    <link rel="stylesheet" href="../all.css/configuracoes.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #3EB489;
            --background-light: #f4f4f4;
            --text-color: #333;
            --white: #ffffff;
            --border-color: #e0e0e0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-light);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .dashboard-container {
            display: flex;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
            background-color: var(--background-light);
        }

        .profile-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .profile-card h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .rh-section {
            background-color: var(--background-light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .rh-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .rh-details {
            background-color: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .policies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .policy-card {
            background-color: var(--background-light);
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .policy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .policy-card h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #32a177;
        }

        /* Dark Mode Styles */
        body.dark {
            background-color: #121212;
            color: #e0e0e0;
        }

        body.dark .dashboard-container {
            background-color: #1e1e1e;
        }

        body.dark .sidebar {
            background-color: #1a1a1a;
            box-shadow: 2px 0 5px rgba(0,0,0,0.3);
        }

        body.dark .sidebar .logo {
            border-bottom: 1px solid #333;
        }

        body.dark .sidebar .logo img {
            filter: brightness(0.8) contrast(1.2);
        }

        body.dark .sidebar .nav-select {
            background-color: #262626;
            color: #e0e0e0;
            border-color: #444;
        }

        body.dark .nav-menu li {
            color: #b0b0b0;
        }

        body.dark .nav-menu li:hover {
            background-color: rgba(62, 180, 137, 0.2);
            color: var(--primary-color);
        }

        body.dark .nav-menu li.active {
            background-color: rgba(62, 180, 137, 0.2);
            color: var(--primary-color);
        }

        body.dark .main-content {
            background-color: #2a2a2a;
        }

        body.dark .profile-card {
            background-color: #262626;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        body.dark .profile-card h1 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        body.dark .rh-section {
            background-color: #1a1a1a;
        }

        body.dark .rh-section h3 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        body.dark .rh-details {
            background-color: #1f1f1f;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        body.dark .policy-card {
            background-color: #1a1a1a;
        }

        body.dark .policy-card h4 {
            color: var(--primary-color);
        }

        body.dark .detail-item {
            border-bottom: 1px solid #333;
        }

        body.dark .btn-primary {
            background-color: var(--primary-color);
            color: #f4f4f4;
        }

        body.dark .btn-primary:hover {
            background-color: #3EB489;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: var(--white);
            border-radius: 8px;
        }

        .form-group {
            display: flex;
            gap: 1px;
            margin-bottom: 15px;
        }

        .form-group input,
        .form-group select {
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            flex: 1;
        }

        .departamento-item,
        .cargo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: var(--white);
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .acoes {
            display: flex;
            gap: 10px;
        }

        .btn-edit,
        .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .dark .form-section {
            background-color: #262626;
        }

        .dark .departamento-item,
        .dark .cargo-item {
            background-color: #262626;
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .data-table th {
            background-color: #3EB489;
            color: white;
            font-weight: 500;
        }

        .data-table tr:hover {
            background-color: #f5f5f5;
        }

        .delete-btn {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #cc0000;
        }

        /* Dark mode styles */
        body.dark .data-table {
            background: #2C2C2C;
            color: #e0e0e0;
        }

        body.dark .data-table th {
            background-color: #64c2a7;
        }

        body.dark .data-table tr:hover {
            background-color: #3C3C3C;
        }

        body.dark .data-table td {
            border-bottom: 1px solid #444;
        }

        /* Estilos para os formulários de departamento e cargo */
        .form-container {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px; /* Aumentar margem para não colar na próxima seção */
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .form-container h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .form-container form {
            display: flex;
            flex-direction: row; /* Elementos lado a lado */
            flex-wrap: wrap; /* Quebrar linha se necessário */
            gap: 20px; /* Espaçamento maior entre os elementos */
        }

        .form-container input,
        .form-container select {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            flex: 1;
            min-width: 200px;
        }

        .form-container button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #32a177;
        }

        /* Dark mode para os formulários */
        body.dark .form-container {
            background-color: #262626;
        }

        body.dark .form-container h3 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        body.dark .form-container input,
        body.dark .form-container select {
            background-color: #2C2C2C;
            color: #e0e0e0;
            border: 1px solid #444;
        }

        body.dark .form-container input::placeholder {
            color: #888;
        }

        /* Estilos para a seção de departamentos e cargos */
        .section {
            background-color: var(--white);
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .section h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        /* Dark mode para a seção */
        body.dark .section {
            background-color: #262626;
        }

        body.dark .section h2 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        /* Ajustes responsivos */
        @media (max-width: 768px) {
            .form-container form {
                flex-direction: column;
            }

            .form-container input,
            .form-container select,
            .form-container button {
                width: 100%;
            }
        }

        /* Estilos do Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #000;
        }

        /* Dark mode para o modal */
        body.dark .modal-content {
            background-color: #262626;
            color: #e0e0e0;
        }

        body.dark .close {
            color: #999;
        }

        body.dark .close:hover {
            color: #fff;
        }

        /* Estilos adicionais para o formulário do modal */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 5px;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal;
        }

        .intervalo-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .intervalo-group input {
            width: 120px;
        }

        body.dark .form-group input,
        body.dark .form-group textarea {
            background-color: #333;
            color: #fff;
            border-color: #444;
        }

        body.dark .checkbox-group label {
            color: #e0e0e0;
        }

        .dias-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .dia-btn {
            padding: 8px 16px;
            border: 2px solid var(--primary-color);
            border-radius: 6px;
            background: transparent;
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            min-width: 100px;
        }

        .dia-btn:hover {
            background-color: rgba(62, 180, 137, 0.1);
        }

        .dia-btn.selecionado {
            background-color: var(--primary-color);
            color: white;
        }

        body.dark .dia-btn {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        body.dark .dia-btn:hover {
            background-color: rgba(62, 180, 137, 0.2);
        }

        body.dark .dia-btn.selecionado {
            background-color: var(--primary-color);
            color: white;
        }

        .bancos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .banco-card {
            background: white;
            border-radius: 10px;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            position: relative;
        }

        .banco-card:hover {
            transform: translateY(-2px);
        }

        .banco-card:hover::after,
        .banco-card:hover::before {
            content: none;
        }

        .banco-logo {
            display: none;
        }

        .banco-info {
            flex-grow: 1;
            margin-right: 15px;
        }

        .banco-nome {
            font-weight: 500;
            margin-bottom: 0;
        }

        .banco-codigo {
            color: #666;
            font-size: 0.9em;
        }

        .banco-toggle {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .banco-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #3EB489;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Dark mode styles */
        body.dark .banco-card {
            background: #262626;
        }

        body.dark .banco-nome {
            color: #e0e0e0;
        }

        body.dark .banco-codigo {
            color: #999;
        }

        .banco-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-edit,
        .btn-delete {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            font-size: 16px;
        }

        .btn-edit {
            color: #3EB489;
        }

        .btn-delete {
            color: #f44336;
        }

        .btn-edit:hover,
        .btn-delete:hover {
            transform: scale(1.1);
        }

        .btn-edit i,
        .btn-delete i {
            font-size: 18px;
        }

        /* Dark mode para os botões */
        body.dark .btn-edit {
            color: #3EB489;
        }

        body.dark .btn-delete {
            color: #f44336;
        }

        body.dark .btn-edit:hover,
        body.dark .btn-delete:hover {
            opacity: 1;
        }

        /* Modal de Edição de Banco */
        .modal-banco {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-banco-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        .modal-banco .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }

        .modal-banco .close:hover {
            color: #000;
        }

        /* Dark mode para o modal de banco */
        body.dark .modal-banco-content {
            background-color: #262626;
            color: #e0e0e0;
        }

        body.dark .modal-banco .close {
            color: #999;
        }

        body.dark .modal-banco .close:hover {
            color: #fff;
        }

        /* Estilos para a seção de subsídios */
        .subsidios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        #subsidios .subsidio-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        #subsidios .subsidio-card:hover {
            transform: translateY(-2px);
        }

        #subsidios .subsidio-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        #subsidios .subsidio-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.1em;
        }

        #subsidios .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 500;
        }

        #subsidios .badge.obrigatorio {
            background-color: #fde9e9;
            color: #d32f2f;
        }

        #subsidios .badge.opcional {
            background-color: #f5f5f5;
            color: #666;
        }

        #subsidios .badge.personalizado {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        #subsidios .subsidio-info {
            color: #666;
            flex-grow: 1;
        }

        #subsidios .subsidio-desc {
            font-size: 0.9em;
            margin-top: 5px;
            color: #888;
        }

        #subsidios .valor-range {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }

        #subsidios .slider {
            flex: 1;
            height: 4px;
            -webkit-appearance: none;
            background: #ddd;
            border-radius: 2px;
            outline: none;
        }

        #subsidios .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            background: #3EB489;
            border-radius: 50%;
            cursor: pointer;
        }

        #subsidios .valor-atual {
            min-width: 50px;
            text-align: right;
            font-weight: 500;
        }

        #subsidios .valor-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }

        #subsidios .valor-subsidio {
            width: 100px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: right;
        }

        #subsidios .moeda {
            color: #666;
            font-size: 0.9em;
        }

        #subsidios .personalizavel {
            margin-top: 10px;
            font-size: 0.9em;
        }

        #subsidios .personalizavel label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        /* Dark mode styles */
        body.dark #subsidios .subsidio-card {
            background: #262626;
        }

        body.dark #subsidios .subsidio-header h3 {
            color: #e0e0e0;
        }

        body.dark #subsidios .badge.obrigatorio {
            background-color: #1a237e;
            color: #e0e0e0;
        }

        body.dark #subsidios .badge.opcional {
            background-color: #424242;
            color: #e0e0e0;
        }

        body.dark #subsidios .badge.personalizado {
            background-color: #0d47a1;
            color: #e0e0e0;
        }

        body.dark #subsidios .subsidio-info {
            color: #b0b0b0;
        }

        body.dark #subsidios .subsidio-desc {
            color: #888;
        }

        body.dark #subsidios .slider {
            background: #444;
        }

        body.dark #subsidios .valor-subsidio {
            background: #333;
            color: #e0e0e0;
            border-color: #444;
        }

        body.dark #subsidios .moeda {
            color: #b0b0b0;
        }

        /* Estilos específicos para o formulário de adicionar subsídio */
        #subsidios .form-container {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px; /* Aumentar margem para não colar na próxima seção */
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        body.dark #subsidios .form-container {
            background-color: #262626;
        }

        #subsidios .form-container h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        #subsidios .form-container form {
            display: flex;
            flex-direction: row; /* Elementos lado a lado */
            flex-wrap: wrap; /* Quebrar linha se necessário */
            gap: 20px; /* Espaçamento maior entre os elementos */
            align-items: flex-start; /* Alinhar itens pelo topo */
        }

        #subsidios .form-group {
             margin-bottom: 0; /* Remover margem inferior padrão do form-group */
             min-width: 150px; /* Largura mínima para evitar que fiquem muito pequenos */
             flex: 1; /* Permitir que os grupos cresçam */
             display: flex; /* Usar flexbox */
             flex-direction: column; /* Empilhar label e input */
             justify-content: flex-start; /* Alinhar conteúdo ao topo dentro do grupo */
             align-items: flex-start; /* Alinhar itens do grupo ao topo */
         }

         #subsidios .form-group label {
             display: block;
             margin-bottom: 2px; /* Reduzido de 5px para 2px */
             font-weight: 500;
             color: var(--text-color);
         }

         body.dark #subsidios .form-group label {
              color: #e0e0e0;
         }

         #subsidios .form-group input[type="text"],
         #subsidios .form-group input[type="number"],
         #subsidios .form-group select {
             padding: 8px; /* Reduzido de 10px para 8px */
             border: 1px solid var(--border-color);
             border-radius: 4px;
             box-sizing: border-box; /* Incluir padding e borda no tamanho */
             background-color: var(--white);
             color: var(--text-color);
             height: 36px; /* Reduzido de 40px para 36px */
             width: 100%; /* Ocupar largura total do flex item */
             line-height: 20px; /* Ajustar line-height para melhor alinhamento do texto */
             margin-top: 0; /* Garantir que não há margem extra no topo */
             margin-bottom: 0; /* Garantir que não há margem extra na base */
         }

         body.dark #subsidios .form-group input[type="text"],
         body.dark #subsidios .form-group input[type="number"],
         body.dark #subsidios .form-group select {
             background-color: #333;
             color: #e0e0e0;
             border-color: #444;
         }

         #subsidios .form-group input::placeholder,
         body.dark #subsidios .form-group input::placeholder {
              color: #888;
         }

         /* Estilo para o grupo do checkbox de personalização */
         #subsidios .form-group:last-child {
             flex: 1 1 100%; /* Ocupar a largura total para ficar numa linha própria */
             display: flex; /* Usar flexbox */
             align-items: center; /* Alinhar verticalmente */
             margin-top: 10px; /* Espaço acima do checkbox */
             min-width: unset; /* Remover largura mínima */
             flex-direction: row; /* Manter lado a lado */
             justify-content: flex-start;
         }

         #subsidios .form-group:last-child label {
             display: flex; /* Flexbox para label e checkbox */
             align-items: center; /* Alinhar verticalmente */
             margin-bottom: 0; /* Remover margem inferior */
             font-weight: normal; /* Remover negrito */
             gap: 5px; /* Espaço entre checkbox e texto */
             cursor: pointer;
         }

         #subsidios .form-group:last-child input[type="checkbox"] {
             accent-color: var(--primary-color); /* Usa a variável CSS para a cor verde */
             margin-right: 0; /* Remover margem à direita extra */
         }

         #subsidios .form-container button[type="submit"] {
              align-self: flex-start; /* Alinhar o botão à esquerda */
              /* Reutiliza estilos do btn-primary */
         }

         /* Ajustes responsivos para o formulário de subsídio */
         @media (max-width: 768px) {
              #subsidios .form-container form {
                  flex-direction: column; /* Empilhar em telas pequenas */
                  gap: 15px;
                  align-items: stretch; /* Esticar itens em telas pequenas */
              }
              #subsidios .form-group {
                  min-width: unset; /* Remover largura mínima em telas pequenas */
                  flex: unset; /* Remover flex-grow/shrink */
                  flex-direction: column; /* Voltar a empilhar label e input */
                  justify-content: flex-start;
              }
              #subsidios .form-group input[type="text"],
              #subsidios .form-group input[type="number"],
              #subsidios .form-group select {
                   height: auto; /* Remover altura fixa em telas pequenas */
                   width: 100%;
               }
               #subsidios .form-group:last-child {
                   flex-direction: row; /* Manter lado a lado em telas pequenas */
                   align-items: center;
                   margin-top: 10px;
                   flex: unset;
               }
               #subsidios .form-group:last-child label {
                   flex-direction: row; /* Manter lado a lado em telas pequenas */
                   align-items: center;
               }
           }

        #subsidios .subsidio-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        #subsidios .btn-edit-subsidio,
        #subsidios .btn-delete-subsidio {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #subsidios .btn-edit-subsidio {
            background-color: #4CAF50;
            color: white;
        }

        #subsidios .btn-delete-subsidio {
            background-color: #f44336;
            color: white;
        }

        #subsidios .btn-edit-subsidio:hover {
            background-color: #45a049;
        }

        #subsidios .btn-delete-subsidio:hover {
            background-color: #da190b;
        }

        /* Modal de edição de subsídio */
        .modal-subsidio {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-subsidio-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        body.dark .modal-subsidio-content {
            background-color: #262626;
            color: #e0e0e0;
        }

        .modal-subsidio .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }

        body.dark .modal-subsidio .close {
            color: #999;
        }

        .modal-subsidio .close:hover {
            color: #000;
        }

        body.dark .modal-subsidio .close:hover {
            color: #fff;
        }

        .modal-subsidio-content .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .modal-subsidio-content .form-group label {
            font-weight: 500;
            color: var(--text-color);
        }

        .modal-subsidio-content .form-group input[type="text"],
        .modal-subsidio-content .form-group input[type="number"],
        .modal-subsidio-content .form-group select {
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        body.dark .modal-subsidio-content .form-group label {
            color: #e0e0e0;
        }

        body.dark .modal-subsidio-content .form-group input[type="text"],
        body.dark .modal-subsidio-content .form-group input[type="number"],
        body.dark .modal-subsidio-content .form-group select {
            background-color: #333;
            color: #e0e0e0;
            border-color: #444;
        }

        .modal-subsidio-content .form-group:last-child {
            flex-direction: row;
            align-items: center;
            gap: 8px;
        }

        .modal-subsidio-content .form-group:last-child label {
            font-weight: normal;
            margin-bottom: 0;
        }

        /* Estilos para a tabela de funcionários */
        .funcionarios-lista {
            margin-top: 20px;
            max-height: 500px;
            overflow-y: auto;
        }

        .funcionarios-lista table {
            width: 100%;
            border-collapse: collapse;
        }

        .funcionarios-lista th,
        .funcionarios-lista td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .funcionarios-lista th {
            background-color: var(--primary-color);
            color: white;
            position: sticky;
            top: 0;
        }

        .funcionarios-lista tr:hover {
            background-color: rgba(0,0,0,0.05);
        }

        body.dark .funcionarios-lista tr:hover {
            background-color: rgba(255,255,255,0.05);
        }

        .funcionarios-lista .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .funcionarios-lista .status-badge.ativo {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .funcionarios-lista .status-badge.inativo {
            background-color: #ffebee;
            color: #c62828;
        }

        body.dark .funcionarios-lista .status-badge.ativo {
            background-color: #1b5e20;
            color: #e0e0e0;
        }

        body.dark .funcionarios-lista .status-badge.inativo {
            background-color: #b71c1c;
            color: #e0e0e0;
        }

        /* Estilo para o switch de ativar/desativar */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider-switch {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider-switch:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider-switch {
            background-color: var(--primary-color);
        }

        input:checked + .slider-switch:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <div class="logo">
            <a href="../UI.php">
                <img src="../img/sam2logo-32.png" alt="SAM Logo">
            </a>
        </div>
        <select class="nav-select">
            <option>sam</option>
        </select>
        <ul class="nav-menu">           
            <a href="conf.sistema.php"><li>Configurações do Sistema</li></a>
            <a href="perfil_adm.php"><li>Perfil do Usuário</li></a>
            <a href="seguranca.php"><li>Segurança</li></a>
            <a href="privacidade.php"><li>Privacidade</li></a>
            <a href="rh_config.php"><li class="active">Configurações de RH</li></a>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="profile-card">
            <h1>Configurações de Recursos Humanos</h1>

            <div class="rh-section">
                <h3>Políticas de Trabalho</h3>
                <div class="rh-details">
                    <div class="policies-grid">
                        <div class="policy-card">
                            <h4>Horário de Trabalho</h4>
                            <p>Segunda a Sexta: 8h - 18h</p>
                            <button class="btn-primary" onclick="abrirModal('horario')">Editar</button>
                        </div>
                        <div class="policy-card">
                            <h4>Política de Home Office</h4>
                            <p>2 dias por semana permitidos</p>
                            <button class="btn-primary" onclick="abrirModal('homeoffice')">Editar</button>
                        </div>
                        <div class="policy-card">
                            <h4>Código de Vestimenta</h4>
                            <p>Vestuário casual e profissional</p>
                            <button class="btn-primary" onclick="abrirModal('vestimenta')">Editar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rh-section">
                <h3>Férias e Licenças</h3>
                <div class="rh-details">
                    <div class="detail-item">
                        <div>
                            <strong>Dias de Férias Disponíveis</strong>
                            <p>30 dias por ano</p>
                        </div>
                        <button class="btn-primary">Configurar</button>
                    </div>
                    <div class="detail-item">
                        <div>
                            <strong>Tipos de Licença</strong>
                            <p>Maternidade, Paternidade, Médica</p>
                        </div>
                        <button class="btn-primary">Gerenciar</button>
                    </div>
                </div>
            </div>

            <div class="rh-section">
                <h3>Benefícios</h3>
                <div class="rh-details">
                    <div class="policies-grid">
                        <div class="policy-card">
                            <h4>Plano de Saúde</h4>
                            <p>Cobertura para funcionário e dependentes</p>
                            <button class="btn-primary">Personalizar</button>
                        </div>
                        <div class="policy-card">
                            <h4>Vale Refeição</h4>
                            <p>R$ 35,00 por dia útil</p>
                            <button class="btn-primary">Ajustar</button>
                        </div>
                        <div class="policy-card">
                            <h4>Auxílio Educação</h4>
                            <p>Reembolso de 50% de cursos</p>
                            <button class="btn-primary">Detalhes</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rh-section">
                <h3>Avaliação de Desempenho</h3>
                <div class="rh-details">
                    <div class="detail-item">
                        <div>
                            <strong>Ciclo de Avaliação</strong>
                            <p>Semestral, com feedback contínuo</p>
                        </div>
                        <button class="btn-primary">Configurar</button>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>Gestão de Departamentos e Cargos</h2>
                
                <!-- Formulário para adicionar departamento -->
                <div class="form-container">
                    <h3>Adicionar Departamento</h3>
                    <form action="rh_config.php" method="POST">
                        <input type="text" name="nome_departamento" placeholder="Nome do Departamento" required>
                        <button type="submit" name="add_departamento">Adicionar Departamento</button>
                    </form>
                </div>

                <!-- Tabela de Departamentos -->
                <div class="table-container">
                    <h3>Departamentos Cadastrados</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome do Departamento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($empresa_id)) {
                                $sql_dept = "SELECT id, nome FROM departamentos WHERE empresa_id = ? ORDER BY nome";
                                $stmt_dept = $conn->prepare($sql_dept);
                                if (!$stmt_dept) {
                                    echo "Erro na preparação da consulta: " . $conn->error;
                                } else {
                                    $stmt_dept->bind_param("i", $empresa_id);
                                    $stmt_dept->execute();
                                    $result_dept = $stmt_dept->get_result();

                                    if ($result_dept->num_rows > 0) {
                                        while($dept = $result_dept->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>".$dept['nome']."</td>";
                                            echo "<td>
                                                    <div class='acoes'>
                                                        <button type='button' class='btn-edit' onclick='editarDepartamento(".$dept['id'].", \"".htmlspecialchars($dept['nome'])."\")'>
                                                            <i class='fas fa-pencil-alt'></i>
                                                        </button>
                                                    <form action='rh_config.php' method='POST' style='display:inline;'>
                                                        <input type='hidden' name='departamento_id' value='".$dept['id']."'>
                                                            <button type='submit' name='delete_departamento' class='btn-delete'>
                                                                <i class='fas fa-trash-alt'></i>
                                                            </button>
                                                    </form>
                                                    </div>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='2'>Nenhum departamento cadastrado</td></tr>";
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Formulário para adicionar cargo -->
                <div class="form-container">
                    <h3>Adicionar Cargo</h3>
                    <form action="rh_config.php" method="POST">
                        <select name="departamento_id" required>
                            <option value="">Selecione o Departamento</option>
                            <?php
                            if (isset($empresa_id)) {
                                $sql = "SELECT * FROM departamentos WHERE empresa_id = ? ORDER BY nome";
                                $stmt = $conn->prepare($sql);
                                if (!$stmt) {
                                    echo "Erro na preparação da consulta: " . $conn->error;
                                } else {
                                    $stmt->bind_param("i", $empresa_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='".$row['id']."'>".$row['nome']."</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                        <input type="text" name="nome_cargo" placeholder="Nome do Cargo" required>
                        <input type="number" name="salario_base" placeholder="Salário Base" step="0.01" required>
                        <button type="submit" name="add_cargo">Adicionar Cargo</button>
                    </form>
                </div>

                <!-- Tabela de Cargos -->
                <div class="table-container">
                    <h3>Cargos Cadastrados</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Departamento</th>
                                <th>Cargo</th>
                                <th>Salário Base</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($empresa_id)) {
                                $sql_cargos = "SELECT c.id, c.nome as cargo_nome, c.salario_base, c.departamento_id, d.nome as dept_nome 
                                              FROM cargos c 
                                              INNER JOIN departamentos d ON c.departamento_id = d.id 
                                              WHERE c.empresa_id = ? 
                                              ORDER BY d.nome, c.nome";
                                $stmt_cargos = $conn->prepare($sql_cargos);
                                if (!$stmt_cargos) {
                                    echo "Erro na preparação da consulta: " . $conn->error;
                                } else {
                                    $stmt_cargos->bind_param("i", $empresa_id);
                                    $stmt_cargos->execute();
                                    $result_cargos = $stmt_cargos->get_result();

                                    if ($result_cargos->num_rows > 0) {
                                        while($cargo = $result_cargos->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>".$cargo['dept_nome']."</td>";
                                            echo "<td>".$cargo['cargo_nome']."</td>";
                                            echo "<td>".number_format($cargo['salario_base'], 2, ',', '.')." KZs</td>";
                                            echo "<td>
                                                    <div class='acoes'>
                                                        <button type='button' class='btn-edit' onclick='editarCargo(".$cargo['id'].", \"".htmlspecialchars($cargo['cargo_nome'])."\", ".$cargo['salario_base'].", ".$cargo['departamento_id'].")'>
                                                            <i class='fas fa-pencil-alt'></i>
                                                        </button>
                                                    <form action='rh_config.php' method='POST' style='display:inline;'>
                                                        <input type='hidden' name='cargo_id' value='".$cargo['id']."'>
                                                            <button type='submit' name='delete_cargo' class='btn-delete'>
                                                                <i class='fas fa-trash-alt'></i>
                                                            </button>
                                                    </form>
                                                    </div>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4'>Nenhum cargo cadastrado</td></tr>";
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section" id="bancos">
            <h2>Configuração de Bancos</h2>
            <p>Selecione os bancos que deseja disponibilizar para pagamentos ou adicione um novo banco.</p>

            <!-- Formulário para adicionar novo banco -->
            <div class="form-container">
                <h3>Adicionar Novo Banco</h3>
                <form action="rh_config.php" method="POST">
                    <input type="text" name="novo_banco_nome" placeholder="Nome do Banco" required>
                    <input type="text" name="novo_banco_codigo" placeholder="Código do Banco" required>
                    <button type="submit" name="add_novo_banco">Adicionar Banco</button>
                </form>
    </div>

            <form method="POST">
                <div class="bancos-grid">
                    <?php foreach ($bancos as $banco): ?>
                        <div class="banco-card" data-tooltip="<?php echo $banco['nome']; ?>">
                            <div class="banco-info">
                                <div class="banco-nome"><?php echo $banco['nome']; ?></div>
                                <div class="banco-codigo"><?php echo $banco['codigo']; ?></div>
                            </div>
                            <div class="banco-actions">
                                <label class="banco-toggle">
                                    <input type="checkbox" name="banco_<?php echo $banco['codigo']; ?>" 
                                           <?php echo in_array($banco['codigo'], $bancos_ativos) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php
                    // Buscar outros bancos adicionados manualmente
                    $sql_outros_bancos = "SELECT id, banco_nome, banco_codigo FROM bancos_ativos WHERE empresa_id = ? AND banco_codigo NOT IN (";
                    $placeholders = array_fill(0, count($bancos), '?');
                    $sql_outros_bancos .= implode(',', $placeholders) . ')';
                    
                    $stmt_outros_bancos = $conn->prepare($sql_outros_bancos);
                    
                    if (!$stmt_outros_bancos) {
                         die("Erro na preparação da consulta de outros bancos: " . $conn->error);
                    }
                    
                    $params = array_merge([$empresa_id], array_column($bancos, 'codigo'));
                    $types = 'i' . str_repeat('s', count($bancos));

                    $stmt_outros_bancos->bind_param($types, ...$params);
                    $stmt_outros_bancos->execute();
                    $result_outros_bancos = $stmt_outros_bancos->get_result();

                    while($outro_banco = $result_outros_bancos->fetch_assoc()):
                        // Verificar se o banco não está na lista padrão
                        $eh_banco_padrao = false;
                        foreach ($bancos as $banco_padrao) {
                            if ($banco_padrao['codigo'] === $outro_banco['banco_codigo']) {
                                $eh_banco_padrao = true;
                                break;
                            }
                        }
                        
                        if (!$eh_banco_padrao):
                    ?>
                         <div class="banco-card" data-tooltip="<?php echo $outro_banco['banco_nome']; ?>">
                            <div class="banco-info">
                                <div class="banco-nome"><?php echo $outro_banco['banco_nome']; ?></div>
                                <div class="banco-codigo"><?php echo $outro_banco['banco_codigo']; ?></div>
                            </div>
                            <div class="banco-actions">
                                <label class="banco-toggle">
                                    <input type="checkbox" name="banco_<?php echo $outro_banco['banco_codigo']; ?>" 
                                           <?php echo in_array($outro_banco['banco_codigo'], $bancos_ativos) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <button type="button" class="btn-edit" onclick="editarBanco(<?php echo $outro_banco['id']; ?>, '<?php echo htmlspecialchars($outro_banco['banco_nome']); ?>', '<?php echo htmlspecialchars($outro_banco['banco_codigo']); ?>')">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn-delete" onclick="excluirBanco(<?php echo $outro_banco['id']; ?>)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endwhile; 
                    ?>
                </div>
                <button type="submit" name="salvar_bancos" class="btn-primary">Salvar Configurações dos Bancos</button>
            </form>
        </div>

        <!-- Nova Seção de Subsídios -->
        <div class="section" id="subsidios">
            <h2>Configuração de Subsídios</h2>
            <p>Configure os subsídios padrão da empresa e defina quais podem ser personalizados por funcionário.</p>

            <!-- Formulário para adicionar novo subsídio -->
            <div class="form-container" id="add-subsidio-form">
                <h3>Adicionar Novo Subsídio</h3>
                <form action="rh_config.php" method="POST">
                    <div class="form-group">
                        <label for="novo_subsidio_nome">Nome do Subsídio:</label>
                        <input type="text" name="novo_subsidio_nome" id="novo_subsidio_nome" placeholder="Nome do Subsídio" required>
                    </div>
                    <div class="form-group">
                        <label for="novo_subsidio_tipo">Tipo:</label>
                        <select name="novo_subsidio_tipo" id="novo_subsidio_tipo" required>
                            <option value="">Selecione o Tipo</option>
                            <option value="valor_fixo">Valor Fixo</option>
                            <option value="percentagem">Percentagem</option>
                        </select>
                    </div>
                    <div class="form-group" id="novo_subsidio_unidade_tipo" style="display: none;">
                        <label for="novo_subsidio_unidade_valor_fixo">Base de Cálculo:</label>
                        <select name="novo_subsidio_unidade_valor_fixo" id="novo_subsidio_unidade_valor_fixo">
                            <option value="/dia útil">Por Dia Útil</option>
                            <option value="/mês">Por Mês</option>
                        </select>
                    </div>
                    <div class="form-group" id="novo_subsidio_valor_padrao_group">
                        <label for="novo_subsidio_valor_padrao">Valor Padrão:</label>
                        <input type="number" name="novo_subsidio_valor_padrao" id="novo_subsidio_valor_padrao" placeholder="Ex: 1000" step="0.01">
                    </div>
                    <button type="submit" name="add_novo_subsidio" class="btn-primary">Adicionar Subsídio</button>
                </form>
            </div>

            <div class="subsidios-grid">
                <!-- Subsídios Obrigatórios -->
                <div class="subsidio-card obrigatorio" data-subsidio-id="1">
                    <div class="subsidio-header">
                        <h3>Férias</h3>
                        <span class="badge obrigatorio">Obrigatório</span>
                    </div>
                    <div class="subsidio-info">
                        <p>100% do salário base</p>
                        <p class="subsidio-desc">Subsídio obrigatório por lei</p>
                    </div>
                </div>

                <div class="subsidio-card obrigatorio" data-subsidio-id="2">
                    <div class="subsidio-header">
                        <h3>13.º Mês</h3>
                        <span class="badge obrigatorio">Obrigatório</span>
                    </div>
                    <div class="subsidio-info">
                        <p>100% do salário base</p>
                        <p class="subsidio-desc">Subsídio obrigatório por lei</p>
                    </div>
                </div>

                <div class="subsidio-card obrigatorio" data-subsidio-id="3">
                    <div class="subsidio-header">
                        <h3>Nocturno / Turno</h3>
                        <span class="badge obrigatorio">Obrigatório</span>
                    </div>
                    <div class="subsidio-info">
                        <div class="valor-range">
                            <input type="range" min="20" max="50" value="35" class="slider" id="nocturnoRange">
                            <span class="valor-atual">35%</span>
                        </div>
                        <p class="subsidio-desc">Percentual sobre o salário base</p>
                    </div>
                </div>

                <div class="subsidio-card obrigatorio" data-subsidio-id="4">
                    <div class="subsidio-header">
                        <h3>Risco / Periculosidade</h3>
                        <span class="badge obrigatorio">Obrigatório</span>
                    </div>
                    <div class="subsidio-info">
                        <div class="valor-range">
                            <input type="range" min="15" max="30" value="20" class="slider" id="riscoRange">
                            <span class="valor-atual">20%</span>
                        </div>
                        <p class="subsidio-desc">Percentual sobre o salário base</p>
                    </div>
                </div>

                <!-- Subsídios Opcionais -->
                <div class="subsidio-card opcional" data-subsidio-id="5">
                    <div class="subsidio-header">
                        <h3>Alimentação</h3>
                        <span class="badge opcional">Opcional</span>
                    </div>
                    <div class="subsidio-info">
                        <div class="valor-input">
                            <input type="number" min="500" max="1000" value="750" class="valor-subsidio">
                            <span class="moeda">Kz/dia útil</span>
                        </div>
                    </div>
                </div>

                <div class="subsidio-card opcional" data-subsidio-id="6">
                    <div class="subsidio-header">
                        <h3>Transporte</h3>
                        <span class="badge opcional">Opcional</span>
                    </div>
                    <div class="subsidio-info">
                        <div class="valor-input">
                            <input type="number" min="5000" max="15000" value="10000" class="valor-subsidio">
                            <span class="moeda">Kz/mês</span>
                        </div>
                    </div>
                </div>

                <div class="subsidio-card opcional" data-subsidio-id="7">
                    <div class="subsidio-header">
                        <h3>Comunicação</h3>
                        <span class="badge opcional">Opcional</span>
                    </div>
                    <div class="subsidio-info">
                        <div class="valor-input">
                            <input type="number" min="2000" max="10000" value="5000" class="valor-subsidio">
                            <span class="moeda">Kz/mês</span>
                        </div>
                    </div>
                </div>

                <div class="subsidio-card opcional" data-subsidio-id="8">
                    <div class="subsidio-header">
                        <h3>Saúde / Seguro</h3>
                        <span class="badge opcional">Opcional</span>
                    </div>
                    <div class="subsidio-info">
                        <div class="valor-input">
                            <input type="number" min="0" value="0" class="valor-subsidio">
                            <span class="moeda">Kz/mês</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subsídios Personalizados -->
            <?php foreach ($subsidios_personalizados as $subsidio_pers): ?>
                <div class="subsidio-card" data-subsidio-id="<?php echo $subsidio_pers['id']; ?>">
                    <div class="subsidio-header">
                        <h3><?php echo htmlspecialchars($subsidio_pers['nome']); ?></h3>
                        <span class="badge personalizado">Personalizado</span>
                    </div>
                    <div class="subsidio-info">
                        <?php if ($subsidio_pers['tipo'] === 'valor_fixo'): ?>
                            <div class="valor-input">
                                <input type="number" value="<?php echo htmlspecialchars($subsidio_pers['valor_padrao']); ?>" class="valor-subsidio" readonly>
                                <span class="moeda"><?php echo htmlspecialchars($subsidio_pers['unidade']); ?></span>
                            </div>
                        <?php elseif ($subsidio_pers['tipo'] === 'percentagem'): ?>
                            <div class="valor-range">
                                <input type="range" min="0" max="100" value="<?php echo htmlspecialchars($subsidio_pers['valor_padrao']); ?>" class="slider" disabled>
                                <span class="valor-atual"><?php echo htmlspecialchars($subsidio_pers['valor_padrao']); ?>%</span>
                            </div>
                        <?php endif; ?>
                        <div class="subsidio-actions">
                            <button type="button" class="btn-edit-subsidio" onclick="editarSubsidio(<?php echo htmlspecialchars(json_encode($subsidio_pers)); ?>)">
                                <i class="fas fa-pencil-alt"></i> Editar
                            </button>
                            <button type="button" class="btn-delete-subsidio" onclick="excluirSubsidio(<?php echo $subsidio_pers['id']; ?>)">
                                <i class="fas fa-trash-alt"></i> Excluir
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-primary" style="margin-top: 20px;">Salvar Configurações de Subsídios</button>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal('modal')">&times;</span>
        <h2 id="modal-titulo">Editar Política</h2>
        <div id="modal-conteudo">
            <form id="form-politica" method="POST">
                <input type="hidden" name="tipo" id="politica-tipo">
                
                <!-- Campos específicos para horário de trabalho -->
                <div id="campos-horario" style="display: none;">
                    <div class="form-group">
                        <label>Dias de Trabalho:</label>
                        <div class="dias-buttons">
                            <button type="button" class="dia-btn" data-dia="segunda">Segunda</button>
                            <button type="button" class="dia-btn" data-dia="terca">Terça</button>
                            <button type="button" class="dia-btn" data-dia="quarta">Quarta</button>
                            <button type="button" class="dia-btn" data-dia="quinta">Quinta</button>
                            <button type="button" class="dia-btn" data-dia="sexta">Sexta</button>
                            <button type="button" class="dia-btn" data-dia="sabado">Sábado</button>
                            <button type="button" class="dia-btn" data-dia="domingo">Domingo</button>
                        </div>
                        <input type="hidden" name="dias_selecionados" id="dias_selecionados">
                    </div>
                    <div class="form-group">
                        <label>Horário de Entrada:</label>
                        <input type="time" name="hora_entrada" id="hora_entrada">
                    </div>
                    <div class="form-group">
                        <label>Horário de Saída:</label>
                        <input type="time" name="hora_saida" id="hora_saida">
                    </div>
                    <div class="form-group">
                        <label>Intervalo para Almoço:</label>
                        <div class="intervalo-group">
                            <input type="time" name="almoco_inicio" id="almoco_inicio" placeholder="Início">
                            <span>até</span>
                            <input type="time" name="almoco_fim" id="almoco_fim" placeholder="Fim">
                        </div>
                    </div>
                </div>

                <!-- Campos específicos para home office -->
                <div id="campos-homeoffice" style="display: none;">
                    <div class="form-group">
                        <label>Dias de Home Office Permitidos:</label>
                        <div class="dias-buttons">
                            <button type="button" class="dia-btn" data-dias="0">Nenhum</button>
                            <button type="button" class="dia-btn" data-dias="1">1 dia</button>
                            <button type="button" class="dia-btn" data-dias="2">2 dias</button>
                            <button type="button" class="dia-btn" data-dias="3">3 dias</button>
                            <button type="button" class="dia-btn" data-dias="4">4 dias</button>
                            <button type="button" class="dia-btn" data-dias="5">5 dias</button>
                        </div>
                        <input type="hidden" name="dias_homeoffice" id="dias_homeoffice">
                    </div>
                </div>

                <!-- Campos específicos para vestimenta -->
                <div id="campos-vestimenta" style="display: none;">
                    <div class="form-group">
                        <label for="titulo_vestimenta">Nome do Código de Vestimenta:</label>
                        <input type="text" id="titulo_vestimenta" name="titulo" placeholder="Ex: Vestuário casual e profissional" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao_vestimenta">Descrição do Dress Code:</label>
                        <textarea id="descricao_vestimenta" name="descricao" placeholder="Descreva as regras de vestimenta da empresa..." required></textarea>
                    </div>
                </div>

                <!-- Campos padrão para outras políticas -->
                <div id="campos-padrao">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" id="titulo" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição:</label>
                        <textarea id="descricao" name="descricao" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="valor">Valor/Configuração:</label>
                        <input type="text" id="valor" name="valor" required>
                    </div>
                </div>

                <button type="submit" name="salvar_politica" class="btn-primary">Salvar Alterações</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Edição de Banco -->
<div id="modal-banco" class="modal-banco">
    <div class="modal-banco-content">
        <span class="close" onclick="fecharModal('modal-banco')">&times;</span>
        <h2>Editar Banco</h2>
        <form id="form-editar-banco" method="POST">
            <input type="hidden" name="banco_id" id="banco_id">
            <div class="form-group">
                <label for="edit_banco_nome">Nome do Banco:</label>
                <input type="text" id="edit_banco_nome" name="edit_banco_nome" required>
            </div>
            <div class="form-group">
                <label for="edit_banco_codigo">Código do Banco:</label>
                <input type="text" id="edit_banco_codigo" name="edit_banco_codigo" required>
            </div>
            <button type="submit" name="editar_banco" class="btn-primary">Salvar Alterações</button>
        </form>
    </div>
</div>

<!-- Modal de Edição de Departamento -->
<div id="modal-departamento" class="modal-banco">
    <div class="modal-banco-content">
        <span class="close" onclick="fecharModal('modal-departamento')">&times;</span>
        <h2>Editar Departamento</h2>
        <form id="form-editar-departamento" method="POST">
            <input type="hidden" name="departamento_id" id="edit_departamento_id">
            <div class="form-group">
                <label for="edit_departamento_nome">Nome do Departamento:</label>
                <input type="text" id="edit_departamento_nome" name="edit_departamento_nome" required>
            </div>
            <button type="submit" name="editar_departamento" class="btn-primary">Salvar Alterações</button>
        </form>
    </div>
</div>

<!-- Modal de Edição de Cargo -->
<div id="modal-cargo" class="modal-banco">
    <div class="modal-banco-content">
        <span class="close" onclick="fecharModal('modal-cargo')">&times;</span>
        <h2>Editar Cargo</h2>
        <form id="form-editar-cargo" method="POST">
            <input type="hidden" name="cargo_id" id="edit_cargo_id">
            <div class="form-group">
                <label for="edit_cargo_nome">Nome do Cargo:</label>
                <input type="text" id="edit_cargo_nome" name="edit_cargo_nome" required>
            </div>
            <div class="form-group">
                <label for="edit_cargo_departamento">Departamento:</label>
                <select id="edit_cargo_departamento" name="edit_cargo_departamento" required>
                    <?php
                    if (isset($empresa_id)) {
                        $sql = "SELECT * FROM departamentos WHERE empresa_id = ? ORDER BY nome";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            echo "Erro na preparação da consulta: " . $conn->error;
                        } else {
                            $stmt->bind_param("i", $empresa_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='".$row['id']."'>".$row['nome']."</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_cargo_salario">Salário Base:</label>
                <input type="number" id="edit_cargo_salario" name="edit_cargo_salario" step="0.01" required>
            </div>
            <button type="submit" name="editar_cargo" class="btn-primary">Salvar Alterações</button>
        </form>
    </div>
</div>

<!-- Modal de Edição de Subsídio -->
<div id="modal-subsidio" class="modal-subsidio">
    <div class="modal-subsidio-content">
        <span class="close" onclick="fecharModal('modal-subsidio')">&times;</span>
        <h2>Editar Subsídio</h2>
        <form id="form-editar-subsidio" method="POST">
            <input type="hidden" name="subsidio_id" id="edit_subsidio_id">
            <div class="form-group">
                <label for="edit_subsidio_nome">Nome do Subsídio:</label>
                <input type="text" name="edit_subsidio_nome" id="edit_subsidio_nome" required>
            </div>
            <div class="form-group">
                <label for="edit_subsidio_tipo">Tipo:</label>
                <select name="edit_subsidio_tipo" id="edit_subsidio_tipo" required>
                    <option value="valor_fixo">Valor Fixo</option>
                    <option value="percentagem">Percentagem</option>
                </select>
            </div>
            <div class="form-group" id="edit_subsidio_unidade_tipo" style="display: none;">
                <label for="edit_subsidio_unidade_valor_fixo">Base de Cálculo:</label>
                <select name="edit_subsidio_unidade_valor_fixo" id="edit_subsidio_unidade_valor_fixo">
                    <option value="/dia útil">Por Dia Útil</option>
                    <option value="/mês">Por Mês</option>
                </select>
            </div>
            <div class="form-group" id="edit_subsidio_valor_padrao_group">
                <label for="edit_subsidio_valor_padrao">Valor Padrão:</label>
                <input type="number" name="edit_subsidio_valor_padrao" id="edit_subsidio_valor_padrao" step="0.01" required>
            </div>
            <button type="submit" name="edit_subsidio" class="btn-primary">Salvar Alterações</button>
        </form>
    </div>
</div>

<!-- Modal de Funcionários para Subsídios -->
<div id="modal-funcionarios-subsidio" class="modal-subsidio">
    <div class="modal-subsidio-content" style="width: 80%; max-width: 1000px;">
        <span class="close" onclick="fecharModal('modal-funcionarios-subsidio')">&times;</span>
        <h2>Gerenciar Subsídio: <span id="subsidio-nome-modal"></span></h2>
        <div class="funcionarios-lista">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="lista-funcionarios-subsidio">
                    <!-- Será preenchido via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../js/theme.js"></script>
<script>
function editarDepartamento(id, nome) {
    const modal = document.getElementById('modal-departamento');
    const form = document.getElementById('form-editar-departamento');
    
    document.getElementById('edit_departamento_id').value = id;
    document.getElementById('edit_departamento_nome').value = nome;
    
    modal.style.display = 'block';
}

function excluirDepartamento(id) {
    if(confirm('Tem certeza que deseja excluir este departamento?')) {
        window.location.href = `gerenciar_departamentos.php?acao=excluir&id=${id}`;
    }
}

function editarCargo(id, nome, salario, departamento_id) {
    const modal = document.getElementById('modal-cargo');
    const form = document.getElementById('form-editar-cargo');
    
    document.getElementById('edit_cargo_id').value = id;
    document.getElementById('edit_cargo_nome').value = nome;
    document.getElementById('edit_cargo_salario').value = salario;
    document.getElementById('edit_cargo_departamento').value = departamento_id;
    
    modal.style.display = 'block';
}

function excluirCargo(id) {
    if(confirm('Tem certeza que deseja excluir este cargo?')) {
        window.location.href = `gerenciar_cargos.php?acao=excluir&id=${id}`;
    }
}

// Função para abrir o modal
function abrirModal(tipo) {
    const modal = document.getElementById('modal');
    const modalTitulo = document.getElementById('modal-titulo');
    const form = document.getElementById('form-politica');
    const politicaTipo = document.getElementById('politica-tipo');
    const camposHorario = document.getElementById('campos-horario');
    const camposHomeoffice = document.getElementById('campos-homeoffice');
    const camposVestimenta = document.getElementById('campos-vestimenta');
    const camposPadrao = document.getElementById('campos-padrao');
    
    // Definir título e valores baseado no tipo
    switch(tipo) {
        case 'horario':
            modalTitulo.textContent = 'Editar Horário de Trabalho';
            politicaTipo.value = 'horario';
            camposHorario.style.display = 'block';
            camposHomeoffice.style.display = 'none';
            camposVestimenta.style.display = 'none';
            camposPadrao.style.display = 'none';
            
            // Carregar valores existentes ou usar padrão
            const horarioData = <?php echo isset($politicas['horario']) ? json_encode($politicas['horario']) : 'null'; ?>;
            if (horarioData) {
                const dias = horarioData.valor.split('|')[0].split(',');
                dias.forEach(dia => {
                    const btn = document.querySelector(`.dia-btn[data-dia="${dia}"]`);
                    if (btn) btn.classList.add('selecionado');
                });
                
                const horarios = horarioData.valor.split('|')[1].split(',');
                document.getElementById('hora_entrada').value = horarios[0];
                document.getElementById('hora_saida').value = horarios[1];
                document.getElementById('almoco_inicio').value = horarios[2];
                document.getElementById('almoco_fim').value = horarios[3];
            } else {
                // Valores padrão
                document.querySelectorAll('.dia-btn').forEach(btn => {
                    if (['segunda', 'terca', 'quarta', 'quinta', 'sexta'].includes(btn.dataset.dia)) {
                        btn.classList.add('selecionado');
                    }
                });
                document.getElementById('hora_entrada').value = '08:00';
                document.getElementById('hora_saida').value = '18:00';
                document.getElementById('almoco_inicio').value = '12:00';
                document.getElementById('almoco_fim').value = '13:00';
            }
            break;
            
        case 'homeoffice':
            modalTitulo.textContent = 'Editar Política de Home Office';
            politicaTipo.value = 'homeoffice';
            camposHorario.style.display = 'none';
            camposHomeoffice.style.display = 'block';
            camposVestimenta.style.display = 'none';
            camposPadrao.style.display = 'none';
            
            // Carregar valores existentes ou usar padrão
            const homeofficeData = <?php echo isset($politicas['homeoffice']) ? json_encode($politicas['homeoffice']) : 'null'; ?>;
            if (homeofficeData) {
                const dias = parseInt(homeofficeData.valor);
                document.querySelector(`.dia-btn[data-dias="${dias}"]`)?.classList.add('selecionado');
            } else {
                // Valores padrão
                document.querySelector('.dia-btn[data-dias="2"]').classList.add('selecionado');
            }
            break;
            
        case 'vestimenta':
            modalTitulo.textContent = 'Editar Código de Vestimenta';
            politicaTipo.value = 'vestimenta';
            camposHorario.style.display = 'none';
            camposHomeoffice.style.display = 'none';
            camposVestimenta.style.display = 'block';
            camposPadrao.style.display = 'none';
            
            // Carregar valores existentes ou usar padrão
            const vestimentaData = <?php echo isset($politicas['vestimenta']) ? json_encode($politicas['vestimenta']) : 'null'; ?>;
            if (vestimentaData) {
                document.getElementById('titulo_vestimenta').value = vestimentaData.titulo;
                document.getElementById('descricao_vestimenta').value = vestimentaData.descricao;
            } else {
                // Valores padrão
                document.getElementById('titulo_vestimenta').value = 'Vestuário casual e profissional';
                document.getElementById('descricao_vestimenta').value = '';
            }
            break;
    }
    
    modal.style.display = 'block';
}

// Fechar o modal quando clicar no X
document.querySelector('.close').onclick = function() {
    document.getElementById('modal').style.display = 'none';
}

// Fechar o modal quando clicar fora dele
window.onclick = function(event) {
    if (event.target.classList.contains('modal') || event.target.classList.contains('modal-banco')) {
        event.target.style.display = 'none';
    }
}

// Adicionar evento de clique para os botões de dia
document.querySelectorAll('.dia-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        this.classList.toggle('selecionado');
        atualizarDiasSelecionados();
    });
});

function atualizarDiasSelecionados() {
    const diasSelecionados = Array.from(document.querySelectorAll('.dia-btn.selecionado'))
        .map(btn => btn.dataset.dia);
    document.getElementById('dias_selecionados').value = diasSelecionados.join(',');
}

// Adicionar evento de clique para os botões de home office
document.querySelectorAll('#campos-homeoffice .dia-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove seleção de todos os botões
        document.querySelectorAll('#campos-homeoffice .dia-btn').forEach(b => b.classList.remove('selecionado'));
        // Adiciona seleção ao botão clicado
        this.classList.add('selecionado');
        // Atualiza o valor do campo oculto
        document.getElementById('dias_homeoffice').value = this.dataset.dias;
    });
});

// Atualizar dias de home office antes do envio do formulário
document.getElementById('form-politica').addEventListener('submit', function(e) {
    const diasHomeoffice = document.querySelector('#campos-homeoffice .dia-btn.selecionado')?.dataset.dias || '0';
    document.getElementById('dias_homeoffice').value = diasHomeoffice;
});

// Funções para gerenciar bancos
function editarBanco(id, nome, codigo) {
    const modal = document.getElementById('modal-banco');
    const form = document.getElementById('form-editar-banco');
    
    document.getElementById('banco_id').value = id;
    document.getElementById('edit_banco_nome').value = nome;
    document.getElementById('edit_banco_codigo').value = codigo;
    
    modal.style.display = 'block';
}

function excluirBanco(id) {
    if (confirm('Tem certeza que deseja excluir este banco?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="banco_id" value="${id}">
            <input type="hidden" name="excluir_banco" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Fechar o modal de banco
document.querySelector('.modal-banco .close').onclick = function() {
    document.getElementById('modal-banco').style.display = 'none';
}

// Fechar o modal quando clicar fora dele
window.onclick = function(event) {
    if (event.target.classList.contains('modal') || event.target.classList.contains('modal-banco')) {
        event.target.style.display = 'none';
    }
}

function fecharModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Atualizar valores dos sliders
document.querySelectorAll('#subsidios .slider').forEach(slider => {
    slider.addEventListener('input', function() {
        this.nextElementSibling.textContent = this.value + '%';
    });
});

// Atualizar valores dos inputs numéricos
document.querySelectorAll('#subsidios .valor-subsidio').forEach(input => {
    input.addEventListener('input', function() {
        const min = parseInt(this.min);
        const max = parseInt(this.max);
        let value = parseInt(this.value);
        
        if (value < min) this.value = min;
        if (value > max) this.value = max;
    });
});

// Exibir/ocultar a opção Dia/Mês com base no tipo de subsídio selecionado
document.getElementById('novo_subsidio_tipo').addEventListener('change', function() {
    const unidadeTipoDiv = document.getElementById('novo_subsidio_unidade_tipo');
    const valorPadraoDiv = document.getElementById('novo_subsidio_valor_padrao').closest('.form-group');
    const valorPadraoLabel = document.querySelector('label[for="novo_subsidio_valor_padrao"]');
    
    if (this.value === 'valor_fixo') {
        unidadeTipoDiv.style.display = 'block';
        valorPadraoDiv.style.display = 'block';
        valorPadraoLabel.textContent = 'Valor Padrão:';
        document.getElementById('novo_subsidio_valor_padrao').placeholder = 'Ex: 1000';
    } else if (this.value === 'percentagem') {
        unidadeTipoDiv.style.display = 'none';
        valorPadraoDiv.style.display = 'block';
        valorPadraoLabel.textContent = 'Porcentagem (0-100):';
        document.getElementById('novo_subsidio_valor_padrao').placeholder = 'Ex: 15';
    } else {
        unidadeTipoDiv.style.display = 'none';
        valorPadraoDiv.style.display = 'none';
    }
});

function editarSubsidio(subsidio) {
    const modal = document.getElementById('modal-subsidio');
    const form = document.getElementById('form-editar-subsidio');
    
    // Preencher o formulário com os dados do subsídio
    document.getElementById('edit_subsidio_id').value = subsidio.id;
    document.getElementById('edit_subsidio_nome').value = subsidio.nome;
    document.getElementById('edit_subsidio_tipo').value = subsidio.tipo;
    document.getElementById('edit_subsidio_valor_padrao').value = subsidio.valor_padrao;
    
    // Ajustar campos baseado no tipo
    const unidadeTipoDiv = document.getElementById('edit_subsidio_unidade_tipo');
    const valorPadraoLabel = document.querySelector('label[for="edit_subsidio_valor_padrao"]');
    
    if (subsidio.tipo === 'valor_fixo') {
        unidadeTipoDiv.style.display = 'block';
        valorPadraoLabel.textContent = 'Valor Padrão:';
        document.getElementById('edit_subsidio_unidade_valor_fixo').value = subsidio.unidade.replace('Kz', '');
    } else {
        unidadeTipoDiv.style.display = 'none';
        valorPadraoLabel.textContent = 'Porcentagem (0-100):';
    }
    
    modal.style.display = 'block';
}

function excluirSubsidio(id) {
    if (confirm('Tem certeza que deseja excluir este subsídio?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="excluir_subsidio" value="1">
            <input type="hidden" name="subsidio_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Atualizar campos do modal de edição quando o tipo é alterado
document.getElementById('edit_subsidio_tipo').addEventListener('change', function() {
    const unidadeTipoDiv = document.getElementById('edit_subsidio_unidade_tipo');
    const valorPadraoLabel = document.querySelector('label[for="edit_subsidio_valor_padrao"]');
    
    if (this.value === 'valor_fixo') {
        unidadeTipoDiv.style.display = 'block';
        valorPadraoLabel.textContent = 'Valor Padrão:';
    } else {
        unidadeTipoDiv.style.display = 'none';
        valorPadraoLabel.textContent = 'Porcentagem (0-100):';
    }
});

// Função para abrir o modal de funcionários
function abrirModalFuncionarios(subsidioId, subsidioNome, tipoSubsidio) {
    console.log('DEBUG: abrirModalFuncionarios chamado');
    console.log('DEBUG: subsidioId:', subsidioId);
    console.log('DEBUG: subsidioNome:', subsidioNome);
    console.log('DEBUG: tipoSubsidio:', tipoSubsidio);

    const modal = document.getElementById('modal-funcionarios-subsidio');
    document.getElementById('subsidio-nome-modal').textContent = subsidioNome;

    // Fazer requisição AJAX para buscar funcionários
    const fetchUrl = `get_funcionarios_subsidio.php?subsidio_id=${subsidioId}&tipo=${tipoSubsidio}`;
    console.log('DEBUG: Fetch URL:', fetchUrl);

    fetch(fetchUrl)
        .then(response => {
            console.log('DEBUG: Resposta da requisição recebida.', response);
            if (!response.ok) {
                console.error('ERRO: Resposta da rede não foi ok', response.statusText);
                return response.text().then(text => {
                    console.error('ERRO: Corpo da resposta:', text);
                    throw new Error('Resposta da rede não foi ok');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('DEBUG: Dados de funcionários recebidos:', data);
            const tbody = document.getElementById('lista-funcionarios-subsidio');
            tbody.innerHTML = '';

            if (data.error) {
                console.error('ERRO: Erro retornado pelo script PHP:', data.error);
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Erro ao carregar funcionários: ${data.error}</td></tr>`;
                return;
            }

            if (data.length === 0) {
                console.log('DEBUG: Nenhum funcionário ativo encontrado para este subsídio.');
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Nenhum funcionário ativo encontrado.</td></tr>';
            } else {
                 data.forEach(funcionario => {
                    console.log('DEBUG: Processando funcionário:', funcionario);
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${funcionario.nome || 'N/A'}</td>
                        <td>${funcionario.cargo || 'N/A'}</td>
                        <td>${funcionario.departamento || 'N/A'}</td>
                        <td>
                            <span class="status-badge ${funcionario.ativo ? 'ativo' : 'inativo'}">
                                ${funcionario.ativo ? 'Ativo' : 'Inativo'}
                            </span>
                        </td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" 
                                       ${funcionario.ativo ? 'checked' : ''} 
                                       onchange="toggleSubsidioFuncionario(${funcionario.id_fun}, ${subsidioId}, '${tipoSubsidio}', this)">
                                <span class="slider-switch"></span>
                            </label>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => {
            console.error('ERRO: Erro na requisição Fetch ou processamento:', error);
            const tbody = document.getElementById('lista-funcionarios-subsidio');
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Erro ao carregar funcionários. Por favor, tente novamente.</td></tr>';
        });

    modal.style.display = 'block';
}

// Função para alternar o status do subsídio para um funcionário
function toggleSubsidioFuncionario(funcionarioId, subsidioId, tipoSubsidio, checkbox) {
    const ativo = checkbox.checked;
    
    // Validar tipo de subsídio
    if (!['obrigatorio', 'opcional', 'personalizado'].includes(tipoSubsidio)) {
        console.error('Tipo de subsídio inválido:', tipoSubsidio);
        alert('Tipo de subsídio inválido');
        checkbox.checked = !ativo;
        return;
    }
    
    // Log dos dados que serão enviados
    console.log('DEBUG: Enviando dados:', {
        funcionario_id: funcionarioId,
        subsidio_id: subsidioId,
        tipo_subsidio: tipoSubsidio,
        ativo: ativo
    });
    
    fetch('toggle_subsidio_funcionario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            funcionario_id: funcionarioId,
            subsidio_id: subsidioId,
            tipo_subsidio: tipoSubsidio,
            ativo: ativo
        })
    })
    .then(response => {
        console.log('DEBUG: Status da resposta:', response.status);
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('DEBUG: Erro ao fazer parse da resposta:', text);
                throw new Error('Resposta inválida do servidor');
            }
        });
    })
    .then(data => {
        console.log('DEBUG: Resposta do servidor:', data);
        if (data.success) {
            // Atualizar o badge de status
            const tr = checkbox.closest('tr');
            const statusBadge = tr.querySelector('.status-badge');
            statusBadge.className = `status-badge ${ativo ? 'ativo' : 'inativo'}`;
            statusBadge.textContent = ativo ? 'Ativo' : 'Inativo';
        } else {
            // Reverter o checkbox se houver erro
            checkbox.checked = !ativo;
            console.error('DEBUG: Erro retornado pelo servidor:', data.error);
            alert(data.error || 'Erro ao atualizar o status do subsídio');
        }
    })
    .catch(error => {
        console.error('DEBUG: Erro na requisição:', error);
        checkbox.checked = !ativo;
        alert('Erro ao atualizar o status do subsídio. Por favor, tente novamente.');
    });
}

// Adicionar evento de clique nos cards de subsídio
document.querySelectorAll('.subsidio-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Não abrir o modal se clicar nos botões de ação
        if (e.target.closest('.subsidio-actions')) {
            return;
        }
        
        const subsidioId = this.dataset.subsidioId || '0';
        const subsidioNome = this.querySelector('h3').textContent;
        const tipoSubsidio = this.classList.contains('obrigatorio') ? 'obrigatorio' : 
                           this.classList.contains('opcional') ? 'opcional' : 'personalizado';
        
        abrirModalFuncionarios(subsidioId, subsidioNome, tipoSubsidio);
    });
});
</script>
</body>
</html>

<?php
function getCargoCount($conn, $dept_id) {
    $sql = "SELECT COUNT(*) as count FROM cargos WHERE departamento_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0 ? $row['count'] : 1;
}
?>