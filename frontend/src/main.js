import { createApp } from 'vue';
import './style.css';
import App from './App.vue';

import 'vuetify/styles';
import vuetify from './plugins/vuetify';

import { createPinia } from 'pinia';

const pinia = createPinia();

createApp(App).use(vuetify).use(pinia).mount('#app');
