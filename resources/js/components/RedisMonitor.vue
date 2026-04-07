// Componente de dashboard de monitoramento Redis
<template>
  <div class="p-6 bg-white rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">📊 Monitoramento Redis</h2>
    
    <!-- Status Atual -->
    <div class="mb-6">
      <h3 class="text-lg font-semibold mb-3 text-gray-700">🎯 Status Atual</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">
          <h4 class="font-medium text-gray-600 mb-2">Memória</h4>
          <div class="text-2xl font-bold" :class="memoryStatus.color">
            {{ memoryStatus.used_mb }}MB / {{ memoryStatus.max_mb }}MB
          </div>
          <div class="text-sm mt-1" :class="memoryStatus.textColor">
            {{ memoryStatus.percent_used }}% utilizado
          </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
          <h4 class="font-medium text-gray-600 mb-2">Performance</h4>
          <div class="text-lg font-semibold text-blue-600">
            {{ memoryStatus.searches_per_minute }} buscas/min
          </div>
          <div class="text-sm text-gray-500 mt-1">
            Eficiência: {{ memoryStatus.cache_efficiency }}
          </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
          <h4 class="font-medium text-gray-600 mb-2">Alertas</h4>
          <div class="text-sm" :class="alertStatus.color">
            {{ alertStatus.message }}
          </div>
        </div>
      </div>
    </div>
    
    <!-- Botão de Atualização -->
    <div class="mb-6 text-center">
      <button 
        @click="refreshMetrics"
        :disabled="loading"
        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 disabled:bg-gray-400"
      >
        <span v-if="!loading">🔄 Atualizar</span>
        <span v-else>⏳ Carregando...</span>
      </button>
    </div>
    
    <!-- Histórico -->
    <div class="bg-gray-50 p-6 rounded-lg">
      <h3 class="text-lg font-semibold mb-4 text-gray-700">📈 Histórico (24h)</h3>
      <div class="space-y-2">
        <div 
          v-for="(metric, index) in metricsHistory" 
          :key="index"
          class="flex justify-between items-center p-3 bg-white rounded border"
        >
          <div>
            <div class="text-sm text-gray-500">
              {{ formatTime(metric.timestamp) }}
            </div>
            <div class="font-medium">
              Memória: {{ metric.memory.percent_used }}%
            </div>
          </div>
          <div class="text-sm text-gray-500">
            Buscas/min: {{ metric.performance.searches_per_minute }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const memoryStatus = ref({
  used_mb: 0,
  max_mb: 512,
  percent_used: 0,
  searches_per_minute: 0,
  cache_efficiency: 'N/A',
  color: 'text-gray-600',
  textColor: 'text-gray-500'
})

const alertStatus = ref({
  message: 'Carregando...',
  color: 'text-gray-500'
})

const metricsHistory = ref([])
const loading = ref(false)

// Formata timestamp
const formatTime = (timestamp) => {
  return new Date(timestamp * 1000).toLocaleString('pt-BR')
}

// Carrega métricas atuais
const loadMetrics = async () => {
  try {
    loading.value = true
    
    const response = await fetch('/api/redis/metrics')
    const data = await response.json()
    
    memoryStatus.value = data.memory
    memoryStatus.value.searches_per_minute = data.performance.searches_per_minute
    memoryStatus.value.cache_efficiency = data.performance.cache_efficiency
    
    // Define cores baseado no uso
    if (data.memory.percent_used > 80) {
      memoryStatus.value.color = 'text-red-600'
      memoryStatus.value.textColor = 'text-red-500'
    } else if (data.memory.percent_used > 60) {
      memoryStatus.value.color = 'text-yellow-600'
      memoryStatus.value.textColor = 'text-yellow-500'
    } else {
      memoryStatus.value.color = 'text-green-600'
      memoryStatus.value.textColor = 'text-green-500'
    }
    
    // Configura alertas
    alertStatus.value = data.alerts
    
  } catch (error) {
    console.error('Erro ao carregar métricas:', error)
    alertStatus.value = {
      message: 'Erro ao carregar dados',
      color: 'text-red-500'
    }
  } finally {
    loading.value = false
  }
}

// Carrega histórico
const loadHistory = async () => {
  try {
    const response = await fetch('/api/redis/history')
    const data = await response.json()
    metricsHistory.value = data.history || []
  } catch (error) {
    console.error('Erro ao carregar histórico:', error)
  }
}

// Atualiza métricas
const refreshMetrics = () => {
  loadMetrics()
  loadHistory()
}

// Carrega dados iniciais
onMounted(() => {
  loadMetrics()
  loadHistory()
  
  // Atualiza automaticamente a cada 30 segundos
  setInterval(loadMetrics, 30000)
})
</script>

<style scoped>
.bg-gray-50 {
  background-color: #f9fafb;
}

.text-2xl {
  font-size: 1.5rem;
  line-height: 2rem;
}

.font-bold {
  font-weight: 700;
}

.text-gray-800 {
  color: #1f2937;
}

.mb-6 {
  margin-bottom: 1.5rem;
}

.text-lg {
  font-size: 1.125rem;
}

.font-semibold {
  font-weight: 600;
}

.mb-3 {
  margin-bottom: 0.75rem;
}

.text-gray-700 {
  color: #374151;
}

.grid {
  display: grid;
}

.grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 768px) {
  .grid-cols-1 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.gap-4 {
  gap: 1rem;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.p-4 {
  padding: 1rem;
}

.text-2xl {
  font-size: 1.5rem;
  line-height: 2rem;
}

.text-blue-600 {
  color: #2563eb;
}

.text-sm {
  font-size: 0.875rem;
}

.mt-1 {
  margin-top: 0.25rem;
}

.text-gray-500 {
  color: #6b7280;
}

.text-center {
  text-align: center;
}

.bg-blue-500 {
  background-color: #3b82f6;
}

.text-white {
  color: white;
}

.px-6 {
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.hover\:bg-blue-600:hover {
  background-color: #2563eb;
}

.disabled\:bg-gray-400:disabled {
  background-color: #9ca3af;
}

.space-y-2 > * + * {
  margin-top: 0.5rem;
}

.justify-between {
  justify-content: space-between;
}

.items-center {
  align-items: center;
}

.border {
  border-width: 1px;
  border-style: solid;
  border-color: #e5e7eb;
}

.text-red-600 {
  color: #dc2626;
}

.text-yellow-600 {
  color: #d97706;
}

.text-green-600 {
  color: #16a34a;
}

.text-red-500 {
  color: #ef4444;
}

.text-yellow-500 {
  color: #f59e0b;
}

.text-green-500 {
  color: #10b981;
}
</style>
