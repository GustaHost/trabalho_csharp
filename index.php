<?php

$tipoUsuario = 'admin';
$nomeUsuario = 'joao';

$response = file_get_contents("http://localhost:5093/api/tarefas/admin/joao");
$tarefas = json_decode($response, true);

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
    $result = file_get_contents($url, false, $context);

    echo "<div class='alert alert-success'>Tarefa $id excluída com sucesso!</div>";

    // Opcional: recarrega a página após exclusão
    header("Refresh:1");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mudar_status'])) {
    $id = $_POST['id_atualizar'];
    $tipoUsuario = $_POST['tipoUsuario'];
    $nomeUsuario = $_POST['nomeUsuario'];

    $url = "http://localhost:5093/api/tarefas/$id/status?tipoUsuario=$tipoUsuario&nomeUsuario=$nomeUsuario";

    $options = [
        'http' => [
            'method' => 'PUT',
            'header' => "Content-Type: application/json",
            'content' => '' // necessário mesmo vazio
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        echo "<div class='alert alert-danger'>Erro ao atualizar status.</div>";
    } else {
        echo "<div class='alert alert-success'>Status atualizado com sucesso.</div>";
        header("Refresh:1");
        exit;
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
            <label for="search">Pesquisar Título:</label>
            <input type="text" id="search" placeholder="Digite um título...">
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
            <tbody>
                <?php foreach ($tarefas as $tarefa): ?>
                    <?php
                    $id = htmlspecialchars($tarefa['id']);
                    $titulo = htmlspecialchars($tarefa['titulo']);
                    $usuario = htmlspecialchars($tarefa['usuario']);
                    $status = strtolower($tarefa['status']);
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
                                <button type="submit" name="mudar_status" class="btn btn-sm <?= $status === 'concluido' ? 'btn-success' : 'btn-warning' ?>">
                                    <?= $status === 'concluido' ? 'Concluído' : 'Pendente' ?>
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

</body>

</html>