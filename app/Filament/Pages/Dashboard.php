<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BankAccountsOverview;
use App\Interfaces\PowensRepositoryInterface;
use App\Repository\PowensRepository;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $navigationIcon = 'fas-magnifying-glass';

    public static $icon = 'heroicon-s-home';

    public function mount(PowensRepository $powensRepository)
    {
        $powensRepository->authenticate(auth()->user(), request());
    }

    protected function getColumns(): int|string|array
    {
        return 1;
    }

    protected function getWidgets(): array
    {
        return [
            BankAccountsOverview::class,
        ];
    }


}
