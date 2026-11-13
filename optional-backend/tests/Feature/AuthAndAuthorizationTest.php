<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_users_are_blocked_from_protected_products(): void
    {
        $this->getJson('/api/products')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');

        $this->getJson('/api/products/1')->assertStatus(401);
        $this->postJson('/api/products')->assertStatus(401);
        $this->patchJson('/api/products/1')->assertStatus(401);
        $this->deleteJson('/api/products/1')->assertStatus(401);
    }

    public function test_registration_creates_a_regular_user_without_exposing_password(): void
    {
        $response = $this->postJson('/api/register', [
            'firstname' => 'New',
            'lastname' => 'User',
            'email' => 'new-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonPath('user.firstname', 'New')
            ->assertJsonPath('user.role', 'user')
            ->assertJsonMissingPath('user.password')
            ->assertJsonStructure(['access_token']);

        $this->assertDatabaseHas('users', [
            'email' => 'new-user@example.com',
            'role' => 'user',
        ]);
        $this->assertTrue(Hash::check(
            'password123',
            User::where('email', 'new-user@example.com')->value('password')
        ));

        $this->withToken($response->json('access_token'))
            ->getJson('/api/products')
            ->assertOk();
    }

    public function test_registration_rejects_privilege_fields(): void
    {
        foreach (['role', 'is_admin', 'permission', 'permissions'] as $field) {
            $this->postJson('/api/register', [
                'firstname' => 'New',
                'lastname' => 'User',
                'email' => "{$field}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                $field => 'admin',
            ])->assertUnprocessable();
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_duplicate_emails(): void
    {
        $this->createUser();

        $this->postJson('/api/register', [
            'firstname' => 'Another',
            'lastname' => 'User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_invalid_login_returns_unauthorized(): void
    {
        $this->postJson('/api/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/products')
            ->assertUnauthorized();
    }

    public function test_regular_users_can_view_products_but_cannot_manage_them(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();
        $token = $user->createToken('user-token')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('data.0.id', $product->id);

        $this->withToken($token)
            ->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id);

        $this->withToken($token)
            ->postJson('/api/products', $this->productData())
            ->assertForbidden();

        $this->withToken($token)
            ->patchJson("/api/products/{$product->id}", ['name' => 'Changed'])
            ->assertForbidden();

        $this->withToken($token)
            ->deleteJson("/api/products/{$product->id}")
            ->assertForbidden();
    }

    public function test_admin_users_can_create_update_and_delete_products(): void
    {
        $admin = $this->createUser('admin');
        $token = $admin->createToken('admin-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/products', $this->productData())
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Coffee Beans');

        $product = Product::firstOrFail();

        $this->withToken($token)
            ->patchJson("/api/products/{$product->id}", ['name' => 'Updated Coffee'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Coffee');

        $this->withToken($token)
            ->deleteJson("/api/products/{$product->id}")
            ->assertOk();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_login_returns_a_token_and_safe_user_data(): void
    {
        $this->createUser();

        $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', 'user@example.com')
            ->assertJsonMissingPath('user.password')
            ->assertJsonStructure(['access_token', 'user' => ['id', 'firstname', 'lastname', 'email', 'role']]);
    }

    public function test_admin_seeder_uses_configured_credentials_and_is_idempotent(): void
    {
        config([
            'services.admin.firstname' => 'Seeded',
            'services.admin.lastname' => 'Admin',
            'services.admin.email' => 'seeded-admin@example.com',
            'services.admin.password' => 'admin-password',
        ]);

        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'seeded-admin@example.com',
            'role' => 'admin',
        ]);
        $this->assertTrue(Hash::check(
            'admin-password',
            User::where('email', 'seeded-admin@example.com')->value('password')
        ));
    }

    public function test_database_seeder_uses_the_current_user_schema(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);
    }

    private function createUser(string $role = 'user'): User
    {
        return User::create([
            'firstname' => ucfirst($role),
            'lastname' => 'User',
            'email' => $role === 'admin' ? 'admin@example.com' : 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Test Category',
            'status' => 'active',
        ]);

        return Product::create([
            ...$this->productData(),
            'sku' => 'TEST-0001',
            'category_id' => $category->id,
        ]);
    }

    /**
     * @return array{category_id: int, name: string, price: float, stock: int, description: string}
     */
    private function productData(): array
    {
        $category = Category::firstOrCreate(
            ['name' => 'Test Category'],
            ['status' => 'active'],
        );

        return [
            'category_id' => $category->id,
            'name' => 'Coffee Beans',
            'price' => 149.99,
            'stock' => 12,
            'description' => 'Freshly roasted beans',
        ];
    }
}
