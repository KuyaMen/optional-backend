<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'image_id' => $this->whenLoaded('productPhotos', fn () => $this->productPhotos->first()?->id),
            'image' => $this->whenLoaded('productPhotos', fn () => $this->productPhotos->first()?->photo),
            'description' => $this->description,
            'brand' => $this->brand,
            'saleprice' => $this->saleprice,
        ];
    }
}
