<!-- <script setup> -->
<!-- import api from '@/services/axios' -->
<!-- import { ref, onMounted } from 'vue' -->
<!-- // eslint-disable-next-line import/extensions -->
<!-- import { requiredValidator } from '@core/utils/validators.js' -->

<!-- // Define la página y sus permisos -->
<!-- definePage({ -->
<!--  meta: { -->
<!--    action: 'manage', // Usamos 'manage' o una acción que defina "gestionar su propio perfil" -->
<!--    subject: 'Productor-Perfil', // Un nuevo sujeto para el perfil -->
<!--  }, -->
<!-- }) -->

<!-- // Formulario simplificado (sin aldea_id) -->
<!-- const form = ref({ -->
<!--  whatsapp: '', -->
<!--  direccion: '', -->
<!--  // eslint-disable-next-line camelcase -->
<!--  ubicacion_lat: null, -->
<!--  // eslint-disable-next-line camelcase -->
<!--  ubicacion_lng: null, -->
<!-- }) -->

<!-- const isLoading = ref(false) -->
<!-- const isProfileLoaded = ref(false) -->
<!-- const refVForm = ref(null) -->

<!-- // -&#45;&#45; Cargar Perfil Actual -&#45;&#45; -->
<!-- onMounted(async () => { -->
<!--  isLoading.value = true -->
<!--  try { -->
<!--    // Cargar el perfil existente del productor -->
<!--    // Llama a la ruta GET: /productor/perfil (¡Esta ruta ya la tienes en tu api.php!) -->
<!--    const responseProfile = await api.get('/productor/perfil') -->

<!--    // Si el perfil ya existe, rellena el formulario -->
<!--    if (responseProfile.data) { -->
<!--      // Nos aseguramos de solo rellenar los campos que existen en el formulario -->
<!--      form.value.whatsapp = responseProfile.data.whatsapp || '' -->
<!--      form.value.direccion = responseProfile.data.direccion || '' -->
<!--      // eslint-disable-next-line camelcase -->
<!--      form.value.ubicacion_lat = responseProfile.data.ubicacion_lat || null -->
<!--      // eslint-disable-next-line camelcase -->
<!--      form.value.ubicacion_lng = responseProfile.data.ubicacion_lng || null -->
<!--    } -->
<!--    isProfileLoaded.value = true -->

<!--  } catch (error) { -->
<!--    console.error('Error al cargar datos:', error) -->
<!--    alert('No se pudo cargar la información del perfil.') -->
<!--  } finally { -->
<!--    isLoading.value = false -->
<!--  } -->
<!-- }) -->

<!-- // -&#45;&#45; Guardar Perfil -&#45;&#45; -->
<!-- const guardarPerfil = async () => { -->
<!--  const { valid } = await refVForm.value.validate() -->
<!--  if (!valid) return -->

<!--  isLoading.value = true -->
<!--  try { -->
<!--    // Llama a la ruta POST: /productor/perfil (¡Esta ruta ya la tienes en tu api.php!) -->
<!--    await api.post('/productor/perfil', form.value) -->

<!--    alert('✅ Perfil guardado exitosamente.') -->

<!--  } catch (error) { -->
<!--    console.error('Error al guardar perfil:', error.response) -->

<!--    const message = error.response?.data?.message || 'Error desconocido al guardar.' -->

<!--    alert(`❌ Error al guardar: ${message}`) -->
<!--  } finally { -->
<!--    isLoading.value = false -->
<!--  } -->
<!-- } -->
<!-- </script> -->

<!-- <template> -->
<!--  <VCard title="Mi Perfil de Productor"> -->
<!--    <VCardText v-if="!isProfileLoaded"> -->
<!--      <VAlert -->
<!--        type="info" -->
<!--        variant="tonal" -->
<!--      > -->
<!--        Cargando información del perfil... -->
<!--      </VAlert> -->
<!--    </VCardText> -->

