<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/axios'
import { useRouter } from 'vue-router'

// 🧩 Define el layout (no requiere permisos especiales)
definePage({
  meta: {
    layout: 'default',
  },
})

const router = useRouter()

// --- Estado ---
const productos = ref([])
const categorias = ref([])
const isLoading = ref(true)
const errorCarga = ref(null)

const searchQuery = ref('')
const selectedCategory = ref(null)
const selectedPriceRange = ref([0, 500]) // Rango inicial

// --- Carga inicial de datos ---
const fetchCategories = async () => {
  try {
    const responseCategorias = await api.get('/categorias-producto')

    categorias.value = responseCategorias.data
  } catch (e) {
    console.warn('Error al cargar categorías:', e)
  }
}

const fetchProducts = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/catalogo')

    productos.value = response.data
  } catch (error) {
    console.error('Error al cargar el catálogo:', error)
    errorCarga.value = 'No se pudo cargar el catálogo de productos.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchProducts()
  fetchCategories()
})

// --- Filtros ---
const filteredProducts = computed(() => {
  let list = productos.value

  // 🔎 Búsqueda
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()

    list = list.filter(p =>
      p.nombre.toLowerCase().includes(query) ||
      p.descripcion.toLowerCase().includes(query),
    )
  }

  // 🏷️ Categoría
  if (selectedCategory.value) {
    list = list.filter(p => p.categoria_nombre === selectedCategory.value)
  }

  // 💰 Rango de precio
  const [min, max] = selectedPriceRange.value

  list = list.filter(p => p.precio_referencia >= min && p.precio_referencia <= max)

  return list
})


// --- Acciones ---
// --- Acciones ---
const openProductDetail = productId => {
  // 🚀 SOLUCIÓN FINAL: Usar PATH DIRECTO para evitar el error 'No match for name'
  router.push({
    path: `/apps/consumidor/detalle-producto/${productId}`,
  })
}

const contactProducer = producerId => {
  alert(`Iniciar contacto con Productor ID: ${producerId} (Próximamente)`)
}
</script>

<template>
  <VRow>
    <!-- 🔹 Columna lateral de filtros -->
    <VCol
      cols="12"
      md="3"
    >
      <VCard title="Filtros">
        <VCardText>
          <AppTextField
            v-model="searchQuery"
            label="Buscar por Nombre"
            prepend-inner-icon="tabler-search"
            class="mb-4"
          />

          <AppSelect
            v-model="selectedCategory"
            label="Tipo de Producto"
            :items="categorias"
            item-title="nombre"
            item-value="nombre"
            placeholder="Todas las categorías"
            clearable
            class="mb-4"
          />

          <VLabel class="mb-3">
            Rango de Precio (Q)
          </VLabel>
          <VRangeSlider
            v-model="selectedPriceRange"
            :max="500"
            :min="0"
            step="1"
            thumb-label="always"
            color="primary"
          >
            <template #append>
              <VTextField
                :model-value="selectedPriceRange[1]"
                style="max-width: 60px"
                density="compact"
                type="number"
                disabled
              />
            </template>
          </VRangeSlider>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 🔹 Catálogo de productos -->
    <VCol
      cols="12"
      md="9"
    >
      <VRow v-if="isLoading">
        <VCol cols="12">
          <VAlert
            type="info"
            variant="tonal"
            title="Cargando Catálogo..."
          />
        </VCol>
      </VRow>

      <VRow v-else-if="errorCarga">
        <VCol cols="12">
          <VAlert
            type="error"
            variant="tonal"
            :title="errorCarga"
          />
        </VCol>
      </VRow>

      <VRow v-else-if="filteredProducts.length === 0">
        <VCol cols="12">
          <VAlert
            type="warning"
            variant="tonal"
            title="No se encontraron productos"
          >
            Ajusta los filtros o intenta con otra búsqueda.
          </VAlert>
        </VCol>
      </VRow>

      <VRow v-else>
        <VCol
          v-for="producto in filteredProducts"
          :key="producto.id"
          cols="12"
          sm="6"
          lg="4"
        >
          <VCard
            class="product-card h-100"
            hover
          >
            <VImg
              :src="producto.imagen_url || 'https://placehold.co/600x400/00A389/ffffff?text=AgroLink'"
              height="200"
              cover
            />

            <VCardText>
              <VChip
                color="info"
                size="small"
                class="mb-2"
              >
                {{ producto.categoria_nombre }}
              </VChip>

              <h5 class="text-h5 mb-2 text-truncate">
                {{ producto.nombre }}
              </h5>

              <VTooltip
                activator="parent"
                location="top"
              >
                {{ producto.descripcion }}
              </VTooltip>

              <VDivider class="my-3" />

              <div class="d-flex justify-space-between align-center">
                <div class="d-flex flex-column">
                  <span class="text-caption text-medium-emphasis">
                    Productor: {{ producto.productor }}
                  </span>
                  <h6 class="text-h6 text-primary">
                    Q{{ producto.precio_referencia.toFixed(2) }}
                    <span class="text-caption text-medium-emphasis">
                      / {{ producto.unidad_medida }}
                    </span>
                  </h6>
                </div>

                <VBtn
                  icon
                  variant="tonal"
                  color="warning"
                  size="small"
                  @click="contactProducer(producto.productor_id)"
                >
                  <VIcon icon="tabler-message" />
                  <VTooltip
                    activator="parent"
                    location="bottom"
                  >
                    Contactar
                  </VTooltip>
                </VBtn>
              </div>
            </VCardText>

            <VCardActions class="pt-0">
              <VBtn
                block
                variant="flat"
                color="primary"
                @click="openProductDetail(producto.id)"
              >
                Ver Detalle
              </VBtn>

              <VBtn
                icon
                variant="tonal"
                color="success"
                @click="alert('Añadir al carrito: Próximamente')"
              >
                <VIcon icon="tabler-shopping-cart-plus" />
              </VBtn>
            </VCardActions>
          </VCard>
        </VCol>
      </VRow>
    </VCol>
  </VRow>
</template>
