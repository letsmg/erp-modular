<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // Renderiza a página de escolha de filtros
    public function index()
    {
        return Inertia::render('Reports/Index', [
            'suppliers' => Supplier::select('id', 'company_name')->orderBy('company_name')->get()
        ]);
    }

    // Gera o PDF
    // No ReportController.php
    public function products(Request $request)
    {
        $type = $request->query('type', 'sintetico');
        
        // O toArray() resolve as imagens e fornecedores imediatamente
        $products = Product::with(['supplier', 'images'])->get()->toArray();

        $data = [
            'products' => $products,
            'type'     => $type,
            'title'    => 'Relatório de Produtos - ' . strtoupper($type),
            'date'     => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('reports.products', $data);
        
        $pdf->setPaper('a4', $type === 'analitico' ? 'landscape' : 'portrait');
        
        // Importante: isRemoteEnabled deve ser true para aceitar base64 e links
        $pdf->getDomPDF()->set_option("isRemoteEnabled", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
        $pdf->getDomPDF()->set_option("chroot", public_path());

        return $pdf->stream('relatorio.pdf');
    }
}