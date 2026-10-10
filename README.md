# vercan — Backend

API em Laravel 13 com autenticação Bearer e banco MySQL.

## Docker

Pré-requisitos: Docker Engine e Docker Compose. O Compose fica no projeto
`desafio-vercan-front` e inicia o frontend, a API e o MySQL. Com os projetos
lado a lado:

```bash
cd ../desafio-vercan-front
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose up --build
```

A API fica em http://localhost:8000 e o frontend em http://localhost:3000. O
container do backend cria a chave da aplicação e executa as migrations. O banco
`vercan` usa o usuário de desenvolvimento `vercan`/`vercan`; os dados ficam no
volume `mysql_data`. Para criar o usuário de demonstração e carregar estados e
cidades:

```bash
docker compose exec backend php artisan db:seed
```

O seeder de estados e cidades consulta o IBGE e precisa de acesso à internet.
Usuário de demonstração: `teste@desafio.com` / `password`.

Para encerrar, use `Ctrl+C` e execute `docker compose down`. Para apagar também
os dados persistidos, use `docker compose down -v`.

## Execução manual

Pré-requisitos: PHP 8.3 ou superior, Composer, MySQL e extensões PHP para
BCMath, Intl, Mbstring, PDO MySQL e Zip. Também é necessário acesso à internet
para carregar estados e cidades. Configure a conexão do banco no `.env`.

Na pasta deste projeto:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve --host=0.0.0.0 --port=8000
```

O comando `db:seed` cria o usuário de demonstração e carrega estados e cidades
do IBGE. Para cadastrar também os 10 fornecedores de demonstração, execute:

```bash
php artisan db:seed --class=SupplierDemoSeeder
```

O mailer padrão salva emails de redefinição de senha no log. Configure SMTP no
`.env` para enviá-los de verdade.

## Comandos úteis

```bash
php artisan test
composer analyse
vendor/bin/pint
```
