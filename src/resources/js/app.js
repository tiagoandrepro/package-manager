require('./bootstrap');

import { createApp, h } from 'vue'
import { App, plugin } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress'
import _ from 'lodash'

InertiaProgress.init()

const el = document.getElementById('app');

const app = createApp({
    render: () => h(App, {
        initialPage: JSON.parse(el.dataset.page),
        resolveComponent: (name) => {
            const isPackage = name.includes('::');

            if(! isPackage) {
                return import(`./Pages/${name}.vue`).then(module => module.default)
            }

            const [packageName, packagePage] = name.split('::');
            const packageFolder = _.find(window.applicationModules, { name: packageName }).vendor;

            return import(`../../modules/${packageFolder}/src/resources/js/Pages/${packagePage}.vue`).then(module => module.default)

        },
    })
});
app.config.globalProperties.$route = window.route;
app.config.globalProperties.$modules = window.applicationModules;

app.provide('$route', window.route);
app.use(plugin).mount(el);
