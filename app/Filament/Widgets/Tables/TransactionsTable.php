<?php

namespace App\Filament\Widgets\Tables;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TransactionsTable extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Transactions';

    protected $polling = false;

    public function mount()
    {
        $this->startPolling();
    }

    public function dehydrate()
    {
        $this->stopPolling();
    }

    protected function getTableQuery(): Builder
    {
        $query = auth()->user()->bankAccounts()->count() > 0
            ? Transaction::query()
                ->whereHas('bankAccount', fn(Builder $query) => $query->where('user_id', auth()->id()))
                ->with('bankAccount')->orderBy('application_date', 'desc')
            : Transaction::query();

        if ($this->polling) {
            return $query->limit(1);
        }

        return $query;
    }

    protected function startPolling()
    {
        $this->polling = true;
    }

    protected function stopPolling()
    {
        $this->polling = false;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\BadgeColumn::make('value')
                ->label('Montant')
                ->color(fn($record) => $record->value > 0 ? 'success' : 'danger')
                ->formatStateUsing(fn($record) => $record->value > 0 ? '+' . format_currency($record->value) : format_currency($record->value))
                ->suffix(' €')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('wording')
                ->label('Libellé')
                ->limit(60)
                ->searchable(),

            Tables\Columns\BadgeColumn::make('type')
                ->label('Type')
                ->enum([
                    Transaction::CARD_TYPE => 'Carte',
                    Transaction::TRANSFER_TYPE => 'Virement',
                    Transaction::ORDER_TYPE => 'Prélèvement',
                    Transaction::WITHDRAWAL_TYPE => 'Retrait',
                    Transaction::PAYBACK_TYPE => 'Remboursement',
                    Transaction::BANK_TYPE => 'Frais bancaires',
                    Transaction::UNKNOWN_TYPE => 'Inconnu',
                ])
                ->color('primary'),

            Tables\Columns\TextColumn::make('bankAccount.bank_name')->label('Banque'),

            Tables\Columns\TextColumn::make('application_date')
                ->label('Date')
                ->sortable()
                ->date('j F Y'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('type')
                ->label('Type')
                ->options([
                    Transaction::CARD_TYPE => 'Carte',
                    Transaction::TRANSFER_TYPE => 'Virement',
                    Transaction::ORDER_TYPE => 'Prélèvement',
                    Transaction::WITHDRAWAL_TYPE => 'Retrait',
                    Transaction::PAYBACK_TYPE => 'Remboursement',
                    Transaction::BANK_TYPE => 'Frais bancaires',
                    Transaction::UNKNOWN_TYPE => 'Inconnu',
                ]),

            Tables\Filters\SelectFilter::make('bank_account_id')
                ->label('Banque')
                ->options(fn() => array_unique(auth()->user()->bankAccounts()->pluck('bank_name', 'id')->toArray())),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [

        ];
    }
}
