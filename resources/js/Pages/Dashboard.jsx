import DashboardLayout from '@/Layouts/DashboardLayout';
import { Head, usePage } from '@inertiajs/react';

export default function Dashboard() {
    const { translations } = usePage().props;
    const t = translations.profile.dashboard;

    return (
        <DashboardLayout>
            <Head title={t.title} />

            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-900">{t.title}</h1>
                <p className="mt-1 text-gray-600">{t.logged_in}</p>
            </div>

            <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-medium text-gray-500">
                        Total Tasks
                    </h3>
                    <p className="mt-2 text-3xl font-bold text-gray-900">12</p>
                </div>
                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-medium text-gray-500">
                        Completed
                    </h3>
                    <p className="mt-2 text-3xl font-bold text-indigo-600">8</p>
                </div>
                <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 className="text-sm font-medium text-gray-500">
                        Pending
                    </h3>
                    <p className="mt-2 text-3xl font-bold text-orange-500">4</p>
                </div>
            </div>

            <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 className="mb-4 text-lg font-semibold text-gray-900">
                    Recent Activity
                </h2>
                <div className="space-y-4">
                    <div className="flex items-center text-sm">
                        <div className="mr-3 h-2 w-2 rounded-full bg-indigo-500"></div>
                        <span className="flex-1 text-gray-600">
                            Completed task "Design Dashboard"
                        </span>
                        <span className="text-gray-400">2h ago</span>
                    </div>
                    <div className="flex items-center text-sm">
                        <div className="mr-3 h-2 w-2 rounded-full bg-gray-300"></div>
                        <span className="flex-1 text-gray-600">
                            Created new project "Quicktask"
                        </span>
                        <span className="text-gray-400">1d ago</span>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
