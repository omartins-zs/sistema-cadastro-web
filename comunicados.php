<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/layout.php';

$nivel = $_SESSION['nivel'] ?? 'Aluno';
$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($nivel === 'Aluno') {
    render_error_view(403, 'Acesso Não Autorizado', 'Apenas professores e administradores publicam comunicados.', 'home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou expirado.', 'comunicados.php');
    }

    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $mensagem = trim($_POST['mensagem'] ?? '');
        if ($titulo === '' || $mensagem === '' || mb_strlen($titulo) > 150 || mb_strlen($mensagem) > 2000) {
            header('Location: comunicados.php?erro=' . urlencode('Informe um título (até 150 caracteres) e uma mensagem (até 2000).'));
            exit;
        }
        $stmt = $conn->prepare('INSERT INTO comunicados (autor_id, titulo, mensagem, criado_em) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iss', $userId, $titulo, $mensagem);
        $stmt->execute();
        $stmt->close();
        header('Location: comunicados.php?ok=' . urlencode('Comunicado publicado! Os alunos já podem vê-lo no boletim.'));
        exit;
    }

    if ($acao === 'excluir') {
        $id = (int) ($_POST['id'] ?? 0);
        // Admin exclui qualquer um; professor apenas os próprios.
        if ($nivel === 'Admin') {
            $stmt = $conn->prepare('DELETE FROM comunicados WHERE id = ?');
            $stmt->bind_param('i', $id);
        } else {
            $stmt = $conn->prepare('DELETE FROM comunicados WHERE id = ? AND autor_id = ?');
            $stmt->bind_param('ii', $id, $userId);
        }
        $stmt->execute();
        $apagou = $stmt->affected_rows > 0;
        $stmt->close();
        header('Location: comunicados.php?' . ($apagou ? 'ok=' . urlencode('Comunicado removido.') : 'erro=' . urlencode('Você não pode remover este comunicado.')));
        exit;
    }

    header('Location: comunicados.php');
    exit;
}

$lista = $conn->query(
    'SELECT c.id, c.titulo, c.mensagem, c.autor_id, u.usuario AS autor, u.nivel, DATE_FORMAT(c.criado_em, "%d/%m/%Y %H:%i") AS data_fmt
     FROM comunicados c JOIN usuarios u ON u.id = c.autor_id ORDER BY c.id DESC'
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><?= layout_head('Comunicados') ?></head>
<body>
    <?= layout_navbar('comunicados.php') ?>

    <div class="dashboard-container">
        <div class="breadcrumb-bar no-print">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Comunicados</span>
        </div>

        <?= layout_alert($_GET['ok'] ?? null, 'success') ?>
        <?= layout_alert($_GET['erro'] ?? null, 'danger') ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.5rem;align-items:start;">
            <div class="table-card">
                <div class="table-title" style="margin-bottom:1.25rem;">
                    <h2><i class="fa-solid fa-pen-to-square" style="color:#38bdf8;margin-right:8px;"></i> Novo Comunicado</h2>
                    <span>Publicado para todos os alunos no Boletim</span>
                </div>
                <form method="post" action="comunicados.php" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                    <input type="hidden" name="acao" value="criar">

                    <div class="form-group">
                        <label class="form-label" for="titulo">Título</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-heading input-icon-left"></i>
                            <input id="titulo" class="form-control" type="text" name="titulo" maxlength="150" placeholder="Ex.: Prova de recuperação" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="mensagem">Mensagem</label>
                        <textarea id="mensagem" name="mensagem" class="textarea-control" rows="5" maxlength="2000" placeholder="Escreva o comunicado aos alunos..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Publicar Comunicado</button>
                </form>
            </div>

            <div class="table-card">
                <div class="table-header-bar" style="flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;">
                    <div class="table-title">
                        <h2><i class="fa-solid fa-bullhorn" style="color:#38bdf8;margin-right:8px;"></i> Publicados (<?= count($lista) ?>)</h2>
                        <span>Mais recentes primeiro</span>
                    </div>

                    <div class="search-wrapper" style="width:220px;margin:0;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" id="filtro-comunicados" class="search-input" placeholder="Filtrar aviso..." onkeyup="filtrarComunicados()">
                    </div>
                </div>

                <?php if (!$lista): ?>
                    <div class="empty-state"><i class="fa-regular fa-comment-dots"></i><p>Nenhum comunicado publicado ainda.</p></div>
                <?php endif; ?>

                <div class="comunicados-lista">
                <?php foreach ($lista as $c): ?>
                    <div class="comunicado-item">
                        <div class="comunicado-topo">
                            <strong><?= escape_html($c['titulo']) ?></strong>
                            <small><?= escape_html($c['data_fmt']) ?></small>
                        </div>
                        <p><?= nl2br(escape_html($c['mensagem'])) ?></p>
                        <div class="comunicado-rodape">
                            <span><i class="fa-solid fa-user"></i> <?= escape_html($c['autor']) ?> · <?= escape_html($c['nivel']) ?></span>
                            <?php if ($nivel === 'Admin' || (int) $c['autor_id'] === $userId): ?>
                                <form method="post" action="comunicados.php" onsubmit="return confirm('Remover este comunicado?')">
                                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i> Remover</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="./js/index.js?v=<?= time() ?>"></script>
    <script>
        function filtrarComunicados() {
            var val = document.getElementById('filtro-comunicados').value.toLowerCase().trim();
            document.querySelectorAll('.comunicado-item').forEach(function(item) {
                var texto = item.textContent.toLowerCase();
                item.style.display = texto.includes(val) ? '' : 'none';
            });
        }
    </script>
</body>
</html>

