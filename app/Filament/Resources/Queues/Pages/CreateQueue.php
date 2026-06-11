<?php

namespace App\Filament\Resources\Queues\Pages;

use App\Filament\Resources\Queues\QueueResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQueue extends CreateRecord
{
    protected static string $resource = QueueResource::class;

    public function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
