<?php

// Define o tipo de usuário e o nome do usuário logado.
// Em uma aplicação real, isso viria de um sistema de autenticação seguro (sessão, JWT, etc.).
$tipoUsuario = 'admin';
$nomeUsuario = 'joao'; 

// --- LÓGICA DE CARREGAMENTO INICIAL DAS TAREFAS ---
// Faz uma requisição GET para a API C# para obter as tarefas com base no tipo e nome do usuário.
$response = file_get_contents("http://localhost:5093/api/tarefas/$tipoUsuario/$nomeUsuario");
// Decodifica a resposta JSON da API em um array associativo PHP.
$tarefas = json_decode($response, true);

// Inicializa a soma total do preço de todas as tarefas.
$precoTotalGeral = 0;
// Inicializa a soma total do tempo gasto para tarefas concluídas em segundos.
// Usaremos segundos para facilitar a soma e depois formatar para exibição.
$tempoTotalConcluidoSegundos = 0; 

// Garante que $tarefas é um array, mesmo que a API retorne algo inesperado ou vazio.
// Isso evita erros no loop foreach caso a requisição falhe.
if (!is_array($tarefas)) {
    $tarefas = [];
    // Opcionalmente, você pode adicionar uma mensagem de erro para o usuário aqui.
    // echo "<div class='alert alert-danger'>Erro ao carregar tarefas.</div>";
}

// --- LÓGICA DE EXCLUSÃO DE TAREFAS ---
// Verifica se a requisição é um POST e se o botão "excluir" foi clicado.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    // Obtém o ID da tarefa a ser apagada dos dados do formulário.
    $id = $_POST['id_apagar'];
    // Obtém o tipo de usuário e nome de usuário, passados via campos hidden no formulário.
    $tipoUsuario = $_POST['tipoUsuario'];
    $nomeUsuario = $_POST['nomeUsuario'];

    // Constrói a URL para a requisição DELETE na API.
    $url = "http://localhost:5093/api/tarefas/$id?tipoUsuario=$tipoUsuario&nomeUsuario=$nomeUsuario";

    // Define as opções para a requisição HTTP (método DELETE, cabeçalhos).
    $options = [
        'http' => [
            'method' => 'DELETE',
            'header' => "Content-Type: application/json" // Indica que o corpo da requisição é JSON (mesmo que vazio)
        ]
    ];

    // Cria um contexto de stream com as opções definidas.
    $context = stream_context_create($options);
    // Faz a requisição DELETE. O '@' suprime warnings PHP em caso de falha, permitindo tratamento manual.
    $result = @file_get_contents($url, false, $context);

    // Verifica se a requisição falhou.
    if ($result === false) {
        // Obtém o último erro do PHP para depuração.
        $error = error_get_last();
        echo "<div class='alert alert-danger'>Erro ao excluir tarefa: " . ($error ? $error['message'] : 'Erro desconhecido') . "</div>";
    } else {
        // Exibe uma mensagem de sucesso.
        echo "<div class='alert alert-success'>Tarefa $id excluída com sucesso!</div>";
    }

    // Recarrega a página IMEDIATAMENTE para refletir as mudanças.
    header("Refresh:0");
    exit; // Termina a execução do script após o redirecionamento.
}

// --- LÓGICA DE MUDANÇA DE STATUS DA TAREFA ---
// Verifica se a requisição é um POST e se o botão "mudar_status" foi clicado.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mudar_status'])) {
    // Obtém o ID da tarefa a ser atualizada.
    $id = $_POST['id_atualizar'];
    // Obtém o tipo de usuário e nome de usuário.
    $tipoUsuario = $_POST['tipoUsuario'];
    $nomeUsuario = $_POST['nomeUsuario'];

    // Constrói a URL para a requisição PUT na API, específica para mudar o status.
    // Presume-se que o endpoint da API C# (`/api/tarefas/{id}/status`) cuida da lógica
    // de alternar o status e de atualizar 'ConcluidoEm' para a data/hora atual ou null.
    $url = "http://localhost:5093/api/tarefas/$id/status?tipoUsuario=$tipoUsuario&nomeUsuario=$nomeUsuario";

    // Define as opções para a requisição HTTP (método PUT, cabeçalhos, conteúdo vazio).
    $options = [
        'http' => [
            'method' => 'PUT',
            'header' => "Content-Type: application/json",
            'content' => '' // Conteúdo vazio é necessário para PUT, mesmo que a API não o utilize diretamente
        ]
    ];

    // Cria um contexto de stream e faz a requisição PUT.
    $context = stream_context_create($options);
    $response_api = @file_get_contents($url, false, $context);

    // Verifica se a requisição falhou.
    if ($response_api === false) {
        $error = error_get_last();
        echo "<div class='alert alert-danger'>Erro ao atualizar status: " . ($error ? $error['message'] : 'Erro desconhecido') . "</div>";
    } else {
        // Exibe uma mensagem de sucesso e redireciona para a mesma página.
        echo "<div class='alert alert-success'>Status atualizado com sucesso!</div>";
        header("Location: " . $_SERVER['PHP_SELF']); // Redireciona para evitar re-submissão do formulário.
        exit;
    }
}

