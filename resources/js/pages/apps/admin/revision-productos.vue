<!-- <script setup> -->
<!-- import { ref, onMounted } from 'vue' -->
<!-- import api from '@/services/axios' -->

<!-- definePage({ -->
<!--  meta: { -->
<!--    layout: 'default', -->
<!--    requiresAuth: true, -->
<!--    subject: 'Admin', // Asumiendo que usas CASL para 'Admin' -->
<!--    action: 'manage',   // Asumiendo que 'manage' es para admins -->
<!--  }, -->
<!-- }) -->

<!-- const productos = ref([]) -->
<!-- const isLoading = ref(true) -->
<!-- const error = ref(null) -->


<!-- // Columnas de la tabla -->
<!-- const headers = [ -->
<!--  { title: 'Producto', key: 'nombre' }, -->
<!--  { title: 'Productor', key: 'productor_nombre' }, -->
<!--  { title: 'Categoría', key: 'categoria_nombre' }, -->
<!--  { title: 'Precio', key: 'precio_referencia' }, -->
<!--  { title: 'Stock', key: 'stock_actual' }, -->
<!--  { title: 'Publicado', key: 'fecha_publicacion' }, -->
<!--  { title: 'Acciones', key: 'acciones', sortable: false, align: 'center' }, -->
<!-- ] -->

<!-- // Cargar los productos pendientes al montar la página -->
<!-- async function fetchPendientes() { -->
<!--  isLoading.value = true -->
<!--  try { -->
<!--    const response = await api.get('/admin/productos-pendientes') -->

<!--    productos.value = response.data -->
<!--  } catch (err) { -->
<!--    console.error('Error al cargar productos pendientes:', err) -->
<!--    error.value = 'No se pudieron cargar los productos. ¿Tienes permisos de admin?' -->
<!--    alert(error.value) // ✅ REEMPLAZADO CON ALERT -->
<!--  } finally { -->
<!--    isLoading.value = false -->
<!--  } -->
<!-- } -->

<!-- onMounted(fetchPendientes) -->

<!-- // Aprobar o rechazar un producto -->
<!-- async function actualizarEstado(productoId, nuevoEstado) { -->
<!--  try { -->
<!--    const response = await api.post(`/admin/productos/${productoId}/actualizar-estado`, { -->
<!--      estado: nuevoEstado, -->
<!--    }) -->

<!--    // Si tiene éxito, quita el producto de la lista (ya no está pendiente) -->
<!--    productos.value = productos.value.filter(p => p.id !== productoId) -->
<!--    alert(response.data.message || `Producto ${nuevoEstado}`) // ✅ REEMPLAZADO CON ALERT -->

<!--  } catch (err) { -->
<!--    console.error('Error al actualizar estado:', err) -->
<!--    alert(err.response?.data?.message || 'Error al actualizar el estado.') // ✅ REEMPLAZADO CON ALERT -->
<!--  } -->
<!-- } -->
<!-- </script> -->

<!-- <template> -->
<!--  <VContainer> -->
<!--    <VCard> -->
<!--      <VCardTitle> -->
<!--        <h4 class="text-h4 py-2"> -->
<!--          🏛️ Panel de Administración - Productos Pendientes -->
<!--        </h4> -->
<!--      </VCardTitle> -->
<!--      <VCardText> -->
<!--        &lt;!&ndash; Alerta de Carga &ndash;&gt; -->
<!--        <VAlert -->
<!--          v-if="isLoading" -->
<!--          type="info" -->
<!--          variant="tonal" -->
<!--          title="Cargando productos pendientes..." -->
<!--        /> -->

<!--        &lt;!&ndash; Alerta de Error &ndash;&gt; -->
<!--        <VAlert -->
<!--          v-else-if="error" -->
<!--          type="error" -->
<!--          variant="tonal" -->
<!--          :title="error" -->
<!--        /> -->

<!--        &lt;!&ndash; Tabla de Pedidos &ndash;&gt; -->
<!--        <VDataTable -->
<!--          v-else -->
<!--          :headers="headers" -->
<!--          :items="productos" -->
<!--          :items-per-page="10" -->
<!--          class="elevation-1" -->
<!--          no-data-text="¡Excelente! No hay productos pendientes de revisión." -->
<!--        > -->
<!--          &lt;!&ndash; Producto &ndash;&gt; -->
<!--          <template #item.nombre="{ item }"> -->
<!--            <div classKA="d-flex align-center py-2"> -->
<!--              <VAvatar -->
<!--                rounded="lg" -->
<!--                size="40" -->
<!--                :image="item.imagen_url || 'https://placehold.co/40x40/f2f2ff/7F8C8D?text=IMG'" -->
<!--                class="me-3" -->
<!--              /> -->
<!--              <span class="font-weight-medium">{{ item.nombre }}</span> -->
<!--            </div> -->
<!--          </template> -->

