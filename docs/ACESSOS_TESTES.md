# 🔐 Acessos e Dados de Teste

As informações abaixo vieram do script `Script-BD-Escola.txt`. Não há seeders no projeto. Use essas credenciais apenas em ambiente local ou Docker de desenvolvimento.

## 1. Usuários de demonstração (Multi-perfil)

| Perfil | Usuário | Senha | E-mail | Permissões & Detalhes |
| --- | --- | --- | --- | --- |
| **Administrador** | `Alberto` | `123` | `alberto@email.com` | Acesso irrestrito ao painel, listagem, edição, exclusão, relatório PDF e cadastro de novos usuários com atribuição de perfil. |
| **Professor** | `ProfessorCarlos` | `12345678` | `professor@escola.com` | Dashboard docente exclusivo com módulos de diário de classe, notas e turmas. Bloqueado de alterações administrativas (403). |
| **Aluno** | `AlunoLucas` | `12345678` | `aluno@escola.com` | Dashboard acadêmico exclusivo com boletim, secretaria online e dados do aluno. Bloqueado de alterações administrativas (403). |

O formulário público de auto-cadastro cria usuários com perfil `Aluno`. O administrador logado pode cadastrar usuários escolhendo o nível (`Admin`, `Prof`, `Aluno`) e permanece logado após a criação.

## 2. URLs principais

| Ambiente | Aplicação | Login | Painel de usuários |
| --- | --- | --- | --- |
| Docker | `http://localhost:8080/` | `http://localhost:8080/index.php` | `http://localhost:8080/listarUsuarios.php` |
| Local | `http://127.0.0.1:8000/` | `http://127.0.0.1:8000/index.php` | `http://127.0.0.1:8000/listarUsuarios.php` |

Login é um formulário dentro de `index.php`; `login.php` recebe o POST. `home.php` é mostrado após autenticação. Não há vitrine pública separada nem painel externo.

## 3. Páginas disponíveis

| Item | URL Docker |
| --- | --- |
| Tela de login | `http://localhost:8080/` |
| Cadastro de usuário | `http://localhost:8080/cadastroUsuario.php` |
| Início autenticado | `http://localhost:8080/home.php` |
| Gerenciamento de usuários (Admin) | `http://localhost:8080/listarUsuarios.php` |
| PHPMyAdmin | `http://localhost:8085/` |

## 4. Validação e Testes Automatizados

Para executar a suíte automatizada de testes que cobre todos os 10 fluxos de ponta a ponta:

```bash
docker exec cadastro-web-app-1 php /var/www/html/tests/test_system.php
```

| Verificação | Resultado esperado |
| --- | --- |
| `app`, `mysql`, `phpmyadmin` | Containers em execução; `app` e `mysql` saudáveis |
| Tela principal | HTTP `200` |
| Credenciais válidas | Redirecionamento HTTP `302` para `home.php` |
| Credenciais inválidas | Retorno à tela de login com mensagem genérica |
| Rate Limiting (5 falhas) | Retorno à tela de login com bloqueio temporário |
| Acesso anônimo a `home.php` | Redirecionamento para o login |
| Usuário sem perfil Admin abre painel | HTTP `403` com página de erro amigável |

## 5. Recriar os dados locais

O script SQL é aplicado automaticamente quando o volume MySQL é criado pela primeira vez. Para recriar o banco Docker e apagar seus dados:

```bash
docker compose down -v
docker compose up -d --build
```

Para um MySQL local, crie o banco `escola` e importe `Script-BD-Escola.txt` conforme [o guia local](COMO_EXECUTAR_LOCAL.md).

## 6. Validação executada

Validado no Docker em 28/10/2026:

| Fluxo | Resultado observado |
| --- | --- |
| Containers `app`, `mysql` e `phpmyadmin` | Em execução; `app` e `mysql` saudáveis |
| Login correto e acesso à home | HTTP `302` para a home, que responde `200` |
| Senha incorreta | Retorno à tela de login com mensagem genérica |
| Página de usuários como Admin | HTTP `200`; senhas não são exibidas |
| Relatório PDF como Admin | HTTP `200`, `application/pdf` |
| Home sem sessão | Redireciona para a tela de login |
| Logout com CSRF | Encerra a sessão e redireciona para o login |
| Painel como perfil Aluno | HTTP `403` |
| Cadastro de usuário | HTTP `302`; novo usuário consegue entrar |
| Edição e exclusão como Admin | Perfil atualizado; registro temporário removido |
| PHPMyAdmin | HTTP `200` |

O PHP embutido local (PHP 8.1) também foi executado e autenticou contra o banco MySQL exposto pelo Docker.

O usuário temporário usado na validação foi removido. A senha demo `123` foi migrada para hash no primeiro login válido.
