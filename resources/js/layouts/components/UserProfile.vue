<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useRouter } from 'vue-router'
import { useAbility } from '@casl/vue'
import api from '@/services/axios' // 💡 Importar axios

const router = useRouter()
const ability = useAbility()

// (Asumiendo que guardas los datos del usuario en localStorage al hacer login)
const userData = ref(JSON.parse(localStorage.getItem('usuario') || '{}')) // 💡 Leer 'usuario'

// Helper simple para borrar cookies
function removeCookie(name) {
  document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;'
}

// --- 💡 FUNCIÓN DE LOGOUT CORREGIDA Y COMPLETA ---
const logout = async () => {
  try {
    // 1. Llama a la API de Laravel
    await api.post('/logout')
    console.log('Logout API call successful')
  } catch (error) {
    // Continuar con la limpieza aunque falle la API
    console.error('Error calling logout API (continuing frontend cleanup):', error)
  } finally {
    // 2. LIMPIAR LOCALSTORAGE (¡El paso que faltaba!)
    localStorage.removeItem('token')
    localStorage.removeItem('usuario') // Limpiar 'usuario'
    localStorage.removeItem('userAbilities')

    // 3. LIMPIAR COOKIES
    removeCookie('accessToken')
    removeCookie('userData')
    removeCookie('userAbilityRules')

    // 4. RESETEAR HABILIDADES CASL
    ability.update([])

    // 5. REDIRIGIR AL LOGIN
    router.replace({ name: 'login' })
  }
}

// --- 💡 LISTA DE ENLACES ACTUALIZADA ---
// Reemplazamos los enlaces de demo por los de nuestra app
const userProfileList = [
  { type: 'divider' },
  {
    type: 'navItem',
    icon: 'tabler-user-circle',
    title: 'Mi Perfil',
    to: { name: 'apps-productor-perfil' },

    // Opcional: solo mostrar si tiene permisos
    // meta: {
    //   action: 'manage',
    //   subject: 'Productor-Perfil',
    // },
  },
  {
    type: 'navItem',
    icon: 'tabler-packages',
    title: 'Mis Productos',
    to: { name: 'apps-productor-mis-productos' },

    // meta: {
    //   action: 'read',
    //   subject: 'Productor-Productos',
    // },
  },
  {
    type: 'navItem',
    icon: 'tabler-shopping-cart',
    title: 'Mi Carrito',
    to: { name: 'apps-consumidor-carrito' },
  },
  { type: 'divider' },

  // Dejamos el de Configuración (Settings) por si acaso
  {
    type: 'navItem',
    icon: 'tabler-settings',
    title: 'Configuración',
    to: {
      name: 'pages-account-settings-tab',
      params: { tab: 'account' },
    },
  },
]
</script>

<template>
  <VBadge
    v-if="userData"
    dot
    bordered
    location="bottom right"
    offset-x="1"
    offset-y="2"
    color="success"
  >
    <VAvatar
      size="38"
      class="cursor-pointer"
      :color="!(userData && userData.foto_perfil_url) ? 'primary' : undefined"
      :variant="!(userData && userData.foto_perfil_url) ? 'tonal' : undefined"
    >
      <VImg
        v-if="userData && userData.foto_perfil_url"
        :src="userData.foto_perfil_url"
      />
      <VIcon
        v-else
        icon="tabler-user"
      />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="240"
        location="bottom end"
        offset="12px"
      >
        <VList>
          <VListItem>
            <div class="d-flex gap-2 align-center">
              <VListItemAction>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                  bordered
                >
                  <VAvatar
                    :color="!(userData && userData.foto_perfil_url) ? 'primary' : undefined"
                    :variant="!(userData && userData.foto_perfil_url) ? 'tonal' : undefined"
                  >
                    <VImg
                      v-if="userData && userData.foto_perfil_url"
                      :src="userData.foto_perfil_url"
                    />
                    <VIcon
                      v-else
                      icon="tabler-user"
                    />
                  </VAvatar>
                </VBadge>
              </VListItemAction>

              <div>
                <h6 class="text-h6 font-weight-medium">
                  <!-- 💡 Usar primer_nombre y primer_apellido del localStorage 'usuario' -->
                  {{ userData.primer_nombre }} {{ userData.primer_apellido }}
                </h6>
                <VListItemSubtitle class="text-capitalize text-disabled">
                  {{ userData.role || (userData.roles && userData.roles[0] ? userData.roles[0].nombre : 'Usuario') }}
                </VListItemSubtitle>
              </div>
            </div>
          </VListItem>

          <PerfectScrollbar :options="{ wheelPropagation: false }">
            <template
              v-for="item in userProfileList"
              :key="item.title"
            >
              <VListItem
                v-if="item.type === 'navItem'"
                :to="item.to"
              >
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="22"
                  />
                </template>

                <VListItemTitle>{{ item.title }}</VListItemTitle>

                <template
                  v-if="item.badgeProps"
                  #append
                >
                  <VBadge
                    rounded="sm"
                    class="me-3"
                    v-bind="item.badgeProps"
                  />
                </template>
              </VListItem>

              <VDivider
                v-else
                class="my-2"
              />
            </template>

            <div class="px-4 py-2">
              <VBtn
                block
                size="small"
                color="error"
                append-icon="tabler-logout"
                @click="logout"
              >
                Cerrar Sesión
              </VBtn>
            </div>
          </PerfectScrollbar>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>









