<script setup>
import { VForm } from 'vuetify/components/VForm'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
// eslint-disable-next-line import/no-unresolved

// --- Imports de Lógica de Vue y Ayudantes ---
import api from '@/services/axios'
import { useRouter } from 'vue-router'
import { ref, watch, onMounted, nextTick } from 'vue'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'

// --- Imports de Validadores (¡Asegúrate que la ruta sea correcta!) ---
// eslint-disable-next-line import/extensions
import { requiredValidator, emailValidator, passwordValidator, confirmedValidator } from '@core/utils/validators.js'

// --- IMPORTS DE IMÁGENES ---
import authV2RegisterIllustrationBorderedDark from '@images/pages/auth-v2-register-illustration-bordered-dark.png'
import authV2RegisterIllustrationBorderedLight from '@images/pages/auth-v2-register-illustration-bordered-light.png'
import authV2RegisterIllustrationDark from '@images/pages/auth-v2-register-illustration-dark.png'
import authV2RegisterIllustrationLight from '@images/pages/auth-v2-register-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'

// --- FIN IMPORTS DE IMÁGENES ---

// --- Configuración de Imágenes y Página ---
const imageVariant = useGenerateImageVariant(authV2RegisterIllustrationLight, authV2RegisterIllustrationDark, authV2RegisterIllustrationBorderedLight, authV2RegisterIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

// --- Refs y Estado del Componente ---
const router = useRouter()
const refVForm = ref(null)

// Estado principal del formulario (Incluye eslint-disable para snake_case/camelCase)
const form = ref({
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
  correo: '',
  password: '',
  // eslint-disable-next-line camelcase
  password_confirmation: '',
  // eslint-disable-next-line camelcase
  pais_id: null,
  // eslint-disable-next-line camelcase
  departamento_id: null,
  // eslint-disable-next-line camelcase
  municipio_id: null,
  // eslint-disable-next-line camelcase
  aldea_id: null,
  direccion: '',
  // eslint-disable-next-line camelcase
  rol_elegido: null,
  privacyPolicies: false,
})

// Estado para la opción de agregar aldea manualmente
const isAddingNewAldea = ref(false)
const newAldeaName = ref('')

// Estado para visibilidad de contraseñas
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// Listas y estados de carga
const paises = ref([])
const departamentos = ref([])
const municipios = ref([])
const aldeas = ref([])
const isLoadingPaises = ref(false)
const isLoadingDepartamentos = ref(false)
const isLoadingMunicipios = ref(false)
const isLoadingAldeas = ref(false)

// Lista fija de opciones de rol para el select
const opcionesRol = ref([
  { text: 'Productor (Quiero vender)', value: 'Productor', icon: 'tabler-leaf' },
  { text: 'Consumidor (Solo quiero comprar)', value: 'Consumidor', icon: 'tabler-shopping-cart' },
  { text: 'Intermediario', value: 'Intermediario', icon: 'tabler-arrows-left-right' },
])

// Estado para la foto de perfil (archivo y cámara)
const fotoFile = ref(null)
const fotoPreviewUrl = ref(null)
const showCameraDialog = ref(false)
const videoPlayer = ref(null)
const videoStream = ref(null)

// --- Hooks de Ciclo de Vida ---
onMounted(async () => {
  isLoadingPaises.value = true
  try {
    const response = await api.get('/paises')

    paises.value = response.data
  } catch (error) {
    console.error('Error al cargar países:', error)
    alert('⚠️ Error al cargar la lista de países. Revisa la consola (F12) y asegúrate que la ruta API `/paises` funcione.')
  } finally {
    isLoadingPaises.value = false
  }
})

// --- Watchers (Geografía y Preview de Foto) ---
watch(() => form.value.pais_id, async (newPaisId, oldPaisId) => {
  if (newPaisId === oldPaisId) return
  // eslint-disable-next-line camelcase
  form.value.departamento_id = null
  // eslint-disable-next-line camelcase
  form.value.municipio_id = null
  // eslint-disable-next-line camelcase
  form.value.aldea_id = null
  departamentos.value = []
  municipios.value = []
  aldeas.value = []
  isAddingNewAldea.value = false
  newAldeaName.value = ''
  if (newPaisId) {
    isLoadingDepartamentos.value = true
    try {
      const response = await api.get(`/departamentos/${newPaisId}`)

      departamentos.value = response.data
    } catch (error) {
      console.error('Error al cargar departamentos:', error)
      alert('⚠️ Error al cargar departamentos. Revisa la consola (F12).')
    } finally {
      isLoadingDepartamentos.value = false
    }
  }
})
watch(() => form.value.departamento_id, async (newDeptId, oldDeptId) => {
  if (newDeptId === oldDeptId) return
  // eslint-disable-next-line camelcase
  form.value.municipio_id = null
  // eslint-disable-next-line camelcase
  form.value.aldea_id = null
  municipios.value = []
  aldeas.value = []
  isAddingNewAldea.value = false
  newAldeaName.value = ''
  if (newDeptId) {
    isLoadingMunicipios.value = true
    try {
      const response = await api.get(`/municipios/${newDeptId}`)

      municipios.value = response.data
    } catch (error) {
      console.error('Error al cargar municipios:', error)
    } finally {
      isLoadingMunicipios.value = false
    }
  }
})
watch(() => form.value.municipio_id, async (newMuniId, oldMuniId) => {
  if (newMuniId === oldMuniId) return
  // eslint-disable-next-line camelcase
  form.value.aldea_id = null
  aldeas.value = []
  isAddingNewAldea.value = false
  newAldeaName.value = ''
  if (newMuniId) {
    isLoadingAldeas.value = true
    try {
      const response = await api.get(`/aldeas/${newMuniId}`)

      aldeas.value = response.data
    } catch (error) {
      console.error('Error al cargar aldeas:', error)
    } finally {
      isLoadingAldeas.value = false
    }
  }
})
watch(fotoFile, newFileArray => {
  const file = newFileArray ? newFileArray[0] : null
  if (file && file instanceof File) {
    const reader = new FileReader()

    reader.onload = e => {
      fotoPreviewUrl.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else {
    fotoPreviewUrl.value = null
  }
})

// --- Métodos de la Cámara (Lógica COMPLETA) ---

const openCamera = async () => {
  showCameraDialog.value = true
  try {
    videoStream.value = await navigator.mediaDevices.getUserMedia({ video: true, audio: false })
    await nextTick()
    if (videoPlayer.value) {
      videoPlayer.value.srcObject = videoStream.value
    } else {
      console.error("El elemento <video> no está listo.")
      closeCamera()
    }
  } catch (err) {
    console.error("Error al acceder a la cámara:", err)
    alert("⛔ No se pudo acceder a la cámara. Asegúrate de dar permisos en el navegador y que no esté siendo usada por otra aplicación.")
    showCameraDialog.value = false
  }
}

const takePhoto = () => {
  if (!videoPlayer.value || !videoStream.value || videoPlayer.value.readyState < 3) {
    console.warn("Video no listo para capturar")
    
    return
  }
  const canvas = document.createElement('canvas')

  canvas.width = videoPlayer.value.videoWidth
  canvas.height = videoPlayer.value.videoHeight

  const context = canvas.getContext('2d')

  context.translate(canvas.width, 0)
  context.scale(-1, 1)
  context.drawImage(videoPlayer.value, 0, 0, canvas.width, canvas.height)
  canvas.toBlob(blob => {
    if (blob) {
      const file = new File([blob], `foto_webcam_${Date.now()}.jpg`, { type: 'image/jpeg' })

      fotoFile.value = [file]
      closeCamera()
    } else {
      console.error("Error al crear Blob desde canvas.")
      alert("Error al capturar la foto.")
    }
  }, 'image/jpeg', 0.9)
}

const closeCamera = () => {
  if (videoStream.value) {
    videoStream.value.getTracks().forEach(track => track.stop())
  }
  showCameraDialog.value = false
  videoStream.value = null
}

// Método principal que se ejecuta al enviar el formulario
const submitRegister = async () => {
  const { valid } = await refVForm.value.validate()
  if (!valid) return
  if (!form.value.privacyPolicies) {
    alert('Debes aceptar la política de privacidad y términos para continuar.')
    
    return
  }

  const formData = new FormData()

  // Mapear el ref 'form' a FormData
  formData.append('primer_nombre', form.value.primer_nombre)
  formData.append('segundo_nombre', form.value.segundo_nombre || '')
  formData.append('primer_apellido', form.value.primer_apellido)
  formData.append('segundo_apellido', form.value.segundo_apellido || '')
  formData.append('dpi', form.value.dpi || '')
  formData.append('nit', form.value.nit || '')
  formData.append('fecha_nacimiento', form.value.fecha_nacimiento || '')
  formData.append('correo', form.value.correo)
  formData.append('password', form.value.password)
  formData.append('password_confirmation', form.value.password_confirmation)
  formData.append('pais_id', form.value.pais_id || '')
  formData.append('departamento_id', form.value.departamento_id || '')
  formData.append('municipio_id', form.value.municipio_id || '')
  formData.append('rol_elegido', form.value.rol_elegido || '') // <-- ROL ELEGIDO

  // Lógica condicional para Aldea
  if (isAddingNewAldea.value && newAldeaName.value.trim()) {
    formData.append('nueva_aldea_nombre', newAldeaName.value.trim())
  } else if (!isAddingNewAldea.value && form.value.aldea_id) {
    formData.append('aldea_id', form.value.aldea_id)
  }
  formData.append('direccion', form.value.direccion || '')

  // Añadir la foto (si existe)
  if (fotoFile.value && fotoFile.value[0]) {
    formData.append('foto', fotoFile.value[0])
  }

  try {
    console.log("Enviando datos de registro:", Object.fromEntries(formData.entries())) // Debugging

    const response = await api.post('/register', formData, { headers: { 'Content-Type': 'multipart/form-data' } })

    // Procesar respuesta exitosa
    const { access_token: accessToken, usuario } = response.data

    localStorage.setItem('token', accessToken)
    localStorage.setItem('usuario', JSON.stringify(usuario))
    useCookie('accessToken').value = accessToken
    useCookie('userData').value = usuario

    // Poner la bandera para que el layout muestre la bienvenida
    localStorage.setItem('showWelcomeNotification', 'true')

    router.push({ name: 'inicio' })

  } catch (error) {
    // Manejar errores
    console.error('Error detallado en el registro:', error.response || error)
    let errorMessage = 'Error al registrarse. Verifica tus datos e inténtalo de nuevo.'
    if (error.response?.data?.message) {
      errorMessage = `Error: ${error.response.data.message}`
    } else if (error.response?.data?.errors) {
      const firstErrorKey = Object.keys(error.response.data.errors)[0]

      errorMessage = `Error en '${firstErrorKey}': ${error.response.data.errors[firstErrorKey][0]}`
    }
    alert(errorMessage)
  }
}
</script>

<template>
  <RouterLink to="/">
    <div class="auth-logo d-flex align-center gap-x-3">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="auth-title">
        {{ themeConfig.app.title }}
      </h1>
    </div>
  </RouterLink>

  <VRow
    no-gutters
    class="auth-wrapper bg-surface"
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative bg-background w-100 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="padding-inline: 100px;"
        >
          <VImg
            max-width="500"
            :src="imageVariant"
            class="auth-illustration mt-16 mb-2"
          />
        </div>
        <img
          class="auth-footer-mask"
          :src="authThemeMask"
          alt="auth-footer-mask"
          height="280"
          width="100"
        >
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
      style="background-color: rgb(var(--v-theme-surface));"
    >

      <VCard
        flat
        max-width="500"
        class="mt-12 mt-sm-0 pa-4"
        style="overflow-y: auto; max-height: 95vh;"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            La aventura comienza aquí 🚀
          </h4>
          <p class="mb-0">
            Crea tu cuenta para empezar.
          </p>
        </VCardText>

        <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="submitRegister"
          >
            <VRow>
              <VCol
                cols="12"
                class="mb-4"
              >
                <AppSelect
                  v-model="form.rol_elegido"
                  :items="opcionesRol"
                  :rules="[requiredValidator]"
                  item-title="text"
                  item-value="value"
                  label="¿Cuál será tu rol principal?"
                  placeholder="Selecciona tu tipo de cuenta"
                  clearable
                >
                  <template #item="{ props, item }">
                    <VListItem
                      v-bind="props"
                      :prepend-icon="item.raw.icon"
                    />
                  </template>
                </AppSelect>
              </VCol>

              <VCol cols="6">
                <AppTextField
                  v-model="form.primer_nombre"
                  :rules="[requiredValidator]"
                  autofocus
                  label="Primer Nombre"
                  placeholder="Juan"
                />
              </VCol>
              <VCol cols="6">
                <AppTextField
                  v-model="form.segundo_nombre"
                  label="Segundo Nombre"
                  placeholder="(Opcional)"
                />
              </VCol>
              <VCol cols="6">
                <AppTextField
                  v-model="form.primer_apellido"
                  :rules="[requiredValidator]"
                  label="Primer Apellido"
                  placeholder="Pérez"
                />
              </VCol>
              <VCol cols="6">
                <AppTextField
                  v-model="form.segundo_apellido"
                  label="Segundo Apellido"
                  placeholder="(Opcional)"
                />
              </VCol>

              <VCol cols="6">
                <AppTextField
                  v-model="form.dpi"
                  label="DPI (Opcional)"
                  placeholder="Tu DPI"
                />
              </VCol>
              <VCol cols="6">
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

              <VCol cols="12">
                <AppTextField
                  v-model="form.correo"
                  :rules="[requiredValidator, emailValidator]"
                  label="Correo Electrónico"
                  type="email"
                  placeholder="tu@correo.com"
                />
              </VCol>
              <VCol cols="6">
                <AppTextField
                  v-model="form.password"
                  :rules="[requiredValidator, passwordValidator]"
                  label="Contraseña"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>
              <VCol cols="6">
                <AppTextField
                  v-model="form.password_confirmation"
                  :rules="[requiredValidator, value => confirmedValidator(value, form.password)]"
                  label="Confirmar"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>


              <VCol cols="12">
                <h6 class="text-h6 mb-2 mt-4">
                  Ubicación
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
                <VSwitch
                  v-model="isAddingNewAldea"
                  label="Mi aldea no está en la lista / Agregar nueva"
                  :disabled="!form.municipio_id"
                  color="primary"
                  inset
                  hide-details
                />
              </VCol>
              <VCol
                v-if="isAddingNewAldea"
                cols="12"
              >
                <AppTextField
                  v-model="newAldeaName"
                  label="Nombre de la Nueva Aldea"
                  placeholder="Escribe el nombre aquí"
                  :rules="isAddingNewAldea ? [requiredValidator] : []"
                  :disabled="!form.municipio_id"
                  clearable
                />
              </VCol>
              <VCol
                v-else
                cols="12"
              >
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

              <VCol cols="12">
                <AppTextField
                  v-model="form.direccion"
                  label="Dirección Específica (Opcional)"
                  placeholder="Ej: Lote 5, Casa Azul, Zona 1"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <VLabel class="mb-2">
                  Foto de Perfil (Opcional)
                </VLabel>
                <div class="d-flex ga-2 align-center">
                  <VFileInput
                    v-model="fotoFile"
                    label="Subir archivo"
                    accept="image/*"
                    prepend-icon="tabler-file-upload"
                    class="flex-grow-1"
                    clearable
                    hide-details="auto"
                  />
                  <VBtn
                    icon="tabler-camera"
                    color="primary"
                    aria-label="Tomar foto"
                    title="Tomar foto con la cámara"
                    @click="openCamera"
                  />
                </div>
                <VAvatar
                  v-if="fotoPreviewUrl"
                  :image="fotoPreviewUrl"
                  size="100"
                  class="mt-4 mx-auto d-block elevation-2"
                  rounded="lg"
                />
              </VCol>

              <VCol cols="12">
                <VCheckbox
                  v-model="form.privacyPolicies"
                  :rules="[requiredValidator]"
                  class="mt-4"
                >
                  <template #label>
                    <div class="text-wrap text-body-2">
                      Acepto la&nbsp;<a
                        href="javascript:void(0)"
                        class="text-primary"
                        @click.stop
                      >política de privacidad y términos</a>
                    </div>
                  </template>
                </VCheckbox>
                <VBtn
                  block
                  type="submit"
                  class="mt-4"
                >
                  Registrarse
                </VBtn>
              </VCol>

              <VCol
                cols="12"
                class="text-center text-base mt-4"
              >
                <span>¿Ya tienes una cuenta?</span>
                <RouterLink
                  class="text-primary ms-1"
                  :to="{ name: 'login' }"
                >
                  Inicia sesión
                </RouterLink>
              </VCol>
              <VCol
                cols="12"
                class="d-flex align-center mt-4"
              >
                <VDivider />
                <span class="mx-4">
                  o
                </span>
                <VDivider />
              </VCol>

              <VCol
                cols="12"
                class="text-center"
              >
                <AuthProvider />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>


  <VDialog
    v-model="showCameraDialog"
    width="auto"
    max-width="500px"
    persistent
  >
    <VCard>
      <VCardTitle class="text-center">
        Tomar Foto
      </VCardTitle>
      <VCardText class="pa-0">
        <video
          ref="videoPlayer"
          autoplay
          playsinline
          style="width: 100%; 
               height: auto; 
               transform: 
               scaleX(-1);
                border-radius: 
                inherit;"
        />
      </VCardText>
      <VCardActions class="justify-center pa-4">
        <VBtn
          color="primary"
          prepend-icon="tabler-camera"
          @click="takePhoto"
        >
          Capturar
        </VBtn>
        <VBtn
          color="secondary"
          variant="tonal"
          @click="closeCamera"
        >
          Cancelar
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";

.auth-card-v2 .v-card {
  overflow-y: auto;
  max-height: 95vh;
}
</style>
