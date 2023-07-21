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

    protected function getTableQuery(): Builder
    {
        return auth()->user()->bankAccounts()->count() > 0
            ? Transaction::query()
                ->whereHas('bankAccount', fn(Builder $query) => $query->where('user_id', auth()->id()))
                ->with('bankAccount')
            : Transaction::query();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('bankAccount.bank_name'),

            Tables\Columns\TextColumn::make('value')
                ->color(fn($record) => $record->value > 0 ? 'success' : 'danger')
                ->money('EUR', true)
                ->sortable(),

            /*Tables\Columns\TextColumn::make('original_wording'),

            Tables\Columns\TextColumn::make('simplified_wording'),

            Tables\Columns\TextColumn::make('stemmed_wording'),*/

            Tables\Columns\TextColumn::make('wording')
                ->limit(50)
                ->searchable(),

            Tables\Columns\BadgeColumn::make('type')
                ->enum([
                    Transaction::CARD_TYPE => 'Card',
                    Transaction::TRANSFER_TYPE => 'Transfer',
                    Transaction::ORDER_TYPE => 'Order',
                    Transaction::WITHDRAWAL_TYPE => 'Withdrawal',
                    Transaction::BANK => 'Bank',
                    Transaction::UNKNOWN_TYPE => 'Unknown',
                ])
                ->colors(
                    [
                        'primary' => Transaction::CARD_TYPE,
                        'success' => Transaction::TRANSFER_TYPE,
                        'warning' => Transaction::ORDER_TYPE,
                        'danger' => Transaction::WITHDRAWAL_TYPE,
                        'neutral' => Transaction::BANK,
                        'dark' => Transaction::UNKNOWN_TYPE,
                    ]
                ),

            Tables\Columns\TextColumn::make('application_date')
                ->sortable()
                ->date('j F Y'),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [

        ];
    }
}
