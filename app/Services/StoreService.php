<?php

namespace App\Services;

use App\Repositories\StoreRepository;
use App\Models\TermAcceptance;
use Illuminate\Http\Request;
use App\Helpers\SanitizerHelper;

class StoreService
{
    protected StoreRepository $repository;

    public function __construct(StoreRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 🔥 Retorna todos os dados necessários para a vitrine
     */
    public function getDataForIndex(array $filters): array
    {
        return [
            'products'         => $this->repository->getFilteredProducts($filters),
            'featuredProducts' => $this->repository->getFeaturedProducts(),
            'onSaleProducts'   => $this->repository->getOnSaleProducts(),
            'brands'           => $this->repository->getAllBrands(),
            'filters'          => $filters,
        ];
    }

    /**
     * Retorna produtos filtrados (para Load More)
     */
    public function getFilteredProducts(array $filters)
    {
        return $this->repository->getFilteredProducts($filters);
    }

    /**
     * 🔐 Registra aceite de termos
     */
    public function recordTermAcceptance(Request $request): TermAcceptance
    {
        return TermAcceptance::create([
            'user_id'      => auth()->id(),
            'ip_address'   => $request->ip(),
            'user_agent'   => $this->sanitizeUserAgent($request->userAgent()),
            'accepted_at'  => now(),
            'term_version' => config('app.term_version', '1.0'),
        ]);
    }

    /**
     * 🛡️ Evita erro de tamanho no banco (PostgreSQL)
     */
    private function sanitizeUserAgent(?string $userAgent): string
    {
        return substr($userAgent ?? 'unknown', 0, 255);
    }
}