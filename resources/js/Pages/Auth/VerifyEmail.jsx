import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <>
            <Head title="Verify Email" />
            <div className="min-h-screen bg-slate-900 flex items-center justify-center px-4">
                <div className="w-full max-w-sm">
                    <div className="text-center mb-8">
                        <span className="text-3xl font-bold text-white tracking-tight">GVOS</span>
                    </div>
                    <div className="bg-white rounded-2xl shadow-xl p-8">
                        <h2 className="text-xl font-semibold text-slate-800 mb-3">Verify your email</h2>
                        <p className="text-sm text-slate-500 mb-6">
                            A verification link has been sent to your email address. Please check your inbox and click the link to continue.
                        </p>

                        {status === 'verification-link-sent' && (
                            <div className="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                                A new verification link has been sent.
                            </div>
                        )}

                        <form onSubmit={submit}>
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors"
                            >
                                {processing ? 'Sending…' : 'Resend verification email'}
                            </button>
                        </form>

                        <div className="mt-4 text-center">
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="text-sm text-slate-500 hover:text-slate-700"
                            >
                                Sign out
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
