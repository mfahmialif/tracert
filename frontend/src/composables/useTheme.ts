import { ref, onMounted, watch } from 'vue'

const THEME_KEY = 'tracer-theme'

export function useTheme() {
  const isDark = ref(false)

  // Initialize theme from localStorage or system preference
  onMounted(() => {
    const stored = localStorage.getItem(THEME_KEY)
    if (stored) {
      isDark.value = stored === 'dark'
    } else {
      isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    applyTheme()
  })

  // Watch for theme changes and apply
  watch(isDark, () => {
    applyTheme()
    localStorage.setItem(THEME_KEY, isDark.value ? 'dark' : 'light')
  })

  function applyTheme() {
    const html = document.documentElement
    // Toggle 'dark' class on html element for Tailwind
    html.classList.toggle('dark', isDark.value)
    
    // Also set data-theme if needed for other libs, but shadcn relies mostly on class
    // We can keep specific style attribute if we want to force color-scheme
    html.style.colorScheme = isDark.value ? 'dark' : 'light'
  }

  function toggleTheme() {
    isDark.value = !isDark.value
  }

  return {
    isDark,
    toggleTheme
  }
}
