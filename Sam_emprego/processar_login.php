<?php
session_start();
require_once 'config/database.php';

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados do formulário
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'empresa';
    
    // Validação básica
    if (empty($email) || empty($senha)) {
        $_SESSION['erro_login'] = "Por favor, preencha todos os campos.";
        header('Location: login.php');
        exit;
    }
    
    try {
        if ($tipo_usuario === 'empresa') {
            // Login como empresa
            $stmt = $pdo->prepare("SELECT * FROM empresas_recrutamento WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                $_SESSION['empresa_id'] = $usuario['id'];
                $_SESSION['empresa_nome'] = $usuario['nome'];
                $_SESSION['tipo_usuario'] = 'empresa';
                
                // Redireciona para o painel da empresa
                header('Location: painel_empresa.php');
                exit;
            } else {
                // Login inválido
                $_SESSION['erro_login'] = "Email ou senha incorretos.";
                header('Location: login.php');
                exit;
            }
        } else {
            // Login como candidato
            $stmt = $pdo->prepare("SELECT * FROM candidatos WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                $_SESSION['candidato_id'] = $usuario['id'];
                $_SESSION['candidato_nome'] = $usuario['nome'];
                $_SESSION['tipo_usuario'] = 'candidato';
                
                // Cria um painel de candidato temporário se não existir
                if (!file_exists('painel_candidato.php')) {
                    file_put_contents('painel_candidato.php', '<?php
                    session_start();
                    if (!isset($_SESSION["candidato_id"])) {
                        header("Location: login.php");
                        exit;
                    }
                    ?>
                    <!DOCTYPE html>
                    <html lang="pt-BR">
                    <head>
                        <meta charset="UTF-8">
                        <title>Painel do Candidato</title>
                        <link rel="stylesheet" href="../all.css/login.css">
                    </head>
                    <body>
                        <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION["candidato_nome"]); ?>!</h1>
                        <p>Painel temporário - Esta página está em construção.</p>
                        <a href="logout.php">Sair</a>
                    </body>
                    </html>');
                }
                
                // Redireciona para o painel do candidato
                header('Location: painel_candidato.php');
                exit;
            } else {
                // Login inválido
                $_SESSION['erro_login'] = "Email ou senha incorretos.";
                header('Location: login.php');
                exit;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['erro_login'] = "Erro ao fazer login: " . $e->getMessage();
        header('Location: login.php');
        exit;
    }
}

// Se o método não for POST, redireciona para a página de login
header('Location: login.php');
exit; 