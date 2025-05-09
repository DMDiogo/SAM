<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Se for uma requisição OPTIONS, apenas retorne os cabeçalhos e encerre
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Conexão com o banco de dados do app
function getAppDbConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'app_empresas';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    // Verificar conexão
    if ($conn->connect_error) {
        die(json_encode(['status' => 'error', 'message' => 'Conexão falhou: ' . $conn->connect_error]));
    }
    
    // Configurar para UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Conexão com o banco de dados do site
function getSiteDbConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'sam'; // Banco de dados do site
    
    $conn = new mysqli($host, $username, $password, $database);
    
    // Verificar conexão
    if ($conn->connect_error) {
        die(json_encode(['status' => 'error', 'message' => 'Conexão falhou: ' . $conn->connect_error]));
    }
    
    // Configurar para UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Receber dados do body
$data = json_decode(file_get_contents('php://input'), true);

// Se não houver dados ou action
if (!$data || !isset($data['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos ou ação não especificada']);
    exit;
}

$action = $data['action'];

// Realizar ação com base no parâmetro 'action'
switch ($action) {
    case 'getEmployees':
        getEmployees($data);
        break;
    case 'getNextId':
        getNextId($data);
        break;
    case 'registerEmployee':
        registerEmployee($data);
        break;
    case 'updateEmployee':
        updateEmployee($data);
        break;
    case 'deleteEmployee':
        deleteEmployee($data);
        break;
    case 'deleteEmpresa':
        deleteEmpresa($data);
        break;
    case 'syncDeletedEmpresa':
        syncDeletedEmpresa($data);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Ação desconhecida']);
        break;
}

// Obter funcionários de uma empresa específica
function getEmployees($data) {
    if (!isset($data['empresa_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID da empresa não fornecido']);
        return;
    }
    
    $empresa_id = $data['empresa_id'];
    $conn = getAppDbConnection();
    
    // Preparar consulta com parâmetro de empresa
    $stmt = $conn->prepare("SELECT * FROM employees WHERE empresa_id = ?");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        echo json_encode(['status' => 'success', 'employees' => $employees]);
    } else {
        echo json_encode(['status' => 'success', 'employees' => [], 'message' => 'Nenhum funcionário encontrado']);
    }
    
    $stmt->close();
    $conn->close();
}

// Gerar próximo ID para funcionário
function getNextId($data) {
    if (!isset($data['empresa_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID da empresa não fornecido']);
        return;
    }
    
    $empresa_id = $data['empresa_id'];
    $conn = getAppDbConnection();
    
    // Encontrar o maior ID atual para a empresa específica
    $stmt = $conn->prepare("SELECT id FROM employees WHERE empresa_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['id'];
        
        // Extrair o número do último ID (assumindo formato EMP001, EMP002, etc)
        preg_match('/EMP(\d+)/', $lastId, $matches);
        
        if (isset($matches[1])) {
            $lastNum = intval($matches[1]);
            $nextNum = $lastNum + 1;
        } else {
            // Se o formato não for o esperado, começar do 1
            $nextNum = 1;
        }
    } else {
        // Se não houver registros, começar do 1
        $nextNum = 1;
    }
    
    // Formatar o próximo ID com zeros à esquerda (ex: EMP001)
    $nextId = 'EMP' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    
    echo json_encode(['status' => 'success', 'nextId' => $nextId]);
    
    $stmt->close();
    $conn->close();
}

