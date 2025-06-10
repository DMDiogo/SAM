<?php
session_start();
include('../config.php');
include('../protect.php');

if (!isset($_SESSION['id_adm'])) {
    die("Acesso negado");
}

if (!isset($_GET['id'])) {
    die("ID do turno não fornecido");
}

$turno_id = $_GET['id'];

// Buscar dados do turno
$sql = "SELECT * FROM turnos_padrao WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $turno_id);
$stmt->execute();
$result = $stmt->get_result();

if ($turno = $result->fetch_assoc()) {
    // Retornar os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'hora_entrada' => $turno['hora_entrada'],
        'hora_saida' => $turno['hora_saida'],
        'almoco_inicio' => $turno['almoco_inicio'],
        'almoco_fim' => $turno['almoco_fim'],
        'dias_semana' => $turno['dias_semana']
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Turno não encontrado']);
}
?> 