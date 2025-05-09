<?php
// Inicia a sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'protect.php'; // Protege a página para usuários autenticados
include 'config.php'; // Conexão com o banco de dados

// Verifica se o usuário está logado e tem um ID válido
if (!isset($_SESSION['id_adm'])) {
    echo "Erro: Usuário não autenticado.";
    exit;
}

// Verifica se o administrador está associado a uma empresa
if (!isset($_SESSION['id_empresa'])) {
    echo "<script>alert('Você precisa criar uma empresa antes de acessar esta página.'); window.location.href='Registro_adm.php';</script>";
    exit;
}

$empresa_id = $_SESSION['id_empresa']; // Recupera o id_empresa da sessão

// Verificar se a tabela registros_ponto existe, senão criar
$sql_check_table = "SHOW TABLES LIKE 'registros_ponto'";
$result_check_table = mysqli_query($conn, $sql_check_table);

if (mysqli_num_rows($result_check_table) == 0) {
    // Tabela não existe, vamos criar
    $sql_create_table = "CREATE TABLE registros_ponto (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empresa_id INT NOT NULL,
        funcionario_id INT NOT NULL,
        data DATE NOT NULL,
        entrada DATETIME NULL,
        saida DATETIME NULL,
        observacao TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (funcionario_id) REFERENCES funcionario(id_fun),
        INDEX (empresa_id),
        INDEX (data),
        UNIQUE KEY (funcionario_id, data)
    )";
    
    if (!mysqli_query($conn, $sql_create_table)) {
        // Se houver um erro na criação, continuamos com o código mesmo assim
        // Apenas registramos o erro
        error_log("Erro ao criar tabela registros_ponto: " . mysqli_error($conn));
    }
    
    // Inserir alguns registros de exemplo para teste (opcional)
    // Vamos buscar alguns funcionários para criar registros de exemplo
    $sql_funcionarios = "SELECT id_fun FROM funcionario WHERE empresa_id = $empresa_id LIMIT 5";
    $result_funcionarios = mysqli_query($conn, $sql_funcionarios);
    
    if ($result_funcionarios && mysqli_num_rows($result_funcionarios) > 0) {
        // Datas para os registros de exemplo (últimos 7 dias)
        $dias = 7;
        while ($row = mysqli_fetch_assoc($result_funcionarios)) {
            $funcionario_id = $row['id_fun'];
            
            for ($i = 0; $i < $dias; $i++) {
                $data = date('Y-m-d', strtotime("-$i days"));
                
                // Só criar registros para dias de semana (1-5)
                $dia_semana = date('N', strtotime($data));
                if ($dia_semana <= 5) {
                    // Gerar hora de entrada aleatória entre 7:45 e 9:00
                    $hora_entrada = rand(7, 8);
                    $minuto_entrada = rand(0, 59);
                    if ($hora_entrada == 7) {
                        $minuto_entrada = rand(45, 59);
                    } elseif ($hora_entrada == 9) {
                        $minuto_entrada = 0;
                    }
                    
                    // Formatar entrada e saída
                    $entrada = $data . ' ' . sprintf('%02d:%02d:00', $hora_entrada, $minuto_entrada);
                    
                    // Gerar hora de saída aleatória entre 17:00 e 18:30
                    $hora_saida = rand(17, 18);
                    $minuto_saida = rand(0, 59);
                    if ($hora_saida == 18) {
                        $minuto_saida = rand(0, 30);
                    }
                    
                    $saida = $data . ' ' . sprintf('%02d:%02d:00', $hora_saida, $minuto_saida);
                    
                    // 10% de chance do funcionário não ter registro no dia (ausente)
                    $presente = (rand(1, 10) > 1);
                    
                    if ($presente) {
                        $sql_insert = "INSERT IGNORE INTO registros_ponto (empresa_id, funcionario_id, data, entrada, saida) 
                                       VALUES ($empresa_id, $funcionario_id, '$data', '$entrada', '$saida')";
                        mysqli_query($conn, $sql_insert);
                    }
                }
            }
        }
    }
}

