<?php

namespace App\Filament\Widgets;

use App\Models\AffiliateHistory;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Helpers\Core as Helper;

class StatsUserDetailOverview extends BaseWidget
{

    public User $record;

    public function mount($record)
    {
       $this->record = $record;
    }

    /**
     * @return array|Stat[]
     */ 
    protected function getStats(): array
    {
        $totalGanhos = Order::where('user_id', $this->record->id)->where('type', 'win')->sum('amount');
        $totalPerdas = Order::where('user_id', $this->record->id)->where('type', 'bet')->sum('amount');
        $totalAfiliados = AffiliateHistory::where('inviter', $this->record->id)->sum('commission_paid');

        // Novos widgets
        $trouxeDeDepositantes = AffiliateHistory::where('inviter', $this->record->id)
            ->where('status', 1)
            ->count();

        $trouxeDeLucro = AffiliateHistory::where('inviter', $this->record->id)
            ->where('status', 1)
            ->sum('deposited');

        $trouxeDeClientes = AffiliateHistory::where('inviter', $this->record->id)
            ->whereIn('status', [0, 1])
            ->count();

        return [
            Stat::make('TOTAL WINS', Helper::amountFormatDecimal(Helper::formatNumber($totalGanhos))) 
                ->description('Total winnings from bets')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([10, 20, 15, 30, 25, 40, 35])
                ->chartColor('rgba(59, 130, 246, 0.5)'),

            Stat::make('TOTAL LOSSES', Helper::amountFormatDecimal(Helper::formatNumber($totalPerdas)))
                ->description('Total losses from bets')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([10, 20, 15, 30, 25, 40, 35])
                ->chartColor('rgba(59, 130, 246, 0.5)'),
 
            Stat::make('AFFILIATE EARNINGS', Helper::amountFormatDecimal(Helper::formatNumber($totalAfiliados)))
                ->description('Total earnings as an affiliate') 
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([10, 20, 15, 30, 25, 40, 35])
                ->chartColor('rgba(59, 130, 246, 0.5)'),

            // Novo widget: Trouxe de Depositantes
            Stat::make('DEPOSITORS REFERRED', $trouxeDeDepositantes)
                ->description('Number of depositors referred')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([10, 20, 15, 30, 25, 40, 35])
                ->chartColor('rgba(59, 130, 246, 0.5)'),
 
            // Novo widget: Trouxe de Lucro
            Stat::make('PROFIT REFERRED', Helper::amountFormatDecimal(Helper::formatNumber($trouxeDeLucro)))
                ->description('Amount of profit referred')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart([10, 20, 15, 30, 25, 40, 35])
                ->chartColor('rgba(59, 130, 246, 0.5)'),
 
            // Novo widget: Trouxe de Clientes
            Stat::make('CLIENTS REFERRED', $trouxeDeClientes)
                ->description('Total clients referred')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([10, 20, 15, 30, 25, 40, 35])
                ->chartColor('rgba(59, 130, 246, 0.5)'),
        ];
    }
}