// --- FUNÇÃO AUXILIAR PHP: Formata um DateInterval para exibição legível ---
// Usada para exibir o tempo gasto individualmente em cada linha da tabela.
function formatTimeDifference($interval) {
    $parts = []; // Array para armazenar as partes formatadas (anos, meses, dias, etc.)
    if ($interval->y > 0) $parts[] = $interval->y . ' ano' . ($interval->y > 1 ? 's' : '');
    if ($interval->m > 0) $parts[] = $interval->m . ' mês' . ($interval->m > 1 ? 'es' : '');
    if ($interval->d > 0) $parts[] = $interval->d . ' dia' . ($interval->d > 1 ? 's' : '');
    if ($interval->h > 0) $parts[] = $interval->h . ' hora' . ($interval->h > 1 ? 's' : '');
    if ($interval->i > 0) $parts[] = $interval->i . ' minuto' . ($interval->i > 1 ? 's' : '');
    // Inclui segundos apenas se o intervalo for muito curto (menos de 1 minuto) para evitar strings muito longas.
    if ($interval->s > 0 && ($interval->y == 0 && $interval->m == 0 && $interval->d == 0 && $interval->h == 0 && $interval->i == 0)) {
        $parts[] = $interval->s . ' segundo' . ($interval->s > 1 ? 's' : '');
    }

    // Retorna '0 segundos' se o intervalo for zero ou vazio, caso contrário, une as partes com vírgula.
    return empty($parts) ? '0 segundos' : implode(', ', $parts);
}

// --- NOVA FUNÇÃO AUXILIAR PHP: Converte DateInterval para total de segundos ---
// Essencial para somar os tempos de múltiplas tarefas de forma consistente.
function dateIntervalToSeconds(DateInterval $interval) {
    $seconds = $interval->s; // Segundos
    $seconds += $interval->i * 60; // Minutos em segundos
    $seconds += $interval->h * 3600; // Horas em segundos
    $seconds += $interval->d * 86400; // Dias em segundos
    $seconds += $interval->m * 30 * 86400; // Meses em segundos (aproximação: 30 dias por mês)
    $seconds += $interval->y * 365 * 86400; // Anos em segundos (aproximação: 365 dias por ano)
    return $seconds;
}

