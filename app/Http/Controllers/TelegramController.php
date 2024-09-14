<?php

namespace App\Http\Controllers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class TelegramController extends Controller
{
    public function handle()
    {
        $bot = new Nutgram(config('nutgram.token'));

        $bot->setWebhook(route('telegram.webhook'));

        $bot->onCommand('start', function (Nutgram $bot) {
            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('باز کردن وب اپ', web_app: [
                        'url' => route('webapp.index')
                    ])
                );

            $bot->sendMessage(
                text: 'لطفاً روی دکمه زیر کلیک کنید تا وب اپ باز شود:',
                reply_markup: $keyboard
            );
        });

        $bot->run();
    }

    public function webhook()
    {
        $bot = new Nutgram(config('nutgram.token'));
        $bot->setRunningMode(Webhook::class);
        $bot->run();
    }
}
