<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import { ref, computed, watch } from 'vue'
import { Plus, Pencil, Trash2, MapPin, Home, Check, User, AlertTriangle, X } from 'lucide-vue-next'

defineOptions({ layout: ClientLayout })

const page = usePage()
const user = page.props.auth.user
const client = computed(() => page.props.client)
const addresses = computed(() => page.props.addresses || [])

const showAddressForm = ref(false)
const editingAddress = ref<any>(null)

// Formulário de endereço
const addressForm = useForm({
  id: null as number | null,
  zip_code: '',
  street: '',
  number: '',
  neighborhood: '',
  city: '',
  state: '',
  complement: '',
  is_delivery_address: false,
})

// Formulário de perfil
const profileForm = useForm({
  name: client.value?.name || '',
  email: user?.email || '',
  phone1: client.value?.phone1 || '',
  contact1: client.value?.contact1 || '',
  phone2: client.value?.phone2 || '',
  contact2: client.value?.contact2 || '',
})

// Formulário de senha
const passwordForm = useForm({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const showPasswordForm = ref(false)

// Atualizar formulário de perfil quando client mudar
watch(() => client.value, (newClient) => {
  if (newClient) {
    profileForm.name = newClient.name || ''
    profileForm.phone1 = newClient.phone1 || ''
    profileForm.contact1 = newClient.contact1 || ''
    profileForm.phone2 = newClient.phone2 || ''
    profileForm.contact2 = newClient.contact2 || ''
  }
}, { immediate: true })

// Verificar se tem pelo menos um endereço de entrega
const hasDeliveryAddress = computed(() => {
  return addresses.value.some((addr: any) => addr.is_delivery_address)
})

// Formatar CEP
const formatZipCode = (event: Event) => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  if (value.length > 8) value = value.slice(0, 8)
  if (value.length >= 5) {
    value = value.replace(/(\d{5})(\d{3})/, '$1-$2')
  }
  input.value = value
  addressForm.zip_code = value
}

// Formatar telefone
const formatPhone = (event: Event, field: 'phone1' | 'phone2') => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  if (value.length > 11) value = value.slice(0, 11)
  
  if (value.length >= 11) {
    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  } else if (value.length >= 10) {
    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
  }
  
  input.value = value
  profileForm[field] = value
}

// Abrir formulário para adicionar endereço
const openAddAddress = () => {
  editingAddress.value = null
  addressForm.reset()
  addressForm.is_delivery_address = !hasDeliveryAddress.value
  showAddressForm.value = true
}

// Abrir formulário para editar endereço
const openEditAddress = (address: any) => {
  editingAddress.value = address
  addressForm.id = address.id
  addressForm.zip_code = address.zip_code
  addressForm.street = address.street
  addressForm.number = address.number
  addressForm.neighborhood = address.neighborhood
  addressForm.city = address.city
  addressForm.state = address.state
  addressForm.complement = address.complement || ''
  addressForm.is_delivery_address = address.is_delivery_address
  showAddressForm.value = true
}

// Fechar formulário de endereço
const closeAddressForm = () => {
  showAddressForm.value = false
  editingAddress.value = null
  addressForm.reset()
}

// Salvar endereço
const saveAddress = () => {
  const routeName = editingAddress.value ? 'client.address.update' : 'client.address.store'
  const method = editingAddress.value ? 'put' : 'post'
  
  addressForm[method](route(routeName, editingAddress.value?.id), {
    onSuccess: () => {
      closeAddressForm()
    },
    onError: (errors) => {
      console.error('Erro ao salvar endereço:', errors)
    }
  })
}

// Excluir endereço
const deleteAddress = (addressId: number) => {
  if (!confirm('Tem certeza que deseja excluir este endereço?')) return
  
  useForm({}).delete(route('client.address.destroy', addressId), {
    onSuccess: () => {},
    onError: (errors) => {
      alert('Erro ao excluir endereço: ' + Object.values(errors).flat().join(', '))
    }
  })
}

// Definir como endereço de entrega
const setAsDelivery = (addressId: number) => {
  useForm({}).patch(route('client.address.set-delivery', addressId), {
    onSuccess: () => {}
  })
}

// Submeter formulário de perfil
const submitProfile = () => {
  profileForm.put(route('client.profile.update'), {
    onSuccess: () => {
      alert('Dados atualizados com sucesso!')
    }
  })
}

// Submeter formulário de senha
const submitPassword = () => {
  passwordForm.put(route('client.password.update'), {
    onSuccess: () => {
      passwordForm.reset()
      showPasswordForm.value = false
      alert('Senha alterada com sucesso!')
    }
  })
}

