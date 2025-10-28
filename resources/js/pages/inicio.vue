<!-- <script setup> -->
<!-- import { ref, onMounted } from 'vue' -->
<!-- import { useRouter } from 'vue-router' -->

<!-- // Define la página y sus permisos si son necesarios -->
<!-- definePage({ -->
<!--  meta: { -->
<!--    // Si esta página requiere que el usuario esté logueado: -->
<!--    requiresAuth: true, -->
<!--    layout: 'default', // Usa el layout principal -->
<!--    // Puedes añadir reglas CASL si aplican a esta página específica -->
<!--    // action: 'read', -->
<!--    // subject: 'Dashboard', -->
<!--  }, -->
<!-- }) -->

<!-- // (Asumiendo que guardas los datos del usuario en localStorage al hacer login) -->
<!-- const userData = ref(JSON.parse(localStorage.getItem('userData') || '{}')) -->
<!-- const router = useRouter() // Inicializar router -->

<!-- // Estado para los precios (simulado) -->
<!-- const precios = ref({ -->
<!--  cafe: { nombre: 'Café (Quintal)', precio: 'Consultando...', icono: 'tabler-coffee' }, -->
<!--  frijol: { nombre: 'Frijol (Quintal)', precio: 'Consultando...', icono: 'tabler-leaf' }, -->
<!--  maiz: { nombre: 'Maíz (Quintal)', precio: 'Consultando...', icono: 'tabler-corn' }, -->
<!-- }) -->

