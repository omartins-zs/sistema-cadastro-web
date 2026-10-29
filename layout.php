<?php
/**
 * Componentes de layout compartilhados (head, navbar, alertas).
 */

function layout_head(string $titulo): string
{
    $v = time();
    return '<meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<meta name="color-scheme" content="dark">'
        . '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">'
        . '<link rel="preconnect" href="https://fonts.googleapis.com">'
        . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
        . '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">'
        . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">'
        . '<link rel="stylesheet" href="./css/app.css?v=' . $v . '">'
        . '<title>' . escape_html($titulo) . ' - Cadastro Web</title>';
}

function layout_navbar(string $ativo = ''): string
{
    $nome = $_SESSION['usuario'] ?? 'Usuário';
    $nivel = $_SESSION['nivel'] ?? 'Aluno';
    $letra = strtoupper(mb_substr($nome, 0, 1, 'UTF-8'));
    $icone = $nivel === 'Admin' ? 'fa-shield-halved' : ($nivel === 'Prof' ? 'fa-chalkboard-user' : 'fa-graduation-cap');

    $links = [['home.php', 'fa-house', 'Início']];
    if ($nivel === 'Admin') {
        $links[] = ['listarUsuarios.php', 'fa-users-gear', 'Usuários'];
    }
    if ($nivel === 'Admin' || $nivel === 'Prof') {
        $links[] = ['diarioClasse.php', 'fa-chalkboard', 'Diário'];
        $links[] = ['comunicados.php', 'fa-bullhorn', 'Comunicados'];
        if ($nivel === 'Prof') {
            $links[] = ['turmas.php', 'fa-users-rectangle', 'Turmas'];
        }
    }
    if ($nivel === 'Aluno') {
        $links[] = ['boletim.php', 'fa-book', 'Boletim'];
        $links[] = ['secretaria.php', 'fa-file-lines', 'Secretaria'];
        $links[] = ['financeiro.php', 'fa-wallet', 'Financeiro'];
    }
    $links[] = ['meusDados.php', 'fa-circle-user', 'Meus dados'];

    $menu = '';
    foreach ($links as [$href, $ic, $label]) {
        $cls = $href === $ativo ? ' active' : '';
        $menu .= '<a href="' . $href . '" class="nav-link' . $cls . '"><i class="fa-solid ' . $ic . '"></i><span>' . $label . '</span></a>';
    }

    return '<nav class="app-navbar no-print">'
        . '<a href="home.php" class="nav-brand"><div class="nav-brand-icon"><i class="fa-solid fa-graduation-cap"></i></div><span>Cadastro Web</span></a>'
        . '<div class="nav-links">' . $menu . '</div>'
        . '<div class="nav-user-area">'
        . '<div class="user-badge-pill"><span class="user-avatar-sm">' . escape_html($letra) . '</span><strong>' . escape_html($nome) . '</strong>'
        . '<span class="role-pill role-' . strtolower($nivel) . '"><i class="fa-solid ' . $icone . '"></i> ' . escape_html($nivel) . '</span></div>'
        . '<form action="logout.php" method="post" style="display:inline;">'
        . '<input type="hidden" name="csrf_token" value="' . escape_html(csrf_token()) . '">'
        . '<button type="submit" class="btn btn-secondary btn-sm" title="Encerrar sessão"><i class="fa-solid fa-right-from-bracket"></i> <span>Sair</span></button>'
        . '</form></div></nav>';
}

function layout_alert(?string $msg, string $tipo = 'success'): string
{
    if (!$msg) {
        return '';
    }
    $icone = $tipo === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation';
    return '<div class="alert alert-' . ($tipo === 'success' ? 'success' : 'danger') . ' no-print" role="alert" data-autohide>'
        . '<i class="fa-solid ' . $icone . '"></i><span>' . escape_html($msg) . '</span></div>';
}