// Processamento do formulário de registro de ponto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_ponto'])) {
    $funcionario_id = mysqli_real_escape_string($conn, $_POST['funcionario_id']);
    $data = mysqli_real_escape_string($conn, $_POST['data']);
    $tipo_registro = mysqli_real_escape_string($conn, $_POST['tipo_registro']);
    $hora = mysqli_real_escape_string($conn, $_POST['hora']);
    $observacao = mysqli_real_escape_string($conn, $_POST['observacao']);
    
    // Determinar o status do registro (presente, ausente, atrasado)
    $status = 'presente';
    if ($tipo_registro === 'entrada' && strtotime($hora) > strtotime('08:30:00')) {
        $status = 'atrasado';
    }
    
    // Verificar se já existe um registro para este funcionário nesta data
    $sql_check = "SELECT * FROM registro_ponto WHERE funcionario_id = '$funcionario_id' AND data_registro = '$data'";
    $result_check = mysqli_query($conn, $sql_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        // Já existe um registro, atualizar
        $registro = mysqli_fetch_assoc($result_check);
        
        if ($tipo_registro === 'entrada') {
            $sql_update = "UPDATE registro_ponto SET 
                          hora_entrada = '$hora', 
                          tipo_registro = '$tipo_registro', 
                          status = '$status', 
                          observacao = '$observacao' 
                          WHERE id_registro = '{$registro['id_registro']}'";
        } else {
            $sql_update = "UPDATE registro_ponto SET 
                          hora_saida = '$hora', 
                          tipo_registro = '$tipo_registro', 
                          observacao = '$observacao' 
                          WHERE id_registro = '{$registro['id_registro']}'";
        }
        
        if (mysqli_query($conn, $sql_update)) {
            echo "<script>alert('Registro de ponto atualizado com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao atualizar registro: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        // Não existe registro, criar novo
        if ($tipo_registro === 'entrada') {
            $sql_insert = "INSERT INTO registro_ponto (
                          funcionario_id, data_registro, hora_entrada, 
                          tipo_registro, status, observacao) 
                          VALUES (
                          '$funcionario_id', '$data', '$hora', 
                          '$tipo_registro', '$status', '$observacao')";
        } else {
            $sql_insert = "INSERT INTO registro_ponto (
                          funcionario_id, data_registro, hora_saida, 
                          tipo_registro, status, observacao) 
                          VALUES (
                          '$funcionario_id', '$data', '$hora', 
                          '$tipo_registro', 'presente', '$observacao')";
        }
        
        if (mysqli_query($conn, $sql_insert)) {
            echo "<script>alert('Registro de ponto criado com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao criar registro: " . mysqli_error($conn) . "');</script>";
        }
    }
    
    // Redirecionar para a mesma página para atualizar os dados
    echo "<script>window.location.href='registro_ponto.php';</script>";
    exit;
}

// Processamento do formulário de exportação de relatório
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exportar_relatorio'])) {
    $periodo = mysqli_real_escape_string($conn, $_POST['periodo_relatorio']);
    $formato = mysqli_real_escape_string($conn, $_POST['formato']);
    
    // Definir o período de datas para o relatório
    $data_fim = date('Y-m-d');
    $data_inicio = $data_fim;
    
    if ($periodo == 'semana') {
        $data_inicio = date('Y-m-d', strtotime('-7 days'));
    } elseif ($periodo == 'mes') {
        $data_inicio = date('Y-m-d', strtotime('-30 days'));
    } elseif ($periodo == 'trimestre') {
        $data_inicio = date('Y-m-d', strtotime('-90 days'));
    }
    
    // Consulta para buscar os dados para o relatório
    $sql_relatorio = "SELECT 
                       f.nome, f.num_mecanografico, f.departamento, r.data_registro, 
                       r.hora_entrada, r.hora_saida, r.status, r.observacao
                     FROM registro_ponto r
                     JOIN funcionario f ON r.funcionario_id = f.id_fun
                     WHERE f.empresa_id = $empresa_id
                     AND r.data_registro BETWEEN '$data_inicio' AND '$data_fim'
                     ORDER BY r.data_registro DESC, f.nome ASC";
    
    $result_relatorio = mysqli_query($conn, $sql_relatorio);
    
    if ($result_relatorio && mysqli_num_rows($result_relatorio) > 0) {
        // Preparar os dados para exportação
        $dados_relatorio = [];
        $dados_relatorio[] = ['Nome', 'Nº Mecanográfico', 'Departamento', 'Data', 'Entrada', 'Saída', 'Status', 'Observação'];
        
        while ($row = mysqli_fetch_assoc($result_relatorio)) {
            $data_formatada = date('d/m/Y', strtotime($row['data_registro']));
            $hora_entrada = !empty($row['hora_entrada']) ? date('H:i', strtotime($row['hora_entrada'])) : '-';
            $hora_saida = !empty($row['hora_saida']) ? date('H:i', strtotime($row['hora_saida'])) : '-';
            
            $dados_relatorio[] = [
                $row['nome'],
                $row['num_mecanografico'],
                $row['departamento'],
                $data_formatada,
                $hora_entrada,
                $hora_saida,
                ucfirst($row['status']),
                $row['observacao']
            ];
        }
        
        // Nome do arquivo
        $nome_arquivo = 'relatorio_ponto_' . date('Ymd_His');
        
        // Exportar para o formato escolhido
        if ($formato == 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $nome_arquivo . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // BOM para garantir que caracteres especiais sejam exibidos corretamente
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($dados_relatorio as $linha) {
                fputcsv($output, $linha, ';');
            }
            
            fclose($output);
            exit;
        } elseif ($formato == 'excel') {
            // Requisitos: biblioteca PHPExcel ou phpspreadsheet
            echo "<script>alert('Funcionalidade de exportação para Excel em desenvolvimento. Por favor, tente a exportação em CSV.');</script>";
        } elseif ($formato == 'pdf') {
            // Requisitos: biblioteca FPDF ou TCPDF
            echo "<script>alert('Funcionalidade de exportação para PDF em desenvolvimento. Por favor, tente a exportação em CSV.');</script>";
        }
    } else {
        echo "<script>alert('Não foram encontrados dados para o período selecionado.');</script>";
    }
}

