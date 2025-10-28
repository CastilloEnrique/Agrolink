<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/axios'
import { VForm } from 'vuetify/components/VForm'
import { emailValidator, requiredValidator } from '@core/utils/validators'

// --- Estado del Componente ---
const refVForm = ref(null)
const isLoading = ref(true)
const successMessage = ref('')
const errorMessage = ref('')

// Almacenará los datos del usuario logueado
const userData = ref({
  // eslint-disable-next-line camelcase
  primer_nombre: '',
  // eslint-disable-next-line camelcase
  segundo_nombre: '',
  // eslint-disable-next-line camelcase
  primer_apellido: '',
  // eslint-disable-next-line camelcase
  segundo_apellido: '',
  correo: '',
  dpi: '',
  nit: '',
  // eslint-disable-next-line camelcase
  fecha_nacimiento: null,

  // (Campos del PerfilProductor que también se cargan)
  whatsapp: '',
  direccion: '',
  // eslint-disable-next-line camelcase
  ubicacion_lat: null,
  // eslint-disable-next-line camelcase
  ubicacion_lng: null,
})

// --- Cargar Datos del Usuario ---
onMounted(async () => {
  isLoading.value = true
  try {
    // 💡 1. LLAMA A LA RUTA GET (que ya existe)
    const response = await api.get('/productor/perfil-completo')

    // Rellena el ref 'userData' con los datos del backend
    if (response.data) {
      // Usamos Object.assign para rellenar solo los campos que definimos en userData
      Object.assign(userData.value, response.data)
    }
  } catch (error) {
    console.error('Error al cargar datos del perfil:', error)
    errorMessage.value = 'No se pudieron cargar los datos de la cuenta.'
  } finally {
    isLoading.value = false
  }
})

// --- Guardar Cambios ---
const handleFormSubmit = async () => {
  successMessage.value = ''
  errorMessage.value = ''

  const { valid } = await refVForm.value.validate()
  if (!valid) return

  isLoading.value = true
  try {
    // 💡 2. LLAMA A LA RUTA POST (que ya existe)
    // Enviamos solo los datos que están en el 'ref' (el backend validará)
    const response = await api.post('/productor/perfil-completo', userData.value)

    successMessage.value = response.data.message

    // 💡 3. Actualizar localStorage
    // Esto asegura que si el usuario recarga, vea los datos nuevos
    const updatedUserData = JSON.parse(localStorage.getItem('usuario') || '{}')

    // Sobreescribimos los datos del localStorage con los datos guardados
    const newUserData = { ...updatedUserData, ...userData.value }

    localStorage.setItem('usuario', JSON.stringify(newUserData))

  } catch (error) {
    console.error('Error al guardar datos:', error)
    if (error.response && error.response.data.errors) {
      // Si Laravel devuelve errores de validación
      const firstError = Object.values(error.response.data.errors)[0][0]

      errorMessage.value = firstError || 'Error de validación.'
    } else {
      errorMessage.value = 'Error al guardar los cambios. Inténtalo de nuevo.'
    }
  } finally {
    isLoading.value = false
  }
}

