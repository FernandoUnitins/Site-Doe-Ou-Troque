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
if ($conn->connect_error) die("Erro na conexão: " . $conn->connect_error);

$id_usuario = $_SESSION['id_usuario'];

// Editar ou excluir
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST['editar']) && isset($_POST['nova_mensagem'], $_POST['id_solicitacao'])) {
    $msg = $_POST['nova_mensagem'];
    $id_solicitacao = intval($_POST['id_solicitacao']);
    $sql = "UPDATE Solicitacao SET mensagem = ? WHERE id_solicitante = ? AND id_solicitacao = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $msg, $id_usuario, $id_solicitacao);
    $stmt->execute();
  } elseif (isset($_POST['cancelar'], $_POST['id_solicitacao'])) {
    $id_solicitacao = intval($_POST['id_solicitacao']);
    $sql = "DELETE FROM Solicitacao WHERE id_solicitante = ? AND id_solicitacao = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_usuario, $id_solicitacao);
    $stmt->execute();
  }
}

// Buscar solicitações enviadas com itens e itens propostos
$sql = "SELECT 
          s.*, 
          i.nome AS nome_item, 
          i.imagem, 
          u.nome AS nome_dono_item,
          ip.nome AS nome_item_proposto,
          ip.imagem AS imagem_item_proposto
        FROM Solicitacao s
        JOIN Item i ON s.id_item = i.id_item
        JOIN Usuario u ON i.id_usuario = u.id_usuario
        LEFT JOIN Item ip ON s.id_item_proposto = ip.id_item
        WHERE s.id_solicitante = ?
        ORDER BY s.data_solicitacao DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Solicitações Enviadas</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f8f8;
      padding: 2rem;
    }

    .solicitacao {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      margin-bottom: 1.5rem;
      display: flex;
      gap: 1.5rem;
      align-items: flex-start;
    }

    .solicitacao img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .info {
      flex: 1;
    }

    .status {
      display: inline-block;
      padding: 0.4rem 0.8rem;
      border-radius: 12px;
      font-weight: bold;
      text-transform: capitalize;
      font-size: 0.9rem;
    }

    .status.pendente {
      background-color: #ffeaa7;
      color: #e17055;
    }

    .status.aceito {
      background-color: #dff9fb;
      color: #0984e3;
    }

    .status.recusado {
      background-color: #fab1a0;
      color: #d63031;
    }

    textarea, select, button {
      margin-top: 0.5rem;
      padding: 0.5rem;
      font-size: 1rem;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    button {
      background: #6c5ce7;
      color: white;
      border: none;
      cursor: pointer;
      margin-right: 0.5rem;
    }

    .item-proposto {
      margin-top: 1rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .item-proposto img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .nenhuma {
      text-align: center;
      font-size: 1.2rem;
      margin-top: 3rem;
    }

    .titulo {
      font-size: 2rem;
      color: #6c5ce7;
      text-align: center;
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>

<h1 class="titulo">Solicitações Enviadas</h1>

<?php if ($result->num_rows > 0): ?>
  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="solicitacao">
      <div class="info">
        <strong>STATUS DA SOLICITAÇÃO:</strong>
        <span class="status <?= $row['status'] ?>">
          <?php
            switch ($row['status']) {
              case 'aceito': echo '✅ Aceito'; break;
              case 'recusado': echo '❌ Recusado'; break;
              default: echo '⌛ ' . ucfirst($row['status']);
            }
          ?>
        </span>

        <p>_________________________________________________</p>
        <h3>🏷️ <strong><?= htmlspecialchars($row['nome_item']) ?></strong></h3>
        <img src="<?= (!empty($row['imagem']) && file_exists($row['imagem'])) ? htmlspecialchars($row['imagem']) : 'img/no-image.png' ?>" alt="Imagem do item">

        <p><strong>👤 Dono do item:</strong> <?= htmlspecialchars($row['nome_dono_item']) ?></p>
        <p><strong>📆 Data da Solicitação:</strong> <?= date('d/m/Y H:i', strtotime($row['data_solicitacao'])) ?></p>  
        <p><strong>📩 Sua mensagem:</strong> <?= nl2br(htmlspecialchars($row['mensagem'])) ?></p>

        <?php if (!empty($row['nome_item_proposto'])): ?>
          <p>_________________________________________________</p>
          <h3><strong>🔁 ITEM QUE VOCÊ OFERECEU:</strong></h3>
          <div class="item-proposto">
            <img src="<?= (!empty($row['imagem_item_proposto']) && file_exists($row['imagem_item_proposto'])) ? htmlspecialchars($row['imagem_item_proposto']) : 'img/no-image.png' ?>" alt="Item proposto">
            <p><strong>🔖 Nome:</strong><br><?= htmlspecialchars($row['nome_item_proposto']) ?></p>
            <a href="detalhes_item.php?id=<?= $row['id_item_proposto'] ?>" style="padding:0.4rem 0.8rem; background:#6c5ce7; color:#fff; border-radius:6px; text-decoration:none; font-weight:bold;">Ver item</a>
          </div>
        <?php endif; ?>

        <?php if (!empty($row['resposta'])): ?>
          <p><strong>💬 Resposta do dono do item:</strong> <?= nl2br(htmlspecialchars($row['resposta'])) ?></p>
        <?php endif; ?>

        <?php if ($row['status'] === 'pendente'): ?>
          <form method="POST">
            <input type="hidden" name="id_solicitacao" value="<?= $row['id_solicitacao'] ?>">
            <label>Editar sua mensagem:</label><br>
            <textarea name="nova_mensagem"><?= htmlspecialchars($row['mensagem']) ?></textarea><br>
            <button type="submit" name="editar">Salvar</button>
            <button type="submit" name="cancelar" style="background:red;">Cancelar</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p class="nenhuma">Você ainda não fez nenhuma solicitação.</p>
<?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
