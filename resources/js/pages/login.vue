


<!-- <script setup> -->
<!-- import { VForm } from 'vuetify/components/VForm' -->
<!-- import AuthProvider from '@/views/pages/authentication/AuthProvider.vue' -->

<!-- import { VNodeRenderer } from '@layouts/components/VNodeRenderer' -->
<!-- import { themeConfig } from '@themeConfig' -->
<!-- import api from '@/services/axios' // ✅ Import correcto -->
<!-- import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant' -->

<!-- // 👇 IMPORTS NECESARIOS PARA PERMISOS 👇 -->
<!-- // CLAVE: Asegúrate de que estas rutas sean correctas en tu proyecto -->
<!-- import { defineAbilitiesFor } from '@/plugins/casl/index' -->
<!-- import { ability } from '@/plugins/casl/ability' // Objeto CASL principal -->
<!-- // 👆 -&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45;&#45; 👆 -->

<!-- import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png' -->
<!-- import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png' -->
<!-- import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png' -->
<!-- import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png' -->
<!-- import authV2MaskDark from '@images/pages/misc-mask-dark.png' -->
<!-- import authV2MaskLight from '@images/pages/misc-mask-light.png' -->

<!-- const authThemeImg = useGenerateImageVariant( -->
<!--  authV2LoginIllustrationLight, -->
<!--  authV2LoginIllustrationDark, -->
<!--  authV2LoginIllustrationBorderedLight, -->
<!--  authV2LoginIllustrationBorderedDark, -->
<!--  true, -->
<!-- ) -->

<!-- const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark) -->

<!-- definePage({ -->
<!--  meta: { -->
<!--    layout: 'blank', -->
<!--    unauthenticatedOnly: true, -->
<!--  }, -->
<!-- }) -->

<!-- const isPasswordVisible = ref(false) -->
<!-- const route = useRoute() -->
<!-- const router = useRouter() -->

<!-- const errors = ref({ -->
<!--  email: undefined, -->
<!--  password: undefined, -->
<!-- }) -->

<!-- const refVForm = ref() -->

<!-- const credentials = ref({ -->
<!--  email: '', -->
<!--  password: '', -->
<!-- }) -->

<!-- const rememberMe = ref(false) -->

<!-- // === LOGIN CONEXIÓN API LARAVEL (MODIFICADO) === -->
<!-- const login = async () => { -->
<!--  try { -->
<!--    // 1. Petición de autenticación -->
<!--    const authResponse = await api.post('/login', { -->
<!--      correo: credentials.value.email, -->
<!--      password: credentials.value.password, -->
<!--    }) -->

<!--    const { access_token: accessToken } = authResponse.data -->

<!--    // 2. Guardar token inmediatamente para la siguiente llamada -->
<!--    localStorage.setItem('token', accessToken) -->
<!--    useCookie('accessToken').value = accessToken -->

<!--    // 3. OBTENER PERFIL Y ROLES (Llamada protegida a tu API) -->
<!--    const profileResponse = await api.get('/usuario/perfil') -->
<!--    const usuarioConRoles = profileResponse.data // Esto incluye el array 'roles' -->

<!--    // 4. GENERAR REGLAS CASL a partir de los roles -->
<!--    const rules = defineAbilitiesFor(usuarioConRoles) -->

<!--    // 5. APLICAR REGLAS AL OBJETO GLOBAL ABILITY -->
<!--    ability.update(rules) -->

<!--    // 6. GUARDAR DATOS COMPLETOS (con las nuevas abilities) en cookies/localStorage -->
<!--    const userDataWithAbilities = { -->
<!--      ...usuarioConRoles, -->
<!--      abilities: rules, // Las reglas generadas -->
<!--      role: usuarioConRoles.roles[0]?.nombre || 'guest', // Rol primario para Vuexy -->
<!--    } -->

<!--    useCookie('userData').value = userDataWithAbilities -->
<!--    localStorage.setItem('usuario', JSON.stringify(userDataWithAbilities)) -->

<!--    console.log('✅ Login y Permisos Aplicados:', usuarioConRoles.roles) -->

<!--    // 7. Redirigir al dashboard principal -->
<!--    router.push({ name: 'apps-ecommerce-dashboard' }) -->
<!--  } catch (error) { -->
<!--    console.error('Error de login:', error.response || error) -->

<!--    // Limpiar token en caso de fallo para evitar bucles -->
<!--    localStorage.removeItem('token') -->
<!--    alert(error.response?.data?.message || 'Error al iniciar sesión. Verifica tus credenciales.') -->
<!--  } -->
<!-- } -->


