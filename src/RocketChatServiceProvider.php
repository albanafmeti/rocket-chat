<?php

namespace Noisim\RocketChat;

use Illuminate\Support\ServiceProvider;

class RocketChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('rocket_chat.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind("rocket-chat", \Noisim\RocketChat\Entities\RocketChat::class);
        $this->app->bind("chat-user", \Noisim\RocketChat\Entities\User::class);
        $this->app->bind("chat-setting", \Noisim\RocketChat\Entities\Setting::class);
        $this->app->bind("chat-channel", \Noisim\RocketChat\Entities\Channel::class);
        $this->app->bind("chat-group", \Noisim\RocketChat\Entities\Group::class);
    }
}
