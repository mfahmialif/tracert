import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
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
      await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
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
