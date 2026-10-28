<?php
/**
 * Configuração da conexão com o banco de dados.
 * Padrão do XAMPP: servidor localhost, usuário "root" e senha vazia.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'logifrete');
define('DB_USER', 'root');
define('DB_PASS', '');

// Permite sobrescrever a configuração localmente (arquivo ignorado pelo Git)
if (file_exists(__DIR__ . '/local.php')) {
    require __DIR__ . '/local.php';
}

/**
 * Retorna a conexão PDO (cria uma única vez por requisição).
 */
function conectar(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = defined('DB_DSN')
            ? DB_DSN
            : 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                $pdo->exec('PRAGMA foreign_keys = ON');
            }
        } catch (PDOException $erro) {
            die('<h3>Erro ao conectar ao banco de dados.</h3>'
              . '<p>Verifique se o MySQL do XAMPP está ligado e se o script sql/banco.sql foi executado.</p>'
              . '<small>' . htmlspecialchars($erro->getMessage()) . '</small>');
        }
    }

    return $pdo;
}
