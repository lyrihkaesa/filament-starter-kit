<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            /** @var string|null $locale */
            $locale = $user->locale ?? config('app.locale');
            if ($locale !== null) {
                Log::info(sprintf('User: %s setting locale to: %s', $user->id, $locale));
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}
