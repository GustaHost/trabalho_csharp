<?php

$tipoUsuario = 'admin';
$nomeUsuario = 'joao'; 

// --- LÓGICA DE CARREGAMENTO INICIAL DAS TAREFAS ---
$response = file_get_contents("http://localhost:5093/api/tarefas/$tipoUsuario/$nomeUsuario");
$tarefas = json_decode($response, true);

$precoTotalGeral = 0;
$tempoTotalConcluidoSegundos = 0; 

// --- NOVA VARIÁVEL: Para contar as prioridades
$contagemPrioridades = [
    'Alta' => 0,
    'Média' => 0,
    'Baixa' => 0,
];

if (!is_array($tarefas)) {
    $tarefas = [];
}

// --- LÓGICA DE EXCLUSÃO DE TAREFAS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $id = $_POST['id_apagar'];
    $tipoUsuario = $_POST['tipoUsuario'];
    $nomeUsuario = $_POST['nomeUsuario'];

    $url = "http://localhost:5093/api/tarefas/$id?tipoUsuario=$tipoUsuario&nomeUsuario=$nomeUsuario";

    $options = [
        'http' => [
            'method' => 'DELETE',
            'header' => "Content-Type: application/json"
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === false) {
        $error = error_get_last();
        echo "<div class='alert alert-danger'>Erro ao excluir tarefa: " . ($error ? $error['message'] : 'Erro desconhecido') . "</div>";
    } else {
        echo "<div class='alert alert-success'>Tarefa $id excluída com sucesso!</div>";
    }

    header("Refresh:0");
    exit;
}

