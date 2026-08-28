<?php
// Router temporal para `php -S` (equivalente a vendor/laravel/framework's server.php),
// usado solo porque ese archivo falla al resolverse por rutas con tildes en Windows.
// Se borra al terminar la prueba manual del CRUD.
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
