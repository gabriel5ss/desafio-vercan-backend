<?php

declare(strict_types=1);

namespace Modules\Audit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class SetAuditContext
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Context::add([
            'log_id' => (string) Str::uuid(),
            'root_type' => null,
        ]);

        $route = $request->route();

        if ($route) {
            foreach ($route->parameters() as $parameter) {
                if ($parameter instanceof \Illuminate\Database\Eloquent\Model) {
                    Context::add('root_type', $parameter);
                    break;
                }
            }
        }

        return $next($request);
    }
}
