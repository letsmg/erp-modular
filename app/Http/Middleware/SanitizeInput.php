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
            
            // Verifica se há algum campo que possa conter HTML/JS/XML
            $hasHtmlFields = $this->hasHtmlFields($input);
            
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
     * Verifica se a requisição contém campos que podem ter HTML/JS/XML
     */
    private function hasHtmlFields(array $input): bool
    {
        $htmlFields = ['schema_markup', 'google_tag_manager', 'content', 'description', 'body'];
        
        foreach ($htmlFields as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                // Verifica se o campo contém tags HTML
                if ($input[$field] !== strip_tags($input[$field])) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Aplica sanitização com exceções para campos específicos
     */
    private function sanitizeWithExceptions(array $input): array
    {
        $exceptions = ['schema_markup', 'google_tag_manager'];
        
        return SanitizerHelper::sanitize($input, $exceptions);
    }
}
