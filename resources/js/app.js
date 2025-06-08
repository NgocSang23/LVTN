import './bootstrap';

import { createApp } from 'vue';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import App from './App.vue'; // 👈 import file chứa <router-view>
import router from './route/index.js';

const app = createApp(App);
app.use(router);
app.mount('#app');
