<?php

namespace App\Filament\Tables\Actions;

use App\Filament\Pages\DetailsAffiliate;
use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\Action;

class DetailsAction extends Action 
{
    use CanCustomizeProcess;

    protected ?Closure $mutateRecordDataUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'Details';
    }
  
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Details'));
        $this->icon('heroicon-o-chart-bar');
        $this->url(function($record){
            return route(DetailsAffiliate::getRouteName(), ["provider" => $record->id]);
        });
        $this->openUrlInNewTab();
    }
}
