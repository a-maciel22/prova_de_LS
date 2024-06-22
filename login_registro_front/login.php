<?php
session_start();

// Inclui a conexão com o banco de dados
include '../db/conn.php';

// Variável para armazenar mensagens de erro
$error = '';

// Verifica se é uma solicitação POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se os campos foram enviados
    if (!isset($_POST['email'], $_POST['password'])) {
        $error = 'Preencha ambos os campos de email e senha!';
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Prepara a consulta SQL para verificar o usuário
        $stmt = $conn->prepare("SELECT id, name, email, password, role_id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // O usuário existe, verificar a senha
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Senha correta, iniciar sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role_id'] = $user['role_id'];

                // Redirecionar com base no papel do usuário
                switch ($user['role_id']) {
                    case 1: // Admin
                        header("Location: ../access_level/admin.php");
                        exit();
                        break;
                    case 2: // User
                        header("Location: ../access_level/user.php");
                        exit();
                        break;
                    default:
                        $error = "Papel de usuário desconhecido";
                }
            } else {
                // Senha incorreta
                $error = "Senha incorreta";
            }
        } else {
            // Usuário não encontrado
            $error = "Usuário não encontrado";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-purple-700">Login</h2>
    <?php if (!empty($error)): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-center">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
        <div>
            <label for="email" class="block text-sm font-medium text-purple-700">Email</label>
            <input type="email" name="email" id="email" required class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-purple-700">Senha</label>
            <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
        </div>
        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                Entrar
            </button>
        </div>
    </form>
    <p class="mt-6 text-center text-sm text-gray-600">
        Ainda não tem uma conta? <a href="../index.php" class="text-purple-600 hover:text-purple-500 font-medium">Registrar</a>
    </p>
</div>

</body>
</html>
