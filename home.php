<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_login();
require_once __DIR__ . '/conexao.php';

$usuarioNome = $_SESSION['usuario'] ?? 'Usuário';
$usuarioNivel = $_SESSION['nivel'] ?? 'Aluno';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

$totalCadastros = 0;
if ($usuarioNivel === 'Admin' && isset($conn)) {
    $res = $conn->query("SELECT COUNT(*) AS total FROM usuarios");
    if ($res) {
        $row = $res->fetch_assoc();
        $totalCadastros = (int) ($row['total'] ?? 0);
    }
}


// Métricas reais por perfil
$statAlunos = $statLancamentos = $statComunicados = $statDocs = 0;
$statMedia = '–'; $statFreq = 0;
if (isset($conn)) {
    if ($usuarioNivel === 'Prof' || $usuarioNivel === 'Admin') {
        $statAlunos = (int) $conn->query("SELECT COUNT(*) FROM usuarios WHERE nivel = 'Aluno'")->fetch_row()[0];
        $statLancamentos = (int) $conn->query('SELECT COUNT(*) FROM boletim')->fetch_row()[0];
        $statComunicados = (int) $conn->query('SELECT COUNT(*) FROM comunicados')->fetch_row()[0];
    } else {
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        $q = $conn->prepare('SELECT AVG((nota_b1 + nota_b2 + nota_b3 + nota_b4) / 4), AVG(frequencia) FROM boletim WHERE usuario_id = ?');
        $q->bind_param('i', $uid);
        $q->execute();
        $q->bind_result($mediaDb, $freqDb);
        $q->fetch();
        $q->close();
        $statMedia = $mediaDb !== null ? number_format((float) $mediaDb, 1, ',', '.') : '–';
        $statFreq = $freqDb !== null ? (int) round($freqDb) : 0;
        $q = $conn->prepare('SELECT COUNT(*) FROM solicitacoes_secretaria WHERE usuario_id = ?');
        $q->bind_param('i', $uid);
        $q->execute();
        $q->bind_result($statDocs);
        $q->fetch();
        $q->close();

        $statFinStatus = 'Em Dia';
        $qFin = $conn->prepare("SELECT COUNT(*) FROM mensalidades WHERE usuario_id = ? AND status = 'Pendente'");
        if ($qFin) {
            $qFin->bind_param('i', $uid);
            $qFin->execute();
            $qFin->bind_result($pendCount);
            if ($qFin->fetch() && $pendCount > 2) {
                $statFinStatus = $pendCount . ' Pendentes';
            }
            $qFin->close();
        }
    }
}
$msgSucesso = null;
if (($_GET['cadastrado'] ?? '') === '1') {
    $msgSucesso = 'Novo usuário cadastrado com sucesso!';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
    <title>Painel Principal - <?= escape_html($usuarioNivel) ?> - Cadastro Web</title>
</head>

<body>
    <!-- Top Navigation Bar -->
    <?= layout_navbar('home.php') ?>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <?php if ($msgSucesso): ?>
            <div class="alert alert-success" role="alert" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= escape_html($msgSucesso) ?></span>
            </div>
        <?php endif; ?>

        <!-- Hero Welcome -->
        <section class="welcome-hero">
            <div class="welcome-text">
                <h1>Olá, <?= escape_html($usuarioNome) ?>! 👋</h1>
                <?php if ($usuarioNivel === 'Admin'): ?>
                    <p>Painel de Administração e Gestão de Usuários da Instituição.</p>
                <?php elseif ($usuarioNivel === 'Prof'): ?>
                    <p>Ambiente Docente - Gerenciamento de aulas, conteúdos e turmas.</p>
                <?php else: ?>
                    <p>Portal Acadêmico do Estudante - Acompanhe sua trajetória e dados cadastrais.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Quick Stats Row com Diferenciação por Perfil -->
        <div class="stats-row">
            <?php if ($usuarioNivel === 'Admin'): ?>
                <div class="stat-box">
                    <div class="stat-icon dash-icon-blue">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $totalCadastros ?></h4>
                        <span>Usuários Registrados</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-purple">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Administrador</h4>
                        <span>Acesso Total / CRUD</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-emerald">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Ativo</h4>
                        <span>MySQL Conectado</span>
                    </div>
                </div>

            <?php elseif ($usuarioNivel === 'Prof'): ?>
                <div class="stat-box">
                    <div class="stat-icon dash-icon-purple">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $statAlunos ?></h4><span>Alunos matriculados</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-blue">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $statLancamentos ?></h4><span>Lançamentos de notas</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-emerald">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $statComunicados ?></h4><span>Comunicados publicados</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-purple">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <h4>3 Turmas</h4><span>Carga atribuída</span>
                    </div>
                </div>

            <?php else: ?>
                <div class="stat-box">
                    <div class="stat-icon dash-icon-emerald">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $statMedia ?></h4><span>Média geral</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-blue">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $statFreq ?>%</h4><span>Frequência média</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-purple">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $statDocs ?></h4><span>Documentos emitidos</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-emerald">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= escape_html($statFinStatus ?? 'Em Dia') ?></h4><span>Financeiro</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Cards Grid -->
        <div class="dash-grid">
            <?php if ($usuarioNivel === 'Admin'): ?>
                <a href="listarUsuarios.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-blue">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <h3>Gerenciar Usuários</h3>
                    <p>Consulte a base completa, edite perfis de acesso e remova contas com segurança.</p>
                    <div class="dash-card-arrow">
                        <span>Acessar lista</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <a href="relatorioUsuario.php" target="_blank" class="dash-card">
                    <div class="dash-icon-box dash-icon-emerald">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <h3>Relatório em PDF</h3>
                    <p>Gere e faça download instantâneo do relatório institucional completo de usuários.</p>
                    <div class="dash-card-arrow">
                        <span>Baixar documento</span>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </div>
                </a>

                <a href="cadastroUsuario.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-purple">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3>Cadastrar Novo Usuário</h3>
                    <p>Adicione diretamente novos Alunos, Professores ou Administradores ao sistema.</p>
                    <div class="dash-card-arrow">
                        <span>Cadastrar agora</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

            <?php elseif ($usuarioNivel === 'Prof'): ?>
                <a href="diarioClasse.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-blue">
                        <i class="fa-solid fa-chalkboard"></i>
                    </div>
                    <h3>Diário de Classe & Notas</h3>
                    <p>Área destinada ao lançamento de notas bimestrais, frequências de alunos e planos de aula.</p>
                    <div class="dash-card-arrow">
                        <span>Acessar Diário</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <a href="turmas.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-emerald">
                        <i class="fa-solid fa-users-rectangle"></i>
                    </div>
                    <h3>Turmas Atribuídas</h3>
                    <p>Acompanhe os estudantes matriculados nas disciplinas sob sua responsabilidade acadêmica.</p>
                    <div class="dash-card-arrow">
                        <span>Ver Turmas</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <a href="comunicados.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-blue" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <h3>Quadro de Comunicados</h3>
                    <p>Publique orientações pedagógicas, avisos de provas e mensagens oficiais para os estudantes.</p>
                    <div class="dash-card-arrow">
                        <span>Gerenciar Avisos</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <a href="meusDados.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-purple">
                        <i class="fa-solid fa-address-card"></i>
                    </div>
                    <h3>Meu Cadastro Docente</h3>
                    <p>Você está conectado como <strong><?= escape_html($usuarioNome) ?></strong> com nível de privilégio <strong>Professor</strong>.</p>
                    <div class="dash-card-arrow">
                        <span>Gerenciar Cadastro</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

            <?php else: ?>
                <a href="boletim.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-emerald">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <h3>Boletim & Desempenho</h3>
                    <p>Consulte suas notas, frequência nas aulas e comunicados emitidos pelos professores.</p>
                    <div class="dash-card-arrow">
                        <span>Acessar Boletim</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <a href="secretaria.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-blue">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h3>Secretaria Virtual</h3>
                    <p>Solicite declarações de matrícula, atestados de frequência e histórico escolar.</p>
                    <div class="dash-card-arrow">
                        <span>Acessar Secretaria</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <a href="financeiro.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-blue" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h3>Financeiro & Mensalidades</h3>
                    <p>Consulte parcelas pagas, gere código PIX para liquidação e emita sua Declaração de Nada Consta.</p>
                    <div class="dash-card-arrow">
                        <span>Acessar Financeiro</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <a href="meusDados.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-purple">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                    <h3>Meus Dados</h3>
                    <p>Conectado como <strong><?= escape_html($usuarioNome) ?></strong> com perfil <strong>Aluno</strong>. Acesso regular ativo.</p>
                    <div class="dash-card-arrow">
                        <span>Gerenciar Dados</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <script src="./js/index.js?v=<?= time() ?>"></script>
</body>

</html>
