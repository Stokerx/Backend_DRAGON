<?php

use App\Http\Controllers\Login\AuthController;
use App\Http\Controllers\Login\LogoutController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\UsersHistoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Login\AdminUserController;
use App\Http\Controllers\ActivitySeriesUserController;
use App\Http\Controllers\UsersSerieController;
use App\Http\Controllers\ChapterController;
use Spatie\Permission\Middleware\RoleMiddleware;

//rutas que no requieren su autenticación
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Rutas que requieren autenticación
Route::middleware(['auth:sanctum'])->group(function () {
    // Rutas GET
    Route::get('/dashboard', function () {
        return 'Endpoint Ambos';
        //logout

    })->name('dashboard');
    Route::middleware(['auth:sanctum'])->get('/user-status', [App\Http\Controllers\Login\UserStatusController::class, 'getUserStatus']);
    Route::get('/getUserProfile', [AdminUserController::class, 'getUserProfile'])->name('getUserProfile');
    Route::get('/series', [SeriesController::class, 'getAllSeries'])->name('getAllSeries');
    Route::get('/getSumFunctionValueByType/{user_id}', [ActivitySeriesUserController::class, 'getSumFunctionValueByType'])->name('getSumFunctionValueByType');
    Route::get('/getTotalSum/{user_id}', [ActivitySeriesUserController::class, 'getTotalSum'])->name('getTotalSum');
    Route::get('/GetUserActivityCurrentMonth/{user_Id}', [ActivitySeriesUserController::class, 'GetUserActivityCurrentMonth'])->name('GetUserActivityCurrentMonth');
    Route::post('/updateProfileUser/' ,[AdminUserController::class, 'updateProfileUser'])->name('updateProfileUser');
    Route::get('/getAssignedAndUnassignedUsers/{serieId}', [UsersSerieController::class, 'getAssignedAndUnassignedUsers'])->name('getAssignedAndUnassignedUsers');
    // Rutas POST
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // Rutas que requieren el rol de administrador
    Route::group(['middleware' => [RoleMiddleware::class . ':admin']], function () {
        // Rutas POST
        Route::post('/deleteSerie/{id}', [SeriesController::class, 'deleteSerie'])->name('deleteSerie');
        Route::put('/updateFunctionsSerie');
        Route::delete('/user-activities/{activity}', [ActivitySeriesUserController::class, 'deleteUserActivity'])->name('deleteUserActivity');
        //ruta reactiveUsers
        Route::put('/reactiveUsers/{id}', [AdminUserController::class, 'reactiveUsers'])->name('reactiveUsers');
        Route::get('/series/{seriesId}/registrations', [ActivitySeriesUserController::class, 'getSeriesRegistrations'])->name('getSeriesRegistrations');
        //Route::post('/createSeriesActivity', [ActivitySeriesUserController::class, 'insertActivitySeriesUserOnSeriesCreation'])->name('createSeriesActivity');

        // Rutas PUT
        Route::put('/desactiveUsers/{id}', [AdminUserController::class, 'desactiveUsers'])->name('desactiveUsers');
        Route::post('/updateUser/{id}', [AdminUserController::class, 'updateUser'])->name('updateUser');
        Route::put('/updateStatus/{id}', [SeriesController::class, 'updateStatus'])->name('updateStatus');
        Route::put('/updateFunctionsSerie/{id}', [SeriesController::class, 'updateFunctionsSerie'])->name('updateFunctionsSerie');

        // Rutas GET
        Route::get('/ActivitySeriesGeneral', [ActivitySeriesUserController::class, 'getLastUserActiviteGeneral'])->name('ActivitySeriesGeneral');
    });

    // Rutas que requieren los roles de administrador, dragon o user
    Route::group(['middleware' => [RoleMiddleware::class . ':admin|dragon|user']], function () {
        // Rutas POST
        Route::post('/createChapter', [ChapterController::class, 'createChapter'])->name('createChapter');
        Route::get('/series/{series}/functions', [SeriesController::class, 'getFunctionSeries'])->name('getFunctionSeries');
    });

    // Rutas que requieren el permiso de ver la actividad de las series
    Route::middleware([RoleMiddleware::class . ':admin|dragon'])->group(function () {
        // Rutas GET
        Route::get('/ActivitySeriesGeneral', [ActivitySeriesUserController::class, 'getLastUserActiviteGeneral'])->name('ActivitySeriesGeneral');
        Route::get('/ActivitySeriesUser/', [ActivitySeriesUserController::class, 'getLastUserActivites'])->name('ActivitySeriesUser');
        Route::post('/updateSerie/{id}', [SeriesController::class, 'updateSerie'])->name('updateSerie');
        Route::post('/updateUser/{id}', [AdminUserController::class, 'updateUser'])->name('updateUser');
        Route::post('/assignSeriesToUser', [UsersSerieController::class, 'assignSeriesToUser'])->name('assignSeriesToUser');
        Route::post('/unassignSeriesToUser', [UsersSerieController::class, 'unassignSeriesToUser'])->name('unassignSeriesToUser');
        Route::post('/createSerie', [SeriesController::class, 'createSerie'])->name('createSerie');
        Route::post('/createUser', [AdminUserController::class, 'createUser'])->name('createUser');
        Route::get('/exportExcelTotalAño', [UsersHistoryController::class, 'exportExcelTotalAño'])->name('exportExcelTotalAño');
        //getAllUsersWithTotalValueForMonthYear
        Route::post('/exportExcelYearMonth', [UsersHistoryController::class, 'exportExcelYearMonth'])->name('getAllUsersWithTotalValueForMonthYear');
    });

    // Rutas que requieren el permiso de ver el historial del usuario
    Route::middleware([RoleMiddleware::class . ':admin|dragon|user'])->group(function () {
        // Rutas GET
        Route::put('/updateUsernormal/{id}', [AdminUserController::class, 'updateUsernormal'])->name('updateUsernormal');
        Route::get('/getUserChaptersWithFunctions/{user_id}', [UsersHistoryController::class, 'getUserChaptersWithFunctions'])->name('getUserChaptersWithFunctions');
        Route::get('/getUserFunctionTotal/{user_id}', [UsersHistoryController::class, 'getUserFunctionTotal'])->name('getUserFunctionTotal');
        Route::get('/getAllUsersWithTotalValue', [UsersHistoryController::class, 'getAllUsersWithTotalValue'])->name('getAllUsersWithTotalValue');
        Route::get('/getListUsers', [ActivitySeriesUserController::class, 'getListUsers'])->name('getListUsers');
    });
});