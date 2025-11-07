// // import { canNavigate } from '@layouts/plugins/casl'
// //
// // export const setupGuards = router => {
// //   // 👉 router.beforeEach
// //   // Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
// //   router.beforeEach(to => {
// //     /*
// //          * If it's a public route, continue navigation. This kind of pages are allowed to visited by login & non-login users. Basically, without any restrictions.
// //          * Examples of public routes are, 404, under maintenance, etc.
// //          */
// //     if (to.meta.public)
// //       return
// //
// //     /**
// //          * Check if user is logged in by checking if token & user data exists in local storage
// //          * Feel free to update this logic to suit your needs
// //          */
// //     // const isLoggedIn = !!(useCookie('userData').value && useCookie('accessToken').value)
// //     const isLoggedIn = !!localStorage.getItem('token')
// //
// //
// //     /*
// //             If user is logged in and is trying to access login like page, redirect to home
// //             else allow visiting the page
// //             (WARN: Don't allow executing further by return statement because next code will check for permissions)
// //            */
// //     if (to.meta.unauthenticatedOnly) {
// //       if (isLoggedIn)
// //         return '/'
// //       else
// //         return undefined
// //     }
// //     if (!canNavigate(to) && to.matched.length) {
// //       /* eslint-disable indent */
// //             return isLoggedIn
// //                 ? { name: 'not-authorized' }
// //                 : {
// //                     name: 'login',
// //                     query: {
// //                         ...to.query,
// //                         to: to.fullPath !== '/' ? to.path : undefined,
// //                     },
// //                 }
// //             /* eslint-enable indent */
// //     }
// //   })
// // }
// import { canNavigate } from '@layouts/plugins/casl'
//
// // Reemplazo manual de useCookie
// function useCookie(name) {
//   return {
//     get: () => {
//       const value = document.cookie
//         .split('; ')
//         .find(row => row.startsWith(name + '='))
//
//      
//       return value ? decodeURIComponent(value.split('=')[1]) : null
//     },
//     set: (value, days = 7) => {
//       const expires = new Date(Date.now() + days * 864e5).toUTCString()
//
//       document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`
//     },
//     remove: () => {
//       document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`
//     },
//   }
// }
//
// export const setupGuards = router => {
//   router.beforeEach(async (to, from, next) => {
//     const token = useCookie('accessToken').get() || localStorage.getItem('token')
//
//     // 1️⃣ Rutas públicas o de autenticación
//     if (to.meta.layout === 'blank' || to.meta.public || ['login', 'register', 'forgot-password'].includes(to.name)) {
//       return next()
//     }
//
//     // 2️⃣ Sin token → login
//     if (!token) {
//       return next({ name: 'login' })
//     }
//
//     // 3️⃣ Con token e intenta ir al login → dashboard
//     if (token && (to.name === 'login' || to.meta.unauthenticatedOnly)) {
//       return next({ name: 'apps-ecommerce-dashboard' })
//     }
//
//     // 4️⃣ Control de permisos con CASL
//     try {
//       if (!canNavigate(to)) {
//         if (to.name === 'apps-ecommerce-dashboard' || to.path === '/') {
//           return next()
//         }
//        
//         return next({ name: 'not-authorized' })
//       }
//     } catch (err) {
//       console.warn('⚠️ CASL no inicializado aún, se permite acceso temporal:', err)
//      
//       return next()
//     }
//
//     return next()
//   })
// }

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

