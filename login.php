<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "seminario123"; // sua senha do MySQL
$db = "seminario";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$email = $_POST['email'];
$senha = $_POST['password'];

$sql = "SELECT id_usuario, nome, senha FROM Usuario WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();
    if (password_verify($senha, $usuario['senha'])) {
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nome'] = $usuario['nome'];
        header("Location: home.php");
        exit;
    } else {
        echo "❌ Senha incorreta.";
    }
} else {
    echo "❌ Usuário não encontrado.";
}

$conn->close();
?>
