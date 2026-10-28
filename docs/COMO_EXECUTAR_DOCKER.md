# Como Executar com Docker — Sistema de Cadastro Web

## Serviços e portas

| Serviço | Função | Porta no computador |
| --- | --- | --- |
| `app` | PHP 8.2 com Apache | `8080` |
| `mysql` | Banco MySQL 8.0 | `3308` |
| `phpmyadmin` | Administração do MySQL | `8085` |

## 1. Preparar ambiente

```powershell
Copy-Item .env.example .env
```

No `.env`, comente o bloco `LOCAL` e ative `DOCKER`. Os valores de exemplo são para desenvolvimento; troque as senhas antes de compartilhar o ambiente.

## 2. Subir os serviços

```bash
docker compose up -d --build
docker compose ps
```

- Aplicação: <http://localhost:8080>
- PHPMyAdmin: <http://localhost:8085>
- MySQL pelo computador host: `127.0.0.1:3308`
- MySQL entre containers: `mysql:3306`

Na primeira inicialização, `Script-BD-Escola.txt` cria o banco, tabela e conta de demonstração. Os dados são mantidos no volume `mysql_data`.

## 3. Acesso de demonstração

| Perfil | Usuário | Senha |
| --- | --- | --- |
| Administrador | `Alberto` | `123` |

No PHPMyAdmin, informe servidor `mysql` e use o usuário e a senha definidos no bloco `DOCKER` do `.env`.

## 4. Logs e diagnóstico

```bash
docker compose logs -f app
docker compose logs -f mysql
docker compose ps
```

## 5. Reiniciar e limpar

```bash
docker compose down
docker compose up -d --build
```

Para apagar também o banco persistido e recriá-lo:

```bash
docker compose down -v
docker compose up -d --build
```

`docker compose down -v` remove os dados do volume MySQL.

## Atualizar um banco Docker criado antes da migração de senha

O projeto agora armazena hashes de senha em uma coluna maior. Para atualizar um volume já existente, execute uma vez:

```bash
docker compose exec mysql mysql -u root -p escola -e "ALTER TABLE usuarios MODIFY senha VARCHAR(255) NOT NULL, MODIFY email VARCHAR(255) NOT NULL;"
```

Quando solicitado, informe `MYSQL_ROOT_PASSWORD` do `.env`.
