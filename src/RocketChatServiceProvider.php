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
            __DIR__ . '/config/rocket_chat.php' => config_path('rocket_chat.php'),
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
        $this->app->bind("rc-user", \Noisim\RocketChat\Entities\User::class);
        $this->app->bind("rc-setting", \Noisim\RocketChat\Entities\Setting::class);
        $this->app->bind("rc-channel", \Noisim\RocketChat\Entities\Channel::class);
        $this->app->bind("rc-group", \Noisim\RocketChat\Entities\Group::class);
        $this->app->bind("rc-im", \Noisim\RocketChat\Entities\Im::class);
        $this->app->bind("rc-chat", \Noisim\RocketChat\Entities\Chat::class);
        $this->app->bind("rc-integration", \Noisim\RocketChat\Entities\Integration::class);
        $this->app->bind("rc-livechat", \Noisim\RocketChat\Entities\Livechat::class);
    }
}
