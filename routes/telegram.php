<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Spin;
use App\Models\Token;
use App\Models\User as TelegramUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Command\MenuButtonWebApp;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->onCommand('start', function (Nutgram $bot) {
    // دریافت اطلاعتات کاربر
    $user = $bot->user();
    $telegramUser = TelegramUsers::where('telegram_id', $user->id)->first();

    if (!$telegramUser) {
        // اگر کاربر وجود ندارد، ایجاد کاربر جدید
        $telegramUser = TelegramUsers::create([
            'telegram_id' => $user->id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'invite_code' => $user->username
        ]);
    }

    // ورود کاربر (login) برای تولید توکن JWT
    $token = Auth::login($telegramUser);

    $tokenReceived = rand(100, 1000);
    Spin::create([
        'user_id' => $telegramUser->id,
        'token_received' => $tokenReceived,
    ]);
    $tokenModel = Token::firstOrCreate(['user_id' => $telegramUser->id]);
    $tokenModel->increment('amount', $tokenReceived);


    Cache::put('jwt_token_' . $telegramUser->id, $token, 100000);

    // ایجاد URL برای وب اپلیکیشن با استفاده از آی‌دی کاربر
    $webAppUrl = "https://3032-167-235-48-101.ngrok-free.app/loading?user_id{$telegramUser->id}&token={$token}";

    // ایجاد دکمه WebApp و تنظیم آن
    $menuButton = new MenuButtonWebApp('Open', new WebAppInfo($webAppUrl));
    $bot->setChatMenuButton(menu_button: $menuButton);

    // ارسال پیام خوش‌آمدگویی به کاربر
    $bot->sendMessage("Hello {$user->first_name}, Welcome To Depintech");
})->description('The start command!');

