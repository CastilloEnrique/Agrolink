// const emailRouteComponent = () => import('@/pages/apps/email/index.vue')
//
// // 👉 Redirects
// export const redirects = [
//   // ℹ️ We are redirecting to different pages based on role.
//   // NOTE: Role is just for UI purposes. ACL is based on abilities.
//   // {
//   //   path: '/',
//   //   name: 'index',
//   //   redirect: to => {
//   //     // TODO: Get type from backend
//   //     const userData = useCookie('userData')
//   //     const userRole = userData.value?.role
//   //     if (userRole === 'admin')
//   //       return { name: 'dashboards-crm' }
//   //     if (userRole === 'client')
//   //       return { name: 'access-control' }
//   //
//   //     return { name: 'login', query: to.query }
//   //   },
//   // },
//   {
//     path: '/',
//     name: 'index',
//     redirect: () => {
//       const token = useCookie('accessToken').value || localStorage.getItem('token')
//      
//       return token
//         ? { name: 'apps-ecommerce-dashboard' } // dashboard principal
//         : { name: 'login' } // login si no hay token
//     },
//   },
//
//    
//   {
//     path: '/pages/user-profile',
//     name: 'pages-user-profile',
//     redirect: () => ({ name: 'pages-user-profile-tab', params: { tab: 'profile' } }),
//   },
//   {
//     path: '/pages/account-settings',
//     name: 'pages-account-settings',
//     redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
//   },
// ]
// export const routes = [
//   // Email filter
//   {
//     path: '/apps/email/filter/:filter',
//     name: 'apps-email-filter',
//     component: emailRouteComponent,
//     meta: {
//       navActiveLink: 'apps-email',
//       layoutWrapperClasses: 'layout-content-height-fixed',
//     },
//   },
//
//   // Email label
//   {
//     path: '/apps/email/label/:label',
//     name: 'apps-email-label',
//     component: emailRouteComponent,
//     meta: {
//       // contentClass: 'email-application',
//       navActiveLink: 'apps-email',
//       layoutWrapperClasses: 'layout-content-height-fixed',
//     },
//   },
//   {
//     path: '/dashboards/logistics',
//     name: 'dashboards-logistics',
//     component: () => import('@/pages/apps/logistics/dashboard.vue'),
//   },
//   {
//     path: '/dashboards/academy',
//     name: 'dashboards-academy',
//     component: () => import('@/pages/apps/academy/dashboard.vue'),
//   },
//   {
//     path: '/apps/ecommerce/dashboard',
//     name: 'apps-ecommerce-dashboard',
//     component: () => import('@/pages/dashboards/ecommerce.vue'),
//   },
// ]

import { canNavigate } from '@layouts/plugins/casl'

// Helper simple para leer cookies
function readCookie(name) {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) {
    const cookieValue = parts.pop().split(';').shift()
    try {
      // Intentar decodificar si está codificado como URI component
      return decodeURIComponent(cookieValue)
    } catch (e) {
      // Si falla la decodificación, devolver el valor crudo
      return cookieValue
    }
  }

  return null
}

const emailRouteComponent = () => import('@/pages/apps/email/index.vue')

// 👉 Redirects
export const redirects = [
  {
    path: '/',
    name: 'index',
    redirect: () => {
      // Intenta obtener el token de localStorage O de la cookie
      const token = localStorage.getItem('token') || readCookie('accessToken')

      // 💡 CORRECCIÓN VERIFICADA: Si hay token, ir al NUEVO dashboard 'inicio', si no, al login
      return token
        ? { name: 'inicio' } // <--- APUNTA AL NUEVO DASHBOARD
        : { name: 'login' }
    },
  },


  // (Mantener las otras redirecciones sin cambios)
  {
    path: '/pages/user-profile',
    name: 'pages-user-profile',
    redirect: () => ({ name: 'pages-user-profile-tab', params: { tab: 'profile' } }),
  },
  {
    path: '/pages/account-settings',
    name: 'pages-account-settings',
    redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
  },
]

