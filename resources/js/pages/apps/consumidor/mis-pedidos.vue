<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/axios'
import { useRouter } from 'vue-router'

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
  },
})

const router = useRouter()
const pedidos = ref([])
const isLoading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const response = await api.get('/pedidos/consumidor')

    pedidos.value = response.data
  } catch (err) {
    console.error('Error al cargar los pedidos:', err)
    error.value = 'No se pudieron cargar tus pedidos.'
  } finally {
    isLoading.value = false
  }
})

// Función para navegar al detalle del producto
const verProducto = productoId => {
  router.push(`/apps/consumidor/detalle-producto/${productoId}`)
}
</script>

<template>
  <VContainer>
    <h4 class="text-h4 mb-6">
      Historial de Mis Pedidos
    </h4>

    <VAlert
      v-if="isLoading"
      type="info"
      variant="tonal"
      title="Cargando tus pedidos..."
    />

    <VAlert
      v-else-if="error"
      type="error"
      variant="tonal"
      :title="error"
    />

    <VCard v-else-if="pedidos.length === 0">
      <VCardText>
        <VAlert
          type="info"
          variant="tonal"
          prominent
          icon="tabler-shopping-cart-off"
        >
          Aún no has realizado ningún pedido.
        </VAlert>
        <VBtn
          color="primary"
          class="mt-4"
          to="/apps/consumidor/catalogo"
        >
          Ir al Catálogo
        </VBtn>
      </VCardText>
    </VCard>

    <VExpansionPanels
      v-else
      variant="inset"
    >
      <VExpansionPanel
        v-for="pedido in pedidos"
        :key="pedido.id"
        class="mb-4"
      >
        <VExpansionPanelTitle>
          <VRow
            no-gutters
            class="d-flex align-center"
          >
            <VCol
              cols="12"
              md="3"
            >
              <span class="text-caption text-medium-emphasis">Pedido #</span>
              <div class="text-body-1 font-weight-medium">
                {{ pedido.id }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <span class="text-caption text-medium-emphasis">Fecha</span>
              <div class="text-body-1">
                {{ pedido.fecha }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <span class="text-caption text-medium-emphasis">Estado</span>
              <div>
                <VChip
                  color="warning"
                  size="small"
                  label
                >
                  {{ pedido.estado }}
                </VChip>
              </div>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <span class="text-caption text-medium-emphasis">Total</span>
              <div class="text-body-1 text-success font-weight-medium">
                Q{{ pedido.total.toFixed(2) }}
              </div>
            </VCol>
          </VRow>
        </VExpansionPanelTitle>

        <VExpansionPanelText>
          <VDivider class="mb-4" />
          <h6 class="text-h6 mb-3">
            Productos en este pedido
          </h6>

          <VList lines="two">
            <VListItem
              v-for="producto in pedido.productos"
              :key="producto.id"
              class="mb-2"
            >
              <template #prepend>
                <VAvatar
                  rounded="lg"
                  size="60"
                  :image="producto.imagen_url || 'https://placehold.co/60x60/f2f2f2/7F8C8D?text=IMG'"
                />
              </template>

              <VListItemTitle class="font-weight-medium">
                {{ producto.nombre }}
              </VListItemTitle>
              <VListItemSubtitle>
                {{ producto.cantidad }} x Q{{ producto.precio_unitario.toFixed(2) }}
              </VListItemSubtitle>

              <template #append>
                <VBtn
                  variant="text"
                  color="primary"
                  @click="verProducto(producto.id)"
                >
                  Ver Producto
                </VBtn>
              </template>
            </VListItem>
          </VList>
        </VExpansionPanelText>
      </VExpansionPanel>
    </VExpansionPanels>
  </VContainer>
</template>
