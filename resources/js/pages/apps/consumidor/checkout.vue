<script setup>
import { ref, onMounted, computed } from 'vue'
import { useCartStore } from '@/stores/useCartStore'
import api from '@/services/axios'
import { useRouter } from 'vue-router'

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
  },
})

const cartStore = useCartStore()
const router = useRouter()

const metodosEntrega = ref([])
const metodoSeleccionado = ref(null)
const direccionEntrega = ref('')
const isLoading = ref(true)
const isPlacingOrder = ref(false)

// Cargar métodos de entrega al montar
onMounted(async () => {
  // Si el carrito está vacío, no deberíamos estar aquí
  if (cartStore.cartItemCount === 0) {
    router.push({ name: 'apps-consumidor-catalogo' })
    return
  }

  try {
    const response = await api.get('/logistica/metodos-entrega')
    metodosEntrega.value = response.data
    // Opcional: seleccionar el primero por defecto
    if (metodosEntrega.value.length > 0) {
      metodoSeleccionado.value = metodosEntrega.value[0].id
    }
  } catch (error) {
    console.error('Error al cargar métodos de entrega:', error)
    alert('No se pudieron cargar los métodos de entrega.')
  } finally {
    isLoading.value = false
  }
})

// --- Cálculos de Total ---
const costoEnvio = computed(() => {
  if (!metodoSeleccionado.value) return 0
  const metodo = metodosEntrega.value.find(m => m.id === metodoSeleccionado.value)
  return metodo ? Number(metodo.costo) : 0
})

const totalPedido = computed(() => {
  return cartStore.cartTotal + costoEnvio.value
})

const isDireccionRequired = computed(() => {
  if (!metodoSeleccionado.value) return false
  const metodo = metodosEntrega.value.find(m => m.id === metodoSeleccionado.value)
  // Asume que si el nombre incluye 'Domicilio' o 'Envío', requiere dirección
  return metodo && (metodo.nombre.includes('Domicilio') || metodo.nombre.includes('Envío'))
})

// --- Finalizar Pedido ---
const finalizarPedido = async () => {
  // Validación
  if (!metodoSeleccionado.value) {
    alert('Por favor, selecciona un método de entrega.')
    return
  }
  if (isDireccionRequired.value && !direccionEntrega.value) {
    alert('Por favor, ingresa tu dirección de entrega.')
    return
  }

  isPlacingOrder.value = true

  // Preparamos el payload para el PedidoController
  const payload = {
    // 1. Los items del carrito (desde Pinia)
    items: cartStore.cartItems.map(item => ({
      id: item.id,
      quantity: item.quantity,
    })),

    // 2. La nueva información de logística
    metodo_entrega_id: metodoSeleccionado.value,
    direccion_entrega: direccionEntrega.value,
  }

  try {
    // Llamamos a la MISMA API de 'store' de PedidoController
    const response = await api.post('/pedidos', payload)

    alert(response.data.message || '¡Pedido realizado exitosamente!')
    cartStore.clearCart() // Limpiamos el carrito en Pinia
    router.push({ name: 'apps-consumidor-mis-pedidos' }) // Llevamos al historial

  } catch (error) {
    console.error('Error al finalizar el pedido:', error)
    alert(error.response?.data?.message || 'Error al procesar el pedido.')
  } finally {
    isPlacingOrder.value = false
  }
}
</script>

<template>
  <VContainer>
    <h4 class="text-h4 mb-6">
      Finalizar Pedido
    </h4>

    <VAlert
      v-if="isLoading"
      type="info"
      variant="tonal"
      title="Cargando métodos de entrega..."
    />

    <VRow v-else>
      <!-- Columna Principal: Dirección y Método -->
      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardTitle>1. Método de Entrega</VCardTitle>
          <VCardText>
            <VRadioGroup v-model="metodoSeleccionado">
              <VRadio
                v-for="metodo in metodosEntrega"
                :key="metodo.id"
                :label="metodo.nombre"
                :value="metodo.id"
              >
                <template #label>
                  <div>
                    <span class="font-weight-medium">{{ metodo.nombre }} (+Q{{ Number(metodo.costo).toFixed(2) }})</span>
                    <div class="text-caption">
                      {{ metodo.descripcion }}
                    </div>
                  </div>
                </template>
              </VRadio>
            </VRadioGroup>
          </VCardText>

          <VDivider />

          <VCardTitle>2. Dirección de Entrega</VCardTitle>
          <VCardText>
            <AppTextarea
              v-model="direccionEntrega"
              label="Dirección de envío"
              placeholder="Ingresa tu dirección completa, departamento, municipio y referencias..."
              rows="4"
              :disabled="!isDireccionRequired"
              :hint="!isDireccionRequired ? 'Este método de entrega no requiere dirección' : ''"
            />
          </VCardText>
        </VCard>
      </VCol>

      <!-- Columna Lateral: Resumen -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardTitle>Resumen de Compra</VCardTitle>
          <VCardText>
            <!-- Lista de Items (Resumida) -->
            <div
              v-for="item in cartStore.cartItems"
              :key="item.id"
              class="d-flex justify-space-between mb-2"
            >
              <span class="text-medium-emphasis">{{ item.name }} (x{{ item.quantity }})</span>
              <span class="font-weight-medium">Q{{ item.subtotal.toFixed(2) }}</span>
            </div>

            <VDivider class="my-3" />

            <!-- Subtotal -->
            <div class="d-flex justify-space-between mb-2">
              <span class="text-medium-emphasis">Subtotal</span>
              <span class="font-weight-medium">Q{{ cartStore.cartTotal.toFixed(2) }}</span>
            </div>

            <!-- Costo de Envío -->
            <div class="d-flex justify-space-between mb-2">
              <span class="text-medium-emphasis">Envío</span>
              <span class="font-weight-medium">Q{{ costoEnvio.toFixed(2) }}</span>
            </div>

            <VDivider class="my-3" />

            <!-- Total -->
            <div class="d-flex justify-space-between text-h6 text-primary">
              <span>Total</span>
              <span>Q{{ totalPedido.toFixed(2) }}</span>
            </div>
          </VCardText>

          <VCardActions>
            <VBtn
              block
              color="success"
              size="large"
              :loading="isPlacingOrder"
              @click="finalizarPedido"
            >
              Confirmar y Realizar Pedido
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>
