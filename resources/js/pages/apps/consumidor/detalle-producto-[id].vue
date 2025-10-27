<!-- <script setup> -->
<!-- import { ref, onMounted } from 'vue' -->
<!-- import { useRoute, useRouter } from 'vue-router' -->
<!-- import api from '@/services/axios' -->

<!-- // 💡 Ya no definimos 'name' ni 'path' aquí. Dejamos que Vue Router lo haga -->
<!-- // basándose en el nombre del archivo, pero la navegación la forzamos por 'path'. -->

<!-- definePage({ -->
<!--  meta: { -->
<!--    layout: 'default', -->
<!--  }, -->
<!-- }) -->

<!-- const route = useRoute() -->
<!-- const router = useRouter() -->
<!-- const producto = ref(null) -->
<!-- const isLoading = ref(true) -->
<!-- const error = ref(null) -->

<!-- // -&#45;&#45; Funcionalidad de Contacto -&#45;&#45; -->
<!-- const contactProducer = producerId => { -->
<!--  alert(`Iniciar contacto directo con Productor ID: ${producerId}. (Mensajería por implementar)`) -->
<!-- } -->

<!-- onMounted(async () => { -->
<!--  // 💡 El ID ahora se busca correctamente desde la URL (path) -->
<!--  const productId = route.params.id -->

<!--  if (!productId) { -->
<!--    error.value = 'ID de producto no encontrado en la URL.' -->
<!--    isLoading.value = false -->
<!--    -->
<!--    return -->
<!--  } -->

<!--  try { -->
<!--    const response = await api.get(`/catalogo/${productId}`) -->

<!--    producto.value = response.data -->

<!--  } catch (err) { -->
<!--    console.error('Error al cargar detalle del producto:', err) -->
<!--    error.value = 'El producto no fue encontrado o no está aprobado.' -->
<!--  } finally { -->
<!--    isLoading.value = false -->
<!--  } -->
<!-- }) -->
<!-- </script> -->

<!-- <template> -->
<!--  <VContainer> -->
<!--    &lt;!&ndash; Muestra mensajes de Error o Carga &ndash;&gt; -->
<!--    <VAlert -->
<!--      v-if="error" -->
<!--      type="error" -->
<!--      variant="tonal" -->
<!--      :title="error" -->
<!--    > -->
<!--      <VBtn -->
<!--        variant="text" -->
<!--        color="error" -->
<!--        class="mt-2" -->
<!--        @click="router.push({ path: '/apps/consumidor/catalogo' })" -->
<!--      > -->
<!--        Volver al Catálogo -->
<!--      </VBtn> -->
<!--    </VAlert> -->

<!--    <VAlert -->
<!--      v-else-if="isLoading" -->
<!--      type="info" -->
<!--      variant="tonal" -->
<!--      title="Cargando detalles del producto..." -->
<!--    /> -->

<!--    &lt;!&ndash; Vista del Producto (Completa) &ndash;&gt; -->
<!--    <VCard -->
<!--      v-else-if="producto" -->
<!--      class="pa-4" -->
<!--    > -->
<!--      <VRow> -->
<!--        &lt;!&ndash; Columna de Imagen (5/12) &ndash;&gt; -->
<!--        <VCol -->
<!--          cols="12" -->
<!--          md="5" -->
<!--        > -->
<!--          <VImg -->
<!--            :src="producto.imagen_url || 'https://placehold.co/800x600/EBF5FB/7F8C8D?text=Producto'" -->
<!--            height="350" -->
<!--            cover -->
<!--            class="rounded-lg elevation-2" -->
<!--          /> -->
<!--        </VCol> -->

<!--        &lt;!&ndash; Columna de Detalles y Acciones (7/12) &ndash;&gt; -->
<!--        <VCol -->
<!--          cols="12" -->
<!--          md="7" -->
<!--        > -->
<!--          <VCardTitle class="text-h3 pa-0 mb-4"> -->
<!--            {{ producto.nombre }} -->
<!--          </VCardTitle> -->

<!--          <VChip -->
<!--            color="info" -->
<!--            class="mb-4" -->
<!--          > -->
<!--            {{ producto.categoria_nombre }} -->
<!--          </VChip> -->

<!--          <h4 class="text-h4 text-primary my-4"> -->
<!--            Q{{ producto.precio_referencia.toFixed(2) }} -->
<!--            <span class="text-h6 text-medium-emphasis">/ {{ producto.unidad_medida }}</span> -->
<!--          </h4> -->

<!--          <p class="text-body-1 mt-4"> -->
<!--            {{ producto.descripcion }} -->
<!--          </p> -->

<!--          <VDivider class="my-4" /> -->

<!--          &lt;!&ndash; Detalle del Productor y Ubicación &ndash;&gt; -->
<!--          <div class="mb-4"> -->
<!--            <h6 class="text-h6 mb-2"> -->
<!--              Detalles del Productor -->
<!--            </h6> -->
<!--            <div class="d-flex align-center mb-1"> -->
<!--              <VIcon -->
<!--                icon="tabler-user" -->
<!--                size="20" -->
<!--                class="me-2" -->
<!--              /> -->
<!--              <span class="text-medium-emphasis">Productor:</span> -->
<!--              <strong class="ms-2">{{ producto.productor }}</strong> -->
<!--            </div> -->
<!--            <div class="d-flex align-center mb-1"> -->
<!--              <VIcon -->
<!--                icon="tabler-map-pin" -->
<!--                size="20" -->
<!--                class="me-2" -->
<!--              /> -->
<!--              <span class="text-medium-emphasis">Ubicación/Zona:</span> -->
<!--              <span class="ms-2">{{ producto.ubicacion_nombre || 'No especificada' }}</span> -->
<!--            </div> -->
<!--            <div class="d-flex align-center"> -->
<!--              <VIcon -->
<!--                icon="tabler-calendar-time" -->
<!--                size="20" -->
<!--                class="me-2" -->
<!--              /> -->
<!--              <span class="text-medium-emphasis">Fecha de Cosecha:</span> -->
<!--              <span class="ms-2">{{ producto.fecha_cosecha || 'N/A' }}</span> -->
<!--            </div> -->
<!--          </div> -->

