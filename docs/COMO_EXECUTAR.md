# Como Executar — Sistema de Cadastro Web

Escolha o guia conforme seu ambiente:

| Guia | Quando usar | Requisitos |
| --- | --- | --- |
| [COMO_EXECUTAR_DOCKER.md](COMO_EXECUTAR_DOCKER.md) | Executar em containers | Docker Desktop |
| [COMO_EXECUTAR_LOCAL.md](COMO_EXECUTAR_LOCAL.md) | Desenvolver sem Docker | PHP, Composer e MySQL |
| [ACESSOS_TESTES.md](ACESSOS_TESTES.md) | Credenciais e validações manuais | Aplicação iniciada |

## Início rápido

### Docker

```powershell
Copy-Item .env.example .env
```

Ative o bloco `DOCKER` no `.env` e execute:

```bash
docker compose up -d --build
```

Aplicação: <http://localhost:8080> · PHPMyAdmin: <http://localhost:8085>

### Local

Ative o bloco `LOCAL` no `.env`, crie o banco `escola`, importe `Script-BD-Escola.txt` e execute:

```bash
composer install
php -S 127.0.0.1:8000 -t .
```

Aplicação: <http://127.0.0.1:8000>

## Acesso de demonstração

| Perfil | Usuário | Senha |
| --- | --- | --- |
| Administrador | `Alberto` | `123` |

Consulte [ACESSOS_TESTES.md](ACESSOS_TESTES.md) para as URLs e verificações detalhadas.
