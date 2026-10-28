# Como Executar Localmente — Sistema de Cadastro Web

Guia para executar sem Docker, usando MySQL local e o servidor embutido do PHP.

> Prefere evitar a instalação de PHP e MySQL? Use o [guia Docker](COMO_EXECUTAR_DOCKER.md).

## Requisitos

| Ferramenta | Versão |
| --- | --- |
| PHP | 8.1 ou superior |
| Composer | 2.x |
| MySQL | 8.0 ou superior |

Extensões PHP: `mysqli`, `mbstring`, `dom` e `pdo_mysql`.

## 1. Preparar o projeto

```powershell
Copy-Item .env.example .env
```

Deixe `LOCAL` ativo e `DOCKER` comentado. O bloco local padrão usa `127.0.0.1:3307`, usuário `root` e senha vazia. Ajuste o host, a porta e a senha se o MySQL local estiver configurado de outra forma.

## 2. Criar e popular o banco

No MySQL, crie o banco:

```sql
CREATE DATABASE escola CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Na pasta do projeto, importe o script:

```powershell
Get-Content .\Script-BD-Escola.txt | mysql -h 127.0.0.1 -P 3307 -u root
```

Para um banco criado antes desta atualização, amplie as colunas uma vez:

```sql
ALTER TABLE usuarios
    MODIFY senha VARCHAR(255) NOT NULL,
    MODIFY email VARCHAR(255) NOT NULL;
```

## 3. Instalar dependências

```bash
composer install
```

## 4. Iniciar a aplicação

```bash
php -S 127.0.0.1:8000 -t .
```

Abra <http://127.0.0.1:8000>. A tela de login é `index.php` e o formulário envia os dados para `login.php`.

## Acesso de demonstração

| Perfil | Usuário | Senha |
| --- | --- | --- |
| Administrador | `Alberto` | `123` |

## Problemas comuns

- **Banco não conecta:** confira o bloco `LOCAL` no `.env` e se o serviço MySQL está ativo na porta configurada.
- **Dependência ausente:** execute `composer install` novamente.
- **E-mail já cadastrado:** cada endereço é único no banco; escolha outro no cadastro.
- **Senha demo não funciona:** confirme que o script SQL inseriu o usuário `Alberto` no banco `escola`.
