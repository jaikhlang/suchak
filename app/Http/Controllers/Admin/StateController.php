<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StateController extends Controller
{
    /**
     * Display a listing of Indian states and union territories.
     */
    public function index(Request $request): Response
    {
        $search = is_string($request->query('search')) ? $request->query('search') : null;
        $type = is_string($request->query('type')) ? $request->query('type') : null;

        $states = State::query()
            ->withCount(['institutions', 'districts'])
            ->when($search, function ($query, string $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'ilike', "%{$term}%")
                        ->orWhere('iso_code', 'ilike', "%{$term}%")
                        ->orWhere('capital', 'ilike', "%{$term}%");
                });
            })
            ->when($type, fn ($query, $t) => $query->where('type', $t))
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/states/Index', [
            'states' => $states,
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
            'stats' => [
                'total_states' => State::where('type', 'state')->count(),
                'total_uts' => State::where('type', 'union_territory')->count(),
            ],
        ]);
    }
}
