<?php
session_start();

// Verificar se o usuário está autenticado como admin
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login_registro_front/login.php");
    exit();
}

include '../db/conn.php';

$error = '';
$success = '';

// Adicionar usuário
if (isset($_POST['addUser'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        if ($password != $confirm_password) {
            throw new Exception('As senhas não coincidem');
        }

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Email já registrado');
        }

        $role_id = 2;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);

        if ($stmt->execute()) {
            $success = 'Usuário adicionado com sucesso';
        } else {
            throw new Exception('Erro ao adicionar usuário: ' . $stmt->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Atualizar usuário
if (isset($_POST['editUser'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];

    try {
        $sql = "UPDATE users SET name = ?, email = ?, role_id = ? WHERE id = ? AND role_id != 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $username, $email, $role_id, $id);

        if ($stmt->execute()) {
            $success = 'Usuário atualizado com sucesso';
        } else {
            throw new Exception('Erro ao atualizar usuário: ' . $stmt->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Deletar usuário
if (isset($_POST['deleteUser'])) {
    $id = $_POST['id'];

    try {
        $sql = "DELETE FROM users WHERE id = ? AND role_id != 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($id == $_SESSION['user_id']) {
                session_destroy();
                header("Location: ../login_registro_front/login.php");
                exit();
            }
            $success = 'Usuário deletado com sucesso';
        } else {
            throw new Exception('Erro ao deletar usuário: ' . $stmt->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Buscar todos os usuários não-admins
$sql = "SELECT * FROM users WHERE role_id != 1";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerenciar Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-12">
        <div class="flex justify-between items-center mb-6">
             <!-- Informações do Usuário Logado -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Bem-vindo(a), <?= htmlspecialchars($_SESSION['username']); ?></h3>
        </div>
            <h2 class="text-2xl font-bold text-purple-700">Gerenciar Usuários</h2>
            <button onclick="openLogoutModal()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</button>
        </div>
        <?php if (!empty($error)): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-center">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <div class="mb-6 text-right">
            <button onclick="openModal('addUserModal')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Adicionar Usuário</button>
        </div>
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead>
            <tr>
                <th class="py-2 px-4 bg-gray-200 text-left">ID</th>
                <th class="py-2 px-4 bg-gray-200 text-left">Nome</th>
                <th class="py-2 px-4 bg-gray-200 text-left">Email</th>
                <th class="py-2 px-4 bg-gray-200 text-left">Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['id']); ?></td>
                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td class="py-2 px-4 border-b">
                        <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['email']); ?>', <?php echo $row['role_id']; ?>)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded">Editar</button>
                        <button onclick="openDeleteModal(<?php echo $row['id']; ?>)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">Deletar</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de Adicionar Usuário -->
    <div id="addUserModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-center text-purple-700">Adicionar Usuário</h2>
            <form method="post" action="">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">Nome de Usuário</label>
                    <input type="text" name="username" id="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                    <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmar Senha</label>
                    <input type="password" name="confirm_password" id="confirm_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="flex justify-between">
                    <button type="button" onclick="closeModal('addUserModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Fechar</button>
                    <button type="submit" name="addUser" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Adicionar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Editar Usuário -->
    <div id="editUserModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-center text-purple-700">Editar Usuário</h2>
            <form method="post" action="">
                <input type="hidden" name="id" id="edit_user_id">
                <div class="mb-4">
                    <label for="edit_username" class="block text-sm font-medium text-gray-700">Nome de Usuário</label>
                    <input type="text" name="username" id="edit_username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="edit_email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <input type="hidden" name="role_id" id="edit_role_id">
                <div class="flex justify-between">
                    <button type="button" onclick="closeModal('editUserModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Fechar</button>
                    <button type="submit" name="editUser" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Deletar Usuário -->
    <div id="deleteUserModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-center text-purple-700">Deletar Usuário</h2>
            <form method="post" action="">
                <input type="hidden" name="id" id="delete_user_id">
                <p class="mb-4 text-center">Tem certeza que deseja deletar este usuário?</p>
                <div class="flex justify-between">
                    <button type="button" onclick="closeModal('deleteUserModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancelar</button>
                    <button type="submit" name="deleteUser" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Deletar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Logout -->
    <div id="logoutModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-center text-purple-700">Logout</h2>
            <p class="mb-4 text-center">Tem certeza que deseja sair?</p>
            <div class="flex justify-between">
                <button type="button" onclick="closeModal('logoutModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancelar</button>
                <a href="../logout/logout.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Sair</a>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function openEditModal(id, username, email, role_id) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role_id').value = role_id;
            openModal('editUserModal');
        }

        function openDeleteModal(id) {
            document.getElementById('delete_user_id').value = id;
            openModal('deleteUserModal');
        }

        function openLogoutModal() {
            openModal('logoutModal');
        }
    </script>
</body>
</html>
