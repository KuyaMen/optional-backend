<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_browse_products_or_access_their_cart(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
        $this->getJson('/api/carts')->assertUnauthorized();
    }

    public function test_authenticated_users_can_browse_and_add_products_to_their_cart(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();
        Sanctum::actingAs($user);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('data.0.id', $product->id);

        $this->postJson('/api/carts', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
            ->assertCreated()
            ->assertJsonPath('data.product.id', $product->id)
            ->assertJsonPath('data.quantity', 1);

        $this->postJson('/api/carts', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
            ->assertOk()
            ->assertJsonPath('data.quantity', 3);

        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_users_cannot_read_or_change_another_users_cart_items(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $product = $this->createProduct();
        $cart = Cart::create([
            'user_id' => $otherUser->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        Sanctum::actingAs($owner);

        $this->getJson('/api/carts')
            ->assertOk()
            ->assertJsonCount(0, 'data');
        $this->getJson("/api/carts/{$cart->id}")->assertNotFound();
        $this->patchJson("/api/carts/{$cart->id}", ['quantity' => 2])->assertNotFound();
        $this->deleteJson("/api/carts/{$cart->id}")->assertNotFound();
    }

    public function test_cart_rejects_quantities_above_product_stock(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(stock: 2);
        Sanctum::actingAs($user);

        $this->postJson('/api/carts', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertDatabaseCount('carts', 0);
    }

    public function test_cart_quantity_can_be_updated_and_an_item_removed(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(stock: 5);
        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/carts/{$cart->id}", ['quantity' => 4])
            ->assertOk()
            ->assertJsonPath('data.quantity', 4);

        $this->deleteJson("/api/carts/{$cart->id}")
            ->assertOk();

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_only_administrators_can_create_products(): void
    {
        $category = Category::create(['name' => 'Drinks']);
        $productData = [
            'category_id' => $category->id,
            'name' => 'Coffee',
            'sku' => 'COFFEE-001',
            'price' => 100,
            'stock' => 5,
        ];

        Sanctum::actingAs($this->createUser());
        $this->postJson('/api/products', $productData)->assertForbidden();
        $product = $this->createProduct();
        $this->patchJson("/api/products/{$product->id}", ['name' => 'Updated'])->assertForbidden();

        Sanctum::actingAs($this->createUser(role: 'admin'));
        $this->postJson('/api/products', $productData)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Coffee');
    }

    private function createUser(string $role = 'user'): User
    {
        return User::create([
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function createProduct(int $stock = 10): Product
    {
        $category = Category::create(['name' => fake()->unique()->word()]);

        return Product::create([
            'category_id' => $category->id,
            'name' => fake()->word(),
            'sku' => fake()->unique()->bothify('SKU-####'),
            'price' => 100,
            'stock' => $stock,
        ]);
    }
}
