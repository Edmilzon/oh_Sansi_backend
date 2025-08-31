<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test obtener lista de productos.
     */
    public function test_can_get_products_list(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->get('/api/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'description',
                                'price',
                                'stock',
                                'category',
                                'image_url',
                                'is_active',
                                'created_at',
                                'updated_at'
                            ]
                        ]
                    ],
                    'message'
                ]);
    }

    /**
     * Test crear un nuevo producto.
     */
    public function test_can_create_product(): void
    {
        $productData = [
            'name' => 'Producto Test',
            'description' => 'Descripción del producto',
            'price' => 99.99,
            'stock' => 10,
            'category' => 'Electrónicos',
            'image_url' => 'https://example.com/image.jpg'
        ];

        $response = $this->post('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Producto creado exitosamente'
                ]);

        $this->assertDatabaseHas('products', $productData);
    }

    /**
     * Test obtener un producto específico.
     */
    public function test_can_get_specific_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->get("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $product->id,
                        'name' => $product->name
                    ]
                ]);
    }

    /**
     * Test actualizar un producto.
     */
    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();
        $updateData = ['name' => 'Producto Actualizado'];

        $response = $this->put("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente'
                ]);

        $this->assertDatabaseHas('products', $updateData);
    }

    /**
     * Test eliminar un producto.
     */
    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente'
                ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
