<?php
/**
 * Test Runner do Sistema de Cadastro Web
 * Executa testes funcionais e de integração na aplicação cobrindo os perfis:
 * - Admin
 * - Prof
 * - Aluno
 */

// Detecta URL: argumento CLI, variável TEST_BASE_URL ou loopback interno
$targetUrl = $argv[1] ?? null;
if (!$targetUrl) {
    $envUrl = getenv('TEST_BASE_URL');
    $targetUrl = ($envUrl !== false && $envUrl !== '') ? $envUrl : 'http://127.0.0.1';
}
$baseUrl = rtrim($targetUrl, '/');

$passed = 0;
$failed = 0;

function run_test(string $name, callable $testFn): void {
    global $passed, $failed;
    echo "• Testando: {$name}... ";
    try {
        $result = $testFn();
        if ($result === true || $result === null) {
            echo "\033[32m[PASSOU]\033[0m\n";
            $passed++;
        } else {
            echo "\033[31m[FALHOU: {$result}]\033[0m\n";
            $failed++;
        }
    } catch (Throwable $e) {
        echo "\033[31m[ERRO: " . $e->getMessage() . "]\033[0m\n";
        $failed++;
    }
}

function http_req(string $url, string $method = 'GET', array $data = [], string $cookieJar = ''): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieJar !== '') {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headerStr = substr((string)$raw, 0, (int)$headerSize);
    $body = substr((string)$raw, (int)$headerSize);

    return [
        'status' => $status,
        'headers' => $headerStr,
        'body' => $body
    ];
}

function login_user(string $baseUrl, string $user, string $pass, string $cookieJar): bool {
    $loginPage = http_req("{$baseUrl}/index.php", 'GET', [], $cookieJar);
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $m);
    $csrf = $m[1] ?? '';

    $res = http_req("{$baseUrl}/login.php", 'POST', [
        'csrf_token' => $csrf,
        'txt_usuario' => $user,
        'txt_senha' => $pass
    ], $cookieJar);

    return ($res['status'] === 302 && str_contains($res['headers'], 'Location: home.php'));
}

echo "\n=================================================================\n";
echo "   SISTEMA DE CADASTRO WEB - SUÍTE DE TESTES MULTIPERFIL\n";
echo "   Alvo: {$baseUrl}\n";
echo "=================================================================\n\n";

// -------------------------------------------------------------
// 1. ROTAS PÚBLICAS
// -------------------------------------------------------------
echo "[1] TESTES DE ROTAS PÚBLICAS\n";

run_test("Acesso público à tela de login (index.php)", function() use ($baseUrl) {
    $res = http_req("{$baseUrl}/index.php");
    if ($res['status'] !== 200) return "Status {$res['status']}";
    if (!str_contains($res['body'], 'Acesso ao Sistema')) return "Conteúdo esperado não encontrado";
    return true;
});

run_test("Acesso público ao formulário de cadastro (cadastroUsuario.php)", function() use ($baseUrl) {
    $res = http_req("{$baseUrl}/cadastroUsuario.php");
    if ($res['status'] !== 200) return "Status {$res['status']}";
    if (!str_contains($res['body'], 'Cadastro de Usuário')) return "Conteúdo esperado não encontrado";
    return true;
});

run_test("Bloqueio de acesso anônimo ao painel (home.php)", function() use ($baseUrl) {
    $res = http_req("{$baseUrl}/home.php");
    if ($res['status'] !== 302 || !str_contains($res['headers'], 'index.php?erro=login')) return "Deveria redirecionar para login";
    return true;
});

// -------------------------------------------------------------
// 2. PERFIL ADMIN (Alberto)
// -------------------------------------------------------------
echo "\n[2] TESTES DO PERFIL ADMINISTRADOR (Alberto)\n";
$cookieAdmin = tempnam(sys_get_temp_dir(), 'test_admin_');

run_test("Login como Administrador (Alberto / 123)", function() use ($baseUrl, $cookieAdmin) {
    if (!login_user($baseUrl, 'Alberto', '123', $cookieAdmin)) return "Falha ao autenticar Admin";
    return true;
});

