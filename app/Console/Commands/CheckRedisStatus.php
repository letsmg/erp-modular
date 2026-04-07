<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CheckRedisStatus extends Command
{
    protected $signature = 'redis:status';
    protected $description = 'Verifica o status do Redis e mostra instruções de instalação';

    public function handle()
    {
        $this->info('Verificando status do Redis...');

        // Primeiro verifica se a extensão está instalada
        if (!class_exists('Redis')) {
            $this->error('Extensão Redis do PHP não está instalada!');
            
            $this->line('');
            $this->info('Para instalar a extensão Redis no PHP com XAMPP:');
            $this->line('1. Verifique sua versão do PHP:');
            $this->line('   php -v');
            $this->line('');
            $this->line('2. Baixe a extensão Redis compatível:');
            $this->line('   https://windows.php.net/downloads/pecl/releases/redis/');
            $this->line('');
            $this->line('3. Coloque o arquivo php_redis.dll em:');
            $this->line('   C:\xampp\php\ext\');
            $this->line('');
            $this->line('4. Edite o php.ini (C:\xampp\php\php.ini):');
            $this->line('   Adicione: extension=redis');
            $this->line('');
            $this->line('5. Reinicie o Apache');
            $this->line('');
            $this->line('6. Verifique a instalação:');
            $this->line('   php -m | findstr redis');
            $this->line('');
            $this->info('Alternativa: Use WSL2 (recomendado para desenvolvimento)');
            $this->line('1. Instale WSL2: wsl --install');
            $this->line('2. Instale Ubuntu: wsl --install -d Ubuntu');
            $this->line('3. No WSL2: sudo apt install redis-server php-redis');
            $this->line('4. Configure Laravel para usar Redis do WSL2');

            return 1;
        }

        try {
            // Testa conexão com Redis
            Redis::ping();
            
            $this->info('Redis está conectado e funcionando!');

            // Mostra informações do Redis
            $info = Redis::info();
            
            $this->line('');
            $this->info('Informações do Redis:');
            $this->line('  - Versão: ' . ($info['redis_version'] ?? 'N/A'));
            $this->line('  - Modo: ' . ($info['redis_mode'] ?? 'standalone'));
            $this->line('  - OS: ' . ($info['os'] ?? 'N/A'));
            $this->line('  - Uptime: ' . ($info['uptime_in_seconds'] ?? 'N/A') . ' segundos');
            $this->line('  - Memória usada: ' . ($info['used_memory_human'] ?? 'N/A'));
            $this->line('  - Clientes conectados: ' . ($info['connected_clients'] ?? 'N/A'));

            // Testa cache
            $this->line('');
            $this->info('Testando cache...');
            Redis::set('test_key', 'test_value', 'EX', 10);
            $value = Redis::get('test_key');
            Redis::del('test_key');
            
            if ($value === 'test_value') {
                $this->info('Cache funcionando perfeitamente!');
            } else {
                $this->error('Cache não está funcionando corretamente');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Redis não está conectado: ' . $e->getMessage());
            
            $this->line('');
            $this->info('Para instalar o Redis Server no Windows:');
            $this->line('1. Baixe o Redis para Windows:');
            $this->line('   https://github.com/microsoftarchive/redis/releases');
            $this->line('');
            $this->line('2. Instale o Redis (msiexec)');
            $this->line('   - Execute o instalador');
            $this->line('   - Aceite as opções padrão');
            $this->line('');
            $this->line('3. Inicie o serviço Redis:');
            $this->line('   - Services.msc');
            $this->line('   - Encontre "Redis" e inicie');
            $this->line('');
            $this->line('4. Verifique a instalação:');
            $this->line('   php artisan redis:status');

            return 1;
        }
    }
}
