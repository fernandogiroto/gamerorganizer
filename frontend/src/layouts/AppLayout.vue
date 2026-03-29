<template>
  <div class="flex h-screen overflow-hidden" style="background:#09090f;">

    <!-- Sidebar -->
    <aside
      class="relative flex flex-col flex-shrink-0 h-full transition-all duration-300 overflow-hidden"
      :style="{ width: collapsed ? '68px' : '240px' }"
      style="background:#0d0d1a; border-right:1px solid rgba(255,255,255,0.06);"
    >
      <!-- Top glow -->
      <div class="absolute top-0 left-0 right-0 h-px pointer-events-none"
        style="background:linear-gradient(90deg,transparent,rgba(124,58,237,0.6),transparent);"></div>

      <!-- Logo + Toggle -->
      <div class="flex items-center h-16 flex-shrink-0"
        :class="collapsed ? 'justify-center px-2' : 'px-3 gap-2'"
        style="border-bottom:1px solid rgba(255,255,255,0.06);">

        <!-- Logo icon (hidden when collapsed) -->
        <div v-if="!collapsed" class="flex-shrink-0 flex items-center justify-center rounded-xl"
          style="width:34px;height:34px;min-width:34px;background:linear-gradient(135deg,#7c3aed,#5b21b6);box-shadow:0 0 16px rgba(124,58,237,0.4);">
          <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
              d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
              d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <!-- Brand text -->
        <div v-if="!collapsed" class="flex-1 overflow-hidden">
          <span class="block font-bold text-white text-sm whitespace-nowrap tracking-tight">GamerOrganizer</span>
          <span class="block text-xs whitespace-nowrap" style="color:rgba(167,139,250,0.7);">Game Studio Hub</span>
        </div>

        <!-- Toggle button -->
        <button
          @click="collapsed = !collapsed"
          class="sidebar-toggle flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg transition-all duration-200"
          :class="collapsed ? '' : 'ml-auto'"
          title="Toggle sidebar"
        >
          <svg
            class="w-4 h-4 transition-transform duration-300"
            :class="{ 'rotate-180': !collapsed }"
            fill="none" viewBox="0 0 24 24" stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 19l-7-7 7-7" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto overflow-x-hidden">
        <p v-if="!collapsed" class="px-2 mb-2 text-xs font-semibold uppercase tracking-widest"
          style="color:rgba(255,255,255,0.2);">Menu</p>

        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="nav-item flex items-center rounded-xl text-sm font-medium transition-all duration-200"
          :class="[
            collapsed ? 'justify-center w-10 h-10 mx-auto' : 'gap-3 px-3 py-2.5',
            isActive(item.to) ? 'nav-active' : 'nav-inactive'
          ]"
          :title="collapsed ? item.label : ''"
        >
          <span class="flex-shrink-0" style="width:18px;height:18px;" v-html="item.iconSvg"></span>
          <span v-if="!collapsed" class="truncate flex-1">{{ item.label }}</span>
          <span v-if="!collapsed && item.badge" class="badge-primary ml-auto flex-shrink-0">{{ item.badge }}</span>
        </RouterLink>
      </nav>

      <!-- User -->
      <div class="flex-shrink-0 p-2" style="border-top:1px solid rgba(255,255,255,0.06);">
        <div class="flex items-center rounded-xl p-2 transition-all duration-200 cursor-pointer user-section"
          :class="collapsed ? 'justify-center' : 'gap-3'">

          <div class="flex-shrink-0 flex items-center justify-center rounded-lg text-white text-xs font-bold"
            style="width:32px;height:32px;min-width:32px;background:linear-gradient(135deg,#7c3aed,#a855f7);box-shadow:0 2px 8px rgba(124,58,237,0.3);">
            {{ userInitials }}
          </div>

          <div v-if="!collapsed" class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate leading-tight">{{ auth.user?.name }}</p>
            <p class="text-xs truncate leading-tight" style="color:rgba(255,255,255,0.35);">{{ auth.user?.email }}</p>
          </div>

          <button v-if="!collapsed" @click.stop="handleLogout"
            class="logout-btn flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-lg transition-all duration-200"
            title="Sair">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 overflow-auto">
      <RouterView />
    </main>

  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const collapsed = ref(false)

const userInitials = computed(() =>
  auth.user?.name?.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase() || 'U'
)

const navItems = [
  {
    to: '/tasks',
    label: 'Tasks',
    iconSvg: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>`,
  },
  {
    to: '/marketing',
    label: 'Marketing',
    iconSvg: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>`,
  },
  {
    to: '/games',
    label: 'Games',
    iconSvg: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>`,
  },
  {
    to: '/reports',
    label: 'Reports',
    iconSvg: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`,
  },
]

function isActive(to: string) {
  if (to === '/tasks') return route.path.startsWith('/tasks')
  return route.path.startsWith(to)
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.sidebar-toggle {
  color: rgba(255,255,255,0.3);
}
.sidebar-toggle:hover {
  background: rgba(255,255,255,0.07);
  color: rgba(255,255,255,0.7);
}

.nav-active {
  background: rgba(124,58,237,0.14);
  color: #a78bfa;
  border: 1px solid rgba(124,58,237,0.22);
  box-shadow: 0 2px 8px rgba(124,58,237,0.08);
}

.nav-inactive {
  color: rgba(255,255,255,0.42);
  border: 1px solid transparent;
}
.nav-inactive:hover {
  background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.75);
}

.user-section {
  background: rgba(255,255,255,0.03);
}
.user-section:hover {
  background: rgba(255,255,255,0.06);
}

.logout-btn {
  color: rgba(255,255,255,0.25);
}
.logout-btn:hover {
  color: #f87171;
  background: rgba(239,68,68,0.1);
}
</style>
