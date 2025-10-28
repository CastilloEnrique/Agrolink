<script setup>
import Shepherd from 'shepherd.js'
import { withQuery } from 'ufo'
import { useConfigStore } from '@core/stores/config'
import { useRouter } from 'vue-router'
import { defineAsyncComponent, ref, watch } from 'vue'

defineOptions({
  inheritAttrs: false,
})

const configStore = useConfigStore()
const isAppSearchBarVisible = ref(false)
const isLoading = ref(false) // Dejamos la lógica de carga, aunque la API sea simulada

// --- 💡 SUGERENCIAS ACTUALIZADAS PARA AGROLINK 💡 ---
const suggestionGroups = [
  {
    title: 'Consumidor',
    content: [
      {
        icon: 'tabler-store',
        title: 'Catálogo de Productos',
        url: { name: 'apps-consumidor-catalogo' },
      },
      {
        icon: 'tabler-shopping-cart',
        title: 'Mi Carrito',
        url: { name: 'apps-consumidor-carrito' },
      },
    ],
  },
  {
    title: 'Productor',
    content: [
      {
        icon: 'tabler-smart-home',
        title: 'Inicio (Dashboard)',
        url: { name: 'inicio' }, // Apunta a tu nuevo dashboard
      },
      {
        icon: 'tabler-user-circle',
        title: 'Mi Perfil',
        url: { name: 'apps-productor-perfil' },
      },
      {
        icon: 'tabler-upload',
        title: 'Publicar Producto',
        url: { name: 'apps-productor-publicar' },
      },
      {
        icon: 'tabler-packages',
        title: 'Mis Productos',
        url: { name: 'apps-productor-mis-productos' },
      },
    ],
  },
]

// 👉 No Data suggestion (Actualizado)
const noDataSuggestions = [
  {
    title: 'Catálogo de Productos',
    icon: 'tabler-store',
    url: { name: 'apps-consumidor-catalogo' },
  },
  {
    title: 'Mi Perfil',
    icon: 'tabler-user-circle',
    url: { name: 'apps-productor-perfil' },
  },
  {
    title: 'Publicar Producto',
    icon: 'tabler-upload',
    url: { name: 'apps-productor-publicar' },
  },
]

const searchQuery = ref('')
const router = useRouter()
const searchResult = ref([])

// --- NOTA IMPORTANTE SOBRE LA BÚSQUEDA DINÁMICA ---
// La plantilla llama a una API de búsqueda falsa: /app-bar/search
// Como esa ruta no existe en tu backend (routes/api.php), la búsqueda
// dinámica (cuando escribes) no funcionará hasta que se implemente.
// Por ahora, solo las sugerencias estáticas (de arriba) funcionarán.
const fetchResults = async () => {
  isLoading.value = true

  // 💡 Simulación: Filtra las sugerencias estáticas en lugar de llamar a la API
  const allSuggestions = [
    ...suggestionGroups[0].content,
    ...suggestionGroups[1].content,
  ]

  if (searchQuery.value) {
    searchResult.value = [{
      title: 'Resultados',
      children: allSuggestions.filter(item =>
        item.title.toLowerCase().includes(searchQuery.value.toLowerCase()),
      ),
    }]
  } else {
    searchResult.value = [] // Vaciar si no hay búsqueda
  }

  // Simular carga
  setTimeout(() => {
    isLoading.value = false
  }, 300)
}


watch(searchQuery, fetchResults)

const closeSearchBar = () => {
  isAppSearchBarVisible.value = false
  searchQuery.value = ''
}

const redirectToSuggestedPage = selected => {
  router.push(selected.url)
  closeSearchBar()
}

const LazyAppBarSearch = defineAsyncComponent(() => import('@core/components/AppBarSearch.vue'))
</script>