<!-- const onSubmit = () => { -->
<!--  refVForm.value?.validate().then(({ valid: isValid }) => { -->
<!--    if (isValid) login() -->
<!--  }) -->
<!-- } -->
<!-- </script> -->


<!-- <template> -->
<!--  <RouterLink to="/"> -->
<!--    <div class="auth-logo d-flex align-center gap-x-3"> -->
<!--      <VNodeRenderer :nodes="themeConfig.app.logo" /> -->
<!--      <h1 class="auth-title"> -->
<!--        {{ themeConfig.app.title }} -->
<!--      </h1> -->
<!--    </div> -->
<!--  </RouterLink> -->

<!--  <VRow -->
<!--    no-gutters -->
<!--    class="auth-wrapper bg-surface" -->
<!--  > -->
<!--    <VCol -->
<!--      md="8" -->
<!--      class="d-none d-md-flex" -->
<!--    > -->
<!--      <div class="position-relative bg-background w-100 me-0"> -->
<!--        <div -->
<!--          class="d-flex align-center justify-center w-100 h-100" -->
<!--          style="padding-inline: 6.25rem;" -->
<!--        > -->
<!--          <VImg -->
<!--            max-width="613" -->
<!--            :src="authThemeImg" -->
<!--            class="auth-illustration mt-16 mb-2" -->
<!--          /> -->
<!--        </div> -->

<!--        <img -->
<!--          class="auth-footer-mask" -->
<!--          :src="authThemeMask" -->
<!--          alt="auth-footer-mask" -->
<!--          height="280" -->
<!--          width="100" -->
<!--        > -->
<!--      </div> -->
<!--    </VCol> -->

<!--    <VCol -->
<!--      cols="12" -->
<!--      md="4" -->
<!--      class="auth-card-v2 d-flex align-center justify-center" -->
<!--    > -->
<!--      <VCard -->
<!--        flat -->
<!--        :max-width="500" -->
<!--        class="mt-12 mt-sm-0 pa-4" -->
<!--      > -->
<!--        <VCardText> -->
<!--          <h4 class="text-h4 mb-1"> -->
<!--            ¡Bienvenido a <span class="text-capitalize">{{ themeConfig.app.title }}</span>! 👋🏻 -->
<!--          </h4> -->
<!--          <p class="mb-0"> -->
<!--            Inicia sesión en tu cuenta y comienza la experiencia. -->
<!--          </p> -->
<!--        </VCardText> -->

<!--        <VCardText> -->
<!--          <VForm -->
<!--            ref="refVForm" -->
<!--            @submit.prevent="onSubmit" -->
<!--          > -->
<!--            <VRow> -->
<!--              <VCol cols="12"> -->
<!--                <AppTextField -->
<!--                  v-model="credentials.email" -->
<!--                  label="Correo electrónico" -->
<!--                  placeholder="ejemplo@correo.com" -->
<!--                  type="email" -->
<!--                  autofocus -->
<!--                  :rules="[requiredValidator, emailValidator]" -->
<!--                  :error-messages="errors.email" -->
<!--                /> -->
<!--              </VCol> -->

<!--              <VCol cols="12"> -->
<!--                <AppTextField -->
<!--                  v-model="credentials.password" -->
<!--                  label="Contraseña" -->
<!--                  placeholder="••••••••••••" -->
<!--                  :rules="[requiredValidator]" -->
<!--                  :type="isPasswordVisible ? 'text' : 'password'" -->
<!--                  autocomplete="password" -->
<!--                  :error-messages="errors.password" -->
<!--                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'" -->
<!--                  @click:append-inner="isPasswordVisible = !isPasswordVisible" -->
<!--                /> -->

<!--                <div class="d-flex align-center flex-wrap justify-space-between my-6"> -->
<!--                  <VCheckbox -->
<!--                    v-model="rememberMe" -->
<!--                    label="Recordarme" -->
<!--                  /> -->
<!--                  <RouterLink -->
<!--                    class="text-primary ms-2 mb-1" -->
<!--                    :to="{ name: 'forgot-password' }" -->
<!--                  > -->
<!--                    ¿Olvidaste tu contraseña? -->
<!--                  </RouterLink> -->
<!--                </div> -->

<!--                <VBtn -->
<!--                  block -->
<!--                  type="submit" -->
<!--                > -->
<!--                  Iniciar sesión -->
<!--                </VBtn> -->
<!--              </VCol> -->

