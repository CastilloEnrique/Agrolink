<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/axios'
import { useRouter } from 'vue-router'

// 💡 Importación de VDataTableServer (Asumiendo que ya resolviste el problema de la ruta/Vite)
// Si el error persiste, tu plantilla usa un componente global, solo remueve la línea de import.

// Define la página y sus permisos
definePage({
  meta: {
    action: 'read',
    subject: 'Productor-Productos',
  },
})

const router = useRouter()

// --- Estado de la Tabla y Filtros ---
const misProductos = ref([])
const categorias = ref([])
const isLoading = ref(true)
const errorCarga = ref(null)
const searchQuery = ref('')
const selectedStatus = ref(null)
const selectedCategory = ref(null)

// Opciones fijas para el filtro de estado
const statusOptions = [
  { title: 'Aprobado', value: 'aprobado' },
  { title: 'Pendiente Revisión', value: 'pendiente_revision' },
  { title: 'Rechazado', value: 'rechazado' },
]

// --- Paginación (Básica) ---
const itemsPerPage = ref(10)
const totalItems = ref(0)
const currentPage = ref(1)

// --- Diálogo de Eliminación ---
const dialogEliminar = ref(false)
const productoParaEliminar = ref(null)
const isDeleting = ref(false)

// --- Cabeceras para VDataTableServer ---
const headers = [
  { title: 'Foto', key: 'primera_imagen', sortable: false, align: 'center' },
  { title: 'Producto', key: 'nombre', sortable: true },
  { title: 'Categoría', key: 'categoria_nombre', sortable: true },
  { title: 'Estado', key: 'estado_publicacion', sortable: true, align: 'center' },
  { title: 'Precio (Q)', key: 'precio_referencia', sortable: true, align: 'center' },
  { title: 'Stock', key: 'stock_actual', sortable: true, align: 'center' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'center' },
]

