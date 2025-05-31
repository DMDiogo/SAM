<?php
session_start();
if (!isset($_SESSION["empresa_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Buscar informações da empresa para o header
try {
    $stmt = $pdo->prepare("SELECT * FROM empresas_recrutamento WHERE id = ?");
    $stmt->execute([$_SESSION['empresa_id']]);
    $empresa = $stmt->fetch();
    
    // Buscar todas as candidaturas para as vagas da empresa
    $stmt = $pdo->prepare("
        SELECT 
            c.*, 
            v.titulo as vaga_titulo,
            cd.nome as candidato_nome,
            cd.email as candidato_email,
            cd.telefone as candidato_telefone,
            cd.curriculo_path as candidato_curriculo,
            cd.formacao as candidato_formacao,
            cd.habilidades as candidato_habilidades,
            cd.experiencia as candidato_experiencia,
            cd.data_nascimento as candidato_data_nascimento,
            cd.endereco as candidato_endereco
        FROM candidaturas c
        JOIN vagas v ON c.vaga_id = v.id
        JOIN candidatos cd ON c.candidato_id = cd.id
        WHERE v.empresa_id = ?
        ORDER BY c.data_candidatura DESC
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $candidaturas = $stmt->fetchAll();

    // Atualizar o card de cada candidatura para mostrar mais informações
    foreach ($candidaturas as $key => $candidatura) {
        // Formatar a data de nascimento
        if ($candidatura['candidato_data_nascimento']) {
            $data = new DateTime($candidatura['candidato_data_nascimento']);
            $candidaturas[$key]['candidato_data_nascimento_formatada'] = $data->format('d/m/Y');
        }
    }
} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="" href="sam2-05.png">
    <link rel="stylesheet" href="../all.css/emprego.css/emp_vagas.css">
    <link rel="stylesheet" href="../all.css/emprego.css/emp_search.css">
    <link rel="stylesheet" href="../all.css/emprego.css/emp_header.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        .candidaturas-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 40px;
            color: #2c3e50;
        }

        .page-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #3EB489, #2c8a66);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-title p {
            color: #7f8c8d;
            font-size: 1.1rem;
            font-weight: 400;
        }

        .candidatura-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(62, 180, 137, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .candidatura-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #3EB489, #27ae60);
        }

        .candidatura-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }

        .candidatura-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }

        .candidatura-header h2 {
            color: #2c3e50;
            font-size: 1.6rem;
            font-weight: 600;
            margin: 0;
            flex: 1;
        }

        .status-badge {
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-left: 20px;
        }

        .status-pendente { 
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }
        .status-visualizada { 
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        .status-analise { 
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }
        .status-entrevista { 
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }
        .status-aprovado { 
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
        }
        .status-rejeitado { 
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .candidato-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #3EB489;
        }

        .info-section h3 {
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-item strong {
            color: #34495e;
            font-weight: 600;
            min-width: 100px;
            flex-shrink: 0;
        }

        .info-item span {
            color: #7f8c8d;
            line-height: 1.5;
        }

        .curriculo-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #3EB489, #27ae60);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .curriculo-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(62, 180, 137, 0.3);
            color: white;
            text-decoration: none;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 20px;
            border-top: 2px solid #f8f9fa;
            justify-content: center;
        }

        .actions button {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
            justify-content: center;
        }

        .actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .actions button:nth-child(1) {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .actions button:nth-child(2) {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }

        .actions button:nth-child(3) {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }

        .actions button:nth-child(4) {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
        }

        .actions button:nth-child(5) {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 4rem;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .candidatura-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .status-badge {
                margin-left: 0;
                align-self: flex-start;
            }

            .candidato-info {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .actions button {
                min-width: 100%;
            }

            .page-title h1 {
                font-size: 2rem;
            }
        }
    </style>
    <title>Candidaturas - SAM Emprego</title>
</head>
<body>
<header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="../fotos/sam30-13.png" alt="SAM Emprego Logo">
            </div>
            <div class="nav-container">
                <nav class="nav-menu">
                    <a href="job_search_page_emp.php">Vagas</a>
                    <a href="emp_vagas.php">Minhas vagas</a>
                    <a href="empresas_candidaturas.php" class="active">Candidaturas</a>
                    <a href="painel_candidato.php">Perfil</a>
                </nav>
            </div>
            <div class="user-section">
                <div class="user-dropdown" id="userDropdownToggle">
                    <div class="user-avatar">
                        <img src="../icones/icons-sam-19.svg" alt="" width="40">
                    </div>
                    <span><?php echo htmlspecialchars($empresa['nome'] ?? $_SESSION['empresa_nome']); ?></span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                    
                    <!-- Dropdown Menu -->
                    <div class="dropdown-menu" id="userDropdownMenu">
                        <a href="painel_candidato.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            Meu Perfil
                        </a>
                        <a href="editar_perfil.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            Configurações
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
                
                <div class="settings-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3EB489" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </header>

    <div class="candidaturas-container">
        <div class="page-title">
            <h1>Candidaturas Recebidas</h1>
            <p>Gerencie todas as candidaturas para suas vagas em um só lugar</p>
        </div>
        
        <?php if (isset($candidaturas) && !empty($candidaturas)): ?>
            <?php foreach ($candidaturas as $candidatura): ?>
                <div class="candidatura-card">
                    <div class="candidatura-header">
                        <h2><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($candidatura['vaga_titulo']); ?></h2>
                        <span class="status-badge status-<?php echo strtolower(str_replace([' ', 'ç'], ['', 'c'], $candidatura['status'])); ?>">
                            <?php echo htmlspecialchars($candidatura['status']); ?>
                        </span>
                    </div>
                    
                    <div class="candidato-info">
                        <div class="info-section">
                            <h3><i class="fas fa-user"></i> Informações Pessoais</h3>
                            <div class="info-item">
                                <strong>Nome:</strong>
                                <span><?php echo htmlspecialchars($candidatura['candidato_nome']); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Email:</strong>
                                <span><?php echo htmlspecialchars($candidatura['candidato_email']); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Telefone:</strong>
                                <span><?php echo htmlspecialchars($candidatura['candidato_telefone']); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Nascimento:</strong>
                                <span><?php echo htmlspecialchars($candidatura['candidato_data_nascimento_formatada'] ?? 'Não informado'); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Endereço:</strong>
                                <span><?php echo htmlspecialchars($candidatura['candidato_endereco'] ?? 'Não informado'); ?></span>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3><i class="fas fa-graduation-cap"></i> Formação e Experiência</h3>
                            <div class="info-item">
                                <strong>Formação:</strong>
                                <span><?php echo nl2br(htmlspecialchars($candidatura['candidato_formacao'] ?? 'Não informado')); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Experiência:</strong>
                                <span><?php echo nl2br(htmlspecialchars($candidatura['candidato_experiencia'] ?? 'Não informado')); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Habilidades:</strong>
                                <span><?php echo nl2br(htmlspecialchars($candidatura['candidato_habilidades'] ?? 'Não informado')); ?></span>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3><i class="fas fa-calendar"></i> Informações da Candidatura</h3>
                            <div class="info-item">
                                <strong>Data:</strong>
                                <span><?php echo date('d/m/Y H:i', strtotime($candidatura['data_candidatura'])); ?></span>
                            </div>
                            <?php if ($candidatura['candidato_curriculo']): ?>
                                <div class="info-item">
                                    <strong>Currículo:</strong>
                                    <span>
                                        <a href="<?php echo htmlspecialchars($candidatura['candidato_curriculo']); ?>" 
                                           target="_blank" class="curriculo-link">
                                            <i class="fas fa-file-pdf"></i>
                                            Ver Currículo
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="actions">
                        <button onclick="updateStatus(<?php echo $candidatura['id']; ?>, 'Visualizada')">
                            <i class="fas fa-eye"></i> Marcar como vista
                        </button>
                        <button onclick="updateStatus(<?php echo $candidatura['id']; ?>, 'Em análise')">
                            <i class="fas fa-search"></i> Em análise
                        </button>
                        <button onclick="updateStatus(<?php echo $candidatura['id']; ?>, 'Entrevista')">
                            <i class="fas fa-comments"></i> Marcar entrevista
                        </button>
                        <button onclick="updateStatus(<?php echo $candidatura['id']; ?>, 'Aprovado')">
                            <i class="fas fa-check"></i> Aprovar
                        </button>
                        <button onclick="updateStatus(<?php echo $candidatura['id']; ?>, 'Rejeitado')">
                            <i class="fas fa-times"></i> Rejeitar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Nenhuma candidatura encontrada</h3>
                <p>Você ainda não recebeu candidaturas para suas vagas publicadas.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function updateStatus(candidaturaId, newStatus) {
        if (confirm('Deseja alterar o status desta candidatura?')) {
            fetch('update_candidatura_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${candidaturaId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao atualizar status.');
                }
            });
        }
    }
    </script>
    <script src="../js/dropdown.js"></script>
</body>
</html>