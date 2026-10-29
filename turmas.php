<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_login();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/documentos.php';

$usuarioNome = $_SESSION['usuario'] ?? 'Professor';
$usuarioNivel = $_SESSION['nivel'] ?? 'Prof';
$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$cacheVersion = time();

if ($usuarioNivel === 'Aluno') {
    render_error_view(403, 'Acesso Não Autorizado', 'Área restrita a professores e administradores.', 'home.php');
}

// Lista real de alunos matriculados
$stmtAlunos = $conn->query("SELECT id, usuario, email FROM usuarios WHERE nivel = 'Aluno' ORDER BY usuario ASC");
$listaAlunos = $stmtAlunos ? $stmtAlunos->fetch_all(MYSQLI_ASSOC) : [];
$totalAlunos = count($listaAlunos);

// Médias por disciplina a partir do boletim
$mediasPorDisciplina = [];
$resMedias = $conn->query("SELECT disciplina, ROUND(AVG((nota_b1 + nota_b2 + nota_b3 + nota_b4)/4), 1) AS media_turma, ROUND(AVG(frequencia)) AS freq_turma FROM boletim GROUP BY disciplina");
if ($resMedias) {
    while ($mRow = $resMedias->fetch_assoc()) {
        $mediasPorDisciplina[$mRow['disciplina']] = [
            'media' => (float)$mRow['media_turma'],
            'freq' => (int)$mRow['freq_turma']
        ];
    }
}

// Definição das Turmas Atribuídas ao Professor
$turmas = [
    [
        'id' => '3A_MEDIO',
        'nome' => 'Turma 3º Ano A - Ensino Médio',
        'disciplina' => 'Matemática',
        'sala' => 'Sala 204 - Bloco B',
        'horario' => 'Segundas e Quartas (08:00 - 10:00)',
        'carga' => '80 horas/aula',
        'cor_badge' => 'dash-icon-blue',
        'media' => $mediasPorDisciplina['Matemática']['media'] ?? 8.5,
        'freq' => $mediasPorDisciplina['Matemática']['freq'] ?? 96
    ],
    [
        'id' => '2B_MEDIO',
        'nome' => 'Turma 2º Ano B - Ensino Médio',
        'disciplina' => 'Física',
        'sala' => 'Laboratório 108 - Bloco A',
        'horario' => 'Terças e Quintas (10:15 - 12:00)',
        'carga' => '60 horas/aula',
        'cor_badge' => 'dash-icon-emerald',
        'media' => $mediasPorDisciplina['Física']['media'] ?? 8.2,
        'freq' => $mediasPorDisciplina['Física']['freq'] ?? 95
    ],
    [
        'id' => '1A_FUND',
        'nome' => 'Turma 1º Ano A - Ensino Fundamental',
        'disciplina' => 'Língua Portuguesa',
        'sala' => 'Sala 102 - Bloco A',
        'horario' => 'Segundas e Sextas (14:00 - 16:00)',
        'carga' => '80 horas/aula',
        'cor_badge' => 'dash-icon-purple',
        'media' => $mediasPorDisciplina['Língua Portuguesa']['media'] ?? 8.8,
        'freq' => $mediasPorDisciplina['Língua Portuguesa']['freq'] ?? 98
    ]
];

$mediaGlobalGeral = !empty($mediasPorDisciplina) 
    ? round(array_sum(array_column($mediasPorDisciplina, 'media')) / count($mediasPorDisciplina), 1) 
    : 8.5;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?= layout_head('Turmas Atribuídas') ?>
</head>

