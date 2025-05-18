<?php 
session_start(); 
if (!isset($_SESSION['id_usuario'])) { 
  header("Location: login.html"); 
  exit; 
} 
$pagina = 'minhas-solicitacoes'; // <- identifica a página atual
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Minhas Solicitações | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
    nav a.active {
      font-weight: bold;
      color: #D41010FF;
      border-bottom: 2px solid #000000FF;
    }

    header { 
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      padding: 0.5rem 3%; /* reduzido de 1rem para 0.5rem */
  margin-bottom: 0; /* opcional, evita espaço extra */ 
    }

    nav a { 
      margin: 0 1rem; 
      text-decoration: none; 
      color: #111; 
      font-weight: 500; 
    }

    .sign-up { 
      background: #111; 
      color: #fff; 
      padding: 0.6rem 1.2rem; 
      border-radius: 25px; 
      text-decoration: none; 
    }

    body { 
      font-family: Arial, sans-serif; 
      background: #f8f8f8; 
      padding: 1rem 3%; /* reduzido de 1rem para 0.5rem, se quiser */
  margin-top: 0; /* garantir que não tenha margem extra */
    }

    h1 { 
      text-align: center; 
      color: #6c5ce7; 
    margin: 0.5rem 0 1rem 0; /* diminui a margem superior */    }

    .tabs { 
      display: flex; 
      justify-content: center; 
      margin-bottom: 0.3rem; /* reduzido para aproximar mais das abas */
    }

    .tab-btn {
      padding: 0.6rem 1.2rem;
      border: none;
      background: #ddd;
      margin: 0 0.3rem;
      cursor: pointer;
      border-radius: 6px;
      font-weight: bold;
    }

    .tab-btn.active {
      background: #6c5ce7;
      color: white;
    }

    .tab-content { 
      display: none; 
    }

    .tab-content.active { 
      display: block; 
    }

    iframe {
      width: 100%;
      height: 70vh;
      max-height: 800px;
      min-height: 400px;
      border: none;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
  </style>
  <script>
    function showTab(tabId) {
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(div => div.classList.remove('active'));

      document.getElementById('btn-' + tabId).classList.add('active');
      document.getElementById(tabId).classList.add('active');
    }

    window.onload = () => showTab('recebidas');
  </script>
</head>
<body>
<header>
    <img src="img/logo.png" alt="Logo" style="width: 200px; height: auto; margin-top: 20px;" />
    <nav>
      <a href="home.php">INÍCIO</a>
      <a href="perfil.php">PERFIL</a>
      <a href="cadastrar-item.php">CADASTRAR ITEM</a>
      <a href="gerenciar.php">GERENCIAR ITENS</a>
      <a href="pesquisar.php">PESQUISAR</a>
      <a href="minhas-solicitacoes.php" class="active">SOLICITAÇÕES</a>
      <a href="logout.php" class="sign-up">SAIR</a>
    </nav>
  </header>

  <h1>Minhas Solicitações</h1>

  <div class="tabs">
    <button class="tab-btn" id="btn-recebidas" onclick="showTab('recebidas')">Recebidas</button>
    <button class="tab-btn" id="btn-enviadas" onclick="showTab('enviadas')">Enviadas</button>
  </div>

  <div class="tab-content" id="recebidas">
    <iframe src="solicitacoes-recebidas.php"></iframe>
  </div>

  <div class="tab-content" id="enviadas">
    <iframe src="solicitacoes-enviadas.php"></iframe>
  </div>

</body>
</html>
