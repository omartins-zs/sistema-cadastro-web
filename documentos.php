<?php
/**
 * Helpers para gerar documentos oficiais da Secretaria Virtual e o boletim.
 * Este arquivo não é uma página: apenas funções reutilizáveis.
 */

const DOCUMENTOS_VALIDOS = [
    'Declaração de Matrícula Regular',
    'Atestado de Frequência Escolar',
    'Histórico Escolar Parcial',
    'Declaração de Quitação e Nada Consta',
];

function doc_chave(string $protocolo, int $usuarioId, string $tipo): string
{
    return strtoupper(substr(hash('sha256', $protocolo . '|' . $usuarioId . '|' . $tipo), 0, 16));
}

function doc_notas(mysqli $conn, int $usuarioId): array
{
    $stmt = $conn->prepare('SELECT disciplina, nota_b1, nota_b2, nota_b3, nota_b4, frequencia FROM boletim WHERE usuario_id = ? ORDER BY disciplina');
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $res = $stmt->get_result();
    $notas = [];
    while ($row = $res->fetch_assoc()) {
        $row['media'] = round(($row['nota_b1'] + $row['nota_b2'] + $row['nota_b3'] + $row['nota_b4']) / 4, 1);
        $row['situacao'] = $row['media'] >= 6 && (int) $row['frequencia'] >= 75 ? 'Aprovado' : 'Reprovado';
        $notas[] = $row;
    }
    $stmt->close();
    return $notas;
}

function doc_fmt($n): string
{
    return number_format((float) $n, 1, ',', '.');
}

function doc_cabecalho(string $subtitulo): string
{
    return '<div class="doc-header"><h1>Instituição de Ensino Cadastro Web</h1><span>'
        . escape_html($subtitulo) . '</span></div>';
}

function doc_info_aluno(array $a): string
{
    return '<div class="doc-info">'
        . '<div><strong>Estudante:</strong> ' . escape_html($a['nome']) . '</div>'
        . '<div><strong>Matrícula:</strong> ' . str_pad((string) $a['id'], 6, '0', STR_PAD_LEFT) . '</div>'
        . '<div><strong>E-mail:</strong> ' . escape_html($a['email']) . '</div>'
        . '<div><strong>Ano letivo:</strong> 2026</div>'
        . '</div>';
}

function doc_rodape(string $protocolo, string $data, string $chave): string
{
    return '<div class="doc-signature"><div></div>Secretaria Geral de Registros Acadêmicos</div>'
        . '<div class="doc-footer">'
        . '<span>Protocolo: <strong>' . escape_html($protocolo) . '</strong></span>'
        . '<span>Emitido em: <strong>' . escape_html($data) . '</strong></span>'
        . '<span>Chave de autenticidade: <strong>' . escape_html($chave) . '</strong></span>'
        . '</div>';
}

function doc_tabela_notas(array $notas, bool $completa): string
{
    if (!$notas) {
        return '<p>Nenhuma disciplina lançada até o momento.</p>';
    }
    $h = '<table><thead><tr><th>Disciplina</th>';
    if ($completa) {
        $h .= '<th>1º Bim</th><th>2º Bim</th><th>3º Bim</th><th>4º Bim</th><th>Média</th>';
    }
    $h .= '<th>Freq.</th><th>Situação</th></tr></thead><tbody>';
    foreach ($notas as $n) {
        $ok = $n['situacao'] === 'Aprovado';
        $h .= '<tr><td>' . escape_html($n['disciplina']) . '</td>';
        if ($completa) {
            $h .= '<td>' . doc_fmt($n['nota_b1']) . '</td><td>' . doc_fmt($n['nota_b2']) . '</td><td>'
                . doc_fmt($n['nota_b3']) . '</td><td>' . doc_fmt($n['nota_b4']) . '</td><td><strong>'
                . doc_fmt($n['media']) . '</strong></td>';
        }
        $h .= '<td>' . (int) $n['frequencia'] . '%</td><td class="' . ($ok ? 'doc-ok' : 'doc-bad') . '">'
            . escape_html($n['situacao']) . '</td></tr>';
    }
    return $h . '</tbody></table>';
}

/**
 * Renderiza o documento (HTML) conforme o tipo solicitado.
 * $aluno: ['id','nome','email']
 */
