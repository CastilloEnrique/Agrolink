<!-- <script setup> -->
<!-- import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant' -->
<!-- import { VNodeRenderer } from '@layouts/components/VNodeRenderer' -->
<!-- import { themeConfig } from '@themeConfig' -->
<!-- import authV2ResetPasswordIllustrationDark from '@images/pages/auth-v2-reset-password-illustration-dark.png' -->
<!-- import authV2ResetPasswordIllustrationLight from '@images/pages/auth-v2-reset-password-illustration-light.png' -->
<!-- import authV2MaskDark from '@images/pages/misc-mask-dark.png' -->
<!-- import authV2MaskLight from '@images/pages/misc-mask-light.png' -->

<!-- definePage({ -->
<!--  meta: { -->
<!--    layout: 'blank', -->
<!--    public: true, -->
<!--  }, -->
<!-- }) -->

<!-- const form = ref({ -->
<!--  newPassword: '', -->
<!--  confirmPassword: '', -->
<!-- }) -->

<!-- const authThemeImg = useGenerateImageVariant(authV2ResetPasswordIllustrationLight, authV2ResetPasswordIllustrationDark) -->
<!-- const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark) -->
<!-- const isPasswordVisible = ref(false) -->
<!-- const isConfirmPasswordVisible = ref(false) -->
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
<!--          style="padding-inline: 150px;" -->
<!--        > -->
<!--          <VImg -->
<!--            max-width="451" -->
<!--            :src="authThemeImg" -->
<!--            class="auth-illustration mt-16 mb-2" -->
<!--          /> -->
<!--        </div> -->

<!--        <img -->
<!--          class="auth-footer-mask flip-in-rtl" -->
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
<!--        class="mt-12 mt-sm-0 pa-6" -->
<!--      > -->
<!--        <VCardText> -->
<!--          <h4 class="text-h4 mb-1"> -->
<!--            Reset Password 🔒 -->
<!--          </h4> -->
<!--          <p class="mb-0"> -->
<!--            Your new password must be different from previously used passwords -->
<!--          </p> -->
<!--        </VCardText> -->

<!--        <VCardText> -->
<!--          <VForm @submit.prevent="() => {}"> -->
<!--            <VRow> -->
<!--              &lt;!&ndash; password &ndash;&gt; -->
<!--              <VCol cols="12"> -->
<!--                <AppTextField -->
<!--                  v-model="form.newPassword" -->
<!--                  autofocus -->
<!--                  label="New Password" -->
<!--                  placeholder="············" -->
<!--                  :type="isPasswordVisible ? 'text' : 'password'" -->
<!--                  autocomplete="password" -->
<!--                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'" -->
<!--                  @click:append-inner="isPasswordVisible = !isPasswordVisible" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; Confirm Password &ndash;&gt; -->
<!--              <VCol cols="12"> -->
<!--                <AppTextField -->
<!--                  v-model="form.confirmPassword" -->
<!--                  label="Confirm Password" -->
<!--                  autocomplete="confirm-password" -->
<!--                  placeholder="············" -->
<!--                  :type="isConfirmPasswordVisible ? 'text' : 'password'" -->
<!--                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'" -->
<!--                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; Set password &ndash;&gt; -->
<!--              <VCol cols="12"> -->
<!--                <VBtn -->
<!--                  block -->
<!--                  type="submit" -->
<!--                > -->
<!--                  Set New Password -->
<!--                </VBtn> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; back to login &ndash;&gt; -->
<!--              <VCol cols="12"> -->
<!--                <RouterLink -->
<!--                  class="d-flex align-center justify-center" -->
<!--                  :to="{ name: 'pages-authentication-login-v2' }" -->
<!--                > -->
<!--                  <VIcon -->
<!--                    icon="tabler-chevron-left" -->
<!--                    size="20" -->
<!--                    class="me-1 flip-in-rtl" -->
<!--                  /> -->
<!--                  <span>Back to login</span> -->
<!--                </RouterLink> -->
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
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { VForm } from 'vuetify/components/VForm'
import api from '@/services/axios'
import { requiredValidator, passwordValidator, confirmedValidator } from '@core/utils/validators'

// 💡 --- Imports Visuales (sin cambios) ---
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import authV2ResetPasswordIllustrationDark from '@images/pages/auth-v2-reset-password-illustration-dark.png'
import authV2ResetPasswordIllustrationLight from '@images/pages/auth-v2-reset-password-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'

// 💡 --- Definición de Página (sin cambios) ---
definePage({
  meta: {
    layout: 'blank',
    public: true, // Correcto, es una página pública
  },
})

// 💡 --- Estado del Componente ---
const route = useRoute()
const router = useRouter()
const refVForm = ref()

const form = ref({
  password: '',
  // eslint-disable-next-line camelcase
  password_confirmation: '',
  email: '', // Se llenará desde la URL
  token: '', // Se llenará desde la URL
})

const isLoading = ref(false)
const successMessage = ref(null)
const errorMessage = ref(null)

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// 💡 --- Lógica Visual (sin cambios) ---
const authThemeImg = useGenerateImageVariant(authV2ResetPasswordIllustrationLight, authV2ResetPasswordIllustrationDark)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

// 💡 --- Lógica de Carga ---
onMounted(() => {
  // Captura el token y el email de la URL
  form.value.email = route.query.email
  form.value.token = route.query.token

  // Si no vienen, muestra un error
  if (!form.value.token || !form.value.email) {
    errorMessage.value = 'El enlace de reseteo es inválido o ha expirado. Por favor, solicita uno nuevo.'
  }
})

// 💡 --- Lógica de Envío ---
const resetPassword = async () => {
  const { valid } = await refVForm.value.validate()
  if (!valid) return

  isLoading.value = true
  errorMessage.value = null
  successMessage.value = null

  try {
    // Llama a la ruta API que ya creamos en AuthController
    await api.post('/reset-password', form.value)

    successMessage.value = '¡Contraseña actualizada! Serás redirigido al inicio de sesión...'

    // Deshabilita el formulario
    isLoading.value = true

    // Redirige al login después de 3 segundos
    setTimeout(() => {
      router.push({ name: 'login' })
    }, 3000)

  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'No se pudo actualizar la contraseña. El token puede ser inválido.'
    isLoading.value = false
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
    />

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-6"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            Restablecer Contraseña 🔒
          </h4>
          <p class="mb-0">
            Tu nueva contraseña debe ser diferente a las anteriores.
          </p>
        </VCardText>

        <VCardText>
          <VAlert
            v-if="successMessage"
            type="success"
            variant="tonal"
            class="mb-4"
          >
            {{ successMessage }}
          </VAlert>
          <VAlert
            v-if="errorMessage"
            type="error"
            variant="tonal"
            class="mb-4"
          >
            {{ errorMessage }}
          </VAlert>

          <VForm
            ref="refVForm"
            @submit.prevent="resetPassword"
          >
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="form.password"
                  autofocus
                  label="Nueva Contraseña"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, passwordValidator]"
                  :disabled="isLoading"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="form.password_confirmation"
                  label="Confirmar Contraseña"
                  autocomplete="confirm-password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, value => confirmedValidator(value, form.password)]"
                  :disabled="isLoading"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  :disabled="!!successMessage || !form.token"
                >
                  Guardar nueva contraseña
                </VBtn>
              </VCol>

              <VCol cols="12">
                <RouterLink
                  class="d-flex align-center justify-center"
                  :to="{ name: 'login' }"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    size="20"
                    class="me-1 flip-in-rtl"
                  />
                  <span>Volver a inicio de sesión</span>
                </RouterLink>
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
