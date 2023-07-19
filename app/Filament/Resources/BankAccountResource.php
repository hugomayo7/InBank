<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Filament\Resources\BankAccountResource\RelationManagers;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Webbingbrasil\FilamentCopyActions\Tables\CopyableTextColumn;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'fas-building-columns';

    protected static ?string $pluralLabel = 'Comptes bancaires';
    protected static ?string $label = 'Compte bancaire';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('original_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('iban')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('last_updated_at')
                    ->required(),
                Forms\Components\TextInput::make('connection_id')
                    ->required(),
                Forms\Components\TextInput::make('account_id')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')->label('Banque'),
                Tables\Columns\TextColumn::make('original_name')->label('Nom de compte'),
                CopyableTextColumn::make('iban')
                    ->successMessage('IBAN copié !')
                    ->copyable(),
                Tables\Columns\TextColumn::make('last_updated_at')
                    ->label('Dernière mise à jour')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('connection_id')->label('ID de connexion'),
                Tables\Columns\TextColumn::make('account_id')->label('ID de compte'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('revoke_account')
                ->action(function () {

                }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}
