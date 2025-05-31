<?php
session_start();
require_once 'config/database.php';

// Verificar se o ID da vaga foi fornecido
if (!isset($_GET['id'])) {
    header("Location: job_search_page.php");
    exit();
}

$vaga_id = $_GET['id'];

try {
    // Buscar informações da vaga e da empresa
    $stmt = $pdo->prepare("
        SELECT v.*, e.nome as empresa_nome, e.logo as empresa_logo 
        FROM vagas v 
        JOIN empresas_recrutamento e ON v.empresa_id = e.id 
        WHERE v.id = ?
    ");
    $stmt->execute([$vaga_id]);
    $vaga = $stmt->fetch();

    if (!$vaga) {
        header("Location: job_search_page.php");
        exit();
    }

    // Verificar se o candidato já se candidatou para esta vaga
    $ja_candidatado = false;
    if (isset($_SESSION['candidato_id'])) {
        $stmt = $pdo->prepare("SELECT id FROM candidaturas WHERE candidato_id = ? AND vaga_id = ?");
        $stmt->execute([$_SESSION['candidato_id'], $vaga_id]);
        $ja_candidatado = $stmt->rowCount() > 0;
    }

    // Formatar salário
    $salario = '';
    if ($vaga['salario_min'] && $vaga['salario_max']) {
        $salario = number_format($vaga['salario_min'], 2, ',', '.') . ' - ' . 
                 number_format($vaga['salario_max'], 2, ',', '.') . ' AOA / ' . 
                 ucfirst($vaga['periodo_salario']);
    } elseif ($vaga['salario_min']) {
        $salario = number_format($vaga['salario_min'], 2, ',', '.') . ' AOA / ' . 
                 ucfirst($vaga['periodo_salario']);
    } elseif ($vaga['salario_max']) {
        $salario = 'Até ' . number_format($vaga['salario_max'], 2, ',', '.') . ' AOA / ' . 
                 ucfirst($vaga['periodo_salario']);
    }

    // Mapear status para classes CSS
    $statusClass = '';
    $statusText = '';
    switch ($vaga['status']) {
        case 'Aberta':
            $statusClass = 'status-open';
            $statusText = 'A contratar';
            break;
        case 'Fechada':
            $statusClass = 'status-closed';
            $statusText = 'Vaga Fechada';
            break;
        case 'Pausada':
            $statusClass = 'status-suspended';
            $statusText = 'Vaga Pausada';
            break;
    }

    // Mapear localização
    $localizacoes = [
        'remoto' => 'Remoto (Online)',
        'hibrido' => 'Híbrido',
        'presencial' => 'Presencial'
    ];

    // Mapear tipo de contrato
    $tipos_contrato = [
        'efetivo' => 'Efetivo',
        'meio_periodo' => 'Meio Período',
        'temporario' => 'Temporário',
        'freelancer' => 'Freelancer',
        'estagio' => 'Estágio'
    ];

} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../all.css/emprego.css/emp_view.css">
  <style>
    .job-meta-icon svg {
      vertical-align: middle;
      margin-right: 8px;
      color: #666;
    }
    .job-meta-item {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
    }
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      font-size: 0.95rem;
    }
    .alert-success {
      background-color: #d4edda;
      border-left: 4px solid #28a745;
      color: #155724;
    }
    .alert-danger {
      background-color: #f8d7da;
      border-left: 4px solid #dc3545;
      color: #721c24;
    }
  </style>
  <title>SAM Emprego - <?php echo htmlspecialchars($vaga['titulo']); ?></title>
