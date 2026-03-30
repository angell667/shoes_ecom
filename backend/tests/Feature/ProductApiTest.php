<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_product_by_slug()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product', // This will generate slug 'test-product'
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products/test-product');

        $response->assertStatus(200)
            ->assertJsonPath('slug', 'test-product');
    }

    public function test_can_filter_products_by_category()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        Product::factory()->count(2)->create(['category_id' => $category1->id, 'is_active' => true]);
        Product::factory()->count(3)->create(['category_id' => $category2->id, 'is_active' => true]);

        $response = $this->getJson("/api/products?category={$category1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_products()
    {
        $category = Category::factory()->create();
        
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Nike Air Max',
            'is_active' => true,
        ]);
        
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Adidas Ultraboost',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products?search=Nike');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_get_featured_products()
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->featured()->create(['category_id' => $category->id, 'is_active' => true]);
        Product::factory()->count(3)->create(['category_id' => $category->id, 'is_featured' => false, 'is_active' => true]);

        $response = $this->getJson('/api/products/featured');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_cannot_view_inactive_products()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'inactive-product',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}