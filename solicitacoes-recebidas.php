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

// Atualização de status e resposta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_solicitacao'], $_POST['novo_status'])) {
  $id_solicitacao = (int)$_POST['id_solicitacao'];
  $novo_status = $_POST['novo_status'];
  $resposta = trim($_POST['resposta'] ?? '');

  // Atualiza o status e resposta da solicitação
  $stmt = $conn->prepare("UPDATE Solicitacao SET status = ?, resposta = ? WHERE id_solicitacao = ?");
  $stmt->bind_param("ssi", $novo_status, $resposta, $id_solicitacao);
  $stmt->execute();
  $stmt->close();

  // Se foi aceita, atualiza o status dos dois itens para 'indisponível'
  if ($novo_status === 'aceito') {
    $stmt = $conn->prepare("SELECT id_item, id_item_proposto FROM Solicitacao WHERE id_solicitacao = ?");
    $stmt->bind_param("i", $id_solicitacao);
    $stmt->execute();
    $stmt->bind_result($id_item, $id_item_proposto);
    $stmt->fetch();
    $stmt->close();
  
    if ($id_item) {
      $stmt = $conn->prepare("UPDATE Item SET status = 'indisponível' WHERE id_item = ?");
      $stmt->bind_param("i", $id_item);
      $stmt->execute();
      $stmt->close();
    }
  
    if ($id_item_proposto) {
      $stmt = $conn->prepare("UPDATE Item SET status = 'indisponível' WHERE id_item = ?");
      $stmt->bind_param("i", $id_item_proposto);
      $stmt->execute();
      $stmt->close();
    }
  }
  
}

// Buscar solicitações recebidas
$sql = "SELECT 
          s.*, 
          i.nome AS nome_item, 
          i.imagem, 
          i.id_usuario AS dono_item, 
          u.nome AS nome_solicitante, 
          s.mensagem, 
          s.resposta,
          ip.nome AS nome_item_proposto,
          ip.imagem AS imagem_item_proposto
        FROM Solicitacao s
        JOIN Item i ON s.id_item = i.id_item
        JOIN Usuario u ON s.id_solicitante = u.id_usuario
        LEFT JOIN Item ip ON s.id_item_proposto = ip.id_item
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
  <meta charset="UTF-8">
  <title>Solicitações Recebidas</title>
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
    form {
      margin-top: 1rem;
    }
    select, button, textarea {
      padding: 0.4rem 0.8rem;
      border-radius: 6px;
      border: 1px solid #ccc;
      margin-top: 0.3rem;
      font-size: 1rem;
    }
    button {
      background: #328718FF;
      color: white;
      border: none;
      font-weight: bold;
      cursor: pointer;
      margin-top: 1rem;
    }
    h3 {
      margin: 0 0 0.5rem;
      color: #333;
    }
    p {
      margin: 0.3rem 0;
      font-size: 0.95rem;
    }
    .nenhuma {
      text-align: center;
      font-size: 1.1rem;
      color: #000000FF;
      margin-top: 3rem;
    }
    .nada {
      text-align: center;
      font-size: 2rem;
      color: #6c5ce7;
      margin-top: 3rem;
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
  </style>
</head>
<body>

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

        <p><strong>👤 Solicitante:</strong> <?= htmlspecialchars($row['nome_solicitante']) ?></p>
        <p><strong>📆 Data da Solicitação:</strong> <?= date('d/m/Y H:i', strtotime($row['data_solicitacao'])) ?></p>  
        <p><strong>📩 Mensagem do solicitante:</strong> <?= nl2br(htmlspecialchars($row['mensagem'])) ?></p>

        <?php if (!empty($row['nome_item_proposto'])): ?>
          <p>_________________________________________________</p>
          <p><h3><strong>🔁 ITEM OFERECIDO EM TROCA:</strong><br></h3></p>
          <div class="item-proposto">
            <img src="<?= (!empty($row['imagem']) && file_exists($row['imagem'])) ? htmlspecialchars($row['imagem']) : 'img/no-image.png' ?>" alt="Imagem do item">
            <h1>🔁</h1>
            <img src="<?= (!empty($row['imagem_item_proposto']) && file_exists($row['imagem_item_proposto'])) ? htmlspecialchars($row['imagem_item_proposto']) : 'img/no-image.png' ?>" alt="Item proposto">
            <p><strong>🔖 Item proposto em troca:</strong><br><?= htmlspecialchars($row['nome_item_proposto']) ?></p>
            <a href="detalhes_item.php?id=<?= $row['id_item_proposto'] ?>" style="padding:0.4rem 0.8rem; background:#6c5ce7; color:#fff; border-radius:6px; text-decoration:none; font-weight:bold;">Ver item</a>
          </div>
          <p>_________________________________________________</p>
        <?php endif; ?>

        <?php if ($row['status'] === 'pendente'): ?>
          <form method="POST">
            <input type="hidden" name="id_solicitacao" value="<?= $row['id_solicitacao'] ?>">
            <label>Mensagem para o solicitante:</label><br>
            <textarea name="resposta" rows="3" style="width:40%;"><?= htmlspecialchars($row['resposta'] ?? '') ?></textarea>
            <p>
            <label>Status:
              <select name="novo_status">
                <option value="pendente" selected>Pendente</option>
                <option value="aceito">Aceito</option>
                <option value="recusado">Recusado</option>
              </select>
            </label>
            <br><br>
            <button type="submit">ATUALIZAR STATUS</button>
          </form>
        <?php else: ?>
          <?php if (!empty($row['resposta'])): ?>
            <p><strong>💬 Mensagem enviada para o solicitante:</strong> <?= nl2br(htmlspecialchars($row['resposta'])) ?></p>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p class="nada"><strong>Solicitações Recebidas</strong></p> 
  <p class="nenhuma">Nenhuma solicitação recebida até o momento.</p>
<?php endif; ?>

<?php $conn->close(); ?>
</body>
</html>
