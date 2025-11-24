import './bootstrap'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createNotivue } from 'notivue'
import { createInertiaApp } from '@inertiajs/vue3'
import { route } from '../../vendor/tightenco/ziggy'
import { ZiggyVue } from 'ziggy-js'

import 'notivue/notification.css'
import 'notivue/animations.css'

const notivue = createNotivue({
  position: 'bottom-right',
  pauseOnHover: true,
  pauseOnTouch: true,
  avoidDuplicates: true,
})

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
    .use(plugin)
    .use(ZiggyVue)
    .use(notivue)
    .mount(el)
  },
})
