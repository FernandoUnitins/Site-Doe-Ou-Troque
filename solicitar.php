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
$id_item = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_item <= 0) die("ID do item inválido.");

// Consulta item doado
$sql = "SELECT i.*, u.nome AS dono_nome, u.email AS dono_email FROM Item i
        JOIN Usuario u ON i.id_usuario = u.id_usuario
        WHERE i.id_item = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_item);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) die("Item não encontrado.");
$item = $result->fetch_assoc();

// Verifica se o usuário tem itens disponíveis para troca
$sql_disponiveis = "SELECT * FROM Item 
                   WHERE id_usuario = ?
                   AND id_item NOT IN (
                     SELECT id_item_proposto FROM Solicitacao
                     WHERE status IN ('pendente', 'aceito') AND id_item_proposto IS NOT NULL
                   )";
$stmt_disp = $conn->prepare($sql_disponiveis);
$stmt_disp->bind_param("i", $id_usuario);
$stmt_disp->execute();
$disponiveis = $stmt_disp->get_result();

// Formulário enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $mensagem = $_POST['mensagem'] ?? '';
  $id_item_proposto = isset($_POST['item_proposto']) ? intval($_POST['item_proposto']) : null;

  $sqlInsert = "INSERT INTO Solicitacao (id_item, id_solicitante, mensagem, id_item_proposto) 
                VALUES (?, ?, ?, ?)";
  $stmtInsert = $conn->prepare($sqlInsert);
  $stmtInsert->bind_param("iisi", $id_item, $id_usuario, $mensagem, $id_item_proposto);

  if ($stmtInsert->execute()) {
    echo "<p style='color:green; text-align:center;'>Solicitação enviada com sucesso!</p>";
    echo "<p style='text-align:center;'><a href='pesquisar.php'>Voltar à pesquisa</a></p>";
    exit;
  } else {
    echo "<p style='color:red; text-align:center;'>Erro ao enviar solicitação: " . $stmtInsert->error . "</p>";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Solicitar Item | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 2rem auto; background: #f8f8f8; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    h1, h2 { color: #6c5ce7; }
    label { display: block; margin-top: 1rem; font-weight: bold; }
    textarea, select { width: 100%; padding: 0.5rem; font-size: 1rem; border-radius: 6px; border: 1px solid #ccc; margin-top: 0.3rem; }
    button { margin-top: 1rem; background: #6c5ce7; color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 6px; cursor: pointer; font-size: 1rem; }
    .colunas { display: flex; gap: 2rem; margin-top: 1rem; }
    .coluna { flex: 1; background: #fff; padding: 1rem; border: 1px solid #ccc; border-radius: 6px; }
    img { max-width: 100%; border-radius: 8px; border: 1px solid #ddd; }
    p { margin: 0.3rem 0; }
    .voltar { display: inline-block; margin-top: 2rem; text-decoration: none; color: #6c5ce7; }
  </style>
</head>
<body>
  <h1>Solicitar Item</h1>

  <div class="colunas">
    <div class="coluna">
      <h2>Item desejado</h2>
      <p><strong><?= htmlspecialchars($item['nome']) ?> (<?= htmlspecialchars($item['tipo']) ?>)</strong></p>
      <img src="<?= !empty($item['imagem']) && file_exists($item['imagem']) ? $item['imagem'] : 'img/no-image.png' ?>" alt="Imagem">
      <p><strong>Descrição:</strong><br><?= nl2br(htmlspecialchars($item['descricao'])) ?></p>
    </div>

    <?php if ($item['tipo'] === 'trocar' && $disponiveis->num_rows > 0): ?>
      <div class="coluna">
        <h2>Escolher item para troca</h2>
        <form method="POST">
          <input type="hidden" name="mensagem" value="">
          <label for="item_proposto">Selecione um item seu:</label>
          <select name="item_proposto" required>
            <option value="">-- Escolha seu item --</option>
            <?php while ($meu = $disponiveis->fetch_assoc()): ?>
              <option value="<?= $meu['id_item'] ?>"><?= htmlspecialchars($meu['nome']) ?> (<?= $meu['categoria'] ?>)</option>
            <?php endwhile; ?>
          </select>
          <button type="submit">Confirmar Troca</button>
        </form>
      </div>
    <?php elseif ($item['tipo'] === 'trocar'): ?>
      <div class="coluna">
        <p>❌ Você não possui itens disponíveis para troca.</p>
        <a href="cadastrar-item.php">Cadastrar item</a>
      </div>
    <?php else: ?>
      <div class="coluna">
        <h2>Mensagem para o dono</h2>
        <form method="POST">
          <label for="mensagem">Mensagem:</label>
          <textarea name="mensagem" placeholder="Escreva sua mensagem..." required></textarea>
          <input type="hidden" name="item_proposto" value="">
          <button type="submit">Solicitar Doação</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <a href="javascript:history.back()" class="voltar">← Voltar</a>
</body>
</html>

<?php $conn->close(); ?>
