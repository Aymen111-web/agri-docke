import { ref, onMounted } from 'vue'

const isDark = ref(false)

export function useTheme() {
  function applyTheme(dark) {
    isDark.value = dark
    if (dark) {
      document.documentElement.classList.add('dark')
      document.body.classList.add('dark')
      localStorage.setItem('theme', 'dark')
    } else {
      document.documentElement.classList.remove('dark')
      document.body.classList.remove('dark')
      localStorage.setItem('theme', 'light')
    }
  }

  function toggleTheme() {
    applyTheme(!isDark.value)
  }

  function initTheme() {
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme) {
      applyTheme(savedTheme === 'dark')
    } else {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
      applyTheme(prefersDark)
    }
  }

  // Initialize on first import / use if in browser
  if (typeof window !== 'undefined' && !document.documentElement.hasAttribute('data-theme-inited')) {
    document.documentElement.setAttribute('data-theme-inited', 'true')
    initTheme()
  }

  return {
    isDark,
    toggleTheme,
    applyTheme
  }
}
