<?php
session_start();
include('../config.php');
include('../protect.php');

if (!isset($_SESSION['id_adm'])) {
    die("Acesso negado");
}

// Criar tabelas se não existirem
$sql_criar_tabelas = "
CREATE TABLE IF NOT EXISTS politicas_trabalho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    tipo ENUM('horario', 'homeoffice', 'vestimenta') NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    valor TEXT NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresa(id_empresa)
)";

if (!$conn->query($sql_criar_tabelas)) {
    die("Erro ao criar tabela: " . $conn->error);
}

// Criar tabela de bancos se não existir
$sql_criar_tabela_bancos = "
CREATE TABLE IF NOT EXISTS bancos_ativos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    banco_nome VARCHAR(100) NOT NULL,
    banco_codigo VARCHAR(10) NOT NULL,
    ativo BOOLEAN DEFAULT false,
    FOREIGN KEY (empresa_id) REFERENCES empresa(id_empresa)
)";

if (!$conn->query($sql_criar_tabela_bancos)) {
    die("Erro ao criar tabela de bancos: " . $conn->error);
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
            
            if ($tipo === 'horario') {
                $tipo_config = $_POST['tipo_config_horario'];
                
                if ($tipo_config === 'turno') {
                    // Criar/atualizar turno padrão
                    $nome_turno = $_POST['nome_turno'];
                    $dias = $_POST['dias_selecionados'];
                    $hora_entrada = $_POST['hora_entrada'];
                    $hora_saida = $_POST['hora_saida'];
                    $almoco_inicio = $_POST['almoco_inicio'];
                    $almoco_fim = $_POST['almoco_fim'];
                    
                    $sql = "INSERT INTO turnos_padrao (empresa_id, nome_turno, hora_entrada, hora_saida, almoco_inicio, almoco_fim, dias_semana) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issssss", $empresa_id, $nome_turno, $hora_entrada, $hora_saida, $almoco_inicio, $almoco_fim, $dias);
                    
                    if (!$stmt->execute()) {
                        die("Erro ao salvar turno: " . $stmt->error);
                    }
                    
                } else {
                    // Salvar horário personalizado
                    $funcionario_id = $_POST['funcionario_id'];
                    $turno_id = $_POST['turno_id'];
                    $dias = $_POST['dias_personalizado'];
                    $hora_entrada = $_POST['hora_entrada_personalizado'];
                    $hora_saida = $_POST['hora_saida_personalizado'];
                    $almoco_inicio = $_POST['almoco_inicio_personalizado'];
                    $almoco_fim = $_POST['almoco_fim_personalizado'];
                    
                    // Verificar se já existe um horário para este funcionário
                    $sql_check = "SELECT id FROM horarios_funcionarios WHERE funcionario_id = ?";
                    $stmt_check = $conn->prepare($sql_check);
                    $stmt_check->bind_param("i", $funcionario_id);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows > 0) {
                        // Atualizar horário existente
                        $sql = "UPDATE horarios_funcionarios SET 
                               turno_id = ?, 
                               hora_entrada = ?, 
                               hora_saida = ?, 
                               almoco_inicio = ?, 
                               almoco_fim = ?, 
                               dias_semana = ?,
                               tipo = ?
                               WHERE funcionario_id = ?";
                        $stmt = $conn->prepare($sql);
                        $tipo_horario = $turno_id ? 'turno' : 'personalizado';
                        $stmt->bind_param("issssssi", $turno_id, $hora_entrada, $hora_saida, $almoco_inicio, $almoco_fim, $dias, $tipo_horario, $funcionario_id);
                    } else {
                        // Inserir novo horário
                        $sql = "INSERT INTO horarios_funcionarios 
                               (funcionario_id, turno_id, hora_entrada, hora_saida, almoco_inicio, almoco_fim, dias_semana, tipo) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $tipo_horario = $turno_id ? 'turno' : 'personalizado';
                        $stmt->bind_param("iissssss", $funcionario_id, $turno_id, $hora_entrada, $hora_saida, $almoco_inicio, $almoco_fim, $dias, $tipo_horario);
                    }
                    
                    if (!$stmt->execute()) {
                        die("Erro ao salvar horário: " . $stmt->error);
                    }
                }
            } else {
                $valor = $_POST['valor'];
            }

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

            // Debug
            error_log("Política salva com sucesso: " . $tipo);

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
            
            // Debug
            error_log("Editando banco: ID=" . $banco_id . ", Nome=" . $novo_nome . ", Codigo=" . $novo_codigo);
            
            $sql = "UPDATE bancos_ativos SET banco_nome = ?, banco_codigo = ? WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("ssii", $novo_nome, $novo_codigo, $banco_id, $empresa_id);
            
            if (!$stmt->execute()) {
                die("Erro ao atualizar banco: " . $stmt->error);
            }
            
            // Debug
            error_log("Banco atualizado com sucesso");
            
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
            
            // Debug
            error_log("Editando departamento: ID=" . $departamento_id . ", Nome=" . $novo_nome);
            
            $sql = "UPDATE departamentos SET nome = ? WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("sii", $novo_nome, $departamento_id, $empresa_id);
            
            if (!$stmt->execute()) {
                die("Erro ao atualizar departamento: " . $stmt->error);
            }
            
            // Debug
            error_log("Departamento atualizado com sucesso");
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#departamentos");
            exit();
        }

        // Processar edição de cargo
        if (isset($_POST['editar_cargo']) && !empty($_POST['cargo_id'])) {
            $cargo_id = $_POST['cargo_id'];
            $novo_nome = $_POST['edit_cargo_nome'];
            $novo_salario = $_POST['edit_cargo_salario'];
            $novo_departamento = $_POST['edit_cargo_departamento'];
            
            // Debug
            error_log("Editando cargo: ID=" . $cargo_id . ", Nome=" . $novo_nome . ", Salario=" . $novo_salario . ", Departamento=" . $novo_departamento);
            
            $sql = "UPDATE cargos SET nome = ?, salario_base = ?, departamento_id = ? WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("sdiis", $novo_nome, $novo_salario, $novo_departamento, $cargo_id, $empresa_id);
            
            if (!$stmt->execute()) {
                die("Erro ao atualizar cargo: " . $stmt->error);
            }
            
            // Debug
            error_log("Cargo atualizado com sucesso");
            
            header("Location: " . $_SERVER['PHP_SELF'] . "#cargos");
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
    if ($row['tipo'] === 'horario') {
        $horario_data = json_decode($row['valor'], true);
        $dias_formatados = formatarDiasSemana($horario_data['dias']);
        $horario = $horario_data['horario'];
        $row['horario_formatado'] = sprintf(
            "%s: %s às %s (Almoço: %s às %s)",
            $dias_formatados,
            $horario['entrada'],
            $horario['saida'],
            $horario['almoco']['inicio'],
            $horario['almoco']['fim']
        );
    }
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
            gap: 10px;
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
            margin-bottom: 20px;
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
            gap: 10px;
            flex-wrap: wrap;
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
                            <p><?php echo isset($politicas['horario']['horario_formatado']) ? $politicas['horario']['horario_formatado'] : 'Não configurado'; ?></p>
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
    </div>
</div>

<!-- Modal -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal('modal')">&times;</span>
        <h2 id="modal-titulo">Editar Política</h2>
        <div id="modal-conteudo">
            <form id="form-politica" method="POST" action="rh_config.php">
                <input type="hidden" name="tipo" id="politica-tipo">
                
                <!-- Campos específicos para horário de trabalho -->
                <div id="campos-horario" style="display: none;">
                    <div class="form-group">
                        <label>Tipo de Configuração:</label>
                        <select id="tipo_config_horario" onchange="toggleTipoHorario()">
                            <option value="turno">Criar Turno Padrão</option>
                            <option value="personalizado">Horário Personalizado</option>
                        </select>
                    </div>

                    <!-- Campos para Turno Padrão -->
                    <div id="campos-turno">
                        <div class="form-group">
                            <label>Nome do Turno:</label>
                            <input type="text" name="nome_turno" id="nome_turno" placeholder="Ex: Turno Matutino">
                        </div>
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
                            <input type="time" name="hora_entrada" id="hora_entrada" required>
                        </div>
                        <div class="form-group">
                            <label>Horário de Saída:</label>
                            <input type="time" name="hora_saida" id="hora_saida" required>
                        </div>
                        <div class="form-group">
                            <label>Intervalo para Almoço:</label>
                            <div class="intervalo-group">
                                <input type="time" name="almoco_inicio" id="almoco_inicio" placeholder="Início" required>
                                <span>até</span>
                                <input type="time" name="almoco_fim" id="almoco_fim" placeholder="Fim" required>
                            </div>
                        </div>
                    </div>

                    <!-- Campos para Horário Personalizado -->
                    <div id="campos-personalizado" style="display: none;">
                        <div class="form-group">
                            <label>Funcionário:</label>
                            <select name="funcionario_id" id="funcionario_id">
                                <option value="">Selecione um funcionário</option>
                                <?php
                                // Buscar funcionários da empresa
                                $sql_func = "SELECT id_funcionario, nome FROM funcionarios WHERE empresa_id = ?";
                                $stmt_func = $conn->prepare($sql_func);
                                $stmt_func->bind_param("i", $empresa_id);
                                $stmt_func->execute();
                                $result_func = $stmt_func->get_result();
                                while($func = $result_func->fetch_assoc()) {
                                    echo "<option value='".$func['id_funcionario']."'>".$func['nome']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Turno:</label>
                            <select name="turno_id" id="turno_id">
                                <option value="">Selecione um turno</option>
                                <?php
                                // Buscar turnos padrão da empresa
                                $sql_turnos = "SELECT id, nome_turno FROM turnos_padrao WHERE empresa_id = ?";
                                $stmt_turnos = $conn->prepare($sql_turnos);
                                $stmt_turnos->bind_param("i", $empresa_id);
                                $stmt_turnos->execute();
                                $result_turnos = $stmt_turnos->get_result();
                                while($turno = $result_turnos->fetch_assoc()) {
                                    echo "<option value='".$turno['id']."'>".$turno['nome_turno']."</option>";
                                }
                                ?>
                            </select>
                        </div>
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
                            <input type="hidden" name="dias_personalizado" id="dias_personalizado">
                        </div>
                        <div class="form-group">
                            <label>Horário de Entrada:</label>
                            <input type="time" name="hora_entrada_personalizado" id="hora_entrada_personalizado">
                        </div>
                        <div class="form-group">
                            <label>Horário de Saída:</label>
                            <input type="time" name="hora_saida_personalizado" id="hora_saida_personalizado">
                        </div>
                        <div class="form-group">
                            <label>Intervalo para Almoço:</label>
                            <div class="intervalo-group">
                                <input type="time" name="almoco_inicio_personalizado" id="almoco_inicio_personalizado" placeholder="Início">
                                <span>até</span>
                                <input type="time" name="almoco_fim_personalizado" id="almoco_fim_personalizado" placeholder="Fim">
                            </div>
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
        <form id="form-editar-banco" method="POST" action="rh_config.php">
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
        <form id="form-editar-departamento" method="POST" action="rh_config.php">
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
        <form id="form-editar-cargo" method="POST" action="rh_config.php">
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

<script src="../js/theme.js"></script>
<script>
// Função para fechar modais
function fecharModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Função para editar departamento
function editarDepartamento(id, nome) {
    console.log('Editar Departamento:', id, nome); // Debug
    const modal = document.getElementById('modal-departamento');
    document.getElementById('edit_departamento_id').value = id;
    document.getElementById('edit_departamento_nome').value = nome;
    modal.style.display = 'block';
}

// Função para editar cargo
function editarCargo(id, nome, salario, departamento_id) {
    console.log('Editar Cargo:', id, nome, salario, departamento_id); // Debug
    const modal = document.getElementById('modal-cargo');
    document.getElementById('edit_cargo_id').value = id;
    document.getElementById('edit_cargo_nome').value = nome;
    document.getElementById('edit_cargo_salario').value = salario;
    document.getElementById('edit_cargo_departamento').value = departamento_id;
    modal.style.display = 'block';
}

// Função para editar banco
function editarBanco(id, nome, codigo) {
    console.log('Editar Banco:', id, nome, codigo); // Debug
    const modal = document.getElementById('modal-banco');
    document.getElementById('banco_id').value = id;
    document.getElementById('edit_banco_nome').value = nome;
    document.getElementById('edit_banco_codigo').value = codigo;
    modal.style.display = 'block';
}

// Função para excluir banco
function excluirBanco(id) {
    console.log('Excluir Banco:', id); // Debug
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

// Função para abrir o modal de políticas
function abrirModal(tipo) {
    console.log('Abrir Modal Política:', tipo); // Debug
    const modal = document.getElementById('modal');
    const modalTitulo = document.getElementById('modal-titulo');
    const form = document.getElementById('form-politica');
    const politicaTipo = document.getElementById('politica-tipo');
    const camposHorario = document.getElementById('campos-horario');
    const camposHomeoffice = document.getElementById('campos-homeoffice');
    const camposVestimenta = document.getElementById('campos-vestimenta');
    const camposPadrao = document.getElementById('campos-padrao');
    
    // Ocultar todos os campos específicos primeiro
    camposHorario.style.display = 'none';
    camposHomeoffice.style.display = 'none';
    camposVestimenta.style.display = 'none';
    camposPadrao.style.display = 'none';
    
    // Resetar classes 'selecionado' dos botões de dias
    document.querySelectorAll('.dia-btn').forEach(btn => btn.classList.remove('selecionado'));
    document.getElementById('dias_selecionados').value = '';
    document.getElementById('dias_homeoffice').value = '';

    // Definir título e valores baseado no tipo
    switch(tipo) {
        case 'horario':
            modalTitulo.textContent = 'Editar Horário de Trabalho';
            politicaTipo.value = 'horario';
            camposHorario.style.display = 'block';
            
            // Carregar valores existentes ou usar padrão
            const horarioData = <?php echo isset($politicas['horario']) ? json_encode($politicas['horario']['valor']) : 'null'; ?>;

            // Limpar campos de horário antes de carregar novos dados
            document.getElementById('hora_entrada').value = '';
            document.getElementById('hora_saida').value = '';
            document.getElementById('almoco_inicio').value = '';
            document.getElementById('almoco_fim').value = '';

            if (horarioData && horarioData !== 'null') {
                try {
                    const dados = JSON.parse(horarioData);
                    const dias = dados.dias ? dados.dias.split(',') : [];
                    dias.forEach(dia => {
                        const btn = document.querySelector(`.dia-btn[data-dia="${dia}"]`);
                        if (btn) btn.classList.add('selecionado');
                    });
                    
                    if(dados.horario) {
                        document.getElementById('hora_entrada').value = dados.horario.entrada || '';
                        document.getElementById('hora_saida').value = dados.horario.saida || '';
                        if(dados.horario.almoco) {
                             document.getElementById('almoco_inicio').value = dados.horario.almoco.inicio || '';
                             document.getElementById('almoco_fim').value = dados.horario.almoco.fim || '';
                        }
                    }
                    // Definir o tipo de configuração no select (assumindo que a política de horário salva era 'turno')
                    document.getElementById('tipo_config_horario').value = 'turno'; // Ou 'personalizado' se aplicável
                    toggleTipoHorario(); // Atualizar visibilidade dos campos

                } catch (e) {
                    console.error("Erro ao parsear dados de horário:", e);
                    // Se houver erro ao carregar dados, exibir campos de turno por padrão
                    document.getElementById('tipo_config_horario').value = 'turno';
                    toggleTipoHorario();
                     // Valores padrão se não conseguir carregar
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
            } else {
                // Valores padrão e seleção padrão 'turno'
                document.getElementById('tipo_config_horario').value = 'turno';
                toggleTipoHorario();
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
            camposHomeoffice.style.display = 'block';
            // Carregar valores existentes ou usar padrão
            const homeofficeData = <?php echo isset($politicas['homeoffice']) ? json_encode($politicas['homeoffice']) : 'null'; ?>;
            document.querySelectorAll('#campos-homeoffice .dia-btn').forEach(b => b.classList.remove('selecionado'));
            if (homeofficeData && homeofficeData !== 'null') {
                 try {
                    const dados = JSON.parse(homeofficeData);
                    const dias = parseInt(dados.valor);
                    document.querySelector(`.dia-btn[data-dias="${dias}"]`)?.classList.add('selecionado');
                    document.getElementById('dias_homeoffice').value = dias;
                 } catch(e) {
                     console.error("Erro ao parsear dados de homeoffice:", e);
                     // Valor padrão
                     document.querySelector('.dia-btn[data-dias="2"]').classList.add('selecionado');
                     document.getElementById('dias_homeoffice').value = '2';
                 }
            } else {
                // Valor padrão
                document.querySelector('.dia-btn[data-dias="2"]').classList.add('selecionado');
                document.getElementById('dias_homeoffice').value = '2';
            }
            break;
            
        case 'vestimenta':
            modalTitulo.textContent = 'Editar Código de Vestimenta';
            politicaTipo.value = 'vestimenta';
            camposVestimenta.style.display = 'block';
            // Carregar valores existentes ou usar padrão
            const vestimentaData = <?php echo isset($politicas['vestimenta']) ? json_encode($politicas['vestimenta']) : 'null'; ?>;
            document.getElementById('titulo_vestimenta').value = '';
            document.getElementById('descricao_vestimenta').value = '';
            if (vestimentaData && vestimentaData !== 'null') {
                 try {
                    const dados = JSON.parse(vestimentaData);
                    document.getElementById('titulo_vestimenta').value = dados.titulo || '';
                    document.getElementById('descricao_vestimenta').value = dados.descricao || '';
                 } catch(e) {
                      console.error("Erro ao parsear dados de vestimenta:", e);
                     // Valores padrão
                     document.getElementById('titulo_vestimenta').value = 'Vestuário casual e profissional';
                     document.getElementById('descricao_vestimenta').value = '';
                 }
            } else {
                // Valores padrão
                document.getElementById('titulo_vestimenta').value = 'Vestuário casual e profissional';
                document.getElementById('descricao_vestimenta').value = '';
            }
            break;

        default:
            // Caso para outras políticas que usam campos padrão
            modalTitulo.textContent = 'Editar Política'; // Título genérico
            politicaTipo.value = tipo; // Define o tipo da política
            camposPadrao.style.display = 'block';
            
            // Carregar valores existentes ou usar padrão
            const politicaPadraoData = <?php echo json_encode($politicas); ?>;
            if (politicaPadraoData && politicaPadraoData[tipo]) {
                 try {
                    const dados = politicaPadraoData[tipo];
                    document.getElementById('titulo').value = dados.titulo || '';
                    document.getElementById('descricao').value = dados.descricao || '';
                    document.getElementById('valor').value = dados.valor || '';
                 } catch(e) {
                     console.error("Erro ao carregar dados de política padrão:", e);
                     // Limpar campos se houver erro
                     document.getElementById('titulo').value = '';
                     document.getElementById('descricao').value = '';
                     document.getElementById('valor').value = '';
                 }
            } else {
                 // Limpar campos se não houver dados
                 document.getElementById('titulo').value = '';
                 document.getElementById('descricao').value = '';
                 document.getElementById('valor').value = '';
            }
            break;
    }
    
    modal.style.display = 'block';
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
// Adicionar evento de clique para os botões de dia
document.querySelectorAll('.dia-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        this.classList.toggle('selecionado');
        atualizarDiasSelecionados();
    });
});

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

    // Fechar modais ao clicar fora deles
    window.onclick = function(event) {
        if (event.target.classList.contains('modal') || event.target.classList.contains('modal-banco')) {
            event.target.style.display = 'none';
        }
    }

    // Atualizar dias selecionados antes do envio do formulário
    document.getElementById('form-politica').addEventListener('submit', function(e) {
        const tipo = document.getElementById('politica-tipo').value;
        if (tipo === 'horario') {
            atualizarDiasSelecionados();
        }
    });
});

function atualizarDiasSelecionados() {
    const diasSelecionados = Array.from(document.querySelectorAll('.dia-btn.selecionado'))
        .map(btn => btn.dataset.dia);
    document.getElementById('dias_selecionados').value = diasSelecionados.join(',');
}

function toggleTipoHorario() {
    const tipo = document.getElementById('tipo_config_horario').value;
    const camposTurno = document.getElementById('campos-turno');
    const camposPersonalizado = document.getElementById('campos-personalizado');
    
    if (tipo === 'turno') {
        camposTurno.style.display = 'block';
        camposPersonalizado.style.display = 'none';
    } else {
        camposTurno.style.display = 'none';
        camposPersonalizado.style.display = 'block';
    }
}

// Atualizar campos quando um turno é selecionado
document.getElementById('turno_id').addEventListener('change', function() {
    const turnoId = this.value;
    if (turnoId) {
        // Fazer uma requisição AJAX para buscar os dados do turno
        fetch(`get_turno.php?id=${turnoId}`)
            .then(response => response.json())
            .then(data => {
                // Preencher os campos com os dados do turno
                document.getElementById('hora_entrada_personalizado').value = data.hora_entrada;
                document.getElementById('hora_saida_personalizado').value = data.hora_saida;
                document.getElementById('almoco_inicio_personalizado').value = data.almoco_inicio;
                document.getElementById('almoco_fim_personalizado').value = data.almoco_fim;
                
                // Selecionar os dias
                const dias = data.dias_semana.split(',');
                document.querySelectorAll('.dia-btn').forEach(btn => {
                    btn.classList.toggle('selecionado', dias.includes(btn.dataset.dia));
                });
            });
    }
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