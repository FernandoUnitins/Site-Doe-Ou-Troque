<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  header("Location: login.html");
  exit;
}

$host = "localhost";
$user = "root";
$pass = "seminario123";
$db = "seminario";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$id = $_SESSION['id_usuario'];
$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$endereco = $_POST['endereco'];
$senha = $_POST['senha'];
$confirmar = $_POST['confirmar'];

if (!empty($senha)) {
  if ($senha !== $confirmar) {
    die("❌ As senhas não coincidem.");
  }
  $hash = password_hash($senha, PASSWORD_DEFAULT);
  $sql = "UPDATE Usuario SET nome=?, email=?, telefone=?, endereco=?, senha=? WHERE id_usuario=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssi", $nome, $email, $telefone, $endereco, $hash, $id);
} else {
  $sql = "UPDATE Usuario SET nome=?, email=?, telefone=?, endereco=? WHERE id_usuario=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssi", $nome, $email, $telefone, $endereco, $id);
}

if ($stmt->execute()) {
  echo "✅ Perfil atualizado com sucesso. <a href='perfil.php'>Voltar</a>";
} else {
  echo "Erro: " . $stmt->error;
}

$conn->close();
?>
