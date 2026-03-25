<?php

namespace App\Repositories; // Mude para namespace App\Services;

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Support\Facades\Log;

class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function storeProduct(array $data, $request)
    {
        return DB::transaction(function () use ($data, $request) {
            $data['is_active'] = false;
            $data['slug'] = $this->generateSlug($data['description']);
            
            $product = $this->repository->create($data);

            if ($request->hasFile('images')) {
                $this->handleImageUpload($product, $request->file('images'));
            }

            $this->syncSeo($product, $request->all());

            return $product;
        });
    }

    public function updateProduct(Product $product, array $data, $request)
    {
        return DB::transaction(function () use ($product, $data, $request) {
            // 1. Gestão de Imagens Antigas
            $existingIds = collect($request->existing_images)->pluck('id')->toArray();
            $imagesToDelete = $product->images()->whereNotIn('id', $existingIds)->get();

            foreach ($imagesToDelete as $oldImg) {
                Storage::disk('public')->delete('products/' . $oldImg->path);
                $oldImg->delete();
            }

            // 2. Novas Imagens
            if ($request->hasFile('new_images')) {
                $this->handleImageUpload($product, $request->file('new_images'));
            }

            // 3. Atualizar Slug se necessário
            if ($product->description !== $data['description']) {
                $data['slug'] = $this->generateSlug($data['description']);
            }

            $product = $this->repository->update($product, $data);
            $this->syncSeo($product, $request->all());

            return $product;
        });
    }

    public function deleteProduct(Product $product)
    {
        return DB::transaction(function () use ($product) {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete('products/' . $img->path);
                $img->delete();
            }

            if ($product->seo) {
                $product->seo->delete();
            }

            return $this->repository->delete($product);
        });
    }

    private function generateSlug($description)
    {
        return Str::slug($description) . '-' . Str::lower(Str::random(5));
    }

    private function handleImageUpload($product, array $files)
    {
        foreach ($files as $file) {
            if (!$this->isImageSafe($file)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'images' => 'Uma das imagens contém conteúdo impróprio (Google Vision).'
                ]);
            }
            $path = $file->store('products', 'public');
            $product->images()->create(['path' => basename($path)]);
        }
    }

    private function syncSeo($product, array $input)
    {
        $seoFields = [
            'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 
            'h1', 'text1', 'h2', 'text2', 'schema_markup', 'google_tag_manager', 'ads'
        ];

        $data = collect($input)->only($seoFields)->toArray();
        $hasValue = collect($data)->some(fn($v) => !empty($v));

        if ($hasValue || $product->seo()->exists()) {
            $product->seo()->updateOrCreate(
                ['seoable_id' => $product->id, 'seoable_type' => get_class($product)],
                array_merge($data, ['slug' => $product->slug])
            );
        }
    }

    private function isImageSafe($image)
    {
        $credentialPath = base_path('google-credentials.json');
        if (!class_exists('Google\Cloud\Vision\V1\ImageAnnotatorClient') || !file_exists($credentialPath)) {
            return true; 
        }

        try {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialPath);
            $imageAnnotator = new ImageAnnotatorClient();
            $content = file_get_contents($image->getRealPath());
            $response = $imageAnnotator->safeSearchDetection($content);
            $safe = $response->getSafeSearchAnnotation();
            $imageAnnotator->close();

            $unsafeLevels = [3, 4, 5]; // Likely, Very Likely
            return !(in_array($safe->getAdult(), $unsafeLevels) || in_array($safe->getViolence(), $unsafeLevels));
        } catch (\Exception $e) {
            Log::error("Erro API Vision: " . $e->getMessage());
            return true;
        }
    }
}