// --- NOVA FUNÇÃO AUXILIAR PHP: Formata total de segundos em um formato legível ---
// Usada para exibir o tempo total no rodapé da tabela.
function formatSecondsToReadableTime($totalSeconds) {
    if ($totalSeconds < 60) {
        return round($totalSeconds) . ' segundos';
    } elseif ($totalSeconds < 3600) { // Menos de 1 hora
        $minutes = floor($totalSeconds / 60);
        $seconds = round($totalSeconds % 60);
        return $minutes . ' minuto' . ($minutes > 1 ? 's' : '') . ($seconds > 0 ? ', ' . $seconds . ' segundo' . ($seconds > 1 ? 's' : '') : '');
    } elseif ($totalSeconds < 86400) { // Menos de 1 dia
        $hours = floor($totalSeconds / 3600);
        $minutes = round(($totalSeconds % 3600) / 60);
        return $hours . ' hora' . ($hours > 1 ? 's' : '') . ($minutes > 0 ? ', ' . $minutes . ' minuto' . ($minutes > 1 ? 's' : '') : '');
    } elseif ($totalSeconds < 31536000) { // Menos de 1 ano
        $days = floor($totalSeconds / 86400);
        $hours = round(($totalSeconds % 86400) / 3600);
        return $days . ' dia' . ($days > 1 ? 's' : '') . ($hours > 0 ? ', ' . $hours . ' hora' . ($hours > 1 ? 's' : '') : '');
    } else { // 1 ano ou mais
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
        /* --- ESTILOS CSS GERAIS DA PÁGINA --- */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 1200px; /* Largura máxima do conteúdo */
            margin: auto; /* Centraliza o container */
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Sombra suave */
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* --- ESTILOS DA BARRA DE PESQUISA --- */
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

        /* --- ESTILOS DA TABELA --- */
        table {
            width: 100%; /* Ocupa toda a largura disponível */
            border-collapse: collapse; /* Remove espaçamento entre as células */
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ccc; /* Linha divisória inferior */
            text-align: left;
        }

        th {
            background-color: #007bff; /* Azul para o cabeçalho */
            color: white;
        }

        /* --- ESTILOS PARA OS STATUS DAS TAREFAS --- */
        .status-pendente {
            color: #ffc107; /* Amarelo/Laranja */
            font-weight: bold;
        }

        .status-concluido {
            color: #28a745; /* Verde */
            font-weight: bold;
        }

        /* --- ESTILOS DO BOTÃO DE EXCLUIR --- */
        .btn-delete {
            background-color: #dc3545; /* Vermelho */
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-delete:hover {
            background-color: #c82333; /* Vermelho mais escuro no hover */
        }

        /* --- ESTILOS PARA SELECTS (se houver, não usado neste código mas mantido) --- */
        select {
            padding: 5px;
            border-radius: 5px;
        }

        /* --- ESTILOS DO RODAPÉ DA TABELA (TOTAIZADORES) --- */
        tfoot td {
            font-weight: bold; /* Texto em negrito */
            border-top: 2px solid #007bff; /* Linha superior azul */
            background-color: #e9f5ff; /* Um fundo leve para os totais */
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
                    <th>Tempo Gasto</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php
                // Loop através de cada tarefa obtida da API
                foreach ($tarefas as $tarefa):
                    // Sanitiza os dados para evitar XSS e os armazena em variáveis.
                    $id = htmlspecialchars($tarefa['id']);
                    $titulo = htmlspecialchars($tarefa['titulo']);
                    $usuario = htmlspecialchars($tarefa['usuario']);
                    $isConcluido = (bool)$tarefa['status']; // Converte o status para booleano
                    $precoTarefa = isset($tarefa['preco']) ? (float)$tarefa['preco'] : 0.00; // Garante que o preço é um float

                    // Cria um objeto DateTime para a data de criação da tarefa.
                    $criadoEm = new DateTime($tarefa['criadoEm']);
                    $tempoGastoDisplay = 'N/A'; // Valor padrão para exibição na coluna "Tempo Gasto"
                    $tempoGastoEmSegundos = 0; // Inicializa o tempo em segundos para esta tarefa.

                    // --- LÓGICA PARA CALCULAR E EXIBIR O TEMPO GASTO INDIVIDUAL ---
                    if ($isConcluido) {
                        // Se a tarefa está concluída, calcula o tempo entre CriadoEm e ConcluidoEm.
                        if (isset($tarefa['concluidoEm']) && !empty($tarefa['concluidoEm'])) {
                            $concluidoEm = new DateTime($tarefa['concluidoEm']);
                            $interval = $criadoEm->diff($concluidoEm); // Calcula a diferença entre as datas
                            $tempoGastoDisplay = formatTimeDifference($interval); // Formata para exibição
                            $tempoGastoEmSegundos = dateIntervalToSeconds($interval); // Converte para segundos para o total
                            
                            // Acumula o tempo gasto APENAS para tarefas concluídas no total geral.
                            $tempoTotalConcluidoSegundos += $tempoGastoEmSegundos; 
                        } else {
                            $tempoGastoDisplay = 'Concluído (sem data de fim)'; // Caso esteja concluído, mas sem data de fim
                        }
                    } else {
                        // Se a tarefa está pendente, calcula o tempo entre CriadoEm e o momento atual.
                        $agora = new DateTime();
                        $interval = $criadoEm->diff($agora);
                        $tempoGastoDisplay = 'Pendente há ' . formatTimeDifference($interval);
                    }

                    // Define o texto e a classe CSS para o status da tarefa.
                    $statusTexto = $isConcluido ? 'Concluído' : 'Pendente';
                    $statusClass = $isConcluido ? 'status-concluido' : 'status-pendente';

                    // Soma o preço da tarefa ao total geral de preços.
                    $precoTotalGeral += $precoTarefa;
                ?>
                    <tr 
                        data-is-concluido="<?= $isConcluido ? 'true' : 'false' ?>" 
                        data-tempo-gasto-segundos="<?= $tempoGastoEmSegundos ?>">
                        <td><?= $id ?></td>
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
                        <td><?= $tempoGastoDisplay ?></td>
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
                    <td id="totalTempoConcluidoGeral">**<?= formatSecondsToReadableTime($tempoTotalConcluidoSegundos) ?>**</td>
                    <td></td> 
                </tr>
            </tfoot>
        </table>
    </div>

    <script>
        // --- FUNÇÃO JAVASCRIPT: Formata total de segundos em um formato legível ---
        // É uma versão da função PHP, reescrita em JS para uso no lado do cliente.
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
            } else if (totalSeconds < 31536000) { // Menos de um ano
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
            // Obtém o campo de entrada de pesquisa.
            input = document.getElementById("searchId");
            // Converte o texto de pesquisa para maiúsculas para comparação sem distinção de maiúsculas/minúsculas.
            filter = input.value.toUpperCase();
            // Obtém o corpo da tabela onde as tarefas são listadas.
            table = document.getElementById("taskTableBody");
            // Obtém todas as linhas da tabela.
            tr = table.getElementsByTagName("tr");

            var currentVisiblePriceTotal = 0; // Inicializa o total de preços para tarefas visíveis.
            var currentVisibleTimeTotalConcluido = 0; // Inicializa o total de tempo para tarefas visíveis e concluídas.

            // Loop através de todas as linhas da tabela.
            for (i = 0; i < tr.length; i++) {
                // Obtém as células de ID (primeira coluna) e Preço (quinta coluna) para a linha atual.
                tdId = tr[i].getElementsByTagName("td")[0];
                tdPrice = tr[i].getElementsByTagName("td")[4]; 
                
                // Obtém os atributos de dados 'data-is-concluido' e 'data-tempo-gasto-segundos' da linha.
                // Isso permite que o JS acesse diretamente o status e o tempo em segundos sem parsing de string.
                var isConcluido = tr[i].getAttribute('data-is-concluido') === 'true';
                var tempoGastoSegundos = parseFloat(tr[i].getAttribute('data-tempo-gasto-segundos')) || 0;

                // Verifica se as células de ID e Preço existem na linha.
                if (tdId && tdPrice) {
                    // Obtém o texto do ID da tarefa.
                    txtValue = tdId.textContent || tdId.innerText;
                    // Verifica se o texto do ID contém o filtro de pesquisa.
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = ""; // Exibe a linha (a torna visível).
                        
                        // Processa o preço da tarefa: remove "R$", substitui vírgula por ponto, remove espaços e converte para float.
                        var priceString = tdPrice.textContent.replace('R$', '').replace(',', '.').trim();
                        // Adiciona o preço da tarefa ao total de preços visíveis.
                        currentVisiblePriceTotal += parseFloat(priceString);

                        // Se a tarefa visível estiver concluída, adiciona seu tempo ao total de tempo visível.
                        if (isConcluido) {
                            currentVisibleTimeTotalConcluido += tempoGastoSegundos;
                        }

                    } else {
                        tr[i].style.display = "none"; // Esconde a linha (se não corresponder ao filtro).
                    }
                }
            }
            // Atualiza o texto da célula de preço total no rodapé da tabela.
            // O toFixed(2) garante duas casas decimais, e o replace('.', ',') formata para o padrão brasileiro.
            document.getElementById("totalPrecoGeral").textContent = "R$ " + currentVisiblePriceTotal.toFixed(2).replace('.', ',');
            // Atualiza o texto da célula de tempo total concluído no rodapé da tabela, usando a função de formatação JS.
            document.getElementById("totalTempoConcluidoGeral").textContent = formatSecondsToReadableTimeJS(currentVisibleTimeTotalConcluido); 
        }
    </script>

</body>

</html>