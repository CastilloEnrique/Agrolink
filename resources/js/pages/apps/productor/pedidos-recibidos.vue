
<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/axios'

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
  },
})

const pedidos = ref([])
const isLoading = ref(true)
const error = ref(null)

// Datos para la tabla (sin cambios)
const headers = [
  { title: 'Pedido ID', key: 'pedido_id', sortable: true },
  { title: 'Fecha', key: 'fecha', sortable: true },
  { title: 'Producto Vendido', key: 'producto_nombre', sortable: true },
  { title: 'Comprador', key: 'comprador_nombre', sortable: true },
  { title: 'Cantidad', key: 'cantidad', sortable: false },
  { title: 'Total Item', key: 'subtotal', sortable: true },
  { title: 'Estado', key: 'estado_pedido', sortable: true },
  { title: 'Acciones', key: 'acciones', sortable: false },
]

// Función de carga (Actualizada para añadir 'isUpdating')
onMounted(async () => {
  try {
    const response = await api.get('/productor/pedidos-recibidos')


    // 💡 IMPORTANTE: Mapear para añadir el estado de carga y arreglar el bug de Pedido #8
    pedidos.value = response.data.map(p => ({
      ...p,
      isUpdating: false, // Añadimos el indicador de loading
    }))
  } catch (err) {
    console.error('Error al cargar los pedidos del productor:', err)
    error.value = 'No se pudieron cargar los pedidos recibidos.'
  } finally {
    isLoading.value = false
  }
})

// --- 👇 ¡FUNCIÓN DE CAMBIAR ESTADO (ACTUALIZADA)! 👇 ---
const cambiarEstado = async (item, nuevoEstado) => {

  if (item.isUpdating) return

  const pedidoId = item.pedido_id

  item.isUpdating = true // 1. Mostramos un 'loading'

  try {
    // 2. Llamamos a la nueva API
    const response = await api.post(`/pedidos/${pedidoId}/actualizar-estado`, {
      estado: nuevoEstado,
    })

    // 3. Si tiene éxito, actualizamos el estado localmente
    item.estado_pedido = nuevoEstado
    alert(response.data.message || 'Estado actualizado')

  } catch (err) {
    console.error('Error al cambiar el estado:', err)
    alert(err.response?.data?.message || 'Error al actualizar el estado.')
  } finally {
    item.isUpdating = false // 4. Quitamos el 'loading'
  }
}

// Función para darle color al chip (Actualizada con todos los estados)
const getEstadoColor = estado => {
  if (estado === 'pendiente') return 'warning'
  if (estado === 'en_preparacion') return 'info'
  if (estado === 'enviado') return 'primary'
  if (estado === 'entregado') return 'success'
  if (estado === 'cancelado') return 'error'
  
  return 'default'
}
</script>

<template>
  <VContainer>
    <VCard>
      <VCardTitle>
        <h4 class="text-h4 py-2">
          📦 Pedidos Recibidos
        </h4>
      </VCardTitle>
      <VCardText>
        <!-- Alerta de Carga -->
        <VAlert
          v-if="isLoading"
          type="info"
          variant="tonal"
          title="Cargando pedidos..."
        />

        <!-- Alerta de Error -->
        <VAlert
          v-else-if="error"
          type="error"
          variant="tonal"
          :title="error"
        />

        <!-- Tabla de Pedidos -->
        <VDataTable
          v-else
          :headers="headers"
          :items="pedidos"
          :items-per-page="10"
          class="elevation-1"
          no-data-text="Aún no has recibido ningún pedido."
        >
          <!-- Plantilla para el producto -->
          <template #item.producto_nombre="{ item }">
            <div class="d-flex align-center py-2">
              <VAvatar
                rounded="lg"
                size="40"
                :image="item.imagen_url || 'https://placehold.co/40x40/f2f2f2/7F8C8D?text=IMG'"
                class="me-3"
              />
              <span>{{ item.producto_nombre }}</span>
            </div>
          </template>

          <!-- Plantilla para el total -->
          <template #item.subtotal="{ item }">
            <span class="text-success font-weight-medium">
              Q{{ item.subtotal.toFixed(2) }}
            </span>
          </template>

          <!-- Plantilla para el estado (Actualizada) -->
          <template #item.estado_pedido="{ item }">
            <VChip
              :color="getEstadoColor(item.estado_pedido)"
              size="small"
              label
            >
              {{ item.estado_pedido.replace('_', ' ') }} <!-- Reemplaza guiones bajos -->
            </VChip>
          </template>

          <!-- Plantilla para acciones (El menú de 3 puntos) -->
          <template #item.acciones="{ item }">
            <VMenu location="start">
              <template #activator="{ props }">
                <VBtn
                  icon
                  variant="text"
                  size="small"
                  v-bind="props"
                  :loading="item.isUpdating"
                  :disabled="item.estado_pedido === 'entregado' || item.estado_pedido === 'cancelado'"
                >
                  <VIcon icon="tabler-dots-vertical" />
                </VBtn>
              </template>
              <VList>
                <!-- EN PREPARACIÓN -->
                <VListItem
                  v-if="item.estado_pedido === 'pendiente'"
                  @click="cambiarEstado(item, 'en_preparacion')"
                >
                  <VListItemTitle>Marcar como "En Preparación"</VListItemTitle>
                </VListItem>

                <!-- ENVIADO -->
                <VListItem
                  v-if="item.estado_pedido === 'en_preparacion'"
                  @click="cambiarEstado(item, 'enviado')"
                >
                  <VListItemTitle>Marcar como "Enviado"</VListItemTitle>
                </VListItem>

                <!-- ENTREGADO -->
                <VListItem
                  v-if="item.estado_pedido === 'enviado'"
                  @click="cambiarEstado(item, 'entregado')"
                >
                  <VListItemTitle>Marcar como "Entregado"</VListItemTitle>
                </VListItem>

                <VDivider v-if="item.estado_pedido !== 'entregado' && item.estado_pedido !== 'cancelado'" />

                <!-- CANCELADO -->
                <VListItem
                  v-if="item.estado_pedido !== 'entregado' && item.estado_pedido !== 'cancelado'"
                  @click="cambiarEstado(item, 'cancelado')"
                >
                  <VListItemTitle class="text-error">
                    Marcar como "Cancelado"
                  </VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </VContainer>
</template>
