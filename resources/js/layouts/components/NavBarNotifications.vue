<script setup>
import { ref, onMounted } from 'vue' // 💡 Importar onMounted
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar5 from '@images/avatars/avatar-5.png'
import paypal from '@images/cards/paypal-rounded.png'

// --- 💡 LÓGICA DE BIENVENIDA 💡 ---
// Cargar datos del usuario que se guardaron en el registro
const userData = JSON.parse(localStorage.getItem('usuario') || '{}')
const welcomeMessage = `¡Bienvenido/a, ${userData.primer_nombre || 'Usuario'}! 🎉`

const welcomeNotification = {
  id: 0, // ID especial para la bienvenida
  // Usar la foto de perfil del registro o una por defecto
  img: userData.foto_perfil_url || avatar3,
  title: welcomeMessage,
  subtitle: 'Gracias por unirte a Agrolink.',
  time: 'Justo ahora',
  isSeen: false, // Marcar como no leída
}

// --- FIN LÓGICA DE BIENVENIDA ---


// Notificaciones estáticas de demostración (las que ya tenías)
const notifications = ref([
  {
    id: 1,
    img: avatar4,
    title: 'Congratulation Flora! 🎉',
    subtitle: 'Won the monthly best seller badge',
    time: 'Today',
    isSeen: true,
  },
  {
    id: 2,
    text: 'Tom Holland',
    title: 'New user registered.',
    subtitle: '5 hours ago',
    time: 'Yesterday',
    isSeen: false,
  },
  {
    id: 3,
    img: avatar5,
    title: 'New message received 👋🏻',
    subtitle: 'You have 10 unread messages',
    time: '11 Aug',
    isSeen: true,
  },
  {
    id: 4,
    img: paypal,
    title: 'PayPal',
    subtitle: 'Received Payment',
    time: '25 May',
    isSeen: false,
    color: 'error',
  },
  {
    id: 5,
    img: avatar3,
    title: 'Received Order 📦',
    subtitle: 'New order received from john',
    time: '19 Mar',
    isSeen: true,
  },
])

// --- 💡 LÓGICA onMounted 💡 ---
// Al cargar el componente (después del login/registro)
onMounted(() => {
  // Revisar si la bandera de bienvenida existe
  const needsWelcome = localStorage.getItem('showWelcomeNotification')

  if (needsWelcome === 'true') {
    // Añadir la notificación de bienvenida al inicio de la lista
    notifications.value.unshift(welcomeNotification)

    // Borrar la bandera para no mostrarlo de nuevo
    localStorage.removeItem('showWelcomeNotification')
  }
})

// --- Funciones existentes ---
const removeNotification = notificationId => {
  notifications.value.forEach((item, index) => {
    if (notificationId === item.id)
      notifications.value.splice(index, 1)
  })
}

const markRead = notificationId => {
  notifications.value.forEach(item => {
    notificationId.forEach(id => {
      if (id === item.id)
        item.isSeen = true
    })
  })
}

const markUnRead = notificationId => {
  notifications.value.forEach(item => {
    notificationId.forEach(id => {
      if (id === item.id)
        item.isSeen = false
    })
  })
}

const handleNotificationClick = notification => {
  if (!notification.isSeen)
    markRead([notification.id])
}
</script>

<template>
  <Notifications
    :notifications="notifications"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @click:notification="handleNotificationClick"
  />
</template>















<!-- <script setup> -->
<!-- import avatar3 from '@images/avatars/avatar-3.png' -->
<!-- import avatar4 from '@images/avatars/avatar-4.png' -->
<!-- import avatar5 from '@images/avatars/avatar-5.png' -->
<!-- import paypal from '@images/cards/paypal-rounded.png' -->

<!-- const notifications = ref([ -->
<!--  { -->
<!--    id: 1, -->
<!--    img: avatar4, -->
<!--    title: 'Congratulation Flora! 🎉', -->
<!--    subtitle: 'Won the monthly best seller badge', -->
<!--    time: 'Today', -->
<!--    isSeen: true, -->
<!--  }, -->
<!--  { -->
<!--    id: 2, -->
<!--    text: 'Tom Holland', -->
<!--    title: 'New user registered.', -->
<!--    subtitle: '5 hours ago', -->
<!--    time: 'Yesterday', -->
<!--    isSeen: false, -->
<!--  }, -->
<!--  { -->
<!--    id: 3, -->
<!--    img: avatar5, -->
<!--    title: 'New message received 👋🏻', -->
<!--    subtitle: 'You have 10 unread messages', -->
<!--    time: '11 Aug', -->
<!--    isSeen: true, -->
<!--  }, -->
<!--  { -->
<!--    id: 4, -->
<!--    img: paypal, -->
<!--    title: 'PayPal', -->
<!--    subtitle: 'Received Payment', -->
<!--    time: '25 May', -->
<!--    isSeen: false, -->
<!--    color: 'error', -->
<!--  }, -->
<!--  { -->
<!--    id: 5, -->
<!--    img: avatar3, -->
<!--    title: 'Received Order 📦', -->
<!--    subtitle: 'New order received from john', -->
<!--    time: '19 Mar', -->
<!--    isSeen: true, -->
<!--  }, -->
<!-- ]) -->

<!-- const removeNotification = notificationId => { -->
<!--  notifications.value.forEach((item, index) => { -->
<!--    if (notificationId === item.id) -->
<!--      notifications.value.splice(index, 1) -->
<!--  }) -->
<!-- } -->

<!-- const markRead = notificationId => { -->
<!--  notifications.value.forEach(item => { -->
<!--    notificationId.forEach(id => { -->
<!--      if (id === item.id) -->
<!--        item.isSeen = true -->
<!--    }) -->
<!--  }) -->
<!-- } -->

<!-- const markUnRead = notificationId => { -->
<!--  notifications.value.forEach(item => { -->
<!--    notificationId.forEach(id => { -->
<!--      if (id === item.id) -->
<!--        item.isSeen = false -->
<!--    }) -->
<!--  }) -->
<!-- } -->

<!-- const handleNotificationClick = notification => { -->
<!--  if (!notification.isSeen) -->
<!--    markRead([notification.id]) -->
<!-- } -->
<!-- </script> -->

<!-- <template> -->
<!--  <Notifications -->
<!--    :notifications="notifications" -->
<!--    @remove="removeNotification" -->
<!--    @read="markRead" -->
<!--    @unread="markUnRead" -->
<!--    @click:notification="handleNotificationClick" -->
<!--  /> -->
<!-- </template> -->