<!--    <VCardText v-if="isProfileLoaded"> -->
<!--      <VForm -->
<!--        ref="refVForm" -->
<!--        @submit.prevent="guardarPerfil" -->
<!--      > -->
<!--        <VRow> -->
<!--          &lt;!&ndash; Campo de WhatsApp &ndash;&gt; -->
<!--          <VCol -->
<!--            cols="12" -->
<!--            md="6" -->
<!--          > -->
<!--            <AppTextField -->
<!--              v-model="form.whatsapp" -->
<!--              label="Número de WhatsApp" -->
<!--              :rules="[requiredValidator]" -->
<!--              placeholder="Ej: +502 12345678" -->
<!--              prepend-inner-icon="tabler-brand-whatsapp" -->
<!--            /> -->
<!--          </VCol> -->

<!--          &lt;!&ndash; Campo de Dirección (Simplificado) &ndash;&gt; -->
<!--          <VCol -->
<!--            cols="12" -->
<!--            md="6" -->
<!--          > -->
<!--            <AppTextField -->
<!--              v-model="form.direccion" -->
<!--              label="Dirección o Referencia" -->
<!--              placeholder="Ej: Finca La Esperanza, sector 2" -->
<!--            /> -->
<!--          </VCol> -->

<!--          &lt;!&ndash; Campo de Descripción/Dirección Larga &ndash;&gt; -->
<!--          <VCol cols="12"> -->
<!--            <AppTextarea -->
<!--              v-model="form.direccion" -->
<!--              label="Dirección o Referencia (más detalles)" -->
<!--              rows="3" -->
<!--              placeholder="Ej: Finca La Esperanza, sector 2, calle principal" -->
<!--            /> -->
<!--          </VCol> -->

<!--          &lt;!&ndash; Campos de Ubicación &ndash;&gt; -->
<!--          <VCol -->
<!--            cols="12" -->
<!--            md="6" -->
<!--          > -->
<!--            <AppTextField -->
<!--              v-model.number="form.ubicacion_lat" -->
<!--              label="Ubicación (Latitud)" -->
<!--              type="number" -->
<!--              step="any" -->
<!--              placeholder="Ej: 14.6349" -->
<!--            /> -->
<!--          </VCol> -->
<!--          <VCol -->
<!--            cols="12" -->
<!--            md="6" -->
<!--          > -->
<!--            <AppTextField -->
<!--              v-model.number="form.ubicacion_lng" -->
<!--              label="Ubicación (Longitud)" -->
<!--              type="number" -->
<!--              step="any" -->
<!--              placeholder="Ej: -90.5069" -->
<!--            /> -->
<!--            </VCol> -->
<!--            <VCol -->
<!--              cols="12" -->
<!--              class="text-caption" -->
<!--            > -->
<!--              (Opcional: Puedes obtener tu latitud y longitud desde Google Maps) -->
<!--            </VCol> -->

<!--            &lt;!&ndash; Botón de Guardar &ndash;&gt; -->
<!--            <VCol -->
<!--              cols="12" -->
<!--              class="text-center mt-4" -->
<!--            > -->
<!--              <VBtn -->
<!--                :loading="isLoading" -->
<!--                type="submit" -->
<!--                color="primary" -->
<!--              > -->
<!--                Guardar Perfil -->
<!--              </VBtn> -->
<!--            </VCol> -->
<!--        </VRow> -->
<!--      </VForm> -->
<!--    </VCardText> -->
<!--  </VCard> -->
<!-- </template> -->

<script setup>
import api from '@/services/axios'
import { ref, onMounted, watch } from 'vue'
// eslint-disable-next-line import/extensions
import { requiredValidator, emailValidator } from '@core/utils/validators.js'
// eslint-disable-next-line import/no-unresolved
import AppDateTimePicker from '@core/components/app-form-elements/AppDateTimePicker.vue'