// Lista de estados brasileiros
const states = [
  { value: 'AC', label: 'Acre' },
  { value: 'AL', label: 'Alagoas' },
  { value: 'AP', label: 'Amapá' },
  { value: 'AM', label: 'Amazonas' },
  { value: 'BA', label: 'Bahia' },
  { value: 'CE', label: 'Ceará' },
  { value: 'DF', label: 'Distrito Federal' },
  { value: 'ES', label: 'Espírito Santo' },
  { value: 'GO', label: 'Goiás' },
  { value: 'MA', label: 'Maranhão' },
  { value: 'MT', label: 'Mato Grosso' },
  { value: 'MS', label: 'Mato Grosso do Sul' },
  { value: 'MG', label: 'Minas Gerais' },
  { value: 'PA', label: 'Pará' },
  { value: 'PB', label: 'Paraíba' },
  { value: 'PR', label: 'Paraná' },
  { value: 'PE', label: 'Pernambuco' },
  { value: 'PI', label: 'Piauí' },
  { value: 'RJ', label: 'Rio de Janeiro' },
  { value: 'RN', label: 'Rio Grande do Norte' },
  { value: 'RS', label: 'Rio Grande do Sul' },
  { value: 'RO', label: 'Rondônia' },
  { value: 'RR', label: 'Roraima' },
  { value: 'SC', label: 'Santa Catarina' },
  { value: 'SP', label: 'São Paulo' },
  { value: 'SE', label: 'Sergipe' },
  { value: 'TO', label: 'Tocantins' },
]
</script>

