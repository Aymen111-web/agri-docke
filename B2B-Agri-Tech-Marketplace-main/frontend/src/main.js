import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './index.css'
import { useLanguage } from './composables/useLanguage'

const app = createApp(App)
const { t } = useLanguage()
app.config.globalProperties.$t = t
app.use(router)
app.mount('#app')