// Define la página y sus permisos
definePage({
  meta: {
    action: 'manage',
    subject: 'Productor-Perfil',
  },
})

// --- 💡 Un solo formulario para TODOS los datos ---
const form = ref({
  // Campos de Usuario (users)
  // eslint-disable-next-line camelcase
  primer_nombre: '',
  // eslint-disable-next-line camelcase
  segundo_nombre: '',
  // eslint-disable-next-line camelcase
  primer_apellido: '',
  // eslint-disable-next-line camelcase
  segundo_apellido: '',
  dpi: '',
  nit: '',
  // eslint-disable-next-line camelcase
  fecha_nacimiento: null,
  correo: '', // Correo (generalmente no editable)
  // eslint-disable-next-line camelcase
  pais_id: null,
  // eslint-disable-next-line camelcase
  departamento_id: null,
  // eslint-disable-next-line camelcase
  municipio_id: null,
  // eslint-disable-next-line camelcase
  aldea_id: null,

  // Campos de PerfilProductor (perfil_productor)
  whatsapp: '',
  direccion: '',
  // eslint-disable-next-line camelcase
  ubicacion_lat: null,
  // eslint-disable-next-line camelcase
  ubicacion_lng: null,
})

// Refs para los selectores de geografía
const paises = ref([])
const departamentos = ref([])
const municipios = ref([])
const aldeas = ref([])
const isLoadingPaises = ref(false)
const isLoadingDepartamentos = ref(false)
const isLoadingMunicipios = ref(false)
const isLoadingAldeas = ref(false)

const isLoading = ref(false)
const isProfileLoaded = ref(false)
const refVForm = ref(null)

// --- Cargar Perfil Completo y Geografía ---
onMounted(async () => {
  isLoading.value = true
  isProfileLoaded.value = false
  try {
    // --- 💡 Llama a la NUEVA ruta que trae TODO ---
    const response = await api.get('/productor/perfil-completo')

    // Rellena el formulario con todos los datos
    if (response.data) {
      form.value = { ...form.value, ...response.data }
    }

    // --- Carga los selectores de geografía ---
    // (Estas rutas son públicas según tu routes/api.php)
    isLoadingPaises.value = true

    const resPaises = await api.get('/paises')

    paises.value = resPaises.data
    isLoadingPaises.value = false

    // Si ya hay IDs, carga los dependientes
    if (form.value.pais_id) {
      await loadDepartamentos(form.value.pais_id, false)
    }
    if (form.value.departamento_id) {
      await loadMunicipios(form.value.departamento_id, false)
    }
    if (form.value.municipio_id) {
      await loadAldeas(form.value.municipio_id, false)
    }

    isProfileLoaded.value = true
  } catch (error) {
    console.error('Error al cargar datos:', error)
    alert('No se pudo cargar la información del perfil.')
  } finally {
    isLoading.value = false
  }
})

// --- 💡 Funciones para cargar Geografía ---
const loadDepartamentos = async (paisId, resetChildren = true) => {
  if (resetChildren) {
    // eslint-disable-next-line camelcase
    form.value.departamento_id = null
    // eslint-disable-next-line camelcase
    form.value.municipio_id = null
    // eslint-disable-next-line camelcase
    form.value.aldea_id = null
    departamentos.value = []
    municipios.value = []
    aldeas.value = []
  }
  if (paisId) {
    isLoadingDepartamentos.value = true
    try {
      const response = await api.get(`/departamentos/${paisId}`)

      departamentos.value = response.data
    } catch (error) {
      console.error('Error al cargar departamentos:', error)
    } finally {
      isLoadingDepartamentos.value = false
    }
  }
}

