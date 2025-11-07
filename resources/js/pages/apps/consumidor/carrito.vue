<script setup>
import { useCartStore } from '@/stores/useCartStore'
import { ref } from 'vue'
import api from '@/services/axios' // 💡 1. Importamos 'api'
import { useRouter } from 'vue-router' // 💡 2. Importamos el router para redirigir

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true, // 💡 3. Protegemos la ruta, solo usuarios logueados
  },
})

const cartStore = useCartStore()
const router = useRouter() // 💡 4. Inicializamos el router
const isProcessing = ref(false)

// 💡 5. FUNCIÓN 'CONSOLIDAR PEDIDO' ACTUALIZADA
const consolidateOrder = async () => {
  if (cartStore.cartItemCount === 0) {
    alert('El carrito está vacío.')

    return
  }

  isProcessing.value = true

  try {
    // a. Preparamos los datos que espera tu PedidoController
    // (Un objeto con una llave "items" que es un array)
    const payload = {
      items: cartStore.cartItems.map(item => ({
        id: item.id,
        quantity: item.quantity,
      })),
    }

    // b. Llamamos a la nueva API de Laravel
    const response = await api.post('/pedidos', payload)

    // c. Mostramos éxito, limpiamos el carrito y redirigimos
    alert(response.data.message || '¡Pedido realizado exitosamente!')
    cartStore.clearCart()

    // d. Redirigimos al inicio después del éxito
    router.push({ name: 'inicio' })

  } catch (error) {
    // e. Manejamos cualquier error del backend
    console.error('Error al consolidar el pedido:', error.response || error)
    alert('Error al procesar el pedido: ' + (error.response?.data?.message || 'Intenta de nuevo más tarde.'))
  } finally {
    // f. Detenemos la carga (ya sea éxito o error)
    isProcessing.value = false
  }
}

