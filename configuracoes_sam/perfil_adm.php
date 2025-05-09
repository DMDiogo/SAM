<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Usuário - Dashboard RH</title>
    <link rel="stylesheet" href="../all.css/registro3.css">
    <link rel="stylesheet" href="../all.css/configuracoes.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3EB489;
            --background-light: #f4f4f4;
            --text-color: #333;
            --white: #ffffff;
            --input-border: #e0e0e0;
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

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--background-light);

        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 30px;
            border: 4px solid var(--primary-color);
            background-color:  #3EB489
        }

        .profile-info h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .profile-info p {
            color: #6c757d;
            margin-bottom: 5px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: #32a177;
        }

        .profile-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .detail-section {
            background-color: var(--background-light);
            border-radius: 10px;
            padding: 20px;
        }

        .detail-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .detail-item {
            margin-bottom: 15px;
        }

        .detail-item label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-color);
        }

        .detail-item input,
        .detail-item select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--input-border);
            border-radius: 6px;
            background-color: var(--white);
            transition: border-color 0.3s ease;
        }

        .detail-item input:focus,
        .detail-item select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(62, 180, 137, 0.2);
        }

        .detail-item input:disabled {
            background-color: #f9f9f9;
            color: #6c757d;
            cursor: not-allowed;
        }

        .profile-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
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

        body.dark .profile-header {
            border-bottom: 2px solid #3a3a3a;
        }

        body.dark .profile-picture {
            border-color: var(--primary-color);
        }

        body.dark .profile-info h1 {
            color: var(--primary-color);
        }

        body.dark .profile-info p {
            color: #a0a0a0;
        }

        body.dark .profile-details {
            color: #e0e0e0;
        }

        body.dark .detail-section {
            background-color: #1a1a1a;
        }

        body.dark .detail-section h3 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        body.dark .detail-item label {
            color: #c0c0c0;
        }

        body.dark .detail-item input,
        body.dark .detail-item select {
            background-color: #333;
            border-color: #444;
            color: #e0e0e0;
        }

        body.dark .detail-item input:focus,
        body.dark .detail-item select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(62, 180, 137, 0.3);
        }

        body.dark .detail-item input:disabled {
            background-color: #2a2a2a;
            color: #777;
            cursor: not-allowed;
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
                <a href="perfil_adm.php"><li class="active">Perfil do Usuário</li></a>
                <a href="seguranca.php"><li>Segurança</li></a>
                <a href="privacidade.php"><li>Privacidade</li></a>
                <a href="rh_config.php"><li>Configurações de RH</li></a>
            </ul>
        </div>

        <div class="main-content">
            <div class="profile-card">
                <div class="profile-header">
                    <img src="../icones/icons-sam-18.svg" alt="Foto de Perfil" class="profile-picture">
                    <div class="profile-info">
                        <h1>Nome do Usuário</h1>
                        <p>Cargo: Administrador de RH</p>
                        <p>Departamento: Recursos Humanos</p>
                    </div>
                </div>

                <div class="profile-details">
                    <div class="detail-section">
                        <h3>Informações Pessoais</h3>
                        <div class="detail-item">
                            <label>Nome Completo</label>
                            <input type="text" value="João Silva">
                        </div>
                        <div class="detail-item">
                            <label>Email</label>
                            <input type="email" value="joao.silva@empresa.com">
                        </div>
                        <div class="detail-item">
                            <label>Telefone</label>
                            <input type="tel" value="(11) 99999-9999">
                        </div>
                        <div class="detail-item">
                            <label>Data de Nascimento</label>
                            <input type="date">
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Informações Profissionais</h3>
                        <div class="detail-item">
                            <label>Matrícula</label>
                            <input type="text" value="RH-2024-001">
                        </div>
                        <div class="detail-item">
                            <label>Data de Admissão</label>
                            <input type="date">
                        </div>
                        <div class="detail-item">
                            <label>Nível de Acesso</label>
                            <select>
                                <option>Administrador</option>
                                <option>Usuário Padrão</option>
                                <option>Gerente</option>
                            </select>
                        </div>
                        <div class="detail-item">
                            <label>Último Login</label>
                            <input type="text" value="25/03/2024 14:30" disabled>
                        </div>
                    </div>
                </div>

                <div class="profile-actions">
                    <button class="btn-primary">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>
        <script src="../js/theme.js"></script>
</body>
</html>