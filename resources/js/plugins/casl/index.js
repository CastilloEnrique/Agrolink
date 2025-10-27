// --- Imports ---
// Usaremos PureAbility y defineAbilityFrom para la sintaxis moderna de CASL v6+
import { AbilityBuilder, createMongoAbility, PureAbility } from '@casl/ability'
import { abilitiesPlugin } from '@casl/vue'

// BORRADO: import { useCookie } from '@vueuse/integrations/useCookie' // Es auto-importado
import { ability } from './ability' // Importa el objeto principal desde ability.js

// --- Lógica de Definición de Reglas ---

// Regla base inicial para usuarios no autenticados
export const initialAbility = [
  { action: 'read', subject: 'Auth' },
]

/**
 * Define las habilidades (rules) para el usuario basado en sus roles.
 * @param {object} user - Objeto de usuario con array 'roles'.
 * @returns {Array} Array de reglas CASL.
 */
export const defineAbilitiesFor = user => { // Paréntesis quitados
  // Usamos destructuring directo de AbilityBuilder
  const { can, rules } = new AbilityBuilder(PureAbility) // Usar PureAbility es más moderno

  // Reglas básicas para usuarios logueados
  can('read', 'dashboards-ecommerce')
  can('read', 'user-profile')

  if (user && user.roles && Array.isArray(user.roles)) {
    user.roles.forEach(role => {
      const roleName = role.nombre.toLowerCase()
      if (roleName === 'productor' || roleName === 'intermediario') {
        can('manage', 'Productor')
        can('create', 'Producto')
        can('read', 'Producto')
      }
      if (roleName === 'admin') {
        can('manage', 'all')
      }
      if (roleName === 'consumidor') {
        can('read', 'catalogo')
      }
    })
  } else {
    can('read', 'Auth') // Regla para no logueados
  }

  return rules
}

// --- Función Principal: Registrar el Plugin ---
export default function (app) { // Paréntesis quitados
  // 1. Obtener reglas guardadas (si existen)
  const userData = useCookie('userData').value
  const storedRules = userData?.abilities || initialAbility

  // 2. Actualizar el objeto 'ability' con las reglas encontradas
  ability.update(storedRules)

  // 3. Registrar el plugin de Vue, pasándole el objeto 'ability'
  app.use(abilitiesPlugin, ability, {
    useGlobalProperties: true,
  })
} // Punto y coma extra eliminado si existía

