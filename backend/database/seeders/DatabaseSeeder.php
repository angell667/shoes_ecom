<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user with secure random password
        $adminPassword = \Illuminate\Support\Str::random(16);
        $admin = User::updateOrCreate(
            ['email' => 'admin@shoestore.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make($adminPassword),
                'phone' => '+1 234 567 890',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Create test user with secure random password
        $testPassword = \Illuminate\Support\Str::random(12);
        $testUser = User::updateOrCreate(
            ['email' => 'test@test.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make($testPassword),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );
        
        // Output credentials for development only
        if (app()->environment('local')) {
            $this->command->info('=== DEVELOPMENT CREDENTIALS ===');
            $this->command->info("Admin: admin@shoestore.com / {$adminPassword}");
            $this->command->info("Test User: test@test.com / {$testPassword}");
            $this->command->info('===============================');
        }

        // Create categories
        $categories = [
            [
                'name' => 'Running Shoes',
                'slug' => 'running-shoes',
                'description' => 'High-performance running shoes designed for comfort and speed.',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop'
            ],
            [
                'name' => 'Basketball Shoes',
                'slug' => 'basketball-shoes',
                'description' => 'Professional basketball shoes with excellent ankle support and grip.',
                'image' => 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?w=400&h=400&fit=crop'
            ],
            [
                'name' => 'Casual Shoes',
                'slug' => 'casual-shoes',
                'description' => 'Stylish everyday shoes for casual occasions.',
                'image' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=400&fit=crop'
            ],
            [
                'name' => 'Formal Shoes',
                'slug' => 'formal-shoes',
                'description' => 'Elegant formal shoes for business and special occasions.',
                'image' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=400&h=400&fit=crop'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create products
        $products = [
            // Running Shoes
            [
                'category_id' => 1,
                'name' => 'Nike Air Max 270',
                'slug' => 'nike-air-max-270',
                'description' => 'The Nike Air Max 270 delivers visible cushioning under every step. Updated for modern comfort, it features Nike\'s biggest heel Air unit yet for a super-soft ride that feels as impossible as it looks.',
                'price' => 150.00,
                'sale_price' => 129.99,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=600&fit=crop',
                'brand' => 'Nike',
                'sku' => 'NK-AM270-BLK-001',
                'stock' => 50,
                'is_featured' => true,
            ],
            [
                'category_id' => 1,
                'name' => 'Adidas Ultraboost 22',
                'slug' => 'adidas-ultraboost-22',
                'description' => 'These running shoes deliver incredible energy return. The BOOST midsole cushions every stride while the linear energy push increases your motion control.',
                'price' => 180.00,
                'sale_price' => null,
                'image' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&h=600&fit=crop',
                'brand' => 'Adidas',
                'sku' => 'AD-UB22-WHT-001',
                'stock' => 35,
                'is_featured' => true,
            ],
            [
                'category_id' => 1,
                'name' => 'New Balance 990v5',
                'slug' => 'new-balance-990v5',
                'description' => 'The Made in USA 990v5 is an iconic sneaker that combines iconic style with unmatched comfort. Premium materials and a perfect fit make this a must-have.',
                'price' => 184.99,
                'sale_price' => null,
                'image' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=600&h=600&fit=crop',
                'brand' => 'New Balance',
                'sku' => 'NB-990V5-GRY-001',
                'stock' => 25,
                'is_featured' => false,
            ],
            [
                'category_id' => 1,
                'name' => 'Asics Gel-Kayano 28',
                'slug' => 'asics-gel-kayano-28',
                'description' => 'The GEL-KAYANO 28 running shoe creates a stable stride with a supportive platform. It\'s designed for long-distance runners who need extra stability.',
                'price' => 160.00,
                'sale_price' => 139.99,
                'image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&h=600&fit=crop',
                'brand' => 'Asics',
                'sku' => 'AS-GK28-BLU-001',
                'stock' => 40,
                'is_featured' => true,
            ],
            
            // Basketball Shoes
            [
                'category_id' => 2,
                'name' => 'Nike Air Jordan 1 Retro',
                'slug' => 'nike-air-jordan-1-retro',
                'description' => 'The Air Jordan 1 Retro High OG returns in a premium leather construction. This iconic silhouette continues to set the standard for basketball style.',
                'price' => 170.00,
                'sale_price' => null,
                'image' => 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?w=600&h=600&fit=crop',
                'brand' => 'Nike',
                'sku' => 'NK-AJ1-RED-001',
                'stock' => 30,
                'is_featured' => true,
            ],
            [
                'category_id' => 2,
                'name' => 'Nike LeBron 19',
                'slug' => 'nike-lebron-19',
                'description' => 'The LeBron 19 is built for one of the most powerful players in the game. It features a Max Air unit for impact protection and a secure fit.',
                'price' => 200.00,
                'sale_price' => 179.99,
                'image' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600&h=600&fit=crop',
                'brand' => 'Nike',
                'sku' => 'NK-LBJ19-PUR-001',
                'stock' => 20,
                'is_featured' => false,
            ],
            [
                'category_id' => 2,
                'name' => 'Adidas Harden Vol. 6',
                'slug' => 'adidas-harden-vol-6',
                'description' => 'Designed for James Harden\'s explosive style of play, the Harden Vol. 6 features responsive cushioning and exceptional traction.',
                'price' => 140.00,
                'sale_price' => null,
                'image' => 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600&h=600&fit=crop',
                'brand' => 'Adidas',
                'sku' => 'AD-HV6-BLK-001',
                'stock' => 28,
                'is_featured' => false,
            ],
            
            // Casual Shoes
            [
                'category_id' => 3,
                'name' => 'Nike Air Force 1',
                'slug' => 'nike-air-force-1',
                'description' => 'The radiance lives on in the Nike Air Force 1, the basketball original that puts a fresh spin on what you know best: durably stitched overlays, clean finishes and the perfect amount of flash.',
                'price' => 110.00,
                'sale_price' => null,
                'image' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600&h=600&fit=crop',
                'brand' => 'Nike',
                'sku' => 'NK-AF1-WHT-001',
                'stock' => 100,
                'is_featured' => true,
            ],
            [
                'category_id' => 3,
                'name' => 'Vans Old Skool',
                'slug' => 'vans-old-skool',
                'description' => 'The Old Skool is the first Vans shoe to feature the iconic side stripe. Durable canvas and suede uppers for a classic look.',
                'price' => 65.00,
                'sale_price' => 54.99,
                'image' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=600&h=600&fit=crop',
                'brand' => 'Vans',
                'sku' => 'VN-OS-BLK-001',
                'stock' => 75,
                'is_featured' => false,
            ],
            [
                'category_id' => 3,
                'name' => 'Converse Chuck Taylor All Star',
                'slug' => 'converse-chuck-taylor-all-star',
                'description' => 'The iconic Chuck Taylor All Star continues to be a staple in casual fashion. Features the classic canvas upper and rubber toe cap.',
                'price' => 55.00,
                'sale_price' => null,
                'image' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=600&h=600&fit=crop',
                'brand' => 'Converse',
                'sku' => 'CV-CTA-WHT-001',
                'stock' => 90,
                'is_featured' => true,
            ],
            [
                'category_id' => 3,
                'name' => 'Puma RS-X',
                'slug' => 'puma-rs-x',
                'description' => 'The RS-X takes Running System technology into the future with a bold design and modern color blocking. Perfect for the streets.',
                'price' => 110.00,
                'sale_price' => 89.99,
                'image' => 'https://images.unsplash.com/photo-1605348532760-6753d2c43329?w=600&h=600&fit=crop',
                'brand' => 'Puma',
                'sku' => 'PM-RSX-MUL-001',
                'stock' => 45,
                'is_featured' => false,
            ],
            
            // Formal Shoes
            [
                'category_id' => 4,
                'name' => 'Cole Haan Zerogrand Oxford',
                'slug' => 'cole-haan-zerogrand-oxford',
                'description' => 'The Grand.OS technology in these oxfords provides athletic shoe comfort in a dress shoe design. Features a full-grain leather upper.',
                'price' => 200.00,
                'sale_price' => 169.99,
                'image' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=600&h=600&fit=crop',
                'brand' => 'Cole Haan',
                'sku' => 'CH-ZGO-BRN-001',
                'stock' => 35,
                'is_featured' => true,
            ],
            [
                'category_id' => 4,
                'name' => 'Clarks Tilden Cap',
                'slug' => 'clarks-tilden-cap',
                'description' => 'A classic wingtip oxford with premium leather and cushion soft technology for all-day comfort. Perfect for the office or formal events.',
                'price' => 130.00,
                'sale_price' => null,
                'image' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?w=600&h=600&fit=crop',
                'brand' => 'Clarks',
                'sku' => 'CL-TC-BLK-001',
                'stock' => 40,
                'is_featured' => false,
            ],
            [
                'category_id' => 4,
                'name' => 'Johnston & Murphy Melton',
                'slug' => 'johnston-murphy-melton',
                'description' => 'The Melton cap toe oxford is crafted from premium calfskin leather. Features TRIFIT technology for superior comfort and support.',
                'price' => 195.00,
                'sale_price' => null,
                'image' => 'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?w=600&h=600&fit=crop',
                'brand' => 'Johnston & Murphy',
                'sku' => 'JM-MLT-TAN-001',
                'stock' => 25,
                'is_featured' => false,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create sample coupons
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10,
                'minimum_order' => 50,
                'usage_limit' => 100,
                'expires_at' => now()->addMonths(3),
            ],
            [
                'code' => 'SAVE20',
                'type' => 'percentage',
                'value' => 20,
                'minimum_order' => 100,
                'maximum_discount' => 50,
                'usage_limit' => 50,
                'expires_at' => now()->addMonth(),
            ],
            [
                'code' => 'FLAT15',
                'type' => 'fixed',
                'value' => 15,
                'minimum_order' => 75,
                'usage_limit' => 200,
                'expires_at' => now()->addMonths(2),
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'fixed',
                'value' => 10,
                'minimum_order' => 50,
                'expires_at' => now()->addMonths(6),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@shoestore.com / password123');
        $this->command->info('User: test@test.com / password');
        $this->command->info('Sample Coupons: WELCOME10, SAVE20, FLAT15, FREESHIP');
    }
}
