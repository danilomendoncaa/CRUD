<?php

$host = "localhost";
$dbname = "banco_dados";
$username = "root";
$password = "";

try {
    $conexao = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

function inserirUsuario($conexao, $nome_completo, $email_usuario, $senha_usuario) {
    $sql = "INSERT INTO usuarios (nome_completo, email_usuario, senha_usuario) VALUES (:nome_completo, :email_usuario, :senha_usuario)";
    $stmt = $conexao->prepare($sql);

    $senha_usuario = password_hash($senha_usuario, PASSWORD_BCRYPT);

    $stmt->bindParam(":nome_completo", $nome_completo);
    $stmt->bindParam(":email_usuario", $email_usuario);
    $stmt->bindParam(":senha_usuario", $senha_usuario);

    return $stmt->execute();
}

function listarUsuarios($conexao) {
    $sql = "SELECT usuario_id, nome_completo, email_usuario FROM usuarios";
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function atualizarUsuario($conexao, $usuario_id, $nome_completo, $email_usuario, $senha_usuario = null) {
    $sql = "UPDATE usuarios SET nome_completo = :nome_completo, email_usuario = :email_usuario";
    if ($senha_usuario) {
        $sql .= ", senha_usuario = :senha_usuario";
    }
    $sql .= " WHERE usuario_id = :usuario_id";

    $stmt = $conexao->prepare($sql);

    if ($senha_usuario) {
        $senha_usuario = password_hash($senha_usuario, PASSWORD_BCRYPT);
        $stmt->bindParam(":senha_usuario", $senha_usuario);
    }

    $stmt->bindParam(':nome_completo', $nome_completo);
    $stmt->bindParam(':email_usuario', $email_usuario);
    $stmt->bindParam(':usuario_id', $usuario_id);

    return $stmt->execute();
}

function excluirUsuario($conexao, $usuario_id) {
    $sql = "DELETE FROM usuarios WHERE usuario_id = :usuario_id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id);
    return $stmt->execute();
}

$acao = isset($_GET['acao']) ? $_GET['acao'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($acao) {
        case 'adicionar':
            $nome_completo = $_POST['nome_completo'];
            $email_usuario = $_POST['email_usuario'];
            $senha_usuario = $_POST['senha_usuario'];
            inserirUsuario($conexao, $nome_completo, $email_usuario, $senha_usuario);
            break;
        case 'editar':
            $usuario_id = $_POST['usuario_id'];
            $nome_completo = $_POST['nome_completo'];
            $email_usuario = $_POST['email_usuario'];
            $senha_usuario = !empty($_POST['senha_usuario']) ? $_POST['senha_usuario'] : null;
            atualizarUsuario($conexao, $usuario_id, $nome_completo, $email_usuario, $senha_usuario);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $acao === 'remover') {
    $usuario_id = $_GET['usuario_id'];
    excluirUsuario($conexao, $usuario_id);
}

$usuarios = listarUsuarios($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>CRUD de Usuários</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Adicionar Novo Usuário</h2>
    <form action="crud.php?acao=adicionar" method="POST">
        <label for="nome_completo">Nome Completo:</label>
        <input type="text" name="nome_completo" required><br>
        <label for="email_usuario">Email:</label>
        <input type="email" name="email_usuario" required><br>
        <label for="senha_usuario">Senha:</label>
        <input type="password" name="senha_usuario" required><br>
        <button type="submit">Adicionar</button>
    </form>

    <h2>Lista de Usuários</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome Completo</th>
            <th>Email</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($usuarios as $usuario) : ?>
        <tr>
            <td><?php echo $usuario['usuario_id']; ?></td>
            <td><?php echo $usuario['nome_completo']; ?></td>
            <td><?php echo $usuario['email_usuario']; ?></td>
            <td>
                <a href="crud.php?acao=editar&usuario_id=<?php echo $usuario['usuario_id']; ?>&nome_completo=<?php echo $usuario['nome_completo']; ?>&email_usuario=<?php echo $usuario['email_usuario']; ?>">Editar</a>
                <a href="crud.php?acao=remover&usuario_id=<?php echo $usuario['usuario_id']; ?>">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($acao === 'editar') : ?>
    <h2>Editar Usuário</h2>
    <form action="crud.php?acao=editar" method="POST">
        <input type="hidden" name="usuario_id" value="<?php echo $_GET['usuario_id']; ?>">
        <label for="nome_completo">Nome Completo:</label>
        <input type="text" name="nome_completo" value="<?php echo $_GET['nome_completo']; ?>" required><br>
        <label for="email_usuario">Email:</label>
        <input type="email" name="email_usuario" value="<?php echo $_GET['email_usuario']; ?>" required><br>
        <label for="senha_usuario">Nova Senha:</label>
        <input type="password" name="senha_usuario"><br>
        <button type="submit">Atualizar</button>
    </form>
    <?php endif; ?>
</body>
</html>

