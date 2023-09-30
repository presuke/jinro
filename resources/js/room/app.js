import '../bootstrap';
import { createApp } from 'vue';
//import router from '../router';
// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

import App from '../../vue/room/App.vue';

const app = createApp(App);

const vuetify = createVuetify({
	components,
	directives,
});

//app.use(router);
app.use(vuetify);
app.mount('#app');
