<?php

function calcularPercentualCrescimento($valorAtual, $valorAnterior) {
    if ($valorAnterior == 0) {
        return "N/A"; // Evita divisão por zero
    }
    
    $percentual = (($valorAtual - $valorAnterior) / $valorAnterior) * 100;
    return number_format($percentual, 1) . '%';
}

function calcularPercentagemCandidaturas($totalCandidaturas, $totalVagas) {
    if ($totalVagas == 0) {
        return "N/A"; // Evita divisão por zero
    }
    
    $percentagem = ($totalCandidaturas / $totalVagas) * 100;
    return number_format($percentagem, 1) . '%';
}
?>
