<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Repositories\ProductRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    public function getAllForPos(?string $category = null, ?string $search = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->productRepository->getAllAvailable($category, $search);
    }

    public function getPaginated(?string $category = null, ?string $search = null, int $perPage = 8): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->productRepository->getPaginated($category, $search, $perPage);
    }

    public function create(array $data, ?UploadedFile $image = null): Product
    {
        if ($image) {
            $data['image'] = $image->storePublicly('products', 's3');
        }

        if (isset($data['category'])) {
            $categoryModel = Category::where('slug', $data['category'])->first();
            $data['category_id'] = $categoryModel->id;
            unset($data['category']);
        }

        do {
            $count = $this->productRepository->count() + rand(1, 999);
            $sku = 'MNU-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        } while (\App\Models\Product::where('sku', $sku)->exists());

        $data['sku'] = $sku;

        return $this->productRepository->create($data);
    }

    public function update(int $id, array $data, ?UploadedFile $image = null): bool
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new RuntimeException("Product not found.");
        }

        if ($image) {
            if ($product->image) {
                Storage::disk('s3')->delete($product->image);  // ganti ke s3
            }
            $data['image'] = $image->storePublicly('products', 's3');
        }

        if (isset($data['category'])) {
            $categoryModel = Category::where('slug', $data['category'])->first();
            $data['category_id'] = $categoryModel->id;
            unset($data['category']);
        }

        return $this->productRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $product = $this->productRepository->findById($id);

        if ($product?->image) {
            Storage::disk('s3')->delete($product->image);  // ganti ke s3
        }

        return $this->productRepository->delete($id);
    }
}