<!-- // -&#45;&#45; Lógica para obtener precios (Simulada con API Gemini) -&#45;&#45; -->
<!-- const fetchPrecios = async () => { -->
<!--  const apiKey = "" -->
<!--  const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}` -->

<!--  // Helper function con reintentos -->
<!--  const fetchWithRetry = async (productName, retries = 3, delay = 1000) => { -->
<!--    const userQuery = `Cuál es el precio actual de referencia por quintal de ${productName} en Guatemala hoy? Responde solo el precio aproximado en Quetzales (ej: Q350.00). Si no lo sabes, di 'No disponible'.` -->

<!--    const payload = { -->
<!--      contents: [{ parts: [{ text: userQuery }] }], -->
<!--      tools: [{ "google_search": {} }], -->
<!--      systemInstruction: { parts: [{ text: "Proporciona solo el precio o 'No disponible'." }] }, -->
<!--    } -->

<!--    for (let i = 0; i < retries; i++) { -->
<!--      try { -->
<!--        const response = await fetch(apiUrl, { -->
<!--          method: 'POST', -->
<!--          headers: { 'Content-Type': 'application/json' }, -->
<!--          body: JSON.stringify(payload), -->
<!--        }) -->

<!--        if (!response.ok) { -->
<!--          if (response.status === 429 && i < retries - 1) { -->
<!--            console.warn(`Throttled fetching price for ${productName}, retrying in ${delay / 1000}s...`) -->
<!--            await new Promise(resolve => setTimeout(resolve, delay)) -->
<!--            delay *= 2 -->
<!--            continue -->
<!--          } -->
<!--          throw new Error(`HTTP error! status: ${response.status}`) -->
<!--        } -->

<!--        const result = await response.json() -->
<!--        const text = result.candidates?.[0]?.content?.parts?.[0]?.text -->
<!--        const priceMatch = text?.match(/Q\s?[\d,]+\.\d{2}/) -->

<!--        // Devolver precio si se encuentra, o el texto si no, o 'No disponible' -->
<!--        return priceMatch ? priceMatch[0] : (text || 'No disponible') -->

<!--      } catch (error) { -->
<!--        console.error(`Error fetching price for ${productName} (attempt ${i + 1}):`, error) -->

<!--        // Si es el último intento, devolver error -->
<!--        if (i === retries - 1) return 'Error al consultar' -->

<!--        // Esperar antes del siguiente reintento -->
<!--        await new Promise(resolve => setTimeout(resolve, delay)) -->
<!--        delay *= 2 -->
<!--      } -->
<!--    } -->
<!--    -->
<!--    return 'Error al consultar' -->
<!--  } -->

<!--  precios.value.cafe.precio = await fetchWithRetry('café pergamino') -->
<!--  precios.value.frijol.precio = await fetchWithRetry('frijol negro') -->
<!--  precios.value.maiz.precio = await fetchWithRetry('maíz blanco') -->
<!-- } -->


<!-- onMounted(() => { -->
<!--  fetchPrecios() -->
<!-- }) -->

<!-- // -&#45;&#45; Funciones de Navegación -&#45;&#45; -->
<!-- const goToCatalog = () => router.push({ path: '/apps/consumidor/catalogo' }) -->
<!-- const goToMyProducts = () => router.push({ path: '/apps/productor/mis-productos' }) -->
<!-- const goToPublish = () => router.push({ path: '/apps/productor/publicar' }) -->
<!-- </script> -->

<!-- <template> -->
<!--  <div> -->
<!--    &lt;!&ndash; Saludo al Usuario &ndash;&gt; -->
<!--    <VCard class="mb-6"> -->
<!--      <VCardText> -->
<!--        <h3 class="text-h3"> -->
<!--          ¡Bienvenido/a, {{ userData.primer_nombre || 'Usuario' }}! 👋 -->
<!--        </h3> -->
<!--        <p class="text-body-1"> -->
<!--          Aquí tienes un resumen rápido de los precios de referencia hoy. -->
<!--        </p> -->
<!--      </VCardText> -->
<!--    </VCard> -->

<!--    &lt;!&ndash; Sección de Precios &ndash;&gt; -->
<!--    <VRow> -->
<!--      <VCol -->
<!--        v-for="(item, key) in precios" -->
<!--        :key="key" -->
<!--        cols="12" -->
<!--        md="4" -->
<!--      > -->
<!--        <VCard elevation="2"> -->
<!--          <VCardText class="d-flex align-center gap-4"> -->
<!--            <VAvatar -->
<!--              :icon="item.icono" -->
<!--              color="primary" -->
<!--              variant="tonal" -->
<!--              size="40" -->
<!--              rounded -->
<!--            /> -->
<!--            <div> -->
<!--              <span class="text-caption">{{ item.nombre }}</span> -->
<!--              <h5 class="text-h5"> -->
<!--                {{ item.precio }} -->
<!--              </h5> -->
<!--            </div> -->
<!--          </VCardText> -->
<!--        </VCard> -->
<!--      </VCol> -->
<!--    </VRow> -->

<!--    &lt;!&ndash; Accesos Rápidos &ndash;&gt; -->
<!--    <VRow class="mt-6"> -->
<!--      <VCol cols="12"> -->
<!--        <h5 class="text-h5 mb-4"> -->
<!--          Accesos Rápidos -->
<!--        </h5> -->
<!--      </VCol> -->
<!--      <VCol -->
<!--        cols="12" -->
<!--        md="4" -->
<!--      > -->
<!--        <VBtn -->
<!--          block -->
<!--          color="secondary" -->
<!--          @click="goToCatalog" -->
<!--        > -->
<!--          <VIcon -->
<!--            start -->
<!--            icon="tabler-store" -->
<!--          /> Ver Catálogo -->
<!--        </VBtn> -->
<!--      </VCol> -->
<!--      <VCol -->
<!--        cols="12" -->
<!--        md="4" -->
<!--      > -->
<!--        <VBtn -->
<!--          block -->
<!--          color="info" -->
<!--          @click="goToMyProducts" -->
<!--        > -->
<!--          <VIcon -->
<!--            start -->
<!--            icon="tabler-packages" -->
<!--          /> Mis Productos -->
<!--        </VBtn> -->
<!--      </VCol> -->
<!--      <VCol -->
<!--        cols="12" -->
<!--        md="4" -->
<!--      > -->
<!--        <VBtn -->
<!--          block -->
<!--          color="warning" -->
<!--          @click="goToPublish" -->
<!--        > -->
<!--          <VIcon -->
<!--            start -->
<!--            icon="tabler-upload" -->
<!--          /> Publicar Producto -->
<!--        </VBtn> -->
<!--      </VCol> -->
<!--    </VRow> -->
<!--  </div> -->
<!-- </template> -->

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

// Define la página y sus permisos
definePage({
  meta: {
    requiresAuth: true,
    layout: 'default',
  },
})

// 💡 CORRECCIÓN: Lee 'usuario' (que es lo que guarda login.vue) en lugar de 'userData'
const userData = ref(JSON.parse(localStorage.getItem('usuario') || '{}'))
const router = useRouter() // Inicializar router

// --- 💡 PRECIOS FIJOS (PLACEHOLDER) ---
const precios = ref({
  cafe: { nombre: 'Café (Quintal)', precio: 'Q320.50 (aprox)', icono: 'tabler-coffee' },
  frijol: { nombre: 'Frijol (Quintal)', precio: 'Q580.00 (aprox)', icono: 'tabler-leaf' },
  maiz: { nombre: 'Maíz (Quintal)', precio: 'Q190.00 (aprox)', icono: 'tabler-corn' },
})

// --- Funciones de Navegación ---
const goToCatalog = () => router.push({ path: '/apps/consumidor/catalogo' })
const goToMyProducts = () => router.push({ path: '/apps/productor/mis-productos' })
const goToPublish = () => router.push({ path: '/apps/productor/publicar' })
</script>

<template>
  <div>
    <!-- Saludo al Usuario -->
    <VCard class="mb-6">
      <VCardText>
        <h3 class="text-h3">
          <!-- Ahora 'userData.primer_nombre' SÍ tendrá datos -->
          ¡Bienvenido/a, {{ userData.primer_nombre || 'Usuario' }}! 👋
        </h3>
        <p class="text-body-1">
          Aquí tienes un resumen rápido de los precios de referencia hoy.
        </p>
      </VCardText>
    </VCard>

    <!-- Sección de Precios -->
    <VRow>
      <VCol
        v-for="(item, key) in precios"
        :key="key"
        cols="12"
        md="4"
      >
        <VCard elevation="2">
          <VCardText class="d-flex align-center gap-4">
            <VAvatar
              :icon="item.icono"
              color="primary"
              variant="tonal"
              size="40"
              rounded
            />
            <div>
              <span class="text-caption">{{ item.nombre }}</span>
              <h5 class="text-h5">
                {{ item.precio }}
              </h5>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Accesos Rápidos -->
    <VRow class="mt-6">
      <VCol cols="12">
        <h5 class="text-h5 mb-4">
          Accesos Rápidos
        </h5>
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VBtn
          block
          color="secondary"
          @click="goToCatalog"
        >
          <VIcon
            start
            icon="tabler-store"
          /> Ver Catálogo
        </VBtn>
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VBtn
          block
          color="info"
          @click="goToMyProducts"
        >
          <VIcon
            start
            icon="tabler-packages"
          /> Mis Productos
        </VBtn>
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VBtn
          block
          color="warning"
          @click="goToPublish"
        >
          <VIcon
            start
            icon="tabler-upload"
          /> Publicar Producto
        </VBtn>
      </VCol>
    </VRow>
  </div>
</template>



<!-- <script setup> -->
<!-- import { ref, onMounted } from 'vue' -->
<!-- import { useRouter } from 'vue-router' -->

<!-- // Define la página y sus permisos -->
<!-- definePage({ -->
<!--  meta: { -->
<!--    requiresAuth: true, -->
<!--    layout: 'default', -->
<!--  }, -->
<!-- }) -->

<!-- // (Asumiendo que guardas los datos del usuario en localStorage al hacer login) -->
<!-- const userData = ref(JSON.parse(localStorage.getItem('userData') || '{}')) -->
<!-- const router = useRouter() // Inicializar router -->

<!-- // -&#45;&#45; 💡 PRECIOS FIJOS (PLACEHOLDER) -&#45;&#45; -->
<!-- // Quitamos la llamada a la API de Gemini que daba error 403 -->
<!-- // y usamos precios de ejemplo. -->
<!-- const precios = ref({ -->
<!--  cafe: { nombre: 'Café (Quintal)', precio: 'Q320.50 (aprox)', icono: 'tabler-coffee' }, -->
<!--  frijol: { nombre: 'Frijol (Quintal)', precio: 'Q580.00 (aprox)', icono: 'tabler-leaf' }, -->
<!--  maiz: { nombre: 'Maíz (Quintal)', precio: 'Q190.00 (aprox)', icono: 'tabler-corn' }, -->
<!-- }) -->

<!-- // -&#45;&#45; Funciones de Navegación -&#45;&#45; -->
<!-- // Usamos router.push con path porque los nombres pueden variar -->
<!-- const goToCatalog = () => router.push({ path: '/apps/consumidor/catalogo' }) -->
<!-- const goToMyProducts = () => router.push({ path: '/apps/productor/mis-productos' }) -->
<!-- const goToPublish = () => router.push({ path: '/apps/productor/publicar' }) -->
<!-- </script> -->

<!-- <template> -->
<!--  <div> -->
<!--    &lt;!&ndash; Saludo al Usuario &ndash;&gt; -->
<!--    <VCard class="mb-6"> -->
<!--      <VCardText> -->
<!--        <h3 class="text-h3"> -->
<!--          ¡Bienvenido/a, {{ userData.primer_nombre || 'Usuario' }}! 👋 -->
<!--        </h3> -->
<!--        <p class="text-body-1"> -->
<!--          Aquí tienes un resumen rápido de los precios de referencia hoy. -->
<!--        </p> -->
<!--      </VCardText> -->
<!--    </VCard> -->

<!--    &lt;!&ndash; Sección de Precios &ndash;&gt; -->
<!--    <VRow> -->
<!--      <VCol -->
<!--        v-for="(item, key) in precios" -->
<!--        :key="key" -->
<!--        cols="12" -->
<!--        md="4" -->
<!--      > -->
<!--        <VCard elevation="2"> -->
<!--          <VCardText class="d-flex align-center gap-4"> -->
<!--            <VAvatar -->
<!--              :icon="item.icono" -->
<!--              color="primary" -->
<!--              variant="tonal" -->
<!--              size="40" -->
<!--              rounded -->
<!--            /> -->
<!--            <div> -->
<!--              <span class="text-caption">{{ item.nombre }}</span> -->
<!--              <h5 class="text-h5"> -->
<!--                {{ item.precio }} -->
<!--              </h5> -->
<!--            </div> -->
<!--          </VCardText> -->
<!--        </VCard> -->
<!--      </VCol> -->
<!--    </VRow> -->

<!--    &lt;!&ndash; Accesos Rápidos &ndash;&gt; -->
<!--    <VRow class="mt-6"> -->
<!--      <VCol cols="12"> -->
<!--        <h5 class="text-h5 mb-4"> -->
<!--          Accesos Rápidos -->
<!--        </h5> -->
<!--      </VCol> -->
<!--      <VCol -->
<!--        cols="12" -->
<!--        md="4" -->
<!--      > -->
<!--        <VBtn -->
<!--          block -->
<!--          color="secondary" -->
<!--          @click="goToCatalog" -->
<!--        > -->
<!--          <VIcon -->
<!--            start -->
<!--            icon="tabler-store" -->
<!--          /> Ver Catálogo -->
<!--        </VBtn> -->
<!--      </VCol> -->
<!--      <VCol -->
<!--        cols="12" -->
<!--        md="4" -->
<!--      > -->
<!--        <VBtn -->
<!--          block -->
<!--          color="info" -->
<!--          @click="goToMyProducts" -->
<!--        > -->
<!--          <VIcon -->
<!--            start -->
<!--            icon="tabler-packages" -->
<!--          /> Mis Productos -->
<!--        </VBtn> -->
<!--      </VCol> -->
<!--      <VCol -->
<!--        cols="12" -->
<!--        md="4" -->
<!--      > -->
<!--        <VBtn -->
<!--          block -->
<!--          color="warning" -->
<!--          @click="goToPublish" -->
<!--        > -->
<!--          <VIcon -->
<!--            start -->
<!--            icon="tabler-upload" -->
<!--          /> Publicar Producto -->
<!--        </VBtn> -->
<!--      </VCol> -->
<!--    </VRow> -->
<!--  </div> -->
<!-- </template> -->

