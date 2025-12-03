<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Contourne un bug connu de PHP ("Narrowing occurred during type inference of ZEND_FETCH_DIM_W")
// en masquant les warnings au niveau de l'affichage tout en conservant les erreurs.
// À retirer une fois PHP mis à jour.
error_reporting(E_ALL & ~E_WARNING);

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
