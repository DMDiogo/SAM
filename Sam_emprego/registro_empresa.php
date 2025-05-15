<?php
session_start();

// Recupera os erros e dados do formulário da sessão, se existirem
$erros = $_SESSION['erros_registro'] ?? [];
$dadosForm = $_SESSION['dados_form'] ?? [];

// Limpa as variáveis de sessão
unset($_SESSION['erros_registro']);
unset($_SESSION['dados_form']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="" href="sam2-05.png">
    <link rel="stylesheet" href="../all.css/login.css">
    <title>SAM - Cadastro de Empresa</title>
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
            <a href="registro_candidato.php">
                <button class="btn btn-criar">Cadastrar Currículo</button>
            </a>
        </div>
    </header>
    
    <div class="login-container">
        <div class="login-card" id="registroEmpresaCard">
            <h2 class="login-title">Cadastro de Empresa</h2>
            
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($erros as $erro): ?>
                            <li><?php echo htmlspecialchars($erro); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="processar_registro_empresa.php" id="registroEmpresaForm">
                <div class="form-group">
                    <label for="nome">Nome da Empresa</label>
                    <input 
                        type="text" 
                        id="nome" 
                        name="nome" 
                        placeholder="Nome da sua empresa" 
                        value="<?php echo htmlspecialchars($dadosForm['nome'] ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea 
                        id="descricao" 
                        name="descricao" 
                        placeholder="Descreva sua empresa brevemente" 
                        style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; min-height: 100px;"
                    ><?php echo htmlspecialchars($dadosForm['descricao'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Corporativo</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="email@suaempresa.com" 
                        value="<?php echo htmlspecialchars($dadosForm['email'] ?? ''); ?>"
                        required
                    >
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
                    <label for="site">Site da Empresa</label>
                    <input 
                        type="url" 
                        id="site" 
                        name="site" 
                        placeholder="https://www.suaempresa.com" 
                        value="<?php echo htmlspecialchars($dadosForm['site'] ?? ''); ?>"
                    >
                </div>
                
                <button type="submit" class="btn-continuar">Cadastrar Empresa</button>
                <div class="signup-link">
                    Já possui uma conta? <a href="login.php">Entrar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 