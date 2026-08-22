<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $selectedRole = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $user */
        $user = $this->record;
        $data['role'] = $user->roles->first()?->name;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedRole = $data['role'] ?? null;
        unset($data['role']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (is_string($this->selectedRole) && $this->selectedRole !== '') {
            /** @var User $user */
            $user = $this->record;
            $user->syncRoles([$this->selectedRole]);
        }
    }
}
