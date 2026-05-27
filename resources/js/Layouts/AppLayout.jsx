import { Link, usePage } from '@inertiajs/react';

export default function AppLayout({ children, title }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    return (
        <div className="min-h-screen bg-slate-50 flex">
            {/* Sidebar */}
            <aside className="w-64 bg-slate-900 text-slate-100 flex flex-col min-h-screen flex-shrink-0">
                {/* Logo */}
                <div className="px-6 py-5 border-b border-slate-700">
                    <span className="text-xl font-bold tracking-tight text-white">GVOS</span>
                    <span className="block text-xs text-slate-400 mt-0.5">GetVirtual Operations</span>
                </div>

                {/* Navigation */}
                <nav className="flex-1 px-4 py-6 space-y-1">
                    <Link
                        href="/"
                        className="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                    >
                        <span>Dashboard</span>
                    </Link>
                </nav>

                {/* User footer */}
                {user && (
                    <div className="px-4 py-4 border-t border-slate-700">
                        <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-full bg-slate-600 flex items-center justify-center text-xs font-semibold text-white flex-shrink-0">
                                {user.name?.charAt(0)?.toUpperCase()}
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-medium text-white truncate">{user.name}</p>
                                <p className="text-xs text-slate-400 truncate">{user.email}</p>
                            </div>
                        </div>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="mt-3 w-full text-left text-xs text-slate-400 hover:text-white transition-colors"
                        >
                            Sign out
                        </Link>
                    </div>
                )}
            </aside>

            {/* Main content */}
            <div className="flex-1 flex flex-col">
                {/* Top bar */}
                <header className="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
                    <h1 className="text-lg font-semibold text-slate-800">{title}</h1>
                    <div className="flex items-center gap-4">
                        <span className="text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-medium capitalize">
                            {user?.role?.replace(/_/g, ' ')}
                        </span>
                    </div>
                </header>

                {/* Page content */}
                <main className="flex-1 px-8 py-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