function render_documento(string $tipo, array $aluno, array $notas, string $protocolo, string $data): string
{
    $chave = doc_chave($protocolo, (int) $aluno['id'], $tipo);
    $nome = '<strong>' . escape_html($aluno['nome']) . '</strong>';
    $freqMedia = $notas ? (int) round(array_sum(array_column($notas, 'frequencia')) / count($notas)) : 100;
    $mediaGeral = $notas ? round(array_sum(array_column($notas, 'media')) / count($notas), 1) : 0;

    $corpo = '';
    switch ($tipo) {
        case 'Declaração de Matrícula Regular':
            $corpo = '<p>Declaramos, para os devidos fins, que ' . $nome . ', matrícula nº '
                . str_pad((string) $aluno['id'], 6, '0', STR_PAD_LEFT)
                . ', encontra-se regularmente matriculado(a) nesta instituição de ensino no ano letivo de 2026, '
                . 'com situação acadêmica ativa e frequência às aulas em conformidade com o regimento escolar.</p>'
                . '<p>Por ser expressão da verdade, firmamos a presente declaração.</p>';
            break;

        case 'Atestado de Frequência Escolar':
            $corpo = '<p>Atestamos que ' . $nome . ' frequenta regularmente as atividades escolares desta instituição '
                . 'no ano letivo de 2026, apresentando a frequência média global de <strong>' . $freqMedia . '%</strong>, '
                . ($freqMedia >= 75 ? 'acima' : 'abaixo')
                . ' do mínimo de 75% exigido pela legislação vigente.</p>'
                . doc_tabela_notas(array_map(fn($n) => $n, $notas), false);
            break;

        case 'Histórico Escolar Parcial':
            $corpo = '<p>Histórico escolar parcial referente ao ano letivo de 2026 do(a) estudante ' . $nome
                . ', com os resultados bimestrais lançados até a data de emissão.</p>'
                . doc_tabela_notas($notas, true)
                . '<p><strong>Média geral:</strong> ' . doc_fmt($mediaGeral)
                . ' &nbsp;|&nbsp; <strong>Frequência média:</strong> ' . $freqMedia . '%</p>';
            break;

        case 'Declaração de Quitação e Nada Consta':
            $corpo = '<p>Declaramos que, até a presente data, não constam pendências financeiras, documentais, '
                . 'disciplinares ou de material bibliográfico em nome de ' . $nome . ', matrícula nº '
                . str_pad((string) $aluno['id'], 6, '0', STR_PAD_LEFT)
                . ', junto a esta instituição de ensino.</p>'
                . '<p>A presente declaração tem validade de 30 (trinta) dias a partir da data de emissão.</p>';
            break;

        default:
            return '';
    }

    return '<article class="doc-paper">'
        . doc_cabecalho('Secretaria Geral de Registros Acadêmicos')
        . '<h2 class="doc-title">' . escape_html($tipo) . '</h2>'
        . doc_info_aluno($aluno)
        . $corpo
        . doc_rodape($protocolo, $data, $chave)
        . '</article>';
}

/**
 * Boletim escolar completo para impressão (somente o documento, sem a interface).
 */
function render_boletim_impressao(array $aluno, array $notas): string
{
    $protocolo = 'BOL-' . date('Y') . '-' . str_pad((string) $aluno['id'], 5, '0', STR_PAD_LEFT);
    $data = date('d/m/Y H:i');
    $chave = doc_chave($protocolo, (int) $aluno['id'], 'Boletim');
    $freq = $notas ? (int) round(array_sum(array_column($notas, 'frequencia')) / count($notas)) : 100;
    $media = $notas ? round(array_sum(array_column($notas, 'media')) / count($notas), 1) : 0;

    return '<article class="doc-paper">'
        . doc_cabecalho('Boletim Escolar - Ano Letivo 2026')
        . '<h2 class="doc-title">Boletim Escolar</h2>'
        . doc_info_aluno($aluno)
        . doc_tabela_notas($notas, true)
        . '<p><strong>Média geral:</strong> ' . doc_fmt($media)
        . ' &nbsp;|&nbsp; <strong>Frequência média:</strong> ' . $freq . '%</p>'
        . doc_rodape($protocolo, $data, $chave)
        . '</article>';
}
