<script setup>
import api from '@/services/axios'
import { ref, onMounted, watch } from 'vue' // 👈 Importa 'watch'

// 👇 Importa los validadores desde la ruta CORE (¡Esta sí es manual y correcta!)
// eslint-disable-next-line import/extensions
import { requiredValidator, integerValidator } from '@core/utils/validators.js'
// eslint-disable-next-line import/no-unresolved
import AppDateTimePicker from '@core/components/app-form-elements/AppDateTimePicker.vue' // <-- ✅ LÍNEA CORREGIDA
// Define la página y sus permisos
definePage({
  meta: {
    action: 'create', // Solo usuarios que puedan 'crear'
    subject: 'Producto', // en el sujeto 'Producto' (definido en CASL)
  },
})

const form = ref({
  nombre: '',
  descripcion: '',
  // eslint-disable-next-line camelcase
  precio_referencia: null,
  // eslint-disable-next-line camelcase
  unidad_medida: '',
  // eslint-disable-next-line camelcase
  stock_actual: null,
  // eslint-disable-next-line camelcase
  categoria_id: null,
  // eslint-disable-next-line camelcase
  fecha_cosecha: null,
  // eslint-disable-next-line camelcase
  imagenes: [], // 👈 Cambiado a 'imagenes' (plural) y es un array
})

const categorias = ref([])
const imagePreviews = ref([]) // 👈 Array para las vistas previas
const isLoading = ref(false)
const refVForm = ref(null)

// Cargar categorías al iniciar
onMounted(async () => {
  // 💡 Carga dinámica desde la API (como pediste)
  try {
    const response = await api.get('/categorias-producto')

    categorias.value = response.data

    // Si no se cargan categorías, avisa al usuario
    if (response.data.length === 0) {
      console.warn("La API /categorias-producto devolvió 0 categorías. Asegúrate de que la tabla `categorias_producto` tenga datos.")

      // Reemplazamos alert con console.warn para no ser intrusivos
    }
  } catch (error) {
    console.error('Error al cargar categorías:', error)

    // No usamos alert aquí para no bloquear al usuario si falla la carga
  }
})

// --- 💡 Watcher para Vistas Previas de Imágenes (Sin cambios, ya funciona bien) ---
watch(() => form.value.imagenes, newFiles => {
  imagePreviews.value = [] // Limpiar vistas previas anteriores

  if (!newFiles || newFiles.length === 0) {
    return
  }

  // Generar una URL de vista previa para cada archivo
  newFiles.forEach(file => {
    if (file instanceof File) {
      const reader = new FileReader()

      reader.onload = e => {
        imagePreviews.value.push(e.target.result) // Añadir la URL base64 al array
      }
      reader.readAsDataURL(file)
    }
  })
})

// 💡 --- NUEVO: Función para eliminar una imagen desde la vista previa ---
const removeImage = index => {
  // 1. Creamos una copia del array de archivos (inmutable)
  const newImages = [...form.value.imagenes]

  // 2. Eliminamos el archivo en el índice específico
  newImages.splice(index, 1)

  // 3. Re-asignamos el array. Esto disparará el 'watch' de arriba
  // y las vistas previas se reconstruirán automáticamente.
  form.value.imagenes = newImages
}