// 👉 Rutas Adicionales
export const routes = [
  // ... (Tus rutas existentes de email, logistics, academy, etc.)
  {
    path: '/apps/email/filter/:filter',
    name: 'apps-email-filter',
    component: emailRouteComponent,
    meta: { navActiveLink: 'apps-email', layoutWrapperClasses: 'layout-content-height-fixed' },
  },
  {
    path: '/apps/email/label/:label',
    name: 'apps-email-label',
    component: emailRouteComponent,
    meta: { navActiveLink: 'apps-email', layoutWrapperClasses: 'layout-content-height-fixed' },
  },
  { path: '/dashboards/logistics', name: 'dashboards-logistics', component: () => import('@/pages/apps/logistics/dashboard.vue') },
  { path: '/dashboards/academy', name: 'dashboards-academy', component: () => import('@/pages/apps/academy/dashboard.vue') },
  { path: '/apps/ecommerce/dashboard', name: 'apps-ecommerce-dashboard', component: () => import('@/pages/dashboards/ecommerce.vue') },
  {
    path: '/inicio',
    name: 'inicio',
    component: () => import('@/pages/inicio.vue'),
    meta: {
      layout: 'default',
      requiresAuth: true,
    },
  },

  // 💡 --- ¡AQUÍ ESTÁ LA NUEVA RUTA! --- 💡
  // Esto le dice a Vue que cargue tu componente cuando
  // alguien visite la URL /reset-password
  {
    path: '/reset-password',
    name: 'reset-password',

    // Apunta a la ruta de tu archivo (la que me diste)
    component: () => import('@/pages/pages/authentication/reset-password-v2.vue'),
    meta: {
      layout: 'blank', // Usa el layout en blanco (sin menús)
      public: true, // Es una ruta pública
    },
  },
]

// const emailRouteComponent = () => import('@/pages/apps/email/index.vue')
//
// // Helper simple para leer cookies (si no tienes useCookie global)
// function readCookie(name) {
//   const value = `; ${document.cookie}`
//   const parts = value.split(`; ${name}=`)
//   if (parts.length === 2) {
//     const cookieValue = parts.pop().split(';').shift()
//     try {
//       // Intentar decodificar si está codificado como URI component
//       return decodeURIComponent(cookieValue)
//     } catch (e) {
//       // Si falla la decodificación, devolver el valor crudo
//       return cookieValue
//     }
//   }
//  
//   return null
// }
//
// // 👉 Redirects
// export const redirects = [
//   {
//     path: '/',
//     name: 'index',
//     redirect: () => {
//       // Intenta obtener el token de localStorage O de la cookie
//       const token = localStorage.getItem('token') || readCookie('accessToken')
//
//       // 💡 CORRECCIÓN VERIFICADA: Si hay token, ir al NUEVO dashboard 'inicio', si no, al login
//       return token
//         ? { name: 'inicio' } // <--- APUNTA AL NUEVO DASHBOARD
//         : { name: 'login' }
//     },
//   },
//
//
//   // (Mantener las otras redirecciones sin cambios)
//   {
//     path: '/pages/user-profile',
//     name: 'pages-user-profile',
//     redirect: () => ({ name: 'pages-user-profile-tab', params: { tab: 'profile' } }),
//   },
//   {
//     path: '/pages/account-settings',
//     name: 'pages-account-settings',
//     redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
//   },
// ]
//
// // 👉 Rutas Adicionales
// export const routes = [
//   // ... (Mantener tus rutas existentes de email, logistics, academy, etc.)
//   {
//     path: '/apps/email/filter/:filter',
//     name: 'apps-email-filter',
//     component: emailRouteComponent,
//     meta: { navActiveLink: 'apps-email', layoutWrapperClasses: 'layout-content-height-fixed' },
//   },
//   {
//     path: '/apps/email/label/:label',
//     name: 'apps-email-label',
//     component: emailRouteComponent,
//     meta: { navActiveLink: 'apps-email', layoutWrapperClasses: 'layout-content-height-fixed' },
//   },
//   { path: '/dashboards/logistics', name: 'dashboards-logistics', component: () => import('@/pages/apps/logistics/dashboard.vue') },
//   { path: '/dashboards/academy', name: 'dashboards-academy', component: () => import('@/pages/apps/academy/dashboard.vue') },
//
//   // La ruta del viejo dashboard de ecommerce sigue existiendo, pero no es la principal
//   { path: '/apps/ecommerce/dashboard', name: 'apps-ecommerce-dashboard', component: () => import('@/pages/dashboards/ecommerce.vue') },
//
//   // --- 💡 RUTA EXPLÍCITA PARA EL NUEVO DASHBOARD 'inicio.vue' 💡 ---
//   // Añadimos esto para asegurarnos de que el router la conozca.
//   // Tu plugin vue-router/auto también debería crearla, pero esto es más seguro.
//   {
//     path: '/inicio', // Puedes cambiar esto a '/' si quieres que sea la raíz absoluta y única
//     name: 'inicio',
//     component: () => import('@/pages/inicio.vue'), // Apunta a tu nuevo archivo
//     meta: {
//       layout: 'default', // Usa el layout principal
//       requiresAuth: true, // Asegura que solo usuarios logueados accedan
//     },
//   },
// ]
//