const loadMunicipios = async (deptId, resetChildren = true) => {
  if (resetChildren) {
    // eslint-disable-next-line camelcase
    form.value.municipio_id = null
    // eslint-disable-next-line camelcase
    form.value.aldea_id = null
    municipios.value = []
    aldeas.value = []
  }
  if (deptId) {
    isLoadingMunicipios.value = true
    try {
      const response = await api.get(`/municipios/${deptId}`)

      municipios.value = response.data
    } catch (error) {
      console.error('Error al cargar municipios:', error)
    } finally {
      isLoadingMunicipios.value = false
    }
  }
}

const loadAldeas = async (muniId, resetChildren = true) => {
  if (resetChildren) {
    // eslint-disable-next-line camelcase
    form.value.aldea_id = null
    aldeas.value = []
  }
  if (muniId) {
    isLoadingAldeas.value = true
    try {
      const response = await api.get(`/aldeas/${muniId}`)

      aldeas.value = response.data
    } catch (error) {
      console.error('Error al cargar aldeas:', error)
    } finally {
      isLoadingAldeas.value = false
    }
  }
}

// --- Watchers para Geografía (llaman a las funciones de carga) ---
watch(() => form.value.pais_id, newVal => loadDepartamentos(newVal))
watch(() => form.value.departamento_id, newVal => loadMunicipios(newVal))
watch(() => form.value.municipio_id, newVal => loadAldeas(newVal))


// --- Obtener Ubicación GPS ---
const getBrowserLocation = () => {
  if (!navigator.geolocation) {
    alert('Tu navegador no soporta la geolocalización.')

    return
  }
  isLoading.value = true
  navigator.geolocation.getCurrentPosition(
    position => {
      // eslint-disable-next-line camelcase
      form.value.ubicacion_lat = position.coords.latitude
      // eslint-disable-next-line camelcase
      form.value.ubicacion_lng = position.coords.longitude
      isLoading.value = false
      alert('¡Ubicación obtenida exitosamente!')
    },
    error => {
      console.error('Error de geolocalización:', error)
      alert(`Error al obtener ubicación: ${error.message}`)
      isLoading.value = false
    },
    { enableHighAccuracy: true },
  )
}

