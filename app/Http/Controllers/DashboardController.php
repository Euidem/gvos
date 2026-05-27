<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function superAdmin(): Response
    {
        return Inertia::render('Dashboard/SuperAdmin');
    }

    public function operationsAdmin(): Response
    {
        return Inertia::render('Dashboard/OperationsAdmin');
    }

    public function lineManager(): Response
    {
        return Inertia::render('Dashboard/LineManager');
    }

    public function talent(): Response
    {
        return Inertia::render('Dashboard/Talent');
    }

    public function client(): Response
    {
        $role = auth()->user()->getGvosRoleName();

        return Inertia::render('Dashboard/' . match ($role) {
            'business_client_admin' => 'BusinessClientAdmin',
            'business_client_staff' => 'BusinessClientStaff',
            default => 'IndividualClient',
        });
    }

    public function lead(): Response
    {
        return Inertia::render('Dashboard/ActiveLead');
    }
}
