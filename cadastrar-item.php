<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  header("Location: login.html");
  exit;
}
$pagina = 'cadastrar'; // ← identifica a página atual

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $host = "localhost";
  $user = "root";
  $pass = "seminario123";
  $db = "seminario";

  $conn = new mysqli($host, $user, $pass, $db);
  if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
  }

  $id_usuario = $_SESSION['id_usuario'];
  $nome = $_POST['nome'];
  $tipo = $_POST['tipo'];
  $descricao = $_POST['descricao'];
  $categoria = $_POST['categoria'];
  $condicao = $_POST['condicao'];
  $endereco = $_POST['endereco'];
  $observacao = $_POST['observacao'];

  $imagem = '';

  // Tratamento do upload da imagem
  if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['imagem']['tmp_name'];
    $fileName = $_FILES['imagem']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Extensões permitidas
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
      } else {
        echo "<p style='color:red; text-align:center;'>Erro ao mover o arquivo da imagem.</p>";
      }
    } else {
      echo "<p style='color:red; text-align:center;'>Tipo de arquivo não permitido. Use jpg, jpeg, png ou gif.</p>";
    }
  }

  $sql = "INSERT INTO Item (id_usuario, nome, tipo, descricao, categoria, condicao, endereco, observacao, imagem)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("issssssss", $id_usuario, $nome, $tipo, $descricao, $categoria, $condicao, $endereco, $observacao, $imagem);

  if ($stmt->execute()) {
    echo "<p style='text-align:center; color:green;'>✅ Item cadastrado com sucesso!</p>";
  } else {
    echo "<p style='text-align:center; color:red;'>Erro: " . $stmt->error . "</p>";
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadastrar Item | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
  <style>
        nav a.active {
  font-weight: bold;
  color: #D41010FF;
  border-bottom: 2px solid #000000FF;
}
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #f8f8f8; color: #222; min-height: 100vh; display: flex; flex-direction: column; }
    header { display: flex; justify-content: space-between; align-items: center; padding: 2rem 5%; }
    nav a { margin: 0 1rem; text-decoration: none; color: #111; font-weight: 500; }
    .sign-up { background: #111; color: #fff; padding: 0.6rem 1.2rem; border-radius: 25px; text-decoration: none; }
    .sign-in { background: blue; color: #fff; padding: 0.6rem 1.2rem; border-radius: 25px; text-decoration: none; }
    .container { flex: 1; padding: 2rem 5%; }
    h2 { margin-bottom: 1.5rem; text-align: center; }
    form { max-width: 600px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); }
    .input-group { margin-bottom: 1rem; }
    .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
    .input-group input, .input-group textarea, .input-group select { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 8px; }
    button { background: #6c5ce7; color: white; border: none; padding: 0.8rem; border-radius: 8px; width: 100%; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 1rem; }
    footer { color: rgb(0, 0, 0); padding: 2rem; text-align: center; margin-top: auto; }
    .cancel-btn { background: #d63031; color: white; border: none; padding: 0.8rem; border-radius: 8px; width: 100%; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 1rem; }
  </style>
</head>
<body>
  <header>
    <img src="img/logo.png" alt="Logo" style="width: 200px; height: auto; margin-top: 20px;" />

    <nav>

      <a href="home.php">INÍCIO</a>
      <a href="perfil.php">PERFIL</a>
      <a href="perfil.php" class="active">CADASTRAR ITEM</a>
      <a href="gerenciar.php">GERENCIAR ITENS</a>
      <a href="pesquisar.php">PESQUISAR</a>
      <a href="minhas-solicitacoes.php">SOLICITAÇÕES</a>
      <a href="logout.php" class="sign-up">SAIR</a>
    </nav>
  </header>
  <div class="container">
    <h2>Cadastrar Item</h2>
    
    <form method="POST" action="cadastrar-item.php" enctype="multipart/form-data">
      <div class="input-group">




      
        <label for="nome">Nome do item</label>
        <input type="text" id="nome" name="nome" required />
      </div>
      <div class="input-group">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo" required>
          <option value="">Selecione</option>
          <option value="doar">Doar</option>
          <option value="trocar">Trocar</option>
        </select>
      </div>
      <div class="input-group">
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" rows="4" required></textarea>
      </div>
      <div class="input-group">
        <label for="categoria">Categoria</label>
        <select id="categoria" name="categoria" required>
          <option value="">Selecione</option>
          <option>Roupas</option>
          <option>Móveis</option>
          <option>Eletrodomésticos</option>
          <option>Materiais Escolares</option>
        </select>
      </div>
      <div class="input-group">
        <label for="condicao">Condição</label>
        <select id="condicao" name="condicao" required>
          <option value="">Selecione</option>
          <option>Novo</option>
          <option>Usado</option>
          <option>Velho</option>
        </select>
      </div>
      <div class="input-group">
        <label for="endereco">Endereço do item</label>
        <input type="text" id="endereco" name="endereco" required />
      </div>
      <div class="input-group">
        <label for="observacao">Observação</label>
        <textarea id="observacao" name="observacao" rows="2"></textarea>
      </div>
      <div class="input-group">
        <label for="imagem">Adicionar Imagem</label>
        <input type="file" id="imagem" name="imagem" />
      </div>
      <button type="submit">CADASTRAR</button>
      <button class="cancel-btn" type="button" onclick="window.location.href='home.php'">CANCELAR</button>

    </form>
  </div>
  <footer>
    <div class="footer-content"><p>© 2025 IntegraTech_Arapoema • Todos os direitos reservados.</p></div>
  </footer>
</body>
</html>