<!--              <VCol -->
<!--                cols="12" -->
<!--                class="text-center" -->
<!--              > -->
<!--                <span>¿Eres nuevo en la plataforma?</span> -->
<!--                <RouterLink -->
<!--                  class="text-primary ms-1" -->
<!--                  :to="{ name: 'register' }" -->
<!--                > -->
<!--                  Crear una cuenta -->
<!--                </RouterLink> -->
<!--              </VCol> -->

<!--              <VCol -->
<!--                cols="12" -->
<!--                class="d-flex align-center" -->
<!--              > -->
<!--                <VDivider /> -->
<!--                <span class="mx-4">o</span> -->
<!--                <VDivider /> -->
<!--              </VCol> -->

<!--              <VCol -->
<!--                cols="12" -->
<!--                class="text-center" -->
<!--              > -->
<!--                <AuthProvider /> -->
<!--              </VCol> -->
<!--            </VRow> -->
<!--          </VForm> -->
<!--        </VCardText> -->
<!--      </VCard> -->
<!--    </VCol> -->
<!--  </VRow> -->
<!-- </template> -->

<!-- <style lang="scss"> -->
<!-- @use "@core-scss/template/pages/page-auth"; -->
<!-- </style> -->



<script setup>
import { VForm } from 'vuetify/components/VForm'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'

import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import api from '@/services/axios' // ✅ Import correcto
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { requiredValidator, emailValidator } from '@core/utils/validators' // ✅ Validadores

// 👇 IMPORTS NECESARIOS PARA PERMISOS 👇
import { defineAbilitiesFor } from '@/plugins/casl/index'
import { ability } from '@/plugins/casl/ability' // Objeto CASL principal
// 👆 --------------------------------- 👆

import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'

// Helper simple para cookies si useCookie no está disponible
function setCookie(name, value, days = 7) {
  const expires = new Date(Date.now() + days * 864e5).toUTCString()

  document.cookie = `${name}=${encodeURIComponent(JSON.stringify(value))}; expires=${expires}; path=/`
}

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

// === LOGIN CONEXIÓN API LARAVEL (MODIFICADO PARA REDIRIGIR A 'inicio') ===
const login = async () => {
  try {
    // 1. Petición de autenticación
    const authResponse = await api.post('/login', {
      correo: credentials.value.email,
      password: credentials.value.password,
    })

    const { access_token: accessToken } = authResponse.data

    // 2. Guardar token inmediatamente
    localStorage.setItem('token', accessToken)
    setCookie('accessToken', accessToken) // Usamos el helper

    // 3. OBTENER PERFIL Y ROLES
    const profileResponse = await api.get('/usuario/perfil')
    const usuarioConRoles = profileResponse.data

    // 4. GENERAR REGLAS CASL
    const rules = defineAbilitiesFor(usuarioConRoles)

    // 5. APLICAR REGLAS AL OBJETO GLOBAL ABILITY
    ability.update(rules)

    // 6. GUARDAR DATOS COMPLETOS
    const userDataWithAbilities = {
      ...usuarioConRoles,
      abilities: rules,

      // Asegúrate de que tu API devuelva el rol principal o ajusta esto
      role: usuarioConRoles.roles[0]?.nombre || 'guest',
    }

    setCookie('userData', userDataWithAbilities) // Usamos el helper
    localStorage.setItem('usuario', JSON.stringify(userDataWithAbilities)) // Guardamos como 'usuario' según tu AuthController

    console.log('✅ Login y Permisos Aplicados:', usuarioConRoles.roles)

    // 7. Redirigir al NUEVO dashboard 'inicio'
    // 💡 CAMBIO CRÍTICO AQUÍ: Apunta al nombre de tu nuevo archivo 'inicio.vue'
    router.push({ name: 'inicio' }) // <--- ¡AQUÍ ESTÁ EL CAMBIO!

  } catch (error) {
    console.error('Error de login:', error.response || error)

    // Limpiar token en caso de fallo
    localStorage.removeItem('token')
    setCookie('accessToken', '', -1) // Borrar cookie
    alert(error.response?.data?.message || 'Error al iniciar sesión. Verifica tus credenciales.')
  }
}


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

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            ¡Bienvenido a <span class="text-capitalize">{{ themeConfig.app.title }}</span>! 👋🏻
          </h4>
          <p class="mb-0">
            Inicia sesión en tu cuenta y comienza la experiencia.
          </p>
        </VCardText>

        <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
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

                <VBtn
                  block
                  type="submit"
                >
                  Iniciar sesión
                </VBtn>
              </VCol>

              <VCol
                cols="12"
                class="text-center"
              >
                <span>¿Eres nuevo en la plataforma?</span>
                <RouterLink
                  class="text-primary ms-1"
                  :to="{ name: 'register' }"
                >
                  Crear una cuenta
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
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
