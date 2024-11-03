<?php

namespace Modules\Shop\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Shop\Entities\Attribute;
use Modules\Shop\Entities\Category;
use Modules\Shop\Entities\Tag;
use Modules\Shop\Entities\Product;
use Modules\Shop\Entities\ProductAttribute; // Pastikan untuk mengimpor ini
use Modules\Shop\Entities\ProductInventory; // Pastikan untuk mengimpor ini juga

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Ambil user pertama sebagai pemilik produk
        $user = User::first();

        // Seed default attributes
        Attribute::setDefaultAttributes();
        $this->command->info('Default attributes seeded.');

        // Pastikan 'weight' attribute ada
        $attributeWeight = Attribute::where('code', Attribute::ATTR_WEIGHT)->first();
        if (!$attributeWeight) {
            $this->command->error('Attribute "weight" not found.');
            return;
        }

        // Seed categories
        Category::factory()->count(10)->create();
        $this->command->info('Categories seeded.');
        $randomCategoryIDs = Category::all()->random(2)->pluck('id'); // Pilih 2 kategori acak

        // Seed tags
        Tag::factory()->count(10)->create();
        $this->command->info('Tags seeded.');
        $randomTagIDs = Tag::all()->random(2)->pluck('id'); // Pilih 2 tag acak
        
        // Seed products
        for ($i = 1; $i <= 10; $i++) {
            $manageStock = (bool)random_int(0, 1);

            // Buat produk
            $product = Product::factory()->create([
                'user_id' => $user->id,
                'manage_stock' => $manageStock,
            ]);

            // Sinkronisasi dengan kategori dan tag acak
            $product->categories()->sync($randomCategoryIDs);
            $product->tags()->sync($randomTagIDs);

            // Buat ProductAttribute untuk weight
            ProductAttribute::create([
                'product_id' => $product->id,
                'attribute_id' => $attributeWeight->id,
                'integer_value' => random_int(200, 2000), // gram
            ]);

            // Jika produk menggunakan manajemen stok, buat inventory
            if ($manageStock) {
                ProductInventory::create([
                    'product_id' => $product->id,
                    'qty' => random_int(3, 20),
                    'low_stock_threshold' => random_int(1,3),
                ]);
            }
        }

        $this->command->info('10 sample products seeded.');
    }
}