<!-- <script setup> -->
<!-- import { PerfectScrollbar } from 'vue3-perfect-scrollbar' -->

<!-- const router = useRouter() -->
<!-- const ability = useAbility() -->

<!-- // TODO: Get type from backend -->
<!-- const userData = useCookie('userData') -->

<!-- const logout = async () => { -->

<!--  // Remove "accessToken" from cookie -->
<!--  useCookie('accessToken').value = null -->

<!--  // Remove "userData" from cookie -->
<!--  userData.value = null -->

<!--  // Redirect to login page -->
<!--  await router.push('/login') -->

<!--  // ℹ️ We had to remove abilities in then block because if we don't nav menu items mutation is visible while redirecting user to login page -->

<!--  // Remove "userAbilities" from cookie -->
<!--  useCookie('userAbilityRules').value = null -->

<!--  // Reset ability to initial ability -->
<!--  ability.update([]) -->
<!-- } -->

<!-- const userProfileList = [ -->
<!--  { type: 'divider' }, -->
<!--  { -->
<!--    type: 'navItem', -->
<!--    icon: 'tabler-user', -->
<!--    title: 'Profile', -->
<!--    to: { -->
<!--      name: 'apps-user-view-id', -->
<!--      params: { id: 21 }, -->
<!--    }, -->
<!--  }, -->
<!--  { -->
<!--    type: 'navItem', -->
<!--    icon: 'tabler-settings', -->
<!--    title: 'Settings', -->
<!--    to: { -->
<!--      name: 'pages-account-settings-tab', -->
<!--      params: { tab: 'account' }, -->
<!--    }, -->
<!--  }, -->
<!--  { -->
<!--    type: 'navItem', -->
<!--    icon: 'tabler-file-dollar', -->
<!--    title: 'Billing Plan', -->
<!--    to: { -->
<!--      name: 'pages-account-settings-tab', -->
<!--      params: { tab: 'billing-plans' }, -->
<!--    }, -->
<!--    badgeProps: { -->
<!--      color: 'error', -->
<!--      content: '4', -->
<!--    }, -->
<!--  }, -->
<!--  { type: 'divider' }, -->
<!--  { -->
<!--    type: 'navItem', -->
<!--    icon: 'tabler-currency-dollar', -->
<!--    title: 'Pricing', -->
<!--    to: { name: 'pages-pricing' }, -->
<!--  }, -->
<!--  { -->
<!--    type: 'navItem', -->
<!--    icon: 'tabler-question-mark', -->
<!--    title: 'FAQ', -->
<!--    to: { name: 'pages-faq' }, -->
<!--  }, -->
<!-- ] -->
<!-- </script> -->

