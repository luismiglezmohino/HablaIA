import { createApp } from 'vue'
import App from './App.vue'
import router from './presentation/router'
import './styles/main.css'

const app = createApp(App)

app.use(router)

app.mount('#app')
