<?php
session_start();
if (!isset($_SESSION["candidato_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Buscar informações do candidato
try {
    $stmt = $pdo->prepare("SELECT * FROM candidatos WHERE id = ?");
    $stmt->execute([$_SESSION['candidato_id']]);
    $candidato = $stmt->fetch();
    
    // Buscar candidaturas do candidato
    $stmt = $pdo->prepare("
        SELECT c.*, v.titulo, v.empresa_id, e.nome as empresa_nome
        FROM candidaturas c
        JOIN vagas v ON c.vaga_id = v.id
        JOIN empresas_recrutamento e ON v.empresa_id = e.id
        WHERE c.candidato_id = ?
        ORDER BY c.data_candidatura DESC
    ");
    $stmt->execute([$_SESSION['candidato_id']]);
    $candidaturas = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Candidato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../all.css/login.css">
    <link rel="stylesheet" href="../all.css/emprego.css/emp_search.css">
    <style>
        :root {
            --primary-color: #3EB489;
            --primary-light: #4fc89a;
            --primary-dark: #339873;
            --secondary-color: #2c3e50;
            --light-gray: #f5f7fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
            --box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
            --border-radius: 12px;
            --container-width: 1200px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: var(--container-width);
            margin: 20px auto;
            padding: 20px;
        }
        
        /* Header styles are preserved */
        .header {
            padding: 10px 0;
            margin-bottom: 20px;
        }
        
        .logo img {
            height: 80px;
        }
        
        .search-jobs-button {
            background-color: #3EB489;
            color: white;
            padding: 8px 16px;
            border-radius: 45px;
            text-decoration: none;
            margin-right: 10px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            height: 33px;
        }
        
        .search-jobs-button i {
            margin-right: 6px;
        }
        
        .user-menu {
            position: absolute;
            top: 60px;
            right: 50px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 100;
            display: none;
            width: 200px;
        }
        
        .user-menu.visible {
            display: block;
        }
        
        .user-menu-item {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
        }
        
        .user-menu-item:last-child {
            border-bottom: none;
        }
        
        .user-menu-item a {
            color: #333;
            text-decoration: none;
            display: block;
        }
        
        .user-menu-item:hover {
            background-color: #f5f5f5;
        }
        
        /* New styles below */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .welcome-section {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 10px;
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
            border-left: 5px solid var(--primary-color);
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(62, 180, 137, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }
        
        .welcome-section h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.8rem;
            position: relative;
        }
        
        .welcome-section p {
            color: var(--dark-gray);
            font-size: 1.1rem;
            max-width: 80%;
        }
        
        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            height: fit-content;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .card h2 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            font-size: 1.5rem;
            position: relative;
            padding-bottom: 12px;
        }
        
        .card h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 60px;
            background-color: var(--primary-color);
            border-radius: 10px;
        }
        
        .action-btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 15px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(62, 180, 137, 0.3);
        }
        
        .action-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(62, 180, 137, 0.4);
        }
        
        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .profile-info {
                grid-template-columns: 1fr;
            }
        }
        
        .profile-item {
            margin-bottom: 15px;
            padding: 15px;
            background-color: var(--light-gray);
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .profile-item:hover {
            background-color: #e6f7f2;
        }
        
        .profile-item strong {
            display: block;
            margin-bottom: 8px;
            color: var(--secondary-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .profile-item span {
            display: block;
            font-size: 1.1rem;
            color: #333;
        }
        
        .profile-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .profile-item a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .candidaturas-container {
            margin-top: 25px;
            max-height: 500px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) var(--light-gray);
            padding-right: 10px;
        }
        
        .candidaturas-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .candidaturas-container::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 10px;
        }
        
        .candidaturas-container::-webkit-scrollbar-thumb {
            background-color: var(--primary-light);
            border-radius: 10px;
        }
        
        .candidatura-item {
            border: none;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }
        
        .candidatura-item:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .candidatura-item h3 {
            margin-top: 0;
            margin-bottom: 12px;
            color: var(--secondary-color);
            font-size: 1.2rem;
        }
        
        .candidatura-item p {
            margin: 10px 0;
            color: #555;
            line-height: 1.7;
        }
        
        .candidatura-item p strong {
            color: var(--secondary-color);
            display: inline-block;
            width: 140px;
        }
        
        .candidatura-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            padding: 8px 15px;
            font-size: 0.9rem;
            border-radius: 50px;
            text-decoration: none;
            color: white;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
        }
        
        .btn-view {
            background-color: var(--primary-color);
            box-shadow: 0 2px 5px rgba(62, 180, 137, 0.3);
        }
        
        .btn-view:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 8px rgba(62, 180, 137, 0.4);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            margin-left: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-recebida { 
            background-color: #6c757d; 
            box-shadow: 0 2px 5px rgba(108, 117, 125, 0.3);
        }
        
        .status-analise { 
            background-color: #ffc107; 
            color: #212529; 
            box-shadow: 0 2px 5px rgba(255, 193, 7, 0.3);
        }
        
        .status-entrevista { 
            background-color: #17a2b8; 
            box-shadow: 0 2px 5px rgba(23, 162, 184, 0.3);
        }
        
        .status-aprovada { 
            background-color: #28a745; 
            box-shadow: 0 2px 5px rgba(40, 167, 69, 0.3);
        }
        
        .status-rejeitada { 
            background-color: #dc3545; 
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        }
        
        .no-candidaturas {
            text-align: center;
            padding: 30px;
            color: #666;
            background-color: var(--light-gray);
            border-radius: 10px;
        }
        
        .no-candidaturas i {
            font-size: 3rem;
            color: var(--dark-gray);
            margin-bottom: 15px;
            display: block;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-header h2 {
            margin-bottom: 0;
        }
        
        .candidaturas-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            color: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(62, 180, 137, 0.2);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(62, 180, 137, 0.3);
        }
        
        .stat-card h3 {
            font-size: 0.9rem;
            margin: 0 0 5px 0;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
        }
        
        .profile-progress {
            margin-top: 25px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
        }
        
        .progress-title {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .progress-bar-container {
            height: 10px;
            background-color: var(--medium-gray);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            border-radius: 5px;
            transition: width 0.5s ease;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header mantido como solicitado -->
        <div class="header">
            <div class="logo">
                <img src="../fotos/sam30-13.png" alt="SAM Logo">
            </div>
            
            <div class="user-section">
                <a href="job_search_page.php" class="search-jobs-button">
                    <i class="fas fa-search"></i> Buscar Vagas
                </a>
                
                <div class="user-dropdown" id="user-dropdown">
                    <div class="user-avatar">
                        <img src="../icones/icons-sam-19.svg" alt="" width="40">
                    </div>
                    <span><?php echo htmlspecialchars($candidato['nome'] ?? $_SESSION['candidato_nome']); ?></span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
                
                <div class="settings-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3EB489" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="user-menu" id="user-menu">
                <div class="user-menu-item">
                    <a href="job_search_page.php">Buscar Vagas</a>
                </div>
                <div class="user-menu-item">
                    <a href="painel_candidato.php">Meu Perfil</a>
                </div>
                <div class="user-menu-item">
                    <a href="minhas_candidaturas.php">Minhas Candidaturas</a>
                </div>
                <div class="user-menu-item">
                    <a href="logout.php">Sair</a>
                </div>
            </div>
        </div>

        <div class="welcome-section fade-in">
            <h1>Bem-vindo(a) ao seu painel, <?php echo htmlspecialchars($candidato['nome'] ?? $_SESSION['candidato_nome']); ?>!</h1>
            <p>Gerencie seu perfil profissional e acompanhe todas as suas candidaturas de forma eficiente e organizada.</p>
        </div>
        
        <div class="content-grid">
            <div class="card fade-in">
                <div class="card-header">
                    <h2>Seu Perfil</h2>
                    <a href="editar_perfil.php" class="action-btn">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Total de Candidaturas</h3>
                        <div class="number"><?php echo isset($candidaturas) ? count($candidaturas) : '0'; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Em Processo</h3>
                        <div class="number">
                            <?php
                            $emProcesso = 0;
                            if (isset($candidaturas)) {
                                foreach ($candidaturas as $candidatura) {
                                    if ($candidatura['status'] == 'Analise' || $candidatura['status'] == 'Entrevista') {
                                        $emProcesso++;
                                    }
                                }
                            }
                            echo $emProcesso;
                            ?>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Aprovadas</h3>
                        <div class="number">
                            <?php
                            $aprovadas = 0;
                            if (isset($candidaturas)) {
                                foreach ($candidaturas as $candidatura) {
                                    if ($candidatura['status'] == 'Aprovada') {
                                        $aprovadas++;
                                    }
                                }
                            }
                            echo $aprovadas;
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="profile-info">
                    <div class="profile-item">
                        <strong>Nome</strong>
                        <span><?php echo htmlspecialchars($candidato['nome'] ?? ''); ?></span>
                    </div>
                    
                    <div class="profile-item">
                        <strong>Email</strong>
                        <span><?php echo htmlspecialchars($candidato['email'] ?? ''); ?></span>
                    </div>
                    
                    <div class="profile-item">
                        <strong>Telefone</strong>
                        <span><?php echo htmlspecialchars($candidato['telefone'] ?? 'Não informado'); ?></span>
                    </div>
                    
                    <div class="profile-item">
                        <strong>Data de Nascimento</strong>
                        <span><?php echo isset($candidato['data_nascimento']) ? date('d/m/Y', strtotime($candidato['data_nascimento'])) : 'Não informada'; ?></span>
                    </div>
                </div>
                
                <div class="profile-item">
                    <strong>Currículo</strong>
                    <span>
                        <?php if (!empty($candidato['cv_anexo'])): ?>
                            <a href="<?php echo htmlspecialchars($candidato['cv_anexo']); ?>" target="_blank">
                                <i class="fas fa-file-pdf"></i> Visualizar currículo
                            </a>
                        <?php else: ?>
                            <span style="color: #dc3545;">
                                <i class="fas fa-exclamation-circle"></i> Nenhum currículo anexado
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="profile-progress">
                    <div class="progress-title">
                        <span>Perfil completo</span>
                        <?php
                        $progress = 20; // Base progress
                        if (!empty($candidato['telefone'])) $progress += 20;
                        if (!empty($candidato['data_nascimento'])) $progress += 20;
                        if (!empty($candidato['cv_anexo'])) $progress += 40;
                        ?>
                        <span><strong><?php echo $progress; ?>%</strong></span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                </div>
            </div>
            
            <div class="card fade-in">
                <div class="candidaturas-header">
                    <h2>Suas Candidaturas</h2>
                    <a href="job_search_page.php" class="action-btn">
                        <i class="fas fa-search"></i> Novas Vagas
                    </a>
                </div>
                
                <div class="candidaturas-container">
                    <?php if (isset($candidaturas) && count($candidaturas) > 0): ?>
                        <?php foreach($candidaturas as $candidatura): ?>
                            <div class="candidatura-item">
                                <h3><?php echo htmlspecialchars($candidatura['titulo']); ?></h3>
                                <p>
                                    <strong>Empresa:</strong> <?php echo htmlspecialchars($candidatura['empresa_nome']); ?>
                                </p>
                                <p>
                                    <strong>Data da candidatura:</strong> <?php echo date('d/m/Y', strtotime($candidatura['data_candidatura'])); ?>
                                </p>
                                <p>
                                    <strong>Status:</strong> 
                                    <span class="status-badge status-<?php echo strtolower($candidatura['status']); ?>">
                                        <?php echo htmlspecialchars($candidatura['status']); ?>
                                    </span>
                                </p>
                                <div class="candidatura-actions">
                                    <a href="visualizar_vaga.php?id=<?php echo $candidatura['vaga_id']; ?>" class="btn-small btn-view">
                                        <i class="fas fa-eye"></i> Ver Detalhes
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-candidaturas">
                            <i class="fas fa-briefcase"></i>
                            <p>Você ainda não se candidatou a nenhuma vaga.</p>
                            <p>Explore as vagas disponíveis e inicie sua jornada profissional!</p>
                            <a href="job_search_page.php" class="action-btn">
                                <i class="fas fa-search"></i> Buscar Vagas
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animação de entrada para os elementos com classe fade-in
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 100 * index);
            });
            
            // Dropdown do usuário
            const userDropdown = document.getElementById('user-dropdown');
            const userMenu = document.getElementById('user-menu');
            
            if (userDropdown && userMenu) {
                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('visible');
                });
                
                // Fechar dropdown ao clicar fora
                document.addEventListener('click', function(e) {
                    if (userMenu.classList.contains('visible') && 
                        !userDropdown.contains(e.target) && 
                        !userMenu.contains(e.target)) {
                        userMenu.classList.remove('visible');
                    }
                });
            }
        });
    </script>
</body>
</html>