<!-- <template> -->
<!--  <VBadge -->
<!--    v-if="userData" -->
<!--    dot -->
<!--    bordered -->
<!--    location="bottom right" -->
<!--    offset-x="1" -->
<!--    offset-y="2" -->
<!--    color="success" -->
<!--  > -->
<!--    <VAvatar -->
<!--      size="38" -->
<!--      class="cursor-pointer" -->
<!--      :color="!(userData && userData.avatar) ? 'primary' : undefined" -->
<!--      :variant="!(userData && userData.avatar) ? 'tonal' : undefined" -->
<!--    > -->
<!--      <VImg -->
<!--        v-if="userData && userData.avatar" -->
<!--        :src="userData.avatar" -->
<!--      /> -->
<!--      <VIcon -->
<!--        v-else -->
<!--        icon="tabler-user" -->
<!--      /> -->

<!--      &lt;!&ndash; SECTION Menu &ndash;&gt; -->
<!--      <VMenu -->
<!--        activator="parent" -->
<!--        width="240" -->
<!--        location="bottom end" -->
<!--        offset="12px" -->
<!--      > -->
<!--        <VList> -->
<!--          <VListItem> -->
<!--            <div class="d-flex gap-2 align-center"> -->
<!--              <VListItemAction> -->
<!--                <VBadge -->
<!--                  dot -->
<!--                  location="bottom right" -->
<!--                  offset-x="3" -->
<!--                  offset-y="3" -->
<!--                  color="success" -->
<!--                  bordered -->
<!--                > -->
<!--                  <VAvatar -->
<!--                    :color="!(userData && userData.avatar) ? 'primary' : undefined" -->
<!--                    :variant="!(userData && userData.avatar) ? 'tonal' : undefined" -->
<!--                  > -->
<!--                    <VImg -->
<!--                      v-if="userData && userData.avatar" -->
<!--                      :src="userData.avatar" -->
<!--                    /> -->
<!--                    <VIcon -->
<!--                      v-else -->
<!--                      icon="tabler-user" -->
<!--                    /> -->
<!--                  </VAvatar> -->
<!--                </VBadge> -->
<!--              </VListItemAction> -->

<!--              <div> -->
<!--                <h6 class="text-h6 font-weight-medium"> -->
<!--                  {{ userData.fullName || userData.username }} -->
<!--                </h6> -->
<!--                <VListItemSubtitle class="text-capitalize text-disabled"> -->
<!--                  {{ userData.role }} -->
<!--                </VListItemSubtitle> -->
<!--              </div> -->
<!--            </div> -->
<!--          </VListItem> -->

<!--          <PerfectScrollbar :options="{ wheelPropagation: false }"> -->
<!--            <template -->
<!--              v-for="item in userProfileList" -->
<!--              :key="item.title" -->
<!--            > -->
<!--              <VListItem -->
<!--                v-if="item.type === 'navItem'" -->
<!--                :to="item.to" -->
<!--              > -->
<!--                <template #prepend> -->
<!--                  <VIcon -->
<!--                    :icon="item.icon" -->
<!--                    size="22" -->
<!--                  /> -->
<!--                </template> -->

<!--                <VListItemTitle>{{ item.title }}</VListItemTitle> -->

<!--                <template -->
<!--                  v-if="item.badgeProps" -->
<!--                  #append -->
<!--                > -->
<!--                  <VBadge -->
<!--                    rounded="sm" -->
<!--                    class="me-3" -->
<!--                    v-bind="item.badgeProps" -->
<!--                  /> -->
<!--                </template> -->
<!--              </VListItem> -->

<!--              <VDivider -->
<!--                v-else -->
<!--                class="my-2" -->
<!--              /> -->
<!--            </template> -->

<!--            <div class="px-4 py-2"> -->
<!--              <VBtn -->
<!--                block -->
<!--                size="small" -->
<!--                color="error" -->
<!--                append-icon="tabler-logout" -->
<!--                @click="logout" -->
<!--              > -->
<!--                Logout -->
<!--              </VBtn> -->
<!--            </div> -->
<!--          </PerfectScrollbar> -->
<!--        </VList> -->
<!--      </VMenu> -->
<!--      &lt;!&ndash; !SECTION &ndash;&gt; -->
<!--    </VAvatar> -->
<!--  </VBadge> -->
<!-- </template> -->
