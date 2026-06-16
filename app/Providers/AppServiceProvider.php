<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tailwind を読み込んでいないため、独自CSSに合わせた素朴な pagination ビューを使う
        Paginator::defaultView('pagination::default');
        Paginator::defaultSimpleView('pagination::simple-default');
    }
}
