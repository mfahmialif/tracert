import axios from 'axios'

// Use environment variable or fallback to production URL
const baseURL = import.meta.env.VITE_API_URL || 'https://tracerapp.uiidalwa.web.id/api'

const api = axios.create({
  baseURL,
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
})

let csrfInitialized = false

// Intercept requests to add CSRF token
api.interceptors.request.use(async (config) => {
  // Get CSRF cookie if not already set (only once)
  if (!csrfInitialized) {
    csrfInitialized = true
    try {
      // Determine sanctum URL based on baseURL
      const sanctumUrl = baseURL.includes('http')
        ? baseURL.replace('/api', '/sanctum/csrf-cookie')
        : '/sanctum/csrf-cookie'
      
      await axios.get(sanctumUrl, { withCredentials: true })
    } catch {
      // Ignore CSRF errors
    }
  }
  return config
})

// Handle errors - don't auto-redirect to avoid loops
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Don't auto-redirect, let the router handle it
    return Promise.reject(error)
  }
)

export default api