<body>
    <!-- Top Navigation Bar -->
    <?= layout_navbar('turmas.php') ?>

    <div class="dashboard-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar no-print">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Turmas Atribuídas & Carga Horária</span>
        </div>

        <div class="table-header-bar no-print" style="margin-bottom: 2rem;">
            <div class="table-title">
                <h2><i class="fa-solid fa-users-rectangle" style="color: #38bdf8; margin-right: 8px;"></i> Turmas & Grade Acadêmica</h2>
                <span>Acompanhamento pedagógico, distribuição de alunos e acesso rápido aos diários</span>
            </div>

            <div class="table-actions">
                <a href="comunicados.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-bullhorn"></i> Comunicados
                </a>
                <a href="diarioClasse.php" class="btn btn-primary btn-sm" style="margin-top:0;">
                    <i class="fa-solid fa-chalkboard"></i> Abrir Diário de Classe
                </a>
            </div>
        </div>

        <!-- Métricas Rápidas das Turmas -->
        <div class="stats-row no-print" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon dash-icon-blue">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="stat-info">
                    <h4><?= count($turmas) ?> Turmas</h4>
                    <span>Atribuídas no Semestre</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-emerald">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h4><?= $totalAlunos ?> Alunos</h4>
                    <span>Matriculados Ativos</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-purple">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h4>220 Horas</h4>
                    <span>Carga Horária Total</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-blue">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div class="stat-info">
                    <h4><?= number_format($mediaGlobalGeral, 1, ',', '.') ?></h4>
                    <span>Média Global Docente</span>
                </div>
            </div>
        </div>

        <!-- Grade de Turmas Atribuídas -->
        <div class="dash-grid no-print" style="margin-bottom: 2.5rem;">
            <?php foreach ($turmas as $t): ?>
                <div class="dash-card" style="display:flex;flex-direction:column;justify-content:space-between;cursor:default;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                            <div class="dash-icon-box <?= $t['cor_badge'] ?>">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>
                            <span class="role-pill role-prof" style="font-size:0.78rem;">
                                <?= escape_html($t['carga']) ?>
                            </span>
                        </div>

                        <h3 style="font-size:1.15rem;margin-bottom:0.6rem;color:#f8fafc;"><?= escape_html($t['nome']) ?></h3>
                        
                        <div style="font-size:0.88rem;color:#cbd5e1;line-height:1.6;margin-bottom:1.25rem;">
                            <p style="margin:0 0 0.3rem;"><strong style="color:#ffffff;">Disciplina:</strong> <?= escape_html($t['disciplina']) ?></p>
                            <p style="margin:0 0 0.3rem;"><strong style="color:#ffffff;">Local:</strong> <?= escape_html($t['sala']) ?></p>
                            <p style="margin:0 0 0.3rem;"><strong style="color:#ffffff;">Horário:</strong> <?= escape_html($t['horario']) ?></p>
                            <div style="display:flex;gap:1rem;margin-top:0.75rem;padding:0.6rem 0.8rem;background:rgba(15,23,42,0.6);border-radius:8px;">
                                <div><small style="color:var(--text-muted);display:block;">Média da Turma</small><strong style="color:#38bdf8;"><?= number_format($t['media'], 1, ',', '.') ?></strong></div>
                                <div><small style="color:var(--text-muted);display:block;">Frequência</small><strong style="color:#34d399;"><?= $t['freq'] ?>%</strong></div>
                                <div><small style="color:var(--text-muted);display:block;">Alunos</small><strong style="color:#ffffff;"><?= $totalAlunos ?></strong></div>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:0.5rem;margin-top:1rem;border-top:1px solid rgba(255,255,255,0.06);padding-top:1rem;">
                        <button type="button" class="btn btn-secondary btn-sm" style="flex:1;" onclick="verAlunosModal('<?= escape_html(addslashes($t['nome'])) ?>', '<?= escape_html(addslashes($t['disciplina'])) ?>')">
                            <i class="fa-solid fa-users"></i> Alunos
                        </button>
                        <a href="diarioClasse.php?disciplina=<?= urlencode($t['disciplina']) ?>" class="btn btn-primary btn-sm" style="flex:1.2;margin-top:0;">
                            <i class="fa-solid fa-pen-to-square"></i> Lançar Notas
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabela do Corpo Discente Matriculado -->
        <div class="table-card no-print">
            <div class="table-header-bar">
                <div class="table-title">
                    <h2>Corpo Discente Matriculado</h2>
                    <span>Relação oficial de alunos cadastrados aptos para lançamento pedagógico</span>
                </div>
            </div>

            <div class="modern-table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome do Aluno</th>
                            <th>E-mail Acadêmico</th>
                            <th>Situação Cadastral</th>
                            <th style="text-align: right;">Ações Pedagógicas</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($listaAlunos)): ?>
                        <?php foreach ($listaAlunos as $al): ?>
                            <tr>
                                <td><strong style="color:#38bdf8;">#<?= str_pad((string)$al['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:0.6rem;">
                                        <span class="user-avatar-sm" style="width:28px;height:28px;font-size:0.75rem;">
                                            <?= strtoupper(mb_substr($al['usuario'], 0, 1, 'UTF-8')) ?>
                                        </span>
                                        <strong style="color:#f8fafc;"><?= escape_html($al['usuario']) ?></strong>
                                    </div>
                                </td>
                                <td style="color:var(--text-muted);"><?= escape_html($al['email']) ?></td>
                                <td>
                                    <span class="role-pill role-aluno">
                                        <i class="fa-solid fa-circle-check"></i> Regular Ativo
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions-cell">
                                        <a href="diarioClasse.php?disciplina=Matemática" class="btn btn-secondary btn-sm" title="Abrir notas no diário">
                                            <i class="fa-solid fa-marker"></i> Notas
                                        </a>
                                        <a href="financeiro.php?aluno_id=<?= (int)$al['id'] ?>" class="btn btn-secondary btn-sm" title="Consultar financeiro do aluno">
                                            <i class="fa-solid fa-wallet"></i> Financeiro
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:2.5rem;color:var(--text-muted);">
                                Nenhum aluno matriculado no sistema até o momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Lista de Alunos da Turma -->
    <div id="modal-alunos" class="modal-backdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
        <div class="modal-box table-card" style="max-width:650px;width:100%;margin:auto;box-shadow:0 25px 50px rgba(0,0,0,0.5);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
                <div>
                    <h3 id="modal-turma-titulo" style="font-size:1.2rem;color:#ffffff;margin:0 0 0.2rem;">Alunos da Turma</h3>
                    <span id="modal-turma-disc" style="font-size:0.85rem;color:#38bdf8;">–</span>
                </div>
                <button type="button" onclick="fecharAlunosModal()" style="background:none;border:none;color:#94a3b8;font-size:1.2rem;cursor:pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div style="max-height:350px;overflow-y:auto;margin-bottom:1.25rem;border:1px solid rgba(255,255,255,0.08);border-radius:10px;">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Aluno</th>
                            <th>E-mail</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($listaAlunos as $al): ?>
                        <tr>
                            <td><strong style="color:#38bdf8;">#<?= str_pad((string)$al['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><strong style="color:#f8fafc;"><?= escape_html($al['usuario']) ?></strong></td>
                            <td style="color:var(--text-muted);font-size:0.85rem;"><?= escape_html($al['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="fecharAlunosModal()">Fechar</button>
                <a id="modal-btn-diario" href="diarioClasse.php" class="btn btn-primary" style="margin-top:0;width:auto;">
                    <i class="fa-solid fa-chalkboard"></i> Abrir no Diário de Classe
                </a>
            </div>
        </div>
    </div>

    <script src="./js/index.js?v=<?= time() ?>"></script>
    <script>
        function verAlunosModal(turmaNome, disciplina) {
            document.getElementById('modal-turma-titulo').textContent = turmaNome;
            document.getElementById('modal-turma-disc').textContent = 'Disciplina: ' + disciplina + ' | <?= $totalAlunos ?> alunos matriculados';
            document.getElementById('modal-btn-diario').href = 'diarioClasse.php?disciplina=' + encodeURIComponent(disciplina);
            document.getElementById('modal-alunos').style.display = 'flex';
        }

        function fecharAlunosModal() {
            document.getElementById('modal-alunos').style.display = 'none';
        }
    </script>
</body>

</html>
