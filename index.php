<?php

$tipoUsuario = 'admin';
$nomeUsuario = 'joao'; // Defina o usuário logado de forma mais robusta em uma aplicação real

// --- PARTE CRÍTICA: COMO VOCÊ ESTÁ BUSCANDO AS TAREFAS INICIALMENTE ---
// Manter esta linha como está, pois corresponde ao seu [HttpGet] na API C#
$response = file_get_contents("http://localhost:5093/api/tarefas/$tipoUsuario/$nomeUsuario");

$tarefas = json_decode($response, true);

// Adicione aqui uma verificação básica se $tarefas foi decodificado corretamente
if (!is_array($tarefas)) {
    $tarefas = []; // Garante que $tarefas é um array para o loop foreach
    // Você pode querer exibir uma mensagem de erro mais proeminente aqui
    // echo "<div class='alert alert-danger'>Erro ao carregar tarefas iniciais.</div>";
}


// --- LÓGICA DE EXCLUSÃO (Está correta) ---
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
    $result = @file_get_contents($url, false, $context); // Usar @ para suprimir warnings e tratar com if ($result === false)

    if ($result === false) {
        $error = error_get_last();
        echo "<div class='alert alert-danger'>Erro ao excluir tarefa: " . ($error ? $error['message'] : 'Erro desconhecido') . "</div>";
    } else {
        echo "<div class='alert alert-success'>Tarefa $id excluída com sucesso!</div>";
    }

    header("Refresh:0"); // Recarrega a página IMEDIATAMENTE após a exclusão
    exit;
}


// --- LÓGICA DE MUDAR STATUS (Foco Principal da Correção) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mudar_status'])) {
    $id = $_POST['id_atualizar'];
    $tipoUsuario = $_POST['tipoUsuario'];
    $nomeUsuario = $_POST['nomeUsuario'];

    $url = "http://localhost:5093/api/tarefas/$id/status?tipoUsuario=$tipoUsuario&nomeUsuario=$nomeUsuario";

    $options = [
        'http' => [
            'method' => 'PUT',
            'header' => "Content-Type: application/json",
            'content' => '' // Necessário mesmo vazio para PUT
        ]
    ];

    $context = stream_context_create($options);
    $response_api = @file_get_contents($url, false, $context); // Usar @ para suprimir warnings

    if ($response_api === false) {
        $error = error_get_last();
        echo "<div class='alert alert-danger'>Erro ao atualizar status: " . ($error ? $error['message'] : 'Erro desconhecido') . "</div>";
    } else {
        // Se a API retornar sucesso, a mensagem de sucesso será exibida.
        echo "<div class='alert alert-success'>Status atualizado com sucesso.</div>";
        // Redireciona para a própria página para forçar a recarga dos dados atualizados
        header("Location: " . $_SERVER['PHP_SELF']);
        exit; // Impede que o restante do script seja executado após o redirecionamento
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
            max-width: 1000px;
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

        /* Estilos para o status */
        .status-pendente {
            color: #ffc107;
            font-weight: bold;
        }

        .status-concluido {
            color: #28a745;
            font-weight: bold;
        }

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
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php foreach ($tarefas as $tarefa): ?>
                    <?php
                    $id = htmlspecialchars($tarefa['id']);
                    $titulo = htmlspecialchars($tarefa['titulo']);
                    $usuario = htmlspecialchars($tarefa['usuario']);

                    $isConcluido = (bool)$tarefa['status']; // Converte explicitamente para booleano

                    $statusTexto = $isConcluido ? 'Concluído' : 'Pendente';
                    $statusClass = $isConcluido ? 'status-concluido' : 'status-pendente';
                    ?>
                    <tr>
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
        </table>
    </div>

    <script>
        function filterTable() {
            // Pega o valor digitado no campo de pesquisa
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchId");
            filter = input.value.toUpperCase(); // Converte para maiúsculas para comparação (útil se o ID for alfanumérico)
            table = document.getElementById("taskTableBody"); // Pega o corpo da tabela
            tr = table.getElementsByTagName("tr"); // Pega todas as linhas da tabela

            // Percorre todas as linhas da tabela
            for (i = 0; i < tr.length; i++) {
                // A primeira célula (índice 0) contém o ID
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText; // Pega o texto do ID na célula
                    // Se o ID da linha começar com o que foi digitado, exibe a linha
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = ""; // Exibe a linha
                    } else {
                        tr[i].style.display = "none"; // Esconde a linha
                    }
                }
            }
        }
    </script>

</body>

</html>