</head>
<body>
  <div class="container">
    <div class="posting-container">
      <a href="job_search_page.php" class="back-link">← Voltar à lista de empregos</a>

      <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert alert-success">
          <?php 
          echo htmlspecialchars($_SESSION['mensagem_sucesso']);
          unset($_SESSION['mensagem_sucesso']);
          ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['mensagem_erro'])): ?>
        <div class="alert alert-danger">
          <?php 
          echo htmlspecialchars($_SESSION['mensagem_erro']);
          unset($_SESSION['mensagem_erro']);
          ?>
        </div>
      <?php endif; ?>

      <div class="job-card">
        <h1 class="job-title"><?php echo htmlspecialchars($vaga['titulo']); ?></h1>
        <div class="job-company">Empregador: <strong><?php echo htmlspecialchars($vaga['empresa_nome']); ?></strong></div>
        
        <div class="status-buttons">
          <div class="status-tag <?php echo $statusClass; ?>"><?php echo $statusText; ?></div>
          <?php if ($vaga['status'] === 'Aberta'): ?>
            <?php if (isset($_SESSION['empresa_id'])): ?>
              <button class="apply-button" disabled>Área exclusiva para candidatos</button>
            <?php elseif (!isset($_SESSION['candidato_id'])): ?>
              <a href="login.php" class="apply-button">Fazer login para se candidatar</a>
            <?php elseif ($ja_candidatado): ?>
              <button class="apply-button" disabled>Já candidatado</button>
            <?php else: ?>
              <form action="candidatar.php" method="POST" style="display: inline;">
                <input type="hidden" name="vaga_id" value="<?php echo $vaga_id; ?>">
                <button type="submit" class="apply-button">Candidatar-se</button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <div class="job-details">
          <div class="job-meta">
            <?php if ($vaga['categoria']): ?>
            <div class="job-meta-item">
              <span class="job-meta-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="20" x2="18" y2="10"></line>
                  <line x1="12" y1="20" x2="12" y2="4"></line>
                  <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
              </span> <?php echo htmlspecialchars($vaga['categoria']); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($salario): ?>
            <div class="job-meta-item">
              <span class="job-meta-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="1" x2="12" y2="23"></line>
                  <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
              </span> <?php echo $salario; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($vaga['localizacao']): ?>
            <div class="job-meta-item">
              <span class="job-meta-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
              </span> <?php echo htmlspecialchars($localizacoes[$vaga['localizacao']] ?? $vaga['localizacao']); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($vaga['tipo_contrato']): ?>
            <div class="job-meta-item">
              <span class="job-meta-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14,2 14,8 20,8"></polyline>
                  <line x1="16" y1="13" x2="8" y2="13"></line>
                  <line x1="16" y1="17" x2="8" y2="17"></line>
                  <polyline points="10,9 9,9 8,9"></polyline>
                </svg>
              </span> <?php echo htmlspecialchars($tipos_contrato[$vaga['tipo_contrato']] ?? $vaga['tipo_contrato']); ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div class="job-card">
        <div class="section-card">
          <h2 class="section-title">Geral</h2>
          <?php if ($vaga['localizacao']): ?>
          <div class="job-location">
            <strong>Localização do trabalho:</strong> 
            <?php echo htmlspecialchars($localizacoes[$vaga['localizacao']] ?? $vaga['localizacao']); ?>
          </div>
          <?php endif; ?>
          
          <?php if ($salario): ?>
          <div class="job-salary">
            <strong>Salário:</strong> <?php echo $salario; ?>
          </div>
          <?php endif; ?>
          
          <?php if ($vaga['metodo_pagamento']): ?>
          <div class="payment-details">
            <strong>Método de Pagamento:</strong> <?php echo htmlspecialchars($vaga['metodo_pagamento']); ?>
          </div>
          <?php endif; ?>
          
          <?php if ($vaga['idioma']): ?>
          <div class="language-details">
            <strong>Língua:</strong> <?php echo htmlspecialchars($vaga['idioma']); ?>
          </div>
          <?php endif; ?>
        </div>

        <?php if ($vaga['fuso_horario'] || $vaga['dias_uteis'] || $vaga['horas_semanais_min'] || $vaga['horas_diarias_min']): ?>
        <div class="section-card">        
          <h2 class="section-title">Carga Horária</h2>
          <?php if ($vaga['fuso_horario']): ?>
          <div class="job-timezone">
            <strong>Fuso horário:</strong> <?php echo htmlspecialchars($vaga['fuso_horario']); ?>
          </div>
          <?php endif; ?>
          
          <div class="schedule-grid">
            <div>
              <?php if ($vaga['dias_uteis']): ?>
              <div class="schedule-item">
                <strong>Dias úteis semanais:</strong> <?php echo htmlspecialchars($vaga['dias_uteis']); ?>
              </div>
              <?php endif; ?>
              
              <?php if ($vaga['horas_semanais_min'] && $vaga['horas_semanais_max']): ?>
              <div class="schedule-item">
                <strong>Horas úteis semanais:</strong> <?php echo $vaga['horas_semanais_min'] . ' - ' . $vaga['horas_semanais_max']; ?> Horas
              </div>
              <?php endif; ?>
            </div>
            <div>
              <?php if ($vaga['horas_diarias_min'] && $vaga['horas_diarias_max']): ?>
              <div class="schedule-item">
                <strong>Horas úteis diárias:</strong> <?php echo $vaga['horas_diarias_min'] . ' - ' . $vaga['horas_diarias_max']; ?> Horas
              </div>
              <?php endif; ?>
              
              <?php if ($vaga['hora_inicio'] && $vaga['hora_fim']): ?>
              <div class="schedule-item">
                <strong>Horário de trabalho:</strong> <?php echo date('H:i', strtotime($vaga['hora_inicio'])) . ' - ' . date('H:i', strtotime($vaga['hora_fim'])); ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <div class="section-card-description">  
          <h2 class="section-title">Descrição do trabalho</h2>
          <div class="job-description">
            <p><?php echo nl2br(htmlspecialchars($vaga['descricao'])); ?></p>
          </div>
          
          <?php if ($vaga['requisitos']): ?>
          <h2 class="section-title">Requisitos</h2>
          <div class="job-requirements">
            <p><?php echo nl2br(htmlspecialchars($vaga['requisitos'])); ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
<script src="../js/dropdown.js"></script>
</html>