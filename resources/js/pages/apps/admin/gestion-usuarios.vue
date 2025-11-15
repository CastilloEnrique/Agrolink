<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/axios'

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
    subject: 'Admin', // Para que solo admins la vean (si usas CASL)
    action: 'manage',
  },
})

// --- Estado ---
const usuarios = ref([]) // Para la tabla de usuarios
const roles = ref([]) // Para el dropdown de roles
const isLoading = ref(true)
const error = ref(null)

// --- Encabezados de la Tabla (ACTUALIZADOS) ---
const headers = [
  { title: 'Nombre Completo', key: 'nombre_completo', sortable: true },
  { title: 'Rol Asignado', key: 'rol_seleccionado_id', sortable: false, width: '250px' },
  { title: 'Estado', key: 'estado', sortable: true, width: '150px' }, // Columna de Estado
  { title: 'Acciones', key: 'acciones', sortable: false, align: 'center', width: '150px' }, // Columna de Acciones
  { title: 'Registrado', key: 'fecha_registro', sortable: true },
]

// --- Carga de Datos (ACTUALIZADA) ---
async function loadData() {
  isLoading.value = true
  error.value = null
  try {
    // 1. Carga la lista de usuarios
    const usuariosResponse = await api.get('/admin/usuarios')

    // ✅ Añadimos estados de carga individuales
    usuarios.value = usuariosResponse.data.map(u => ({
      ...u,
      isSavingRol: false,     // Estado de carga para el botón Guardar
      isSavingEstado: false, // Estado de carga para el Switch
    }))

    // 2. Carga la lista de roles disponibles
    const rolesResponse = await api.get('/admin/roles')

    roles.value = rolesResponse.data

  } catch (err) {
    console.error('Error al cargar datos de administración:', err)
    error.value = 'No se pudieron cargar los datos de usuarios.'
    alert(error.value)
  } finally {
    isLoading.value = false
  }
}

onMounted(loadData)

// --- ✅ LÓGICA DE ACTUALIZACIÓN DE ROL (CON BOTÓN GUARDAR) ---
async function actualizarRol(item) {
  // Ahora recibe el 'item' completo
  if (item.rol_principal_id === item.rol_seleccionado_id) return // No hay cambios

  item.isSavingRol = true // Inicia la carga

  try {
    const response = await api.post(`/admin/usuarios/${item.id}/actualizar-rol`, {
      // eslint-disable-next-line camelcase
      rol_id: item.rol_seleccionado_id, // Envía el rol seleccionado
    })

    // Si tiene éxito, actualiza el "rol principal" para que el botón se deshabilite
    item.rol_principal_id = item.rol_seleccionado_id

    alert(response.data.message || 'Rol actualizado')

  } catch (err) {
    console.error('Error al actualizar el rol:', err)
    alert(err.response?.data?.message || 'Error al actualizar el rol.')

    // Si falla, revertimos el dropdown al valor original
    // eslint-disable-next-line camelcase
    item.rol_seleccionado_id = item.rol_principal_id
  } finally {
    item.isSavingRol = false // Termina la carga
  }
}

// --- ✅ NUEVA FUNCIÓN PARA ACTUALIZAR ESTADO (ACTIVAR/DESACTIVAR) ---
async function actualizarEstado(item) {
  // El v-model del VSwitch ya actualizó el 'item.estado'
  // Determinamos el nuevo estado (solo puede ser activo o inactivo)
  const nuevoEstado = item.estado // 'activo' o 'inactivo'

  item.isSavingEstado = true // Inicia la carga del Switch

  try {
    const response = await api.post(`/admin/usuarios/${item.id}/actualizar-estado`, {
      estado: nuevoEstado,
    })

    alert(response.data.message || 'Estado actualizado')

  } catch (err) {
    console.error('Error al actualizar el estado:', err)
    alert(err.response?.data?.message || 'Error al actualizar el estado.')

    // Si falla, revertimos el switch al estado contrario
    item.estado = nuevoEstado === 'activo' ? 'inactivo' : 'activo'
  } finally {
    item.isSavingEstado = false // Termina la carga
  }
}
</script>

<template>
  <VContainer>
    <VCard>
      <VCardTitle>
        <h4 class="text-h4 py-2">
          👥 Panel de Administración - Gestión de Usuarios
        </h4>
      </VCardTitle>
      <VCardText>
        <!-- Alerta de Carga -->
        <VAlert
          v-if="isLoading"
          type="info"
          variant="tonal"
          title="Cargando lista de usuarios..."
        />

        <!-- Alerta de Error -->
        <VAlert
          v-else-if="error"
          type="error"
          variant="tonal"
          :title="error"
        />

        <!-- Tabla de Usuarios -->
        <VDataTable
          v-else
          :headers="headers"
          :items="usuarios"
          :items-per-page="10"
          class="elevation-1"
          no-data-text="No se encontraron usuarios."
        >
          <!-- Plantilla para el Nombre -->
          <template #item.nombre_completo="{ item }">
            <div class="d-flex align-center py-2">
              <VAvatar
                color="primary"
                variant="tonal"
                size="40"
                class="me-3"
              >
                {{ item.nombre_completo.substring(0, 2) }}
              </VAvatar>
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ item.nombre_completo }}</span>
                <span class="text-caption text-medium-emphasis">{{ item.correo }}</span>
              </div>
            </div>
          </template>

          <!-- Correo (oculto) -->
          <template #item.correo>
            <span />
          </template>

          <!-- ✅ --- TEMPLATE DE ESTADO (AHORA UN SWITCH) --- ✅ -->
          <template #item.estado="{ item }">
            <VSwitch
              v-model="item.estado"
              color="success"
              true-value="activo"
              false-value="inactivo"
              label=""
              hide-details
              :loading="item.isSavingEstado"
              :disabled="isLoading || item.isSavingEstado"
              @change="actualizarEstado(item)"
            />
          </template>

          <!-- ✅ --- TEMPLATE DE ROL (VSELECT) --- ✅ -->
          <!-- Ahora solo actualiza el v-model, no llama a la API -->
          <template #item.rol_seleccionado_id="{ item }">
            <VSelect
              v-model="item.rol_seleccionado_id"
              :items="roles"
              item-title="nombre"
              item-value="id"
              density="compact"
              variant="outlined"
              :disabled="isLoading"
              hide-details
              placeholder="Asignar rol"
            />
          </template>

          <!-- ✅ --- NUEVO TEMPLATE DE ACCIONES (BOTÓN GUARDAR) --- ✅ -->
          <template #item.acciones="{ item }">
            <VBtn
              color="success"
              variant="tonal"
              size="small"
              :disabled="item.rol_principal_id === item.rol_seleccionado_id || isLoading || item.isSavingRol"
              :loading="item.isSavingRol"
              @click="actualizarRol(item)"
            >
              Guardar
            </VBtn>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </VContainer>
</template>