// Función para resetear el formulario a los datos cargados
const resetForm = async () => {
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const response = await api.get('/productor/perfil-completo')
    if (response.data) {
      Object.assign(userData.value, response.data)
    }
  } catch (error) {
    errorMessage.value = 'No se pudieron recargar los datos.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VCard title="Detalles de la Cuenta">
    <VCardText>
      <!-- Mensaje de Carga -->
      <VAlert
        v-if="isLoading && !successMessage && !errorMessage"
        type="info"
        variant="tonal"
        title="Cargando datos de la cuenta..."
      />

      <!-- Mensaje de Éxito -->
      <VAlert
        v-if="successMessage"
        type="success"
        variant="tonal"
        class="mb-4"
      >
        {{ successMessage }}
      </VAlert>

      <!-- Mensaje de Error -->
      <VAlert
        v-if="errorMessage"
        type="error"
        variant="tonal"
        class="mb-4"
      >
        {{ errorMessage }}
      </VAlert>

      <!-- Formulario (solo se muestra si no está cargando) -->
      <VForm
        v-if="!isLoading"
        ref="refVForm"
        @submit.prevent="handleFormSubmit"
      >
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="userData.primer_nombre"
              label="Primer Nombre"
              :rules="[requiredValidator]"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="userData.segundo_nombre"
              label="Segundo Nombre"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="userData.primer_apellido"
              label="Primer Apellido"
              :rules="[requiredValidator]"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="userData.segundo_apellido"
              label="Segundo Apellido"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="userData.correo"
              label="Correo Electrónico"
              type="email"
              :rules="[requiredValidator, emailValidator]"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="userData.whatsapp"
              label="WhatsApp"
              placeholder="+502 1234 5678"
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="userData.dpi"
              label="DPI"
              placeholder="0000 00000 0000"
            />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="userData.nit"
                label="NIT"
                placeholder="0000000-0"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <AppDateTimePicker
                v-model="userData.fecha_nacimiento"
                label="Fecha de Nacimiento"
                placeholder="Selecciona la fecha"
                :config="{ altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', maxDate: 'today' }"
              />
            </VCol>

            <VCol
              cols="12"
              class="d-flex flex-wrap gap-4"
            >
              <VBtn
                type="submit"
                color="primary"
                :loading="isLoading"
              >
                Guardar Cambios
              </VBtn>
              <VBtn
                type="reset"
                color="secondary"
                variant="tonal"
                @click.prevent="resetForm"
              >
                Cancelar
              </VBtn>
            </VCol>

        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>










<!-- <script setup> -->
<!-- import avatar1 from '@images/avatars/avatar-1.png' -->

<!-- const accountData = { -->
<!--  avatarImg: avatar1, -->
<!--  firstName: 'john', -->
<!--  lastName: 'Doe', -->
<!--  email: 'johnDoe@example.com', -->
<!--  org: 'Pixinvent', -->
<!--  phone: '+1 (917) 543-9876', -->
<!--  address: '123 Main St, New York, NY 10001', -->
<!--  state: 'New York', -->
<!--  zip: '10001', -->
<!--  country: 'USA', -->
<!--  language: 'English', -->
<!--  timezone: '(GMT-11:00) International Date Line West', -->
<!--  currency: 'USD', -->
<!-- } -->

<!-- const refInputEl = ref() -->
<!-- const isConfirmDialogOpen = ref(false) -->
<!-- const accountDataLocal = ref(structuredClone(accountData)) -->
<!-- const isAccountDeactivated = ref(false) -->
<!-- const validateAccountDeactivation = [v => !!v || 'Please confirm account deactivation'] -->

<!-- const resetForm = () => { -->
<!--  accountDataLocal.value = structuredClone(accountData) -->
<!-- } -->

<!-- const changeAvatar = file => { -->
<!--  const fileReader = new FileReader() -->
<!--  const { files } = file.target -->
<!--  if (files && files.length) { -->
<!--    fileReader.readAsDataURL(files[0]) -->
<!--    fileReader.onload = () => { -->
<!--      if (typeof fileReader.result === 'string') -->
<!--        accountDataLocal.value.avatarImg = fileReader.result -->
<!--    } -->
<!--  } -->
<!-- } -->

<!-- // reset avatar image -->
<!-- const resetAvatar = () => { -->
<!--  accountDataLocal.value.avatarImg = accountData.avatarImg -->
<!-- } -->

<!-- const timezones = [ -->
<!--  '(GMT-11:00) International Date Line West', -->
<!--  '(GMT-11:00) Midway Island', -->
<!--  '(GMT-10:00) Hawaii', -->
<!--  '(GMT-09:00) Alaska', -->
<!--  '(GMT-08:00) Pacific Time (US & Canada)', -->
<!--  '(GMT-08:00) Tijuana', -->
<!--  '(GMT-07:00) Arizona', -->
<!--  '(GMT-07:00) Chihuahua', -->
<!--  '(GMT-07:00) La Paz', -->
<!--  '(GMT-07:00) Mazatlan', -->
<!--  '(GMT-07:00) Mountain Time (US & Canada)', -->
<!--  '(GMT-06:00) Central America', -->
<!--  '(GMT-06:00) Central Time (US & Canada)', -->
<!--  '(GMT-06:00) Guadalajara', -->
<!--  '(GMT-06:00) Mexico City', -->
<!--  '(GMT-06:00) Monterrey', -->
<!--  '(GMT-06:00) Saskatchewan', -->
<!--  '(GMT-05:00) Bogota', -->
<!--  '(GMT-05:00) Eastern Time (US & Canada)', -->
<!--  '(GMT-05:00) Indiana (East)', -->
<!--  '(GMT-05:00) Lima', -->
<!--  '(GMT-05:00) Quito', -->
<!--  '(GMT-04:00) Atlantic Time (Canada)', -->
<!--  '(GMT-04:00) Caracas', -->
<!--  '(GMT-04:00) La Paz', -->
<!--  '(GMT-04:00) Santiago', -->
<!--  '(GMT-03:30) Newfoundland', -->
<!--  '(GMT-03:00) Brasilia', -->
<!--  '(GMT-03:00) Buenos Aires', -->
<!--  '(GMT-03:00) Georgetown', -->
<!--  '(GMT-03:00) Greenland', -->
<!--  '(GMT-02:00) Mid-Atlantic', -->
<!--  '(GMT-01:00) Azores', -->
<!--  '(GMT-01:00) Cape Verde Is.', -->
<!--  '(GMT+00:00) Casablanca', -->
<!--  '(GMT+00:00) Dublin', -->
<!--  '(GMT+00:00) Edinburgh', -->
<!--  '(GMT+00:00) Lisbon', -->
<!--  '(GMT+00:00) London', -->
<!-- ] -->

<!-- const currencies = [ -->
<!--  'USD', -->
<!--  'EUR', -->
<!--  'GBP', -->
<!--  'AUD', -->
<!--  'BRL', -->
<!--  'CAD', -->
<!--  'CNY', -->
<!--  'CZK', -->
<!--  'DKK', -->
<!--  'HKD', -->
<!--  'HUF', -->
<!--  'INR', -->
<!-- ] -->
<!-- </script> -->

<!-- <template> -->
<!--  <VRow> -->
<!--    <VCol cols="12"> -->
<!--      <VCard> -->
<!--        <VCardText class="d-flex"> -->
<!--          &lt;!&ndash; 👉 Avatar &ndash;&gt; -->
<!--          <VAvatar -->
<!--            rounded -->
<!--            size="100" -->
<!--            class="me-6" -->
<!--            :image="accountDataLocal.avatarImg" -->
<!--          /> -->

<!--          &lt;!&ndash; 👉 Upload Photo &ndash;&gt; -->
<!--          <form class="d-flex flex-column justify-center gap-4"> -->
<!--            <div class="d-flex flex-wrap gap-4"> -->
<!--              <VBtn -->
<!--                color="primary" -->
<!--                size="small" -->
<!--                @click="refInputEl?.click()" -->
<!--              > -->
<!--                <VIcon -->
<!--                  icon="tabler-cloud-upload" -->
<!--                  class="d-sm-none" -->
<!--                /> -->
<!--                <span class="d-none d-sm-block">Upload new photo</span> -->
<!--              </VBtn> -->

<!--              <input -->
<!--                ref="refInputEl" -->
<!--                type="file" -->
<!--                name="file" -->
<!--                accept=".jpeg,.png,.jpg,GIF" -->
<!--                hidden -->
<!--                @input="changeAvatar" -->
<!--              > -->

<!--              <VBtn -->
<!--                type="reset" -->
<!--                size="small" -->
<!--                color="secondary" -->
<!--                variant="tonal" -->
<!--                @click="resetAvatar" -->
<!--              > -->
<!--                <span class="d-none d-sm-block">Reset</span> -->
<!--                <VIcon -->
<!--                  icon="tabler-refresh" -->
<!--                  class="d-sm-none" -->
<!--                /> -->
<!--              </VBtn> -->
<!--            </div> -->

<!--            <p class="text-body-1 mb-0"> -->
<!--              Allowed JPG, GIF or PNG. Max size of 800K -->
<!--            </p> -->
<!--          </form> -->
<!--        </VCardText> -->

<!--        <VCardText class="pt-2"> -->
<!--          &lt;!&ndash; 👉 Form &ndash;&gt; -->
<!--          <VForm class="mt-3"> -->
<!--            <VRow> -->
<!--              &lt;!&ndash; 👉 First Name &ndash;&gt; -->
<!--              <VCol -->
<!--                md="6" -->
<!--                cols="12" -->
<!--              > -->
<!--                <AppTextField -->
<!--                  v-model="accountDataLocal.firstName" -->
<!--                  placeholder="John" -->
<!--                  label="First Name" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Last Name &ndash;&gt; -->
<!--              <VCol -->
<!--                md="6" -->
<!--                cols="12" -->
<!--              > -->
<!--                <AppTextField -->
<!--                  v-model="accountDataLocal.lastName" -->
<!--                  placeholder="Doe" -->
<!--                  label="Last Name" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Email &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppTextField -->
<!--                  v-model="accountDataLocal.email" -->
<!--                  label="E-mail" -->
<!--                  placeholder="johndoe@gmail.com" -->
<!--                  type="email" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Organization &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppTextField -->
<!--                  v-model="accountDataLocal.org" -->
<!--                  label="Organization" -->
<!--                  placeholder="Pixinvent" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Phone &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppTextField -->
<!--                  v-model="accountDataLocal.phone" -->
<!--                  label="Phone Number" -->
<!--                  placeholder="+1 (917) 543-9876" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Address &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppTextField -->
<!--                  v-model="accountDataLocal.address" -->
<!--                  label="Address" -->
<!--                  placeholder="123 Main St, New York, NY 10001" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 State &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppTextField -->
<!--                  v-model="accountDataLocal.state" -->
<!--                  label="State" -->
<!--                  placeholder="New York" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Zip Code &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppTextField -->
<!--                  v-model="accountDataLocal.zip" -->
<!--                  label="Zip Code" -->
<!--                  placeholder="10001" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Country &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppSelect -->
<!--                  v-model="accountDataLocal.country" -->
<!--                  label="Country" -->
<!--                  :items="['USA', 'Canada', 'UK', 'India', 'Australia']" -->
<!--                  placeholder="Select Country" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Language &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppSelect -->
<!--                  v-model="accountDataLocal.language" -->
<!--                  label="Language" -->
<!--                  placeholder="Select Language" -->
<!--                  :items="['English', 'Spanish', 'Arabic', 'Hindi', 'Urdu']" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Timezone &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppSelect -->
<!--                  v-model="accountDataLocal.timezone" -->
<!--                  label="Timezone" -->
<!--                  placeholder="Select Timezone" -->
<!--                  :items="timezones" -->
<!--                  :menu-props="{ maxHeight: 200 }" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Currency &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                md="6" -->
<!--              > -->
<!--                <AppSelect -->
<!--                  v-model="accountDataLocal.currency" -->
<!--                  label="Currency" -->
<!--                  placeholder="Select Currency" -->
<!--                  :items="currencies" -->
<!--                  :menu-props="{ maxHeight: 200 }" -->
<!--                /> -->
<!--              </VCol> -->

<!--              &lt;!&ndash; 👉 Form Actions &ndash;&gt; -->
<!--              <VCol -->
<!--                cols="12" -->
<!--                class="d-flex flex-wrap gap-4" -->
<!--              > -->
<!--                <VBtn>Save changes</VBtn> -->

<!--                <VBtn -->
<!--                  color="secondary" -->
<!--                  variant="tonal" -->
<!--                  type="reset" -->
<!--                  @click.prevent="resetForm" -->
<!--                > -->
<!--                  Cancel -->
<!--                </VBtn> -->
<!--              </VCol> -->
<!--            </VRow> -->
<!--          </VForm> -->
<!--        </VCardText> -->
<!--      </VCard> -->
<!--    </VCol> -->

<!--    <VCol cols="12"> -->
<!--      &lt;!&ndash; 👉 Delete Account &ndash;&gt; -->
<!--      <VCard title="Delete Account"> -->
<!--        <VCardText> -->
<!--          &lt;!&ndash; 👉 Checkbox and Button  &ndash;&gt; -->
<!--          <div> -->
<!--            <VCheckbox -->
<!--              v-model="isAccountDeactivated" -->
<!--              :rules="validateAccountDeactivation" -->
<!--              label="I confirm my account deactivation" -->
<!--            /> -->
<!--          </div> -->

<!--          <VBtn -->
<!--            :disabled="!isAccountDeactivated" -->
<!--            color="error" -->
<!--            class="mt-6" -->
<!--            @click="isConfirmDialogOpen = true" -->
<!--          > -->
<!--            Deactivate Account -->
<!--          </VBtn> -->
<!--        </VCardText> -->
<!--      </VCard> -->
<!--    </VCol> -->
<!--  </VRow> -->

<!--  &lt;!&ndash; Confirm Dialog &ndash;&gt; -->
<!--  <ConfirmDialog -->
<!--    v-model:is-dialog-visible="isConfirmDialogOpen" -->
<!--    confirmation-question="Are you sure you want to deactivate your account?" -->
<!--    confirm-title="Deactivated!" -->
<!--    confirm-msg="Your account has been deactivated successfully." -->
<!--    cancel-title="Cancelled" -->
<!--    cancel-msg="Account Deactivation Cancelled!" -->
<!--  /> -->
<!-- </template> -->
