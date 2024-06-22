<?php
session_start();

$error = ''; // Variável para armazenar mensagens de erro

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifique se os campos foram enviados
    if (!isset($_POST['email'], $_POST['password'])) {
        $error = 'Preencha ambos os campos de email e senha!';
    } else {
        // Inclua a conexão com o banco de dados
        include '../db/conn.php';

        // Prepare a consulta SQL para verificar o usuário
        $email = $_POST['email'];
        $password = $_POST['password'];

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
                        break;
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