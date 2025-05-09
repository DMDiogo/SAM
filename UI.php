<?php 
// Adicionar logs no início do arquivo
error_log("Debug - UI.php - Sessão ID: " . session_id());
error_log("Debug - UI.php - Verificando sessão antes de include protect.php");
if(isset($_SESSION['id_adm'])) {
    error_log("Debug - UI.php - id_adm: " . $_SESSION['id_adm']);
} else {
    error_log("Debug - UI.php - id_adm não está definido na sessão");
}

include 'protect.php';
include 'config.php';

// Mais logs após os includes
error_log("Debug - UI.php - Após include protect.php");
error_log("Debug - UI.php - id_adm: " . (isset($_SESSION['id_adm']) ? $_SESSION['id_adm'] : 'Não definido'));
error_log("Debug - UI.php - id_empresa: " . (isset($_SESSION['id_empresa']) ? $_SESSION['id_empresa'] : 'Não definido'));

$intervalo = 7;

// Obter o ID da empresa da sessão
if (!isset($_SESSION['id_empresa'])) {
    die("ID da empresa não está definido na sessão.");
}
$id_empresa = $_SESSION['id_empresa'];  

$sql_novos_funcionarios = "
    SELECT id_fun, nome, departamento, foto 
    FROM funcionario 
    WHERE data_admissao >= NOW() - INTERVAL ? DAY
    AND empresa_id = ?
    ORDER BY data_admissao DESC
    LIMIT 4";

$stmt_novos_funcionarios = $conn->prepare($sql_novos_funcionarios);
if (!$stmt_novos_funcionarios) {
    die("Erro na preparação da consulta: " . $conn->error);
}
$stmt_novos_funcionarios->bind_param("ii", $intervalo, $id_empresa);
$stmt_novos_funcionarios->execute();
$result_novos_funcionarios = $stmt_novos_funcionarios->get_result();

$novos_funcionarios = [];
while ($row = $result_novos_funcionarios->fetch_assoc()) {
    $novos_funcionarios[] = $row;
}

$stmt_novos_funcionarios->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="UI.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <script src="../js/theme.js"></script>
    <title>Dashboard RH</title>
</head>
<style>

    
    .exit-tag {
    background-color: #FF6B6B;
    padding: 8px 16px;
    border-radius: 20px;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
}

.employee-item {
        cursor: pointer;
    }

 .add-button1{
    list-style: none;
}

.header-buttons {
    display: flex;           
    align-items: center;
    justify-content: space-between;
    gap: 15px; 
    height: 50px;
    background: white;
    padding: 8px 15px; 
    border-radius: 25px;
    width: 32%;
}

.btn-enter {
    background-color: hwb(158 24% 29%);
    margin-left: 0;
}

.time-container {
    min-width: 120px;
    text-align: center;
    font-family: 'Poppins', sans-serif;
}



#current-time {
    display: inline-flex; 
    align-items: center;
    justify-content: center;
    height: 34px; 
    padding: 0 16px;
    border-radius: 20px;
    font-size: 15px;
    font-weight: 500;
    background: white;
    color: #000;
    white-space: nowrap;
}

/* Dark Mode Styles */
body.dark {
    background-color:#1E1E1E;
    color: #ffffff;

}

body.dark .card {
    background-color:rgb(26, 26, 26);
    box-shadow: 0 2px 4px rgba(255, 255, 255, 0.1);
}


body.dark .sidebar {
    background-color: #121212;
}

body.dark .header-buttons {
    background-color: #2C2C2C;
}

body.dark #current-time {
    background-color: #2C2C2C;
    color: #ffffff;
}

body.dark .welcome-card {
    background-color: #3EB489;
}

body.dark .employee-item {
    background-color: #2C2C2C;
}

body.dark .employee-name {
    color: #ffffff;
}

body.dark .employee-sector {
    color: #aaaaaa;
}

body.dark .status-label {
    color: #aaaaaa;
}

body.dark .birthday-name {
    color: #aaaaaa;
}


</style>
<body>
    <div class="sidebar">
        <div class="logo">
        </div>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div></div>
            <div class="header-buttons">
    <div class="time-container">
        <div class="time" id="current-time"></div>
    </div>
    <a href="registro.php">
        <button class="btn btn-enter">Gerenciar</button>
    </a>
    <a class="exit-tag" href="logout.php">Sair</a>
    <a href="configuracoes_sam/perfil_adm.php" class="settings-icon" style="margin-top: 7.6px; margin-left:-7px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3EB489" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
    </a>
