<?php
/**
 * Arquivo incluído no início de todas as páginas.
 */
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/funcoes.php';
