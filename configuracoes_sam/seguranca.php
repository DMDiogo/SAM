<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Segurança - Dashboard RH</title>
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
        }

        .profile-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .security-section {
            background-color: var(--background-light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .security-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .detail-item label {
            font-weight: 500;
            color: var(--text-color);
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

        .active-sessions {
            background-color: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .session-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .session-item:last-child {
            border-bottom: none;
        }

        .session-item strong {
            color: var(--text-color);
            display: block;
            margin-bottom: 5px;
        }

        .session-item p {
            color: #6c757d;
            font-size: 0.9em;
            margin: 0;
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

        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background-color: var(--white);
            transition: border-color 0.3s ease;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(62, 180, 137, 0.2);
        }

        body.dark {
    background-color: #121212;
    color: #e0e0e0;
}

body.dark .dashboard-container {
    background-color: #1e1e1e;
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
}

body.dark .security-section {
    background-color: #1a1a1a;
}

body.dark .security-section h3 {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
}

body.dark .detail-item label {
    color: #c0c0c0;
}

body.dark .active-sessions {
    background-color: #1f1f1f;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

body.dark .session-item {
    border-bottom: 1px solid #333;
}

body.dark .session-item strong {
    color: #e0e0e0;
}

body.dark .session-item p {
    color: #888;
}

body.dark .toggle-switch .slider {
    background-color: #555;
}

body.dark .toggle-switch .slider:before {
    background-color: #aaa;
}

body.dark .toggle-switch input:checked + .slider {
    background-color: var(--primary-color);
}

body.dark input[type="password"] {
    background-color: #333;
    border-color: #444;
    color: #e0e0e0;
}

body.dark input[type="password"]:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(62, 180, 137, 0.3);
}

body.dark .btn-primary {
    background-color: var(--primary-color);
    color: #f4f4f4;
    transition: background-color 0.3s ease;
}

body.dark .btn-primary:hover {
    background-color: #3EB489;
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
    transition: all 0.3s ease;
}

body.dark .nav-menu li:hover {
    background-color: rgba(62, 180, 137, 0.2);
    color: var(--primary-color);
}

body.dark .nav-menu li.active {
    background-color: rgba(62, 180, 137, 0.2);
    color: var(--primary-color);
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
            <a href="seguranca.php"><li class="active">Segurança</li></a>
            <a href="privacidade.php"><li>Privacidade</li></a>
            <a href="rh_config.php"><li>Configurações de RH</li></a>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="profile-card">
            <h1>Configurações de Segurança</h1>

            <div class="security-section">
                <h3>Autenticação</h3>
                <div class="detail-item">
                    <label>Autenticação de Dois Fatores</label>
                    <label class="toggle-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="detail-item">
                    <label>Login com Biometria</label>
                    <label class="toggle-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="security-section">
                <h3>Sessões Ativas</h3>
                <div class="active-sessions">
                    <div class="session-item">
                        <div>
                            <strong>Navegador Chrome</strong>
                            <p>Computador - Windows 10 | IP: 192.168.1.100</p>
                        </div>
                        <button class="btn-primary">Encerrar Sessão</button>
                    </div>
                    <div class="session-item">
                        <div>
                            <strong>Aplicativo Mobile</strong>
                            <p>Android | IP: 10.0.0.200</p>
                        </div>
                        <button class="btn-primary">Encerrar Sessão</button>
                    </div>
                </div>
            </div>

            <div class="security-section">
                <h3>Log de Atividades</h3>
                <div class="active-sessions">
                    <div class="session-item">
                        <div>
                            <strong>Alteração de Senha</strong>
                            <p>25/03/2024 14:45 | IP: 192.168.1.100</p>
                        </div>
                    </div>
                    <div class="session-item">
                        <div>
                            <strong>Login Efetuado</strong>
                            <p>25/03/2024 14:30 | IP: 192.168.1.100</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="security-section">
                <h3>Redefinição de Senha</h3>
                <div class="detail-item">
                    <label>Nova Senha</label>
                    <input type="password" placeholder="Digite sua nova senha">
                </div>
                <div class="detail-item">
                    <label>Confirmar Nova Senha</label>
                    <input type="password" placeholder="Confirme sua nova senha">
                </div>
                <button class="btn-primary" style="margin-top: 10px;">Redefinir Senha</button>
            </div>
        </div>
    </div>
</div>
<script src="../js/theme.js"></script>
</body>
</html> 