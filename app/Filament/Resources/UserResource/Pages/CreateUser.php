<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $selectedRole = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedRole = $data['role'] ?? null;
        unset($data['role']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (is_string($this->selectedRole) && $this->selectedRole !== '') {
            /** @var \App\Models\User $user */
            $user = $this->record;
            $user->syncRoles([$this->selectedRole]);
        }
    }
}