// (Función sin cambios, pero con una pequeña mejora para evitar errores si se borra el número)
const handleQuantityChange = (id, event) => {
  let newQuantity = parseInt(event.target.value)

  // Si el usuario borra el campo (NaN) o pone 0, lo forzamos a 1
  if (isNaN(newQuantity) || newQuantity < 1) {
    newQuantity = 1
    event.target.value = 1 // Corregimos la vista
  }

  cartStore.updateQuantity(id, newQuantity)
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h4 class="text-h4 mb-4">
        🛒 Mi Carrito de Compras ({{ cartStore.cartItemCount }} productos)
      </h4>
    </VCol>

    <VCol
      cols="12"
      md="8"
    >
      <VCard v-if="cartStore.cartItemCount === 0">
        <VCardText>
          <VAlert
            type="info"
            variant="tonal"
          >
            Tu carrito está vacío. ¡Añade productos del catálogo!
          </VAlert>
          <VBtn
            color="primary"
            class="mt-4"
            :to="{ name: 'apps-consumidor-catalogo' }"
          >
            Ir al Catálogo
          </VBtn>
        </VCardText>
      </VCard>

      <VCard
        v-for="item in cartStore.cartItems"
        :key="item.id"
        class="mb-4"
      >
        <VCardText>
          <div class="d-flex align-center gap-4">
            <VAvatar
              size="60"
              rounded="lg"
              :image="item.image_url || 'https://placehold.co/60x60/f2f2f2/7F8C8D?text=IMG'"
              alt="Producto"
            />

            <div class="flex-grow-1">
              <h6 class="text-h6 mb-1">
                {{ item.name }}
              </h6>
              <span class="text-caption text-medium-emphasis">Productor: {{ item.producer }}</span>
              <p class="text-body-2 text-primary mt-1">
                Q{{ item.price.toFixed(2) }} / {{ item.unit }}
              </p>
            </div>

            <div class="d-flex flex-column align-end">
              <AppTextField
                :model-value="item.quantity"
                type="number"
                min="1"
                density="compact"
                style="max-width: 60px;"
                class="mb-2"
                @input="handleQuantityChange(item.id, $event)"
              />
              <VBtn
                icon
                size="small"
                variant="tonal"
                color="error"
                @click="cartStore.removeFromCart(item.id)"
              >
                <VIcon
                  size="18"
                  icon="tabler-trash"
                />
              </VBtn>
            </div>

            <div class="ms-4 text-end">
              <h6 class="text-h6 text-success">
                Q{{ item.subtotal.toFixed(2) }}
              </h6>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-if="cartStore.cartItemCount > 0"
      cols="12"
      md="4"
    >
      <VCard title="Resumen del Pedido">
        <VCardText>
          <VDivider class="my-2" />
          <div class="d-flex justify-space-between text-h6 text-primary">
            <span>Total a Pagar:</span>
            <span>Q{{ cartStore.cartTotal.toFixed(2) }}</span>
          </div>
        </VCardText>

        <VCardActions class="d-flex flex-column gap-2">
          <VBtn
            block
            color="success"
            size="large"
            :loading="isProcessing"
            @click="consolidateOrder"
          >
            Consolidar Pedido
          </VBtn>
          <VBtn
            block
            variant="tonal"
            color="error"
            @click="cartStore.clearCart"
          >
            Vaciar Carrito
          </VBtn>
        </VCardActions>
      </VCard>
    </VCol>
  </VRow>
</template>












<!-- import { VDivider } from 'vuetify/components' -->

<!-- <script setup> -->
<!-- import { useCartStore } from '@/stores/useCartStore' // 💡 Importar el store -->
<!-- import { ref } from 'vue' -->

<!-- definePage({ -->
<!--  meta: { -->
<!--    layout: 'default', -->
<!--  }, -->
<!-- }) -->

<!-- const cartStore = useCartStore() -->
<!-- const isProcessing = ref(false) -->

<!-- // Función placeholder para consolidar el pedido (Backend no implementado) -->
<!-- const consolidateOrder = () => { -->
<!--  if (cartStore.cartItemCount === 0) { -->
<!--    alert('El carrito está vacío.') -->
<!--    -->
<!--    return -->
<!--  } -->

<!--  isProcessing.value = true -->

<!--  // 💡 Lógica Futura: Llamar a la API de Laravel para crear el pedido (Order/Invoice) -->
<!--  setTimeout(() => { -->
<!--    alert(`✅ Pedido consolidado exitosamente por Q${cartStore.cartTotal.toFixed(2)}. ¡Pronto nos contactaremos!`) -->
<!--    cartStore.clearCart() // Limpiar después del éxito -->
<!--    isProcessing.value = false -->
<!--  }, 1500) -->
<!-- } -->

<!-- // Función para ajustar la cantidad (llamada desde la plantilla) -->
<!-- const handleQuantityChange = (id, event) => { -->
<!--  const newQuantity = parseInt(event.target.value) -->

<!--  cartStore.updateQuantity(id, newQuantity) -->
<!-- } -->
<!-- </script> -->

<!-- <template> -->
<!--  <VRow> -->
<!--    <VCol cols="12"> -->
<!--      <h4 class="text-h4 mb-4"> -->
<!--        🛒 Mi Carrito de Compras ({{ cartStore.cartItemCount }} productos) -->
<!--      </h4> -->
<!--    </VCol> -->

<!--    <VCol -->
<!--      cols="12" -->
<!--      md="8" -->
<!--    > -->
<!--      <VCard v-if="cartStore.cartItemCount === 0"> -->
<!--        <VCardText> -->
<!--          <VAlert -->
<!--            type="info" -->
<!--            variant="tonal" -->
<!--          > -->
<!--            Tu carrito está vacío. ¡Añade productos del catálogo! -->
<!--          </VAlert> -->
<!--          <VBtn -->
<!--            color="primary" -->
<!--            class="mt-4" -->
<!--            :to="{ name: 'apps-consumidor-catalogo' }" -->
<!--          > -->
<!--            Ir al Catálogo -->
<!--          </VBtn> -->
<!--        </VCardText> -->
<!--      </VCard> -->

<!--      <VCard -->
<!--        v-for="item in cartStore.cartItems" -->
<!--        :key="item.id" -->
<!--        class="mb-4" -->
<!--      > -->
<!--        <VCardText> -->
<!--          <div class="d-flex align-center gap-4"> -->
<!--            <VAvatar -->
<!--              size="60" -->
<!--              rounded="lg" -->
<!--              :image="item.image_url || 'https://placehold.co/60x60/f2f2f2/7F8C8D?text=IMG'" -->
<!--              alt="Producto" -->
<!--            /> -->

<!--            <div class="flex-grow-1"> -->
<!--              <h6 class="text-h6 mb-1"> -->
<!--                {{ item.name }} -->
<!--              </h6> -->
<!--              <span class="text-caption text-medium-emphasis">Productor: {{ item.producer }}</span> -->
<!--              <p class="text-body-2 text-primary mt-1"> -->
<!--                Q{{ item.price.toFixed(2) }} / {{ item.unit }} -->
<!--              </p> -->
<!--            </div> -->

<!--            <div class="d-flex flex-column align-end"> -->
<!--              <AppTextField -->
<!--                :model-value="item.quantity" -->
<!--                type="number" -->
<!--                min="1" -->
<!--                density="compact" -->
<!--                style="max-width: 60px;" -->
<!--                class="mb-2" -->
<!--                @input="handleQuantityChange(item.id, $event)" -->
<!--              /> -->
<!--              <VBtn -->
<!--                icon -->
<!--                size="small" -->
<!--                variant="tonal" -->
<!--                color="error" -->
<!--                @click="cartStore.removeFromCart(item.id)" -->
<!--              > -->
<!--                <VIcon -->
<!--                  size="18" -->
<!--                  icon="tabler-trash" -->
<!--                /> -->
<!--              </VBtn> -->
<!--            </div> -->

<!--            <div class="ms-4 text-end"> -->
<!--              <h6 class="text-h6 text-success"> -->
<!--                Q{{ item.subtotal.toFixed(2) }} -->
<!--              </h6> -->
<!--            </div> -->
<!--          </div> -->
<!--        </VCardText> -->
<!--      </VCard> -->
<!--    </VCol> -->

<!--    <VCol -->
<!--      v-if="cartStore.cartItemCount > 0" -->
<!--      cols="12" -->
<!--      md="4" -->
<!--    > -->
<!--      <VCard title="Resumen del Pedido"> -->
<!--        <VCardText> -->
<!--          <VDivider class="my-2" /> -->
<!--          <div class="d-flex justify-space-between text-h6 text-primary"> -->
<!--            <span>Total a Pagar:</span> -->
<!--            <span>Q{{ cartStore.cartTotal.toFixed(2) }}</span> -->
<!--          </div> -->
<!--        </VCardText> -->

<!--        <VCardActions class="d-flex flex-column gap-2"> -->
<!--          <VBtn -->
<!--            block -->
<!--            color="success" -->
<!--            size="large" -->
<!--            :loading="isProcessing" -->
<!--            @click="consolidateOrder" -->
<!--          > -->
<!--            Consolidar Pedido -->
<!--          </VBtn> -->
<!--          <VBtn -->
<!--            block -->
<!--            variant="tonal" -->
<!--            color="error" -->
<!--            @click="cartStore.clearCart" -->
<!--          > -->
<!--            Vaciar Carrito -->
<!--          </VBtn> -->
<!--        </VCardActions> -->
<!--      </VCard> -->
<!--    </VCol> -->
<!--  </VRow> -->
<!-- </template> -->
