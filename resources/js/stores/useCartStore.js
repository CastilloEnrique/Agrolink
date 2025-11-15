// import { defineStore } from 'pinia'
// import api from '@/services/axios'
//
// export const useCartStore = defineStore('cart', {
//   state: () => ({
//     cartItems: [],
//   }),
//
//   getters: {
//     cartItemCount: state => state.cartItems.length,
//
//     cartTotal: state =>
//       state.cartItems.reduce(
//         (t, p) => t + (p.price * p.quantity),
//         0,
//       ),
//   },
//
//   actions: {
//     // 🔵 Obtener carrito del backend
//     // async fetchCart() {
//     //   const res = await api.get('/carrito')
//     //
//     //   this.cartItems = Object.values(res.data.items || [])
//     // },
//     async fetchCart() {
//       const res = await api.get('/carrito')
//
//       this.cartItems = Object.values(res.data.items || []).map(item => ({
//         id: item.id,
//         name: item.nombre,
//         price: Number(item.precio),     // ← BACKEND ENVÍA "precio"
//         // eslint-disable-next-line camelcase
//         image_url: item.image,          // ← BACKEND ENVÍA "image"
//         producer: item.productor,
//         quantity: item.cantidad,
//         subtotal: Number(item.precio) * Number(item.cantidad),
//       }))
//     },
//
//
//
//     // 🟢 Agregar producto
//     async addToCart(productId, quantity = 1) {
//       await api.post('/carrito/agregar', {
//         // eslint-disable-next-line camelcase
//         producto_id: Number(productId),   //  🔥 ARREGLA EL ERROR
//         cantidad: Number(quantity),
//       })
//       await this.fetchCart()
//     },
//
//     // async addToCart(productId, quantity = 1) {
//     //   await api.post('/carrito/agregar', {
//     //     // eslint-disable-next-line camelcase
//     //     producto_id: productId,
//     //     cantidad: quantity,
//     //   })
//     //   await this.fetchCart()
//     // },
//
//     // 🟡 Actualizar cantidad
//     async updateQuantity(productId, quantity) {
//       await api.put('/carrito/actualizar-cantidad', {
//         // eslint-disable-next-line camelcase
//         producto_id: productId,
//         cantidad: quantity,
//       })
//       await this.fetchCart()
//     },
//
//     // 🔴 Eliminar
//     async removeFromCart(productId) {
//       await api.delete('/carrito/eliminar', {
//         // eslint-disable-next-line camelcase
//         data: { producto_id: productId },
//       })
//       await this.fetchCart()
//     },
//
//     // ⚪ Limpiar (solo UI)
//     clearCart() {
//       this.cartItems = []
//     },
//   },
// })


import { defineStore } from 'pinia'
import api from '@/services/axios'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
  }),

  getters: {
    cartItems: state => state.items,

    // Suma la cantidad total de items
    cartItemCount: state => state.items.reduce((sum, i) => sum + i.quantity, 0),

    // Suma el subtotal que ya calculó el backend
    cartTotal: state => state.items.reduce((sum, i) => sum + i.subtotal, 0),
  },

  actions: {
    /**
     * ✅ Carga el carrito desde el Backend (AHORA USA LA TABLA NUEVA)
     */
    async fetchCart() {
      try {
        const res = await api.get('/carrito')


        // El backend ya devuelve los datos formateados y listos
        this.items = res.data.items
      } catch (e) {
        console.error("Error al cargar el carrito", e)
        this.items = [] // Limpiar en caso de error
      }
    },

    /**
     * ✅ Agrega un item llamando al Backend
     */
    async addToCart(productId, qty) {
      try {
        await api.post('/carrito/agregar', {
          // eslint-disable-next-line camelcase
          producto_id: Number(productId),
          cantidad: Number(qty),
        })

        // Después de agregar, volvemos a cargar todo el carrito desde la BD
        await this.fetchCart()
      } catch (e) {
        console.error("Error al agregar al carrito", e)
        alert("Error al agregar producto: " + (e.response?.data?.message || 'Error desconocido'))
      }
    },

    /**
     * ✅ Actualiza la cantidad (llamando al Backend)
     */
    async updateQuantity(productId, qty) {
      // 'productId' es el ID del PRODUCTO
      try {
        await api.put('/carrito/actualizar-cantidad', {
          // eslint-disable-next-line camelcase
          producto_id: productId,
          cantidad: qty,
        })

        // Actualizamos el estado local para respuesta rápida
        const item = this.items.find(i => i.id === productId)
        if (item) {
          item.quantity = qty
          item.subtotal = qty * item.price
        }
      } catch (e) {
        console.error("Error al actualizar cantidad", e)
        await this.fetchCart() // Recargar todo si falla
      }
    },

    /**
     * ✅ Elimina un item llamando al Backend
     */
    async removeFromCart(productId) {
      // 'productId' es el ID del PRODUCTO
      try {
        this.items = this.items.filter(i => i.id !== productId) // Optimista
        await api.delete('/carrito/eliminar', {
          // eslint-disable-next-line camelcase
          data: { producto_id: productId },
        })
      } catch (e) {
        console.error("Error al eliminar del carrito", e)
        await this.fetchCart() // Revertir si falla
      }
    },

    /**
     * ⚪ Limpia el carrito (solo local)
     */
    clearCart() {
      // Opcional: podrías llamar a un endpoint /carrito/limpiar
      // que haga CarritoItem::where('usuario_id', auth()->id())->delete();
      this.items = []
    },
  },
})
