<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  header("Location: login.html");
  exit;
}
$pagina = 'gerenciar'; // <- identifica a página atual

$host = "localhost";
$user = "root";
$pass = "seminario123";
$db = "seminario";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "
SELECT i.*, (
  SELECT s.status
  FROM Solicitacao s
  WHERE s.id_item = i.id_item
  ORDER BY s.data_solicitacao DESC
  LIMIT 1
) AS status_atual
FROM Item i
WHERE i.id_usuario = ?
ORDER BY i.data_cadastro DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Gerenciar Itens | Doe ou Troque</title>
  <link rel="icon" href="img/icon.png" type="image/png" />
  <style>
    nav a.active {
  font-weight: bold;
  color: #D41010FF;
  border-bottom: 2px solid #000000FF;
}

     body { font-family: 'Inter', sans-serif; background: #f8f8f8; color: #222; min-height: 100vh; display: flex; flex-direction: column; }
    header { display: flex; justify-content: space-between; align-items: center; padding: 2rem 5%; }
    nav a { margin: 0 1rem; text-decoration: none; color: #111; font-weight: 500; }
    .sign-up { background: #111; color: #fff; padding: 0.6rem 1.2rem; border-radius: 25px; text-decoration: none; }
    footer { color: rgb(0, 0, 0); padding: 2rem; text-align: center; margin-top: auto; }

    body {
      font-family: Arial, sans-serif;
      background-color: #f8f8f8;
      padding: 2rem;
      margin: 0;
    }

    h1 {
      text-align: center;
      color: #6c5ce7;
      margin-bottom: 2rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      border-radius: 8px;
      overflow: hidden;
    }

    th, td {
      padding: 1rem;
      border-bottom: 1px solid #eee;
      text-align: left;
      vertical-align: middle;
    }

    th {
      background-color: #6c5ce7;
      color: white;
    }

    tr:hover {
      background-color: #f2f2f2;
    }

    .img-preview {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .actions a {
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-size: 0.9rem;
      text-decoration: none;
      margin-right: 0.5rem;
      display: inline-block;
    }

    .edit {
      background-color: #6c5ce7;
      color: white;
    }

    .delete {
      background-color: #e74c3c;
      color: white;
    }

    .details {
      background-color: #3498db;
      color: white;
    }

    .no-items {
      text-align: center;
      font-size: 1.1rem;
      color: #555;
      margin-top: 2rem;
    }

    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      th {
        position: absolute;
        top: -9999px;
        left: -9999px;
      }

      td {
        padding-left: 50%;
        position: relative;
        border: none;
        border-bottom: 1px solid #eee;
      }

      td::before {
        position: absolute;
        left: 1rem;
        width: 45%;
        padding-right: 1rem;
        white-space: nowrap;
        font-weight: bold;
        color: #6c5ce7;
      }

      td:nth-of-type(1)::before { content: "Imagem"; }
      td:nth-of-type(2)::before { content: "Nome"; }
      td:nth-of-type(3)::before { content: "Tipo"; }
      td:nth-of-type(4)::before { content: "Categoria"; }
      td:nth-of-type(5)::before { content: "Condição"; }
      td:nth-of-type(6)::before { content: "Endereço"; }
      td:nth-of-type(7)::before { content: "Data"; }
      td:nth-of-type(8)::before { content: "Ações"; }
    }
  </style>
</head>
<body>
<header>
    <img src="img/logo.png" alt="Logo" style="width: 200px;" />

    <nav>

      <a href="home.php">INÍCIO</a>
      <a href="perfil.php">PERFIL</a>
      <a href="cadastrar-item.php">CADASTRAR ITEM</a>
      <a href="gerenciar.php" class="active" >GERENCIAR ITENS</a>
      <a href="pesquisar.php">PESQUISAR</a>
      <a href="minhas-solicitacoes.php">SOLICITAÇÕES</a>
      <a href="logout.php" class="sign-up">SAIR</a>
    </nav>

    </nav>
  </header>

  <h1>Meus Itens Cadastrados</h1>

  <?php if ($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Imagem</th>
          <th>Nome</th>
          <th>Tipo</th>
          <th>Categoria</th>
          <th>Condição</th>
          <th>Status</th>
          <th>Data</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($item = $result->fetch_assoc()): ?>
          <tr>
            <td>
              <?php if (!empty($item['imagem']) && file_exists($item['imagem'])): ?>
                <img src="<?= htmlspecialchars($item['imagem']) ?>" alt="Imagem" class="img-preview">
              <?php else: ?>
                <img src="img/no-image.png" alt="Sem imagem" class="img-preview">
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($item['nome']) ?></td>
            <td><?= htmlspecialchars($item['tipo']) ?></td>
            <td><?= htmlspecialchars($item['categoria']) ?></td>
            <td><?= htmlspecialchars($item['condicao']) ?></td>
            <td><?= htmlspecialchars($item['status_atual']) ?></td>
            
            <td><?= date('d/m/Y', strtotime($item['data_cadastro'])) ?></td>
            <td class="actions">
            <?php if ($item['status_atual'] !== 'aceito'): ?>
  <a href="editar-item.php?id=<?= $item['id_item'] ?>" class="edit">Editar</a>
<?php endif; ?>
              <a href="excluir-item.php?id=<?= $item['id_item'] ?>" class="delete" onclick="return confirm('Tem certeza que deseja excluir este item?');">Excluir</a>
              <a href="ver-item.php?id=<?= $item['id_item'] ?>" class="details">Detalhes</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="no-items">Você ainda não cadastrou nenhum item.</p>
  <?php endif; ?>
  <footer>
    <div class="footer-content">
      <p>© 2025 IntegraTech_Arapoema • Todos os direitos reservados.</p>
    </div>
  </footer>
</body>
</html>

<?php $conn->close(); ?>
