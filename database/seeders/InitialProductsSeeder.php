<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InitialProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Caramel Macchiato',
                'price' => 199,
                'category' => 'Espresso-Based Beverage',
                'image' => 'https://dinnerthendessert.com/wp-content/uploads/2025/10/caramel-macchiato-Enhanced-.png',
                'description' => 'Freshly steamed milk with vanilla-flavored syrup marked with espresso and topped with a caramel drizzle for an oh-so-sweet finish.',
            ],
            [
                'name' => 'Dalgona Matcha Latte',
                'price' => 135,
                'category' => 'Tea-Based Beverage / Specialty Latte',
                'image' => 'https://www.proportionalplate.com/wp-content/uploads/2020/04/Dalgona-Whipped-Matcha-1-683x1024.jpg',
                'description' => 'A popular layered drink made by topping cold or hot milk and ice with a thick, fluffy, whipped matcha foam.',
            ],
            [
                'name' => 'Classic Buttermilk Waffle',
                'price' => 230,
                'category' => 'Pastries',
                'image' => 'https://www.simplejoy.com/wp-content/uploads/2024/11/waffle-recipe-683x1024.jpg',
                'description' => 'A golden-brown and light, our house-made buttermilk waffle is crisp on the outside, fluffy on the inside, and served with a pat of whipped butter and warm maple syrup.',
            ],
            [
                'name' => 'Vanilla Bean Crème Frappuccino',
                'price' => 148,
                'category' => 'Blended Beverage',
                'image' => 'https://images.squarespace-cdn.com/content/v1/533f584fe4b0f77e1bd2e4d1/1481654887039-X1MJPWGEW1VPAXMOVAYD/image-asset.jpeg?format=1000w',
                'description' => 'A rich, caffeine-free, blended frozen beverage.',
            ],
            [
                'name' => 'Espresso Coffee',
                'price' => 160,
                'category' => 'Pressure-Brewed Coffee',
                'image' => 'https://www.sharmispassions.com/wp-content/uploads/2012/07/espresso-coffee-recipe022.jpg',
                'description' => 'Espresso is a concentrated, full-bodied coffee beverage brewed by forcing hot water through tightly packed, finely ground coffee beans under high pressure.',
            ],
            [
                'name' => 'French fries',
                'price' => 120,
                'category' => 'Snacks',
                'image' => 'https://www.recipetineats.com/tachyon/2022/09/Fries-with-rosemary-salt_1.jpg?resize=900%2C1125&zoom=0.72',
                'description' => 'Golden crispy potato fries seasoned lightly with salt and served hot.',
            ],
            [
                'name' => 'Cinnamon Coffee Cake',
                'price' => 225,
                'category' => 'Pastries',
                'image' => 'https://www.dessertnowdinnerlater.com/wp-content/uploads/2025/01/Cinnamon-Coffee-Cake-1-700x1050.jpg',
                'description' => 'A tender, moist sponge cake layered with warm cinnamon sugar and finished with a buttery crumb topping.',
            ],
            [
                'name' => 'Strawberry Matcha Latte',
                'price' => 150,
                'category' => 'Specialty Lattes / Signature Drinks',
                'image' => 'https://www.veggiessavetheday.com/wp-content/uploads/2022/08/Strawberry-Matcha-Latte-1200x1800-1.jpg',
                'description' => 'A stunning multi-layered favorite. Sweet, house-made strawberry purée meets smooth full-cream milk, crowned with a rich and earthy whisked matcha top layer. Refreshing, creamy, and balanced.',
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::firstOrCreate(
                ['name' => $productData['category']],
                ['status' => 'active'],
            );

            $product = Product::firstOrNew(['name' => $productData['name']]);
            if (! $product->exists) {
                $product->sku = 'INIT-'.Str::upper(Str::random(8));
                $product->stock = 10;
            }

            $product->category()->associate($category);
            $product->price = $productData['price'];
            $product->description = $productData['description'];
            $product->save();

            $photo = $product->productPhotos()->first();
            if ($photo) {
                $photo->update(['photo' => $productData['image']]);
            } else {
                $product->productPhotos()->create(['photo' => $productData['image']]);
            }
        }
    }
}