// --- Carga de Datos Inicial ---
const fetchData = async () => {
  isLoading.value = true
  errorCarga.value = null
  try {
    // Cargar Productos
    const responseProductos = await api.get('/productor/mis-productos')

    misProductos.value = responseProductos.data
    totalItems.value = misProductos.value.length

    // Cargar Categorías (para el filtro)
    const responseCategorias = await api.get('/categorias-producto')

    categorias.value = responseCategorias.data
  } catch (error) {
    console.error('Error al cargar datos:', error)
    errorCarga.value = 'No se pudieron cargar los datos. Intenta de nuevo.'
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchData) // Llamar a la función de carga

// --- Lógica de Filtro y Búsqueda (Frontend) ---
const filteredProductos = computed(() => {
  let productos = misProductos.value

  // Filtrar por búsqueda
  if (searchQuery.value) {
    productos = productos.filter(p =>
      p.nombre.toLowerCase().includes(searchQuery.value.toLowerCase()),
    )
  }

  // Filtrar por estado
  if (selectedStatus.value) {
    productos = productos.filter(p => p.estado_publicacion === selectedStatus.value)
  }

  // Filtrar por categoría
  if (selectedCategory.value) {
    productos = productos.filter(p => p.categoria_id === selectedCategory.value)
  }

  // eslint-disable-next-line vue/no-side-effects-in-computed-properties
  totalItems.value = productos.length

  // Simular paginación
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value

  return productos.slice(start, end)
})


// --- Funciones de Utilidad y Acciones ---
const resolveEstadoChip = estado => {
  if (estado === 'aprobado') return 'success'
  if (estado === 'pendiente_revision') return 'warning'
  if (estado === 'rechazado') return 'error'

  return 'secondary'
}

// 💡 FUNCIÓN DE EDITAR (YA DEBE LLEVAR AL FORMULARIO)
const editarProducto = productoId => {
  // Esta ruta ya debe funcionar porque creaste el archivo editar-producto.vue
  router.push({ name: 'apps-productor-editar-id', params: { id: productoId } })
}

const prepararEliminar = producto => {
  productoParaEliminar.value = producto
  dialogEliminar.value = true
}

const cerrarDialogo = () => {
  productoParaEliminar.value = null
  dialogEliminar.value = false
}

const confirmarEliminar = async () => {
  if (!productoParaEliminar.value) return
  isDeleting.value = true
  try {
    await api.delete(`/productor/productos/${productoParaEliminar.value.id}`)
    await fetchData()
    cerrarDialogo()
  } catch (error) {
    console.error('Error al eliminar el producto:', error)
    alert('Error al eliminar el producto. Inténtalo de nuevo.')
  } finally {
    isDeleting.value = false
  }
}

const goToAddProduct = () => {
  router.push({ name: 'apps-productor-publicar' })
}

// 💡 FUNCIONES DE EXPORTACIÓN FINALIZADAS 💡
const exportarReporte = async tipo => {
  isLoading.value = true
  try {
    const url = `/productor/exportar/${tipo}`

    const response = await api.get(url, {
      responseType: 'blob', // 👈 Indispensable para manejar la descarga
    })

    // Crear un link y hacer clic para iniciar la descarga
    const blob = new Blob([response.data], { type: response.headers['content-type'] })
    const link = document.createElement('a')

    link.href = window.URL.createObjectURL(blob)
    link.download = tipo === 'excel' ? 'Productos_Export.xlsx' : 'Productos_Export.pdf'
    link.click()

  } catch (error) {
    console.error(`Error al exportar a ${tipo}:`, error)
    alert(`Error al generar el archivo ${tipo.toUpperCase()}.`)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Mis Productos">
        <VCardText>
          <VRow class="mb-4">
            <VCol
              cols="12"
              md="3"
            >
              <AppSelect
                v-model="selectedStatus"
                label="Estado"
                :items="statusOptions"
                item-title="title"
                item-value="value"
                placeholder="Todos"
                clearable
                density="compact"
              />
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <AppSelect
                v-model="selectedCategory"
                label="Categoría"
                :items="categorias"
                item-title="nombre"
                item-value="id"
                placeholder="Todas"
                clearable
                density="compact"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="searchQuery"
                label="Buscar Producto"
                placeholder="Escribe el nombre..."
                prepend-inner-icon="tabler-search"
                clearable
                density="compact"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardText class="d-flex flex-wrap gap-4 align-center">
          <AppSelect
            v-model="itemsPerPage"
            label="Mostrar"
            :items="[10, 25, 50, 100]"
            style="max-width: 6rem;"
            density="compact"
          />

          <VSpacer />

          <span>
            <VBtn
              variant="tonal"
              color="success"
              prepend-icon="tabler-file-export"
              :loading="isLoading"
              @click="exportarReporte('excel')"
            >
              Exportar Excel
            </VBtn>
            <VTooltip
              activator="parent"
              location="bottom"
            >
              Descargar en formato Excel
            </VTooltip>
          </span>

          <span>
            <VBtn
              variant="tonal"
              color="error"
              prepend-icon="tabler-file-type-pdf"
              :loading="isLoading"
              @click="exportarReporte('pdf')"
            >
              Exportar PDF
            </VBtn>
            <VTooltip
              activator="parent"
              location="bottom"
            >
              Descargar en formato PDF
            </VTooltip>
          </span>

          <VBtn
            prepend-icon="tabler-plus"
            @click="goToAddProduct"
          >
            Añadir Producto
          </VBtn>
        </VCardText>

        <VDataTableServer
          v-model:items-per-page="itemsPerPage"
          :headers="headers"
          :items="filteredProductos"
          :items-length="totalItems"
          :loading="isLoading"
          class="text-no-wrap"
          hover
          @update:options="fetchData"
        >
          <template #item.primera_imagen="{ item }">
            <VAvatar
              size="40"
              rounded="lg"
              class="elevation-1 my-2"
              :image="item.primera_imagen || 'https://placehold.co/100x100/EBF5FB/7F8C8D?text=IMG'"
              alt="Foto"
            />
          </template>

          <template #item.nombre="{ item }">
            <div class="d-flex flex-column">
              <span class="font-weight-medium">{{ item.nombre }}</span>
            </div>
          </template>

          <template #item.estado_publicacion="{ item }">
            <VChip
              :color="resolveEstadoChip(item.estado_publicacion)"
              size="small"
              label
            >
              {{ item.estado_publicacion.replace('_', ' ') }}
            </VChip>
          </template>

          <template #item.precio_referencia="{ item }">
            Q{{ item.precio_referencia.toFixed(2) }}
          </template>

          <template #item.stock_actual="{ item }">
            {{ item.stock_actual }} {{ item.unidad_medida }}
          </template>

          <template #item.actions="{ item }">
            <VBtn
              icon
              variant="text"
              size="small"
              color="medium-emphasis"
              @click="editarProducto(item.id)"
            >
              <VIcon
                size="22"
                icon="tabler-edit"
              />
              <VTooltip
                activator="parent"
                location="bottom"
              >
                Editar
              </VTooltip>
            </VBtn>
            <VBtn
              icon
              variant="text"
              size="small"
              color="medium-emphasis"
              @click="prepararEliminar(item)"
            >
              <VIcon
                size="22"
                icon="tabler-trash"
              />
              <VTooltip
                activator="parent"
                location="bottom"
              >
                Eliminar
              </VTooltip>
            </VBtn>
          </template>

          <template #no-data>
            <VAlert
              v-if="!isLoading"
              type="info"
              variant="tonal"
              class="ma-4"
            >
              {{ errorCarga ? errorCarga : 'No se encontraron productos que coincidan con tu búsqueda o filtros.' }}
              <br v-if="!errorCarga">
              <RouterLink
                v-if="!errorCarga"
                :to="{ name: 'apps-productor-publicar' }"
              >
                ¡Publica tu primer producto aquí!
              </RouterLink>
            </VAlert>
          </template>
        </VDataTableServer>
      </VCard>
    </VCol>

    <VDialog
      v-model="dialogEliminar"
      max-width="500px"
      persistent
    >
      <VCard>
        <VCardTitle class="text-h5">
          Confirmar Eliminación
        </VCardTitle>
        <VCardText>
          ¿Estás seguro de que deseas eliminar el producto
          <strong>{{ productoParaEliminar?.nombre }}</strong>?
          <br>
          Esta acción no se puede deshacer.
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="cerrarDialogo"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="error"
            variant="elevated"
            :loading="isDeleting"
            @click="confirmarEliminar"
          >
            Sí, eliminar
          </VBtn>
          <VSpacer />
        </VCardActions>
      </VCard>
    </VDialog>
  </VRow>
</template>

<style lang="scss">
// Estilos opcionales para VDataTableServer si son necesarios
</style>
