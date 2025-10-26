// import { isEmpty, isEmptyArray, isNullOrUndefined } from './helpers'
//
// // 👉 Required Validator
// export const requiredValidator = value => {
//   if (isNullOrUndefined(value) || isEmptyArray(value) || value === false)
//     return 'This field is required'
//
//   return !!String(value).trim().length || 'This field is required'
// }
//
// // 👉 Email Validator
// export const emailValidator = value => {
//   if (isEmpty(value))
//     return true
//   const re = /^(?:[^<>()[\]\\.,;:\s@"]+(?:\.[^<>()[\]\\.,;:\s@"]+)*|".+")@(?:\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\]|(?:[a-z\-\d]+\.)+[a-z]{2,})$/i
//   if (Array.isArray(value))
//     return value.every(val => re.test(String(val))) || 'The Email field must be a valid email'
//
//   return re.test(String(value)) || 'The Email field must be a valid email'
// }
//
// // 👉 Password Validator
// export const passwordValidator = password => {
//   const regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/
//   const validPassword = regExp.test(password)
//
//   return validPassword || 'Field must contain at least one uppercase, lowercase, special character and digit with min 8 chars'
// }
//
// // 👉 Confirm Password Validator
// export const confirmedValidator = (value, target) => value === target || 'The Confirm Password field confirmation does not match'
//
// // 👉 Between Validator
// export const betweenValidator = (value, min, max) => {
//   const valueAsNumber = Number(value)
//
//   return (Number(min) <= valueAsNumber && Number(max) >= valueAsNumber) || `Enter number between ${min} and ${max}`
// }
//
// // 👉 Integer Validator
// export const integerValidator = value => {
//   if (isEmpty(value))
//     return true
//   if (Array.isArray(value))
//     return value.every(val => /^-?\d+$/.test(String(val))) || 'This field must be an integer'
//
//   return /^-?\d+$/.test(String(value)) || 'This field must be an integer'
// }
//
// // 👉 Regex Validator
// export const regexValidator = (value, regex) => {
//   if (isEmpty(value))
//     return true
//   let regeX = regex
//   if (typeof regeX === 'string')
//     regeX = new RegExp(regeX)
//   if (Array.isArray(value))
//     return value.every(val => regexValidator(val, regeX))
//
//   return regeX.test(String(value)) || 'The Regex field format is invalid'
// }
//
// // 👉 Alpha Validator
// export const alphaValidator = value => {
//   if (isEmpty(value))
//     return true
//
//   return /^[A-Z]*$/i.test(String(value)) || 'The Alpha field may only contain alphabetic characters'
// }
//
// // 👉 URL Validator
// export const urlValidator = value => {
//   if (isEmpty(value))
//     return true
//   const re = /^https?:\/\/[^\s$.?#].\S*$/
//
//   return re.test(String(value)) || 'URL is invalid'
// }
//
// // 👉 Length Validator
// export const lengthValidator = (value, length) => {
//   if (isEmpty(value))
//     return true
//
//   return String(value).length === length || `"The length of the Characters field must be ${length} characters."`
// }
//
// // 👉 Alpha-dash Validator
// export const alphaDashValidator = value => {
//   if (isEmpty(value))
//     return true
//   const valueAsString = String(value)
//
//   return /^[\w-]*$/.test(valueAsString) || 'All Character are not valid'
// }

import { isEmpty, isEmptyArray, isNullOrUndefined } from './helpers'

// 👉 Validador Requerido
export const requiredValidator = value => {
  if (isNullOrUndefined(value) || isEmptyArray(value) || value === false)
    return 'Este campo es requerido'

  return !!String(value).trim().length || 'Este campo es requerido'
}

// 👉 Validador de Email
export const emailValidator = value => {
  if (isEmpty(value))
    return true
  const re = /^(?:[^<>()[\]\\.,;:\s@"]+(?:\.[^<>()[\]\\.,;:\s@"]+)*|".+")@(?:\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\]|(?:[a-z\-\d]+\.)+[a-z]{2,})$/i
  if (Array.isArray(value))
    return value.every(val => re.test(String(val))) || 'El campo Email debe ser un correo electrónico válido'

  return re.test(String(value)) || 'El campo Email debe ser un correo electrónico válido'
}

// 👉 Validador de Contraseña
export const passwordValidator = password => {
  const regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/
  const validPassword = regExp.test(password)

  return validPassword || 'El campo debe contener al menos una mayúscula, una minúscula, un carácter especial y un dígito, con un mínimo de 8 caracteres'
}

// 👉 Validador de Confirmación de Contraseña
export const confirmedValidator = (value, target) => value === target || 'La confirmación de la contraseña no coincide'

// 👉 Validador "Entre" (Rango)
export const betweenValidator = (value, min, max) => {
  const valueAsNumber = Number(value)

  return (Number(min) <= valueAsNumber && Number(max) >= valueAsNumber) || `Ingrese un número entre ${min} y ${max}`
}

// 👉 Validador de Entero
export const integerValidator = value => {
  if (isEmpty(value))
    return true
  if (Array.isArray(value))
    return value.every(val => /^-?\d+$/.test(String(val))) || 'Este campo debe ser un número entero'

  return /^-?\d+$/.test(String(value)) || 'Este campo debe ser un número entero'
}

// 👉 Validador de Regex (Expresión Regular)
export const regexValidator = (value, regex) => {
  if (isEmpty(value))
    return true
  let regeX = regex
  if (typeof regeX === 'string')
    regeX = new RegExp(regeX)
  if (Array.isArray(value))
    return value.every(val => regexValidator(val, regeX))

  return regeX.test(String(value)) || 'El formato del campo no es válido'
}

// 👉 Validador Alfabético
export const alphaValidator = value => {
  if (isEmpty(value))
    return true

  return /^[A-Z]*$/i.test(String(value)) || 'Este campo solo puede contener caracteres alfabéticos'
}

// 👉 Validador de URL
export const urlValidator = value => {
  if (isEmpty(value))
    return true
  const re = /^https?:\/\/[^\s$.?#].\S*$/

  return re.test(String(value)) || 'La URL no es válida'
}

// 👉 Validador de Longitud
export const lengthValidator = (value, length) => {
  if (isEmpty(value))
    return true

  return String(value).length === length || `"La longitud del campo debe ser de ${length} caracteres."`
}

// 👉 Validador Alfa-numérico con guiones
export const alphaDashValidator = value => {
  if (isEmpty(value))
    return true
  const valueAsString = String(value)

  return /^[\w-]*$/.test(valueAsString) || 'El campo contiene caracteres no válidos'
}