run_test("Home do Administrador renderiza cards e stats de Admin", function() use ($baseUrl, $cookieAdmin) {
    $res = http_req("{$baseUrl}/home.php", 'GET', [], $cookieAdmin);
    if ($res['status'] !== 200) return "Status {$res['status']}";
    if (!str_contains($res['body'], 'Administrador') || !str_contains($res['body'], 'Gerenciar Usuários')) {
        return "Elementos de Admin não encontrados";
    }
    return true;
});

run_test("Admin acessa gerenciamento de usuários (listarUsuarios.php)", function() use ($baseUrl, $cookieAdmin) {
    $res = http_req("{$baseUrl}/listarUsuarios.php", 'GET', [], $cookieAdmin);
    if ($res['status'] !== 200) return "Status {$res['status']}";
    if (!str_contains($res['body'], 'Usuários Cadastrados')) return "Tabela não encontrada";
    return true;
});

run_test("Admin acessa edição de usuário (editar.php?codigo=1)", function() use ($baseUrl, $cookieAdmin) {
    $res = http_req("{$baseUrl}/editar.php?codigo=1", 'GET', [], $cookieAdmin);
    if ($res['status'] !== 200) return "Status {$res['status']}";
    if (!str_contains($res['body'], 'Editar Usuário')) return "Formulário de edição não encontrado";
    return true;
});

run_test("Admin emite relatório em PDF (relatorioUsuario.php)", function() use ($baseUrl, $cookieAdmin) {
    $res = http_req("{$baseUrl}/relatorioUsuario.php", 'GET', [], $cookieAdmin);
    if ($res['status'] !== 200) return "Status {$res['status']}";
    if (!str_contains($res['body'], '%PDF-')) return "Saída não é PDF válido";
    return true;
});

run_test("Admin cadastra novo usuário com perfil e PERMANECE LOGADO", function() use ($baseUrl, $cookieAdmin) {
    $cadPage = http_req("{$baseUrl}/cadastroUsuario.php", 'GET', [], $cookieAdmin);
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $cadPage['body'], $m);
    $csrf = $m[1] ?? '';

    $novoUser = 'test_cad_' . time();
    $cadRes = http_req("{$baseUrl}/cadastrophp" === '' ? '' : "{$baseUrl}/cadastro.php", 'POST', [
        'csrf_token' => $csrf,
        'txt_usuario' => $novoUser,
        'txt_email' => "{$novoUser}@teste.com",
        'txt_senha' => 'senha12345',
        'nivel' => 'Prof'
    ], $cookieAdmin);

    // O Admin deve ser redirecionado para home.php?cadastrado=1 permanecendo autenticado
    if ($cadRes['status'] !== 302) return "Esperava 302, recebeu {$cadRes['status']}";
    if (!str_contains($cadRes['headers'], 'home.php?cadastrado=1')) {
        return "Redirecionamento incorreto: " . $cadRes['headers'];
    }

    // Verifica que o Admin continua logado na home e vê a mensagem de sucesso
    $homeCheck = http_req("{$baseUrl}/home.php?cadastrado=1", 'GET', [], $cookieAdmin);
    if ($homeCheck['status'] !== 200 || !str_contains($homeCheck['body'], 'Novo usuário cadastrado com sucesso')) {
        return "Admin não permaneceu logado ou mensagem de sucesso não apareceu na home";
    }

    // Verifica que o novo usuário foi efetivamente salvo no banco consultando a lista
    $listCheck = http_req("{$baseUrl}/listarUsuarios.php", 'GET', [], $cookieAdmin);
    if ($listCheck['status'] !== 200 || !str_contains($listCheck['body'], $novoUser)) {
        return "Novo usuário não apareceu na lista de usuários";
    }

    return true;
});

