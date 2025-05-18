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

$sql = "SELECT s.*, i.nome AS item_nome, u.nome AS solicitante_nome
        FROM Solicitacao s
        JOIN Item i ON s.id_item = i.id_item
        JOIN Usuario u ON s.id_solicitante = u.id_usuario
        WHERE i.id_usuario = ?
        ORDER BY s.data_solicitacao DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Minhas Solicitações Recebidas</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
    body { font-family: Arial, sans-serif; padding: 2rem; background-color: #f8f8f8; }
    h1 { color: #6c5ce7; text-align: center; }
    table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    th, td { padding: 0.8rem; border: 1px solid #ddd; text-align: left; }
    th { background-color: #6c5ce7; color: white; }
    .status { text-transform: capitalize; font-weight: bold; }
    .status.pendente { color: orange; }
    .status.aceito { color: green; }
    .status.recusado { color: red; }
  </style>
</head>
<body>
  <h1>Solicitações Recebidas</h1>
  <?php if ($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Item</th>
          <th>Solicitante</th>
          <th>Mensagem</th>
          <th>Status</th>
          <th>Data</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['item_nome']) ?></td>
            <td><?= htmlspecialchars($row['solicitante_nome']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['mensagem'])) ?></td>
            <td class="status <?= $row['status'] ?>"><?= $row['status'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['data_solicitacao'])) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p style="text-align:center;">Nenhuma solicitação recebida até o momento.</p>
  <?php endif; ?>
</body>
</html>

<?php $conn->close(); ?>
