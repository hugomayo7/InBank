<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\Widget;

class BankAccountList extends Widget
{
    use CanPoll;

    protected ?array $cachedCards = null;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.bank-account-list';

    protected function getColumns(): int
    {
        return match ($count = count($this->getCachedCards())) {
            5, 6, 9, 11 => 3,
            7, 8, 10, 12 => 4,
            default => $count,
        };
    }

    protected function getCachedCards(): array
    {
        return $this->cachedCards ??= $this->getCards();
    }

    protected function getCards(): array
    {
        return [];
    }
}
