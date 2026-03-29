import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
      meta: { guest: true },
    },
    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/tasks',
        },
        {
          path: 'tasks',
          name: 'tasks',
          component: () => import('@/views/TasksView.vue'),
        },
        {
          path: 'tasks/:boardId',
          name: 'board',
          component: () => import('@/views/BoardView.vue'),
        },
        {
          path: 'marketing',
          name: 'marketing',
          component: () => import('@/views/MarketingView.vue'),
        },
        {
          path: 'games',
          name: 'games',
          component: () => import('@/views/GamesView.vue'),
        },
        {
          path: 'reports',
          name: 'reports',
          component: () => import('@/views/ReportsView.vue'),
        },
      ],
    },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'tasks' }
  }
})

export default router
