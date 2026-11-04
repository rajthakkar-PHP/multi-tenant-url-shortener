<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $companies = Company::query()
            ->withCount('users')
            ->orderBy('name');

        if (! $request->user()->role->hasPermission('companies.view:any')) {
            $companies->whereKey($request->user()->company_id);
        }

        return view('companies.index', [
            'companies' => $companies->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('companies.create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $company = Company::create($request->validated());

        return redirect()
            ->route('companies.show', $company)
            ->with('status', 'Company created.');
    }

    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        return view('companies.show', [
            'company' => $company,
            'users' => $company->users()->with('role')->orderBy('name')->paginate(15),
            'invitations' => $company->invitations()->with('role')->latest()->limit(20)->get(),
        ]);
    }
}
