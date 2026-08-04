import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';

export default function Dashboard() {
    const { translations } = usePage().props;
    const t = translations.profile.dashboard;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl leading-tight font-semibold text-gray-800">
                    {t.title}
                </h2>
            }
        >
            <Head title={t.title} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">{t.logged_in}</div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
