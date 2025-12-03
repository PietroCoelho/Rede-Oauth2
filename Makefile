.PHONY: help build up down test cs-check cs-fix phpstan install shell setup-env

CONTAINER_NAME = rede-auth-php
IMAGE_NAME = rede-auth-php

help: ## Mostra esta mensagem de ajuda
	@echo "Comandos disponíveis:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Constrói a imagem Docker
	docker build -t $(IMAGE_NAME) .

up: ## Inicia o container
	@if [ -z "$$(docker ps -q -f name=$(CONTAINER_NAME))" ]; then \
		docker run -d --name $(CONTAINER_NAME) \
			-v $$(pwd):/var/www/html \
			-v $$(pwd)/docker/php/php.ini:/usr/local/etc/php/php.ini \
			-w /var/www/html \
			$(IMAGE_NAME); \
		echo "Container iniciado!"; \
	else \
		echo "Container já está rodando!"; \
	fi

down: ## Para e remove o container
	@if [ -n "$$(docker ps -q -f name=$(CONTAINER_NAME))" ]; then \
		docker stop $(CONTAINER_NAME); \
		docker rm $(CONTAINER_NAME); \
		echo "Container parado e removido!"; \
	else \
		echo "Container não está rodando!"; \
	fi

install: ## Instala as dependências do projeto
	docker exec -it $(CONTAINER_NAME) composer install

test: ## Executa os testes
	docker exec -it $(CONTAINER_NAME) composer test

cs-check: ## Verifica o código com PHP_CodeSniffer
	docker exec -it $(CONTAINER_NAME) composer cs-check

cs-fix: ## Corrige o código com PHP_CodeSniffer
	docker exec -it $(CONTAINER_NAME) composer cs-fix

phpstan: ## Executa análise estática com PHPStan
	docker exec -it $(CONTAINER_NAME) composer phpstan

shell: ## Abre um shell no container
	docker exec -it $(CONTAINER_NAME) bash

setup-env: ## Cria o arquivo .env a partir do exemplo
	@if [ ! -f .env ]; then \
		cp env.example .env; \
		echo "Arquivo .env criado! Configure suas credenciais no arquivo .env"; \
	else \
		echo "Arquivo .env já existe!"; \
	fi

