<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BankAccountsOverview;
use App\Filament\Widgets\Tables\TransactionsTable;
use App\Filament\Widgets\TotalBalanceOverview;
use App\Interfaces\PowensRepositoryInterface;
use Filament\Pages\Dashboard as BasePage;
use Illuminate\Support\Facades\App;

class Dashboard extends BasePage
{
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $navigationIcon = 'fas-home';

    public static $icon = 'heroicon-s-home';

    public $request_connection_id;
    public $request_code;

    public function mount()
    {
        if (request()->has('code')) {
            $this->request_code = request()->get('code');
        }

        if (request()->has('connection_id')) {
            $this->request_connection_id = request()->get('connection_id');
        }
    }

    public function loadData()
    {
        App::get(PowensRepositoryInterface::class)->authenticate(auth()->user(), request(), $this->request_connection_id, $this->request_code);
        App::get(PowensRepositoryInterface::class)->refreshData();

        $this->request_connection_id = null;
        $this->request_code = null;
    }

    protected function getColumns(): int|string|array
    {
        return 1;
    }

    protected function getWidgets(): array
    {
        return [
            TotalBalanceOverview::class,
            BankAccountsOverview::class,
            TransactionsTable::class
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return App::get(PowensRepositoryInterface::class)->getTotalBalance(auth()->user()->auth_token);
    }
}
