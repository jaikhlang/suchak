<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\NoticeManagementController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionController;

// Public Candidate Portal, Subscriptions & Feeds
Route::get('/', [RecruitmentController::class, 'home'])->name('home');
Route::get('recruitment', [RecruitmentController::class, 'index'])->name('recruitment.index');
Route::get('recruitment/{institutionSlug}/{postSlug}', [RecruitmentController::class, 'show'])->name('recruitment.show');
Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
Route::get('subscriptions/{token}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('feeds/latest.xml', [FeedController::class, 'latest'])->name('feeds.latest');
Route::get('feeds/state/{stateSlug}.xml', [FeedController::class, 'byState'])->name('feeds.state');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware(['role:admin,moderator'])->group(function () {
        Route::get('states', [StateController::class, 'index'])->name('states.index');
        Route::resource('institutions', InstitutionController::class);
        Route::post('sources/{source}/crawl', [SourceController::class, 'crawlNow'])->name('sources.crawl');
        Route::post('sources/artifacts/{artifact}/extract', [SourceController::class, 'extractArtifact'])->name('sources.artifacts.extract');
        Route::resource('sources', SourceController::class);

        // Notices & Corrigenda Catalog
        Route::get('notices', [NoticeManagementController::class, 'index'])->name('notices.index');
        Route::get('notices/{notice}', [NoticeManagementController::class, 'show'])->name('notices.show');
        Route::post('notices/{notice}/republish', [NoticeManagementController::class, 'republish'])->name('notices.republish');
        Route::post('notices/{notice}/archive', [NoticeManagementController::class, 'archive'])->name('notices.archive');
        Route::post('notices/{notice}/dispatch-distribution', [NoticeManagementController::class, 'dispatchDistribution'])->name('notices.dispatch-distribution');

        // Moderation Workbench
        Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
        Route::get('moderation/{notice}', [ModerationController::class, 'show'])->name('moderation.show');
        Route::post('moderation/{notice}/approve', [ModerationController::class, 'approve'])->name('moderation.approve');
        Route::post('moderation/{notice}/reject', [ModerationController::class, 'reject'])->name('moderation.reject');
        Route::post('moderation/{notice}/corrigendum', [ModerationController::class, 'markCorrigendum'])->name('moderation.corrigendum');
        Route::post('moderation/{notice}/merge-duplicate', [ModerationController::class, 'mergeDuplicate'])->name('moderation.merge-duplicate');
        Route::put('moderation/{notice}', [ModerationController::class, 'update'])->name('moderation.update');
    });
});

require __DIR__.'/settings.php';
