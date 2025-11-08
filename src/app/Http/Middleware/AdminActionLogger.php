<?php

namespace App\Http\Middleware;

use App\Models\ActionLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminActionLogger
{
    private const LOGGABLE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request)) {
            $this->logRequest($request);
        }

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        return Auth::guard('admin')->check()
            && in_array(strtoupper($request->method()), self::LOGGABLE_METHODS, true);
    }

    private function logRequest(Request $request): void
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (! $admin) {
                return;
            }

            ActionLog::create([
                'admin_id' => $admin->id,
                'content' => $this->buildContent($request),
                'context' => $this->buildContext($request),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function buildContent(Request $request): string
    {
        $routeName = $request->route()?->getName();
        $target = $routeName ?: $request->path();

        return Str::limit(sprintf('%s %s', strtoupper($request->method()), $target), 255, '');
    }

    private function buildContext(Request $request): array
    {
        $route = $request->route();

        return [
            'method' => strtoupper($request->method()),
            'route' => $route?->getName(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'query' => $request->query(),
            'route_parameters' => $route ? $route->parameters() : [],
            'payload' => $this->sanitizeInput($request->except($this->excludedInputKeys())),
            'user_agent' => $request->userAgent(),
        ];
    }

    private function excludedInputKeys(): array
    {
        return ['_token', '_method'];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function sanitizeInput(array $input): array
    {
        return collect($input)
            ->reject(function ($value, $key) {
                return in_array(strtolower((string) $key), [
                    'password',
                    'password_confirmation',
                    'current_password',
                    'token',
                ], true);
            })
            ->map(function ($value) {
                return $this->normalizeValue($value);
            })
            ->all();
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return [
                'original_name' => $value->getClientOriginalName(),
                'size' => $value->getSize(),
                'mime_type' => $value->getClientMimeType(),
            ];
        }

        if (is_array($value)) {
            return Arr::map($value, fn ($nested) => $this->normalizeValue($nested));
        }

        if (is_object($value)) {
            return method_exists($value, '__toString')
                ? (string) $value
                : 'object:' . get_class($value);
        }

        return $value;
    }
}
