<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis de ambiente do arquivo .env
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = __DIR__ . '/../.env';
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Processa linhas no formato KEY=VALUE
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove aspas se existirem
            $value = trim($value, '"\'');
            
            // Define a variável de ambiente se ainda não estiver definida
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

