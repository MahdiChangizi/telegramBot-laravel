<?php

use App\Models\Invitation;
use App\Models\Spin;
use App\Models\User as TelegramUsers;
use App\Models\Token;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTAuth;


Route::get('/last_spin', function () {
    $spin = Spin::where('user_id', auth()->user()->id)->latest('created_at')->first();
    if (is_null($spin)) return response()->json(true);
    $date = $spin->created_at->gt(Carbon::now()->subHours(24));
    return response()->json(!$date);
});

Route::get('/tokens', function (Request $request) {
    $userId = auth()->user()->id;
    $user = TelegramUsers::find($userId);

    // پیدا کردن توکن‌های کاربر
    $token = Token::where('user_id', $user->id)->first();

    return response()->json([
        'user' => $user->first_name,
        'total_tokens' => $token->amount,
    ]);
});

Route::post('/spin', function (Request $request) {
    $userId = auth()->user()->id;
    $tokenReceived = $request->input('token_received'); // مقدار توکنی که کاربر دریافت کرده

    // پیدا کردن کاربر
    $user = TelegramUsers::find($userId);

    // بررسی اینکه مقدار توکن باید مثبت و معتبر باشد
    if ($tokenReceived <= 0) {
        return response()->json(['message' => 'مقدار توکن نامعتبر است.'], 400);
    }

    // ذخیره اطلاعات Spin
    Spin::create([
        'user_id' => $user->id,
        'token_received' => $tokenReceived,
    ]);

    // آپدیت کردن تعداد توکن‌های کاربر
    $token = Token::firstOrCreate(['user_id' => $user->id]);
    $token->increment('amount', $tokenReceived);

    return response()->json([
        'message' => "شما $tokenReceived توکن دریافت کردید!",
        'total_tokens' => $token->amount,
    ]);
});

Route::get('/leaderboard', function () {
    // دریافت شناسه کاربر فعلی
    $currentUserId = auth()->user()->id; // از Auth برای دریافت شناسه کاربر استفاده می‌شود

    // دریافت اطلاعات کاربر فعلی
    $currentUser = TelegramUsers::withSum('tokens', 'amount')
        ->find($currentUserId);

    // دریافت 20 کاربر برتر از نظر تعداد توکن‌ها
    $topUsers = TelegramUsers::withSum('tokens', 'amount')
        ->orderBy('tokens_sum_amount', 'desc')
        ->take(20)
        ->get(['id', 'first_name', 'tokens_sum_amount as amount']);

    // شمارش کل کاربران
    $totalUsers = TelegramUsers::count();

    // ترکیب اطلاعات برای پاسخ
    $response = [
        'current_user' => [
            'id' => $currentUser->telegram_id,
            'username' => $currentUser->username,
            'first_name' => $currentUser->first_name,
            'tokens_amount' => $currentUser->tokens_sum_amount,
        ],
        'top_users' => $topUsers,
        'total_users' => $totalUsers,
    ];

    return response()->json($response);
});

Route::get('/friends', function () {
    $userId = auth()->user()->id;
    $user = TelegramUsers::find($userId);

    // گرفتن لیست دوستان دعوت‌شده
    $friends = Invitation::where('user_id', $user->id)
        ->with('invitedUser') // گرفتن اطلاعات کاربران دعوت‌شده
        ->get()
        ->pluck('invitedUser');

    return response()->json($friends);
});

// بخش Invite Code
Route::get('/invite-code', function () {
    $userId = auth()->user()->id;

    // پیدا کردن کاربر
    $user = TelegramUsers::find($userId);

    return response()->json([
        'invite_code' => $user->invite_code,
    ]);
});


Route::post('complete_task', function (Request $request) {
    $user = auth()->user();
    if ($user->point >= 6) return response()->json(['status' => 'failed', 'message' => 'Maximum level reached'], 400);
    $taskRewards = [0 => 100, 1 => 100, 2 => 50, 3 => 50, 4 => 50, 5 => 50];
    if (array_key_exists($request->task, $taskRewards) && $user->point == $request->task) {
        $token = Token::firstOrCreate(['user_id' => $user->id]);
        $token->increment('amount', $taskRewards[$request->task]);
        $user->point += 1;
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Task completed successfully',
            'new_point' => $user->point,
            'token_amount' => $token->amount
        ]);
    }
    return response()->json(['status' => 'failed', 'message' => 'Invalid task or point mismatch'], 400);
});

Route::get('complete_task', function () {
    $user = auth()->user();
    return $user->point;
});
