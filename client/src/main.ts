import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./router";
import { Service as AuthService } from "./services/authService";
import { Service as AdminService } from "./services/adminService";
import "./assets/pico.min.css";
import "./assets/main.css";

const app = createApp(App);

app.provide(AuthService.INJECTION_KEY, new AuthService());
app.provide(AdminService.INJECTION_KEY, new AdminService());

const pinia = createPinia();
pinia.use(() => ({ authService: AuthService.use(), adminService: AdminService.use() }));
app.use(pinia);
app.use(router);

app.mount("#app");
