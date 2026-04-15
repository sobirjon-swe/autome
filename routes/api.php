<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BranchStockController;
use App\Http\Controllers\Api\StockTransferController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\HandoverController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ReconciliationController;
use App\Http\Controllers\Api\DebtController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\NotificationController;

// ============================================================
// Auth (public)
// ============================================================
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ============================================================
// Protected routes
// ============================================================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // --------------------------------------------------------
    // Branches
    // --------------------------------------------------------
    Route::get('/branches/compare', [BranchController::class, 'compare']);
    Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches', [BranchController::class, 'store']);
    Route::put('/branches/{branch}', [BranchController::class, 'update']);
    Route::patch('/branches/{branch}/toggle', [BranchController::class, 'toggle']);
    Route::get('/branches/{branch}/stats', [BranchController::class, 'stats']);

    // Branch stocks (nested under branch)
    Route::get('/branches/{branch}/stocks/alerts', [BranchStockController::class, 'alerts']);
    Route::get('/branches/{branch}/stocks', [BranchStockController::class, 'index']);
    Route::patch('/branches/{branch}/stocks/{product}', [BranchStockController::class, 'update']);

    // --------------------------------------------------------
    // Categories
    // --------------------------------------------------------
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // --------------------------------------------------------
    // Products
    // --------------------------------------------------------
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // --------------------------------------------------------
    // Stock Transfers
    // --------------------------------------------------------
    Route::get('/stock-transfers', [StockTransferController::class, 'index']);
    Route::post('/stock-transfers', [StockTransferController::class, 'store']);
    Route::patch('/stock-transfers/{stockTransfer}/confirm', [StockTransferController::class, 'confirm']);
    Route::patch('/stock-transfers/{stockTransfer}/cancel', [StockTransferController::class, 'cancel']);

    // --------------------------------------------------------
    // Shifts
    // --------------------------------------------------------
    Route::get('/shifts/current', [ShiftController::class, 'current']);
    Route::get('/shifts', [ShiftController::class, 'index']);
    Route::post('/shifts/open', [ShiftController::class, 'open']);
    Route::post('/shifts/close', [ShiftController::class, 'close']);
    Route::get('/shifts/{shift}', [ShiftController::class, 'show']);

    // --------------------------------------------------------
    // Shift Handovers
    // --------------------------------------------------------
    Route::get('/handovers', [HandoverController::class, 'index']);
    Route::post('/handovers/confirm', [HandoverController::class, 'confirm']);
    Route::post('/handovers/dispute', [HandoverController::class, 'dispute']);

    // --------------------------------------------------------
    // Sales
    // --------------------------------------------------------
    Route::get('/sales', [SaleController::class, 'index']);
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales/{sale}', [SaleController::class, 'show']);
    Route::post('/sales/{sale}/receipt', [SaleController::class, 'uploadReceipt']);

    // --------------------------------------------------------
    // Expenses
    // --------------------------------------------------------
    Route::get('/expense-categories', [ExpenseController::class, 'categories']);
    Route::post('/expense-categories', [ExpenseController::class, 'storeCategory']);
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // --------------------------------------------------------
    // Reconciliation
    // --------------------------------------------------------
    Route::get('/reconciliations/summary', [ReconciliationController::class, 'summary']);
    Route::post('/reconciliations', [ReconciliationController::class, 'store']);
    Route::patch('/reconciliations/{reconciliation}', [ReconciliationController::class, 'update']);

    // --------------------------------------------------------
    // Debts
    // --------------------------------------------------------
    Route::get('/debts/summary', [DebtController::class, 'summary']);
    Route::get('/debts', [DebtController::class, 'index']);
    Route::post('/debts', [DebtController::class, 'store']);
    Route::patch('/debts/{debt}/pay', [DebtController::class, 'pay']);

    // --------------------------------------------------------
    // KPI
    // --------------------------------------------------------
    Route::get('/kpi/settings', [KpiController::class, 'settings']);
    Route::post('/kpi/settings', [KpiController::class, 'storeSetting']);
    Route::put('/kpi/settings/{kpiSetting}', [KpiController::class, 'updateSetting']);
    Route::get('/kpi/results', [KpiController::class, 'results']);
    Route::get('/kpi/my', [KpiController::class, 'myResults']);

    // --------------------------------------------------------
    // Schedules
    // --------------------------------------------------------
    Route::get('/schedules/my', [ScheduleController::class, 'my']);
    Route::get('/schedules/summary', [ScheduleController::class, 'summary']);
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::post('/schedules', [ScheduleController::class, 'store']);
    Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']);
    Route::patch('/schedules/{schedule}/swap', [ScheduleController::class, 'swap']);

    // --------------------------------------------------------
    // Inventories
    // --------------------------------------------------------
    Route::get('/inventories', [InventoryController::class, 'index']);
    Route::post('/inventories', [InventoryController::class, 'store']);
    Route::get('/inventories/{inventory}', [InventoryController::class, 'show']);
    Route::put('/inventories/{inventory}/items', [InventoryController::class, 'updateItems']);
    Route::patch('/inventories/{inventory}/complete', [InventoryController::class, 'complete']);

    // --------------------------------------------------------
    // Users
    // --------------------------------------------------------
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::patch('/users/{user}/role', [UserController::class, 'assignRole']);
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggle']);
    Route::post('/users/{user}/branches', [UserController::class, 'attachBranch']);
    Route::delete('/users/{user}/branches/{branch}', [UserController::class, 'detachBranch']);

    // --------------------------------------------------------
    // Reports
    // --------------------------------------------------------
    Route::prefix('reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily']);
        Route::get('/weekly', [ReportController::class, 'weekly']);
        Route::get('/monthly', [ReportController::class, 'monthly']);
        Route::get('/by-category', [ReportController::class, 'byCategory']);
        Route::get('/by-employee', [ReportController::class, 'byEmployee']);
        Route::get('/by-product', [ReportController::class, 'byProduct']);
        Route::get('/by-branch', [ReportController::class, 'byBranch']);
        Route::get('/profit-loss', [ReportController::class, 'profitLoss']);
    });

    // --------------------------------------------------------
    // Notifications
    // --------------------------------------------------------
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
});
