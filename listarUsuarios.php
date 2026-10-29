<?php
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/conexao.php';

$usuarioNome = $_SESSION['usuario'] ?? 'Admin';
$usuarioNivel = $_SESSION['nivel'] ?? 'Admin';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

$usuarios = $conn->query('SELECT id, usuario, email, nivel FROM usuarios ORDER BY usuario');
$totalUsuarios = $usuarios ? $usuarios->num_rows : 0;

$msgSucesso = null;
if (($_GET['cadastrado'] ?? '') === '1') {
    $msgSucesso = 'Novo usuário cadastrado com sucesso!';
} elseif (($_GET['atualizado'] ?? '') === '1') {
    $msgSucesso = 'Usuário atualizado com sucesso!';
} elseif (($_GET['excluido'] ?? '') === '1') {
    $msgSucesso = 'Usuário removido com sucesso!';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="./css/app.css?v=<?= $cacheVersion ?>">
    <title>Gerenciamento de Usuários - Cadastro Web</title>
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="app-navbar">
        <a href="home.php" class="nav-brand">
            <div class="nav-brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <span>Cadastro Web</span>
        </a>

        <div class="nav-user-area">
            <div class="user-badge-pill">
                <span class="user-avatar-sm"><?= escape_html($primeiraLetra) ?></span>
                <strong><?= escape_html($usuarioNome) ?></strong>
                <span class="role-pill role-<?= strtolower($usuarioNivel) ?>">
                    <?= escape_html($usuarioNivel) ?>
                </span>
            </div>

            <form action="logout.php" method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                <button type="submit" class="btn btn-secondary btn-sm" title="Encerrar sessão">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Gerenciar Usuários</span>
        </div>

        <?php if ($msgSucesso): ?>
            <div class="alert alert-success" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= escape_html($msgSucesso) ?></span>
            </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-header-bar">
                <div class="table-title">
                    <h2>Usuários Cadastrados</h2>
                    <span>Gerencie acessos, edite dados ou exclua contas cadastradas (<span id="user-count"><?= $totalUsuarios ?></span> registros)</span>
                </div>

                <div class="table-actions">
                    <a href="relatorioUsuario.php" target="_blank" class="btn btn-secondary btn-sm" title="Exportar PDF">
                        <i class="fa-solid fa-file-pdf"></i> Baixar PDF
                    </a>
                    <a href="cadastroUsuario.php" class="btn btn-primary btn-sm" style="margin-top:0;">
                        <i class="fa-solid fa-user-plus"></i> Novo Usuário
                    </a>
                </div>
            </div>

            <!-- Filter / Search Bar -->
            <div class="table-filter-bar">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="filter-input" class="search-input" placeholder="Filtrar por nome ou e-mail..." onkeyup="filterTable()">
                </div>
            </div>

            <div class="modern-table-responsive">
                <table class="modern-table" id="users-table">
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-user"></i> Usuário</th>
                            <th><i class="fa-solid fa-envelope"></i> E-mail</th>
                            <th><i class="fa-solid fa-id-badge"></i> Perfil</th>
                            <th style="text-align: right;"><i class="fa-solid fa-sliders"></i> Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($usuarios && $usuarios->num_rows > 0): ?>
                        <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                            <?php 
                                $nivelRole = strtolower($usuario['nivel'] ?? 'aluno');
                                $isSelf = ((int)$usuario['id'] === (int)($_SESSION['user_id'] ?? 0));
                            ?>
                            <tr class="user-row">
                                <td class="col-user">
                                    <strong><?= escape_html($usuario['usuario']) ?></strong>
                                    <?php if ($isSelf): ?>
                                        <small style="color: #38bdf8; margin-left: 6px; font-weight: 600;">(você)</small>
                                    <?php endif; ?>
                                </td>
                                <td class="col-email" style="color: var(--text-muted);"><?= escape_html($usuario['email']) ?></td>
                                <td>
                                    <span class="role-pill role-<?= $nivelRole ?>">
                                        <?php if ($nivelRole === 'admin'): ?>
                                            <i class="fa-solid fa-shield-halved"></i>
                                        <?php elseif ($nivelRole === 'prof'): ?>
                                            <i class="fa-solid fa-chalkboard-user"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-graduation-cap"></i>
                                        <?php endif; ?>
                                        <?= escape_html($usuario['nivel']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end; align-items: center;">
                                        <a class="btn btn-secondary btn-sm" href="editar.php?codigo=<?= (int) $usuario['id'] ?>" title="Editar usuário">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                        </a>

                                        <?php if (!$isSelf): ?>
                                            <form action="excluir.php" method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir o usuário <?= escape_html(addslashes($usuario['usuario'])) ?>?')">
                                                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                                                <input type="hidden" name="codigo" value="<?= (int) $usuario['id'] ?>">
                                                <button class="btn btn-danger btn-sm" type="submit" title="Excluir usuário">
                                                    <i class="fa-solid fa-trash-can"></i> Excluir
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.45; cursor: not-allowed;" title="Você não pode excluir sua própria conta">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr id="empty-state">
                            <td colspan="4" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                <i class="fa-regular fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                                Nenhum usuário cadastrado até o momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                        <tr id="no-search-results" style="display: none;">
                            <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                                Nenhum usuário corresponde ao filtro digitado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function filterTable() {
            const input = document.getElementById("filter-input");
            const filter = input.value.toLowerCase().trim();
            const rows = document.querySelectorAll("#users-table .user-row");
            let visibleCount = 0;

            rows.forEach(row => {
                const userText = row.querySelector(".col-user") ? row.querySelector(".col-user").textContent.toLowerCase() : "";
                const emailText = row.querySelector(".col-email") ? row.querySelector(".col-email").textContent.toLowerCase() : "";

                if (userText.includes(filter) || emailText.includes(filter)) {
                    row.style.display = "";
                    visibleCount++;
                } else {
                    row.style.display = "none";
                }
            });

            const noResults = document.getElementById("no-search-results");
            if (noResults) {
                noResults.style.display = (visibleCount === 0 && rows.length > 0) ? "" : "none";
            }

            const countEl = document.getElementById("user-count");
            if (countEl) {
                countEl.textContent = visibleCount;
            }
        }
    </script>
</body>

</html>
