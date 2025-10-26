
<script setup>
import { VForm } from 'vuetify/components/VForm'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'

import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import api from '@/services/axios' // ✅ Import correcto
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
// eslint-disable-next-line import/no-unresolved
//import { useCookie } from '@vueuse/integrations/useCookie/index.mjs'




const authThemeImg = useGenerateImageVariant(
  authV2LoginIllustrationLight,
  authV2LoginIllustrationDark,
  authV2LoginIllustrationBorderedLight,
  authV2LoginIllustrationBorderedDark,
  true,
)

const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const isPasswordVisible = ref(false)
const route = useRoute()
const router = useRouter()

const errors = ref({
  email: undefined,
  password: undefined,
})

const refVForm = ref()

const credentials = ref({
  email: '',
  password: '',
})

const rememberMe = ref(false)

// === LOGIN CONEXIÓN API LARAVEL ===
const login = async () => {
  try {
    const response = await api.post('/login', {
      correo: credentials.value.email,
      password: credentials.value.password,
    })

    const { access_token: accessToken, usuario } = response.data

    // ✅ Guardar token y usuario en localStorage
    localStorage.setItem('token', accessToken)
    localStorage.setItem('usuario', JSON.stringify(usuario))

    // ✅ Guardar también en cookies (Vuexy usa esto)
    useCookie('accessToken').value = accessToken
    useCookie('userData').value = usuario

    console.log('✅ Login correcto:', usuario)

    // ✅ Redirigir al dashboard principal
    router.push({ name: 'apps-ecommerce-dashboard' })
  } catch (error) {
    console.error('Error de login:', error)
    alert(error.response?.data?.message || 'Error al iniciar sesión. Verifica tus credenciales.')
  }
}

// const login = async () => {
//   try {
//     const response = await api.post('/login', {
//       correo: credentials.value.email,
//       password: credentials.value.password,
//     })
//
//     const { access_token: accessToken, usuario } = response.data
//
//     // Guarda token y usuario localmente
//     localStorage.setItem('token', accessToken)
//     localStorage.setItem('usuario', JSON.stringify(usuario))
//
//     // Prueba: obtener perfil del usuario
//     const perfil = await api.get('/usuario/perfil')
//
//     console.log('Perfil:', perfil.data)
//
//     // Redirige al dashboard
//     await nextTick(() => {
//       router.replace('/')
//     })
//   } catch (error) {
//     console.error('Error de login:', error)
//     alert(error.response?.data?.message || 'Error al iniciar sesión. Verifica tus credenciales.')
//   }
// }

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) login()
  })
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

  <VRow no-gutters class="auth-wrapper bg-surface">
    <!-- IMAGEN -->
    <VCol md="8" class="d-none d-md-flex">
      <div class="position-relative bg-background w-100 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="padding-inline: 6.25rem;"
        >
          <VImg
            max-width="613"
            :src="authThemeImg"
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

    <!-- FORMULARIO -->
    <VCol cols="12" md="4" class="auth-card-v2 d-flex align-center justify-center">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardText>
          <h4 class="text-h4 mb-1">
            ¡Bienvenido a <span class="text-capitalize">{{ themeConfig.app.title }}</span>! 👋🏻
          </h4>
          <p class="mb-0">
            Inicia sesión en tu cuenta y comienza la experiencia.
          </p>
        </VCardText>

        <VCardText>
          <VForm ref="refVForm" @submit.prevent="onSubmit">
            <VRow>
              <!-- Correo -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.email"
                  label="Correo electrónico"
                  placeholder="ejemplo@correo.com"
                  type="email"
                  autofocus
                  :rules="[requiredValidator, emailValidator]"
                  :error-messages="errors.email"
                />
              </VCol>

              <!-- Contraseña -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.password"
                  label="Contraseña"
                  placeholder="••••••••••••"
                  :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :error-messages="errors.password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center flex-wrap justify-space-between my-6">
                  <VCheckbox
                    v-model="rememberMe"
                    label="Recordarme"
                  />
                  <RouterLink
                    class="text-primary ms-2 mb-1"
                    :to="{ name: 'forgot-password' }"
                  >
                    ¿Olvidaste tu contraseña?
                  </RouterLink>
                </div>

                <VBtn block type="submit">
                  Iniciar sesión
                </VBtn>
              </VCol>

              <!-- Crear cuenta -->
              <VCol cols="12" class="text-center">
                <span>¿Eres nuevo en la plataforma?</span>
                <RouterLink class="text-primary ms-1" :to="{ name: 'register' }">
                  Crear una cuenta
                </RouterLink>
              </VCol>

              <!-- Divider -->
              <VCol cols="12" class="d-flex align-center">
                <VDivider />
                <span class="mx-4">o</span>
                <VDivider />
              </VCol>

              <!-- Proveedores externos -->
              <VCol cols="12" class="text-center">
                <AuthProvider />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
