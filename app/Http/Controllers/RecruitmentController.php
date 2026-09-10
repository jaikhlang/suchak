<?php

namespace App\Http\Controllers;

use App\Actions\Publishing\GenerateJobPostingSchemaAction;
use App\Enums\InstitutionType;
use App\Enums\NoticeStatus;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Post;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RecruitmentController extends Controller
{
    public function home(Request $request): Response
    {
        $publishedNoticesQuery = Notice::where('status', NoticeStatus::Published);

        $totalVacancies = (int) (clone $publishedNoticesQuery)->sum('total_vacancies');
        $activeNoticesCount = (int) (clone $publishedNoticesQuery)
            ->where(function ($q) {
                $q->whereNull('application_end_at')
                    ->orWhere('application_end_at', '>=', now());
            })->count();
        $totalInstitutions = Institution::where('is_active', true)->count();

        // Featured / Latest Notices
        $featuredPosts = Post::with(['notice.institution.state', 'notice.positions.reservations'])
            ->where('status', 'published')
            ->latest('published_at')
            ->take(6)
            ->get();

        $states = State::where('is_active', true)->orderBy('name')->get(['id', 'name', 'iso_code']);

        $topInstitutions = Institution::where('is_verified', true)
            ->where('is_active', true)
            ->take(8)
            ->get(['id', 'name', 'short_name', 'slug', 'official_domain']);

        return Inertia::render('Welcome', [
            'featuredPosts' => $featuredPosts,
            'stats' => [
                'total_vacancies' => $totalVacancies,
                'active_notices' => $activeNoticesCount,
                'total_institutions' => $totalInstitutions,
            ],
            'states' => $states,
            'topInstitutions' => $topInstitutions,
        ]);
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $stateId = $request->input('state_id');
        $institutionId = $request->input('institution_id');
        $institutionType = $request->input('institution_type');
        $category = $request->input('category');
        $deadline = $request->input('deadline', 'active');

        $query = Post::with(['notice.institution.state', 'notice.positions.reservations'])
            ->where('status', 'published');

        // Full-Text Search
        if ($search) {
            if (DB::getDriverName() === 'pgsql') {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("search_vector @@ websearch_to_tsquery('english', ?)", [$search])
                        ->orWhereRaw('title % ?', [$search])
                        ->orWhere('title', 'ilike', "%{$search}%");
                });
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            }
        }

        // State Filter
        if ($stateId) {
            $query->whereHas('notice.institution', function ($q) use ($stateId) {
                $q->where('state_id', $stateId);
            });
        }

        // Institution Filter
        if ($institutionId) {
            $query->whereHas('notice', function ($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            });
        }

        // Institution Type Filter
        if ($institutionType) {
            $query->whereHas('notice.institution', function ($q) use ($institutionType) {
                $q->where('institution_type', $institutionType);
            });
        }

        // Reservation Category Filter
        if ($category) {
            $categoryVal = strtoupper($category);
            $query->whereHas('notice.positions.reservations', function ($q) use ($categoryVal) {
                $q->where('category', $categoryVal)->where('vacancies', '>', 0);
            });
        }

        // Deadline Filter
        if ($deadline === 'active') {
            $query->whereHas('notice', function ($q) {
                $q->whereNull('application_end_at')->orWhere('application_end_at', '>=', now());
            });
        } elseif ($deadline === 'expired') {
            $query->whereHas('notice', function ($q) {
                $q->where('application_end_at', '<', now());
            });
        }

        $posts = $query->latest('published_at')->paginate(12)->withQueryString();

        $states = State::where('is_active', true)->orderBy('name')->get(['id', 'name', 'iso_code']);
        $institutions = Institution::where('is_active', true)->orderBy('name')->get(['id', 'name', 'short_name']);

        return Inertia::render('recruitment/Index', [
            'posts' => $posts,
            'filters' => [
                'search' => $search,
                'state_id' => $stateId,
                'institution_id' => $institutionId,
                'institution_type' => $institutionType,
                'category' => $category,
                'deadline' => $deadline,
            ],
            'states' => $states,
            'institutions' => $institutions,
            'institutionTypes' => array_map(fn ($t) => ['value' => $t->value, 'label' => $t->label()], InstitutionType::cases()),
        ]);
    }

    public function show(string $institutionSlug, string $postSlug, GenerateJobPostingSchemaAction $schemaGenerator): Response
    {
        $institution = Institution::where('slug', $institutionSlug)->firstOrFail();

        $post = Post::with([
            'notice.institution.state',
            'notice.positions.reservations',
            'notice.applicationDetail',
            'notice.eligibilityRule',
            'notice.evidence',
            'notice.revisions.triggeringArtifact',
            'notice.sourceArtifact',
        ])
            ->where('slug', $postSlug)
            ->whereHas('notice', function ($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            })
            ->where('status', 'published')
            ->firstOrFail();

        $schemaJson = $schemaGenerator->execute($post->notice);

        return Inertia::render('recruitment/Show', [
            'post' => $post,
            'notice' => $post->notice,
            'schemaJson' => $schemaJson,
        ]);
    }
}
