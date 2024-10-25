<?php

namespace App\Constants\Messages;

class TournamentResponseMessages
{
    public const TOURNAMENT_CREATED_SUCCESS = "Torneo creado exitosamente";
    public const PLAYER_REGISTERED_SUCCESS = "Jugador registrado exitosamente";
    public const TOURNAMENT_IS_COMPLETE = "El torneo ya tiene el número de jugadores requeridos";
    public const PLAYER_ALREADY_REGISTERED = "El jugador ya está registrado en el torneo";
    public const TOURNAMENT_NOT_FOUND = "El torneo especificado no existe";
    public const TOURNAMENT_INVALID_PLAYERS = "El número de participantes debe ser mayor a cero y múltiplo de dos";
    public const ERROR_CREATING_TOURNAMENT = "Error al crear el torneo";
    public const SERVER_ERROR = "Error interno del servidor";
    public const TOURNAMENT_FETCH_SUCCESS = "Lista de torneos obtenida exitosamente";
}
