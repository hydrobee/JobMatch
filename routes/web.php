<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\JobSourceController;
use App\Http\Controllers\PreferencesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('jobs', JobListingController::class)
        ->parameters(['jobs' => 'listing'])
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::patch('/jobs/{listing}/expired', [JobListingController::class, 'toggleExpired'])->name('jobs.expired');

    Route::resource('applications', ApplicationController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::patch('/applications/{application}/stage', [ApplicationController::class, 'stage'])->name('applications.stage');

    Route::resource('skills', SkillController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('sources', JobSourceController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/sources/{source}/fetch', [JobSourceController::class, 'fetch'])->name('sources.fetch');

    Route::get('/resume', [ResumeController::class, 'master'])->name('resume.master');
    Route::put('/resume', [ResumeController::class, 'saveMaster'])->name('resume.save');
    Route::post('/resume/upload', [ResumeController::class, 'uploadMaster'])->name('resume.upload');
    Route::post('/jobs/{listing}/resume', [ResumeController::class, 'tailor'])->name('jobs.resume');
    Route::get('/resumes/{resume}', [ResumeController::class, 'show'])->name('resumes.show');
    Route::put('/resumes/{resume}', [ResumeController::class, 'update'])->name('resumes.update');
    Route::get('/resumes/{resume}/download', [ResumeController::class, 'download'])->name('resumes.download');
    Route::get('/resumes/{resume}/print', [ResumeController::class, 'print'])->name('resumes.print');
    Route::delete('/resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');

    Route::get('/preferences', [PreferencesController::class, 'edit'])->name('preferences.edit');
    Route::patch('/preferences', [PreferencesController::class, 'update'])->name('preferences.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
