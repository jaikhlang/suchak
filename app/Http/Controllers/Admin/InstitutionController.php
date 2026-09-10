<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InstitutionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInstitutionRequest;
use App\Http\Requests\Admin\UpdateInstitutionRequest;
use App\Models\Institution;
use App\Models\InstitutionAlias;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InstitutionController extends Controller
{
    /**
     * Display a paginated listing of recruiting institutions.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $type = $request->query('type');
        $stateId = $request->query('state_id');
        $verified = $request->query('verified');

        $institutions = Institution::query()
            ->with(['state', 'aliases'])
            ->search($search)
            ->when($type, fn ($query, $t) => $query->where('institution_type', $t))
            ->when($stateId, fn ($query, $s) => $query->where('state_id', $s))
            ->when($verified !== null && $verified !== '', fn ($query) => $query->where('is_verified', filter_var($verified, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $types = array_map(
            fn (InstitutionType $t) => ['value' => $t->value, 'label' => $t->label()],
            InstitutionType::cases()
        );

        $states = State::orderBy('name')->get(['id', 'name', 'iso_code']);

        return Inertia::render('admin/institutions/Index', [
            'institutions' => $institutions,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'state_id' => $stateId,
                'verified' => $verified,
            ],
            'types' => $types,
            'states' => $states,
        ]);
    }

    /**
     * Show the form for creating a new institution.
     */
    public function create(): Response
    {
        $states = State::orderBy('name')->get(['id', 'name', 'iso_code']);
        $parents = Institution::orderBy('name')->get(['id', 'name', 'short_name']);
        $types = array_map(
            fn (InstitutionType $t) => ['value' => $t->value, 'label' => $t->label()],
            InstitutionType::cases()
        );

        return Inertia::render('admin/institutions/Create', [
            'states' => $states,
            'parents' => $parents,
            'types' => $types,
        ]);
    }

    /**
     * Store a newly created institution in storage.
     */
    public function store(StoreInstitutionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $aliasesData = $data['aliases'] ?? [];
        unset($data['aliases']);

        DB::transaction(function () use ($data, $aliasesData) {
            $institution = Institution::create($data);

            foreach ($aliasesData as $alias) {
                if (! empty($alias['alias'])) {
                    InstitutionAlias::create([
                        'institution_id' => $institution->id,
                        'alias' => trim($alias['alias']),
                        'locale' => $alias['locale'] ?? 'en',
                        'is_primary' => (bool) ($alias['is_primary'] ?? false),
                    ]);
                }
            }
        });

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Institution created successfully.');
    }

    /**
     * Show the form for editing the specified institution.
     */
    public function edit(Institution $institution): Response
    {
        $institution->load(['aliases', 'state']);
        $states = State::orderBy('name')->get(['id', 'name', 'iso_code']);
        $parents = Institution::where('id', '!=', $institution->id)
            ->orderBy('name')
            ->get(['id', 'name', 'short_name']);

        $types = array_map(
            fn (InstitutionType $t) => ['value' => $t->value, 'label' => $t->label()],
            InstitutionType::cases()
        );

        return Inertia::render('admin/institutions/Edit', [
            'institution' => $institution,
            'states' => $states,
            'parents' => $parents,
            'types' => $types,
        ]);
    }

    /**
     * Update the specified institution in storage.
     */
    public function update(UpdateInstitutionRequest $request, Institution $institution): RedirectResponse
    {
        $data = $request->validated();
        $aliasesData = $data['aliases'] ?? [];
        unset($data['aliases']);

        DB::transaction(function () use ($institution, $data, $aliasesData) {
            $institution->update($data);

            // Synchronize aliases
            $institution->aliases()->delete();
            foreach ($aliasesData as $alias) {
                if (! empty($alias['alias'])) {
                    InstitutionAlias::create([
                        'institution_id' => $institution->id,
                        'alias' => trim($alias['alias']),
                        'locale' => $alias['locale'] ?? 'en',
                        'is_primary' => (bool) ($alias['is_primary'] ?? false),
                    ]);
                }
            }
        });

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Institution updated successfully.');
    }

    /**
     * Remove the specified institution from storage.
     */
    public function destroy(Institution $institution): RedirectResponse
    {
        $institution->delete();

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Institution deleted successfully.');
    }
}
