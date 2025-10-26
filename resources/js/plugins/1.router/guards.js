// import { canNavigate } from '@layouts/plugins/casl'
//
// export const setupGuards = router => {
//   // 👉 router.beforeEach
//   // Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
//   router.beforeEach(to => {
//     /*
//          * If it's a public route, continue navigation. This kind of pages are allowed to visited by login & non-login users. Basically, without any restrictions.
//          * Examples of public routes are, 404, under maintenance, etc.
//          */
//     if (to.meta.public)
//       return
//
//     /**
//          * Check if user is logged in by checking if token & user data exists in local storage
//          * Feel free to update this logic to suit your needs
//          */
//     // const isLoggedIn = !!(useCookie('userData').value && useCookie('accessToken').value)
//     const isLoggedIn = !!localStorage.getItem('token')
//
//
//     /*
//             If user is logged in and is trying to access login like page, redirect to home
//             else allow visiting the page
//             (WARN: Don't allow executing further by return statement because next code will check for permissions)
//            */
//     if (to.meta.unauthenticatedOnly) {
//       if (isLoggedIn)
//         return '/'
//       else
//         return undefined
//     }
//     if (!canNavigate(to) && to.matched.length) {
//       /* eslint-disable indent */
//             return isLoggedIn
//                 ? { name: 'not-authorized' }
//                 : {
//                     name: 'login',
//                     query: {
//                         ...to.query,
//                         to: to.fullPath !== '/' ? to.path : undefined,
//                     },
//                 }
//             /* eslint-enable indent */
//     }
//   })
// }
import { canNavigate } from '@layouts/plugins/casl'

// Reemplazo manual de useCookie
function useCookie(name) {
  return {
    get: () => {
      const value = document.cookie
        .split('; ')
        .find(row => row.startsWith(name + '='))

      
      return value ? decodeURIComponent(value.split('=')[1]) : null
    },
    set: (value, days = 7) => {
      const expires = new Date(Date.now() + days * 864e5).toUTCString()

      document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`
    },
    remove: () => {
      document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`
    },
  }
}

export const setupGuards = router => {
  router.beforeEach(async (to, from, next) => {
    const token = useCookie('accessToken').get() || localStorage.getItem('token')

    // 1️⃣ Rutas públicas o de autenticación
    if (to.meta.layout === 'blank' || to.meta.public || ['login', 'register', 'forgot-password'].includes(to.name)) {
      return next()
    }

    // 2️⃣ Sin token → login
    if (!token) {
      return next({ name: 'login' })
    }

    // 3️⃣ Con token e intenta ir al login → dashboard
    if (token && (to.name === 'login' || to.meta.unauthenticatedOnly)) {
      return next({ name: 'apps-ecommerce-dashboard' })
    }

    // 4️⃣ Control de permisos con CASL
    try {
      if (!canNavigate(to)) {
        if (to.name === 'apps-ecommerce-dashboard' || to.path === '/') {
          return next()
        }
        
        return next({ name: 'not-authorized' })
      }
    } catch (err) {
      console.warn('⚠️ CASL no inicializado aún, se permite acceso temporal:', err)
      
      return next()
    }

    return next()
  })
}
