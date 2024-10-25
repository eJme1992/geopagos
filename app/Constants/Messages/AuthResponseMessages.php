<?php 

namespace App\Constants\Messages;

class AuthResponseMessages {
    // Login messages
    const LOGIN_SUCCESS           = 'El usuario se ha logeado correctamente';
    const LOGIN_FAILURE           = 'El usuario no se ha podido logear correctamente';
    const LOGIN_USER_NOT_FOUND    = 'Los datos no han sido encontrados';

    // Registration messages
    const REGISTRATION_SUCCESS    = 'El usuario ha sido creado';
    const REGISTRATION_FAILURE    = 'El usuario no ha sido creado';

    // General error messages
    const INTERNAL_SERVER_ERROR   = 'Ocurrió un error interno en el servidor';
}