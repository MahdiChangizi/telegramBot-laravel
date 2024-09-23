<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Invitation;
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
    $user = $bot->user();
    $telegramUser = TelegramUsers::where('telegram_id', $user->id)->first();
    if (!$telegramUser) {
        // اگر کاربر وجود ندارد، ایجاد کاربر جدید
        $telegramUser = TelegramUsers::create([
            'telegram_id' => $user->id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'invite_code' => $user->username,
            'point' => 0
        ]);

        $telegramId = $telegramUser->telegram_id;
        $tokenAmount = 0;
        if ($telegramId < 1000000000) {
            $tokenAmount = 10000;
        } elseif ($telegramId < 2000000000) {
            $tokenAmount = 5000;
        } elseif ($telegramId < 4000000000) {
            $tokenAmount = 2000;
        } else {
            $tokenAmount = 1000;
        }
        $userToken = Token::firstOrCreate(['user_id' => $telegramUser->id]);
        $userToken->increment('amount', $tokenAmount);


        $token = Auth::login($telegramUser);
        $webAppUrl = "https://arioad.com/loading?user_id={$telegramUser->id}&token={$token}";
        $menuButton = new MenuButtonWebApp('Open', new WebAppInfo($webAppUrl));
        $bot->setChatMenuButton(menu_button: $menuButton);
    } else {
        $token = Auth::login($telegramUser);
        $webAppUrl = "https://arioad.com/?user_id={$telegramUser->id}&token={$token}";
        $menuButton = new MenuButtonWebApp('Open', new WebAppInfo($webAppUrl));
        $bot->setChatMenuButton(menu_button: $menuButton);
    }

    $bot->sendMessage("Hello {$user->first_name}, Welcome To Depintech");
})->description('The start command!');


// invite friends
$bot->onCommand('start {referral_code}', function (Nutgram $bot, $referral_code = null) {
    $user = $bot->user();
    $telegramUser = TelegramUsers::where('telegram_id', $user->id)->first();

    if (!$telegramUser) {
        $telegramUser = TelegramUsers::create([
            'telegram_id' => $user->id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'invite_code' => $user->username,
            'point' => 0
        ]);

        $telegramId = $telegramUser->telegram_id;
        $tokenAmount = 0;
        if ($telegramId < 1000000000) {
            $tokenAmount = 10000;
        } elseif ($telegramId < 2000000000) {
            $tokenAmount = 5000;
        } elseif ($telegramId < 4000000000) {
            $tokenAmount = 2000;
        } else {
            $tokenAmount = 1000;
        }
        $userToken = Token::firstOrCreate(['user_id' => $telegramUser->id]);
        $userToken->increment('amount', $tokenAmount);


        if ($referral_code) {
            $referrer = TelegramUsers::where('invite_code', $referral_code)->first();
            if ($referrer) {
                Invitation::create([
                    'user_id' => $referrer->id,
                    'invited_user_id' => $telegramUser->id
                ]);

                // اعطای توکن به کاربر دعوت کننده
                $referrerToken = Token::firstOrCreate(['user_id' => $referrer->id]);
                $referrerToken->increment('amount', 1000);
                $bot->sendMessage("You have been successfully invited by @{$referrer->username}!");
            }
        }

        // ایجاد توکن ورود برای کاربر
        $token = Auth::login($telegramUser);
        $webAppUrl = "https://arioad.com/loading?user_id={$telegramUser->id}&token={$token}";
    } else {
        return;
    }

    // تنظیم دکمه منوی وب اپ
    $menuButton = new MenuButtonWebApp('Open', new WebAppInfo($webAppUrl));
    $bot->setChatMenuButton(menu_button: $menuButton);

    $bot->sendMessage("Hello {$user->first_name}, Welcome To Depintech");
});