<template>
  <div
    class="d-flex align-center cursor-pointer"
    v-bind="$attrs"
    style="user-select: none;"
    @click="isAppSearchBarVisible = !isAppSearchBarVisible"
  >
    <!-- 👉 Search Trigger button -->
    <IconBtn @click="Shepherd.activeTour?.cancel()">
      <VIcon icon="tabler-search" />
    </IconBtn>

    <span
      v-if="configStore.appContentLayoutNav === 'vertical'"
      class="d-none d-md-flex align-center text-disabled ms-2"
      @click="Shepherd.activeTour?.cancel()"
    >
      <span class="me-2">Buscar</span>
      <span class="meta-key">&#8984;K</span>
    </span>
  </div>

  <!-- 👉 App Bar Search -->
  <LazyAppBarSearch
    v-model:is-dialog-visible="isAppSearchBarVisible"
    :search-results="searchResult"
    :is-loading="isLoading"
    @search="searchQuery = $event"
  >
    <!-- suggestion -->
    <template #suggestions>
      <VCardText class="app-bar-search-suggestions pa-12">
        <VRow v-if="suggestionGroups">
          <VCol
            v-for="suggestion in suggestionGroups"
            :key="suggestion.title"
            cols="12"
            sm="6"
          >
            <p
              class="custom-letter-spacing text-disabled text-uppercase py-2 px-4 mb-0"
              style="font-size: 0.75rem; line-height: 0.875rem;"
            >
              {{ suggestion.title }}
            </p>
            <VList class="card-list">
              <VListItem
                v-for="item in suggestion.content"
                :key="item.title"
                class="app-bar-search-suggestion mx-4 mt-2"
                @click="redirectToSuggestedPage(item)"
              >
                <VListItemTitle>{{ item.title }}</VListItemTitle>
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="20"
                    class="me-n1"
                  />
                </template>

              </vlistitem>
            </VList>
          </VCol>
        </VRow>
      </VCardText>
    </template>

    <!-- no data suggestion -->
    <template #noDataSuggestion>
      <div class="mt-9">
        <span class="d-flex justify-center text-disabled mb-2">Sugerencias rápidas:</span>
        <h6
          v-for="suggestion in noDataSuggestions"
          :key="suggestion.title"
          class="app-bar-search-suggestion text-h6 font-weight-regular cursor-pointer py-2 px-4"
          @click="redirectToSuggestedPage(suggestion)"
        >
          <VIcon
            size="20"
            :icon="suggestion.icon"
            class="me-2"
          />
          <span>{{ suggestion.title }}</span>
        </h6>
      </div>
    </template>

    <!-- search result -->
    <template #searchResult="{ item }">
      <VListSubheader class="text-disabled custom-letter-spacing font-weight-regular ps-4">
        {{ item.title }}
      </VListSubheader>
      <VListItem
        v-for="list in item.children"
        :key="list.title"
        :to="list.url"
        @click="closeSearchBar"
      >
        <template #prepend>
          <VIcon
            size="20"
            :icon="list.icon"
            class="me-n1"
          />
        </template>
        <template #append>
          <VIcon
            size="20"
            icon="tabler-corner-down-left"
            class="enter-icon flip-in-rtl"
          />
        </template>
        <VListItemTitle>
          {{ list.title }}
        </VListItemTitle>
      </VListItem>
    </template>
  </LazyAppBarSearch>
</template>

<style lang="scss">
@use "@styles/variables/vuetify.scss";

.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  font-size: 0.8125rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.app-bar-search-dialog {
  .custom-letter-spacing {
    letter-spacing: 0.8px;
  }

  .card-list {
    --v-card-list-gap: 8px;
  }
}
</style>






















<!-- <script setup> -->
<!-- import Shepherd from 'shepherd.js' -->
<!-- import { withQuery } from 'ufo' -->
<!-- import { useConfigStore } from '@core/stores/config' -->

<!-- defineOptions({ -->
<!--  inheritAttrs: false, -->
<!-- }) -->

<!-- const configStore = useConfigStore() -->
<!-- const isAppSearchBarVisible = ref(false) -->
<!-- const isLoading = ref(false) -->

