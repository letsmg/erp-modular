<?php

namespace App\Jobs;

use App\Services\SmartSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClearSearchCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(SmartSearchService $smartSearchService): void
    {
        try {
            $smartSearchService->clearSearchCache();
            
            \Log::info('Cache de busca limpo com sucesso');
        } catch (\Exception $e) {
            \Log::error('Erro ao limpar cache de busca: ' . $e->getMessage());
            
            $this->fail($e);
        }
    }
}
