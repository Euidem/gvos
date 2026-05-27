import AppLayout from '@/Layouts/AppLayout';

export default function Talent() {
    return (
        <AppLayout title="Talent Workspace">
            <div className="max-w-4xl">
                <div className="bg-white rounded-xl border border-slate-200 p-8 mb-6">
                    <div className="flex items-center gap-4 mb-4">
                        <div className="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <span className="text-2xl">💼</span>
                        </div>
                        <div>
                            <h2 className="text-xl font-semibold text-slate-900">GVOS Talent Dashboard</h2>
                            <p className="text-slate-500 text-sm">GVOS Talent Portal — Your work area</p>
                        </div>
                    </div>
                    <div className="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                        <p className="text-amber-800 text-sm font-medium">Phase 0 Placeholder</p>
                        <p className="text-amber-700 text-sm mt-1">
                            Your full workspace (tasks, time tracking, chat, files, daily reports) will be available in Phase 4–7.
                        </p>
                    </div>
                </div>

                <div className="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-4">
                    <p className="text-blue-800 text-sm">
                        <span className="font-semibold">Notice:</span> By using GVOS, you acknowledge that your work activity is tracked and monitored.
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {[
                        { label: 'Portal', value: 'GVOS Talent Portal', color: 'text-slate-700' },
                        { label: 'Access Level', value: 'Assigned Workspaces', color: 'text-emerald-600' },
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
