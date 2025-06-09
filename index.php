<?php

// --- VARIÁVEIS GLOBAIS DE USUÁRIO (para a API C#) ---
$tipoUsuario = 'admin';
$nomeUsuario = 'joao'; // Usuário padrão para exibir e criar tarefas

// --- LÓGICA DE PROCESSAMENTO DE FORMULÁRIOS ---

// Processar a Criação de Nova Tarefa (via API C#)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_tarefa'])) {
    $titulo = $_POST['titulo'];
    $usuarioTarefa = $_POST['usuario'];
    $preco = (float)$_POST['preco'];
    $prioridadeForm = $_POST['prioridade']; // 'Alta', 'Media', 'Baixa'

    // Dados a serem enviados para a API C#
    $data = [
        'Titulo' => $titulo, 
        'Usuario' => $usuarioTarefa, 
        'Preco' => $preco, 
        'Prioridade' => $prioridadeForm 
        // status, criadoEm e concluidoEm serão definidos pela API C# ao criar
        // A API C# espera o enum como string ex: "Baixa", "Media", "Alta" (sem acento)
    ];

    $json_data = json_encode($data);

    $url = "http://localhost:5093/api/tarefas"; // Endpoint para criar tarefas (verifique se é este mesmo na sua API)

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n",
            'content' => $json_data
        ]
    ];

    $context = stream_context_create($options);
    $response_api = @file_get_contents($url, false, $context);

    if ($response_api === false) {
        $error = error_get_last();
        echo "<div class='alert alert-danger'>Erro ao criar tarefa na API: " . ($error ? $error['message'] : 'Erro desconhecido') . "</div>";
    } else {
        echo "<div class='alert alert-success'>Nova tarefa criada na API com sucesso!</div>";
    }
    // Redireciona para recarregar a página e exibir a tarefa recém-criada
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Lógica de Exclusão de Tarefas (via API C#)
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
    header("Refresh:0"); // Redireciona para recarregar a página
    exit;
}

// Lógica de Mudança de Status (via API C#)
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
        header("Location: " . $_SERVER['PHP_SELF']); // Redireciona para recarregar a página
        exit;
    }
}

// --- LÓGICA DE CARREGAMENTO INICIAL DAS TAREFAS (via API C#) ---
$response = @file_get_contents("http://localhost:5093/api/tarefas/$tipoUsuario/$nomeUsuario");
$tarefas = json_decode($response, true);

if (!is_array($tarefas)) {
    $tarefas = []; // Garante que $tarefas seja um array mesmo se a API falhar
    echo "<div class='alert alert-danger'>Não foi possível carregar as tarefas da API. Verifique se a API está rodando em http://localhost:5093.</div>";
}

// --- CÁLCULOS PARA TOTAIS NO RODAPÉ ---
$precoTotalGeral = 0;
$tempoTotalConcluidoSegundos = 0;

// Array de prioridades válidas para checagem e exibição
// ATENÇÃO: Ajuste aqui para "Media" (sem acento) para corresponder ao C#
$prioridadesValidas = ['Alta', 'Media', 'Baixa'];

// Nova variável: Para contar as prioridades
$contagemPrioridades = [
    'Alta' => 0,
    'Media' => 0, // <-- Ajuste aqui
    'Baixa' => 0,
    'Desconhecida' => 0 // Adicionado para prioridades não reconhecidas
];

