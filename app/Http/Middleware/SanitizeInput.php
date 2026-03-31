<?php

namespace App\Http\Middleware;

use App\Helpers\SanitizerHelper;
use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Se for uma requisição que altera dados (POST, PUT, PATCH)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $input = $request->all();
            
            // APENAS schema_markup e google_tag_manager podem ter HTML
            $htmlFields = ['schema_markup', 'google_tag_manager'];
            $hasHtmlFields = false;
            
            foreach ($htmlFields as $field) {
                if (isset($input[$field]) && is_string($input[$field])) {
                    $hasHtmlFields = true;
                    break;
                }
            }
            
            if ($hasHtmlFields) {
                // Se há campos HTML, aplicamos sanitização seletiva
                $sanitized = $this->sanitizeWithExceptions($input);
                $request->merge($sanitized);
            } else {
                // Se não há campos HTML, aplicamos sanitização completa
                $sanitized = SanitizerHelper::sanitize($input);
                $request->merge($sanitized);
            }
        }

        return $next($request);
    }

    /**
     * Aplica sanitização com exceções para campos específicos (APENAS schema_markup e google_tag_manager)
     */
    private function sanitizeWithExceptions(array $input): array
    {
        $exceptions = ['schema_markup', 'google_tag_manager'];
        
        return SanitizerHelper::sanitize($input, $exceptions);
    }
}
