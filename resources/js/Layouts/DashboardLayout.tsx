import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useEffect } from 'react';
import Header from '@/Components/Dashboard/Header';
import Sidebar from '@/Components/Dashboard/Sidebar';

interface Props {
    children: ReactNode;
}

export default function DashboardLayout({ children }: Props) {
    const { locale } = usePage().props as any;

    useEffect(() => {
        document.documentElement.lang = locale;
    }, [locale]);

    return (
        <div className="flex min-h-screen bg-gray-50">
            <Sidebar />
            <div className="flex min-w-0 flex-1 flex-col">
                <Header />
                <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
