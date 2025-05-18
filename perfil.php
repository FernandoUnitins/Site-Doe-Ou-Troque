<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  header("Location: login.html");
  exit;
}
$pagina = 'perfil'; // ← identifica a página atual

$host = "localhost";
$user = "root";
$pass = "seminario123";
$db = "seminario";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$id = $_SESSION['id_usuario'];
$sql = "SELECT nome, email, telefone, endereco FROM Usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Editar Perfil | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #f8f8f8; color: #222; min-height: 100vh; display: flex; flex-direction: column; }
    header { display: flex; justify-content: space-between; align-items: center; padding: 2rem 5%; }
    nav a { margin: 0 1rem; text-decoration: none; color: #111; font-weight: 500; }
    .sign-up { background: #111; color: #fff; padding: 0.6rem 1.2rem; border-radius: 25px; text-decoration: none; }
    .sign-in { background: blue; color: #fff; padding: 0.6rem 1.2rem; border-radius: 25px; text-decoration: none; }
    .container { display: flex; justify-content: center; align-items: center; flex: 1; padding: 2rem; position: relative; }
    .card { background: white; padding: 3rem 2rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); max-width: 500px; width: 100%; }
    .card h2 { text-align: center; margin-bottom: 2rem; }
    .input-group { margin-bottom: 1rem; }
    .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
    .input-group input { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 8px; }
    .save-btn { background: #30B839FF; color: white; border: none; padding: 0.8rem; border-radius: 8px; width: 100%; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 1rem; }
    .save-btn:hover { background: #5a4fcf; }
    .cancel-btn { background: #d63031; color: white; border: none; padding: 0.8rem; border-radius: 8px; width: 100%; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 1rem; }
    footer { color: rgb(0, 0, 0); padding: 2rem; text-align: center; margin-top: auto; }
    nav a.active {
  font-weight: bold;
  color: #D41010FF;
  border-bottom: 2px solid #000000FF;
}

  </style>
</head>
<body>

  <header>
    <img src="img/logo.png" alt="Logo" style="width: 200px; height: auto; margin-top: 20px;" />
    <nav>
      <a href="home.php">INÍCIO</a>
      <a href="#" class="active">PERFIL </a>
      <a href="cadastrar-item.php">CADASTRAR ITEM</a>
      <a href="gerenciar.php">GERENCIAR ITENS</a>
      <a href="pesquisar.php">PESQUISAR</a>
      <a href="minhas-solicitacoes.php">SOLICITAÇÕES</a>
      <a href="logout.php" class="sign-up">SAIR</a>
    </nav>
  </header>

  <div class="container">
    <div class="card">
      <h2>Editar Perfil</h2>
      <form method="POST" action="atualizar_perfil.php">
        <div class="input-group">
          <label for="nome">Nome</label>
          <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required />
        </div>
        <div class="input-group">
          <label for="endereco">Endereço</label>
          <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($usuario['endereco']) ?>" required />
        </div>
        <div class="input-group">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required />
        </div>
        <div class="input-group">
          <label for="telefone">Telefone</label>
          <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>" required />
        </div>
        <div class="input-group">
          <label for="senha">Nova Senha</label>
          <input type="password" id="senha" name="senha" placeholder="********" />
        </div>
        <div class="input-group">
          <label for="confirmar">Confirmar Nova Senha</label>
          <input type="password" id="confirmar" name="confirmar" placeholder="********" />
        </div>
        <button class="save-btn" type="submit">Salvar</button>
        <button class="cancel-btn" type="button" onclick="window.location.href='home.php'">Cancelar</button>
      </form>
    </div>
  </div>
  <footer>
    <div class="footer-content">
      <p>© 2025 IntegraTech_Arapoema • Todos os direitos reservados.</p>
    </div>
  </footer>
</body>
</html>
