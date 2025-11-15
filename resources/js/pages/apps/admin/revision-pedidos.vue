<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/axios'

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
    subject: 'Admin', // Solo para Admins
    action: 'manage',
  },
})

const pedidos = ref([])
const isLoading = ref(true)
const error = ref(null)

// Columnas de la tabla
const headers = [
  { title: 'Pedido ID', key: 'id', sortable: true },
  { title: 'Fecha', key: 'fecha', sortable: true },
  { title: 'Comprador', key: 'comprador_nombre', sortable: true },
  { title: 'N° Items', key: 'items_count', sortable: false },
  { title: 'Total', key: 'total', sortable: true },
  { title: 'Estado', key: 'estado_pedido', sortable: true },

  // { title: 'Acciones', key: 'acciones', sortable: false }, // Para el futuro
]

onMounted(async () => {
  try {
    // 1. Llamamos a la nueva API
    const response = await api.get('/admin/pedidos-historial')

    pedidos.value = response.data
  } catch (err) {
    console.error('Error al cargar el historial de pedidos:', err)
    error.value = 'No se pudo cargar el historial de pedidos.'
    alert(error.value)
  } finally {
    isLoading.value = false
  }
})

// Función para dar color al estado
const getEstadoColor = estado => {
  if (estado === 'pendiente') return 'warning'
  if (estado === 'enviado') return 'info'
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
          🧾 Historial Maestro de Pedidos
        </h4>
      </VCardTitle>
      <VCardText>
        <!-- Alerta de Carga -->
        <VAlert
          v-if="isLoading"
          type="info"
          variant="tonal"
          title="Cargando historial de pedidos..."
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
          :items-per-page="15"
          class="elevation-1"
          no-data-text="Aún no se ha realizado ningún pedido en la plataforma."
        >
          <!-- Total -->
          <template #item.total="{ item }">
            <span class="text-success font-weight-medium">
              Q{{ item.total.toFixed(2) }}
            </span>
          </template>

          <!-- Estado -->
          <template #item.estado_pedido="{ item }">
            <VChip
              :color="getEstadoColor(item.estado_pedido)"
              size="small"
              label
            >
              {{ item.estado_pedido }}
            </VChip>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </VContainer>
</template>
