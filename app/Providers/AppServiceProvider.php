<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;

use App\Models\Repository\User\UserRepository;
use App\Models\Repository\User\IUserRepository;

use App\Models\Repository\Player\IPlayerRepository;
use App\Models\Repository\Player\PlayerRepository;
use App\Models\Repository\Attribute\IAttributeRepository;
use App\Models\Repository\Attribute\AttributeRepository;
use App\Models\Repository\Play\IPlayRepository;
use App\Models\Repository\Play\PlayRepository;
use App\Models\Repository\Gender\IGenderRepository;
use App\Models\Repository\Gender\GenderRepository;
use App\Models\Repository\Tournament\ITournamentRepository;
use App\Models\Repository\Tournament\TournamentRepository;
use App\Models\Repository\PlayerAttribute\PlayerAttributeRepository;
use App\Models\Repository\PlayerAttribute\IPlayerAttributeRepository;
use App\Models\Repository\Tournament\ITournamentPlayerRepository;
use App\Models\Repository\Tournament\TournamentPlayerRepository;
use App\Models\Repository\Tournament\TournamentStateRepository;
use App\Models\Repository\Tournament\ITournamentStateRepository;
use App\Models\Repository\Tournament\ITournamentPlayerStateRepository;
use App\Models\Repository\Tournament\TournamentPlayerStateRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IUserRepository::class, UserRepository::class);
        $this->app->bind(IPlayerRepository::class, PlayerRepository::class);
        $this->app->bind(IAttributeRepository::class, AttributeRepository::class);
        $this->app->bind(IPlayRepository::class, PlayRepository::class);
        $this->app->bind(IGenderRepository::class, GenderRepository::class);
        $this->app->bind(ITournamentRepository::class, TournamentRepository::class);
        $this->app->bind(IPlayerAttributeRepository::class, PlayerAttributeRepository::class);
        $this->app->bind(ITournamentPlayerRepository::class, TournamentPlayerRepository::class);
        $this->app->bind(ITournamentStateRepository::class, TournamentStateRepository::class);
        $this->app->bind(ITournamentPlayerStateRepository::class, TournamentPlayerStateRepository::class);

     

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
