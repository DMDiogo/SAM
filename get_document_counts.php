<?php
session_start();
include 'config.php'; // Conexão com o banco de dados

// Verifica se o usuário está logado e tem uma empresa associada
if (!isset($_SESSION['id_empresa'])) {
    echo json_encode([]);
    exit;
}

$empresa_id = $_SESSION['id_empresa']; // Define $empresa_id a partir da sessão

// Verifica se o ID do funcionário foi passado
if (!isset($_GET['employeeId'])) {
    echo json_encode([]);
    exit;
}

$employeeId = $_GET['employeeId']; // ID do funcionário selecionado

// Função para contar documentos em uma pasta para um funcionário específico
function countDocuments($conn, $folder, $employeeId) {
    $sql = "SELECT COUNT(*) as total FROM documentos WHERE folder = ? AND num_funcionario = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $folder, $employeeId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Contar documentos para cada pasta
$documentCounts = [
    'documentacao' => countDocuments($conn, 'documentacao', $employeeId),
    'frequencia' => countDocuments($conn, 'frequencia', $employeeId),
    'solicitacoes' => countDocuments($conn, 'solicitacoes', $employeeId),
    'outros' => countDocuments($conn, 'outros', $employeeId),
];

// Retornar os contadores em formato JSON
header('Content-Type: application/json');
echo json_encode($documentCounts);
?>