// --- Guardar Perfil Completo ---
const guardarPerfilCompleto = async () => {
  const { valid } = await refVForm.value.validate()
  if (!valid) return

  isLoading.value = true
  try {
    // --- 💡 Llama a la NUEVA ruta que guarda TODO ---
    await api.post('/productor/perfil-completo', form.value)
    alert('✅ Perfil guardado exitosamente.')
  } catch (error) {
    console.error('Error al guardar perfil:', error.response)

    const message = error.response?.data?.message || 'Error desconocido al guardar.'

    alert(`❌ Error al guardar: ${message}`)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VCard title="Mi Perfil Completo">
    <VCardText v-if="!isProfileLoaded">
      <VAlert
        type="info"
        variant="tonal"
      >
        Cargando información del perfil...
      </VAlert>
    </VCardText>

    <VCardText v-if="isProfileLoaded">
      <VForm
        ref="refVForm"
        @submit.prevent="guardarPerfilCompleto"
      >
        <VRow>
          <!-- --- 💡 SECCIÓN 1: DATOS DE USUARIO (de register.vue) --- -->
          <VCol cols="12">
            <h6 class="text-h6">
              Información Personal (de tu Cuenta)
            </h6>
          </VCol>

          <VCol cols="12">
            <AppTextField
              v-model="form.correo"
              :rules="[requiredValidator, emailValidator]"
              label="Correo Electrónico"
              type="email"
              placeholder="tu@correo.com"
              readonly
              disabled
              prepend-inner-icon="tabler-mail"
            />
            <VAlert
              density="compact"
              variant="tonal"
              color="info"
              icon="tabler-info-circle"
              class="mt-2"
            >
              El correo electrónico no se puede modificar.
            </VAlert>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.primer_nombre"
              :rules="[requiredValidator]"
              label="Primer Nombre"
              placeholder="Juan"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.segundo_nombre"
              label="Segundo Nombre"
              placeholder="(Opcional)"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.primer_apellido"
              :rules="[requiredValidator]"
              label="Primer Apellido"
              placeholder="Pérez"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.segundo_apellido"
              label="Segundo Apellido"
              placeholder="(Opcional)"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.dpi"
              label="DPI (Opcional)"
              placeholder="Tu DPI"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.nit"
              label="NIT (Opcional)"
              placeholder="Tu NIT"
            />
          </VCol>

          <VCol cols="12">
            <AppDateTimePicker
              v-model="form.fecha_nacimiento"
              label="Fecha de Nacimiento (Opcional)"
              placeholder="Selecciona tu fecha"
              clearable
              :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', maxDate: 'today' }"
            />
          </VCol>

          <!-- --- 💡 SECCIÓN 2: DATOS DE UBICACIÓN (de register.vue) --- -->
          <VCol cols="12">
            <VDivider class="my-4" />
            <h6 class="text-h6">
              Ubicación Geográfica
            </h6>
          </VCol>

          <VCol cols="12">
            <AppSelect
              v-model="form.pais_id"
              :items="paises"
              :loading="isLoadingPaises"
              :rules="[requiredValidator]"
              item-title="nombre"
              item-value="id"
              label="País"
              placeholder="Selecciona un país"
              clearable
            />
          </VCol>
          <VCol cols="12">
            <AppSelect
              v-model="form.departamento_id"
              :items="departamentos"
              :loading="isLoadingDepartamentos"
              :disabled="!form.pais_id || isLoadingDepartamentos"
              :rules="[requiredValidator]"
              item-title="nombre"
              item-value="id"
              label="Departamento"
              placeholder="Selecciona un departamento"
              clearable
            />
          </VCol>
          <VCol cols="12">
            <AppSelect
              v-model="form.municipio_id"
              :items="municipios"
              :loading="isLoadingMunicipios"
              :disabled="!form.departamento_id || isLoadingMunicipios"
              :rules="[requiredValidator]"
              item-title="nombre"
              item-value="id"
              label="Municipio"
              placeholder="Selecciona un municipio"
              clearable
            />
          </VCol>
          <VCol cols="12">
            <AppSelect
              v-model="form.aldea_id"
              label="Aldea (Opcional)"
              :items="aldeas"
              item-title="nombre"
              item-value="id"
              placeholder="Selecciona una aldea existente"
              :loading="isLoadingAldeas"
              :disabled="!form.municipio_id || isLoadingAldeas"
              clearable
            />
          </VCol>

          <!-- --- 💡 SECCIÓN 3: DATOS DE PRODUCTOR (de perfil.vue) --- -->
          <VCol cols="12">
            <VDivider class="my-4" />
            <h6 class="text-h6">
              Perfil de Productor
            </h6>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.whatsapp"
              label="Número de WhatsApp"
              :rules="[requiredValidator]"
              placeholder="Ej: +502 12345678"
              prepend-inner-icon="tabler-brand-whatsapp"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="form.direccion"
              label="Dirección Específica (Adicional)"
              placeholder="Ej: Lote 5, Casa Azul"
            />
          </VCol>

          <VCol
            cols="12"
            class="d-flex"
          >
            <VBtn
              variant="tonal"
              prepend-icon="tabler-map-pin"
              :loading="isLoading"
              @click="getBrowserLocation"
            >
              Obtener mi ubicación GPS
            </VBtn>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model.number="form.ubicacion_lat"
              label="Ubicación (Latitud)"
              type="number"
              step="any"
              placeholder="14.6349"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model.number="form.ubicacion_lng"
              label="Ubicación (Longitud)"
              type="number"
              step="any"
              placeholder="-90.5069"
            />
          </VCol>

          <!-- Botón de Guardar -->
          <VCol
            cols="12"
            class="text-center mt-6"
          >
            <VBtn
              :loading="isLoading"
              type="submit"
              color="primary"
              size="large"
            >
              Guardar Cambios del Perfil
            </VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>



