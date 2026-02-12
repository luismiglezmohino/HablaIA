import { createApp } from 'vue'
import { createPinia } from 'pinia'
import * as Sentry from '@sentry/vue'
// Inter font — solo subset latin (español)
// Los imports genéricos (400.css, etc.) incluyen todos los subsets
import '@fontsource/inter/latin-400.css'
import '@fontsource/inter/latin-500.css'
import '@fontsource/inter/latin-600.css'
import '@fontsource/inter/latin-700.css'
import App from './App.vue'
import router from './presentation/router'
import './styles/main.css'

const app = createApp(App)

const sentryDsn = import.meta.env.VITE_SENTRY_DSN
if (sentryDsn) {
  Sentry.init({
    app,
    dsn: sentryDsn,
    environment: import.meta.env.MODE,
  })
}

app.use(createPinia())
app.use(router)

app.mount('#app')
