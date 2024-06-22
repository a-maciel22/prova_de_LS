<?php
session_start();

include 'db/conn.php';

$error = '';
$success = '';
$username = ''; // Inicializa a variável $username para garantir que seja definida

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
        $error = 'Todos os campos são obrigatórios';
    } else {
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
                $success = 'Usuário registrado com sucesso';
                $_SESSION['success_message'] = $success; // Armazena a mensagem de sucesso na sessão
                $_SESSION['username'] = $username; // Armazena temporariamente o nome de usuário na sessão

                echo '<script>
                setTimeout(function() {
                    window.location.href = "./login_registro_front/login.php";
                }, 3000);
              </script>';

            } else {
                throw new Exception('Erro ao registrar usuário: ' . $stmt->error);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
} else {
    // Se não for uma solicitação POST, defina $username como vazio para limpar o campo
    $username = '';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-purple-700">Registrar</h2>

    <?php if (!empty($error)): ?>
        <div id="alert" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-center">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div id="success" class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-center">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
        <div>
            <label for="username" class="block text-sm font-medium text-purple-700">Nome de Usuário</label>
            <input type="text" name="username" id="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-purple-700">Email</label>
            <input type="email" name="email" id="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-purple-700">Senha</label>
            <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
        </div>
        <div>
            <label for="confirm_password" class="block text-sm font-medium text-purple-700">Confirme a Senha</label>
            <input type="password" name="confirm_password" id="confirm_password" required class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
        </div>
        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                Registrar
            </button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Já tem uma conta? <a href="./login_registro_front/login.php" class="text-purple-600 hover:text-purple-500 font-medium">Login</a>
    </p>
</div>

</body>


</html>
