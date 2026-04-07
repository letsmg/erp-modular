<?php

namespace App\Http\Controllers;

use App\Services\SearchSuggestionsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchSuggestionsController extends Controller
{
    public function __construct(
        private SearchSuggestionsService $suggestionsService
    ) {}

    /**
     * Obtém sugestões de busca
     */
    public function index(Request $request): JsonResponse
    {
        $term = $request->get('term', '');
        
        if (strlen($term) < 1) {
            return response()->json([]);
        }

        $suggestions = $this->suggestionsService->getSuggestions($term, 8);

        return response()->json([
            'suggestions' => $suggestions,
            'term' => $term
        ]);
    }

    /**
     * Registra busca do usuário
     */
    public function register(Request $request): JsonResponse
    {
        $term = $request->get('term', '');
        
        if (strlen($term) >= 2) {
            $this->suggestionsService->registerSearch($term);
        }

        return response()->json(['registered' => true]);
    }

    /**
     * Obtém palavras mais pesquisadas (trending)
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 20); // Máximo 20
        
        $trending = $this->suggestionsService->getTrendingSearches($limit);

        return response()->json([
            'trending' => $trending,
            'limit' => $limit,
            'total' => count($trending)
        ]);
    }

    /**
     * Estatísticas das buscas
     */
    public function stats(): JsonResponse
    {
        $stats = $this->suggestionsService->getSearchStats();

        return response()->json($stats);
    }
}
