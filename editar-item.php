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
$sql = "SELECT * FROM Item WHERE id_item = ? AND id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_item, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Item não encontrado ou você não tem permissão para editar.");
}

$item = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $categoria = $_POST['categoria'];
    $condicao = $_POST['condicao'];
    $endereco = $_POST['endereco'];
    $observacao = $_POST['observacao'];

    // Tratamento da imagem (opcional)
    $imagem = $item['imagem'];
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagem']['tmp_name'];
        $fileName = $_FILES['imagem']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploads/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imagem = $dest_path;
                // Opcional: deletar a imagem antiga
                if ($item['imagem'] && file_exists($item['imagem'])) {
                    unlink($item['imagem']);
                }
            } else {
                echo "<p style='color:red;'>Erro ao mover o arquivo da imagem.</p>";
            }
        } else {
            echo "<p style='color:red;'>Tipo de arquivo não permitido.</p>";
        }
    }

    $sqlUpdate = "UPDATE Item SET nome=?, tipo=?, descricao=?, categoria=?, condicao=?, endereco=?, observacao=?, imagem=? WHERE id_item=? AND id_usuario=?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssssssssii", $nome, $tipo, $descricao, $categoria, $condicao, $endereco, $observacao, $imagem, $id_item, $id_usuario);
    if ($stmtUpdate->execute()) {
        echo "<p style='color:green; text-align:center;'>ITEM ATUALIZADO COM SUCESSO.</p>";
        // Atualiza os dados para exibir no formulário
        $item = array_merge($item, [
            'nome' => $nome,
            'tipo' => $tipo,
            'descricao' => $descricao,
            'categoria' => $categoria,
            'condicao' => $condicao,
            'endereco' => $endereco,
            'observacao' => $observacao,
            'imagem' => $imagem,
        ]);
    } else {
        echo "<p style='color:red; text-align:center;'>Erro: " . $stmtUpdate->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Editar Item | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
    body {
      font-family: Arial, sans-serif;
      max-width: 700px;
      margin: 2rem auto;
      background: #f9f9f9;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #6c5ce7;
    }
    form label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: bold;
      margin-top: 1rem;
    }
    form input[type="text"],
    form select,
    form textarea,
    form input[type="file"] {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
      box-sizing: border-box;
    }
    form textarea {
      resize: vertical;
      min-height: 80px;
    }
    button {
      margin-top: 1.5rem;
      background-color: #6c5ce7;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      font-size: 1.1rem;
      border-radius: 6px;
      cursor: pointer;
      display: block;
      width: 100%;
    }
    img.current-image {
      max-width: 100%;
      max-height: 250px;
      display: block;
      margin-top: 0.5rem;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    .back-link {
      display: inline-block;
      margin-top: 1.5rem;
      text-decoration: none;
      color: #6c5ce7;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <h1>Editar Item</h1>
  <form method="POST" enctype="multipart/form-data">
    <label for="nome">Nome</label>
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($item['nome']) ?>" required />

    <label for="tipo">Tipo</label>
    <select id="tipo" name="tipo" required>
      <option value="doar" <?= $item['tipo'] === 'doar' ? 'selected' : '' ?>>Doar</option>
      <option value="trocar" <?= $item['tipo'] === 'trocar' ? 'selected' : '' ?>>Trocar</option>
    </select>

    <label for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao" required><?= htmlspecialchars($item['descricao']) ?></textarea>

    <label for="categoria">Categoria</label>
    <select id="categoria" name="categoria" required>
      <?php
        $cats = ['Roupas','Móveis','Eletrodomésticos','Materiais Escolares'];
        foreach($cats as $c){
          $sel = ($item['categoria'] === $c) ? 'selected' : '';
          echo "<option value=\"$c\" $sel>$c</option>";
        }
      ?>
    </select>

    <label for="condicao">Condição</label>
    <select id="condicao" name="condicao" required>
      <?php
        $conds = ['Novo','Usado','Velho'];
        foreach($conds as $c){
          $sel = ($item['condicao'] === $c) ? 'selected' : '';
          echo "<option value=\"$c\" $sel>$c</option>";
        }
      ?>
    </select>

    <label for="endereco">Endereço</label>
    <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($item['endereco']) ?>" required />

    <label for="observacao">Observação</label>
    <textarea id="observacao" name="observacao"><?= htmlspecialchars($item['observacao']) ?></textarea>

    <label>Imagem atual:</label>
    <?php if ($item['imagem'] && file_exists($item['imagem'])): ?>
      <img src="<?= htmlspecialchars($item['imagem']) ?>" alt="Imagem do item" class="current-image" />
    <?php else: ?>
      <p>Sem imagem</p>
    <?php endif; ?>

    <label for="imagem">Nova Imagem</label>
    <input type="file" id="imagem" name="imagem" />

    <button type="submit">SALVAR</button>
  </form>
  <a href="gerenciar.php" class="back-link">&larr; VOLTAR</a>
</body>
</html>

<?php $conn->close(); ?>
