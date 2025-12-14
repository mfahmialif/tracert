import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('../views/alumni/DashboardView.vue'),
      meta: { requiresAuth: true, role: 'alumni' },
    },
    {
      path: '/questionnaire/:id',
      name: 'questionnaire',
      component: () => import('../views/alumni/QuestionnaireView.vue'),
      meta: { requiresAuth: true, role: 'alumni' },
    },
    {
      path: '/admin',
      name: 'admin-dashboard',
      component: () => import('../views/admin/DashboardView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/alumni',
      name: 'admin-alumni',
      component: () => import('../views/admin/AlumniView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/alumni/create',
      name: 'admin-alumni-create',
      component: () => import('../views/admin/alumni/CreateView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/alumni/:id/edit',
      name: 'admin-alumni-edit',
      component: () => import('../views/admin/alumni/EditView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/questionnaires',
      name: 'admin-questionnaires',
      component: () => import('../views/admin/questionnaires/IndexView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/questionnaires/create',
      name: 'admin-questionnaires-create',
      component: () => import('../views/admin/questionnaires/CreateView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/questionnaires/:id/edit',
      name: 'admin-questionnaires-edit',
      component: () => import('../views/admin/questionnaires/EditView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/questionnaires/:id/results',
      name: 'admin-questionnaires-results',
      component: () => import('../views/admin/questionnaires/ResultsView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },

    {
      path: '/admin/faculties',
      name: 'admin-faculties',
      component: () => import('../views/admin/faculty/IndexView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/faculties/create',
      name: 'admin-faculties-create',
      component: () => import('../views/admin/faculty/CreateView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/faculties/:id/edit',
      name: 'admin-faculties-edit',
      component: () => import('../views/admin/faculty/EditView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/prodis',
      name: 'admin-prodis',
      component: () => import('../views/admin/prodi/IndexView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/prodis/create',
      name: 'admin-prodis-create',
      component: () => import('../views/admin/prodi/CreateView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/prodis/:id/edit',
      name: 'admin-prodis-edit',
      component: () => import('../views/admin/prodi/EditView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    // Year
    {
      path: '/admin/years',
      name: 'admin-years',
      component: () => import('../views/admin/year/IndexView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/years/create',
      name: 'admin-years-create',
      component: () => import('../views/admin/year/CreateView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/admin/years/:id/edit',
      name: 'admin-years-edit',
      component: () => import('../views/admin/year/EditView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
  ],
})

let authChecked = false

router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()

  // Only fetch user once on initial load
  if (!authChecked) {
    authChecked = true
    try {
      await authStore.fetchUser()
    } catch (error) {
      // User is not authenticated
    }
  }

  const requiresAuth = to.meta.requiresAuth
  const isGuest = to.meta.guest
  const requiredRole = to.meta.role as string | undefined

  // Redirect unauthenticated users to login
  if (requiresAuth && !authStore.isAuthenticated) {
    next({ path: '/login', replace: true })
    return
  }

  // Redirect authenticated users away from login page
  if (isGuest && authStore.isAuthenticated) {
    if (authStore.isAdmin) {
      next({ path: '/admin', replace: true })
    } else {
      next({ path: '/', replace: true })
    }
    return
  }

  // Check role access
  if (requiredRole && authStore.isAuthenticated) {
    if (requiredRole === 'admin' && !authStore.isAdmin) {
      next({ path: '/', replace: true })
      return
    }
    if (requiredRole === 'alumni' && authStore.isAdmin) {
      next({ path: '/admin', replace: true })
      return
    }
  }

  next()
})

export default router