// --- LÓGICA DE MUDANÇA DE STATUS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mudar_status'])) {
    $id = $_POST['id_atualizar'];
    $tipoUsuario = $_POST['tipoUsuario'];
    $nomeUsuario = $_POST['nomeUsuario'];

    $url = "http://localhost:5093/api/tarefas/$id/status?tipoUsuario=$tipoUsuario&nomeUsuario=$nomeUsuario";

    $options = [
        'http' => [
            'method' => 'PUT',
            'header' => "Content-Type: application/json",
            'content' => ''
        ]
    ];

    $context = stream_context_create($options);
    $response_api = @file_get_contents($url, false, $context);

    if ($response_api === false) {
        $error = error_get_last();
        echo "<div class='alert alert-danger'>Erro ao atualizar status: " . ($error ? $error['message'] : 'Erro desconhecido') . "</div>";
    } else {
        echo "<div class='alert alert-success'>Status atualizado com sucesso!</div>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// --- FUNÇÃO AUXILIAR PHP: Formata um DateInterval para exibição legível ---
function formatTimeDifference($interval) {
    $parts = [];
    if ($interval->y > 0) $parts[] = $interval->y . ' ano' . ($interval->y > 1 ? 's' : '');
    if ($interval->m > 0) $parts[] = $interval->m . ' mês' . ($interval->m > 1 ? 'es' : '');
    if ($interval->d > 0) $parts[] = $interval->d . ' dia' . ($interval->d > 1 ? 's' : '');
    if ($interval->h > 0) $parts[] = $interval->h . ' hora' . ($interval->h > 1 ? 's' : '');
    if ($interval->i > 0) $parts[] = $interval->i . ' minuto' . ($interval->i > 1 ? 's' : '');
    if ($interval->s > 0 && ($interval->y == 0 && $interval->m == 0 && $interval->d == 0 && $interval->h == 0 && $interval->i == 0)) {
        $parts[] = $interval->s . ' segundo' . ($interval->s > 1 ? 's' : '');
    }

    return empty($parts) ? '0 segundos' : implode(', ', $parts);
}

// --- NOVA FUNÇÃO AUXILIAR PHP: Converte DateInterval para total de segundos ---
function dateIntervalToSeconds(DateInterval $interval) {
    $seconds = $interval->s;
    $seconds += $interval->i * 60;
    $seconds += $interval->h * 3600;
    $seconds += $interval->d * 86400;
    $seconds += $interval->m * 30 * 86400; 
    $seconds += $interval->y * 365 * 86400; 
    return $seconds;
}

// --- NOVA FUNÇÃO AUXILIAR PHP: Formata total de segundos em um formato legível ---
function formatSecondsToReadableTime($totalSeconds) {
    if ($totalSeconds < 60) {
        return round($totalSeconds) . ' segundos';
    } elseif ($totalSeconds < 3600) {
        $minutes = floor($totalSeconds / 60);
        $seconds = round($totalSeconds % 60);
        return $minutes . ' minuto' . ($minutes > 1 ? 's' : '') . ($seconds > 0 ? ', ' . $seconds . ' segundo' . ($seconds > 1 ? 's' : '') : '');
    } elseif ($totalSeconds < 86400) {
        $hours = floor($totalSeconds / 3600);
        $minutes = round(($totalSeconds % 3600) / 60);
        return $hours . ' hora' . ($hours > 1 ? 's' : '') . ($minutes > 0 ? ', ' . $minutes . ' minuto' . ($minutes > 1 ? 's' : '') : '');
    } elseif ($totalSeconds < 31536000) { 
        $days = floor($totalSeconds / 86400);
        $hours = round(($totalSeconds % 86400) / 3600);
        return $days . ' dia' . ($days > 1 ? 's' : '') . ($hours > 0 ? ', ' . $hours . ' hora' . ($hours > 1 ? 's' : '') : '');
    } else {
        $years = floor($totalSeconds / 31536000);
        $days = round(($totalSeconds % 31536000) / 86400);
        return $years . ' ano' . ($years > 1 ? 's' : '') . ($days > 0 ? ', ' . $days . ' dia' . ($days > 1 ? 's' : '') : '');
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ToDo List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-bar {
            text-align: right;
            margin-bottom: 10px;
        }

        .search-bar input {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .status-pendente {
            color: #ffc107;
            font-weight: bold;
        }

        .status-concluido {
            color: #28a745;
            font-weight: bold;
        }

        /* --- ESTILOS PARA PRIORIDADES --- */
        .prioridade-alta { color: #dc3545; font-weight: bold; } /* Vermelho */
        .prioridade-media { color: #ffc107; font-weight: bold; } /* Laranja */
        .prioridade-baixa { color: #17a2b8; font-weight: bold; } /* Ciano */


        .btn-delete {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        select {
            padding: 5px;
            border-radius: 5px;
        }

        tfoot td {
            font-weight: bold;
            border-top: 2px solid #007bff;
            background-color: #e9f5ff; 
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>ToDo List</h1>

        <div class="search-bar">
            <label for="searchId">Pesquisar por ID:</label>
            <input type="text" id="searchId" placeholder="Digite o ID..." onkeyup="filterTable()">
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Usuário</th>
                    <th>Status</th>
                    <th>Preço</th>
                    <th>Prioridade</th> <th>Tempo Gasto</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php
                // Array de prioridades possíveis
                $prioridadesPossiveis = ['Alta', 'Média', 'Baixa'];

                foreach ($tarefas as $tarefa):
                    $id = htmlspecialchars($tarefa['id']);
                    $titulo = htmlspecialchars($tarefa['titulo']);
                    $usuario = htmlspecialchars($tarefa['usuario']);
                    $isConcluido = (bool)$tarefa['status'];
                    $precoTarefa = isset($tarefa['preco']) ? (float)$tarefa['preco'] : 0.00;
                    
                    // --- SIMULAÇÃO DA PRIORIDADE ---
                    // Atribui uma prioridade aleatória para fins de demonstração
                    $prioridade = $prioridadesPossiveis[array_rand($prioridadesPossiveis)];
                    // Adiciona a prioridade à tarefa no array PHP (não persistente, apenas para esta exibição)
                    $tarefa['prioridade'] = $prioridade; 

                    $criadoEm = new DateTime($tarefa['criadoEm']);
                    $tempoGastoDisplay = 'N/A'; 
                    $tempoGastoEmSegundos = 0; 

                    if ($isConcluido) {
                        if (isset($tarefa['concluidoEm']) && !empty($tarefa['concluidoEm'])) {
                            $concluidoEm = new DateTime($tarefa['concluidoEm']);
                            $interval = $criadoEm->diff($concluidoEm);
                            $tempoGastoDisplay = formatTimeDifference($interval);
                            $tempoGastoEmSegundos = dateIntervalToSeconds($interval);
                            
                            $tempoTotalConcluidoSegundos += $tempoGastoEmSegundos; 
                        } else {
                            $tempoGastoDisplay = 'Concluído (sem data de fim)';
                        }
                    } else {
                        $agora = new DateTime();
                        $interval = $criadoEm->diff($agora);
                        $tempoGastoDisplay = 'Pendente há ' . formatTimeDifference($interval);
                    }

                    $statusTexto = $isConcluido ? 'Concluído' : 'Pendente';
                    $statusClass = $isConcluido ? 'status-concluido' : 'status-pendente';

                    // Define a classe CSS para a prioridade
                    $prioridadeClass = 'prioridade-' . strtolower($prioridade);
                    
                    $precoTotalGeral += $precoTarefa;
                ?>
                    <tr 
                        data-is-concluido="<?= $isConcluido ? 'true' : 'false' ?>" 
                        data-tempo-gasto-segundos="<?= $tempoGastoEmSegundos ?>"
                        data-prioridade="<?= htmlspecialchars($prioridade) ?>"> <td><?= $id ?></td>
                        <td><?= $titulo ?></td>
                        <td><?= $usuario ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="id_atualizar" value="<?= $id ?>">
                                <input type="hidden" name="tipoUsuario" value="admin">
                                <input type="hidden" name="nomeUsuario" value="<?= $usuario ?>">
                                <button type="submit" name="mudar_status" class="btn btn-sm <?= $isConcluido ? 'btn-success' : 'btn-warning' ?> <?= $statusClass ?>">
                                    <?= $statusTexto ?>
                                </button>
                            </form>
                        </td>
                        <td>R$ <?= number_format($precoTarefa, 2, ',', '.') ?></td>
                        <td class="<?= $prioridadeClass ?>"><?= htmlspecialchars($prioridade) ?></td> <td><?= $tempoGastoDisplay ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="id_apagar" value="<?= $id ?>">
                                <input type="hidden" name="tipoUsuario" value="admin">
                                <input type="hidden" name="nomeUsuario" value="<?= $usuario ?>">
                                <button type="submit" name="excluir" class="btn-delete">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;">**Totais Gerais:**</td>
                    <td id="totalPrecoGeral">**R$ <?= number_format($precoTotalGeral, 2, ',', '.') ?>**</td>
                    <td id="totalPrioridades"></td> <td id="totalTempoConcluidoGeral">**<?= formatSecondsToReadableTime($tempoTotalConcluidoSegundos) ?>**</td>
                    <td></td> 
                </tr>
            </tfoot>
        </table>
    </div>

    <script>
        function formatSecondsToReadableTimeJS(totalSeconds) {
            if (totalSeconds < 60) {
                return Math.round(totalSeconds) + ' segundos';
            } else if (totalSeconds < 3600) {
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = Math.round(totalSeconds % 60);
                return minutes + ' minuto' + (minutes > 1 ? 's' : '') + (seconds > 0 ? ', ' + seconds + ' segundo' + (seconds > 1 ? 's' : '') : '');
            } else if (totalSeconds < 86400) {
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.round((totalSeconds % 3600) / 60);
                return hours + ' hora' + (hours > 1 ? 's' : '') + (minutes > 0 ? ', ' + minutes + ' minuto' + (minutes > 1 ? 's' : '') : '');
            } else if (totalSeconds < 31536000) { 
                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.round((totalSeconds % 86400) / 3600);
                return days + ' dia' + (days > 1 ? 's' : '') + (hours > 0 ? ', ' + hours + ' hora' + (hours > 1 ? 's' : '') : '');
            } else {
                const years = Math.floor(totalSeconds / 31536000);
                const days = Math.round((totalSeconds % 31536000) / 86400);
                return years + ' ano' + (years > 1 ? 's' : '') + (days > 0 ? ', ' + days + ' dia' + (days > 1 ? 's' : '') : '');
            }
        }

        // --- FUNÇÃO JAVASCRIPT: Filtra a tabela e atualiza os totais ---
        function filterTable() {
            var input, filter, table, tr, tdId, tdPrice, i, txtValue;
            input = document.getElementById("searchId");
            filter = input.value.toUpperCase();
            table = document.getElementById("taskTableBody");
            tr = table.getElementsByTagName("tr");

            var currentVisiblePriceTotal = 0; 
            var currentVisibleTimeTotalConcluido = 0; 
            // --- NOVAS VARIÁVEIS PARA CONTAR PRIORIDADES VISÍVEIS ---
            var currentVisiblePriorities = {
                'Alta': 0,
                'Média': 0,
                'Baixa': 0
            };

            for (i = 0; i < tr.length; i++) {
                tdId = tr[i].getElementsByTagName("td")[0];
                tdPrice = tr[i].getElementsByTagName("td")[4]; 
                
                var isConcluido = tr[i].getAttribute('data-is-concluido') === 'true';
                var tempoGastoSegundos = parseFloat(tr[i].getAttribute('data-tempo-gasto-segundos')) || 0;
                // --- OBTÉM A PRIORIDADE DA LINHA ---
                var prioridade = tr[i].getAttribute('data-prioridade');


                if (tdId && tdPrice) {
                    txtValue = tdId.textContent || tdId.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = ""; 
                        
                        var priceString = tdPrice.textContent.replace('R$', '').replace(',', '.').trim();
                        currentVisiblePriceTotal += parseFloat(priceString);

                        if (isConcluido) {
                            currentVisibleTimeTotalConcluido += tempoGastoSegundos;
                        }
                        // --- INCREMENTA O CONTADOR DE PRIORIDADE DA TAREFA VISÍVEL ---
                        if (prioridade && currentVisiblePriorities.hasOwnProperty(prioridade)) {
                            currentVisiblePriorities[prioridade]++;
                        }

                    } else {
                        tr[i].style.display = "none"; 
                    }
                }
            }
            
            document.getElementById("totalPrecoGeral").textContent = "R$ " + currentVisiblePriceTotal.toFixed(2).replace('.', ',');
            document.getElementById("totalTempoConcluidoGeral").textContent = formatSecondsToReadableTimeJS(currentVisibleTimeTotalConcluido); 
            
            // --- ATUALIZA O RODAPÉ COM OS TOTAIS DE PRIORIDADE ---
            document.getElementById("totalPrioridades").innerHTML = 
                `Alta: ${currentVisiblePriorities['Alta']} | ` +
                `Média: ${currentVisiblePriorities['Média']} | ` +
                `Baixa: ${currentVisiblePriorities['Baixa']}`;
        }

        // Chama a função filterTable() uma vez ao carregar a página
        // para garantir que os totais estejam corretos antes de qualquer pesquisa.
        filterTable(); 
    </script>

</body>

</html>