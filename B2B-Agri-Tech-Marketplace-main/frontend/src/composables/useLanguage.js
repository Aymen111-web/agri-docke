import { ref, computed } from 'vue'
import en from '@/locales/en.js'
import am from '@/locales/am.js'

const currentLang = ref(localStorage.getItem('agri_lang') || 'en')

const locales = { en, am }

function getNestedValue(obj, path) {
  if (!obj || !path) return undefined
  const keys = path.split('.')
  let current = obj
  for (const k of keys) {
    if (current && typeof current === 'object' && k in current) {
      current = current[k]
    } else {
      return undefined
    }
  }
  return typeof current === 'string' ? current : undefined
}

function searchLocaleFlat(obj, key) {
  if (!obj || !key) return undefined
  // Direct match at root
  if (typeof obj[key] === 'string') return obj[key]
  // Check common or nav first
  if (obj.common && typeof obj.common[key] === 'string') return obj.common[key]
  if (obj.nav && typeof obj.nav[key] === 'string') return obj.nav[key]
  
  // Recursively search sub-namespaces
  for (const ns of Object.keys(obj)) {
    if (obj[ns] && typeof obj[ns] === 'object' && typeof obj[ns][key] === 'string') {
      return obj[ns][key]
    }
  }
  return undefined
}

export function useLanguage() {
  function t(key, paramsOrFallback, maybeFallback) {
    if (!key) return ''
    
    let params = null
    let fallback = null
    if (typeof paramsOrFallback === 'object' && paramsOrFallback !== null) {
      params = paramsOrFallback
      fallback = maybeFallback
    } else if (typeof paramsOrFallback === 'string') {
      fallback = paramsOrFallback
    }

    const lang = currentLang.value
    const activeLocale = locales[lang] || locales.en
    const fallbackLocale = locales.en

    let result = undefined

    // 1. Try dot-path in active locale
    if (key.includes('.')) {
      result = getNestedValue(activeLocale, key)
    }

    // 2. Try flat search in active locale
    if (!result) {
      result = searchLocaleFlat(activeLocale, key)
    }

    // 3. Fallback to English dot-path
    if (!result && lang !== 'en' && key.includes('.')) {
      result = getNestedValue(fallbackLocale, key)
    }

    // 4. Fallback to English flat search
    if (!result && lang !== 'en') {
      result = searchLocaleFlat(fallbackLocale, key)
    }

    // 5. Explicit fallback or original key
    if (!result) {
      result = fallback || key
    }

    // Parameter interpolation: {key} -> val
    if (params && typeof result === 'string') {
      for (const [pKey, pVal] of Object.entries(params)) {
        result = result.replace(new RegExp(`\\{${pKey}\\}`, 'g'), String(pVal))
      }
    }

    return result
  }

  function toggleLanguage() {
    currentLang.value = currentLang.value === 'en' ? 'am' : 'en'
    localStorage.setItem('agri_lang', currentLang.value)
  }

  function setLanguage(lang) {
    if (lang === 'en' || lang === 'am') {
      currentLang.value = lang
      localStorage.setItem('agri_lang', lang)
    }
  }

  return {
    currentLang,
    t,
    toggleLanguage,
    setLanguage,
  }
}