// Registrar novo funcionário
function registerEmployee($data) {
    // Verificar se todos os campos necessários estão presentes
    if (!isset($data['id']) || !isset($data['name']) || !isset($data['position']) || 
        !isset($data['department']) || !isset($data['empresa_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
        return;
    }
    
    $id = $data['id'];
    $name = $data['name'];
    $position = $data['position'];
    $department = $data['department'];
    $digitalSignature = isset($data['digitalSignature']) ? $data['digitalSignature'] : 0;
    $empresa_id = $data['empresa_id'];
    
    $conn = getAppDbConnection();
    
    // Verificar se a empresa existe
    $stmt = $conn->prepare("SELECT id FROM empresas WHERE id = ?");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Empresa não encontrada']);
        $stmt->close();
        $conn->close();
        return;
    }
    
    // Inserir funcionário
    $stmt = $conn->prepare("INSERT INTO employees (id, name, position, department, digital_signature, empresa_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $id, $name, $position, $department, $digitalSignature, $empresa_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Funcionário cadastrado com sucesso']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar funcionário: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
}

// Atualizar funcionário existente
function updateEmployee($data) {
    // Verificar se todos os campos necessários estão presentes
    if (!isset($data['id']) || !isset($data['name']) || !isset($data['position']) || 
        !isset($data['department']) || !isset($data['empresa_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
        return;
    }
    
    $id = $data['id'];
    $name = $data['name'];
    $position = $data['position'];
    $department = $data['department'];
    $digitalSignature = isset($data['digitalSignature']) ? $data['digitalSignature'] : 0;
    $empresa_id = $data['empresa_id'];
    
    $conn = getAppDbConnection();
    
    // Atualizar funcionário
    $stmt = $conn->prepare("UPDATE employees SET name = ?, position = ?, department = ?, digital_signature = ? WHERE id = ? AND empresa_id = ?");
    $stmt->bind_param("sssisi", $name, $position, $department, $digitalSignature, $id, $empresa_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Funcionário atualizado com sucesso']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Funcionário não encontrado ou nenhuma alteração feita']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar funcionário: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
}

// Excluir funcionário
function deleteEmployee($data) {
    if (!isset($data['id']) || !isset($data['empresa_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID do funcionário ou da empresa não fornecido']);
        return;
    }
    
    $id = $data['id'];
    $empresa_id = $data['empresa_id'];
    
    $conn = getAppDbConnection();
    
    // Excluir funcionário
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ? AND empresa_id = ?");
    $stmt->bind_param("si", $id, $empresa_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Funcionário excluído com sucesso']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Funcionário não encontrado']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir funcionário: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
}

// Excluir empresa e seus funcionários (para chamada direta da API)
function deleteEmpresa($data) {
    if (!isset($data['empresa_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID da empresa não fornecido']);
        return;
    }
    
    $empresa_id = $data['empresa_id'];
    
    $conn = getAppDbConnection();
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Primeiro excluir todos os funcionários da empresa
        $stmt = $conn->prepare("DELETE FROM employees WHERE empresa_id = ?");
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $stmt->close();
        
        // Depois excluir a empresa
        $stmt = $conn->prepare("DELETE FROM empresas WHERE id = ?");
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Commit da transação
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Empresa e funcionários excluídos com sucesso']);
        } else {
            // Rollback se a empresa não for encontrada
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Empresa não encontrada']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir empresa: ' . $e->getMessage()]);
    }
    
    $conn->close();
}

// Sincronização de exclusão de empresa - chamada pelo trigger do site
function syncDeletedEmpresa($data) {
    if (!isset($data['empresa_email'])) {
        file_put_contents('sync_error_log.txt', date('Y-m-d H:i:s') . " - ID da empresa não fornecido\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Email da empresa não fornecido']);
        return;
    }
    
    $empresa_email = $data['empresa_email'];
    
    $conn = getAppDbConnection();
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Primeiro encontrar o ID da empresa pelo email
        $stmt = $conn->prepare("SELECT id FROM empresas WHERE email = ?");
        $stmt->bind_param("s", $empresa_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $empresa_id = $row['id'];
            $stmt->close();
            
            // Excluir todos os funcionários da empresa
            $stmt = $conn->prepare("DELETE FROM employees WHERE empresa_id = ?");
            $stmt->bind_param("i", $empresa_id);
            $stmt->execute();
            $stmt->close();
            
            // Excluir a empresa
            $stmt = $conn->prepare("DELETE FROM empresas WHERE id = ?");
            $stmt->bind_param("i", $empresa_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit da transação
            $conn->commit();
            file_put_contents('sync_log.txt', date('Y-m-d H:i:s') . " - Exclusão sincronizada com sucesso para email: $empresa_email\n", FILE_APPEND);
            echo json_encode(['status' => 'success', 'message' => 'Exclusão sincronizada com sucesso']);
        } else {
            // Empresa não encontrada
            $stmt->close();
            $conn->rollback();
            file_put_contents('sync_error_log.txt', date('Y-m-d H:i:s') . " - Empresa não encontrada para email: $empresa_email\n", FILE_APPEND);
            echo json_encode(['status' => 'error', 'message' => 'Empresa não encontrada']);
        }
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        file_put_contents('sync_error_log.txt', date('Y-m-d H:i:s') . " - Erro ao sincronizar exclusão: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao sincronizar exclusão: ' . $e->getMessage()]);
    }
    
    $conn->close();
} 