<!-- // 👉 Default suggestions -->
<!-- const suggestionGroups = [ -->
<!--  { -->
<!--    title: 'Popular Searches', -->
<!--    content: [ -->
<!--      { -->
<!--        icon: 'tabler-chart-bar', -->
<!--        title: 'Analytics', -->
<!--        url: { name: 'dashboards-analytics' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-chart-donut-3', -->
<!--        title: 'CRM', -->
<!--        url: { name: 'dashboards-crm' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-shopping-cart', -->
<!--        title: 'eCommerce', -->
<!--        url: { name: 'dashboards-ecommerce' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-truck', -->
<!--        title: 'Logistics', -->
<!--        url: { name: 'dashboards-logistics' }, -->
<!--      }, -->
<!--    ], -->
<!--  }, -->
<!--  { -->
<!--    title: 'Apps & Pages', -->
<!--    content: [ -->
<!--      { -->
<!--        icon: 'tabler-calendar', -->
<!--        title: 'Calendar', -->
<!--        url: { name: 'apps-calendar' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-lock', -->
<!--        title: 'Roles & Permissions', -->
<!--        url: { name: 'apps-roles' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-settings', -->
<!--        title: 'Account Settings', -->
<!--        url: { -->
<!--          name: 'pages-account-settings-tab', -->
<!--          params: { tab: 'account' }, -->
<!--        }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-copy', -->
<!--        title: 'Dialog Examples', -->
<!--        url: { name: 'pages-dialog-examples' }, -->
<!--      }, -->
<!--    ], -->
<!--  }, -->
<!--  { -->
<!--    title: 'User Interface', -->
<!--    content: [ -->
<!--      { -->
<!--        icon: 'tabler-typography', -->
<!--        title: 'Typography', -->
<!--        url: { name: 'pages-typography' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-menu-2', -->
<!--        title: 'Accordion', -->
<!--        url: { name: 'components-expansion-panel' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-info-triangle', -->
<!--        title: 'Alert', -->
<!--        url: { name: 'components-alert' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-checkbox', -->
<!--        title: 'Cards', -->
<!--        url: { name: 'pages-cards-card-basic' }, -->
<!--      }, -->
<!--    ], -->
<!--  }, -->
<!--  { -->
<!--    title: 'Forms & Tables', -->
<!--    content: [ -->
<!--      { -->
<!--        icon: 'tabler-circle-dot', -->
<!--        title: 'Radio', -->
<!--        url: { name: 'forms-radio' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-file-invoice', -->
<!--        title: 'Form Layouts', -->
<!--        url: { name: 'forms-form-layouts' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-table', -->
<!--        title: 'Table', -->
<!--        url: { name: 'tables-data-table' }, -->
<!--      }, -->
<!--      { -->
<!--        icon: 'tabler-edit', -->
<!--        title: 'Editor', -->
<!--        url: { name: 'forms-editors' }, -->
<!--      }, -->
<!--    ], -->
<!--  }, -->
<!-- ] -->

<!-- // 👉 No Data suggestion -->
<!-- const noDataSuggestions = [ -->
<!--  { -->
<!--    title: 'Analytics', -->
<!--    icon: 'tabler-chart-bar', -->
<!--    url: { name: 'dashboards-analytics' }, -->
<!--  }, -->
<!--  { -->
<!--    title: 'CRM', -->
<!--    icon: 'tabler-chart-donut-3', -->
<!--    url: { name: 'dashboards-crm' }, -->
<!--  }, -->
<!--  { -->
<!--    title: 'eCommerce', -->
<!--    icon: 'tabler-shopping-cart', -->
<!--    url: { name: 'dashboards-ecommerce' }, -->
<!--  }, -->
<!-- ] -->

<!-- const searchQuery = ref('') -->
<!-- const router = useRouter() -->
<!-- const searchResult = ref([]) -->

<!-- const fetchResults = async () => { -->
<!--  isLoading.value = true -->

<!--  const { data } = await useApi(withQuery('/app-bar/search', { q: searchQuery.value })) -->

<!--  searchResult.value = data.value -->

<!--  // ℹ️ simulate loading: we have used setTimeout for better user experience your can remove it -->
<!--  setTimeout(() => { -->
<!--    isLoading.value = false -->
<!--  }, 500) -->
<!-- } -->

<!-- watch(searchQuery, fetchResults) -->

<!-- const closeSearchBar = () => { -->
<!--  isAppSearchBarVisible.value = false -->
<!--  searchQuery.value = '' -->
<!-- } -->

<!-- const redirectToSuggestedPage = selected => { -->
<!--  router.push(selected.url) -->
<!--  closeSearchBar() -->
<!-- } -->

<!-- const LazyAppBarSearch = defineAsyncComponent(() => import('@core/components/AppBarSearch.vue')) -->
<!-- </script> -->

<!-- <template> -->
<!--  <div -->
<!--    class="d-flex align-center cursor-pointer" -->
<!--    v-bind="$attrs" -->
<!--    style="user-select: none;" -->
<!--    @click="isAppSearchBarVisible = !isAppSearchBarVisible" -->
<!--  > -->
<!--    &lt;!&ndash; 👉 Search Trigger button &ndash;&gt; -->
<!--    &lt;!&ndash; close active tour while opening search bar using icon &ndash;&gt; -->
<!--    <IconBtn @click="Shepherd.activeTour?.cancel()"> -->
<!--      <VIcon icon="tabler-search" /> -->
<!--    </IconBtn> -->

<!--    <span -->
<!--      v-if="configStore.appContentLayoutNav === 'vertical'" -->
<!--      class="d-none d-md-flex align-center text-disabled ms-2" -->
<!--      @click="Shepherd.activeTour?.cancel()" -->
<!--    > -->
<!--      <span class="me-2">Search</span> -->
<!--      <span class="meta-key">&#8984;K</span> -->
<!--    </span> -->
<!--  </div> -->

<!--  &lt;!&ndash; 👉 App Bar Search &ndash;&gt; -->
<!--  <LazyAppBarSearch -->
<!--    v-model:is-dialog-visible="isAppSearchBarVisible" -->
<!--    :search-results="searchResult" -->
<!--    :is-loading="isLoading" -->
<!--    @search="searchQuery = $event" -->
<!--  > -->
<!--    &lt;!&ndash; suggestion &ndash;&gt; -->
<!--    <template #suggestions> -->
<!--      <VCardText class="app-bar-search-suggestions pa-12"> -->
<!--        <VRow v-if="suggestionGroups"> -->
<!--          <VCol -->
<!--            v-for="suggestion in suggestionGroups" -->
<!--            :key="suggestion.title" -->
<!--            cols="12" -->
<!--            sm="6" -->
<!--          > -->
<!--            <p -->
<!--              class="custom-letter-spacing text-disabled text-uppercase py-2 px-4 mb-0" -->
<!--              style="font-size: 0.75rem; line-height: 0.875rem;" -->
<!--            > -->
<!--              {{ suggestion.title }} -->
<!--            </p> -->
<!--            <VList class="card-list"> -->
<!--              <VListItem -->
<!--                v-for="item in suggestion.content" -->
<!--                :key="item.title" -->
<!--                class="app-bar-search-suggestion mx-4 mt-2" -->
<!--                @click="redirectToSuggestedPage(item)" -->
<!--              > -->
<!--                <VListItemTitle>{{ item.title }}</VListItemTitle> -->
<!--                <template #prepend> -->
<!--                  <VIcon -->
<!--                    :icon="item.icon" -->
<!--                    size="20" -->
<!--                    class="me-n1" -->
<!--                  /> -->
<!--                </template> -->
<!--              </VListItem> -->
<!--            </VList> -->
<!--          </VCol> -->
<!--        </VRow> -->
<!--      </VCardText> -->
<!--    </template> -->

<!--    &lt;!&ndash; no data suggestion &ndash;&gt; -->
<!--    <template #noDataSuggestion> -->
<!--      <div class="mt-9"> -->
<!--        <span class="d-flex justify-center text-disabled mb-2">Try searching for</span> -->
<!--        <h6 -->
<!--          v-for="suggestion in noDataSuggestions" -->
<!--          :key="suggestion.title" -->
<!--          class="app-bar-search-suggestion text-h6 font-weight-regular cursor-pointer py-2 px-4" -->
<!--          @click="redirectToSuggestedPage(suggestion)" -->
<!--        > -->
<!--          <VIcon -->
<!--            size="20" -->
<!--            :icon="suggestion.icon" -->
<!--            class="me-2" -->
<!--          /> -->
<!--          <span>{{ suggestion.title }}</span> -->
<!--        </h6> -->
<!--      </div> -->
<!--    </template> -->

<!--    &lt;!&ndash; search result &ndash;&gt; -->
<!--    <template #searchResult="{ item }"> -->
<!--      <VListSubheader class="text-disabled custom-letter-spacing font-weight-regular ps-4"> -->
<!--        {{ item.title }} -->
<!--      </VListSubheader> -->
<!--      <VListItem -->
<!--        v-for="list in item.children" -->
<!--        :key="list.title" -->
<!--        :to="list.url" -->
<!--        @click="closeSearchBar" -->
<!--      > -->
<!--        <template #prepend> -->
<!--          <VIcon -->
<!--            size="20" -->
<!--            :icon="list.icon" -->
<!--            class="me-n1" -->
<!--          /> -->
<!--        </template> -->
<!--        <template #append> -->
<!--          <VIcon -->
<!--            size="20" -->
<!--            icon="tabler-corner-down-left" -->
<!--            class="enter-icon flip-in-rtl" -->
<!--          /> -->
<!--        </template> -->
<!--        <VListItemTitle> -->
<!--          {{ list.title }} -->
<!--        </VListItemTitle> -->
<!--      </VListItem> -->
<!--    </template> -->
<!--  </LazyAppBarSearch> -->
<!-- </template> -->

<!-- <style lang="scss"> -->
<!-- @use "@styles/variables/vuetify.scss"; -->

<!-- .meta-key { -->
<!--  border: thin solid rgba(var(&#45;&#45;v-border-color), var(&#45;&#45;v-border-opacity)); -->
<!--  border-radius: 6px; -->
<!--  block-size: 1.5625rem; -->
<!--  font-size: 0.8125rem; -->
<!--  line-height: 1.3125rem; -->
<!--  padding-block: 0.125rem; -->
<!--  padding-inline: 0.25rem; -->
<!-- } -->

<!-- .app-bar-search-dialog { -->
<!--  .custom-letter-spacing { -->
<!--    letter-spacing: 0.8px; -->
<!--  } -->

<!--  .card-list { -->
<!--    &#45;&#45;v-card-list-gap: 8px; -->
<!--  } -->
<!-- } -->
<!-- </style> -->
