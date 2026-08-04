import { Link } from '@inertiajs/react';
import { Home, ListTodo, Users } from 'lucide-react';
import ApplicationLogo from '@/Components/ApplicationLogo';

export default function Sidebar() {
    return (
        <aside className="hidden min-h-screen w-64 flex-col bg-gray-900 text-white md:flex">
            <div className="flex h-16 items-center bg-gray-950 px-6">
                <Link href="/" className="flex items-center gap-2">
                    <ApplicationLogo className="block h-8 w-auto fill-current text-indigo-500" />
                    <span className="text-xl font-bold">Quicktask</span>
                </Link>
            </div>
            <nav className="flex-1 space-y-2 px-4 py-6">
                <Link
                    href={route('dashboard')}
                    className="flex items-center space-x-3 rounded-lg bg-gray-800 px-4 py-3 text-white transition-colors"
                >
                    <Home size={20} />
                    <span className="font-medium">Dashboard</span>
                </Link>
                <div className="flex cursor-not-allowed items-center space-x-3 rounded-lg px-4 py-3 text-gray-400 transition-colors hover:bg-gray-800/50">
                    <ListTodo size={20} />
                    <span className="font-medium">Tasks (Soon)</span>
                </div>
                <div className="flex cursor-not-allowed items-center space-x-3 rounded-lg px-4 py-3 text-gray-400 transition-colors hover:bg-gray-800/50">
                    <Users size={20} />
                    <span className="font-medium">Users (Soon)</span>
                </div>
            </nav>
        </aside>
    );
}
