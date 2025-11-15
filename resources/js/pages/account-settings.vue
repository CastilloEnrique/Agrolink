<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/axios'

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
  },
})

const activeTab = ref('cuenta')
const isLoading = ref(true)

// --- Pestaña 1: Datos de la Cuenta ---
const accountData = ref({
  // eslint-disable-next-line camelcase
  primer_nombre: '',
  // eslint-disable-next-line camelcase
  primer_apellido: '',
  correo: '',
  telefono: '',
})

const accountFormRef = ref(null)
const isSavingAccount = ref(false)

// --- Pestaña 2: Seguridad (Contraseña) ---
const securityData = ref({
  // eslint-disable-next-line camelcase
  contrasena_actual: '',
  // eslint-disable-next-line camelcase
  nueva_contrasena: '',
  // eslint-disable-next-line camelcase
  nueva_contrasena_confirmation: '',
})

const securityFormRef = ref(null)
const isSavingSecurity = ref(false)
const isPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// Cargar datos del perfil al montar la página
onMounted(async () => {
  isLoading.value = true
  try {
    const response = await api.get('/usuario/perfil')
    const user = response.data

    accountData.value = {
      // eslint-disable-next-line camelcase
      primer_nombre: user.primer_nombre,
      // eslint-disable-next-line camelcase
      primer_apellido: user.primer_apellido,
      correo: user.correo,
      telefono: user.telefono,
    }
  } catch (error) {
    console.error('Error al cargar el perfil:', error)
    alert('No se pudo cargar tu información de perfil.')
  } finally {
    isLoading.value = false
  }
})

// --- Lógica para Pestaña 1 ---
const guardarCambiosCuenta = async () => {
  const { valid } = await accountFormRef.value.validate()
  if (!valid) return

  isSavingAccount.value = true
  try {
    const response = await api.post('/usuario/actualizar-perfil', accountData.value)

    alert(response.data.message || 'Perfil actualizado.')
  } catch (error) {
    console.error('Error al guardar perfil:', error)
    alert(error.response?.data?.message || 'Error al guardar. Revisa tu correo, puede que ya esté en uso.')
  } finally {
    isSavingAccount.value = false
  }
}

const resetFormCuenta = () => {
  // Recarga los datos originales
  onMounted()
}

// --- Lógica para Pestaña 2 ---
const guardarCambiosSeguridad = async () => {
  const { valid } = await securityFormRef.value.validate()
  if (!valid) return

  isSavingSecurity.value = true
  try {
    const response = await api.post('/usuario/cambiar-contrasena', securityData.value)

    alert(response.data.message || 'Contraseña cambiada exitosamente.')
    resetFormSeguridad() // Limpia los campos
  } catch (error) {
    console.error('Error al cambiar contraseña:', error)
    alert(error.response?.data?.message || 'Error al cambiar la contraseña. Asegúrate de que tu contraseña actual sea correcta.')
  } finally {
    isSavingSecurity.value = false
  }
}

const resetFormSeguridad = () => {
  securityData.value = {
    // eslint-disable-next-line camelcase
    contrasena_actual: '',
    // eslint-disable-next-line camelcase
    nueva_contrasena: '',
    // eslint-disable-next-line camelcase
    nueva_contrasena_confirmation: '',
  }
  securityFormRef.value.resetValidation()
}
</script>

<template>
  <VContainer>
    <VCard v-if="!isLoading">
      <VTabs
        v-model="activeTab"
        show-arrows
      >
        <VTab value="cuenta">
          <VIcon
            icon="tabler-user"
            class="me-2"
          />
          Cuenta
        </VTab>
        <VTab value="seguridad">
          <VIcon
            icon="tabler-lock"
            class="me-2"
          />
          Seguridad
        </VTab>
      </VTabs>

      <VDivider />

      <VWindow
        v-model="activeTab"
        class="mt-6"
      >
        <!-- Pestaña 1: Cuenta -->
        <VWindowItem value="cuenta">
          <VCardText>
            <h5 class="text-h5 mb-4">
              Detalles de la Cuenta
            </h5>
            <VForm
              ref="accountFormRef"
              @submit.prevent="guardarCambiosCuenta"
            >
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="accountData.primer_nombre"
                    label="Primer Nombre"
                    :rules="[v => !!v || 'El nombre es requerido']"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="accountData.primer_apellido"
                    label="Primer Apellido"
                    :rules="[v => !!v || 'El apellido es requerido']"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="accountData.correo"
                    label="Correo Electrónico"
                    type="email"
                    :rules="[v => !!v || 'El correo es requerido', v => /.+@.+\..+/.test(v) || 'Debe ser un correo válido']"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="accountData.telefono"
                    label="Número de Teléfono"
                  />
                </VCol>

                <VCol
                  cols="12"
                  class="d-flex gap-4"
                >
                  <VBtn
                    type="submit"
                    :loading="isSavingAccount"
                  >
                    Guardar Cambios
                  </VBtn>
                  <VBtn
                    color="secondary"
                    variant="tonal"
                    type="reset"
                    @click.prevent="resetFormCuenta"
                  >
                    Resetear
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>

        <!-- Pestaña 2: Seguridad -->
        <VWindowItem value="seguridad">
          <VCardText>
            <h5 class="text-h5 mb-4">
              Cambiar Contraseña
            </h5>
            <VForm
              ref="securityFormRef"
              @submit.prevent="guardarCambiosSeguridad"
            >
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="securityData.contrasena_actual"
                    label="Contraseña Actual"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    :rules="[v => !!v || 'Contraseña actual requerida']"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  />
                </VCol>
              </VRow>
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="securityData.nueva_contrasena"
                    label="Nueva Contraseña"
                    :type="isNewPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    :rules="[v => !!v || 'Nueva contraseña requerida', v => v.length >= 8 || 'Mínimo 8 caracteres']"
                    @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="securityData.nueva_contrasena_confirmation"
                    label="Confirmar Nueva Contraseña"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    :rules="[v => !!v || 'Confirmación requerida', v => v === securityData.nueva_contrasena || 'Las contraseñas no coinciden']"
                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                  />
                </VCol>

                <VCol
                  cols="12"
                  class="d-flex gap-4"
                >
                  <VBtn
                    type="submit"
                    :loading="isSavingSecurity"
                  >
                    Guardar Cambios
                  </VBtn>
                  <VBtn
                    color="secondary"
                    variant="tonal"
                    type="reset"
                    @click.prevent="resetFormSeguridad"
                  >
                    Limpiar
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>
      </VWindow>
    </VCard>
  </VContainer>
</template>
