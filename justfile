set dotenv-load

alias bd := build
alias u := up
alias s := stop
alias rp := reset-permissions

alias b := bash
alias cct := cc-test

alias start := setup-project
alias sdb := setup-database
alias rdb := reset-database
alias ddb := drop-database
alias cdb := create-database
alias m := migrate
alias f := fixtures
alias mm := make-migration

alias sdbt := setup-database-test
alias ddbt := drop-database-test
alias cdbt := create-database-test
alias mt := migrate-test
alias ft := fixtures-test

alias e := ecs

alias c := composer-install

alias im := importmap
alias ir := importmap-require
alias ii := importmap-install
alias iu := importmap-update
alias irm := importmap-remove
alias ima := importmap-audit

alias tw := tailwind-watch

alias ss := supervisor-start
alias sp := supervisor-stop

container-name := "ns2-ubcr-php-1"
docker-running := `docker ps -q --filter name=ns2-ubcr-php-1 | grep -q . && echo true || echo false`

d := if docker-running == "true" { "docker exec -t " + container-name + " php" } else { "php" }
shell := if docker-running == "true" { "docker exec -t " + container-name } else { "" }
console := if docker-running == "true" { "docker exec -t " + container-name + " php bin/console" } else { "php bin/console" }

default:
    just --list

#---------- Docker management ----------
setup-project:
    @echo "Building and starting the project..."
    just bd
    just u
    just rp
    just sdb
    just sdbt
    @echo "Project is ready!"
    @echo "You can now access the project at https://localhost"
    @echo "Webmail is available at http://localhost:8025"

build:
    COMPOSE_BAKE=true docker compose build --no-cache

up:
    docker compose -f compose.yaml -f compose.override.yaml up -d

down:
    docker compose down --remove-orphans

stop:
    docker compose stop

prune:
    docker compose down --remove-orphans
    docker compose down --volumes
    docker compose rm -f

reset-permissions:
    sudo chown -Rf $(id -u):$(id -g) ./

#---------- Container commands ----------
bash:
    @docker exec -it ns2-ubcr-php-1 bash

#---------- Symfony commands ----------
cc:
    {{console}} cache:clear

cc-test:
    {{console}} cache:clear -e test

#---------- Database commands ----------
setup-database:
    just m
    just f

reset-database:
    @echo "Resetting the database..."
    just ddb
    just sdb
#    just sp
#    just ss

create-database:
    {{console}} doctrine:database:create --if-not-exists

drop-database:
    {{console}} doctrine:database:drop --force --if-exists

migrate:
    {{console}} doctrine:migrations:migrate --no-interaction

fixtures:
    {{console}} doctrine:fixtures:load --no-interaction

make-migration:
    {{console}} make:migration

setup-database-test:
    just ddbt
    just cdbt
    just mt
    just ft

create-database-test:
    {{console}} doctrine:database:create --if-not-exists -e test

drop-database-test:
    {{console}} doctrine:database:drop --force --if-exists -e test

migrate-test:
    {{console}} doctrine:migrations:migrate --no-interaction -e test

fixtures-test:
    {{console}} doctrine:fixtures:load --no-interaction -e test

#---------- Symfony & ImportMap commands ----------
importmap:
    {{console}} importmap

importmap-require:
    {{console}} importmap:require

importmap-install:
    {{console}} importmap:install

importmap-update:
    {{console}} importmap:update

importmap-remove:
    {{console}} importmap:remove

importmap-audit:
    {{console}} importmap:audit

tailwind-watch:
    {{console}} tailwind:build --watch

#---------- Test & Analysis commands ----------
tests:
    {{d}} vendor/bin/pest

stan:
    {{d}} vendor/bin/phpstan analyse --memory-limit=256M

ecs:
    {{d}} vendor/bin/ecs check --fix

ecs-check:
    {{d}} vendor/bin/ecs check

#---------- Composer commands ----------
composer-install:
    {{shell}} composer install

#---------- Supervisor commands ----------
supervisor-start:
    {{shell}} supervisorctl start all

supervisor-stop:
    {{shell}} supervisorctl stop all
