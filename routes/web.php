<?php

use App\Http\Controllers\CrmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebsiteSettingController;
use App\Models\Notification;
use App\Services\CentrifugoService;
use Buki\AutoRoute\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth')->post('/centrifugo/token', function (Request $request) {
    if (! config('langkahkecil.notification_enable')) {
        return response()->json(['token' => 'disabled']);
    }

    $centrifugo = app(CentrifugoService::class);
    $user = Auth::user();

    if ($request->input('channel')) {
        return response()->json([
            'token' => $centrifugo->generateSubscriptionToken((string) $user->id, $request->input('channel')),
        ]);
    }

    return response()->json([
        'token' => $centrifugo->generateConnectionToken((string) $user->id),
    ]);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/laundry/picker', [LaundryController::class, 'picker'])->name('laundry.picker');
    Route::post('/laundry/select', [LaundryController::class, 'select'])->name('laundry.select');
});

Route::middleware(['auth', 'verified', 'access', 'laundry.selected'])->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('/crm', CrmController::class)->name('crm.dashboard');

    Route::auto('/user', 'UsersController', ['name' => 'user']);

    Route::auto('/kategori', 'KategoriController', ['name' => 'kategori']);

    Route::auto('/customer', 'CustomerController', ['name' => 'customer']);

    Route::auto('/product', 'ProductController', ['name' => 'product']);

    Route::auto('/discount', 'DiscountController', ['name' => 'discount']);

    Route::auto('/order-status', 'OrderStatusController', ['name' => 'order-status']);

    Route::view('/pos', 'pages.order.pos')->name('pos.index');

    Route::auto('/order', 'OrderController', ['name' => 'order']);

    Route::auto('/expense', 'ExpenseController', ['name' => 'expense']);

    Route::post('/order/{id}/transit', [OrderController::class, 'postTransit'])->name('order.transit');
    Route::get('/order/{id}/struk-pdf', [OrderController::class, 'getStrukPdf'])->name('order.strukpdf');
    Route::get('/order/{id}/print', [OrderController::class, 'getPrint'])->name('order.print');

    Route::get('/native-bridge-test', function () {
        return view('pages.settings.native-bridge-test');
    })->name('native-bridge-test');

    Route::get('/settings/website', [WebsiteSettingController::class, 'index'])->name('settings.website');
    Route::post('/settings/website', [WebsiteSettingController::class, 'save'])->name('settings.website.save');

    Route::prefix('notifications-web')->group(function () {
        Route::get('/', function (Request $request) {
            $notifications = Notification::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->limit($request->input('limit', 50))
                ->get();

            $unreadCount = Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count();

            return response()->json([
                'notifications' => $notifications->map(fn ($n) => [
                    'id' => $n->id,
                    'icon' => $n->icon,
                    'iconColor' => $n->icon_color,
                    'title' => $n->title,
                    'body' => $n->body,
                    'url' => $n->url,
                    'type' => $n->type,
                    'read' => $n->read,
                    'time' => $n->created_at?->diffForHumans() ?? '',
                    'created_at' => $n->created_at->toIso8601String(),
                ]),
                'unread_count' => $unreadCount,
            ]);
        });

        Route::put('/{id}/read', function (int $id) {
            $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
            $notification->update(['read' => true]);

            return response()->json(['message' => 'Marked as read']);
        });

        Route::put('/read-all', function () {
            Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->update(['read' => true]);

            return response()->json(['message' => 'All marked as read']);
        });
    });
});

require __DIR__.'/settings.php';
