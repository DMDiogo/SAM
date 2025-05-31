<?php
session_start();
if (!isset($_SESSION["empresa_id"])) {
    die(json_encode(['success' => false, 'message' => 'Não autorizado']));
}

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    try {
        $stmt = $pdo->prepare("UPDATE candidaturas SET status = ? WHERE id = ? AND empresa_id = ?");
        $success = $stmt->execute([
            $_POST['status'],
            $_POST['id'],
            $_SESSION['empresa_id']
        ]);
        
        echo json_encode(['success' => $success]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
}
