import { Head, Link } from '@inertiajs/react';

/**
 * Register page — stub only.
 *
 * GVOS does not allow self-registration. Users are created by
 * Operations Admins in the GVOS Ops Console (Filament admin panel).
 *
 * This page exists to satisfy the route definition.
 * It will be replaced with a proper notice page in Phase 1.
 */
export default function Register() {
    return (
        <>
            <Head title="Register" />
            <div className="min-h-screen bg-slate-900 flex items-center justify-center px-4">
                <div className="w-full max-w-sm">
                    <div className="text-center mb-8">
                        <span className="text-3xl font-bold text-white tracking-tight">GVOS</span>
                        <p className="text-slate-400 text-sm mt-1">GetVirtual Operations System</p>
                    </div>
                    <div className="bg-white rounded-2xl shadow-xl p-8 text-center">
                        <div className="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span className="text-2xl">🔒</span>
                        </div>
                        <h2 className="text-lg font-semibold text-slate-800 mb-2">Registration is not available</h2>
                        <p className="text-sm text-slate-500 mb-6">
                            GVOS accounts are created by GetVirtual administrators only.
                            If you have been invited, please check your email for a login link.
                        </p>
                        <Link
                            href={route('login')}
                            className="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors"
                        >
                            Go to sign in
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