// Consulta para obter o total de funcionários
$sql_total_funcionarios = "SELECT COUNT(*) AS total FROM funcionario WHERE empresa_id = $empresa_id AND estado = 'Ativo'";
$result_total_funcionarios = mysqli_query($conn, $sql_total_funcionarios);
$total_funcionarios = mysqli_fetch_assoc($result_total_funcionarios)['total'];

// Obter a data atual no formato YYYY-MM-DD
$data_atual = date('Y-m-d');

// Consulta para obter o total de funcionários presentes hoje na tabela registro_ponto
$sql_presentes = "SELECT COUNT(DISTINCT funcionario_id) AS total FROM registro_ponto 
                 WHERE data_registro = '$data_atual' AND status = 'presente'";
$result_presentes = mysqli_query($conn, $sql_presentes);
$total_presentes = 0;
if ($result_presentes && mysqli_num_rows($result_presentes) > 0) {
    $total_presentes = mysqli_fetch_assoc($result_presentes)['total'];
}

// Consulta para obter o total de ausentes hoje
$sql_ausentes = "SELECT COUNT(DISTINCT funcionario_id) AS total FROM registro_ponto 
                 WHERE data_registro = '$data_atual' AND status = 'ausente'";
$result_ausentes = mysqli_query($conn, $sql_ausentes);
$total_ausentes = 0;
if ($result_ausentes && mysqli_num_rows($result_ausentes) > 0) {
    $total_ausentes = mysqli_fetch_assoc($result_ausentes)['total'];
}

// Se não tiver funcionários registrados como ausentes, calcular a diferença entre total e presentes
if ($total_ausentes == 0) {
    // Consultar todos os funcionários ativos que não têm registro hoje
    $sql_ausentes_calc = "SELECT COUNT(*) AS total FROM funcionario f 
                         WHERE f.empresa_id = $empresa_id 
                         AND f.estado = 'Ativo'
                         AND NOT EXISTS (
                             SELECT 1 FROM registro_ponto r 
                             WHERE r.funcionario_id = f.id_fun 
                             AND r.data_registro = '$data_atual'
                         )";
    $result_ausentes_calc = mysqli_query($conn, $sql_ausentes_calc);
    if ($result_ausentes_calc && mysqli_num_rows($result_ausentes_calc) > 0) {
        $total_ausentes = mysqli_fetch_assoc($result_ausentes_calc)['total'];
    }
}

// Consultar os registros da tabela ausencias para complementar
$sql_ausencias_hoje = "SELECT COUNT(DISTINCT funcionario_id) AS total FROM ausencias 
                      WHERE empresa_id = $empresa_id 
                      AND '$data_atual' BETWEEN data_inicio AND data_fim";
$result_ausencias_hoje = mysqli_query($conn, $sql_ausencias_hoje);
if ($result_ausencias_hoje && mysqli_num_rows($result_ausencias_hoje) > 0) {
    $total_ausencias = mysqli_fetch_assoc($result_ausencias_hoje)['total'];
    // Somar ao total de ausentes, mas evitar duplicação
    $total_ausentes += $total_ausencias;
    // Garantir que não ultrapasse o total de funcionários
    if ($total_ausentes > $total_funcionarios) {
        $total_ausentes = $total_funcionarios;
    }
}

// Cálculo de funcionários atrasados (chegaram após as 8:30)
$sql_atrasados = "SELECT COUNT(DISTINCT funcionario_id) AS total FROM registro_ponto 
                 WHERE data_registro = '$data_atual' 
                 AND status = 'atrasado'";
