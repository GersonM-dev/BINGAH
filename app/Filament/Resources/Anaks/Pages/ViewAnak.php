<?php

namespace App\Filament\Resources\Anaks\Pages;

use App\Filament\Resources\Anaks\AnakResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAnak extends ViewRecord
{
    protected static string $resource = AnakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
