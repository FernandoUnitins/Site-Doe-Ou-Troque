<?php
session_start();
$pagina = 'pesquisar';

$host = "localhost";
$user = "root";
$pass = "seminario123";
$db = "seminario";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$nome = isset($_GET['nome']) ? "%" . $_GET['nome'] . "%" : "%";
$categoria = isset($_GET['categoria']) && $_GET['categoria'] !== "" ? $_GET['categoria'] : null;
$tipo = isset($_GET['tipo']) && $_GET['tipo'] !== "" ? $_GET['tipo'] : null;
$condicao = isset($_GET['condicao']) && $_GET['condicao'] !== "" ? $_GET['condicao'] : null;

$sql = "SELECT i.*, u.nome AS usuario_nome 
        FROM Item i 
JOIN Usuario u ON i.id_usuario = u.id_usuario 
WHERE i.nome LIKE ? 
  AND i.status = 'disponivel'
          AND NOT EXISTS (
              SELECT 1 FROM Solicitacao s 
              WHERE s.id_item = i.id_item AND s.status = 'aceito'
          )
          AND i.id_usuario != ?";

$params = [$nome, $_SESSION['id_usuario']];
$types = "si";

if ($categoria) {
    $sql .= " AND i.categoria = ?";
    $params[] = $categoria;
    $types .= "s";
}
if ($tipo) {
    $sql .= " AND i.tipo = ?";
    $params[] = $tipo;
    $types .= "s";
}
if ($condicao) {
    $sql .= " AND i.condicao = ?";
    $params[] = $condicao;
    $types .= "s";
}

$sql .= " ORDER BY i.data_cadastro DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Pesquisar Itens | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
    nav a.active {
      font-weight: bold;
      color: #D41010FF;
      border-bottom: 2px solid #000000FF;
    }
    header { display: flex; justify-content: space-between; align-items: center; padding: 2rem 5%; }
    nav a { margin: 0 1rem; text-decoration: none; color: #111; font-weight: 500; }
    .sign-up { background: #111; color: #fff; padding: 0.6rem 1.2rem; border-radius: 25px; text-decoration: none; }
    body { font-family: Arial, sans-serif; padding: 2rem; background-color: #f8f8f8; }
    h1 { text-align: center; color: #6c5ce7; }
    form { background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
    label { flex: 1 1 150px; display: flex; flex-direction: column; font-weight: bold; }
    input, select, button { padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc; font-size: 1rem; }
    button { background-color: #6c5ce7; color: white; border: none; cursor: pointer; }
    .item { display: flex; gap: 1rem; margin-bottom: 1rem; background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .item img { width: 150px; height: 150px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }
    .info { flex: 1; }
    .info h3 { margin: 0 0 0.5rem 0; color: #333; }
    .info p { margin: 0.3rem 0; }
    .info a { color: #6c5ce7; font-weight: bold; text-decoration: none; }
  </style>
</head>
<body>
<header>
  <img src="img/logo.png" alt="Logo" style="width: 200px; height: auto; margin-top: 20px;" />
  <nav>
    <a href="home.php">INÍCIO</a>
    <a href="perfil.php">PERFIL</a>
    <a href="cadastrar-item.php">CADASTRAR ITEM</a>
    <a href="gerenciar.php">GERENCIAR ITENS</a>
    <a href="pesquisar.php" class="active">PESQUISAR</a>
    <a href="minhas-solicitacoes.php">SOLICITAÇÕES</a>
    <a href="logout.php" class="sign-up">SAIR</a>
  </nav>
</header>

<h1>Pesquisar Itens</h1>
<form method="GET" action="pesquisar.php">
  <label for="nome">Nome:
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($_GET['nome'] ?? '') ?>" />
  </label>

  <label for="categoria">Categoria:
    <select id="categoria" name="categoria">
      <option value="">Todas</option>
      <?php
        $cats = ['Roupas','Móveis','Eletrodomésticos','Materiais Escolares'];
        foreach($cats as $c){
          $sel = (isset($_GET['categoria']) && $_GET['categoria'] === $c) ? 'selected' : '';
          echo "<option value='$c' $sel>$c</option>";
        }
      ?>
    </select>
  </label>

  <label for="tipo">Tipo:
    <select id="tipo" name="tipo">
      <option value="">Todos</option>
      <option value="doar" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'doar') ? 'selected' : '' ?>>Doar</option>
      <option value="trocar" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'trocar') ? 'selected' : '' ?>>Trocar</option>
    </select>
  </label>

  <label for="condicao">Condição:
    <select id="condicao" name="condicao">
      <option value="">Todas</option>
      <?php
        $conds = ['Novo','Usado','Velho'];
        foreach($conds as $c){
          $sel = (isset($_GET['condicao']) && $_GET['condicao'] === $c) ? 'selected' : '';
          echo "<option value='$c' $sel>$c</option>";
        }
      ?>
    </select>
  </label>

  <button type="submit">Pesquisar</button>
</form>

<?php if ($result->num_rows > 0): ?>
  <?php while($item = $result->fetch_assoc()): ?>
    <div class="item">
      <?php if ($item['imagem'] && file_exists($item['imagem'])): ?>
        <img src="<?= htmlspecialchars($item['imagem']) ?>" alt="Imagem do item">
      <?php else: ?>
        <img src="img/no-image.png" alt="Sem imagem">
      <?php endif; ?>
      <div class="info">
        <h3><?= htmlspecialchars($item['nome']) ?> (<?= htmlspecialchars($item['tipo']) ?>)</h3>
        <p><strong>Categoria:</strong> <?= htmlspecialchars($item['categoria']) ?></p>
        <p><strong>Condição:</strong> <?= htmlspecialchars($item['condicao']) ?></p>

        <p><a href="ver-item.php?id=<?= $item['id_item'] ?>">👁 VER DETALHES</a></p>
        </div>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p>Nenhum item encontrado.</p>
<?php endif; ?>

<?php $conn->close(); ?>
</body>
</html>
