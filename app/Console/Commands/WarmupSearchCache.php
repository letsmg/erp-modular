<?php

namespace App\Console\Commands;

use App\Services\SmartSearchService;
use Illuminate\Console\Command;

class WarmupSearchCache extends Command
{
    protected $signature = 'search:warmup {--popular= : Termos populares separados por vírgula}';
    protected $description = 'Pré-aquece o cache do Redis com buscas populares';

    protected SmartSearchService $smartSearchService;

    public function __construct(SmartSearchService $smartSearchService)
    {
        parent::__construct();
        $this->smartSearchService = $smartSearchService;
    }

    public function handle()
    {
        $this->info('Iniciando warmup do cache de busca...');

        // Termos populares padrão (letras e números individuais)
        $defaultTerms = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];

        // Termos personalizados do usuário
        $popularTerms = [];
        if ($this->option('popular')) {
            $popularTerms = explode(',', $this->option('popular'));
            $popularTerms = array_map('trim', $popularTerms);
            $popularTerms = array_filter($popularTerms);
        }

        // Combina termos padrão com personalizados
        $allTerms = array_merge($defaultTerms, $popularTerms);

        $this->info('Processando ' . count($allTerms) . ' termos...');

        $progressBar = $this->output->createProgressBar(count($allTerms));
        $progressBar->start();

        foreach ($allTerms as $term) {
            try {
                // Faz a busca para cachear automaticamente
                $this->smartSearchService->search($term);
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->line("\nErro ao processar termo '{$term}': " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->line("\n");

        // Mostra estatísticas do cache
        $stats = $this->smartSearchService->getCacheStats();
        $this->info('Estatísticas do Cache:');
        $this->line('  - Buscas cacheadas: ' . $stats['total_cached_searches']);
        $this->line('  - Uso de memória: ' . number_format($stats['memory_usage'] / 1024, 2) . ' KB');

        $this->info('Warmup do cache concluído com sucesso!');
        
        return 0;
    }
}
