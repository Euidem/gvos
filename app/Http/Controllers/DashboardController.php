<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function superAdmin(): View
    {
        return view('dashboard.super-admin');
    }

    public function operationsAdmin(): View
    {
        return view('dashboard.operations-admin');
    }

    public function lineManager(): View
    {
        return view('dashboard.line-manager');
    }

    public function talent(): View
    {
        return view('dashboard.talent');
    }

    public function client(): View
    {
        $role = auth()->user()->getGvosRoleName();

        return match ($role) {
            'business_client_admin' => view('dashboard.business-client-admin'),
            'business_client_staff' => view('dashboard.business-client-staff'),
            default                 => view('dashboard.individual-client'),
        };
    }

    public function lead(): View
    {
        return view('dashboard.active-lead');
    }
}
