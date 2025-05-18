<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  header("Location: login.html");
  exit;
}
$pagina = 'home'; // <- identifica a página atual

$nome = $_SESSION['nome'] ?? 'Usuário';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Início | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
        nav a.active {
  font-weight: bold;
  color: #D41010FF;
  border-bottom: 2px solid #000000FF;
}
    body {
      font-family: Arial, sans-serif;
      background: #f8f8f8;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    main {
      flex: 1;
      padding: 2rem;
    }

    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 2rem;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      max-width: 1000px;
      margin: 0 auto;
    }

    .card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transition: transform 0.2s;
    }

    .card:hover {
      transform: translateY(-3px);
    }

    .card .icon {
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }

    .card p {
      margin: 0.5rem 0 1rem;
      font-size: 0.95rem;
      color: #555;
    }

    .card a {
      padding: 0.6rem 1.2rem;
      background: #6c5ce7;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
      transition: background 0.3s;
    }

    .card a:hover {
      background: #5945d2;
    }

    .card.logout a {
      background: red;
    }

    footer {
      text-align: center;
      padding: 1rem;
      background: #eee;
    }
    nav a.active {
  font-weight: bold;
  color: #D41010FF;
  border-bottom: 2px solid #000000FF;
}

  </style>
</head>
<body>

  <main>
    <h1>Bem-vindo <?= htmlspecialchars($nome) ?>! O que você deseja fazer?</h1>

    <div class="grid">
      <div class="card">
        <div class="icon">👤</div>
        <h3>Perfil</h3>
        <p>Visualize ou edite seus dados pessoais e endereço.</p>
        <a href="perfil.php">Acessar</a>
      </div>

      <div class="card">
        <div class="icon">➕</div>
        <h3>Cadastrar Item</h3>
        <p>Registre um novo item para doar ou trocar com outros usuários.</p>
        <a href="cadastrar-item.php">Cadastrar</a>
      </div>

      <div class="card">
        <div class="icon">🛠️</div>
        <h3>Gerenciar Itens</h3>
        <p>Edite ou exclua os itens que você já cadastrou na plataforma.</p>
        <a href="gerenciar.php">Gerenciar</a>
      </div>

      <div class="card">
        <div class="icon">🔍</div>
        <h3>Pesquisar</h3>
        <p>Encontre itens disponíveis para doação ou troca por categoria e tipo.</p>
        <a href="pesquisar.php">Buscar</a>
      </div>

      <div class="card">
        <div class="icon">📨</div>
        <h3>Minhas Solicitações</h3>
        <p>Veja solicitações enviadas e recebidas e acompanhe o status.</p>
        <a href="minhas-solicitacoes.php">Abrir</a>
      </div>

      <div class="card logout">
        <div class="icon">🚪</div>
        <h3>Sair</h3>
        <p>Finaliza sua sessão com segurança e retorna à tela de login.</p>
        <a href="logout.php">Sair</a>
      </div>
    </div>
  </main>

  <footer>
    <p>© 2025 Doe ou Troque • Todos os direitos reservados.</p>
  </footer>

</body>
</html>
