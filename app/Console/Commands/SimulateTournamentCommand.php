<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Constants\GeneralStatusResponse;

class SimulateTournamentCommand extends Command
{
    protected $signature = 'app:simulate-tournament-command';
    protected $description = 'Simular un torneo por eliminación directa';

    private $jwt;

    // Definir constantes reutilizables
    const OPTIONS = [
        'createPlayer'     => 'Crear jugador',
        'createTournament' => 'Crear torneo',
        'startTournament'  => 'Empezar torneo',
        'getPlayersDisplay'=> 'Obtener jugadores',
        'getTournaments'   => 'Obtener torneos',
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

    private function main()
    {
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
        do {
            $jwt = $this->ask(self::MESSAGES['enterJwt']."\n ejemplo (Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...)");

            $validator = Validator::make(['jwt' => $jwt], [
                'jwt' => 'required|string|max:455',
            ]);

            if ($validator->fails()) {
                $this->handleValidationErrors($validator);
            } else {
                $this->jwt = $jwt;
                break;
            }
        } while (true);
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

        do {
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
            } else {
                $parameters = [
                    'gender' => $gender,
                    'name' => $name,
                    'ability' => $ability,
                    'atributesPlayer' => $atributesPlayer,
                ];

                $url = self::BASE_URL . 'players/register';

                if ($this->postSend($url, $parameters)) {
                    $this->main();
                    return;
                } else {
                    $this->error(self::MESSAGES['errorPlayerCreation']);
                }
            }
        } while (true);
    }

    private function getGender(): string
    {
        $this->info('Ingresa el género (slug) ---- GÉNEROS DISPONIBLES:');
        $response = $this->getSend(self::BASE_URL . 'genders/all');
        $response = json_decode($response, true);
        if ($response['status'] === GeneralStatusResponse::SUCCESS) {
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
                break;
            }

            $attribute = json_decode($jsonAttributes, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Formato JSON inválido. Intente nuevamente.');
                continue;
            }

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

        do {
            $gender = $this->getGender();
            $name = $this->ask('Ingrese el nombre');
            $numberPlayers = $this->ask('Ingrese la cantidad de jugadores');

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
            } else {
                $parameters = [
                    'gender' => $gender,
                    'name' => $name,
                    'number_players' => $numberPlayers
                ];

                $url = self::BASE_URL . 'tournaments/register';

                $response = $this->postSend($url, $parameters);
                $response = json_decode($response, true);
                if ($response['status'] === GeneralStatusResponse::SUCCESS) {
                    $this->info('Torneo creado exitosamente');
                    $this->addPlayerToTournament($response['data']['id'], $gender);
                    return;
                } else {
                    $this->error('Error al crear el torneo');
                }
            }
        } while (true);
    }

    public function addPlayerToTournament(int $tournamentId, string $gender): void
    {
        do {
            $this->info('######### Agregar jugador a torneo #########');
            $playerId = $this->getPlayers($gender, $tournamentId);
            $parameters = [
                'tournament_id' => $tournamentId,
                'player_id' => $playerId
            ];
            $url = self::BASE_URL . 'tournaments/register-player';
            $response = $this->postSend($url, $parameters);
            $response = json_decode($response, true);
            if ($response['status'] !== GeneralStatusResponse::SUCCESS) {
                $this->error('Error al agregar jugador al torneo');
            } else {
                dump("Jugador agregado al torneo");
                if ($this->isCompleteTournament($tournamentId)) {
                    $this->startTournament();
                    return;
                } else {
                    dump("Si el torneo no tiene el número de jugadores completos no podrá iniciar");
                }
            }
        } while ($this->confirm('¿Desea agregar otro jugador?'));

        $this->main();
    }

    public function isCompleteTournament($tournamentId): bool
    {
        $response = $this->getSend(self::BASE_URL . 'tournaments/is-complete', [$tournamentId]);
        $response = json_decode($response, true);
        if ($response['status'] === GeneralStatusResponse::SUCCESS) {
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

    public function getPlayers($gender, $tournament): string
    {
        $this->info('Ingresa el Jugador por (id) ---- Jugadores DISPONIBLES:');
        $response = $this->getSend(self::BASE_URL . 'players/gender/nottournament', [$gender, $tournament]);
        $response = json_decode($response, true);

        if ($response['status'] === GeneralStatusResponse::SUCCESS) {
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

        if ($response['status'] === GeneralStatusResponse::SUCCESS) {
            $data = $response['data'];
            foreach ($data as $item) {
                $this->info("ID: {$item['id']}, Nombre: {$item['name']}");
            }
            $this->main();
        }
    }

    // Muestra torneos por genero //
    public function getTournaments(): void
    {
        $gender = $this->getGender();
        $this->info('Lista de Torneos:');
        $response = $this->getSend(self::BASE_URL.'tournaments/gender/'.$gender);
        $response = json_decode($response, true);
        if ($response['status'] === GeneralStatusResponse::SUCCESS) {
            $data = $response['data'];
            foreach ($data as $item) {
                $this->info("ID: {$item['id']}, Nombre: {$item['name']}");
            }
            $this->main();
        }
    }
}