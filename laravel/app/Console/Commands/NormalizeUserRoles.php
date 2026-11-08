<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\RoleCatalog;
use Illuminate\Console\Command;

class NormalizeUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:normalize-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normaliza usuarios heredados para que cada usuario tenga un solo rol valido.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        RoleCatalog::sync();

        $updated = 0;
        $review = 0;

        User::withTrashed()
            ->with(['roles', 'client'])
            ->get()
            ->each(function (User $user) use (&$updated, &$review) {
                if ($user->trashed()) {
                    return;
                }

                $targetRole = $this->resolveRole($user);
                if (!$targetRole) {
                    $review++;
                    $this->warn("Revisar usuario {$user->id} {$user->name_complete}: no se pudo determinar un rol.");
                    return;
                }

                $currentRoles = $user->roles->pluck('name')->values()->all();
                $needsSync = count($currentRoles) !== 1 || !in_array($targetRole, $currentRoles, true);

                $legacyType = $targetRole === RoleCatalog::ROLE_COBRADOR ? '1' : '0';
                $typeChanged = $user->type !== $legacyType;

                if (!$needsSync && !$typeChanged) {
                    return;
                }

                $user->syncRoles([$targetRole]);
                $user->syncLegacyCollectorFlag();

                $updated++;
                $this->info("Usuario {$user->id} {$user->name_complete} => {$targetRole}");
            });

        $this->line("Usuarios actualizados: {$updated}");
        $this->line("Usuarios para revisar manualmente: {$review}");

        return 0;
    }

    protected function resolveRole(User $user)
    {
        if ($user->hasRole(RoleCatalog::ROLE_ADMIN)) {
            return RoleCatalog::ROLE_ADMIN;
        }

        if ($user->hasRole(RoleCatalog::ROLE_SECRETARIA)) {
            return RoleCatalog::ROLE_SECRETARIA;
        }

        if ($user->hasRole(RoleCatalog::ROLE_TECNICO)) {
            return RoleCatalog::ROLE_TECNICO;
        }

        if ($user->hasRole(RoleCatalog::ROLE_COBRADOR)) {
            return RoleCatalog::ROLE_COBRADOR;
        }

        if ($user->hasRole(RoleCatalog::ROLE_CLIENTE)) {
            return RoleCatalog::ROLE_CLIENTE;
        }

        if ($user->client) {
            return RoleCatalog::ROLE_CLIENTE;
        }

        if ((string) $user->type === '1') {
            return RoleCatalog::ROLE_COBRADOR;
        }

        return null;
    }
}