$result_atrasados = mysqli_query($conn, $sql_atrasados);
$total_atrasados = 0;
if ($result_atrasados && mysqli_num_rows($result_atrasados) > 0) {
    $total_atrasados = mysqli_fetch_assoc($result_atrasados)['total'];
}

// Consulta para obter os registros de ponto mais recentes
$sql_registros_recentes = "SELECT r.*, f.nome, f.foto, f.departamento 
                          FROM registro_ponto r
                          JOIN funcionario f ON r.funcionario_id = f.id_fun
                          WHERE f.empresa_id = $empresa_id
                          ORDER BY r.data_registro DESC, r.hora_entrada DESC
                          LIMIT 6";
$result_registros_recentes = mysqli_query($conn, $sql_registros_recentes);

// Obter dados dos últimos 7 dias para o gráfico de presença
$labels_dias = [];
$dados_presentes = [];
$dados_ausentes = [];
$dados_atrasados = [];

for ($i = 6; $i >= 0; $i--) {
    $data = date('Y-m-d', strtotime("-$i days"));
    $dia_semana = date('D', strtotime($data));
    
    // Traduzir o dia da semana para português
    $dias_semana = [
        'Mon' => 'Seg',
        'Tue' => 'Ter',
        'Wed' => 'Qua',
        'Thu' => 'Qui',
        'Fri' => 'Sex',
        'Sat' => 'Sáb',
        'Sun' => 'Dom'
    ];
    
    $labels_dias[] = $dias_semana[$dia_semana];
    
    // Consultar presentes neste dia
    $sql_dia_presentes = "SELECT COUNT(DISTINCT funcionario_id) AS total FROM registro_ponto 
                         WHERE data_registro = '$data' AND status = 'presente'";
    $result_dia_presentes = mysqli_query($conn, $sql_dia_presentes);
    $presentes_dia = 0;
    if ($result_dia_presentes && mysqli_num_rows($result_dia_presentes) > 0) {
        $presentes_dia = mysqli_fetch_assoc($result_dia_presentes)['total'];
    }
    $dados_presentes[] = $presentes_dia;
    
    // Consultar ausentes neste dia
    $sql_dia_ausentes = "SELECT COUNT(DISTINCT funcionario_id) AS total FROM registro_ponto 
                         WHERE data_registro = '$data' AND status = 'ausente'";
    $result_dia_ausentes = mysqli_query($conn, $sql_dia_ausentes);
    $ausentes_dia = 0;
    if ($result_dia_ausentes && mysqli_num_rows($result_dia_ausentes) > 0) {
        $ausentes_dia = mysqli_fetch_assoc($result_dia_ausentes)['total'];
    }
    
    // Se não tiver funcionários registrados como ausentes, calcular a diferença
    if ($ausentes_dia == 0) {
        // Calcular funcionários que não têm registro neste dia
        $sql_dia_ausentes_calc = "SELECT COUNT(*) AS total FROM funcionario f 
                                 WHERE f.empresa_id = $empresa_id 
                                 AND f.estado = 'Ativo'
                                 AND NOT EXISTS (
                                     SELECT 1 FROM registro_ponto r 
                                     WHERE r.funcionario_id = f.id_fun 
                                     AND r.data_registro = '$data'
                                 )";
        $result_dia_ausentes_calc = mysqli_query($conn, $sql_dia_ausentes_calc);
        if ($result_dia_ausentes_calc && mysqli_num_rows($result_dia_ausentes_calc) > 0) {
            $ausentes_dia = mysqli_fetch_assoc($result_dia_ausentes_calc)['total'];
        }
    }
    
    // Consultar ausências na tabela ausencias para complementar
    $sql_dia_ausencias = "SELECT COUNT(DISTINCT funcionario_id) AS total FROM ausencias 
                         WHERE empresa_id = $empresa_id 
                         AND '$data' BETWEEN data_inicio AND data_fim";
    $result_dia_ausencias = mysqli_query($conn, $sql_dia_ausencias);
    if ($result_dia_ausencias && mysqli_num_rows($result_dia_ausencias) > 0) {
        $ausencias_dia = mysqli_fetch_assoc($result_dia_ausencias)['total'];
        // Somar ao total de ausentes
        $ausentes_dia += $ausencias_dia;
        // Garantir que não ultrapasse o total de funcionários
        if ($ausentes_dia > $total_funcionarios) {
            $ausentes_dia = $total_funcionarios;
        }
    }
    
    $dados_ausentes[] = $ausentes_dia;
    
    // Consultar atrasados neste dia
    $sql_dia_atrasados = "SELECT COUNT(DISTINCT funcionario_id) AS total FROM registro_ponto 
                         WHERE data_registro = '$data' AND status = 'atrasado'";
    $result_dia_atrasados = mysqli_query($conn, $sql_dia_atrasados);
    $atrasados_dia = 0;
    if ($result_dia_atrasados && mysqli_num_rows($result_dia_atrasados) > 0) {
        $atrasados_dia = mysqli_fetch_assoc($result_dia_atrasados)['total'];
    }
    $dados_atrasados[] = $atrasados_dia;
}

