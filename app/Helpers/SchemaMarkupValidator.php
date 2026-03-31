<?php

namespace App\Helpers;

class SchemaMarkupValidator
{
    /**
     * Valida e sanitiza schema markup para SEO
     * Apenas permite JSON-LD válido para structured data
     */
    public static function validateAndSanitize(?string $schema): ?string
    {
        if (empty($schema)) {
            return null;
        }

        // Remove espaços em branco no início e fim
        $schema = trim($schema);

        // Verifica se é JSON válido
        json_decode($schema);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Se não for JSON válido, retorna null para segurança
            return null;
        }

        // Verifica se contém apenas JSON-LD permitido
        $allowedPatterns = [
            '/^\s*\{\s*"@context"\s*:\s*"[^"]*"\s*,\s*"@type"\s*:/i', // Início válido de JSON-LD
            '/^\s*\[\s*\{\s*"@context"\s*:/i', // Array JSON-LD
        ];

        $isValidJsonLd = false;
        foreach ($allowedPatterns as $pattern) {
            if (preg_match($pattern, $schema)) {
                $isValidJsonLd = true;
                break;
            }
        }

        if (!$isValidJsonLd) {
            return null;
        }

        // Remove qualquer tag HTML que possa ter sido injetada
        $schema = strip_tags($schema);

        // Verifica novamente se ainda é JSON válido após limpeza
        json_decode($schema);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Remove padrões perigosos que possam ter escapado
        $dangerousPatterns = [
            '/javascript\s*:/i',
            '/on\w+\s*=/i',
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $schema)) {
                return null;
            }
        }

        return $schema;
    }

    /**
     * Escapa HTML para uso seguro em v-html quando absolutamente necessário
     * Use apenas para conteúdo confiado e validado
     */
    public static function escapeForVHtml(?string $content): ?string
    {
        if (empty($content)) {
            return null;
        }

        // Converte entidades HTML para evitar execução
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