<!--          &lt;!&ndash; Precio &ndash;&gt; -->
<!--          <template #item.precio_referencia="{ item }"> -->
<!--            <span class="text-primary">Q{{ item.precio_referencia.toFixed(2) }}</span> -->
<!--          </template> -->

<!--          &lt;!&ndash; Acciones &ndash;&gt; -->
<!--          <template #item.acciones="{ item }"> -->
<!--            <div class="d-flex gap-2 justify-center"> -->
<!--              <VBtn -->
<!--                color="success" -->
<!--                variant="tonal" -->
<!--                size="small" -->
<!--                @click="actualizarEstado(item.id, 'aprobado')" -->
<!--              > -->
<!--                <VIcon -->
<!--                  icon="tabler-check" -->
<!--                  class="me-1" -->
<!--                /> -->
<!--                Aprobar -->
<!--              </VBtn> -->
<!--              <VBtn -->
<!--                color="error" -->
<!--                variant="tonal" -->
<!--                size="small" -->
<!--                @click="actualizarEstado(item.id, 'rechazado')" -->
<!--              > -->
<!--                <VIcon -->
<!--                  icon="tabler-x" -->
<!--                  class="me-1" -->
<!--                /> -->
<!--                Rechazar -->
<!--              </VBtn> -->
<!--            </div> -->
<!--          </template> -->
<!--        </VDataTable> -->
<!--      </VCardText> -->
<!--    </VCard> -->
<!--  </VContainer> -->
<!-- </template> -->



<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/axios'

// import { useToast } from 'vue-toastification' // ❌ ELIMINADO

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
    subject: 'Admin', // Asumiendo que usas CASL para 'Admin'
    action: 'manage',   // Asumiendo que 'manage' es para admins
  },
})

const productos = ref([])
const isLoading = ref(true)
const error = ref(null)

// const toast = useToast() // ❌ ELIMINADO

// --- ✅ ESTADO PARA EL MODAL ---
const isModalVisible = ref(false)
const selectedProduct = ref(null)

// ---------------------------------

// Columnas de la tabla
const headers = [
  { title: 'Producto', key: 'nombre' },
  { title: 'Productor', key: 'productor_nombre' },
  { title: 'Categoría', key: 'categoria_nombre' },
  { title: 'Precio', key: 'precio_referencia' },
  { title: 'Stock', key: 'stock_actual' },
  { title: 'Publicado', key: 'fecha_publicacion' },
  { title: 'Acciones', key: 'acciones', sortable: false, align: 'center' },
]