const publicarProducto = async () => {
  const { valid } = await refVForm.value.validate()
  if (!valid) return

  // 💡 Modificación: Revisar si el array está vacío después de que el usuario pudo haber eliminado fotos
  if (!form.value.imagenes || form.value.imagenes.length === 0) {
    alert('Debe subir al menos una foto del producto.')

    return
  }

  const formData = new FormData()

  formData.append('nombre', form.value.nombre)
  formData.append('descripcion', form.value.descripcion || '')
  formData.append('precio_referencia', form.value.precio_referencia)
  formData.append('unidad_medida', form.value.unidad_medida || '')
  formData.append('stock_actual', form.value.stock_actual)
  formData.append('categoria_id', form.value.categoria_id)
  formData.append('fecha_cosecha', form.value.fecha_cosecha || '')

  // --- Lógica de Múltiples Imágenes (Sin cambios) ---
  if (form.value.imagenes && form.value.imagenes.length > 0) {
    form.value.imagenes.forEach(file => {
      formData.append('imagenes[]', file)
    })
  }

  // La validación de arriba ya cubre el 'else'

  // --- Fin Lógica Múltiples Imágenes ---

  isLoading.value = true
  try {
    await api.post('/productor/productos', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    alert('✅ Producto publicado y enviado a revisión exitosamente.')
    refVForm.value.reset()
    form.value.imagenes = [] // Limpiar el array de imágenes
    imagePreviews.value = [] // Limpiar las vistas previas
  } catch (error) {
    console.error('Error al publicar producto:', error.response)

    const message = error.response?.data?.message || 'Error desconocido al publicar.'

    alert(`❌ Error al publicar: ${message}`)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VCard title="Publicar Nuevo Producto">
    <VCardText>
      <VForm
        ref="refVForm"
        @submit.prevent="publicarProducto"
      >
        <VRow>
          <!-- ... (Resto del formulario: Nombre, Categoría, Precio, etc. Sin cambios) ... -->
          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.nombre"
              label="Nombre del Producto"
              :rules="[requiredValidator]"
              placeholder="Ej: Maíz Blanco Orgánico"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppSelect
              v-model="form.categoria_id"
              label="Categoría"
              :items="categorias"
              item-title="nombre"
              item-value="id"
              :rules="[requiredValidator]"
              placeholder="Selecciona la categoría"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.precio_referencia"
              label="Precio de Referencia"
              type="number"
              step="0.01"
              prefix="Q"
              :rules="[requiredValidator, v => v > 0 || 'El precio debe ser mayor a cero']"
              placeholder="0.00"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.unidad_medida"
              label="Unidad de Medida"
              :rules="[requiredValidator]"
              placeholder="Ej: Libra, Quintal, Docena"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.stock_actual"
              label="Stock Disponible"
              type="number"
              :rules="[requiredValidator, integerValidator, v => v >= 0 || 'El stock no puede ser negativo']"
              placeholder="0"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppDateTimePicker
              v-model="form.fecha_cosecha"
              label="Fecha de Cosecha (Opcional)"
              placeholder="Selecciona la fecha"
              clearable
              :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', maxDate: 'today' }"
            />
          </VCol>

          <VCol cols="12">
            <AppTextarea
              v-model="form.descripcion"
              label="Descripción (Máx. 500 caracteres)"
              rows="3"
              counter="500"
              :rules="[v => (!v || v.length <= 500) || 'Máximo 500 caracteres']"
            />
          </VCol>

          <VCol cols="12">
            <VFileInput
              v-model="form.imagenes"
              label="Fotos del Producto (La primera será la principal)"
              accept="image/*"
              :rules="[requiredValidator]"
              prepend-icon="tabler-camera"
              multiple
              clearable
            />
          </VCol>

          <!-- 💡 --- CONTENEDOR DE VISTAS PREVIAS (MODIFICADO) --- -->
          <VCol
            v-if="imagePreviews.length > 0"
            cols="12"
          >
            <VLabel class="mb-2">
              Vistas Previas:
            </VLabel>
            <VRow>
              <!-- 💡 CAMBIO: El v-for ahora usa (src, i) para obtener el índice -->
              <VCol
                v-for="(src, i) in imagePreviews"
                :key="i"
                cols="4"
                sm="3"
                md="2"
              >
                <!-- 💡 NUEVO: Contenedor relativo para el botón -->
                <div class="preview-container">
                  <VImg
                    :src="src"
                    aspect-ratio="1"
                    cover
                    class="rounded border elevation-2"
                  />
                  <!-- 💡 NUEVO: Botón de eliminar -->
                  <VBtn
                    icon
                    size="x-small"
                    color="error"
                    class="preview-remove-btn"
                    @click="removeImage(i)"
                  >
                    <VIcon
                      icon="tabler-x"
                      size="16"
                    />
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VCol>
          <!-- --- Fin Vistas Previas --- -->

          <VCol
            cols="12"
            class="text-center mt-4"
          >
            <VBtn
              :loading="isLoading"
              type="submit"
              color="success"
            >
              Publicar y Enviar a Revisión
            </VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>

<!-- 💡 --- CSS ACTUALIZADO --- -->
<style scoped>
.border {
  border: 1px solid rgba(0,0,0,0.1);
}

/* 💡 NUEVO: Estilos para el botón de eliminar */
.preview-container {
  position: relative;
}

.preview-remove-btn {
  position: absolute;
  top: 4px; /* Pequeño espacio desde arriba */
  right: 4px; /* Pequeño espacio desde la derecha */
  z-index: 10;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3); /* Sombra para que resalte */
}
</style>

