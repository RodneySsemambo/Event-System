<?php

namespace App\Filament\Resources\Withdrawals;

use App\Filament\Resources\Withdrawals\Pages\CreateWithdrawal;
use App\Filament\Resources\Withdrawals\Pages\EditWithdrawal;
use App\Filament\Resources\Withdrawals\Pages\ListWithdrawals;
use App\Filament\Resources\Withdrawals\Pages\ViewWithdrawal;
use App\Filament\Resources\Withdrawals\Schemas\WithdrawalForm;
use App\Filament\Resources\Withdrawals\Schemas\WithdrawalInfolist;
use App\Filament\Resources\Withdrawals\Tables\WithdrawalsTable;
use App\Models\Withdrawal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WithdrawalResource extends Resource
{
    protected static ?string $model = Withdrawal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string |UnitEnum|null $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 6;



    protected static ?string $recordTitleAttribute = 'withdrawal';

    public static function form(Schema $schema): Schema
    {
        return WithdrawalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WithdrawalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WithdrawalsTable::configure($table);
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
            'index' => ListWithdrawals::route('/'),
            'create' => CreateWithdrawal::route('/create'),
            'view' => ViewWithdrawal::route('/{record}'),
            'edit' => EditWithdrawal::route('/{record}/edit'),
        ];
    }
}
