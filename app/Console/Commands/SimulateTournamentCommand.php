<?php
namespace App\Console\Commands;

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

use App\Constants\ErrorMessages\GeneralStatusResponse;
class SimulateTournamentCommand extends Command
{
    protected $signature = 'app:simulate-tournament-command';
    protected $description = 'Simular un torneo por eliminación directa';

    private $jwt;

    // Definir constantes reutilizables
    const OPTIONS = [
        'createPlayer'     => 'Crear jugador',
        'createTournament' => 'Crear torneo',
        'startTournament'     => 'Empezar torneo',
        'getPlayersDisplay'       => 'Obtener jugadores',
        'getTournaments'       => 'Obtener torneos',
    ];

    const MESSAGES = [
        'selectedOption' => 'Seleccionaste la opción: ',
        'enterJwt' => '>>>> Ingrese el token JWT ',
        'invalidInput' => 'Error en la validación:',
        'successPlayer' => 'Jugador creado exitosamente',
        'errorPlayerCreation' => 'Error al crear el jugador: ',
    ];

    const BASE_URL = 'http://localhost:8000/api/';

    public function handle()
    {
        try {
            $this->showInstructions();
            $this->askJwtToken();
            $this->main();
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage(), $e->getLine());
        }
       
    }

    private function main(){
        $option = $this->mainOptions();
        $this->executeOption($option);
    }

    private function showInstructions(): void
    {
        $this->info('#####################################################Instrucciones #####################################################');
        $this->info(
            "\nEste comando permite simular el FRONT del sistema.\n" .
            "Asegúrese de que el sistema esté corriendo localmente.\n" .
            "Luego, use Swagger para registrar un nuevo usuario y obtener el token JWT."
        );
        $this->info('#####################################################Instrucciones #####################################################');
    }

    private function mainOptions(): string
    {
        $choice = $this->choice('Opciones:', array_values(self::OPTIONS), 0);
        return array_search($choice, self::OPTIONS);
    }

    private function askJwtToken(): void
    {
        $jwt = $this->ask(self::MESSAGES['enterJwt']."\n ejemplo (Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...)");

        $validator = Validator::make(['jwt' => $jwt], [
            'jwt' => 'required|string|max:455',
        ]);

        if ($validator->fails()) {
            $this->handleValidationErrors($validator);
            $this->askJwtToken(); // Recursión defensiva
        } else {
            $this->jwt = $jwt;
        }
    }

    private function handleValidationErrors($validator): void
    {
        $this->error(self::MESSAGES['invalidInput']);
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }
    }

    private function executeOption(string $option): void
    {
        if (method_exists($this, $option)) {
            $this->$option();
        } else {
            $this->error('Opción inválida seleccionada.');
        }
    }

    public function createPlayer(): void
    {
        $this->info('######### Crear jugador #########');

        $gender = $this->getGender();
        $name = $this->ask('Ingrese el nombre');
        $ability = $this->ask('Ingrese la habilidad (1-100)');
        $atributesPlayer = $this->askJsonAttributes($gender);
     

        $validator = Validator::make([
            'gender' => $gender,
            'name' => $name,
            'ability' => $ability,
            'atributesPlayer' => $atributesPlayer,
        ], [
            'gender' => 'required|string|exists:genders,slug',
            'name' => 'required|string|max:255',
            'ability' => 'required|int|min:1|max:100',
            'atributesPlayer' => 'required|array',
        ]);

        if ($validator->fails()) {
            $this->handleValidationErrors($validator);
            $this->createPlayer(); // Recursión defensiva
            return;
        }

        $parameters = [
            'gender' => $gender,
            'name' => $name,
            'ability' => $ability,
            'atributesPlayer' => $atributesPlayer,
        ];

        // Usa la URL base y la ruta de registro
        $url = self::BASE_URL . 'players/register';

        if ($this->postSend($url, $parameters)) {
            $this->main();
        } else {
            $this->createPlayer(); // Recursión defensiva
        }
    }

    private function getGender(): string
    {
        $this->info('Ingresa el género (slug) ---- GÉNEROS DISPONIBLES:');
        $response = $this->getSend(self::BASE_URL . 'genders/all');
         $response = json_decode($response, true);
        if($response['status'] ===  GeneralStatusResponse::SUCCESS) {
            $data = $response['data'];
            foreach ($data as $item) {
                $this->info("Slug: {$item['slug']}, Nombre: {$item['name']}");
            }
        }
        return $this->ask('');
    }

 
    private function askJsonAttributes(string $gender): array
    {
        $this->info('Ingrese los atributos del jugador en formato JSON (ejemplo {"slugString":ValorInt}) ---- ATRIBUTOS REQUERIDOS POR EL GENERO:');
        $response = $this->getSend(self::BASE_URL . 'genders/attributes', [$gender]);
        $response = json_decode($response, true);
        if ($response['status'] === GeneralStatusResponse::SUCCESS) {
            $data = $response['data'];
            foreach ($data as $item) {
                $this->info("Slug: {$item['slug']}, Nombre: {$item['name']}");
            }
        }
    
        $attributes = [];
        while (true) {
            $jsonAttributes = $this->ask('Ingrese un atributo en formato JSON o presione Enter para finalizar:');
            if (empty($jsonAttributes)) {
                break; // Salir del bucle si el usuario presiona Enter sin ingresar nada
            }
    
            $attribute = json_decode($jsonAttributes, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Formato JSON inválido. Intente nuevamente.');
                continue; // Pedir nuevamente si el formato JSON es inválido
            }
    
            // Asegúrate de que el atributo ingresado sea un array clave-valor
            if (is_array($attribute)) {
                $attributes = array_merge($attributes, $attribute);
            } else {
                $this->error('Formato JSON inválido. Intente nuevamente.');
            }
        }
    
        dump("Atributos ingresados");
        dump($attributes);
        return $attributes;
    }
    
    private function sendRequest(string $method, string $url, array $parameters = [], array $body = []): ?string
    {
        $urlWithParams = $this->buildUrlWithParameters($url, $parameters);
    
        $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $this->jwt,
                            'Content-Type' => 'application/json',
                            'accept' => 'application/json'
                        ])
                        ->withBody(json_encode($body), 'application/json')
                        ->$method($urlWithParams);
    
        // Construir manualmente el comando cURL
        $curlCommand = $this->buildCurlCommand($method, $urlWithParams, $body, [
            'Authorization' => 'Bearer ' . $this->jwt,
            'Content-Type' => 'application/json',
            'accept' => 'application/json'
        ]);
        info($curlCommand);
    
        if ($response->successful()) {
            return $response->body();
        }
    
        $this->error(self::MESSAGES['errorPlayerCreation'] . $response->body());
        return null;
    }
    
    private function buildCurlCommand(string $method, string $url, array $body, array $headers): string
    {
        $curlCommand = "curl --location -X '$method' '$url'";
        
        foreach ($headers as $key => $value) {
            $curlCommand .= " --header '$key: $value'";
        }
    
        if (!empty($body)) {
            $bodyString = json_encode($body, JSON_PRETTY_PRINT);
            $curlCommand .= " --data '" . addslashes($bodyString) . "'";
        }
    
        return $curlCommand;
    }

    private function buildUrlWithParameters(string $url, array $parameters): string
    {
        if (empty($parameters)) {
            return $url;
        }

        // Construir URL con parámetros
        $paramString = implode('/', $parameters);
        return $url . '/' . $paramString;
    }

    private function postSend(string $url, array $parameters): ?string
    {
        return $this->sendRequest('post', $url, [], $parameters);
    }

    private function getSend(string $url, array $parameters = []): ?string
    {
       
        return $this->sendRequest('get', $url, $parameters);
    }

    public function createTournament(): void
    {
        $this->info('######### Crear Torneo #########');

        $gender = $this->getGender();
        $name = $this->ask('Ingrese el nombre');
        $numberPlayers = $this->ask('Ingrese la habilidad cantidad de jugadores');
        
        $validator = Validator::make([
            'gender'  => $gender,
            'name'    => $name,
            'number_players' => $numberPlayers,
        ], [
            'gender'  => 'required|string|exists:genders,slug',
            'name'    => 'required|string|max:255',
            'number_players' => 'required|int|min:2',
        ]);

        if ($validator->fails()) {
            $this->handleValidationErrors($validator);
            $this->createTournament();
            return;
        }

        $parameters = [
            'gender' => $gender,
            'name' => $name,
            'number_players' => $numberPlayers
        ];

        // Usa la URL base y la ruta de registro
        $url = self::BASE_URL . 'tournaments/register';

        $response = $this->postSend($url, $parameters);
        $response = json_decode($response, true);
        if ($response['status'] ===  GeneralStatusResponse::SUCCESS) {
            $this->info('Torneo creado exitosamente');
            $this->addPlayerToTournament($response['data']['id'], $gender);
           
        } else {
            $this->error('Error al crear el torneo');
            $this->createTournament(); // Recursión defensiva
        }
    }
    
    //Agregar jugador a torneo
    public function addPlayerToTournament(int $tournamentId, string $gender): void{
        do{
            $this->info('######### Agregar jugador a torneo #########');
            $playerId = $this->getPlayers($gender,$tournamentId);
            $parameters = [
                'tournament_id' => $tournamentId,
                'player_id' => $playerId
            ];
            $url = self::BASE_URL . 'tournaments/register-player';
            $response = $this->postSend($url, $parameters);
            $response = json_decode($response, true);
            if (!$response['status'] ===  GeneralStatusResponse::SUCCESS) {
                $this->error('Error al agregar jugador al torneo');
                $this->addPlayerToTournament($tournamentId, $gender); 
            } 
            dump("Jugador agregado al torneo");
            $isCompleteTournament = $this->isCompleteTournament($tournamentId);
            if($isCompleteTournament){
                $this->startTournament();
            }
            if(!$isCompleteTournament) dump("Si el torneo no tiene el numero de jugadores completos no podra iniciar");
        }while($this->confirm('¿Desea agregar otro jugador?'));
        $this->main();
    }

    // verifica si el torneo esta completo
    public function isCompleteTournament($tournamentId): bool
    {
        $response = $this->getSend(self::BASE_URL . 'tournaments/is-complete', [$tournamentId]);
        $response = json_decode($response, true);
        if ($response['status'] ===  GeneralStatusResponse::SUCCESS) {
            if ($response['isComplete']) {
                $this->info('El torneo está completo.');
                return true;
            } else {
                $this->info('El torneo no está completo.');
                return false;
            }
        }
        return false;
    }

    public function startTournament(): void
    {
        $this->info('Lógica para empezar torneo aquí.');
    }

    public function getPlayers($gender,$tournament): string
    {
        $this->info('Ingresa el Jugador por (id) ---- Jugadores DISPONIBLES:');
        $response = $this->getSend(self::BASE_URL . 'players/gender/nottournament', [$gender,$tournament]);
        $response = json_decode($response, true);
    
        // Verificar si la respuesta es exitosa
        if ($response['status'] ===  GeneralStatusResponse::SUCCESS) {
            $data = $response['data'];
            foreach ($data as $item) {
                $this->info("ID: {$item['id']}, Nombre: {$item['name']}");
            }
        }
        return $this->ask('');
    }

     public function getPlayersDisplay(): void
    {
            $gender = $this->getGender();
            $this->info('Lista de Jugadores:');
            $response = $this->getSend(self::BASE_URL . 'players/gender', [$gender]);
            $response = json_decode($response, true);

        // Verificar si la respuesta es exitosa
        if ($response['status'] ===  GeneralStatusResponse::SUCCESS) {
            $data = $response['data'];
            foreach ($data as $item) {
                $this->info("ID: {$item['id']}, Nombre: {$item['name']}");
            }
            $this->main();
        }
    }
}