// Função para calcular horas trabalhadas
function calcularHorasTrabalhadas($entrada, $saida) {
    if (empty($entrada) || empty($saida)) {
        return 'N/A';
    }
    
    // Verificar o formato da data/hora
    if (strpos($entrada, ' ') !== false) {
        // Formato datetime completo (YYYY-MM-DD HH:MM:SS)
        $entrada_time = strtotime($entrada);
        $saida_time = strtotime($saida);
    } else {
        // Formato apenas hora (HH:MM:SS)
        $entrada_time = strtotime("1970-01-01 " . $entrada);
        $saida_time = strtotime("1970-01-01 " . $saida);
    }
    
    // Se a saída for menor que a entrada, provavelmente é do dia seguinte
    if ($saida_time < $entrada_time) {
        $saida_time += 86400; // Adiciona 24 horas
    }
    
    $diferenca = $saida_time - $entrada_time;
    $horas = floor($diferenca / 3600);
    $minutos = floor(($diferenca % 3600) / 60);
    
    return sprintf('%02d:%02d', $horas, $minutos);
}

// Obter resumo mensal de presença
$inicio_mes = date('Y-m-01');
$fim_mes = date('Y-m-t');
$dias_uteis_mes = 0;

// Calcular dias úteis no mês (excluindo fins de semana)
$inicio = new DateTime($inicio_mes);
$fim = new DateTime($fim_mes);
$fim->modify('+1 day');
$intervalo = new DateInterval('P1D');
$periodo = new DatePeriod($inicio, $intervalo, $fim);

foreach ($periodo as $data) {
    $dia_semana = $data->format('N');
    if ($dia_semana < 6) { // 1 (segunda) até 5 (sexta)
        $dias_uteis_mes++;
    }
}

// Estatísticas do mês atual
$sql_presencas_mes = "SELECT 
                      COUNT(DISTINCT data_registro) as dias_presentes,
                      COUNT(DISTINCT CASE WHEN status = 'presente' THEN data_registro END) as dias_presentes_no_hora,
                      COUNT(DISTINCT CASE WHEN status = 'atrasado' THEN data_registro END) as dias_atrasados
                      FROM registro_ponto
                      WHERE funcionario_id IN (SELECT id_fun FROM funcionario WHERE empresa_id = $empresa_id AND estado = 'Ativo')
                      AND data_registro BETWEEN '$inicio_mes' AND '$fim_mes'";
$result_presencas_mes = mysqli_query($conn, $sql_presencas_mes);
$presencas_mes = mysqli_fetch_assoc($result_presencas_mes);

$dias_presentes = $presencas_mes['dias_presentes'] ?? 0;
$dias_presentes_no_hora = $presencas_mes['dias_presentes_no_hora'] ?? 0;
$dias_atrasados = $presencas_mes['dias_atrasados'] ?? 0;

// Calcular média de horas trabalhadas no mês
$sql_media_horas = "SELECT AVG(
                    TIME_TO_SEC(TIMEDIFF(hora_saida, hora_entrada))/3600
                  ) as media_horas 
                  FROM registro_ponto
                  WHERE funcionario_id IN (SELECT id_fun FROM funcionario WHERE empresa_id = $empresa_id)
                  AND data_registro BETWEEN '$inicio_mes' AND '$fim_mes'
                  AND hora_entrada IS NOT NULL 
                  AND hora_saida IS NOT NULL";
