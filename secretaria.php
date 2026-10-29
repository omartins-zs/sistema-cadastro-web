<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_login();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/documentos.php';

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$usuarioNome = $_SESSION['usuario'] ?? 'Aluno';
$usuarioNivel = $_SESSION['nivel'] ?? 'Aluno';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

// Emissão de novo documento (Post/Redirect/Get para não duplicar ao recarregar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou expirado.', 'secretaria.php');
    }

    $tipoDoc = trim($_POST['tipo_documento'] ?? '');
    if (in_array($tipoDoc, DOCUMENTOS_VALIDOS, true)) {
        $protocolo = 'SEC-' . date('Y') . '-' . str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        $status = 'Emitido (Disponível)';

        $stmt = $conn->prepare('INSERT INTO solicitacoes_secretaria (usuario_id, tipo_documento, protocolo, status, data_solicitacao) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('isss', $usuarioId, $tipoDoc, $protocolo, $status);
        $stmt->execute();
        $novoId = $stmt->insert_id;
        $stmt->close();

        header('Location: secretaria.php?ver=' . (int) $novoId . '&novo=1');
        exit;
    }
    header('Location: secretaria.php');
    exit;
}

// Dados do aluno
$stmt = $conn->prepare('SELECT email FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$stmt->bind_result($usuarioEmail);
$stmt->fetch();
$stmt->close();

// Documento selecionado para visualização/impressão (somente do próprio usuário)
$documentoHtml = null;
$documentoTitulo = '';
$verId = filter_input(INPUT_GET, 'ver', FILTER_VALIDATE_INT);
if ($verId) {
    $stmt = $conn->prepare('SELECT tipo_documento, protocolo, DATE_FORMAT(data_solicitacao, "%d/%m/%Y %H:%i") FROM solicitacoes_secretaria WHERE id = ? AND usuario_id = ?');
    $stmt->bind_param('ii', $verId, $usuarioId);
    $stmt->execute();
    $stmt->bind_result($docTipo, $docProtocolo, $docData);
    if ($stmt->fetch()) {
        $documentoTitulo = $docTipo;
        $aluno = ['id' => $usuarioId, 'nome' => $usuarioNome, 'email' => (string) $usuarioEmail];
        $stmt->close();
        $documentoHtml = render_documento($docTipo, $aluno, doc_notas($conn, $usuarioId), $docProtocolo, $docData);
    } else {
        $stmt->close();
    }
}
$recemEmitido = ($_GET['novo'] ?? '') === '1' && $documentoHtml !== null;

// Histórico
$stmt = $conn->prepare('SELECT id, tipo_documento, protocolo, status, DATE_FORMAT(data_solicitacao, "%d/%m/%Y %H:%i") AS data_fmt FROM solicitacoes_secretaria WHERE usuario_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$solicitacoes = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="./css/app.css?v=<?= $cacheVersion ?>">
    <title>Secretaria Virtual - Cadastro Web</title>
</head>

<body>
    <?= layout_navbar('secretaria.php') ?>

    <div class="dashboard-container">
        <div class="breadcrumb-bar no-print">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <?php if ($documentoHtml): ?>
                <a href="secretaria.php">Secretaria Virtual</a>
                <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                <span><?= escape_html($documentoTitulo) ?></span>
            <?php else: ?>
                <span>Secretaria Virtual</span>
            <?php endif; ?>
        </div>

        <?php if ($recemEmitido): ?>
            <div class="alert alert-success no-print" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <span>Documento emitido com sucesso e autenticado digitalmente!</span>
            </div>
        <?php endif; ?>

        <?php if ($documentoHtml): ?>
            <div class="table-actions no-print" style="margin-bottom: 1.25rem;">
                <a href="secretaria.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Voltar à Secretaria
                </a>
                <a href="baixar.php?doc=<?= (int) $verId ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-file-arrow-down"></i> Baixar PDF
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" style="margin-top:0;width:auto;">
                    <i class="fa-solid fa-print"></i> Imprimir
                </button>
            </div>

            <?= $documentoHtml ?>
        <?php else: ?>
            <div class="table-card no-print" style="margin-bottom: 2rem;">
                <div class="table-header-bar">
                    <div class="table-title">
                        <h2><i class="fa-solid fa-file-circle-plus" style="color: #38bdf8; margin-right: 8px;"></i> Solicitar Novo Documento</h2>
                        <span>Selecione o documento necessário para geração instantânea</span>
                    </div>
                </div>

                <form action="secretaria.php" method="post" class="auth-form" style="max-width: 600px;">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

                    <div class="form-group">
                        <label class="form-label" for="tipo_documento">Tipo de Documento Desejado</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-file-contract input-icon-left"></i>
                            <select id="tipo_documento" name="tipo_documento" class="form-control" required>
                                <?php foreach (DOCUMENTOS_VALIDOS as $d): ?>
                                    <option value="<?= escape_html($d) ?>"><?= escape_html($d) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="max-width: 260px; margin-top: 0.5rem;">
                        <i class="fa-solid fa-bolt"></i> Emitir Documento Agora
                    </button>
                </form>
            </div>

            <div class="table-card no-print">
                <div class="table-header-bar">
                    <div class="table-title">
                        <h2>Histórico de Documentos e Protocolos</h2>
                        <span>Abra novamente qualquer documento já emitido</span>
                    </div>
                </div>

                <div class="modern-table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th style="width: 170px; white-space: nowrap;"><i class="fa-solid fa-hashtag"></i> Protocolo</th>
                                <th><i class="fa-solid fa-file-lines"></i> Documento</th>
                                <th style="width: 170px; white-space: nowrap;"><i class="fa-solid fa-calendar-day"></i> Data de Emissão</th>
                                <th style="width: 230px; text-align: right; white-space: nowrap;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($solicitacoes && $solicitacoes->num_rows > 0): ?>
                            <?php while ($row = $solicitacoes->fetch_assoc()): ?>
                                <tr>
                                    <td><strong style="color: #38bdf8;"><?= escape_html($row['protocolo']) ?></strong></td>
                                    <td><?= escape_html($row['tipo_documento']) ?></td>
                                    <td style="color: var(--text-muted);"><?= escape_html($row['data_fmt']) ?></td>
                                    <td style="text-align: right;">
                                        <div class="table-actions-cell">
                                            <a class="btn btn-secondary btn-sm" href="secretaria.php?ver=<?= (int) $row['id'] ?>">
                                                <i class="fa-solid fa-eye"></i> Visualizar
                                            </a>
                                            <a class="btn btn-primary btn-sm" href="baixar.php?doc=<?= (int) $row['id'] ?>">
                                                <i class="fa-solid fa-file-arrow-down"></i> Baixar PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    Nenhuma solicitação realizada até o momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="./js/index.js?v=<?= time() ?>"></script>
</body>

</html>
