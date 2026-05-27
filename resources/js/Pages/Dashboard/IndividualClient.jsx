import AppLayout from '@/Layouts/AppLayout';

export default function IndividualClient() {
    return (
        <AppLayout title="Client Dashboard">
            <div className="max-w-4xl">
                <div className="bg-white rounded-xl border border-slate-200 p-8 mb-6">
                    <div className="flex items-center gap-4 mb-4">
                        <div className="w-12 h-12 bg-sky-100 rounded-xl flex items-center justify-center">
                            <span className="text-2xl">🏠</span>
                        </div>
                        <div>
                            <h2 className="text-xl font-semibold text-slate-900">GVOS Client Dashboard</h2>
                            <p className="text-slate-500 text-sm">GVOS Client Portal — Your workspace overview</p>
                        </div>
                    </div>
                    <div className="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                        <p className="text-amber-800 text-sm font-medium">Phase 0 Placeholder</p>
                        <p className="text-amber-700 text-sm mt-1">
                            Your full client portal (workspace activity, tasks, weekly summaries, files, billing) will be available in Phase 4–8.
                        </p>
                    </div>
                </div>

                <div className="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-4">
                    <p className="text-blue-800 text-sm">
                        <span className="font-semibold">Notice:</span> Activity on this platform is tracked and monitored by GetVirtual.
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {[
                        { label: 'Portal', value: 'GVOS Client Portal', color: 'text-slate-700' },
                        { label: 'Account Type', value: 'Individual Client', color: 'text-sky-600' },
                        { label: 'Build Phase', value: 'Phase 0 — Foundation', color: 'text-indigo-600' },
                    ].map((item) => (
                        <div key={item.label} className="bg-white rounded-xl border border-slate-200 p-5">
                            <p className="text-xs text-slate-500 uppercase tracking-wide font-medium">{item.label}</p>
                            <p className={`text-base font-semibold mt-1 ${item.color}`}>{item.value}</p>
                        </div>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
