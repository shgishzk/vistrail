start:
	docker compose up -d
build:
	docker compose build
install:
	docker compose up -d --build
	docker compose exec app composer install
	docker compose exec app cp .env.example .env
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate:fresh
	docker compose exec app php artisan storage:link
stop:
	docker compose down
delete:
	docker compose down --volumes
	docker volume prune -f
	docker network prune -f
restart:
	docker compose down
	docker compose up -d --build
logs:
	docker compose logs -f
shell:
	docker compose exec app bash

# Artisan
artisan:
	docker compose exec app php artisan $(args)
migrate:
	docker compose exec app php artisan migrate
migrate-fresh:
	docker compose exec app php artisan migrate:fresh
migrate-refresh:
	docker compose exec app php artisan migrate:refresh
seed:
	docker compose exec app php artisan db:seed

# Composer
composer-install:
	docker compose exec app composer install
composer-update:
	docker compose exec app composer update
composer-dump-autoload:
	docker compose exec app composer dump-autoload

# NPM
npm-install:
	docker compose run --rm npm install
npm-update:
	docker compose run --rm npm update
npm-run-dev:
	docker compose run --rm npm run dev

# PHPUnit
test:
	docker compose exec app vendor/bin/phpunit
init-test:
	docker compose exec db sh -c "export MYSQL_PWD=secret; mysql -uroot -e \"CREATE DATABASE test_vistrail;\""
	docker compose exec db sh -c "export MYSQL_PWD=secret; mysql -uroot -e \"GRANT ALL PRIVILEGES ON *.* TO 'vistrail'@'%';\""