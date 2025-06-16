<?php
require_once '../config.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_adm'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Adicionar logs temporários para depuração
error_log("DEBUG: get_funcionarios_subsidio.php iniciado");
error_log("DEBUG: Session id_adm = " . (isset($_SESSION['id_adm']) ? $_SESSION['id_adm'] : 'Não definido'));

// Buscar ID da empresa do administrador
$sql_empresa = "SELECT id_empresa FROM empresa WHERE adm_id = ?";
$stmt_empresa = $conn->prepare($sql_empresa);

if ($stmt_empresa === false) {
    error_log("ERRO: Preparação da query da empresa falhou: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor ao buscar empresa']);
    exit;
}

$stmt_empresa->bind_param("i", $_SESSION['id_adm']);
$stmt_empresa->execute();
$result_empresa = $stmt_empresa->get_result();
$empresa = $result_empresa->fetch_assoc();

$stmt_empresa->close();

if (!$empresa) {
    error_log("ERRO: Empresa não encontrada para o admin logado");
    http_response_code(500);
    echo json_encode(['error' => 'Empresa não encontrada para o usuário logado']);
    exit;
}

$empresa_id = $empresa['id_empresa'];
error_log("DEBUG: Session empresa_id = " . $empresa_id);

$subsidio_id = isset($_GET['subsidio_id']) ? intval($_GET['subsidio_id']) : 0;
$tipo_subsidio = isset($_GET['tipo']) ? $_GET['tipo'] : '';
error_log("DEBUG: GET subsidio_id = " . $subsidio_id);
error_log("DEBUG: GET tipo = " . $tipo_subsidio);

// Buscar todos os funcionários da empresa (remover filtro por estado = 'Ativo')
$sql = "SELECT f.id_fun, f.nome, c.nome as cargo, d.nome as departamento,
        COALESCE(sf.ativo, 0) as ativo
        FROM funcionario f
        LEFT JOIN cargos c ON f.cargo = c.id
        LEFT JOIN departamentos d ON f.departamento = d.id
        LEFT JOIN subsidios_funcionarios sf ON f.id_fun = sf.funcionario_id 
            AND sf.subsidio_id = ? AND sf.tipo_subsidio = ?
        WHERE f.empresa_id = ? 
        ORDER BY f.nome";

$stmt = $conn->prepare($sql);

// Adicionar verificação de erro na preparação da query
if ($stmt === false) {
    error_log("ERRO: Preparação da query de funcionários falhou: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor ao buscar funcionários']);
    exit;
}

$stmt->bind_param("isi", $subsidio_id, $tipo_subsidio, $empresa_id);

// Adicionar verificação de erro na execução da query
if ($stmt->execute() === false) {
    error_log("ERRO: Execução da query de funcionários falhou: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor ao executar consulta']);
    exit;
}

$result = $stmt->get_result();

$funcionarios = [];
while ($row = $result->fetch_assoc()) {
    $funcionarios[] = $row;
}

error_log("DEBUG: " . count($funcionarios) . " funcionários encontrados.");

header('Content-Type: application/json');
echo json_encode($funcionarios);

$stmt->close();
$conn->close(); 