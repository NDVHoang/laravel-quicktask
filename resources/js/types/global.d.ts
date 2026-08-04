import type { Auth } from '@/types/auth';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            locale: string;
            supportedLocales: string[];
            translations: {
                auth_ui: {
                    login: Record<string, string>;
                    register: Record<string, string>;
                    forgot_password: Record<string, string>;
                    reset_password: Record<string, string>;
                    confirm_password: Record<string, string>;
                    verify_email: Record<string, string>;
                    logout: string;
                    language_switcher: Record<string, string>;
                };
                profile: {
                    navigation: Record<string, string>;
                    dashboard: Record<string, string>;
                    edit: Record<string, string>;
                    update_profile: Record<string, string>;
                    update_password: Record<string, string>;
                    delete_account: Record<string, string>;
                };
            };
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}

declare global {
    function route(name?: string, params?: any, absolute?: boolean): any;
}
