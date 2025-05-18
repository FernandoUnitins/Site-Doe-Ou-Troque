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

$id_usuario = $_SESSION['id_usuario'];
$id_item = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_item <= 0) {
    die("ID inválido.");
}

// Verifica se o item pertence ao usuário
$sql = "SELECT imagem FROM Item WHERE id_item = ? AND id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_item, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Item não encontrado ou você não tem permissão para excluir.");
}

$row = $result->fetch_assoc();
if ($row['imagem'] && file_exists($row['imagem'])) {
    unlink($row['imagem']); // remove arquivo da imagem
}

$sqlDelete = "DELETE FROM Item WHERE id_item = ? AND id_usuario = ?";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->bind_param("ii", $id_item, $id_usuario);
if ($stmtDelete->execute()) {
    header("Location: gerenciar.php?msg=excluido");
    exit;
} else {
    echo "Erro ao excluir: " . $stmtDelete->error;
}

$conn->close();
?>