</div>
        </div>

        <div class="dashboard-grid">
            <div>
                <div class="welcome-card">
                    <div class="welcome-title">Gestor</div>
                    <div class="welcome-text">Olá, <span><?php echo $_SESSION['nome']; ?></span>. Bem-vind@ de volta</div>
                </div>

                <div class="card status-section">
                    <div class="section-header">
                        <img src="icones/icons-sam-17.svg" alt="" class="who-is-icon">
                        <h2 class="status-title">Quem está...</h2>
                    </div>
                    
                    <div class="status-group">
                        <div class="status-label">A trabalhar</div>
                        <div class="avatar-group">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <div class="avatar">+63</div>
                        </div>
                    </div>

                    <div class="status-group">
                        <div class="status-label">Descanso</div>
                        <div class="avatar-group">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">

                        </div>
                    </div>

                    <div class="status-group">
                        <div class="status-label">Férias</div>
                        <div class="avatar-group">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                        </div>

                    </div>

                    <div class="status-group">
                        <div class="status-label">Faltas</div>
                        <div class="avatar-group">
                            <div class="avatar">N/D</div>
                        </div>
                    </div>

                    <div class="status-group">
                        <div class="status-label">Todos</div>
                        <div class="avatar-group">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-19.svg" alt="" class="avatar">
                            <img src="icones/icons-sam-18.svg" alt="" class="avatar">
                        </div>
                        
                    </div>

                </div>
            </div>

            <div>
                <div class="card">
                    <div class="section-header">
                        <img src="icones/icons-sam-15.svg" alt="" class="section-icon">
                        <h2>Próximos aniversários</h2>
                    </div>

                    <div class="birthdays-container">
                        <div class="birthday-item">
                            <div class="birthday-date">21, Maio</div>
                            <img src="icones/icons-sam-18.svg" alt="" class="birthday-avatar" >
                            <div class="birthday-name">Josilde Costa</div>
                        </div>

                        <div class="birthday-item">
                            <div class="birthday-date">15, Setembro</div>
                            <img src="icones/icons-sam-18.svg" alt="" class="birthday-avatar">
                            <div class="birthday-name">Kelson Mota</div>
                        </div>

                        <div class="birthday-item">
                            <div class="birthday-date">05, Dezembro</div>
                            <img src="icones/icons-sam-18.svg" alt="" class="birthday-avatar">
                            <div class="birthday-name">Kimi Carvalho</div>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <div class="section-header">
                        <img src="icones/icons-sam-16.svg" alt="" class="section-icon">
                        <h2>Próximos Feriados</h2>
                    </div>

                    <div class="holiday-item">
                        <div class="holiday-date">04/02</div>
                        <div class="holiday1">Dia do Início da Luta Armada de Libertação Nacional</div>
                    </div>

                    <div class="holiday-item">
                        <div class="holiday-date">04/03</div>
                        <div class="holiday1">Carnaval</div>
                    </div>

                    <div class="holiday-item">
                        <div class="holiday-date">17/10</div>
                        <div class="holiday1">Fundador da Nação e Dia dos Heróis Nacionais</div>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px; min-height: 22.8%;">
                </div>
            </div>

            <div>
                <div class="card calendar">
                    <div class="calendar-header">
                        <span id="prevMonth">&lt;</span>
                        <span id="currentMonth">Fevereiro 2025</span>
                        <span id="nextMonth">&gt;</span>
                    </div>

                    <div class="calendar-grid calendar-weekdays">
                        <div>S</div>
                        <div>T</div>
                        <div>Q</div>
                        <div>Q</div>
                        <div>S</div>
                        <div>S</div>
                        <div>D</div>
                    </div>

                    <div class="calendar-grid" id="calendar-days">
                    </div>
                </div>

                <div class="card new-employees">
                    <div class="new-employees-header">
                        <div class="new-employees-header1">Novos Empregados</div>
                        <div class="add-button"><a href="registro.php" class="add-button1">+ Adicionar</a></div>
                    </div>

                    <?php if (!empty($novos_funcionarios)): ?>
                        <?php foreach ($novos_funcionarios as $funcionario): ?>
                            <div class="employee-item" onclick="window.location.href='detalhes_funcionario.php?id=<?php echo $funcionario['id_fun']; ?>'">
                                <div class="employee-avatar">
                                    <?php
                                    // Verifica se a foto existe
                                    $foto = !empty($funcionario['foto']) && file_exists($funcionario['foto']) ? $funcionario['foto'] : 'icones/icons-sam-18.svg';
                                    ?>
                                    <img src="<?php echo $foto; ?>" alt="Foto de <?php echo htmlspecialchars($funcionario['nome']); ?>">
                                </div>
                                <div class="employee-info">
                                    <div class="employee-name"><?php echo htmlspecialchars($funcionario['nome']); ?></div>
                                    <div class="employee-sector"><?php echo htmlspecialchars($funcionario['departamento']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="employee-item">
                            <div class="employee-info">
                                <div class="employee-name">Nenhum novo funcionário admitido recentemente.</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
        }
        updateTime();

        setInterval(updateTime, 1000);
    </script>
    <script src="./js/UI.js"></script>
    <script src="./js/theme.js"></script>
</body>
</html>