$result_media_horas = mysqli_query($conn, $sql_media_horas);
$media_horas = 0;
if ($result_media_horas && mysqli_num_rows($result_media_horas) > 0) {
    $media_horas = mysqli_fetch_assoc($result_media_horas)['media_horas'];
    $media_horas = round($media_horas, 1); // Arredondar para 1 casa decimal
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="all.css/registro3.css">
    <link rel="stylesheet" href="all.css/timer.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAM - Registro de Ponto</title>
    <style>
        /* Estilos específicos para a página de registro de ponto */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #64c2a7;
        }
        
        .stat-card .number {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #777;
            font-size: 0.9rem;
        }
        
        /* Estilos para o resumo mensal */
        .month-summary {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .month-summary h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.2rem;
            text-align: center;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
        }
        
        .summary-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 15px 10px;
            border-radius: 10px;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }
        
        .summary-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .summary-item i {
            font-size: 1.8rem;
            color: #64c2a7;
            margin-bottom: 10px;
        }
        
        .summary-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .summary-label {
            color: #777;
            font-size: 0.8rem;
        }
        
        /* Responsividade para o resumo mensal */
        @media (max-width: 992px) {
            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            position: relative;
            min-height: 300px;
        }
        
        .chart-card h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 1.2rem;
        }
        
        .attendance-table {
            width: 100%;
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .attendance-table h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 1.2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background-color: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 500;
            color: #555;
            border-bottom: 1px solid #ddd;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-align: center;
        }
        
        .status-presente {
            background-color: rgba(100, 194, 167, 0.2);
            color: #2e7d32;
        }
        
        .status-ausente {
            background-color: rgba(239, 83, 80, 0.2);
            color: #c62828;
        }
        
        .status-atrasado {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ff8f00;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: #64c2a7;
            color: white;
        }
        
        .btn-secondary {
            background-color: #f5f5f5;
            color: #555;
        }
        
        /* Estilos para a imagem do funcionário na tabela */
        .employee-avatar {
            object-fit: cover;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 2px solid white;
        }
        
        /* Estilos para os modais */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #888;
            cursor: pointer;
        }
        
        .close:hover {
            color: #333;
        }
        
        .modal h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }

        /* Dark mode */
        body.dark {
            background-color: #1A1A1A;
            color: #e0e0e0;
        }
        
        body.dark .stat-card,
        body.dark .chart-card,
        body.dark .attendance-table,
        body.dark .month-summary {
            background-color: #1E1E1E;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        
        body.dark .stat-card .label,
        body.dark .summary-label {
            color: #b0b0b0;
        }
        
        body.dark .month-summary h3,
        body.dark .chart-card h3,
        body.dark .attendance-table h3 {
            color: #e0e0e0;
        }
        
        body.dark .summary-item {
            background-color: #2C2C2C;
        }
        
        body.dark table th {
            background-color: #2C2C2C;
            color: #b0b0b0;
            border-bottom: 1px solid #444;
        }
        
        body.dark table td {
            border-bottom: 1px solid #333;
        }
        
        body.dark .btn-secondary {
            background-color: #2C2C2C;
            color: #e0e0e0;
        }
        
        body.dark .modal-content {
            background-color: #1E1E1E;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        body.dark .modal h2 {
            color: #e0e0e0;
        }
        
        body.dark .form-group label {
            color: #b0b0b0;
        }
        
        body.dark .form-group input, 
        body.dark .form-group select, 
        body.dark .form-group textarea {
            background-color: #2C2C2C;
            border-color: #444;
            color: #e0e0e0;
        }
        
        body.dark .close {
            color: #999;
        }
        
        body.dark .close:hover {
            color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <a href="UI.php">
                <img src="img/sam2logo-32.png" alt="SAM Logo">
            </a>
        </div>
        <select class="nav-select">
            <option>sam</option>
        </select>
        <ul class="nav-menu">           
            <a href="funcionarios.php"><li>Funcionários</li></a>
            <a href="registro.php"><li>Novo Funcionário</li></a>
            <li>Processamento Salarial</li>
            <a href="docs.php"><li>Documentos</li></a>
            <a href="registro_ponto.php"><li class="active">Registro de Ponto</li></a>
            <a href="ausencias.php"><li>Ausências</li></a>
            <a href="recrutamento.php"><li>Recrutamento</li></a>
        </ul>
    </div>

    <div class="main-content">
        <header class="header">
            <h1 class="page-title">Registro de Ponto</h1>
            <div class="header-buttons">
                <div class="time" id="current-time"></div>
                <a class="exit-tag" href="logout.php">Sair</a>
                <a href="./configuracoes_sam/perfil_adm.php" class="perfil_img">                
                    <div class="user-profile">
                        <img src="icones/icons-sam-18.svg" alt="User" width="20">
                        <span><?php echo $_SESSION['nome']; ?></span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                </a>
            </div>
        </header>

        <div class="action-buttons">
            <button class="btn btn-primary" onclick="document.getElementById('modal-exportar').style.display='block'">
                <i class="fas fa-file-export"></i> Exportar Relatório
            </button>
            <button class="btn btn-secondary" onclick="document.getElementById('modal-registrar-ponto').style.display='block'">
                <i class="fas fa-clock"></i> Registrar Ponto
            </button>
        </div>

        <!-- Modal para Registro de Ponto -->
        <div id="modal-registrar-ponto" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('modal-registrar-ponto').style.display='none'">&times;</span>
                <h2>Registrar Ponto</h2>
                <form action="" method="post">
                    <div class="form-group">
                        <label for="funcionario_id">Funcionário:</label>
                        <select name="funcionario_id" id="funcionario_id" required>
                            <option value="">Selecione um funcionário</option>
                            <?php
                            $sql_funcionarios = "SELECT id_fun, nome, num_mecanografico FROM funcionario 
                                               WHERE empresa_id = $empresa_id AND estado = 'Ativo'
                                               ORDER BY nome ASC";
                            $result_funcionarios = mysqli_query($conn, $sql_funcionarios);
                            while ($funcionario = mysqli_fetch_assoc($result_funcionarios)) {
                                echo "<option value='{$funcionario['id_fun']}'>{$funcionario['nome']} (#{$funcionario['num_mecanografico']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="data">Data:</label>
                        <input type="date" name="data" id="data" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo_registro">Tipo de Registro:</label>
                        <select name="tipo_registro" id="tipo_registro" required>
                            <option value="entrada">Entrada</option>
                            <option value="saida">Saída</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="hora">Hora:</label>
                        <input type="time" name="hora" id="hora" value="<?php echo date('H:i'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="observacao">Observação:</label>
                        <textarea name="observacao" id="observacao" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="registrar_ponto" class="btn btn-primary">Registrar</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-registrar-ponto').style.display='none'">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal para Exportar Relatório -->
        <div id="modal-exportar" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('modal-exportar').style.display='none'">&times;</span>
                <h2>Exportar Relatório</h2>
                <form action="" method="post">
                    <div class="form-group">
                        <label for="periodo_relatorio">Período:</label>
                        <select name="periodo_relatorio" id="periodo_relatorio">
                            <option value="semana">Última semana</option>
                            <option value="mes" selected>Último mês</option>
                            <option value="trimestre">Último trimestre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="formato">Formato:</label>
                        <select name="formato" id="formato">
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="exportar_relatorio" class="btn btn-primary">Exportar</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-exportar').style.display='none'">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cards de estatísticas -->
        <div class="dashboard-cards">
            <div class="stat-card">
                <i class="fas fa-users icon"></i>
                <div class="number"><?php echo $total_funcionarios; ?></div>
                <div class="label">Total de Funcionários</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle icon"></i>
                <div class="number"><?php echo $total_presentes; ?></div>
                <div class="label">Presentes Hoje</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-times-circle icon"></i>
                <div class="number"><?php echo $total_ausentes; ?></div>
                <div class="label">Ausentes Hoje</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-exclamation-circle icon"></i>
                <div class="number"><?php echo $total_atrasados; ?></div>
                <div class="label">Atrasados Hoje</div>
            </div>
        </div>

        <!-- Resumo mensal -->
        <div class="month-summary">
            <h3>Resumo do Mês de <?php echo date('F Y'); ?></h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <i class="fas fa-calendar-check"></i>
                    <div class="summary-value"><?php echo $dias_uteis_mes; ?></div>
                    <div class="summary-label">Dias úteis</div>
                </div>
                <div class="summary-item">
                    <i class="fas fa-calendar-day"></i>
                    <div class="summary-value"><?php echo $dias_presentes; ?></div>
                    <div class="summary-label">Dias com registro</div>
                </div>
                <div class="summary-item">
                    <i class="fas fa-hourglass-half"></i>
                    <div class="summary-value"><?php echo $media_horas; ?></div>
                    <div class="summary-label">Média de horas por dia</div>
                </div>
                <div class="summary-item">
                    <i class="fas fa-clock"></i>
                    <div class="summary-value"><?php echo $dias_presentes_no_hora; ?></div>
                    <div class="summary-label">Registros no horário</div>
                </div>
                <div class="summary-item">
                    <i class="fas fa-running"></i>
                    <div class="summary-value"><?php echo $dias_atrasados; ?></div>
                    <div class="summary-label">Dias com atrasos</div>
                </div>
                <div class="summary-item">
                    <i class="fas fa-percentage"></i>
                    <div class="summary-value"><?php echo ($dias_uteis_mes > 0) ? round(($dias_presentes / $dias_uteis_mes) * 100) : 0; ?>%</div>
                    <div class="summary-label">Taxa de presença</div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="chart-container">
            <div class="chart-card">
                <h3>Presença Diária - Últimos 7 dias</h3>
                <canvas id="presencaChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Distribuição de Status - Hoje</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Tabela de registros -->
        <div class="attendance-table">
            <h3>Registros de Ponto Recentes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Departamento</th>
                        <th>Data</th>
                        <th>Entrada</th>
                        <th>Saída</th>
                        <th>Horas Trabalhadas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_registros_recentes && mysqli_num_rows($result_registros_recentes) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result_registros_recentes)): 
                            $data_formatada = date('d/m/Y', strtotime($row['data_registro']));
                            $entrada = !empty($row['hora_entrada']) ? date('H:i', strtotime($row['hora_entrada'])) : '-';
                            $saida = !empty($row['hora_saida']) ? date('H:i', strtotime($row['hora_saida'])) : '-';
                            $horas_trabalhadas = calcularHorasTrabalhadas($row['hora_entrada'], $row['hora_saida']);
                            
                            // Determinar o status com base no campo status da tabela
                            $status = ucfirst($row['status']); // Capitaliza a primeira letra
                            $status_class = 'status-' . $row['status'];
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['foto'])): ?>
                                    <img src="<?php echo $row['foto']; ?>" alt="<?php echo $row['nome']; ?>" class="employee-avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 8px; vertical-align: middle;">
                                <?php else: ?>
                                    <i class="fas fa-user-circle" style="font-size: 18px; color: #aaa; margin-right: 8px; vertical-align: middle;"></i>
                                <?php endif; ?>
                                <?php echo $row['nome']; ?>
                            </td>
                            <td><?php echo $row['departamento'] ?? 'N/D'; ?></td>
                            <td><?php echo $data_formatada; ?></td>
                            <td><?php echo $entrada; ?></td>
                            <td><?php echo $saida; ?></td>
                            <td><?php echo $horas_trabalhadas; ?></td>
                            <td><span class="status <?php echo $status_class; ?>"><?php echo $status; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">Nenhum registro encontrado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Função para atualizar o relógio
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Traduzir nomes de meses para português
        document.addEventListener('DOMContentLoaded', function() {
            // Traduzir nome do mês no título do resumo mensal
            const monthNames = {
                'January': 'Janeiro',
                'February': 'Fevereiro',
                'March': 'Março',
                'April': 'Abril',
                'May': 'Maio',
                'June': 'Junho',
                'July': 'Julho',
                'August': 'Agosto',
                'September': 'Setembro',
                'October': 'Outubro',
                'November': 'Novembro',
                'December': 'Dezembro'
            };
            
            const monthSummaryTitle = document.querySelector('.month-summary h3');
            if (monthSummaryTitle) {
                let titleText = monthSummaryTitle.textContent;
                Object.keys(monthNames).forEach(englishMonth => {
                    titleText = titleText.replace(englishMonth, monthNames[englishMonth]);
                });
                monthSummaryTitle.textContent = titleText;
            }
            
            // Configuração dos gráficos
            // Gráfico de presença diária (últimos 7 dias)
            const ctxPresenca = document.getElementById('presencaChart').getContext('2d');
            const presencaChart = new Chart(ctxPresenca, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels_dias); ?>,
                    datasets: [{
                        label: 'Presentes',
                        data: <?php echo json_encode($dados_presentes); ?>,
                        borderColor: '#64c2a7',
                        backgroundColor: 'rgba(100, 194, 167, 0.1)',
                        tension: 0.3,
                        fill: true
                    }, {
                        label: 'Ausentes',
                        data: <?php echo json_encode($dados_ausentes); ?>,
                        borderColor: '#ef5350',
                        backgroundColor: 'rgba(239, 83, 80, 0.1)',
                        tension: 0.3,
                        fill: true
                    }, {
                        label: 'Atrasados',
                        data: <?php echo json_encode($dados_atrasados); ?>,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Gráfico de distribuição de status (hoje)
            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Presentes', 'Ausentes', 'Atrasados'],
                    datasets: [{
                        data: [<?php echo $total_presentes; ?>, <?php echo $total_ausentes; ?>, <?php echo $total_atrasados; ?>],
                        backgroundColor: [
                            'rgba(100, 194, 167, 0.8)',
                            'rgba(239, 83, 80, 0.8)',
                            'rgba(255, 193, 7, 0.8)'
                        ],
                        borderColor: [
                            'rgba(100, 194, 167, 1)',
                            'rgba(239, 83, 80, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        });
    </script>
    <script src="./js/theme.js"></script>
</body>
</html> 