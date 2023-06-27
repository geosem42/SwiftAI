import('./bootstrap');
import {resolvePageComponent} from "laravel-vite-plugin/inertia-helpers";

// Import modules...
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    progress: {
        color: '#ff0000',
        includeCSS: true,
        showSpinner: true,
    },
    title: (title) => `${title} - ${appName}`,
    //
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob("./Pages/**/*.vue")),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mixin({ methods: { route } })
            .mount(el);
    },
});