<?php
session_start();
// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Redireciona para a página de login
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../all.css/emprego.css/emp_view.css">
  <link rel="icon" type="" href="sam2-05.png">
  <title>SAM Emprego - Assistente de Logística</title>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo">
          <img src="../fotos/sam30-13.png" alt="SAM Emprego Logo">
      </div>
      <div class="user-section">
          <div class="user-dropdown">
              <div class="user-avatar">
                <img src="../icones/icons-sam-19.svg" alt="" width="40">
              </div>
              <span>Josilde da Co...</span>
              <span class="arrow-icon arrow-down"></span>
          </div>
          <div class="settings-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3EB489" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="3"></circle>
                  <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
              </svg>
          </div>
      </div>
  </div>
    
    <div class="posting-container">
      <a href="job_search_page.php" class="back-link">← Voltar à lista de empregos</a>
      <div class="job-card">
        <h1 class="job-title">Assistente de Logística</h1>
        <div class="job-company">Empregador: <strong>Grupo Kurt</strong>
        </div>
        
        <div class="status-buttons">
          <div class="status-tag closed-tag">Vaga Fechada</div>
          <a href="#" class="apply-button">candidatar-se  </a>
        </div>

        <div class="job-details">
          <div class="job-meta">
            <div class="job-meta-item">
              <span class="job-meta-icon">📊</span> Logística e Distribuição
            </div>
            <div class="job-meta-item">
              <span class="job-meta-icon">💰</span> 115.000,00 - 180.000,00 AOA / Mês
            </div>
            <div class="job-meta-item">
              <span class="job-meta-icon">🏠</span> Trabalho Remoto
            </div>
            <div class="job-meta-item">
              <span class="job-meta-icon">📝</span> Efetivo
            </div>
          </div>
        </div>
      </div>
      
      <div class="job-card">
        <div class="section-card">
          <h2 class="section-title">Geral</h2>
          <div class="job-location"><strong>Localização do trabalho:</strong> Remoto (Online)</div>
          <div class="job-salary"><strong>Salário:</strong> 115.000,00 - 180.000,00 AOA / Mês</div>
          
          <div class="payment-details">
            <strong>Método de Pagamento:</strong> Tranferência Bancária
          </div>
          <div class="language-details">
            <strong>Língua:</strong> Português
          </div>

        </div>

        <div class="section-card">        
        <h2 class="section-title">Carga Horária</h2>
        <div class="job-timezone"><strong>Fuso horário:</strong> África Ocidental (GMT +1)</div>
            <div class="schedule-grid">
              <div>
                <div class="schedule-item"><strong>Dias úteis semanais:</strong> Segunda à Sexta</div>
                <div class="schedule-item"><strong>Horas úteis semanais:</strong> 15 - 20 Horas</div>
              </div>
              <div>
                <div class="schedule-item"><strong>Horas úteis diárias:</strong> 3 - 4 Horas</div>
                <div class="schedule-item"><strong>Horário de trabalho:</strong> 09:00 - 13:00</div>
              </div>
            </div>
        </div>  
        <div class="section-card-description">  
          <h2 class="section-title">Descrição do trabalho</h2>
          <div class="job-description">
            <p>O Grupo Kurt é uma empresa de distribuição de produtos para revenda, emprestimos rentáveis e muito mais. No trabalho em questão, o candidato, terá a função de organização de documentação.</p>
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>