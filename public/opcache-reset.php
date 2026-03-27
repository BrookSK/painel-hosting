<?php
// Temporário — remover após uso
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache limpo.';
} else {
    echo 'OPcache não está ativo.';
}