<template>
  <Head title="Meus Dados" />

  <div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Meus Dados</h1>
        <p class="mt-2 text-gray-600">Gerencie suas informações pessoais e endereços de entrega</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna Principal - Dados Pessoais -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Dados Pessoais -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <User class="w-5 h-5 text-indigo-600" />
                Dados Pessoais
              </h2>
            </div>
            
            <form @submit.prevent="submitProfile" class="p-6 space-y-6">
              <!-- Nome e Email -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                    Nome Completo *
                  </label>
                  <input
                    id="name"
                    v-model="profileForm.name"
                    type="text"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                    :class="{ 'border-red-500': profileForm.errors.name }"
                  />
                  <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600">{{ profileForm.errors.name }}</p>
                </div>

                <div>
                  <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                    E-mail *
                  </label>
                  <input
                    id="email"
                    v-model="profileForm.email"
                    type="email"
                    disabled
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed"
                  />
                  <p class="mt-1 text-xs text-gray-500">O e-mail não pode ser alterado</p>
                </div>
              </div>

              <!-- Documento -->
              <div v-if="client">
                <label class="block text-sm font-bold text-gray-700 mb-2">
                  {{ client.document_type }}
                </label>
                <input
                  type="text"
                  :value="client.formatted_document || client.document_number"
                  disabled
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed"
                />
                <p class="mt-1 text-xs text-gray-500">O documento não pode ser alterado</p>
              </div>

              <!-- Telefones -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="phone1" class="block text-sm font-bold text-gray-700 mb-2">
                    Telefone Principal
                  </label>
                  <input
                    id="phone1"
                    v-model="profileForm.phone1"
                    type="tel"
                    placeholder="(00) 00000-0000"
                    maxlength="15"
                    @input="formatPhone($event, 'phone1')"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                    :class="{ 'border-red-500': profileForm.errors.phone1 }"
                  />
                  <p v-if="profileForm.errors.phone1" class="mt-1 text-sm text-red-600">{{ profileForm.errors.phone1 }}</p>
                </div>

                <div>
                  <label for="contact1" class="block text-sm font-bold text-gray-700 mb-2">
                    Nome do Contato Principal
                  </label>
                  <input
                    id="contact1"
                    v-model="profileForm.contact1"
                    type="text"
                    placeholder="Quem devemos contactar"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                    :class="{ 'border-red-500': profileForm.errors.contact1 }"
                  />
                  <p v-if="profileForm.errors.contact1" class="mt-1 text-sm text-red-600">{{ profileForm.errors.contact1 }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="phone2" class="block text-sm font-bold text-gray-700 mb-2">
                    Telefone Secundário
                  </label>
                  <input
                    id="phone2"
                    v-model="profileForm.phone2"
                    type="tel"
                    placeholder="(00) 00000-0000"
                    maxlength="15"
                    @input="formatPhone($event, 'phone2')"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                    :class="{ 'border-red-500': profileForm.errors.phone2 }"
                  />
                  <p v-if="profileForm.errors.phone2" class="mt-1 text-sm text-red-600">{{ profileForm.errors.phone2 }}</p>
                </div>

                <div>
                  <label for="contact2" class="block text-sm font-bold text-gray-700 mb-2">
                    Nome do Contato Secundário
                  </label>
                  <input
                    id="contact2"
                    v-model="profileForm.contact2"
                    type="text"
                    placeholder="Contato alternativo"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                    :class="{ 'border-red-500': profileForm.errors.contact2 }"
                  />
                  <p v-if="profileForm.errors.contact2" class="mt-1 text-sm text-red-600">{{ profileForm.errors.contact2 }}</p>
                </div>
              </div>

              <!-- Botão Salvar -->
              <div class="flex justify-end pt-4 border-t border-gray-100">
                <button
                  type="submit"
                  :disabled="profileForm.processing"
                  class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                  <Check v-if="!profileForm.processing" class="w-5 h-5" />
                  <span v-if="profileForm.processing">Salvando...</span>
                  <span v-else>Salvar Dados</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Endereços -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
              <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <MapPin class="w-5 h-5 text-indigo-600" />
                Endereços de Entrega
              </h2>
              <button
                @click="openAddAddress"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition-all flex items-center gap-2"
              >
                <Plus class="w-4 h-4" />
                Adicionar Endereço
              </button>
            </div>

            <div class="p-6">
              <!-- Alerta se não tiver endereço de entrega -->
              <div v-if="!hasDeliveryAddress && addresses.length > 0" class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <p class="text-sm text-amber-800 flex items-center gap-2">
                  <AlertTriangle class="w-5 h-5" />
                  <span>Você precisa definir pelo menos um endereço como principal de entrega.</span>
                </p>
              </div>

              <!-- Lista de Endereços -->
              <div v-if="addresses.length > 0" class="space-y-4">
                <div
                  v-for="address in addresses"
                  :key="address.id"
                  class="border border-gray-200 rounded-xl p-4 hover:border-indigo-300 transition-all"
                  :class="{ 'border-indigo-500 bg-indigo-50/50': address.is_delivery_address }"
                >
                  <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                      <div class="flex items-center gap-2 mb-2">
                        <span class="font-bold text-gray-900">
                          {{ address.street }}, {{ address.number }}
                        </span>
                        <span
                          v-if="address.is_delivery_address"
                          class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full flex items-center gap-1"
                        >
                          <Home class="w-3 h-3" />
                          Principal
                        </span>
                      </div>
                      <p class="text-sm text-gray-600">
                        {{ address.neighborhood }}, {{ address.city }} - {{ address.state }}
                      </p>
                      <p class="text-sm text-gray-600">CEP: {{ address.zip_code }}</p>
                      <p v-if="address.complement" class="text-sm text-gray-500 mt-1">
                        {{ address.complement }}
                      </p>
                    </div>

                    <div class="flex items-center gap-2">
                      <button
                        v-if="!address.is_delivery_address"
                        @click="setAsDelivery(address.id)"
                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                        title="Definir como endereço principal"
                      >
                        <Home class="w-5 h-5" />
                      </button>
                      <button
                        @click="openEditAddress(address)"
                        class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                        title="Editar"
                      >
                        <Pencil class="w-5 h-5" />
                      </button>
                      <button
                        @click="deleteAddress(address.id)"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all"
                        title="Excluir"
                      >
                        <Trash2 class="w-5 h-5" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sem endereços -->
              <div v-else class="text-center py-12">
                <MapPin class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                <p class="text-gray-500 font-medium">Nenhum endereço cadastrado</p>
                <p class="text-sm text-gray-400 mt-1">Adicione um endereço para receber seus pedidos</p>
                <button
                  @click="openAddAddress"
                  class="mt-4 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition-all inline-flex items-center gap-2"
                >
                  <Plus class="w-4 h-4" />
                  Adicionar Endereço
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Coluna Lateral - Segurança -->
        <div class="space-y-6">
          <!-- Alterar Senha -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <h2 class="text-lg font-bold text-gray-900">Segurança</h2>
            </div>
            <div class="p-6">
              <button
                @click="showPasswordForm = !showPasswordForm"
                class="w-full py-3 border-2 border-indigo-600 text-indigo-600 font-bold rounded-xl hover:bg-indigo-50 transition-all"
              >
                {{ showPasswordForm ? 'Cancelar' : 'Alterar Senha' }}
              </button>

              <form v-if="showPasswordForm" @submit.prevent="submitPassword" class="mt-6 space-y-4">
                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Senha Atual</label>
                  <input
                    v-model="passwordForm.current_password"
                    type="password"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                  />
                  <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">
                    {{ passwordForm.errors.current_password }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Nova Senha</label>
                  <input
                    v-model="passwordForm.new_password"
                    type="password"
                    required
                    minlength="8"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                  />
                  <p v-if="passwordForm.errors.new_password" class="mt-1 text-sm text-red-600">
                    {{ passwordForm.errors.new_password }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Confirmar Nova Senha</label>
                  <input
                    v-model="passwordForm.new_password_confirmation"
                    type="password"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                  />
                </div>

                <button
                  type="submit"
                  :disabled="passwordForm.processing"
                  class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all disabled:opacity-50"
                >
                  {{ passwordForm.processing ? 'Alterando...' : 'Confirmar Alteração' }}
                </button>
              </form>
            </div>
          </div>

          <!-- Dica -->
          <div class="bg-blue-50 rounded-2xl p-6 border border-blue-100">
            <h3 class="text-sm font-bold text-blue-900 mb-2">Dica importante</h3>
            <p class="text-sm text-blue-700">
              Mantenha seus dados sempre atualizados para garantir a entrega dos seus pedidos.
              Você pode cadastrar múltiplos endereços e definir um como principal.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Endereço -->
    <div v-if="showAddressForm" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900">
            {{ editingAddress ? 'Editar Endereço' : 'Adicionar Endereço' }}
          </h3>
          <button @click="closeAddressForm" class="p-2 hover:bg-gray-100 rounded-lg transition-all">
            <X class="w-5 h-5 text-gray-500" />
          </button>
        </div>

        <form @submit.prevent="saveAddress" class="p-6 space-y-6">
          <!-- CEP -->
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">CEP *</label>
            <input
              v-model="addressForm.zip_code"
              type="text"
              placeholder="00000-000"
              maxlength="9"
              required
              @input="formatZipCode"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
              :class="{ 'border-red-500': addressForm.errors.zip_code }"
            />
            <p v-if="addressForm.errors.zip_code" class="mt-1 text-sm text-red-600">{{ addressForm.errors.zip_code }}</p>
          </div>

          <!-- Rua e Número -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-bold text-gray-700 mb-2">Rua/Logradouro *</label>
              <input
                v-model="addressForm.street"
                type="text"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                :class="{ 'border-red-500': addressForm.errors.street }"
              />
              <p v-if="addressForm.errors.street" class="mt-1 text-sm text-red-600">{{ addressForm.errors.street }}</p>
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Número *</label>
              <input
                v-model="addressForm.number"
                type="text"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                :class="{ 'border-red-500': addressForm.errors.number }"
              />
              <p v-if="addressForm.errors.number" class="mt-1 text-sm text-red-600">{{ addressForm.errors.number }}</p>
            </div>
          </div>

          <!-- Bairro -->
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Bairro *</label>
            <input
              v-model="addressForm.neighborhood"
              type="text"
              required
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
              :class="{ 'border-red-500': addressForm.errors.neighborhood }"
            />
            <p v-if="addressForm.errors.neighborhood" class="mt-1 text-sm text-red-600">{{ addressForm.errors.neighborhood }}</p>
          </div>

          <!-- Cidade e Estado -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Cidade *</label>
              <input
                v-model="addressForm.city"
                type="text"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                :class="{ 'border-red-500': addressForm.errors.city }"
              />
              <p v-if="addressForm.errors.city" class="mt-1 text-sm text-red-600">{{ addressForm.errors.city }}</p>
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Estado *</label>
              <select
                v-model="addressForm.state"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                :class="{ 'border-red-500': addressForm.errors.state }"
              >
                <option value="">Selecione...</option>
                <option v-for="state in states" :key="state.value" :value="state.value">
                  {{ state.value }} - {{ state.label }}
                </option>
              </select>
              <p v-if="addressForm.errors.state" class="mt-1 text-sm text-red-600">{{ addressForm.errors.state }}</p>
            </div>
          </div>

          <!-- Complemento -->
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Complemento</label>
            <input
              v-model="addressForm.complement"
              type="text"
              placeholder="Apto, Bloco, Sala, etc."
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
              :class="{ 'border-red-500': addressForm.errors.complement }"
            />
            <p v-if="addressForm.errors.complement" class="mt-1 text-sm text-red-600">{{ addressForm.errors.complement }}</p>
          </div>

          <!-- Checkbox de entrega -->
          <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
            <input
              id="is_delivery"
              v-model="addressForm.is_delivery_address"
              type="checkbox"
              class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
            />
            <label for="is_delivery" class="text-sm font-medium text-gray-700 cursor-pointer">
              Definir como endereço principal de entrega
            </label>
          </div>

          <!-- Botões -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button
              type="button"
              @click="closeAddressForm"
              class="px-6 py-3 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="addressForm.processing"
              class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all disabled:opacity-50 flex items-center gap-2"
            >
              <Check v-if="!addressForm.processing" class="w-5 h-5" />
              <span v-if="addressForm.processing">Salvando...</span>
              <span v-else>{{ editingAddress ? 'Atualizar' : 'Salvar' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