// Percorre as tarefas para calcular totais e adicionar prioridade para exibição
foreach ($tarefas as &$tarefa) { // Use & para modificar o array original
    // Use isset() e o camelCase para acessar as chaves do JSON retornado pela API
    $precoTotalGeral += isset($tarefa['preco']) ? (float)$tarefa['preco'] : 0.00; // <-- AGORA camelCase "preco"

    $isConcluido = isset($tarefa['status']) ? (bool)$tarefa['status'] : false; // <-- AGORA camelCase "status"

    // --- LÓGICA PARA PRIORIDADE: Tenta pegar da API, senão define como 'Desconhecida' ---
    // A API C# AGORA DEVE retornar a prioridade como 'prioridade' (camelCase)
    $prioridadeApi = isset($tarefa['prioridade']) ? $tarefa['prioridade'] : ''; // <-- AGORA camelCase "prioridade"

    if (in_array($prioridadeApi, $prioridadesValidas)) {
        $tarefa['prioridadeTexto'] = $prioridadeApi;
    } else {
        // Fallback se a API não retornar uma prioridade válida
        $tarefa['prioridadeTexto'] = 'Desconhecida';
    }

    // Converte a string de data da API para um objeto DateTime
    // Garanta que 'criadoEm' e 'concluidoEm' venham da API e estejam no formato correto (ex: ISO 8601)
    // Use isset() e o camelCase
    $criadoEm = isset($tarefa['criadoEm']) ? new DateTime($tarefa['criadoEm']) : new DateTime(); // <-- AGORA camelCase "criadoEm"

    // Inicializa $tempoGastoDisplay e $tempoGastoEmSegundos para cada tarefa
    $tempoGastoDisplay = 'N/A';
    $tempoGastoEmSegundos = 0;
    $interval = null; // Inicializa $interval para garantir que sempre exista

    if ($isConcluido) {
        // Use isset() e o camelCase
        if (isset($tarefa['concluidoEm']) && !empty($tarefa['concluidoEm'])) { // <-- AGORA camelCase "concluidoEm"
            $concluidoEm = new DateTime($tarefa['concluidoEm']);
            $interval = $criadoEm->diff($concluidoEm); // $interval é definido aqui
            $tempoGastoDisplay = formatTimeDifference($interval);
            $tempoGastoEmSegundos = dateIntervalToSeconds($interval);

            $tempoTotalConcluidoSegundos += $tempoGastoEmSegundos;
        } else {
            $tempoGastoDisplay = 'Concluído (sem data de fim)';
        }
    } else {
        $agora = new DateTime();
        $interval = $criadoEm->diff($agora); // $interval é definido aqui para tarefas pendentes
        $tempoGastoDisplay = 'Pendente há ' . formatTimeDifference($interval);
        // Para tarefas pendentes, o tempo gasto em segundos é o tempo que está pendente
        $tempoGastoEmSegundos = dateIntervalToSeconds($interval);
    }

    // Adiciona o tempo gasto formatado e em segundos à tarefa para uso na tabela e JS
    $tarefa['tempoGastoDisplay'] = $tempoGastoDisplay;
    $tarefa['tempoGastoEmSegundos'] = $tempoGastoEmSegundos;

    // Incrementa a contagem de prioridades para o rodapé
    if (isset($contagemPrioridades[$tarefa['prioridadeTexto']])) {
        $contagemPrioridades[$tarefa['prioridadeTexto']]++;
    } else {
        $contagemPrioridades['Desconhecida']++; // Conta prioridades não reconhecidas
    }
}
unset($tarefa); // Quebra a referência do último elemento

