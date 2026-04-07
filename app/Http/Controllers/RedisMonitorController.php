<?php

namespace App\Http\Controllers;

use App\Services\RedisMonitorService;
use Illuminate\Http\JsonResponse;

class RedisMonitorController extends Controller
{
    public function __construct(
        private RedisMonitorService $monitorService
    ) {}

    /**
     * Obtém métricas atuais do Redis
     */
    public function metrics(): JsonResponse
    {
        $metrics = $this->monitorService->getMemoryUsage();
        
        return response()->json([
            'status' => $metrics['alert'] ? 'warning' : 'healthy',
            'memory' => [
                'used_mb' => $metrics['used_mb'],
                'max_mb' => $metrics['max_mb'],
                'percent_used' => $metrics['percent_used']
            ],
            'performance' => [
                'searches_per_minute' => $metrics['searches_per_minute'] ?? 0,
                'cache_efficiency' => $this->calculateCacheEfficiency()
            ],
            'alerts' => [
                'memory_warning' => $metrics['alert'],
                'recommendation' => $this->getRecommendation($metrics)
            ]
        ]);
    }

    /**
     * Obtém histórico de métricas
     */
    public function history(): JsonResponse
    {
        $history = $this->monitorService->getMetricsHistory();
        
        return response()->json([
            'history' => $history,
            'total_records' => count($history),
            'time_range' => '24 horas'
        ]);
    }

    /**
     * Calcula eficiência do cache
     */
    private function calculateCacheEfficiency(): string
    {
        try {
            $cacheHits = Redis::get('cache_hits_counter') ?? 0;
            $totalSearches = Redis::get('total_searches_counter') ?? 1;
            
            if ($totalSearches > 0) {
                $efficiency = ($cacheHits / $totalSearches) * 100;
                return round($efficiency, 1) . '%';
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    /**
     * Gera recomendações baseadas nas métricas
     */
    private function getRecommendation(array $metrics): string
    {
        if ($metrics['percent_used'] > 90) {
            return 'Aumentar memória Redis para 1GB';
        }
        
        if ($metrics['percent_used'] > 80) {
            return 'Monitorar uso de memória';
        }
        
        if ($metrics['searches_per_minute'] > 100) {
            return 'Considerar Redis Cluster';
        }
        
        return 'Sistema operando normalmente';
    }
}
