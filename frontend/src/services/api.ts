import axios from 'axios'

// Use environment variable or fallback to production URL
const baseURL = import.meta.env.VITE_API_URL || 'https://tracerapp.uiidalwa.web.id/api'

const api = axios.create({
  baseURL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
})

// Token storage key
const TOKEN_KEY = 'auth_token'

// Token management functions
export const setAuthToken = (token: string) => {
  localStorage.setItem(TOKEN_KEY, token)
  console.log('store token: ', token)
}

export const getAuthToken = (): string | null => {
  return localStorage.getItem(TOKEN_KEY)
}

export const removeAuthToken = () => {
  localStorage.removeItem(TOKEN_KEY)
}

// Request interceptor - attach bearer token if available
api.interceptors.request.use(
  (config) => {
    const token = getAuthToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - handle 401 errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token is invalid or expired
      removeAuthToken()
      // Redirect to login will be handled by router guards
    }
    return Promise.reject(error)
  }
)

export default api
