<?php
session_start();

// Recupera os erros e dados do formulário da sessão, se existirem
$erros = $_SESSION['erros_registro_candidato'] ?? [];
$dadosForm = $_SESSION['dados_form_candidato'] ?? [];

// Limpa as variáveis de sessão
unset($_SESSION['erros_registro_candidato']);
unset($_SESSION['dados_form_candidato']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="" href="sam2-05.png">
    <link rel="stylesheet" href="../all.css/login.css">
    <title>SAM - Cadastro de Candidato</title>
</head>
<style>
    .logo{
        height: 80px;
    }
    
    .form-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .form-row .form-group {
        flex: 1;
        margin-bottom: 0;
    }
    
    .login-card {
        max-width: 600px;
    }
    
    .file-input-container {
        position: relative;
        overflow: hidden;
        display: inline-block;
        width: 100%;
    }
    
    .file-input {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-input-label {
        display: inline-block;
        padding: 0.8rem;
        background-color: #f5f5f5;
        color: #333;
        border: 1px dashed #ddd;
        border-radius: 5px;
        width: 100%;
        text-align: center;
        cursor: pointer;
    }
    
    .file-input-label:hover {
        background-color: #eee;
    }
    
    .alert {
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-size: 0.9rem;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
</style>
<body>
    <header class="header">
        <a href="login.php">
            <img src="../fotos/sam30-13.png" alt="SAM Logo" class="logo">
        </a>
        <div class="nav-container">
            <nav class="nav-menu">
                <div class="dropdown">
                    <a href="#" class="dropbtn">Produtos ▾</a>
                    <div class="dropdown-content">
                        <a href="#">Produto 1</a>
                        <a href="#">Produto 2</a>
                        <a href="#">Produto 3</a>
                    </div>
                </div>
                <div class="dropdown">
                    <a href="#" class="dropbtn">Funcionalidades ▾</a>
                    <div class="dropdown-content">
                        <a href="#">Funcionalidade 1</a>
                        <a href="#">Funcionalidade 2</a>
                        <a href="#">Funcionalidade 3</a>
                    </div>
                </div>
                <a href="#">Preços</a>
            </nav>
        </div>
        <div class="nav-buttons">
            <a href="login.php">
                <button class="btn btn-entrar">Entrar</button>
            </a>
            <a href="registro_empresa.php">
                <button class="btn btn-criar">Cadastrar Empresa</button>
            </a>
        </div>
    </header>
    
    <div class="login-container">
        <div class="login-card" id="registroCandidatoCard">
            <h2 class="login-title">Cadastro de Candidato</h2>
            
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($erros as $erro): ?>
                            <li><?php echo htmlspecialchars($erro); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="processar_registro_candidato.php" id="registroCandidatoForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input 
                        type="text" 
                        id="nome" 
                        name="nome" 
                        placeholder="Seu nome completo" 
                        value="<?php echo htmlspecialchars($dadosForm['nome'] ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="seu.email@exemplo.com" 
                            value="<?php echo htmlspecialchars($dadosForm['email'] ?? ''); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input 
                            type="tel" 
                            id="telefone" 
                            name="telefone" 
                            placeholder="(00) 00000-0000" 
                            value="<?php echo htmlspecialchars($dadosForm['telefone'] ?? ''); ?>"
                        >
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            placeholder="Crie uma senha" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmarSenha">Confirmar Senha</label>
                        <input 
                            type="password" 
                            id="confirmarSenha" 
                            name="confirmarSenha" 
                            placeholder="Confirme sua senha" 
                            required
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input 
                        type="date" 
                        id="data_nascimento" 
                        name="data_nascimento" 
                        value="<?php echo htmlspecialchars($dadosForm['data_nascimento'] ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="cv_anexo">Currículo (PDF)</label>
                    <div class="file-input-container">
                        <label class="file-input-label" id="fileLabel">
                            <?php if (!empty($dadosForm['cv_anexo_nome'])): ?>
                                <?php echo htmlspecialchars($dadosForm['cv_anexo_nome']); ?>
                            <?php else: ?>
                                Clique aqui para anexar seu currículo
                            <?php endif; ?>
                        </label>
                        <input 
                            type="file" 
                            id="cv_anexo" 
                            name="cv_anexo" 
                            accept=".pdf,.doc,.docx" 
                            class="file-input"
                            onchange="updateFileLabel(this)"
                        >
                    </div>
                </div>
                
                <button type="submit" class="btn-continuar">Cadastrar Currículo</button>
                <div class="signup-link">
                    Já possui uma conta? <a href="login.php">Entrar</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function updateFileLabel(input) {
            const label = document.getElementById('fileLabel');
            if (input.files.length > 0) {
                label.textContent = input.files[0].name;
            } else {
                label.textContent = 'Clique aqui para anexar seu currículo';
            }
        }
    </script>
</body>
</html> 