import axios from 'axios'

// ✅ Crear instancia base de Axios
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL, // ej: http://agrolink.local/api
  headers: {
    Accept: 'application/json',
  },
})

// ✅ Interceptor para agregar token (sin loops)
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token && !config.headers.Authorization) {
    config.headers.Authorization = `Bearer ${token}`
  }
  
  return config
})

export default api