export const setupGuards = router => {
  router.beforeEach(async (to, from, next) => {
    // Intenta obtener token de localStorage o cookie
    const token = localStorage.getItem('token') || readCookie('accessToken')
    const isPublicRoute = to.meta.public || to.meta.layout === 'blank' // Rutas que no requieren login
    const isAuthRoute = ['login', 'register', 'forgot-password', 'reset-password'].includes(to.name) // Rutas de autenticación

    // --- LÓGICA DE REDIRECCIÓN CORREGIDA ---

    // 1. Si NO hay token e intenta ir a una ruta que NO es pública/auth => A LOGIN
    if (!token && !isPublicRoute && !isAuthRoute) {
      console.log('Guard: No token, redirecting to login from protected route:', to.fullPath)

      return next({
        name: 'login',
        query: { to: to.fullPath !== '/' ? to.path : undefined },
      })
    }

    // 2. Si HAY token E intenta ir a una ruta de autenticación (login/register) => AL NUEVO DASHBOARD 'inicio'
    if (token && isAuthRoute) {
      // Excepción: Permite a los usuarios con token ver /reset-password si vienen del enlace de correo
      // (Esto es opcional, pero previene que un usuario logueado en otra pestaña no pueda resetear)
      if(to.name === 'reset-password') {
        return next()
      }

      console.log('Guard: Token exists, redirecting from auth page to inicio')

      return next({ name: 'inicio' }) // <--- CORRECCIÓN AQUÍ
    }

    // 3. Si HAY token Y la ruta destino es el DASHBOARD 'inicio': Permitir acceso directo
    if (token && to.name === 'inicio') { // <--- CORRECCIÓN AQUÍ
      console.log('Guard: Accessing new dashboard (inicio), allowed.')

      return next()
    }

    // 4. Si HAY token, NO es 'inicio', NO es pública/auth => Verificar Permisos (CASL)
    if (token && !isPublicRoute && !isAuthRoute && to.name !== 'inicio') { // <--- CORRECCIÓN AQUÍ
      try {
        // Asegúrate que canNavigate esté bien configurado en tu proyecto
        if (canNavigate(to)) {
          console.log('Guard: CASL allows access to', to.fullPath)

          return next() // Tiene permiso
        } else {
          // No tiene permiso
          console.warn('Guard: CASL DENIED access to:', to.fullPath)

          return next({ name: 'not-authorized' })
        }
      } catch (err) {
        // Si CASL falla, permitir acceso temporal (pero loguear)
        console.error('Guard: CASL check failed, allowing temporary access:', err, 'Route:', to.fullPath)

        return next()
      }
    }

    // 5. Caso por defecto (rutas públicas si no hay token, etc.)
    // Si llegamos aquí, es probablemente una ruta pública o el usuario no tiene token
    console.log('Guard: Default case (likely public or no token), allowing navigation to:', to.fullPath)

    return next()
  })
}




// import { canNavigate } from '@layouts/plugins/casl'
//
// // Helper simple para leer cookies
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
// export const setupGuards = router => {
//   router.beforeEach(async (to, from, next) => {
//     // Intenta obtener token de localStorage o cookie
//     const token = localStorage.getItem('token') || readCookie('accessToken')
//     const isPublicRoute = to.meta.public || to.meta.layout === 'blank' // Rutas que no requieren login
//     const isAuthRoute = ['login', 'register', 'forgot-password'].includes(to.name) // Rutas de autenticación
//
//     // --- LÓGICA DE REDIRECCIÓN CORREGIDA ---
//
//     // 1. Si NO hay token e intenta ir a una ruta que NO es pública/auth => A LOGIN
//     if (!token && !isPublicRoute && !isAuthRoute) {
//       console.log('Guard: No token, redirecting to login from protected route:', to.fullPath)
//
//       return next({
//         name: 'login',
//         query: { to: to.fullPath !== '/' ? to.path : undefined },
//       })
//     }
//
//     // 2. Si HAY token E intenta ir a una ruta de autenticación (login/register) => AL NUEVO DASHBOARD 'inicio'
//     if (token && isAuthRoute) {
//       console.log('Guard: Token exists, redirecting from auth page to inicio')
//
//       return next({ name: 'inicio' }) // <--- CORRECCIÓN AQUÍ
//     }
//
//     // 3. Si HAY token Y la ruta destino es el DASHBOARD 'inicio': Permitir acceso directo
//     if (token && to.name === 'inicio') { // <--- CORRECCIÓN AQUÍ
//       console.log('Guard: Accessing new dashboard (inicio), allowed.')
//
//       return next()
//     }
//
//     // 4. Si HAY token, NO es 'inicio', NO es pública/auth => Verificar Permisos (CASL)
//     if (token && !isPublicRoute && !isAuthRoute && to.name !== 'inicio') { // <--- CORRECCIÓN AQUÍ
//       try {
//         // Asegúrate que canNavigate esté bien configurado en tu proyecto
//         if (canNavigate(to)) {
//           console.log('Guard: CASL allows access to', to.fullPath)
//
//           return next() // Tiene permiso
//         } else {
//           // No tiene permiso
//           console.warn('Guard: CASL DENIED access to:', to.fullPath)
//
//           return next({ name: 'not-authorized' })
//         }
//       } catch (err) {
//         // Si CASL falla, permitir acceso temporal (pero loguear)
//         console.error('Guard: CASL check failed, allowing temporary access:', err, 'Route:', to.fullPath)
//
//         return next()
//       }
//     }
//
//     // 5. Caso por defecto (rutas públicas si no hay token, etc.)
//     // Si llegamos aquí, es probablemente una ruta pública o el usuario no tiene token
//     console.log('Guard: Default case (likely public or no token), allowing navigation to:', to.fullPath)
//
//     return next()
//   })
// }

