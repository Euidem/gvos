import { Head, Link, useForm } from '@inertiajs/react';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <>
            <Head title="Reset Password" />
            <div className="min-h-screen bg-slate-900 flex items-center justify-center px-4">
                <div className="w-full max-w-sm">
                    <div className="text-center mb-8">
                        <span className="text-3xl font-bold text-white tracking-tight">GVOS</span>
                        <p className="text-slate-400 text-sm mt-1">GetVirtual Operations System</p>
                    </div>
                    <div className="bg-white rounded-2xl shadow-xl p-8">
                        <h2 className="text-xl font-semibold text-slate-800 mb-2">Reset your password</h2>
                        <p className="text-sm text-slate-500 mb-6">
                            Enter your email and we will send you a password reset link.
                        </p>

                        {status && (
                            <div className="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                                {status}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-slate-700 mb-1">
                                    Email address
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    autoFocus
                                />
                                {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                            </div>
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors"
                            >
                                {processing ? 'Sending…' : 'Send reset link'}
                            </button>
                        </form>

                        <div className="mt-4 text-center">
                            <Link href={route('login')} className="text-sm text-indigo-600 hover:text-indigo-500">
                                Back to sign in
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