<!--          &lt;!&ndash; Acciones de Compra y Contacto &ndash;&gt; -->
<!--          <VCardActions class="pa-0 mt-6 gap-3"> -->
<!--            <VBtn -->
<!--              color="success" -->
<!--              variant="elevated" -->
<!--              size="large" -->
<!--              prepend-icon="tabler-shopping-cart-plus" -->
<!--              @click="alert('Añadir al carrito: Próximamente')" -->
<!--            > -->
<!--              Añadir al Carrito -->
<!--            </VBtn> -->
<!--            <VBtn -->
<!--              color="warning" -->
<!--              variant="tonal" -->
<!--              size="large" -->
<!--              prepend-icon="tabler-message" -->
<!--              @click="contactProducer(producto.productor_id)" -->
<!--            > -->
<!--              Contactar -->
<!--            </VBtn> -->
<!--          </VCardActions> -->
<!--        </VCol> -->
<!--      </VRow> -->
<!--    </VCard> -->
<!--  </VContainer> -->
<!-- </template> -->


<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/axios'
import { useCartStore } from '@/stores/useCartStore' // 💡 Importar Pinia Store

// 💡 IMPORTANTE: Usamos el nombre de ruta que coincide con tu archivo.
definePage({
  name: 'apps-consumidor-detalle-producto',
  path: '/apps/consumidor/detalle-producto/:id', // Define la ruta dinámica
  meta: {
    layout: 'default',
  },
})

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore() // 💡 Inicializar el store
const producto = ref(null)
const isLoading = ref(true)
const error = ref(null)
const quantity = ref(1) // 💡 Estado para la cantidad a agregar

// --- Funcionalidad de Carrito ---
const handleAddToCart = () => {
  if (!producto.value) return

  cartStore.addToCart(producto.value, quantity.value)
  alert(`✅ ${producto.value.nombre} (${quantity.value} ${producto.value.unidad_medida}) añadido al carrito!`)
  quantity.value = 1 // Resetear la cantidad
}

// --- Funcionalidad de Contacto ---
const contactProducer = producerId => {
  alert(`Iniciar contacto directo con Productor ID: ${producerId}. (Mensajería por implementar)`)
}

onMounted(async () => {
  const productId = route.params.id

  if (!productId) {
    error.value = 'ID de producto no encontrado en la URL.'
    isLoading.value = false
    
    return
  }

  try {
    const response = await api.get(`/catalogo/${productId}`)

    // Asignar los datos recibidos
    producto.value = response.data

  } catch (err) {
    console.error('Error al cargar detalle del producto:', err)
    error.value = 'El producto no fue encontrado o no está aprobado.'
  } finally {
    isLoading.value = false
  }
})

// 💡 NUEVA FUNCIÓN: Volver al catálogo
const goBackToCatalog = () => {
  router.back()
}
</script>

<template>
  <VContainer>
    <!-- Botón Volver -->
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

    <!-- Muestra mensajes de Error o Carga -->
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
        @click="router.push({ path: '/apps/consumidor/catalogo' })"
      >
        Ir al Catálogo Principal
      </VBtn>
    </VAlert>

    <VAlert
      v-else-if="isLoading"
      type="info"
      variant="tonal"
      title="Cargando detalles del producto..."
    />

    <!-- Vista del Producto (Completa) -->
    <VCard
      v-else-if="producto"
      class="pa-4"
    >
      <VRow>
        <!-- Columna de Imagen (5/12) -->
        <VCol
          cols="12"
          md="5"
        >
          <VImg
            :src="producto.imagen_url || 'https://placehold.co/800x600/EBF5FB/7F8C8D?text=Producto'"
            height="350"
            cover
            class="rounded-lg elevation-2"
          />
        </VCol>

        <!-- Columna de Detalles y Acciones (7/12) -->
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

          <!-- Detalle del Productor y Ubicación -->
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
              <span class="text-medium-emphasis">Ubicación/Zona:</span>
              <span class="ms-2">{{ producto.ubicacion_nombre || 'No especificada' }}</span>
            </div>
            <div class="d-flex align-center">
              <VIcon
                icon="tabler-calendar-time"
                size="20"
                class="me-2"
              />
              <span class="text-medium-emphasis">Fecha de Cosecha:</span>
              <span class="ms-2">{{ producto.fecha_cosecha || 'N/A' }}</span>
            </div>
          </div>

          <!-- Acciones de Compra y Contacto -->
          <VCardActions class="pa-0 mt-6 gap-3 align-center">
            <!-- Campo de Cantidad -->
            <AppTextField
              v-model.number="quantity"
              label="Cantidad"
              type="number"
              :min="1"
              :max="producto.stock_actual || 99"
              style="max-width: 120px;"
              density="compact"
              :rules="[v => v >= 1 || 'Mínimo 1']"
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
              color="warning"
              variant="tonal"
              size="large"
              icon="tabler-message"
              @click="contactProducer(producto.productor_id)"
            />
          </VCardActions>
        </VCol>
      </VRow>
    </VCard>
  </VContainer>
</template>
