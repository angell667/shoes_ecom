<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_effective_price()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'sale_price' => 80.00,
        ]);

        $this->assertEquals(80.00, $product->effective_price);
    }

    public function test_product_discount_percentage()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'sale_price' => 80.00,
        ]);

        $this->assertEquals(20, $product->discount_percentage);
    }

    public function test_product_with_no_sale_price_returns_regular_price()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'sale_price' => null,
        ]);

        $this->assertEquals(100.00, $product->effective_price);
        $this->assertEquals(0, $product->discount_percentage);
    }

    public function test_product_generates_slug_on_creation()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Nike Air Max 270',
        ]);

        $this->assertEquals('nike-air-max-270', $product->slug);
    }

    public function test_product_generates_sku_if_not_provided()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'sku' => null,
        ]);

        $this->assertNotNull($product->sku);
        $this->assertStringStartsWith('SKU-', $product->sku);
    }

    public function test_product_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }
}