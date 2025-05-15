<?php
session_start();
require_once 'config/database.php';

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmarSenha'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    
    // Validação básica dos dados
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = "O nome completo é obrigatório";
    }
    
    if (empty($email)) {
        $erros[] = "O email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O email fornecido não é válido";
    }
    
    if (empty($senha)) {
        $erros[] = "A senha é obrigatória";
    } elseif (strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres";
    }
    
    if ($senha !== $confirmarSenha) {
        $erros[] = "As senhas não coincidem";
    }
    
    if (empty($data_nascimento)) {
        $erros[] = "A data de nascimento é obrigatória";
    }
    
    // Verifica se já existe um email cadastrado
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidatos WHERE email = ?");
        $stmt->execute([$email]);
        $contagem = $stmt->fetchColumn();
        
        if ($contagem > 0) {
            $erros[] = "Este email já está cadastrado";
        }
    } catch (PDOException $e) {
        $erros[] = "Erro ao verificar email: " . $e->getMessage();
    }
    
    // Processa o upload do currículo, se fornecido
    $cv_anexo = null;
    $cv_anexo_nome = null;
    
    if (isset($_FILES['cv_anexo']) && $_FILES['cv_anexo']['error'] == 0) {
        $arquivo = $_FILES['cv_anexo'];
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $extensoes_permitidas = ['pdf', 'doc', 'docx'];
        
        if (!in_array(strtolower($extensao), $extensoes_permitidas)) {
            $erros[] = "Formato de arquivo não permitido. Use PDF, DOC ou DOCX.";
        } elseif ($arquivo['size'] > 5242880) { // 5MB
            $erros[] = "O arquivo é muito grande. Tamanho máximo: 5MB.";
        } else {
            // Cria o diretório para currículos se não existir
            $uploadDir = 'uploads/curriculos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Gera um nome único para o arquivo
            $novoNome = uniqid() . '_' . $arquivo['name'];
            $caminho = $uploadDir . $novoNome;
            
            // Move o arquivo para o diretório de destino
            if (!move_uploaded_file($arquivo['tmp_name'], $caminho)) {
                $erros[] = "Falha ao fazer upload do currículo.";
            } else {
                $cv_anexo = $caminho;
                $cv_anexo_nome = $arquivo['name'];
            }
        }
    }
    
    // Se não houver erros, insere os dados no banco
    if (empty($erros)) {
        try {
            // Criptografa a senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inicia a transação
            $pdo->beginTransaction();
            
            // Insere o candidato
            $stmt = $pdo->prepare("
                INSERT INTO candidatos (nome, email, senha, telefone, data_nascimento, cv_anexo, data_cadastro)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$nome, $email, $senhaHash, $telefone, $data_nascimento, $cv_anexo]);
            $candidatoId = $pdo->lastInsertId();
            
            $pdo->commit();
            
            // Redireciona para página de sucesso ou login
            $_SESSION['mensagem_sucesso'] = "Cadastro realizado com sucesso! Agora você pode fazer login.";
            header('Location: login.php');
            exit;
            
        } catch (PDOException $e) {
            // Em caso de erro, desfaz as alterações
            $pdo->rollBack();
            
            // Remove o arquivo de currículo se foi feito upload
            if ($cv_anexo && file_exists($cv_anexo)) {
                unlink($cv_anexo);
            }
            
            $erros[] = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
    
    // Se houver erros, armazena na sessão para exibir na página de registro
    if (!empty($erros)) {
        $_SESSION['erros_registro_candidato'] = $erros;
        $_SESSION['dados_form_candidato'] = [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'data_nascimento' => $data_nascimento,
            'cv_anexo_nome' => $cv_anexo_nome
        ];
        header('Location: registro_candidato.php');
        exit;
    }
}

// Se o método não for POST, redireciona para a página de registro
header('Location: registro_candidato.php');
exit; 