// --- FUNÇÕES AUXILIARES PHP (Manter como estão, apenas revisar se algo foi alterado) ---
function formatTimeDifference($interval) {
    if (!$interval instanceof DateInterval) {
        return 'Data inválida';
    }
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

function dateIntervalToSeconds(DateInterval $interval) {
    if (!$interval instanceof DateInterval) {
        return 0;
    }
    $seconds = $interval->s;
    $seconds += $interval->i * 60;
    $seconds += $interval->h * 3600;
    $seconds += $interval->d * 86400;
    $seconds += $interval->m * 30 * 86400;
    $seconds += $interval->y * 365 * 86400;
    return $seconds;
}

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
    <title>ToDo List - Integrado C# API</title>
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
            margin-bottom: 30px; /* Espaço para o próximo container */
        }

        .form-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px; /* Espaço entre o formulário e a tabela */
        }

        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        h2 {
            margin-top: 0; /* Ajuste para o h2 do formulário */
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
        .prioridade-desconhecida { color: #6c757d; font-weight: bold; } /* Cinza */


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

        .btn-status {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
        }
        .btn-status.btn-success { background-color: #28a745; color: white; }
        .btn-status.btn-warning { background-color: #ffc107; color: #333; }
        .btn-status.btn-success:hover { background-color: #218838; }
        .btn-status.btn-warning:hover { background-color: #e0a800; }


        select, input[type="text"], input[type="number"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: calc(100% - 22px);
            box-sizing: border-box; /* Garante que padding e borda não aumentem o tamanho total */
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }


        tfoot td {
            font-weight: bold;
            border-top: 2px solid #007bff;
            background-color: #e9f5ff;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>Criar Nova Tarefa</h2>
        <form method="post">
            <label for="titulo">Título da Tarefa:</label>
            <input type="text" id="titulo" name="titulo" required>

            <label for="usuario">Usuário Atribuído:</label>
            <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($nomeUsuario) ?>" required>
            <label for="preco">Preço (R$):</label>
            <input type="number" id="preco" name="preco" step="0.01" min="0" value="0.00">

            <label for="prioridade">Prioridade:</label>
            <select id="prioridade" name="prioridade" required>
                <option value="Media">Média</option>
                <option value="Alta">Alta</option>
                <option value="Baixa">Baixa</option>
            </select>

            <input type="submit" name="criar_tarefa" value="Adicionar Tarefa">
        </form>
    </div>

    <div class="container">
        <h1>Minhas Tarefas</h1>

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
                    <th>Prioridade</th>
                    <th>Tempo Gasto</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php
                if (!empty($tarefas)) {
                    foreach ($tarefas as $tarefa):
                        // Use isset() para verificar se a chave existe antes de acessar
                        $id = htmlspecialchars(isset($tarefa['id']) ? $tarefa['id'] : 'N/A'); 
                        $titulo = htmlspecialchars(isset($tarefa['titulo']) ? $tarefa['titulo'] : 'N/A'); 
                        $usuario = htmlspecialchars(isset($tarefa['usuario']) ? $tarefa['usuario'] : 'N/A'); 
                        $isConcluido = isset($tarefa['status']) ? (bool)$tarefa['status'] : false; 
                        $precoTarefa = isset($tarefa['preco']) ? (float)$tarefa['preco'] : 0.00; 

                        // Prioridade: Tenta pegar da API, senão define como 'Desconhecida'
                        $prioridadeTexto = isset($tarefa['prioridade']) ? htmlspecialchars($tarefa['prioridade']) : 'Desconhecida';
                        $prioridadeClass = 'prioridade-' . strtolower($prioridadeTexto);
                        // Garante que a classe CSS para 'Desconhecida' exista, se for o caso.
                        if (!in_array(strtolower($prioridadeTexto), ['alta', 'media', 'baixa'])) {
                             $prioridadeClass = 'prioridade-desconhecida';
                        }


                        $statusTexto = $isConcluido ? 'Concluído' : 'Pendente';
                        $statusClass = $isConcluido ? 'status-concluido' : 'status-pendente';
                    ?>
                        <tr
                            data-is-concluido="<?= $isConcluido ? 'true' : 'false' ?>"
                            data-tempo-gasto-segundos="<?= $tarefa['tempoGastoEmSegundos'] ?? 0 ?>"
                            data-prioridade="<?= $prioridadeTexto ?>">
                            <td><?= $id ?></td>
                            <td><?= $titulo ?></td>
                            <td><?= $usuario ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="id_atualizar" value="<?= $id ?>">
                                    <input type="hidden" name="tipoUsuario" value="<?= htmlspecialchars($tipoUsuario) ?>">
                                    <input type="hidden" name="nomeUsuario" value="<?= htmlspecialchars($usuario) ?>">
                                    <button type="submit" name="mudar_status" class="btn-status <?= $statusClass ?>">
                                        <?= $statusTexto ?>
                                    </button>
                                </form>
                            </td>
                            <td>R$ <?= number_format($precoTarefa, 2, ',', '.') ?></td>
                            <td class="<?= $prioridadeClass ?>"><?= $prioridadeTexto ?></td>
                            <td><?= $tarefa['tempoGastoDisplay'] ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="id_apagar" value="<?= $id ?>">
                                    <input type="hidden" name="tipoUsuario" value="<?= htmlspecialchars($tipoUsuario) ?>">
                                    <input type="hidden" name="nomeUsuario" value="<?= htmlspecialchars($usuario) ?>">
                                    <button type="submit" name="excluir" class="btn-delete">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                } else { ?>
                    <tr>
                        <td colspan="8">Nenhuma tarefa encontrada ou erro na API. Crie uma nova acima!</td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;">**Totais Visíveis:**</td>
                    <td id="totalPrecoGeral">**R$ <?= number_format($precoTotalGeral, 2, ',', '.') ?>**</td>
                    <td id="totalPrioridades">
                        **Alta:** <?= $contagemPrioridades['Alta'] ?> |
                        **Média:** <?= $contagemPrioridades['Media'] ?> |
                        **Baixa:** <?= $contagemPrioridades['Baixa'] ?> |
                        **Desconhecida:** <?= $contagemPrioridades['Desconhecida'] ?>
                    </td>
                    <td id="totalTempoConcluidoGeral">**<?= formatSecondsToReadableTime($tempoTotalConcluidoSegundos) ?>**</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <script>
        // Função para formatar segundos em tempo legível (igual à do PHP)
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

        // FUNÇÃO JAVASCRIPT: Filtra a tabela e atualiza os totais
        function filterTable() {
            var input, filter, table, tr, tdId, tdPrice, i, txtValue;
            input = document.getElementById("searchId");
            filter = input.value.toUpperCase();
            table = document.getElementById("taskTableBody");
            tr = table.getElementsByTagName("tr");

            var currentVisiblePriceTotal = 0;
            var currentVisibleTimeTotalConcluido = 0;
            var currentVisiblePriorities = {
                'Alta': 0,
                'Media': 0, // <-- Ajuste aqui
                'Baixa': 0,
                'Desconhecida': 0
            };

            for (i = 0; i < tr.length; i++) {
                tdId = tr[i].getElementsByTagName("td")[0];
                tdPrice = tr[i].getElementsByTagName("td")[4];

                var isConcluido = tr[i].getAttribute('data-is-concluido') === 'true';
                var tempoGastoSegundos = parseFloat(tr[i].getAttribute('data-tempo-gasto-segundos')) || 0;
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

                        if (prioridade && currentVisiblePriorities.hasOwnProperty(prioridade)) {
                            currentVisiblePriorities[prioridade]++;
                        } else {
                            currentVisiblePriorities['Desconhecida']++;
                        }

                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

            document.getElementById("totalPrecoGeral").textContent = "R$ " + currentVisiblePriceTotal.toFixed(2).replace('.', ',');
            document.getElementById("totalTempoConcluidoGeral").textContent = formatSecondsToReadableTimeJS(currentVisibleTimeTotalConcluido);

            // ATUALIZA O RODAPÉ COM OS TOTAIS DE PRIORIDADE
            document.getElementById("totalPrioridades").innerHTML =
                `**Alta:** ${currentVisiblePriorities['Alta']} | ` +
                `**Média:** ${currentVisiblePriorities['Media']} | ` + // <-- Ajuste aqui para exibir Média
                `**Baixa:** ${currentVisiblePriorities['Baixa']} | ` +
                `**Desconhecida:** ${currentVisiblePriorities['Desconhecida']}`;
        }

        // Chama a função filterTable() uma vez ao carregar a página
        filterTable();
    </script>

</body>

</html>