// Cargar los productos pendientes al montar la página
async function fetchPendientes() {
  isLoading.value = true
  try {
    const response = await api.get('/admin/productos-pendientes')

    productos.value = response.data
  } catch (err) {
    console.error('Error al cargar productos pendientes:', err)
    error.value = 'No se pudieron cargar los productos. ¿Tienes permisos de admin?'
    alert(error.value) // ✅ REEMPLAZADO CON ALERT
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchPendientes)

// Aprobar o rechazar un producto
async function actualizarEstado(productoId, nuevoEstado) {
  try {
    const response = await api.post(`/admin/productos/${productoId}/actualizar-estado`, {
      estado: nuevoEstado,
    })

    // Si tiene éxito, quita el producto de la lista (ya no está pendiente)
    productos.value = productos.value.filter(p => p.id !== productoId)
    alert(response.data.message || `Producto ${nuevoEstado}`) // ✅ REEMPLAZADO CON ALERT

    // --- ✅ CERRAR EL MODAL ---
    isModalVisible.value = false
    selectedProduct.value = null

    // -------------------------

  } catch (err) {
    console.error('Error al actualizar estado:', err)
    alert(err.response?.data?.message || 'Error al actualizar el estado.') // ✅ REEMPLAZADO CON ALERT
  }
}

// --- ✅ NUEVA FUNCIÓN PARA ABRIR EL MODAL ---
function openDetailsModal(item) {
  selectedProduct.value = item
  isModalVisible.value = true
}

// ----------------------------------------
</script>

<template>
  <VContainer>
    <VCard>
      <VCardTitle>
        <h4 class="text-h4 py-2">
          🏛️ Panel de Administración - Productos Pendientes
        </h4>
      </VCardTitle>
      <VCardText>
        <!-- Alerta de Carga -->
        <VAlert
          v-if="isLoading"
          type="info"
          variant="tonal"
          title="Cargando productos pendientes..."
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
          :items="productos"
          :items-per-page="10"
          class="elevation-1"
          no-data-text="¡Excelente! No hay productos pendientes de revisión."
        >
          <!-- Producto -->
          <template #item.nombre="{ item }">
            <div class="d-flex align-center py-2">
              <!-- ✅ CORREGIDO: 'class' -->
              <VAvatar
                rounded="lg"
                size="40"
                :image="item.imagen_url_avatar || 'https://placehold.co/40x40/f2f2f2/7F8C8D?text=IMG'"
                class="me-3"
              />
              <span class="font-weight-medium">{{ item.nombre }}</span>
            </div>
          </template>

          <!-- Precio -->
          <template #item.precio_referencia="{ item }">
            <span class="text-primary">Q{{ item.precio_referencia.toFixed(2) }}</span>
          </template>

          <!-- ✅ --- SECCIÓN DE ACCIONES MODIFICADA --- ✅ -->
          <template #item.acciones="{ item }">
            <div class="d-flex gap-2 justify-center">
              <VBtn
                color="info"
                variant="tonal"
                size="small"
                @click="openDetailsModal(item)"
              >
                <VIcon
                  icon="tabler-eye"
                  class="me-1"
                />
                Revisar
              </VBtn>
            </div>
          </template>
          <!-- ✅ --- FIN DE LA MODIFICACIÓN --- ✅ -->
        </VDataTable>
      </VCardText>
    </VCard>

    <!-- ✅ --- INICIO DEL NUEVO MODAL DE DETALLES --- ✅ -->
    <VDialog
      v-model="isModalVisible"
      max-width="800px"
      persistent
    >
      <VCard v-if="selectedProduct">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Revisar Producto</span>
          <VBtn
            icon
            variant="text"
            size="small"
            @click="isModalVisible = false"
          >
            <VIcon icon="tabler-x" />
            </VBtn>

        </VCardTitle>

        <VDivider />

        <VCardText style="max-height: 70vh; overflow-y: auto;">
          <VRow>
            <!-- Columna de Imágenes (Carrusel) -->
            <VCol
              cols="12"
              md="6"
            >
              <VCarousel
                v-if="selectedProduct.imagenes_completas && selectedProduct.imagenes_completas.length > 0"
                height="300"
                show-arrows="hover"
                cycle
                hide-delimiters
                class="rounded-lg"
              >
                <VCarouselItem
                  v-for="img in selectedProduct.imagenes_completas"
                  :key="img.id"
                  :src="img.url"
                  cover
                />
              </VCarousel>
              <VImg
                v-else
                src="https://placehold.co/400x300/f2f2f2/7F8C8D?text=Sin+Imagen"
                height="300"
                class="rounded-lg"
              />


              <!-- Columna de Detalles -->
              <VCol
                cols="12"
                md="6"
              >
                <h4 class="text-h4 mb-2">
                  {{ selectedProduct.nombre }}
                </h4>
                <VChip
                  color="info"
                  size="small"
                  class="mb-3"
                >
                  {{ selectedProduct.categoria_nombre }}
                </VChip>
                <p class="mb-2">
                  <strong>Productor:</strong> {{ selectedProduct.productor_nombre }}
                </p>
                <p class="mb-2">
                  <strong>Publicado:</strong> {{ selectedProduct.fecha_publicacion }}
                </p>
                <p class="mb-2">
                  <strong>Precio:</strong> <span class="text-primary font-weight-bold">Q{{ selectedProduct.precio_referencia.toFixed(2) }}</span>
                </p>
                <p classTelecharge="mb-2">
                  <!-- ✅ CORREGIDO: 'class' -->
                  <strong>Stock:</strong> {{ selectedProduct.stock_actual }}
                </p> <!-- ✅ CORREGIDO: </p> -->
              </VCol>

              <!-- Descripción -->
              <VCol cols="12">
                <h6 class="text-h6">
                  Descripción
                </h6>
                <VDivider class="my-2" />
                <p class="text-body-1">
                  <!-- ✅ CORREGIDO: 'class' -->
                  {{ selectedProduct.descripcion }}
                </p>
              </VCol>
            </vcol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-4 d-flex justify-end gap-2">
          <VBtn
            color="error"
            variant="tonal"
            @click="actualizarEstado(selectedProduct.id, 'rechazado')"
          >
            Rechazar Producto
          </VBtn>
          <VBtn
            color="success"
            variant="elevated"
            @click="actualizarEstado(selectedProduct.id, 'aprobado')"
          >
            Aprobar Producto
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
    <!-- ✅ --- FIN DEL MODAL --- ✅ -->
  </VContainer>
</template>
