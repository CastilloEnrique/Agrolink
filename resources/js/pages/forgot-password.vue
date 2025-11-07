<!-- <script setup> -->
<!-- import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant' -->
<!-- import { VNodeRenderer } from '@layouts/components/VNodeRenderer' -->
<!-- import { themeConfig } from '@themeConfig' -->
<!-- import authV2ForgotPasswordIllustrationDark from '@images/pages/auth-v2-forgot-password-illustration-dark.png' -->
<!-- import authV2ForgotPasswordIllustrationLight from '@images/pages/auth-v2-forgot-password-illustration-light.png' -->
<!-- import authV2MaskDark from '@images/pages/misc-mask-dark.png' -->
<!-- import authV2MaskLight from '@images/pages/misc-mask-light.png' -->

<!-- const email = ref('') -->
<!-- const authThemeImg = useGenerateImageVariant(authV2ForgotPasswordIllustrationLight, authV2ForgotPasswordIllustrationDark) -->
<!-- const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark) -->

<!-- definePage({ -->
<!--  meta: { -->
<!--    layout: 'blank', -->
<!--    unauthenticatedOnly: true, -->
<!--  }, -->
<!-- }) -->
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
<!--    class="auth-wrapper bg-surface" -->
<!--    no-gutters -->
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
<!--            max-width="468" -->
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
<!--      class="d-flex align-center justify-center" -->
<!--    > -->
<!--      <VCard -->
<!--        flat -->
<!--        :max-width="500" -->
<!--        class="mt-12 mt-sm-0 pa-4" -->
<!--      > -->
<!--        <VCardText> -->
<!--          <h4 class="text-h4 mb-1"> -->
<!--            Forgot Password? 🔒 -->
<!--          </h4> -->
<!--          <p class="mb-0"> -->
<!--            Enter your email and we'll send you instructions to reset your password -->
<!--          </p> -->
<!--        </VCardText> -->

<!--        <VCardText> -->
<!--          <VForm @submit.prevent="() => {}"> -->
<!--            <VRow> -->
<!--              &lt;!&ndash; email &ndash;&gt; -->
<!--              <VCol cols="12"> -->
<!--                <AppTextField -->
<!--                  v-model="email" -->
<!--                  autofocus -->
<!--                  label="Email" -->
<!--                  type="email" -->
<!--                  placeholder="johndoe@email.com" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; Reset link &ndash;&gt; -->
<!--              <VCol cols="12"> -->
<!--                <VBtn -->
<!--                  block -->
<!--                  type="submit" -->
<!--                > -->
<!--                  Send Reset Link -->
<!--                </VBtn> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; back to login &ndash;&gt; -->
<!--              <VCol cols="12"> -->
<!--                <RouterLink -->
<!--                  class="d-flex align-center justify-center" -->
<!--                  :to="{ name: 'login' }" -->
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
<!-- @use "@core-scss/template/pages/page-auth.scss"; -->
<!-- </style> -->
<script setup>
import { ref } from 'vue' // 💡 Asegúrate que ref esté importado
import { VForm } from 'vuetify/components/VForm' // 💡 Importar VForm
import api from '@/services/axios' // 💡 Importar tu cliente API
import { requiredValidator, emailValidator } from '@core/utils/validators' // 💡 Importar validadores
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import authV2ForgotPasswordIllustrationDark from '@images/pages/auth-v2-forgot-password-illustration-dark.png'
import authV2ForgotPasswordIllustrationLight from '@images/pages/auth-v2-forgot-password-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'

const refVForm = ref() // 💡 Para validar el formulario
const email = ref('')
const isLoading = ref(false) // 💡 Para el estado de carga
const successMessage = ref(null) // 💡 Para el mensaje de éxito

const authThemeImg = useGenerateImageVariant(authV2ForgotPasswordIllustrationLight, authV2ForgotPasswordIllustrationDark)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

// 💡 Función para enviar el enlace
const sendLink = async () => {
  const { valid } = await refVForm.value.validate()
  if (!valid) return

  isLoading.value = true
  successMessage.value = null
  try {
    // 💡 Llama a la ruta API que crearemos en Laravel
    await api.post('/forgot-password', { email: email.value })
    successMessage.value = '¡Enlace enviado! Revisa tu correo para continuar.'
  } catch (error) {
    console.error(error)
    alert('Error al enviar el enlace. ¿El correo es correcto?')
  } finally {
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
    class="auth-wrapper bg-surface"
    no-gutters
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    />

    <VCol
      cols="12"
      md="4"
      class="d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            ¿Olvidaste tu contraseña? 🔒
          </h4>
          <p class="mb-0">
            Ingresa tu correo y te enviaremos instrucciones.
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

          <VForm
            ref="refVForm"
            @submit.prevent="sendLink"
          >
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  autofocus
                  label="Correo electrónico"
                  type="email"
                  placeholder="ejemplo@correo.com"
                  :rules="[requiredValidator, emailValidator]"
                  :disabled="isLoading"
                />
              </VCol>

              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  :disabled="!!successMessage"
                >
                  Enviar enlace de reseteo
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
                  <span>Volver al inicio de sesión</span>
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
@use "@core-scss/template/pages/page-auth.scss";
</style>
