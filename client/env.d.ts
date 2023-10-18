/// <reference types="vite/client" />
import "pinia";

declare module "pinia" {
    import type { AuthService } from "@/services/authService";
    import type { AdminService } from "@/services/adminService";
    export interface PiniaCustomProperties {
        authService: AuthService;
        adminService: AdminService;
    }
}
