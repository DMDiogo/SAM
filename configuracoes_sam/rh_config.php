<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Configurações de RH - Dashboard RH</title>
    <link rel="stylesheet" href="../all.css/registro3.css">
    <link rel="stylesheet" href="../all.css/configuracoes.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                            <p>Segunda a Sexta: 8h - 18h</p>
                            <button class="btn-primary">Editar</button>
                        </div>
                        <div class="policy-card">
                            <h4>Política de Home Office</h4>
                            <p>2 dias por semana permitidos</p>
                            <button class="btn-primary">Editar</button>
                        </div>
                        <div class="policy-card">
                            <h4>Código de Vestimenta</h4>
                            <p>Vestuário casual e profissional</p>
                            <button class="btn-primary">Editar</button>
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
        </div>
    </div>
</div>
<script src="../js/theme.js"></script>
</body>
</html>