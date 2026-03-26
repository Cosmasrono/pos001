<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Generate unique SKU with timestamp format: dd/mm/yy hh:mm:milliseconds
     */
    private function generateSku(): string
    {
        $now = now();
        $microseconds = (int)(microtime(true) * 1000) % 1000;
        return $now->format('d/m/y H:i') . ':' . str_pad($microseconds, 3, '0', STR_PAD_LEFT);
    }

    public function run(): void
    {
        // Get or create categories
        $electronics = Category::firstOrCreate(['name' => 'Electronics'], ['description' => 'Electronic devices and accessories']);
        $groceries = Category::firstOrCreate(['name' => 'Groceries'], ['description' => 'Food and household items']);
        $clothing = Category::firstOrCreate(['name' => 'Clothing'], ['description' => 'Apparel and accessories']);
        $stationery = Category::firstOrCreate(['name' => 'Stationery'], ['description' => 'Office and school supplies']);

        $products = [
            // Electronics
            [
                'name' => 'Samsung Galaxy A54',
                'description' => 'Smartphone with 128GB storage',
                'barcode' => '8801643734229',
                'cost_price' => 35000.00,
                'selling_price' => 42000.00,
                'quantity_in_stock' => 15,
                'total_cost' => 525000.00,
                'reorder_level' => 5,
                'category_id' => $electronics->id,
            ],
            [
                'name' => 'HP Laptop 15s',
                'description' => 'Intel i5, 8GB RAM, 256GB SSD',
                'barcode' => '4589632145789',
                'cost_price' => 45000.00,
                'selling_price' => 55000.00,
                'quantity_in_stock' => 8,
                'total_cost' => 360000.00,
                'reorder_level' => 3,
                'category_id' => $electronics->id,
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Logitech M185 Wireless Mouse',
                'barcode' => '1234567890123',
                'cost_price' => 800.00,
                'selling_price' => 1200.00,
                'quantity_in_stock' => 20,
                'total_cost' => 16000.00,
                'reorder_level' => 8,
                'category_id' => $electronics->id,
            ],
            [
                'name' => 'USB Flash Drive 32GB',
                'description' => 'SanDisk USB 3.0',
                'barcode' => '2345678901234',
                'cost_price' => 450.00,
                'selling_price' => 650.00,
                'quantity_in_stock' => 35,
                'total_cost' => 15750.00,
                'reorder_level' => 15,
                'category_id' => $electronics->id,
            ],
            // Groceries
            [
                'name' => 'Rice 2kg',
                'description' => 'Premium Basmati Rice',
                'barcode' => '6789012345678',
                'cost_price' => 250.00,
                'selling_price' => 350.00,
                'quantity_in_stock' => 50,
                'total_cost' => 12500.00,
                'reorder_level' => 20,
                'category_id' => $groceries->id,
            ],
            [
                'name' => 'Cooking Oil 1L',
                'description' => 'Vegetable Cooking Oil',
                'barcode' => '7890123456789',
                'cost_price' => 180.00,
                'selling_price' => 250.00,
                'quantity_in_stock' => 30,
                'total_cost' => 5400.00,
                'reorder_level' => 15,
                'category_id' => $groceries->id,
            ],
            [
                'name' => 'Sugar 1kg',
                'description' => 'White Sugar',
                'barcode' => '8901234567890',
                'cost_price' => 100.00,
                'selling_price' => 150.00,
                'quantity_in_stock' => 40,
                'total_cost' => 4000.00,
                'reorder_level' => 20,
                'category_id' => $groceries->id,
            ],
            [
                'name' => 'Bread 400g',
                'description' => 'Fresh wheat bread',
                'barcode' => '3456789012345',
                'cost_price' => 40.00,
                'selling_price' => 60.00,
                'quantity_in_stock' => 25,
                'total_cost' => 1000.00,
                'reorder_level' => 10,
                'category_id' => $groceries->id,
            ],
            // Clothing
            [
                'name' => 'Men T-Shirt',
                'description' => 'Cotton T-Shirt - Medium',
                'barcode' => '9012345678901',
                'cost_price' => 500.00,
                'selling_price' => 800.00,
                'quantity_in_stock' => 25,
                'total_cost' => 12500.00,
                'reorder_level' => 10,
                'category_id' => $clothing->id,
            ],
            [
                'name' => 'Women Jeans',
                'description' => 'Denim Jeans - Size 32',
                'barcode' => '0123456789012',
                'cost_price' => 1200.00,
                'selling_price' => 1800.00,
                'quantity_in_stock' => 12,
                'total_cost' => 14400.00,
                'reorder_level' => 5,
                'category_id' => $clothing->id,
            ],
            // Stationery
            [
                'name' => 'Notepad A4',
                'description' => '100 pages ruled notepad',
                'sku' => 'STAT-001',
                'barcode' => null,
                'cost_price' => 80.00,
                'selling_price' => 120.00,
                'quantity_in_stock' => 60,
                'total_cost' => 4800.00,
                'reorder_level' => 25,
                'category_id' => $stationery->id,
            ],
            [
                'name' => 'Ballpoint Pen (Box of 12)',
                'description' => 'Blue ink pens',
                'sku' => 'STAT-002',
                'barcode' => '4567890123456',
                'cost_price' => 120.00,
                'selling_price' => 180.00,
                'quantity_in_stock' => 45,
                'total_cost' => 5400.00,
                'reorder_level' => 20,
                'category_id' => $stationery->id,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }

        $this->command->info('Products seeded successfully!');
    }
}
