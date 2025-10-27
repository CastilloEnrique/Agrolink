import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {
  // 💡 Estado: El carrito cargado desde localStorage o vacío
  const items = ref(JSON.parse(localStorage.getItem('agrolink_cart') || '[]'))

  // 💡 Getters
  const cartItemCount = computed(() => items.value.length)

  const cartItems = computed(() => {
    // Calcula los subtotales para la vista
    return items.value.map(item => ({
      ...item,
      subtotal: item.price * item.quantity,
    }))
  })

  const cartTotal = computed(() => {
    return cartItems.value.reduce((total, item) => total + item.subtotal, 0)
  })

  // 💡 Actions: Persistencia y Modificación
  const saveCartToStorage = () => {
    localStorage.setItem('agrolink_cart', JSON.stringify(items.value))
  }

  const addToCart = (product, quantity = 1) => {
    const existingItem = items.value.find(item => item.id === product.id)

    if (existingItem) {
      existingItem.quantity += quantity
    } else {
      items.value.push({
        id: product.id,
        name: product.nombre,
        producer: product.productor,
        // eslint-disable-next-line camelcase
        producer_id: product.productor_id,
        price: product.precio_referencia,
        unit: product.unidad_medida,
        // eslint-disable-next-line camelcase
        image_url: product.imagen_url,
        quantity: quantity,
      })
    }
    saveCartToStorage()
  }

  const updateQuantity = (id, newQuantity) => {
    const item = items.value.find(item => item.id === id)
    if (item && newQuantity > 0) {
      item.quantity = newQuantity
      saveCartToStorage()
    } else if (item && newQuantity <= 0) {
      // Si la cantidad es cero o menos, eliminar
      removeFromCart(id)
    }
  }

  const removeFromCart = id => {
    items.value = items.value.filter(item => item.id !== id)
    saveCartToStorage()
  }

  const clearCart = () => {
    items.value = []
    saveCartToStorage()
  }

  return {
    items,
    cartItemCount,
    cartItems,
    cartTotal,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart,
  }
})
