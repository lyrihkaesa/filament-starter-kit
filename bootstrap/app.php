<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->authenticateSessions();
        $middleware->throttleApi();
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $exception->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Unauthenticated.',
                'errors' => [
                    'auth' => ['Authentication is required to access this resource.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'This action is unauthorized.',
                'errors' => [
                    'authorization' => [$exception->getMessage() !== '' ? $exception->getMessage() : 'You are not allowed to perform this action.'],
                ],
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Resource not found.',
                'errors' => [
                    'resource' => ['The requested resource could not be found.'],
                ],
            ], Response::HTTP_NOT_FOUND);
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('app:anonymize-deleted-users')->daily();

        $backupRun = $schedule->command('backup:run')->withoutOverlapping();
        $backupFrequency = (string) config('backup.schedule.frequency', 'daily');
        $backupTime = (string) config('backup.schedule.time', '01:00');

        match ($backupFrequency) {
            'weekly' => $backupRun->weeklyOn((int) config('backup.schedule.weekly_day', 1), $backupTime),
            'monthly' => $backupRun->monthlyOn((int) config('backup.schedule.monthly_day', 1), $backupTime),
            default => $backupRun->dailyAt($backupTime),
        };

        $schedule->command('backup:clean')
            ->dailyAt((string) config('backup.schedule.cleanup_time', '01:30'))
            ->withoutOverlapping();

        $schedule->command('backup:monitor')
            ->dailyAt((string) config('backup.schedule.monitor_time', '06:00'))
            ->withoutOverlapping();
    })
    ->create();
