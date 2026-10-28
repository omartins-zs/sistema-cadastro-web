<div align="center">

# 💻 Sistema de Cadastro Web

Aplicação web monolítica para cadastro e gerenciamento de usuários, com login, perfis e exportação de relatório PDF.

![PHP 8.2](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL 8](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Docker Compose](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)

<p>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" width="36" alt="PHP" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/mysql/mysql-original.svg" width="36" alt="MySQL" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/docker/docker-original.svg" width="36" alt="Docker" />
</p>

</div>

## 🧭 Sobre o projeto

Sistema acadêmico de cadastro desenvolvido em PHP procedural. A interface e o processamento ficam no mesmo projeto e se conectam a um banco MySQL.

<cite>Cadastro e gerenciamento de usuários com controle de acesso e relatório PDF.</cite>

<h4 align="center">⚙️ Em desenvolvimento 🚀</h4>

## 🏗️ Arquitetura

- **Tipo:** monólito web.
- **Backend:** PHP 8.1+; a imagem Docker utiliza PHP 8.2 com Apache.
- **Banco:** MySQL 8.0.
- **Dependências:** Dompdf para PDF e PHP dotenv para configuração local.

## ✨ Funcionalidades

- Login e encerramento de sessão.
- Cadastro público de usuário com perfil padrão `Aluno`.
- Gerenciamento de usuários para o perfil `Admin`.
- Alteração e exclusão de usuários.
- Exportação da lista de usuários em PDF.
- Verificação de CSRF nos formulários de autenticação e alteração de dados.

## 🚀 Executar com Docker

Consulte o [guia Docker](docs/COMO_EXECUTAR_DOCKER.md). A aplicação fica em `http://localhost:8080`, o PHPMyAdmin em `http://localhost:8085` e o MySQL pode ser acessado pelo host na porta `3308`.

```powershell
Copy-Item .env.example .env
# Ative o bloco DOCKER no .env antes de subir os serviços.
docker compose up -d --build
```

## 🖥️ Executar localmente

Consulte o [guia local](docs/COMO_EXECUTAR_LOCAL.md). O servidor embutido do PHP atende em `http://127.0.0.1:8000`.

## 🔐 Acesso de demonstração

| Perfil | Usuário | Senha |
| --- | --- | --- |
| Administrador | `Alberto` | `123` |

Essas credenciais são apenas para desenvolvimento local e estão no script SQL de inicialização. No primeiro login, a senha antiga em texto puro é convertida em hash.

## 🧪 Validação e testes manuais

Os endpoints e os resultados de validação estão descritos em [Acessos e dados de teste](docs/ACESSOS_TESTES.md). O projeto não inclui suíte automatizada de testes.

## 🖼️ Preview

🚧 Preview não disponível no repositório.

## 📁 Estrutura principal

```text
.
├── css/                   # Estilos das páginas
├── docker/                # Configurações de PHP e Apache
├── docs/                  # Guias de execução, acessos e análise
├── js/                    # Interações da interface
├── auth.php               # Sessão, acesso e CSRF
├── conexao.php            # Carregamento do .env e conexão MySQL
├── cadastro*.php          # Cadastro de usuários
├── login.php              # Validação de credenciais
├── listarUsuarios.php     # Lista administrativa
└── relatorioUsuario.php   # Relatório em PDF
```

## 🛠️ Comandos úteis

```bash
composer install
php -S 127.0.0.1:8000 -t .
docker compose up -d --build
docker compose ps
docker compose logs -f app mysql
```

> Estes são comandos básicos. Consulte os arquivos em `docs/` para a configuração completa de cada ambiente.

## 🔭 Melhorias futuras

- Adicionar recuperação de senha e limitação de tentativas de login.
- Criar testes automatizados para autenticação e operações de usuários.
- Migrar gradualmente as páginas PHP para uma estrutura de aplicação mais modular.

<div align="center">

Feito com ❤️ por Gabriel Martins 🚀

</div>
