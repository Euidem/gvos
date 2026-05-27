import AppLayout from '@/Layouts/AppLayout';

export default function ActiveLead() {
    return (
        <AppLayout title="Your Inquiry Status">
            <div className="max-w-4xl">
                <div className="bg-white rounded-xl border border-slate-200 p-8 mb-6">
                    <div className="flex items-center gap-4 mb-4">
                        <div className="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                            <span className="text-2xl">🔔</span>
                        </div>
                        <div>
                            <h2 className="text-xl font-semibold text-slate-900">GVOS Lead Dashboard</h2>
                            <p className="text-slate-500 text-sm">Welcome — Your inquiry is being reviewed by GetVirtual</p>
                        </div>
                    </div>
                    <div className="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                        <p className="text-amber-800 text-sm font-medium">Phase 0 Placeholder</p>
                        <p className="text-amber-700 text-sm mt-1">
                            Your lead status, price estimate, and trial workspace will be available here in Phase 3.
                            A GetVirtual team member will be in touch shortly.
                        </p>
                    </div>
                </div>

                <div className="bg-white rounded-xl border border-slate-200 p-6">
                    <h3 className="font-semibold text-slate-800 mb-3">What happens next?</h3>
                    <ol className="space-y-2 text-sm text-slate-600">
                        <li className="flex gap-3">
                            <span className="flex-shrink-0 w-5 h-5 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-semibold">1</span>
                            GetVirtual reviews your inquiry
                        </li>
                        <li className="flex gap-3">
                            <span className="flex-shrink-0 w-5 h-5 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-semibold">2</span>
                            A price estimate is prepared for you
                        </li>
                        <li className="flex gap-3">
                            <span className="flex-shrink-0 w-5 h-5 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-semibold">3</span>
                            You review and accept the estimate
                        </li>
                        <li className="flex gap-3">
                            <span className="flex-shrink-0 w-5 h-5 bg-indigo-100 text-indigo-700 rounded-full text-xs flex items-center justify-center font-semibold">4</span>
                            Your trial workspace is activated
                        </li>
                    </ol>
                </div>
            </div>
        </AppLayout>
    );
}
