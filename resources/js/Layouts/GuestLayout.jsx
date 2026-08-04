import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export default function GuestLayout({ children }) {
    const { locale, translations } = usePage().props;
    const t = translations.auth_ui.language_switcher;
    const nextLocale = locale === 'en' ? 'vi' : 'en';

    useEffect(() => {
        document.documentElement.lang = locale;
    }, [locale]);

    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0">
            <div className="absolute top-0 right-0 p-6">
                <Link
                    href={route('locale.update', { locale: nextLocale })}
                    method="post"
                    as="button"
                    className="text-sm text-gray-500 underline hover:text-gray-900 focus:outline-none"
                >
                    {t[nextLocale]}
                </Link>
            </div>

            <div>
                <Link href="/">
                    <ApplicationLogo className="h-20 w-20 fill-current text-gray-500" />
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
                {children}
            </div>
        </div>
    );
}
