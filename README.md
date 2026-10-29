<div align="center">

# 💻 Sistema de Cadastro Web

Aplicação web robusta e moderna para cadastro e gerenciamento de usuários, com autenticação, perfis de acesso, pesquisa dinâmica e exportação de relatório PDF.

![PHP 8.2](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL 8](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Docker Compose](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)
![Status](https://img.shields.io/badge/Status-Concluído-success)

<p>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" width="36" alt="PHP" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/mysql/mysql-original.svg" width="36" alt="MySQL" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/docker/docker-original.svg" width="36" alt="Docker" />
</p>

<h4 align="center">✅ Projeto Concluído & Homologado 🚀</h4>

</div>

## 🧭 Sobre o projeto

Sistema acadêmico e corporativo desenvolvido em PHP procedural com arquitetura segura e interface de alta fidelidade baseada em **SaaS Dark Mode com Glassmorphism**.

A aplicação reúne formulários com validações avançadas, proteção de credenciais, controle de acesso baseado em níveis (Admin, Prof, Aluno) e relatórios formatados em PDF.

## 🏗️ Arquitetura e Tecnologias

- **Backend:** PHP 8.1+ (executado em PHP 8.2 com Apache no Docker).
- **Banco de Dados:** MySQL 8.0 com charset UTF-8 mb4.
- **Frontend & Design:** Design System customizado (Glassmorphism, Google Fonts, Font Awesome 6.5, CSS Variables).
- **Segurança:** Tokens anti-CSRF, hash seguro com `password_hash`, proteção contra brute-force (Rate Limiting) e prevenção de XSS/SQL Injection.
- **Dependências:** Dompdf para geração de PDF e PHP Dotenv para variáveis de ambiente.

## ✨ Funcionalidades Principais

- 🔐 **Autenticação Segura:** Login com hash `BCRYPT`, migração automática de senhas antigas e bloqueio temporário após 5 tentativas consecutivas incorretas (Rate Limiting).
- 👁️ **Visualização de Senhas:** Alternância dinâmica de visibilidade de senha (*mostrar/ocultar*) em todos os formulários.
- 📝 **Cadastro Público:** Criação de novos usuários com perfil padrão `Aluno` e validação de requisitos mínimos.
- 📊 **Dashboard Executivo Multi-Perfil:** Painel personalizado de acordo com o nível do usuário (`Admin`, `Prof` ou `Aluno`).
- 👥 **Gerenciamento Completo (Admin):**
  - Listagem com **pesquisa dinâmica em tempo real** (filtra por nome ou e-mail sem recarregar a página).
  - Edição de usuários com alteração opcional de senha e nível de privilégio.
  - Exclusão com confirmação e bloqueio de segurança contra autoexclusão da conta ativa.
  - Cadastro de novos usuários com escolha de perfil mantendo a sessão do administrador ativa.
- 🎓 **Portal do Aluno:**
  - 📖 **Boletim & Desempenho (`boletim.php`):** Consulta de notas bimestrais, médias calculadas, frequência escolar, comunicados em tempo real dos professores e impressão/download exclusivo do documento em PDF (`baixar.php?boletim=1`).
  - 🏛️ **Secretaria Virtual (`secretaria.php`):** Emissão instantânea de 4 tipos de documentos oficiais autenticados (Declaração de Matrícula, Atestado de Frequência, Histórico Parcial e Nada Consta) com chave digital SHA-256 e download em PDF (`baixar.php?doc=ID`).
  - 💳 **Financeiro & Mensalidades (`financeiro.php`):** Acompanhamento completo da grade de mensalidades, consulta de pagamentos liquidados, emissão de comprovantes de quitação, simulação de pagamento instantâneo via PIX com QR Code e atalho para emissão da Declaração de Nada Consta.
  - 👤 **Meus Dados (`meusDados.php`):** Consulta do perfil e atualização de e-mail e senha pelo próprio aluno.
- 👨‍🏫 **Portal do Professor & Gestão Acadêmica:**
  - 📋 **Diário de Classe (`diarioClasse.php`):** Lançamento de notas bimestrais (B1 a B4) e frequência de todos os alunos da disciplina, com cálculo ao vivo no navegador, preenchimento rápido de frequência e persistência via `ON DUPLICATE KEY UPDATE`.
  - 🏫 **Turmas Atribuídas (`turmas.php`):** Grade curricular, salas, horários semanais, cálculo automático de média geral da turma e frequência, além de modal interativo para listagem dos alunos matriculados e atalho de lançamento.
  - 📢 **Quadro de Comunicados (`comunicados.php`):** Publicação de avisos, comunicados pedagógicos e orientações oficiais com feed dinâmico integrado ao boletim dos alunos.
- 📄 **Relatório em PDF:** Emissão de documento analítico com cabeçalho institucional, data de emissão e linhas zebradas.
- 🛡️ **Páginas de Erro Amigáveis:** Tratamento padronizado de exceções (400, 403, 404, 500) com interface visual integrada.

## 🚀 Executar com Docker

Consulte o [guia Docker](docs/COMO_EXECUTAR_DOCKER.md). A aplicação fica disponível em `http://localhost:8080`, o PHPMyAdmin em `http://localhost:8085` e o MySQL na porta `3308`.

```powershell
Copy-Item .env.example .env
# Certifique-se de que o bloco DOCKER está ativo no .env
docker compose up -d --build
```

## 🖥️ Executar Localmente

Consulte o [guia local](docs/COMO_EXECUTAR_LOCAL.md). O servidor embutido do PHP atende em `http://127.0.0.1:8000`:

```bash
composer install
php -S 127.0.0.1:8000 -t .
```

## 🔐 Acessos de Demonstração (Multi-perfil)

| Perfil | Usuário | Senha | E-mail | Privilégios & Funcionalidades |
| --- | --- | --- | --- | --- |
| **Administrador** | `Alberto` | `123` | `alberto@email.com` | Controle total, listagem, edição, exclusão, relatório PDF e cadastro com seleção de perfis |
| **Professor** | `ProfessorCarlos` | `12345678` | `professor@escola.com` | Painel docente exclusivo (Diário de Classe, Notas, Turmas). Sem acesso administrativo (403) |
| **Aluno** | `AlunoLucas` | `12345678` | `aluno@escola.com` | Portal do estudante (Boletim, Secretaria Virtual, Dados). Sem acesso administrativo (403) |

> No primeiro acesso com senhas legadas, a conversão para hash seguro (`password_hash`) ocorre automaticamente no banco.

## 🧪 Suíte de Testes Automatizados

O projeto inclui uma suíte completa de **37 testes de integração automatizados** em `tests/test_system.php`, cobrindo 100% dos fluxos públicos, administrativos, docentes e discentes:

```bash
# Execução no container Docker:
docker exec cadastro-web-app-1 php /var/www/html/tests/test_system.php

# Ou execução local:
php tests/test_system.php http://127.0.0.1:8000
```

**Cenários cobertos:**
1. Acesso à tela de Login (`index.php`)
2. Acesso à tela de Cadastro (`cadastroUsuario.php`)
3. Proteção de rotas privadas contra acessos anônimos (`home.php`)
4. Rejeição de credenciais inválidas e rate limiting
5. Autenticação com credenciais válidas
6. Renderização do dashboard autenticado
7. Acesso e renderização da lista de usuários
8. Formulário de edição de usuários
9. Emissão e integridade de binário PDF (`relatorioUsuario.php`)
10. Encerramento seguro de sessão com CSRF (`logout.php`)

## 📁 Estrutura do Projeto

```text
.
├── css/                   # Estilos globais e Design System (style.css, estilo.css)
├── docker/                # Configurações de PHP e Apache
├── docs/                  # Guias de execução e documentação detalhada
├── js/                    # Scripts de interação (index.js)
├── tests/                 # Suíte de testes automatizados (test_system.php)
├── auth.php               # Gestão de sessões, perfis, CSRF e telas de erro
├── conexao.php            # Leitura do .env e conexão resiliente com o MySQL
├── index.php              # Tela de Login com feedback de tentativas
├── cadastroUsuario.php    # Formulário público de novo cadastro
├── home.php               # Dashboard principal com métricas
├── listarUsuarios.php     # Painel de gestão com pesquisa em tempo real
├── editar.php             # Edição de usuários e permissões
├── relatorioUsuario.php   # Gerador de relatório PDF com Dompdf
└── Script-BD-Escola.txt   # Script SQL de inicialização do banco
```

<div align="center">

Feito com ❤️ por Gabriel Martins 🚀

</div>
