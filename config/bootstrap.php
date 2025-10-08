<?php

set_error_handler(function ($severity, $message, $file, $line) {
    if (str_contains($message, 'Narrowing occurred during type inference of ZEND_FETCH_DIM_W')) {
        return true;
    }

    return false;
}, E_WARNING);
