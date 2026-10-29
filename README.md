<div align="center">

# 💻 Sistema de Cadastro Web

Aplicação web robusta e moderna para gestão acadêmica e corporativa, com controle de acesso multi-perfil (Admin, Professor, Aluno), emissão de documentos autenticados, diário de classe, financeiro e exportação de relatórios em PDF.

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![Testes](https://img.shields.io/badge/Testes-37%20Aprovados-10b981?style=flat-square)](https://github.com/omartins-zs/sistema-cadastro-web)
[![Status](https://img.shields.io/badge/Status-Concluído-38bdf8?style=flat-square)](https://github.com/omartins-zs/sistema-cadastro-web)

<br/>

**✅ Projeto Concluído & 100% Homologado 🚀**

</div>

---

## 🧭 Sobre o Projeto

O **Sistema de Cadastro Web** é uma plataforma acadêmica e administrativa completa desenvolvida em PHP e MySQL. Possui arquitetura segura, controle rigoroso de acesso e design moderno baseado no padrão **SaaS Dark Mode com Glassmorphism**, compatível com desktop e telas móveis sem barras de rolagem indesejadas.

---

## 🏗️ Arquitetura e Tecnologias

- **Backend:** PHP 8.1+ (executado em PHP com Apache no Docker).
- **Banco de Dados:** MySQL 8.0 com suporte completo a caracteres UTF-8 (`utf8mb4`).
- **Frontend & UI/UX:** Design System exclusivo (Glassmorphism, Google Fonts *Plus Jakarta Sans*, Font Awesome 6.5, CSS Variables).
- **Segurança:** 
  - Tokens anti-CSRF em todos os formulários e ações de estado.
  - Criptografia com `password_hash` (`BCRYPT`) e upgrade automático de senhas legadas.
  - Proteção contra força bruta (Rate Limiting por IP e tentativa).
  - Sanitização de entradas e prevenção contra XSS e SQL Injection (Prepared Statements).
- **Bibliotecas:** Dompdf (geração de documentos e relatórios oficiais) e PHP Dotenv (gestão de ambiente).

---

## ✨ Módulos e Funcionalidades

### 🔐 Autenticação e Segurança
- Login seguro com bloqueio temporário após 5 tentativas incorretas.
- Alternância dinâmica de visibilidade de senha (*olho mágico*) em todos os campos.
- Cadastro público de visitantes com atribuição automática do perfil `Aluno`.
- Bloqueio imediato com página amigável de erro HTTP 403 para acessos não autorizados.

### 👥 Módulo Administrador (`Admin`)
- **Dashboard Gerencial:** Métricas globais de cadastros, status do banco de dados e atalhos rápidos.
- **Gestão de Usuários (`listarUsuarios.php`):** Pesquisa instantânea em tempo real (filtro por nome ou e-mail), paginação e contadores.
- **Edição & Permissões (`editar.php`):** Alteração de dados cadastrais, redefinição de perfil e redefinição de senha com trava de segurança para autoexclusão.
- **Relatório PDF (`relatorioUsuario.php`):** Emissão de listagem institucional analítica de todos os usuários cadastrados.

### 👨‍🏫 Módulo Docente (`Professor`)
- **Turmas Atribuídas (`turmas.php`):** Visão completa das turmas do semestre, salas, horários, médias consolidadas, percentual de frequência e modal dinâmico com a relação de estudantes matriculados.
- **Diário de Classe (`diarioClasse.php`):** Lançamento de notas bimestrais (B1 a B4) e faltas por disciplina, cálculo de média e situação em tempo real no front-end, preenchimento de frequência em lote (100%) e salvamento idempotente via `ON DUPLICATE KEY UPDATE`.
- **Quadro de Avisos (`comunicados.php`):** Publicação de comunicados pedagógicos com feed dinâmico integrado diretamente ao painel do aluno.

### 🎓 Módulo Discente (`Aluno`)
- **Boletim Escolar (`boletim.php`):** Consulta das notas dos quatro bimestres, média anual, situação acadêmica e atalho para impressão e download de boletim oficial formatado.
- **Secretaria Virtual (`secretaria.php`):** Emissão instantânea com assinatura digital SHA-256 e download em PDF de:
  - Declaração de Matrícula Regular
  - Atestado de Frequência Escolar
  - Histórico Escolar Parcial
  - Declaração de Quitação e Nada Consta
- **Portal Financeiro (`financeiro.php`):** Extrato completo de mensalidades, consulta de parcelas pagas/a vencer, simulação de pagamento instantâneo via PIX com QR Code dinâmico e emissão do Nada Consta com 1 clique.
- **Meus Dados (`meusDados.php`):** Autoatendimento para atualização de e-mail e redefinição de senha pessoal.

---

## 🚀 Como Executar com Docker

Consulte o [guia de execução com Docker](docs/COMO_EXECUTAR_DOCKER.md). A aplicação fica disponível em `http://localhost:8080`, o PHPMyAdmin em `http://localhost:8085` e o MySQL na porta `3308`.

```powershell
# 1. Copie o arquivo de variáveis de ambiente
Copy-Item .env.example .env

# 2. Suba os containers do projeto
docker compose up -d --build
```

---

## 🖥️ Como Executar Localmente

Consulte o [guia de execução local](docs/COMO_EXECUTAR_LOCAL.md).

```bash
composer install
php -S 127.0.0.1:8000 -t .
```

---

## 🔐 Acessos de Demonstração (Multi-Perfil)

| Perfil | Usuário | Senha | E-mail | Acesso & Funcionalidades |
| :--- | :--- | :--- | :--- | :--- |
| **Administrador** | `Alberto` | `123` | `alberto@email.com` | Acesso pleno: Gestão de contas, CRUD completo, Relatório PDF |
| **Professor** | `ProfessorCarlos` | `12345678` | `professor@escola.com` | Painel Docente: Turmas, Diário de Classe, Lançamento de Notas |
| **Aluno** | `AlunoLucas` | `12345678` | `aluno@escola.com` | Portal Discente: Boletim, Secretaria Virtual, Financeiro e PIX |

---

## 🧪 Suíte de Testes Automatizados

O projeto conta com **37 testes de integração ponta a ponta** em `tests/test_system.php`, validando todos os endpoints, segurança de rotas, geração de documentos em PDF e autenticação:

```bash
# Execução no container Docker:
docker exec cadastro-web-app-1 php /var/www/html/tests/test_system.php
```

```text
=================================================================
   SISTEMA DE CADASTRO WEB - SUÍTE DE TESTES MULTIPERFIL
   RESULTADO FINAL: 37 PASSARAM | 0 FALHARAM
=================================================================
```

---

## 📁 Estrutura de Arquivos

```text
.
├── css/
│   └── app.css             # Design System completo (SaaS Dark Mode & Glassmorphism)
├── docker/                 # Imagens e configurações de Apache/PHP
├── docs/                   # Documentação detalhada de instalação e execução
├── js/
│   └── index.js            # Interações dinâmicas, cálculos ao vivo e validações
├── tests/
│   └── test_system.php     # Suíte automatizada com 37 testes de integração
├── auth.php                # Controle de sessão, CSRF, permissões e telas de erro
├── baixar.php              # Endpoint seguro para download de PDFs e boletins
├── boletim.php             # Portal do estudante: notas, faltas e comunicados
├── cadastroUsuario.php     # Cadastro público e formulário de novo usuário
├── comunicados.php         # Feed de avisos e comunicados pedagógicos
├── conexao.php             # Conexão resiliente com MySQL e charset utf8mb4
├── diarioClasse.php        # Diário de classe com notas, frequência e persistência
├── documentos.php          # Gerador e renderizador de documentos oficiais com SHA-256
├── editar.php              # Edição e gerenciamento de permissões
├── financeiro.php          # Portal financeiro, mensalidades, PIX e Nada Consta
├── home.php                # Dashboard executivo multi-perfil com métricas
├── index.php               # Portal de login seguro com rate limiting
├── layout.php              # Componentes reaproveitáveis de navbar e head
├── listarUsuarios.php      # Gestão de usuários com busca em tempo real
├── meusDados.php           # Atualização cadastral do usuário conectado
├── relatorioUsuario.php    # Geração do relatório geral de usuários em PDF
├── turmas.php              # Gestão de turmas docentes e alunos matriculados
└── Script-BD-Escola.txt    # DDL e seeds iniciais do banco de dados
```

<div align="center">

Feito com ❤️ por Gabriel Martins 🚀

</div>
