<?php
session_start();

// Verificar se já existe uma mensagem de sucesso
$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? '';
unset($_SESSION['mensagem_sucesso']);

// Verificar se há mensagem de erro do login
$erroLogin = $_SESSION['erro_login'] ?? '';
unset($_SESSION['erro_login']);

// Redirecionar se já estiver logado
if (isset($_SESSION['empresa_id'])) {
    header('Location: painel_empresa.php');
    exit;
} else if (isset($_SESSION['candidato_id'])) {
    header('Location: painel_candidato.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="" href="sam2-05.png"style>
    <link rel="stylesheet" href="../all.css/login.css">
    <title>SAM - Login</title>
</head>
<style>
    .logo{
        height: 80px;
    }
    .forgot-password {
        text-align: right;
        margin-top: 0.5rem;
        font-size: 0.85rem;
    }

    .forgot-password a {
        color: #3EB489;
        text-decoration: none;
    }

    .forgot-password a:hover {
        text-decoration: underline;
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
    
    .register-options {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .register-options a {
        flex: 1;
    }
    
    .register-options button {
        width: 100%;
        padding: 0.6rem;
        border-radius: 5px;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-empresa {
        background-color: #3EB489;
        color: white;
    }
    
    .btn-candidato {
        background-color: #007bff;
        color: white;
    }
    
    .btn-empresa:hover {
        background-color: #36a078;
    }
    
    .btn-candidato:hover {
        background-color: #0069d9;
    }
</style>
<body>
    <header class="header">
        <a href="emprego_homepage.html">
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
            <div class="dropdown">
                <button class="btn btn-criar">Cadastrar-se ▾</button>
                <div class="dropdown-content">
                    <a href="registro_empresa.php">Empresa</a>
                    <a href="registro_candidato.php">Candidato</a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="login-container">
        <!-- Formulário de login (mostrado por padrão) -->
        <div class="login-card" id="loginCard">
            <h2 class="login-title">Entrar</h2>
            
            <?php if (!empty($mensagemSucesso)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($mensagemSucesso); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($erroLogin)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($erroLogin); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="processar_login.php" id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="samrh@exemplo.com" 
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        placeholder="Digite sua senha" 
                        required
                    >
                    <div class="forgot-password">
                        <a href="recuperar_senha.php">Esqueceu a senha?</a>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tipo_usuario">Entrar como:</label>
                    <select name="tipo_usuario" id="tipo_usuario" class="form-control" required>
                        <option value="empresa">Empresa</option>
                        <option value="candidato">Candidato</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-continuar">Continuar</button>
                <div class="signup-link">
                    Ainda não tenho conta.
                </div>
                
                <div class="register-options">
                    <a href="registro_empresa.php">
                        <button type="button" class="btn-empresa">Empresa</button>
                    </a>
                    <a href="registro_candidato.php">
                        <button type="button" class="btn-candidato">Candidato</button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 