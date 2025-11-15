<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/axios'
// eslint-disable-next-line no-restricted-imports
import VueApexCharts from 'vue3-apexcharts' // 💡 1. IMPORTAMOS LA LIBRERÍA DE GRÁFICOS
import { useTheme } from 'vuetify'

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
  },
})

// --- Estado General ---
const stats = ref(null)
const precios = ref(null)
const isLoading = ref(true)
const error = ref(null)
const userData = ref(JSON.parse(localStorage.getItem('usuario') || '{}'))
const vuetifyTheme = useTheme()

// --- 💡 2. ESTADO Y OPCIONES PARA LOS GRÁFICOS ---
const lineChartSeries = ref([])
const donutChartSeries = ref([])

const lineChartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors

  return {
    chart: { type: 'line', toolbar: { show: false } },
    xaxis: {
      categories: [],
      labels: { style: { colors: currentTheme.onSurface } },
    },
    yaxis: {
      labels: {
        style: { colors: currentTheme.onSurface },
        formatter: value => `Q${value.toFixed(0)}`,
      },
    },
    stroke: { curve: 'smooth' },
    grid: { borderColor: 'rgba(var(--v-border-color), var(--v-border-opacity))' },
    colors: [currentTheme.primary],
    tooltip: { theme: vuetifyTheme.current.value.dark ? 'dark' : 'light' },
  }
})

const donutChartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors

  return {
    chart: { type: 'donut' },
    labels: [],
    legend: { labels: { colors: currentTheme.onSurface } },
    plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total' } } } } },
    tooltip: { theme: vuetifyTheme.current.value.dark ? 'dark' : 'light' },
  }
})

// --- Carga de Datos ---
onMounted(async () => {
  isLoading.value = true
  try {
    const response = await api.get('/dashboard/stats')

    stats.value = response.data
    precios.value = response.data.precios_referencia

    // --- 💡 3. POBLAR LOS GRÁFICOS CON DATOS DE LA API ---
    if (response.data.grafico_ventas) {
      lineChartSeries.value = [{ name: 'Ventas (Q)', data: response.data.grafico_ventas.data }]

      // Actualizamos las 'options' del gráfico de líneas
      lineChartOptions.value = {
        ...lineChartOptions.value,
        xaxis: {
          ...lineChartOptions.value.xaxis,
          categories: response.data.grafico_ventas.labels,
        },
      }
    }

    if (response.data.grafico_top_productos) {
      donutChartSeries.value = response.data.grafico_top_productos.data

      // Actualizamos las 'options' del gráfico de dona
      donutChartOptions.value = {
        ...donutChartOptions.value,
        labels: response.data.grafico_top_productos.labels,
      }
    }

  } catch (err) {
    console.error('Error al cargar el dashboard:', err)
    error.value = 'No se pudieron cargar las estadísticas.'
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <VContainer>
    <!-- 💡 --- MENSAJE DE BIENVENIDA MEJORADO --- 💡 -->
    <VRow class="mb-6">
      <VCol cols="12">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar
              rounded
              size="60"
              color="primary"
              variant="tonal"
            >
              <VIcon
                icon="tabler-plant-2"
                size="30"
              />
            </VAvatar>
            <div>
              <h4 class="text-h4">
                ¡Bienvenido/a, {{ userData.primer_nombre || 'Usuario' }}! 👋
              </h4>
              <p
                v-if="stats"
                class="text-medium-emphasis mb-0"
              >
                Aquí está tu resumen de hoy como {{ stats.rol }}.
              </p>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <!-- --- FIN DEL MENSAJE DE BIENVENIDA --- -->

    <!-- Alerta de Carga -->
    <VAlert
      v-if="isLoading"
      type="info"
      variant="tonal"
      title="Cargando estadísticas..."
    />

    <!-- Alerta de Error -->
    <VAlert
      v-else-if="error"
      type="error"
      variant="tonal"
      :title="error"
    />

    <!-- Tarjetas de Estadísticas (KPIs) -->
    <VRow v-if="stats && stats.tarjetas">
      <VCol
        v-for="tarjeta in stats.tarjetas"
        :key="tarjeta.titulo"
        cols="12"
        sm="6"
        md="3"
      >
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar
              :color="tarjeta.color || 'primary'"
              variant="tonal"
              size="40"
              class="me-4"
            >
              <VIcon :icon="tarjeta.icono" />
            </VAvatar>
            <div>
              <span class="text-caption">{{ tarjeta.titulo }}</span>
              <h5 class="text-h5">
                {{ tarjeta.valor }}
              </h5>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- --- 👇 4. NUEVA SECCIÓN DE GRÁFICOS 👇 --- -->
    <VRow
      v-if="stats && (stats.rol === 'admin' || stats.rol === 'productor')"
      class="mt-6"
    >
      <!-- Gráfico de Líneas (Ventas) -->
      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardTitle>Ventas Últimos 7 Días</VCardTitle>
          <VCardText>
            <VueApexCharts
              type="line"
              height="300"
              :options="lineChartOptions"
              :series="lineChartSeries"
            />
          </VCardText>
        </VCard>
      </VCol>

      <!-- Gráfico de Dona (Top Productos) -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardTitle>Top 5 Productos (Uds.)</VCardTitle>
          <VCardText>
            <VueApexCharts
              type="donut"
              height="300"
              :options="donutChartOptions"
              :series="donutChartSeries"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <!-- --- FIN DE LA SECCIÓN DE GRÁFICOS --- -->

    <!-- Precios de Referencia (Esto ya lo teníamos) -->
    <VRow
      v-if="precios"
      class="mt-6"
    >
      <VCol cols="12">
        <h5 class="text-h5 mb-4">
          Precios de Referencia (Promedio de Mercado)
        </h5>
      </VCol>
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
                {{ item.precio > 0 ? `Q${item.precio.toFixed(2)}` : 'N/A' }}
              </h5>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>
