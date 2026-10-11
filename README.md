# vercan — Backend

API em Laravel 13 com autenticação Bearer e banco MySQL. 

Frontend: https://github.com/gabriel5ss/desafio-vercan-frontend

## Docker

Pré-requisitos: Docker Engine e Docker Compose. O backend pode ser executado
sozinho, com seu próprio MySQL:

```bash
cp .env.example .env
php artisan key:generate
docker compose up -d --build
```

No servidor, mantenha um `.env` próprio no diretório de deploy, com `APP_KEY`,
`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` e `DB_ROOT_PASSWORD` definidos.
Use senhas fortes para o banco. O workflow preserva esse arquivo entre os
deploys.

A API fica em `http://localhost:8089` e o health check em `/up`. As migrations
são executadas na inicialização. Os dados do MySQL e os arquivos de `storage`
ficam em volumes Docker. Para criar o usuário de demonstração e carregar estados
e cidades:

```bash
docker compose exec backend php artisan db:seed
```

### Portas

O container escuta na porta `8089`, publicada como `8089` no host (`8089:8089`
no `docker-compose.yml`). Se precisar trocar somente a porta acessada no host,
altere o valor à esquerda, por exemplo `8090:8089`. Para trocar a porta interna
da aplicação, mantenha sincronizados o valor à direita do mapeamento, o
`EXPOSE` do Dockerfile, o `--port` do entrypoint e o health check.

No `.env`, ajuste `APP_URL` para o endereço usado pelos clientes. Em produção,
prefira a URL pública HTTPS da API; se um proxy encaminhar essa URL para o
container, não é necessário expor a porta `8089` publicamente.

O seeder de estados e cidades consulta o IBGE e precisa de acesso à internet.
Usuário de demonstração: `teste@desafio.com` / `password`. Para encerrar, use
`docker compose down`; os volumes são mantidos. `docker compose down -v` também
apaga os dados persistidos.

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
php artisan serve --host=0.0.0.0 --port=8089
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

## Hooks locais do Git

Para executar os testes, o PHPStan e a verificação do Pint antes de cada
commit, ative o hook uma vez:

```bash
./scripts/install-git-hooks.sh
```

O hook requer as dependências instaladas pelo `composer install`.
