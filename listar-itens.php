<?php
session_start();

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "seminario123";
$db = "seminario";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

// Pega todos os itens ordenados pela data de cadastro
$sql = "SELECT i.id_item, i.nome, i.tipo, i.categoria, i.condicao, i.endereco, i.imagem, u.nome AS usuario_nome
        FROM Item i
        JOIN Usuario u ON i.id_usuario = u.id_usuario
        ORDER BY i.data_cadastro DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Lista de Itens | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; }
    .item { border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; display: flex; gap: 1rem; }
    .item img { width: 150px; height: 150px; object-fit: cover; border-radius: 8px; }
    .item-info { flex: 1; }
    .item-info h3 { margin: 0; }
  </style>
</head>
<body>
  <h1>Itens cadastrados</h1>
  <?php
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo '<div class="item">';
      if ($row['imagem'] && file_exists($row['imagem'])) {
        echo '<img src="' . htmlspecialchars($row['imagem']) . '" alt="Imagem do item">';
      } else {
        echo '<img src="img/no-image.png" alt="Sem imagem">';
      }
      echo '<div class="item-info">';
      echo '<h3>' . htmlspecialchars($row['nome']) . ' (' . htmlspecialchars($row['tipo']) . ')</h3>';
      echo '<p><strong>Categoria:</strong> ' . htmlspecialchars($row['categoria']) . '</p>';
      echo '<p><strong>Condição:</strong> ' . htmlspecialchars($row['condicao']) . '</p>';
      echo '<p><strong>Endereço:</strong> ' . htmlspecialchars($row['endereco']) . '</p>';
      echo '<p><strong>Cadastrado por:</strong> ' . htmlspecialchars($row['usuario_nome']) . '</p>';
      echo '</div></div>';
    }
  } else {
    echo '<p>Nenhum item cadastrado.</p>';
  }
  $conn->close();
  ?>
</body>
</html>
