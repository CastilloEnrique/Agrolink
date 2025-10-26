
<script setup>
import { VForm } from 'vuetify/components/VForm'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

// Imports de la lógica
import api from '@/services/axios'
import { useRouter } from 'vue-router'

// 👇 IMPORTS DE IMÁGENES (Asegúrate de que las rutas @images sean correctas) 👇
import authV2RegisterIllustrationBorderedDark from '@images/pages/auth-v2-register-illustration-bordered-dark.png'
import authV2RegisterIllustrationBorderedLight from '@images/pages/auth-v2-register-illustration-bordered-light.png'
import authV2RegisterIllustrationDark from '@images/pages/auth-v2-register-illustration-dark.png'
import authV2RegisterIllustrationLight from '@images/pages/auth-v2-register-illustration-light.png' // <-- ESTE FALTABA
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'

// 👆 --- FIN DE IMPORTS DE IMÁGENES --- 👆

// 👇 Esta función necesita las variables importadas arriba
const imageVariant = useGenerateImageVariant(authV2RegisterIllustrationLight, authV2RegisterIllustrationDark, authV2RegisterIllustrationBorderedLight, authV2RegisterIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const router = useRouter()
const refVForm = ref(null) // Para validar el formulario

// --- Estado del Formulario ---
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
  aldea_id: null, // Sigue siendo opcional
  direccion: '',
  privacyPolicies: false,
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// --- Listas para Selects Anidados ---
const paises = ref([])
const departamentos = ref([])
const municipios = ref([])
const aldeas = ref([])
const isLoadingPaises = ref(false)
const isLoadingDepartamentos = ref(false)
const isLoadingMunicipios = ref(false)
const isLoadingAldeas = ref(false)

// --- Lógica de Cámara y Foto ---
const fotoFile = ref(null)
const fotoPreviewUrl = ref(null)
const showCameraDialog = ref(false)
const videoPlayer = ref(null)
const videoStream = ref(null)

// --- Cargar PAÍSES al inicio ---
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

// --- Watcher 1: Cargar DEPARTAMENTOS ---
watch(() => form.value.pais_id, async newPaisId => {
  // eslint-disable-next-line camelcase
  form.value.departamento_id = null
  // eslint-disable-next-line camelcase
  form.value.municipio_id = null
  // eslint-disable-next-line camelcase
  form.value.aldea_id = null
  departamentos.value = []
  municipios.value = []
  aldeas.value = []

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

// --- Watcher 2: Cargar MUNICIPIOS ---
watch(() => form.value.departamento_id, async newDeptId => {
  // eslint-disable-next-line camelcase
  form.value.municipio_id = null
  // eslint-disable-next-line camelcase
  form.value.aldea_id = null
  municipios.value = []
  aldeas.value = []

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

// --- Watcher 3: Cargar ALDEAS ---
watch(() => form.value.municipio_id, async newMuniId => {
  // eslint-disable-next-line camelcase
  form.value.aldea_id = null
  aldeas.value = []

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


// --- Watcher 4: Actualizar Preview de Foto ---
watch(fotoFile, newFileArray => {
  const file = newFileArray ? newFileArray[0] : null
  if (file) {
    const reader = new FileReader()

    reader.onload = e => (fotoPreviewUrl.value = e.target.result)
    reader.readAsDataURL(file)
  } else {
    fotoPreviewUrl.value = null
  }
})


// --- Métodos de la Cámara ---
const openCamera = async () => {
  showCameraDialog.value = true
  try {
    videoStream.value = await navigator.mediaDevices.getUserMedia({ video: true, audio: false })
    await nextTick()
    if (videoPlayer.value) {
      videoPlayer.value.srcObject = videoStream.value
    }
  } catch (err) {
    console.error("Error al acceder a la cámara:", err)
    alert("⛔ No se pudo acceder a la cámara. Asegúrate de dar permisos en el navegador.")
    showCameraDialog.value = false
  }
}

const takePhoto = () => {
  if (!videoPlayer.value) return
  const canvas = document.createElement('canvas')

  canvas.width = videoPlayer.value.videoWidth
  canvas.height = videoPlayer.value.videoHeight

  const context = canvas.getContext('2d')

  context.translate(canvas.width, 0) // Voltear para efecto espejo
  context.scale(-1, 1)
  context.drawImage(videoPlayer.value, 0, 0, canvas.width, canvas.height)

  canvas.toBlob(blob => {
    const file = new File([blob], 'foto_perfil.jpg', { type: 'image/jpeg' })

    fotoFile.value = [file] // Simula la estructura de VFileInput
    closeCamera()
  }, 'image/jpeg')
}

const closeCamera = () => {
  if (videoStream.value) {
    videoStream.value.getTracks().forEach(track => track.stop())
  }
  showCameraDialog.value = false
  videoStream.value = null
}

// --- Método Principal: REGISTRAR ---
const submitRegister = async () => {
  const { valid } = await refVForm.value.validate()
  if (!valid) return

  if (!form.value.privacyPolicies) {
    // La validación del checkbox ya debería manejar esto, pero por si acaso.
    alert('Debes aceptar la política de privacidad y términos.')

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
  formData.append('correo', form.value.correo)
  formData.append('password', form.value.password)
  formData.append('password_confirmation', form.value.password_confirmation)
  formData.append('pais_id', form.value.pais_id || '')
  formData.append('departamento_id', form.value.departamento_id || '')
  formData.append('municipio_id', form.value.municipio_id || '')
  formData.append('aldea_id', form.value.aldea_id || '') // Se envía vacío si es null
  formData.append('direccion', form.value.direccion || '')

  // Añadir la foto (si existe)
  if (fotoFile.value && fotoFile.value[0]) {
    formData.append('foto', fotoFile.value[0])
  }

  try {
    const response = await api.post('/register', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    const { access_token: accessToken, usuario } = response.data

    localStorage.setItem('token', accessToken)
    localStorage.setItem('usuario', JSON.stringify(usuario))
    useCookie('accessToken').value = accessToken
    useCookie('userData').value = usuario

    router.push({ name: 'apps-ecommerce-dashboard' }) // O a donde quieras ir después del registro

  } catch (error) {
    console.error('Error en el registro:', error)

    // Mostrar mensaje de error más específico del backend si está disponible
    let errorMessage = 'Error al registrarse. Verifica tus datos e inténtalo de nuevo.'
    if (error.response?.data?.message) {
      errorMessage = `Error: ${error.response.data.message}`
    } else if (error.response?.data?.errors) {
      const firstErrorKey = Object.keys(error.response.data.errors)[0]

      errorMessage = `Error en el campo '${firstErrorKey}': ${error.response.data.errors[firstErrorKey][0]}`
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
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
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
                />
              </VCol>
              <VCol cols="6">
                <AppTextField
                  v-model="form.nit"
                  label="NIT (Opcional)"
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
                  label="País"
                  :items="paises"
                  item-title="nombre"
                  item-value="id"
                  placeholder="Selecciona un país"
                  :loading="isLoadingPaises"
                  :rules="[requiredValidator]"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="form.departamento_id"
                  label="Departamento"
                  :items="departamentos"
                  item-title="nombre"
                  item-value="id"
                  placeholder="Selecciona un departamento"
                  :loading="isLoadingDepartamentos"
                  :disabled="!form.pais_id || isLoadingDepartamentos"
                  :rules="[requiredValidator]"
                  clearable
                />
              </VCol>

              <VCol cols="6">
                <AppSelect
                  v-model="form.municipio_id"
                  label="Municipio"
                  :items="municipios"
                  item-title="nombre"
                  item-value="id"
                  placeholder="Selecciona..."
                  :loading="isLoadingMunicipios"
                  :disabled="!form.departamento_id || isLoadingMunicipios"
                  :rules="[requiredValidator]"
                  clearable
                />
              </VCol>

              <VCol cols="6">
                <AppSelect
                  v-model="form.aldea_id"
                  label="Aldea (Opcional)"
                  :items="aldeas"
                  item-title="nombre"
                  item-value="id"
                  placeholder="Selecciona..."
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
                />
              </VCol>

              <VCol cols="12">
                <VLabel class="mb-2">
                  Foto de Perfil (Opcional)
                </VLabel>
                <div class="d-flex ga-2">
                  <VFileInput
                    v-model="fotoFile"
                    label="Subir archivo"
                    accept="image/png, image/jpeg, image/jpg, image/webp"
                    prepend-icon="tabler-file-upload"
                    class="flex-grow-1"
                    clearable
                  />
                  <VBtn
                    icon="tabler-camera"
                    color="primary"
                    aria-label="Tomar foto"
                    @click="openCamera"
                  />
                </div>
                <VAvatar
                  v-if="fotoPreviewUrl"
                  :image="fotoPreviewUrl"
                  size="100"
                  class="mt-4 mx-auto d-block"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex align-center my-6">
                  <VCheckbox
                    id="privacy-policy"
                    v-model="form.privacyPolicies"
                    :rules="[requiredValidator]"
                    inline
                  />
                  <VLabel
                    for="privacy-policy"
                    style="opacity: 1;"
                  >
                    <span class="me-1 text-high-emphasis">Acepto la</span>
                    <a
                      href="javascript:void(0)"
                      class="text-primary"
                    >política de privacidad y términos</a>
                  </VLabel>
                </div>

                <VBtn
                  block
                  type="submit"
                >
                  Registrarse
                </VBtn>
              </VCol>

              <VCol
                cols="12"
                class="text-center text-base"
              >
                <span class="d-inline-block">¿Ya tienes una cuenta?</span>
                <RouterLink
                  class="text-primary ms-1 d-inline-block"
                  :to="{ name: 'login' }"
                >
                  Inicia sesión
                </RouterLink>
              </VCol>

              <VCol
                cols="12"
                class="d-flex align-center"
              >
                <VDivider />
                <span class="mx-4">o</span>
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
      <VCardTitle>Tomar Foto</VCardTitle>
      <VCardText class="pa-0">
        <video
          ref="videoPlayer"
          autoplay
          playsinline
          style="width: 100%; height: auto; transform: scaleX(-1);"
        />
      </VCardText>
      <VCardActions class="justify-center pa-4">
        <VBtn
          color="primary"
          @click="takePhoto"
        >
          <VIcon
            icon="tabler-camera"
            class="me-2"
          />
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
</style>
