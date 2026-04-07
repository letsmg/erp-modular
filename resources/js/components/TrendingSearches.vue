// Componente de trending searches (palavras mais pesquisadas)
<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800">
        <span class="text-orange-500">fire</span> Palavras Mais Pesquisadas
      </h3>
      <button 
        @click="refreshTrending"
        :disabled="loading"
        class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full transition-colors"
      >
        <span v-if="!loading">Atualizar</span>
        <span v-else>...</span>
      </button>
    </div>
    
    <!-- Lista de trending searches -->
    <div v-if="trending.length > 0" class="space-y-2">
      <div 
        v-for="(item, index) in trending" 
        :key="item.term"
        @click="selectTrending(item)"
        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition-colors group"
      >
        <div class="flex items-center space-x-3">
          <!-- Posição -->
          <div 
            class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
            :class="getPositionColor(index)"
          >
            {{ index + 1 }}
          </div>
          
          <!-- Termo e tendência -->
          <div>
            <div class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors">
              {{ item.term }}
            </div>
            <div class="text-sm text-gray-500">
              {{ formatCount(item.count) }} buscas
            </div>
          </div>
        </div>
        
        <!-- Ícone de tendência -->
        <div class="flex items-center space-x-2">
          <div 
            class="flex items-center space-x-1 text-xs"
            :class="getTrendColor(item.trend)"
          >
            <span v-if="item.trend === 'up'">trending_up</span>
            <span v-else-if="item.trend === 'down'">trending_down</span>
            <span v-else>trending_flat</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Estado vazio -->
    <div v-else-if="!loading" class="text-center py-8 text-gray-500">
      <div class="text-4xl mb-2">search</div>
      <p class="text-sm">Nenhuma busca popular ainda</p>
      <p class="text-xs text-gray-400 mt-1">
        As palavras mais pesquisadas aparecerão aqui
      </p>
    </div>
    
    <!-- Loading -->
    <div v-else class="text-center py-8 text-gray-500">
      <div class="text-4xl mb-2 animate-pulse">hourglass_empty</div>
      <p class="text-sm">Carregando...</p>
    </div>
    
    <!-- Link para ver mais -->
    <div v-if="trending.length > 0" class="mt-4 text-center">
      <button 
        @click="showAllTrending"
        class="text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors"
      >
        Ver todas as tendências
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const emit = defineEmits(['trending-selected'])

const trending = ref([])
const loading = ref(false)

// Carrega trending searches
const loadTrending = async () => {
  try {
    loading.value = true
    
    const response = await fetch('/api/search/trending?limit=10')
    const data = await response.json()
    
    trending.value = data.trending || []
    
  } catch (error) {
    console.error('Erro ao carregar trending searches:', error)
    trending.value = []
  } finally {
    loading.value = false
  }
}

// Seleciona trending search
const selectTrending = (item) => {
  emit('trending-selected', item)
}

// Atualiza trending
const refreshTrending = () => {
  loadTrending()
}

// Mostra todas as tendências
const showAllTrending = () => {
  // Implementar modal ou página com todas as tendências
  console.log('Mostrar todas as tendências')
}

// Formata contagem
const formatCount = (count) => {
  if (count >= 1000) {
    return (count / 1000).toFixed(1) + 'k'
  }
  return count.toString()
}

// Cor da posição
const getPositionColor = (index) => {
  if (index === 0) return 'bg-yellow-400 text-white' // 1º lugar - ouro
  if (index === 1) return 'bg-gray-400 text-white'    // 2º lugar - prata
  if (index === 2) return 'bg-orange-400 text-white'  // 3º lugar - bronze
  return 'bg-gray-200 text-gray-700'                  // Demais
}

// Cor da tendência
const getTrendColor = (trend) => {
  switch (trend) {
    case 'up': return 'text-green-600'
    case 'down': return 'text-red-600'
    default: return 'text-gray-500'
  }
}

// Carrega dados iniciais
onMounted(() => {
  loadTrending()
  
  // Atualiza a cada 5 minutos
  setInterval(loadTrending, 300000)
})
</script>

<style scoped>
.bg-white {
  background-color: white;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.shadow-md {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.p-6 {
  padding: 1.5rem;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.justify-between {
  justify-content: space-between;
}

.mb-4 {
  margin-bottom: 1rem;
}

.text-lg {
  font-size: 1.125rem;
}

.font-semibold {
  font-weight: 600;
}

.text-gray-800 {
  color: #1f2937;
}

.text-orange-500 {
  color: #f97316;
}

.space-x-3 > * + * {
  margin-left: 0.75rem;
}

.text-sm {
  font-size: 0.875rem;
}

.bg-gray-100 {
  background-color: #f3f4f6;
}

.hover\:bg-gray-200:hover {
  background-color: #e5e7eb;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.py-1 {
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}

.rounded-full {
  border-radius: 9999px;
}

.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.space-y-2 > * + * {
  margin-top: 0.5rem;
}

.p-3 {
  padding: 0.75rem;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.hover\:bg-gray-100:hover {
  background-color: #f3f4f6;
}

.cursor-pointer {
  cursor: pointer;
}

.group:hover .group-hover\:text-blue-600 {
  color: #2563eb;
}

.flex-shrink-0 {
  flex-shrink: 0;
}

.w-8 {
  width: 2rem;
}

.h-8 {
  height: 2rem;
}

.font-medium {
  font-weight: 500;
}

.text-gray-900 {
  color: #111827;
}

.text-xs {
  font-size: 0.75rem;
}

.text-gray-500 {
  color: #6b7280;
}

.bg-yellow-400 {
  background-color: #facc15;
}

.bg-gray-400 {
  background-color: #9ca3af;
}

.bg-orange-400 {
  background-color: #fb923c;
}

.bg-gray-200 {
  background-color: #e5e7eb;
}

.text-white {
  color: white;
}

.text-gray-700 {
  color: #374151;
}

.text-green-600 {
  color: #16a34a;
}

.text-red-600 {
  color: #dc2626;
}

.text-center {
  text-align: center;
}

.py-8 {
  padding-top: 2rem;
  padding-bottom: 2rem;
}

.text-4xl {
  font-size: 2.25rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.text-gray-400 {
  color: #9ca3af;
}

.mt-4 {
  margin-top: 1rem;
}

.text-blue-600 {
  color: #2563eb;
}

.hover\:text-blue-700:hover {
  color: #1d4ed8;
}

.font-bold {
  font-weight: 700;
}

.mt-1 {
  margin-top: 0.25rem;
}
</style>
