<?php
$host = "localhost";
$db = "seminario"; // substitua pelo nome do seu banco
$user = "root"; // ou outro usuário MySQL
$pass = "seminario123"; // sua senha do MySQL

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$email = $_POST['email'];
$nome = $_POST['name'];
$telefone = $_POST['phone'];
$endereco = $_POST['address'];
$senha = password_hash($_POST['password'], PASSWORD_DEFAULT);
$confirmar = $_POST['confirm_password'];

if ($_POST['password'] !== $_POST['confirm_password']) {
    die("As senhas não coincidem.");
}

$sql = "INSERT INTO Usuario (nome, email, telefone, endereco, senha) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nome, $email, $telefone, $endereco, $senha);

if ($stmt->execute()) {
    echo "Cadastro realizado com sucesso. <a href='login.html'>Fazer login</a>";
} else {
    echo "Erro: " . $stmt->error;
}

$conn->close();
?>
