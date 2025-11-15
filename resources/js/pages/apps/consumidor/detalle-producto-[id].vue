<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/axios'
import { useCartStore } from '@/stores/useCartStore'

// Ruta de esta página 
definePage({
  name: 'apps-consumidor-detalle-producto',
  path: '/apps/consumidor/detalle-producto/:id',
  meta: { layout: 'default' },
})

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()

const producto = ref(null)
const relacionados = ref([])
const isLoading = ref(true)
const error = ref(null)
const quantity = ref(1)

// ----------------------------- 
// Agregar al Carrito 
// -----------------------------
const handleAddToCart = () => {
  if (!producto.value) return

  cartStore.addToCart(producto.value.id, quantity.value)   // ✅ SOLO EL ID
  alert(`✅ ${producto.value.nombre} añadido al carrito`)

  quantity.value = 1
}

// const handleAddToCart = () => {
//   if (!producto.value) return
//
//   cartStore.addToCart(producto.value, quantity.value)
//   alert(`✅ ${producto.value.nombre} añadido al carrito`)
//
//   quantity.value = 1
// }

// ----------------------------- 
// Contactar por WhatsApp 
// ----------------------------- 
const contactarWhatsapp = async productoId => {
  try {
    const resp = await api.get(`/producto/${productoId}/contactar-whatsapp`)

    window.open(resp.data.whatsapp_url, '_blank')
  } catch (error) {
    console.error(error)
    alert('❌ No se pudo generar el enlace de WhatsApp.')
  }
}

// ----------------------------- 
// Cargar información al entrar 
// ----------------------------- 
onMounted(async () => {
  const productId = route.params.id

  if (!productId) {
    error.value = 'ID de producto inválido.'
    isLoading.value = false

    return
  }

  try {
    // Cargar detalle del producto 
    const response = await api.get(`/catalogo/${productId}`)

    producto.value = response.data

    // Cargar relacionados 
    const relatedResponse = await api.get(`/producto/${productId}/relacionados`)

    relacionados.value = relatedResponse.data

  } catch (err) {
    console.error('Error al cargar detalle del producto:', err)
    error.value = 'El producto no existe o no está disponible.'
  } finally {
    isLoading.value = false
  }
})

// Volver al catálogo 
const goBackToCatalog = () => router.back()
</script>

<template>
  <VContainer>
    <VBtn
      v-if="!isLoading && !error"
      prepend-icon="tabler-arrow-narrow-left"
      variant="text"
      color="primary"
      class="mb-4"
      @click="goBackToCatalog"
    >
      Volver al Catálogo
    </VBtn>

    <VAlert
      v-if="error"
      type="error"
      variant="tonal"
      :title="error"
    >
      <VBtn
        variant="text"
        color="error"
        class="mt-2"
        @click="router.push('/apps/consumidor/catalogo')"
      >
        Ir al Catálogo
      </VBtn>
    </VAlert>

    <VAlert
      v-else-if="isLoading"
      type="info"
      variant="tonal"
      title="Cargando detalles del producto..."
    />

    <VCard
      v-else-if="producto"
      class="pa-4"
    >
      <VRow>
        <VCol
          cols="12"
          md="5"
        >
          <VCarousel
            v-if="producto.imagenes && producto.imagenes.length > 0"
            height="350"
            show-arrows="hover"
            cycle
            hide-delimiters
            class="rounded-lg elevation-2"
          >
            <VCarouselItem
              v-for="imagen in producto.imagenes"
              :key="imagen.id"
              :src="imagen.url"
              cover
            />
          </VCarousel>

          <VImg
            v-else
            src="https://placehold.co/800x600?text=Producto"
            height="350"
            cover
            class="rounded-lg elevation-2"
          />
        </VCol>
        <VCol
          cols="12"
          md="7"
        >
          <VCardTitle class="text-h3 pa-0 mb-4">
            {{ producto.nombre }}
          </VCardTitle>

          <VChip
            color="info"
            class="mb-4"
          >
            {{ producto.categoria_nombre }}
          </VChip>

          <h4 class="text-h4 text-primary my-4">
            Q{{ producto.precio_referencia.toFixed(2) }}
            <span class="text-h6 text-medium-emphasis">/ {{ producto.unidad_medida }}</span>
          </h4>

          <p class="text-body-1 mt-4">
            {{ producto.descripcion }}
          </p>

          <VDivider class="my-4" />

          <div class="mb-4">
            <h6 class="text-h6 mb-2">
              Detalles del Productor
            </h6>

            <div class="d-flex align-center mb-1">
              <VIcon
                icon="tabler-user"
                size="20"
                class="me-2"
              />
              <span class="text-medium-emphasis">Productor:</span>
              <strong class="ms-2">{{ producto.productor }}</strong>
            </div>

            <div class="d-flex align-center mb-1">
              <VIcon
                icon="tabler-map-pin"
                size="20"
                class="me-2"
              />
              <span class="text-medium-emphasis">Ubicación:</span>
              <span class="ms-2">{{ producto.ubicacion_nombre || 'No especificada' }}</span>
            </div>

            <div class="d-flex align-center">
              <VIcon
                icon="tabler-calendar"
                size="20"
                class="me-2"
              />
              <span class="text-medium-emphasis">Fecha de Cosecha:</span>
              <span class="ms-2">{{ producto.fecha_cosecha || 'N/A' }}</span>
            </div>
          </div>

          <VCardActions class="pa-0 mt-6 gap-3 align-center">
            <AppTextField
              v-model.number="quantity"
              label="Cantidad"
              type="number"
              :min="1"
              :max="producto.stock_actual || 99"
              density="compact"
              style="max-width: 120px"
            />

            <VBtn
              color="success"
              variant="elevated"
              size="large"
              prepend-icon="tabler-shopping-cart-plus"
              @click="handleAddToCart"
            >
              Añadir al Carrito
            </VBtn>

            <VBtn
              color="success"
              variant="tonal"
              size="large"
              prepend-icon="tabler-brand-whatsapp"
              @click="contactarWhatsapp(producto.id)"
            >
              Contactar por WhatsApp
            </VBtn>
          </VCardActions>
        </VCol>
      </VRow>

      <VDivider class="my-6" />

      <h3 class="text-h5 mb-4">
        Productos Relacionados
      </h3>

      <VRow>
        <VCol
          v-for="rel in relacionados"
          :key="rel.id"
          cols="12"
          sm="6"
          md="4"
        >
          <VCard
            class="hoverable"
            @click="router.push(`/apps/consumidor/detalle-producto/${rel.id}`)"
          >
            <VImg
              :src="rel.imagen_url"
              height="150"
              cover
            />
            <VCardText>
              <h5 class="text-h6">
                {{ rel.nombre }}
              </h5>
              <span class="text-primary">Q{{ rel.precio_referencia }}</span>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VCard>
  </VContainer>
</template>
