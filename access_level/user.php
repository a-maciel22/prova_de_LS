<?php
// Iniciar ou retomar a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    // Se não estiver logado, redirecionar para a página de login
    header("Location: ../login_registro_front/login.php");
    exit();
}

// Incluir o arquivo de conexão com o banco de dados
include '../db/conn.php';

// Função para processar o logout
function logout() {
    // Destruir a sessão
    session_destroy();
    // Redirecionar para a página de login
    header("Location: ../login_registro_front/login.php");
    exit();
}

// Verificar se o logout foi solicitado
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Se o usuário confirmar o logout, executar a função de logout
    logout();
}

// Consulta SQL para selecionar todos os usuários com seus níveis de acesso
$sql = "SELECT users.id, users.name, users.email, access_level.role
        FROM users
        INNER JOIN access_level ON users.role_id = access_level.role_id";
$result = $conn->query($sql);

// Array para armazenar os resultados da consulta
$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página do Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Estilos CSS adicionais para o modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 8px;
        }

        .modal-button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-8">
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-purple-700">Página do Usuário</h2>

        <!-- Informações do Usuário Logado -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Bem-vindo, <?= htmlspecialchars($_SESSION['username']); ?></h3>
        </div>

        <!-- Tabela de Usuários -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Lista de Usuários</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível de Acesso</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['role']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Botão de Logout -->
        <div class="text-center">
            <button onclick="openModal()" class="inline-block px-6 py-3 bg-red-500 text-white rounded-md shadow-sm hover:bg-red-600">
                Logout
            </button>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <p class="text-xl font-semibold mb-4">Tem certeza de que deseja sair?</p>
        <div class="modal-button-container">
            <button onclick="logout()" class="px-4 py-2 bg-red-500 text-white rounded-md shadow-sm hover:bg-red-600">Sim, sair</button>
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md shadow-sm hover:bg-gray-400">Cancelar</button>
        </div>
    </div>
</div>

<script>
    // Função para abrir o modal
    function openModal() {
        document.getElementById('logoutModal').style.display = 'block';
    }

    // Função para fechar o modal
    function closeModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Função para realizar o logout
    function logout() {
        window.location.href = 'user.php?action=logout';
    }
</script>

</body>
</html>
