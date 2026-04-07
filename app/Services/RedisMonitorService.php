<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisMonitorService
{
    private const MONITORING_KEY = 'redis_monitoring';
    private const ALERT_THRESHOLD = 80; // 80% de uso

    /**
     * Monitora uso de memória do Redis
     */
    public function getMemoryUsage(): array
    {
        try {
            $info = Redis::info('memory');
            
            $used = $info['used_memory'] ?? 0;
            $max = $info['maxmemory'] ?? 536870912; // 512MB padrão
            $percent = $max > 0 ? ($used / $max) * 100 : 0;

            // Registra métricas
            $this->recordMetrics($used, $max, $percent);

            return [
                'used_mb' => round($used / 1024 / 1024, 2),
                'max_mb' => round($max / 1024 / 1024, 2),
                'percent_used' => round($percent, 2),
                'alert' => $percent > self::ALERT_THRESHOLD,
                'timestamp' => now()->toISOString()
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao monitorar Redis', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'error' => $e->getMessage(),
                'used_mb' => 0,
                'max_mb' => 512,
                'percent_used' => 0,
                'alert' => false
            ];
        }
    }

    /**
     * Registra métricas de uso
     */
    private function recordMetrics(float $used, float $max, float $percent): void
    {
        $metrics = [
            'timestamp' => time(),
            'used_memory' => $used,
            'max_memory' => $max,
            'percent_used' => $percent,
            'searches_per_minute' => $this->getSearchesPerMinute()
        ];

        // Guarda últimas 24 horas
        Redis::zadd(self::MONITORING_KEY, time(), json_encode($metrics));
        Redis::zremrangebyrank(self::MONITORING_KEY, 0, -1440); // Remove mais antigos
    }

    /**
     * Conta buscas por minuto
     */
    private function getSearchesPerMinute(): float
    {
        $keys = Redis::keys('search_count:*');
        $totalSearches = 0;
        
        foreach ($keys as $key) {
            $count = Redis::zscore('search_counts', $key);
            if ($count) {
                $totalSearches += $count;
            }
        }

        return $totalSearches / 60; // Média por minuto
    }

    /**
     * Limpa métricas antigas
     */
    public function clearOldMetrics(): void
    {
        Redis::zremrangebyrank(self::MONITORING_KEY, 0, -1440);
    }

    /**
     * Obtém estatísticas das últimas 24h
     */
    public function getMetricsHistory(): array
    {
        $metrics = Redis::zrevrange(self::MONITORING_KEY, 0, -1, 'WITHSCORES');
        
        $history = [];
        foreach ($metrics as $metric) {
            $data = json_decode($metric, true);
            $data['score'] = $metric;
            $history[] = $data;
        }
        
        return $history;
    }
}
