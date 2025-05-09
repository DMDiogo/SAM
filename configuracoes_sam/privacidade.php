<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Privacidade - Dashboard RH</title>
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

        .privacy-section {
            background-color: var(--background-light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .privacy-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .privacy-details {
            background-color: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 54px;
            height: 28px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .toggle-switch input:checked + .slider {
            background-color: var(--primary-color);
        }

        .toggle-switch input:checked + .slider:before {
            transform: translateX(26px);
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

        body.dark .privacy-section {
            background-color: #1a1a1a;
        }

        body.dark .privacy-section h3 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        body.dark .privacy-details {
            background-color: #1f1f1f;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        body.dark .detail-item {
            border-bottom: 1px solid #333;
        }

        body.dark .toggle-switch .slider {
            background-color: #555;
        }

        body.dark .toggle-switch .slider:before {
            background-color: #aaa;
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
            <a href="privacidade.php"><li class="active">Privacidade</li></a>
            <a href="rh_config.php"><li>Configurações de RH</li></a>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="profile-card">
            <h1>Configurações de Privacidade</h1>

            <div class="privacy-section">
                <h3>Compartilhamento de Dados</h3>
                <div class="privacy-details">
                    <div class="detail-item">
                        <label>Compartilhar dados com terceiros</label>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="detail-item">
                        <label>Permitir análise de dados para melhorias do sistema</label>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="privacy-section">
                <h3>Preferências de Comunicação</h3>
                <div class="privacy-details">
                    <div class="detail-item">
                        <label>Receber comunicações por email</label>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="detail-item">
                        <label>Receber notificações push</label>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="detail-item">
                        <label>Receber atualizações de marketing</label>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="privacy-section">
                <h3>Consentimento de Dados</h3>
                <div class="privacy-details">
                    <div class="detail-item" style="border-bottom: none;">
                        <div>
                            <strong>Última atualização do consentimento</strong>
                            <p>25/03/2024</p>
                        </div>
                        <button class="btn-primary">Revisar Consentimento</button>
                    </div>
                </div>
            </div>

            <div class="privacy-section">
                <h3>Exportação de Dados Pessoais</h3>
                <div class="privacy-details">
                    <div class="detail-item" style="border-bottom: none;">
                        <div>
                            <strong>Exportar Dados Pessoais</strong>
                            <p>Você pode exportar todos os seus dados pessoais em um formato legível.</p>
                        </div>
                        <button class="btn-primary">Exportar Dados</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/theme.js"></script>
</body>
</html>