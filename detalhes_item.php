<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  header("Location: login.html");
  exit;
}

if (!isset($_GET['id'])) {
  die("ID do item não informado.");
}

$host = "localhost";
$user = "root";
$pass = "seminario123";
$db = "seminario";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$id_item = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

// Buscar item e usuário dono do item
$sql = "SELECT i.*, u.nome AS nome_usuario, u.email FROM Item i
        JOIN Usuario u ON i.id_usuario = u.id_usuario
        WHERE i.id_item = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_item);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Item não encontrado.");
}

$item = $result->fetch_assoc();

// Buscar itens disponíveis do usuário (sem solicitação pendente ou aceita) para troca, se for item do tipo trocar
$meus_itens = [];
if ($item['tipo'] === 'trocar') {
  $sqlDisponiveis = "SELECT * FROM Item 
                     WHERE id_usuario = ? 
                     AND id_item != ?
                     AND id_item NOT IN (
                        SELECT id_item FROM Solicitacao WHERE status IN ('pendente', 'aceito')
                     )";
  $stmtDisp = $conn->prepare($sqlDisponiveis);
  $stmtDisp->bind_param("ii", $id_usuario, $id_item);
  $stmtDisp->execute();
  $meus_itens = $stmtDisp->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Detalhes do Item | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f8f8;
      padding: 2rem;
    }
    .container {
      max-width: 700px;
      margin: auto;
      background: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #6c5ce7;
      margin-bottom: 1.5rem;
    }
    img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      margin-bottom: 1rem;
      border: 1px solid #ddd;
    }
    .info {
      line-height: 1.6;
    }
    .info strong {
      color: #333;
    }
    .back {
      display: block;
      text-align: center;
      margin-top: 2rem;
      text-decoration: none;
      background: #6c5ce7;
      color: white;
      padding: 0.7rem 1.5rem;
      border-radius: 8px;
      font-weight: bold;
      width: fit-content;
      margin-left: auto;
      margin-right: auto;
    }
    .back:hover {
      background: #5945d2;
    }
  </style>
</head>
<body>

<div class="container">
  <h1><?= htmlspecialchars($item['nome']) ?></h1>

  <?php if (!empty($item['imagem']) && file_exists($item['imagem'])): ?>
    <img src="<?= htmlspecialchars($item['imagem']) ?>" alt="Imagem do item">
  <?php else: ?>
    <img src="img/no-image.png" alt="Sem imagem disponível">
  <?php endif; ?>

  <div class="info">
    <p><strong>Tipo:</strong> 
      <span style="color: <?= $item['tipo'] === 'trocar' ? '#d35400' : '#27ae60' ?>;">
        <?= strtoupper($item['tipo']) ?>
      </span>
    </p>
    <p><strong>Categoria:</strong> <?= htmlspecialchars($item['categoria']) ?></p>
    <p><strong>Condição:</strong> <?= htmlspecialchars($item['condicao']) ?></p>
    <p><strong>Endereço:</strong> <?= htmlspecialchars($item['endereco']) ?></p>
    <p><strong>Descrição:</strong><br><?= nl2br(htmlspecialchars($item['descricao'])) ?></p>
    <p><strong>Observação:</strong><br><?= nl2br(htmlspecialchars($item['observacao'])) ?></p>
    <p><strong>Data de Cadastro:</strong> <?= date('d/m/Y H:i', strtotime($item['data_cadastro'])) ?></p>
    <p><strong>Cadastrado por:</strong> <?= htmlspecialchars($item['nome_usuario']) ?> (<?= htmlspecialchars($item['email']) ?>)</p>
  </div>

  <a href="javascript:history.back()" class="back">VOLTAR</a>
</div>

</body>
</html>

<?php $conn->close(); ?>