// -------------------------------------------------------------
// 3. PERFIL PROFESSOR (ProfessorCarlos)
// -------------------------------------------------------------
echo "\n[3] TESTES DO PERFIL PROFESSOR (ProfessorCarlos)\n";
$cookieProf = tempnam(sys_get_temp_dir(), 'test_prof_');

run_test("Login como Professor (ProfessorCarlos / 12345678)", function() use ($baseUrl, $cookieProf) {
    if (!login_user($baseUrl, 'ProfessorCarlos', '12345678', $cookieProf)) return "Falha ao autenticar ProfessorCarlos";
    return true;
});

run_test("Home do Professor renderiza painel Docente e cards de Turmas", function() use ($baseUrl, $cookieProf) {
    $res = http_req("{$baseUrl}/home.php", 'GET', [], $cookieProf);
    if ($res['status'] !== 200) return "Status {$res['status']}";
    if (!str_contains($res['body'], 'Docente') || !str_contains($res['body'], 'Diário de Classe')) {
        return "Elementos de Professor não encontrados";
    }
    return true;
});

run_test("Professor é BLOQUEADO ao tentar acessar listarUsuarios.php (HTTP 403)", function() use ($baseUrl, $cookieProf) {
    $res = http_req("{$baseUrl}/listarUsuarios.php", 'GET', [], $cookieProf);
    if ($res['status'] !== 403) return "Esperava 403 Forbidden, recebeu {$res['status']}";
    if (!str_contains($res['body'], 'Acesso Não Autorizado')) return "Mensagem de 403 não encontrada";
    return true;
});

run_test("Professor é BLOQUEADO ao tentar acessar editar.php (HTTP 403)", function() use ($baseUrl, $cookieProf) {
    $res = http_req("{$baseUrl}/editar.php?codigo=1", 'GET', [], $cookieProf);
    if ($res['status'] !== 403) return "Esperava 403 Forbidden, recebeu {$res['status']}";
    return true;
});

// -------------------------------------------------------------
// 4. PERFIL ALUNO (AlunoLucas)
// -------------------------------------------------------------
echo "\n[4] TESTES DO PERFIL ALUNO (AlunoLucas)\n";
$cookieAluno = tempnam(sys_get_temp_dir(), 'test_aluno_');

run_test("Login como Aluno (AlunoLucas / 12345678)", function() use ($baseUrl, $cookieAluno) {
    if (!login_user($baseUrl, 'AlunoLucas', '12345678', $cookieAluno)) return "Falha ao autenticar AlunoLucas";
    return true;
});

run_test("Home do Aluno renderiza Portal do Estudante e Boletim", function() use ($baseUrl, $cookieAluno) {
    $res = http_req("{$baseUrl}/home.php", 'GET', [], $cookieAluno);
    if ($res['status'] !== 200) return "Status {$res['status']}";
    if (!str_contains($res['body'], 'Estudante') || !str_contains($res['body'], 'Boletim')) {
        return "Elementos de Aluno não encontrados";
    }
    return true;
});

run_test("Aluno é BLOQUEADO ao tentar acessar listarUsuarios.php (HTTP 403)", function() use ($baseUrl, $cookieAluno) {
    $res = http_req("{$baseUrl}/listarUsuarios.php", 'GET', [], $cookieAluno);
    if ($res['status'] !== 403) return "Esperava 403 Forbidden, recebeu {$res['status']}";
    return true;
});

run_test("Aluno é BLOQUEADO ao tentar acessar relatorioUsuario.php (HTTP 403)", function() use ($baseUrl, $cookieAluno) {
    $res = http_req("{$baseUrl}/relatorioUsuario.php", 'GET', [], $cookieAluno);
    if ($res['status'] !== 403) return "Esperava 403 Forbidden, recebeu {$res['status']}";
    return true;
});

// Limpeza de cookies de teste
@unlink($cookieAdmin);
@unlink($cookieProf);
@unlink($cookieAluno);

echo "\n=================================================================\n";
echo "   RESULTADO FINAL: {$passed} PASSARAM | {$failed} FALHARAM\n";
echo "=================================================================\n\n";

exit($failed > 0 ? 1 : 0);
