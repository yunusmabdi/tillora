<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TilloraDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
             * ============================================================
             * 1. CLEAN PREVIOUS TILLORA DEMO DATA
             * ============================================================
             *
             * Only records created by this seeder are removed.
             *
             * Demo identifiers:
             *   Categories: DEMO-CAT-*
             *   Suppliers:  DEMO-SUP-*
             *   Products:   DEMO-*
             *   Purchases:  DEMO-PO-*
             */

            $demoPurchaseIds = Purchase::where('purchase_number', 'like', 'DEMO-PO-%')
                ->pluck('id');

            if ($demoPurchaseIds->isNotEmpty()) {
                StockMovement::where('reference_type', 'Purchase')
                    ->whereIn('reference_id', $demoPurchaseIds)
                    ->delete();

                PurchaseItem::whereIn('purchase_id', $demoPurchaseIds)->delete();
                Purchase::whereIn('id', $demoPurchaseIds)->delete();
            }

            Product::where('sku', 'like', 'DEMO-%')->delete();

            Supplier::where('supplier_code', 'like', 'DEMO-SUP-%')->delete();

            Category::where('code', 'like', 'DEMO-CAT-%')->delete();


            /*
             * ============================================================
             * 2. CATEGORIES
             * ============================================================
             */

            $categories = [];

            $categoryData = [
                [
                    'code' => 'DEMO-CAT-001',
                    'name' => 'Beverages',
                    'description' => 'Soft drinks, juices and refreshment beverages.',
                ],
                [
                    'code' => 'DEMO-CAT-002',
                    'name' => 'Water & Energy Drinks',
                    'description' => 'Bottled water, sports and energy drinks.',
                ],
                [
                    'code' => 'DEMO-CAT-003',
                    'name' => 'Snacks & Confectionery',
                    'description' => 'Chips, biscuits, chocolates and confectionery.',
                ],
                [
                    'code' => 'DEMO-CAT-004',
                    'name' => 'Breakfast & Cereals',
                    'description' => 'Breakfast cereals, spreads, oats and honey.',
                ],
                [
                    'code' => 'DEMO-CAT-005',
                    'name' => 'Cooking & Pantry',
                    'description' => 'Rice, flour, sugar, cooking oil, pasta and pantry essentials.',
                ],
                [
                    'code' => 'DEMO-CAT-006',
                    'name' => 'Dairy & Chilled',
                    'description' => 'Milk, yoghurt, butter and chilled products.',
                ],
                [
                    'code' => 'DEMO-CAT-007',
                    'name' => 'Personal Care',
                    'description' => 'Personal hygiene and grooming products.',
                ],
                [
                    'code' => 'DEMO-CAT-008',
                    'name' => 'Home Care',
                    'description' => 'Laundry, cleaning and household care products.',
                ],
                [
                    'code' => 'DEMO-CAT-009',
                    'name' => 'Baby Care',
                    'description' => 'Baby hygiene and childcare products.',
                ],
                [
                    'code' => 'DEMO-CAT-010',
                    'name' => 'Electronics & Accessories',
                    'description' => 'Chargers, cables, power banks and small electronics.',
                ],
                [
                    'code' => 'DEMO-CAT-011',
                    'name' => 'Stationery & Office',
                    'description' => 'Office, school and stationery products.',
                ],
                [
                    'code' => 'DEMO-CAT-012',
                    'name' => 'Kitchen & Household',
                    'description' => 'Kitchen accessories and household essentials.',
                ],
                [
                    'code' => 'DEMO-CAT-013',
                    'name' => 'Health & Wellness',
                    'description' => 'General wellness and personal health products.',
                ],
                [
                    'code' => 'DEMO-CAT-014',
                    'name' => 'Premium Foods',
                    'description' => 'Premium imported and specialty food products.',
                ],
            ];

            foreach ($categoryData as $data) {

                $category = Category::create([
                    'code' => $data['code'],
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'is_active' => true,
                ]);

                $categories[$data['name']] = $category;
            }


            /*
             * ============================================================
             * 3. SUPPLIERS
             * ============================================================
             */

            $supplierData = [
                [
                    'supplier_code' => 'DEMO-SUP-001',
                    'company_name' => 'East Africa Beverages Ltd',
                    'contact_person' => 'Daniel Mwangi',
                    'email' => 'sales@eabeverages.demo',
                    'phone' => '0712345601',
                    'alternative_phone' => '0207001001',
                    'address' => 'Industrial Area',
                    'city' => 'Nairobi',
                    'country' => 'Kenya',
                    'tax_number' => 'P051234567A',
                ],
                [
                    'supplier_code' => 'DEMO-SUP-002',
                    'company_name' => 'Nairobi Wholesale Distributors',
                    'contact_person' => 'Peter Kamau',
                    'email' => 'orders@nairobiwholesale.demo',
                    'phone' => '0723456702',
                    'alternative_phone' => '0207001002',
                    'address' => 'Muthurwa Wholesale Market',
                    'city' => 'Nairobi',
                    'country' => 'Kenya',
                    'tax_number' => 'P051234568B',
                ],
                [
                    'supplier_code' => 'DEMO-SUP-003',
                    'company_name' => 'Fresh Foods Kenya Ltd',
                    'contact_person' => 'Mary Wanjiku',
                    'email' => 'procurement@freshfoods.demo',
                    'phone' => '0734567803',
                    'alternative_phone' => '0207001003',
                    'address' => 'Riverside Drive',
                    'city' => 'Nairobi',
                    'country' => 'Kenya',
                    'tax_number' => 'P051234569C',
                ],
                [
                    'supplier_code' => 'DEMO-SUP-004',
                    'company_name' => 'Metro Consumer Goods',
                    'contact_person' => 'Brian Otieno',
                    'email' => 'sales@metroconsumer.demo',
                    'phone' => '0745678904',
                    'alternative_phone' => '0207001004',
                    'address' => 'Baba Dogo',
                    'city' => 'Nairobi',
                    'country' => 'Kenya',
                    'tax_number' => 'P051234570D',
                ],
                [
                    'supplier_code' => 'DEMO-SUP-005',
                    'company_name' => 'TechLink Distributors Kenya',
                    'contact_person' => 'Ahmed Hassan',
                    'email' => 'orders@techlink.demo',
                    'phone' => '0756789005',
                    'alternative_phone' => '0207001005',
                    'address' => 'Luthuli Avenue',
                    'city' => 'Nairobi',
                    'country' => 'Kenya',
                    'tax_number' => 'P051234571E',
                ],
                [
                    'supplier_code' => 'DEMO-SUP-006',
                    'company_name' => 'Household Essentials Kenya',
                    'contact_person' => 'Grace Njeri',
                    'email' => 'orders@householdessentials.demo',
                    'phone' => '0767890106',
                    'alternative_phone' => '0207001006',
                    'address' => 'Kirinyaga Road',
                    'city' => 'Nairobi',
                    'country' => 'Kenya',
                    'tax_number' => 'P051234572F',
                ],
                [
                    'supplier_code' => 'DEMO-SUP-007',
                    'company_name' => 'Urban Lifestyle Distributors',
                    'contact_person' => 'Kevin Kariuki',
                    'email' => 'sales@urbanlifestyle.demo',
                    'phone' => '0778901207',
                    'alternative_phone' => '0207001007',
                    'address' => 'Ngara',
                    'city' => 'Nairobi',
                    'country' => 'Kenya',
                    'tax_number' => 'P051234573G',
                ],
                [
                    'supplier_code' => 'DEMO-SUP-008',
                    'company_name' => 'Premium Imports Kenya',
                    'contact_person' => 'Samuel Maina',
                    'email' => 'imports@premiumfoods.demo',
                    'phone' => '0789012308',
                    'alternative_phone' => '0207001008',
                    'address' => 'Westlands',
                    'city' => 'Nairobi',
                    'country' => 'Kenya',
                    'tax_number' => 'P051234574H',
                ],
            ];

            $suppliers = [];

            foreach ($supplierData as $data) {

                $supplier = Supplier::create([
                    ...$data,
                    'is_active' => true,
                ]);

                $suppliers[$data['supplier_code']] = $supplier;
            }


            /*
             * ============================================================
             * 4. PRODUCTS
             * ============================================================
             *
             * Stock starts at zero.
             *
             * Received purchases below will populate actual stock.
             */

            $products = [];

            $productData = [

                // ========================================================
                // BEVERAGES
                // ========================================================

                [
                    'category' => 'Beverages',
                    'name' => 'Coca-Cola 500ml',
                    'cost' => 55,
                    'price' => 75,
                    'min' => 30,
                    'unit' => 'Bottle',
                    'image' => 'products/coca-cola-500ml.jpg',
                ],
                [
                    'category' => 'Beverages',
                    'name' => 'Coca-Cola 1.25L',
                    'cost' => 95,
                    'price' => 130,
                    'min' => 20,
                    'unit' => 'Bottle',
                    'image' => 'products/coca-cola-1-25l.jpg',
                ],
                [
                    'category' => 'Beverages',
                    'name' => 'Sprite 500ml',
                    'cost' => 55,
                    'price' => 75,
                    'min' => 25,
                    'unit' => 'Bottle',
                    'image' => 'products/sprite-500ml.jpg',
                ],
                [
                    'category' => 'Beverages',
                    'name' => 'Fanta Orange 500ml',
                    'cost' => 55,
                    'price' => 75,
                    'min' => 25,
                    'unit' => 'Bottle',
                    'image' => 'products/fanta-orange-500ml.jpg',
                ],
                [
                    'category' => 'Beverages',
                    'name' => 'Minute Maid Mango 400ml',
                    'cost' => 65,
                    'price' => 95,
                    'min' => 20,
                    'unit' => 'Bottle',
                    'image' => 'products/minute-maid-mango.jpg',
                ],
                [
                    'category' => 'Beverages',
                    'name' => 'Schweppes Tonic 500ml',
                    'cost' => 70,
                    'price' => 100,
                    'min' => 15,
                    'unit' => 'Bottle',
                    'image' => 'products/schweppes-tonic.jpg',
                ],

                // ========================================================
                // WATER & ENERGY
                // ========================================================

                [
                    'category' => 'Water & Energy Drinks',
                    'name' => 'Keringet Water 1L',
                    'cost' => 55,
                    'price' => 80,
                    'min' => 30,
                    'unit' => 'Bottle',
                    'image' => 'products/keringet-1l.jpg',
                ],
                [
                    'category' => 'Water & Energy Drinks',
                    'name' => 'Aqua Mist Water 500ml',
                    'cost' => 28,
                    'price' => 50,
                    'min' => 40,
                    'unit' => 'Bottle',
                    'image' => 'products/aqua-mist-500ml.jpg',
                ],
                [
                    'category' => 'Water & Energy Drinks',
                    'name' => 'Red Bull 250ml',
                    'cost' => 105,
                    'price' => 150,
                    'min' => 20,
                    'unit' => 'Can',
                    'image' => 'products/red-bull-250ml.jpg',
                ],
                [
                    'category' => 'Water & Energy Drinks',
                    'name' => 'Monster Energy 500ml',
                    'cost' => 130,
                    'price' => 180,
                    'min' => 15,
                    'unit' => 'Can',
                    'image' => 'products/monster-energy-500ml.jpg',
                ],
                [
                    'category' => 'Water & Energy Drinks',
                    'name' => 'Predator Energy 500ml',
                    'cost' => 75,
                    'price' => 110,
                    'min' => 20,
                    'unit' => 'Can',
                    'image' => 'products/predator-energy-500ml.jpg',
                ],

                // ========================================================
                // SNACKS
                // ========================================================

                [
                    'category' => 'Snacks & Confectionery',
                    'name' => 'Pringles Original 165g',
                    'cost' => 280,
                    'price' => 380,
                    'min' => 10,
                    'unit' => 'Can',
                    'image' => 'products/pringles-original.jpg',
                ],
                [
                    'category' => 'Snacks & Confectionery',
                    'name' => 'Doritos Cheese 100g',
                    'cost' => 120,
                    'price' => 170,
                    'min' => 15,
                    'unit' => 'Pack',
                    'image' => 'products/doritos-cheese.jpg',
                ],
                [
                    'category' => 'Snacks & Confectionery',
                    'name' => 'Oreo Original 133g',
                    'cost' => 85,
                    'price' => 120,
                    'min' => 15,
                    'unit' => 'Pack',
                    'image' => 'products/oreo-original.jpg',
                ],
                [
                    'category' => 'Snacks & Confectionery',
                    'name' => 'KitKat 4 Finger',
                    'cost' => 55,
                    'price' => 80,
                    'min' => 20,
                    'unit' => 'Bar',
                    'image' => 'products/kitkat-4-finger.jpg',
                ],
                [
                    'category' => 'Snacks & Confectionery',
                    'name' => 'Snickers 50g',
                    'cost' => 55,
                    'price' => 80,
                    'min' => 20,
                    'unit' => 'Bar',
                    'image' => 'products/snickers-50g.jpg',
                ],
                [
                    'category' => 'Snacks & Confectionery',
                    'name' => 'Ferrero Rocher 200g',
                    'cost' => 850,
                    'price' => 1200,
                    'min' => 5,
                    'unit' => 'Box',
                    'image' => 'products/ferrero-rocher.jpg',
                ],

                // ========================================================
                // BREAKFAST
                // ========================================================

                [
                    'category' => 'Breakfast & Cereals',
                    'name' => 'Weetabix 430g',
                    'cost' => 480,
                    'price' => 650,
                    'min' => 10,
                    'unit' => 'Box',
                    'image' => 'products/weetabix.jpg',
                ],
                [
                    'category' => 'Breakfast & Cereals',
                    'name' => 'Kelloggs Corn Flakes 500g',
                    'cost' => 390,
                    'price' => 520,
                    'min' => 10,
                    'unit' => 'Box',
                    'image' => 'products/kelloggs-corn-flakes.jpg',
                ],
                [
                    'category' => 'Breakfast & Cereals',
                    'name' => 'Nutella 350g',
                    'cost' => 520,
                    'price' => 700,
                    'min' => 8,
                    'unit' => 'Jar',
                    'image' => 'products/nutella-350g.jpg',
                ],
                [
                    'category' => 'Breakfast & Cereals',
                    'name' => 'Peanut Butter 400g',
                    'cost' => 280,
                    'price' => 390,
                    'min' => 10,
                    'unit' => 'Jar',
                    'image' => 'products/peanut-butter.jpg',
                ],
                [
                    'category' => 'Breakfast & Cereals',
                    'name' => 'Pure Honey 500g',
                    'cost' => 420,
                    'price' => 600,
                    'min' => 8,
                    'unit' => 'Jar',
                    'image' => 'products/pure-honey.jpg',
                ],

                // ========================================================
                // COOKING & PANTRY
                // ========================================================

                [
                    'category' => 'Cooking & Pantry',
                    'name' => 'Pishori Rice 2kg',
                    'cost' => 360,
                    'price' => 470,
                    'min' => 15,
                    'unit' => 'Bag',
                    'image' => 'products/pishori-rice-2kg.jpg',
                ],
                [
                    'category' => 'Cooking & Pantry',
                    'name' => 'Basmati Rice 2kg',
                    'cost' => 390,
                    'price' => 520,
                    'min' => 15,
                    'unit' => 'Bag',
                    'image' => 'products/basmati-rice-2kg.jpg',
                ],
                [
                    'category' => 'Cooking & Pantry',
                    'name' => 'Mumias Sugar 2kg',
                    'cost' => 300,
                    'price' => 370,
                    'min' => 20,
                    'unit' => 'Bag',
                    'image' => 'products/mumias-sugar-2kg.jpg',
                ],
                [
                    'category' => 'Cooking & Pantry',
                    'name' => 'Fresh Fri Cooking Oil 2L',
                    'cost' => 430,
                    'price' => 540,
                    'min' => 15,
                    'unit' => 'Bottle',
                    'image' => 'products/fresh-fri-2l.jpg',
                ],
                [
                    'category' => 'Cooking & Pantry',
                    'name' => 'Spaghetti 500g',
                    'cost' => 95,
                    'price' => 135,
                    'min' => 20,
                    'unit' => 'Pack',
                    'image' => 'products/spaghetti-500g.jpg',
                ],
                [
                    'category' => 'Cooking & Pantry',
                    'name' => 'Tomato Sauce 400g',
                    'cost' => 120,
                    'price' => 170,
                    'min' => 15,
                    'unit' => 'Bottle',
                    'image' => 'products/tomato-sauce.jpg',
                ],

                // ========================================================
                // DAIRY
                // ========================================================

                [
                    'category' => 'Dairy & Chilled',
                    'name' => 'Brookside Fresh Milk 1L',
                    'cost' => 115,
                    'price' => 150,
                    'min' => 20,
                    'unit' => 'Packet',
                    'image' => 'products/brookside-milk-1l.jpg',
                ],
                [
                    'category' => 'Dairy & Chilled',
                    'name' => 'Brookside Yoghurt Strawberry 500ml',
                    'cost' => 105,
                    'price' => 150,
                    'min' => 15,
                    'unit' => 'Bottle',
                    'image' => 'products/brookside-yoghurt.jpg',
                ],
                [
                    'category' => 'Dairy & Chilled',
                    'name' => 'Blue Band Original 500g',
                    'cost' => 210,
                    'price' => 280,
                    'min' => 12,
                    'unit' => 'Tub',
                    'image' => 'products/blue-band-500g.jpg',
                ],

                // ========================================================
                // PERSONAL CARE
                // ========================================================

                [
                    'category' => 'Personal Care',
                    'name' => 'Nivea Men Deep 150ml',
                    'cost' => 390,
                    'price' => 550,
                    'min' => 8,
                    'unit' => 'Can',
                    'image' => 'products/nivea-men-deep.jpg',
                ],
                [
                    'category' => 'Personal Care',
                    'name' => 'Dove Beauty Bar 100g',
                    'cost' => 100,
                    'price' => 150,
                    'min' => 15,
                    'unit' => 'Bar',
                    'image' => 'products/dove-beauty-bar.jpg',
                ],
                [
                    'category' => 'Personal Care',
                    'name' => 'Colgate Total 100ml',
                    'cost' => 210,
                    'price' => 290,
                    'min' => 15,
                    'unit' => 'Tube',
                    'image' => 'products/colgate-total.jpg',
                ],
                [
                    'category' => 'Personal Care',
                    'name' => 'Vaseline Cocoa Glow 400ml',
                    'cost' => 390,
                    'price' => 520,
                    'min' => 10,
                    'unit' => 'Bottle',
                    'image' => 'products/vaseline-cocoa-glow.jpg',
                ],
                [
                    'category' => 'Personal Care',
                    'name' => 'Gillette Blue II',
                    'cost' => 180,
                    'price' => 260,
                    'min' => 10,
                    'unit' => 'Pack',
                    'image' => 'products/gillette-blue-ii.jpg',
                ],

                // ========================================================
                // HOME CARE
                // ========================================================

                [
                    'category' => 'Home Care',
                    'name' => 'Ariel Washing Powder 1kg',
                    'cost' => 320,
                    'price' => 430,
                    'min' => 15,
                    'unit' => 'Pack',
                    'image' => 'products/ariel-1kg.jpg',
                ],
                [
                    'category' => 'Home Care',
                    'name' => 'Omo Washing Powder 1kg',
                    'cost' => 300,
                    'price' => 400,
                    'min' => 15,
                    'unit' => 'Pack',
                    'image' => 'products/omo-1kg.jpg',
                ],
                [
                    'category' => 'Home Care',
                    'name' => 'Harpic Toilet Cleaner 500ml',
                    'cost' => 180,
                    'price' => 250,
                    'min' => 12,
                    'unit' => 'Bottle',
                    'image' => 'products/harpic-500ml.jpg',
                ],
                [
                    'category' => 'Home Care',
                    'name' => 'Jik Bleach 1L',
                    'cost' => 140,
                    'price' => 200,
                    'min' => 12,
                    'unit' => 'Bottle',
                    'image' => 'products/jik-1l.jpg',
                ],
                [
                    'category' => 'Home Care',
                    'name' => 'Sunlight Dishwashing Liquid 750ml',
                    'cost' => 170,
                    'price' => 240,
                    'min' => 12,
                    'unit' => 'Bottle',
                    'image' => 'products/sunlight-dishwashing.jpg',
                ],

                // ========================================================
                // BABY CARE
                // ========================================================

                [
                    'category' => 'Baby Care',
                    'name' => 'Huggies Dry Comfort Medium',
                    'cost' => 850,
                    'price' => 1100,
                    'min' => 8,
                    'unit' => 'Pack',
                    'image' => 'products/huggies-medium.jpg',
                ],
                [
                    'category' => 'Baby Care',
                    'name' => 'Pampers Baby Dry Medium',
                    'cost' => 900,
                    'price' => 1180,
                    'min' => 8,
                    'unit' => 'Pack',
                    'image' => 'products/pampers-medium.jpg',
                ],

                // ========================================================
                // ELECTRONICS
                // ========================================================

                [
                    'category' => 'Electronics & Accessories',
                    'name' => '20W USB-C Fast Charger',
                    'cost' => 650,
                    'price' => 950,
                    'min' => 8,
                    'unit' => 'Piece',
                    'image' => 'products/usb-c-fast-charger.jpg',
                ],
                [
                    'category' => 'Electronics & Accessories',
                    'name' => 'USB-C Fast Charging Cable',
                    'cost' => 250,
                    'price' => 450,
                    'min' => 15,
                    'unit' => 'Piece',
                    'image' => 'products/usb-c-cable.jpg',
                ],
                [
                    'category' => 'Electronics & Accessories',
                    'name' => 'Power Bank 10000mAh',
                    'cost' => 1050,
                    'price' => 1500,
                    'min' => 8,
                    'unit' => 'Piece',
                    'image' => 'products/power-bank-10000mah.jpg',
                ],
                [
                    'category' => 'Electronics & Accessories',
                    'name' => 'Bluetooth Earbuds',
                    'cost' => 1200,
                    'price' => 1850,
                    'min' => 6,
                    'unit' => 'Piece',
                    'image' => 'products/bluetooth-earbuds.jpg',
                ],
                [
                    'category' => 'Electronics & Accessories',
                    'name' => 'Wireless Mouse',
                    'cost' => 650,
                    'price' => 950,
                    'min' => 8,
                    'unit' => 'Piece',
                    'image' => 'products/wireless-mouse.jpg',
                ],

                // ========================================================
                // STATIONERY
                // ========================================================

                [
                    'category' => 'Stationery & Office',
                    'name' => 'A4 Printing Paper 500 Sheets',
                    'cost' => 650,
                    'price' => 850,
                    'min' => 10,
                    'unit' => 'Ream',
                    'image' => 'products/a4-paper.jpg',
                ],
                [
                    'category' => 'Stationery & Office',
                    'name' => 'Premium Ballpoint Pens Pack',
                    'cost' => 180,
                    'price' => 280,
                    'min' => 10,
                    'unit' => 'Pack',
                    'image' => 'products/ballpoint-pens.jpg',
                ],

                // ========================================================
                // KITCHEN
                // ========================================================

                [
                    'category' => 'Kitchen & Household',
                    'name' => 'Stainless Steel Flask 1L',
                    'cost' => 950,
                    'price' => 1400,
                    'min' => 5,
                    'unit' => 'Piece',
                    'image' => 'products/stainless-flask.jpg',
                ],
                [
                    'category' => 'Kitchen & Household',
                    'name' => 'Non-Stick Frying Pan 28cm',
                    'cost' => 1200,
                    'price' => 1750,
                    'min' => 5,
                    'unit' => 'Piece',
                    'image' => 'products/non-stick-pan.jpg',
                ],

                // ========================================================
                // HEALTH
                // ========================================================

                [
                    'category' => 'Health & Wellness',
                    'name' => 'Multivitamin Tablets 30s',
                    'cost' => 550,
                    'price' => 800,
                    'min' => 8,
                    'unit' => 'Pack',
                    'image' => 'products/multivitamins.jpg',
                ],

                // ========================================================
                // PREMIUM
                // ========================================================

                [
                    'category' => 'Premium Foods',
                    'name' => 'Ferrero Nutella Biscuits',
                    'cost' => 420,
                    'price' => 650,
                    'min' => 6,
                    'unit' => 'Pack',
                    'image' => 'products/nutella-biscuits.jpg',
                ],
                [
                    'category' => 'Premium Foods',
                    'name' => 'Imported Extra Virgin Olive Oil 500ml',
                    'cost' => 950,
                    'price' => 1400,
                    'min' => 5,
                    'unit' => 'Bottle',
                    'image' => 'products/olive-oil-500ml.jpg',
                ],
            ];


            foreach ($productData as $index => $data) {

                $sku = 'DEMO-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT);

                $product = Product::create([
                    'category_id' => $categories[$data['category']]->id,
                    'sku' => $sku,
                    'barcode' => '890' . str_pad((string) ($index + 1), 9, '0', STR_PAD_LEFT),
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => 'Premium retail product stocked by Tillora demo store.',
                    'image' => $data['image'],
                    'cost_price' => $data['cost'],
                    'selling_price' => $data['price'],
                    'stock_quantity' => 0,
                    'minimum_stock' => $data['min'],
                    'unit' => $data['unit'],
                    'is_active' => true,
                ]);

                $products[$data['name']] = $product;
            }


            /*
             * ============================================================
             * 5. PURCHASE HELPER
             * ============================================================
             */

            $createPurchase = function (
                string $number,
                Supplier $supplier,
                string $status,
                array $items,
                string $note
            ) use ($products): Purchase {

                $purchase = Purchase::create([
                    'purchase_number' => $number,
                    'supplier_id' => $supplier->id,
                    'purchase_date' => now()->subDays(rand(1, 30)),
                    'status' => $status,
                    'total_amount' => 0,
                    'note' => $note,
                ]);

                $total = 0;

                foreach ($items as $itemData) {

                    $product = $products[$itemData['product']];

                    $quantity = $itemData['quantity'];
                    $unitCost = $itemData['unit_cost'];

                    $lineTotal = $quantity * $unitCost;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'line_total' => $lineTotal,
                    ]);

                    $total += $lineTotal;
                }

                $purchase->update([
                    'total_amount' => $total,
                ]);

                return $purchase;
            };


            /*
             * ============================================================
             * 6. PURCHASES
             * ============================================================
             */

            $purchases = [];

            // ------------------------------------------------------------
            // RECEIVED PURCHASE 1 - BEVERAGES
            // ------------------------------------------------------------

            $purchases[] = $createPurchase(
                'DEMO-PO-00001',
                $suppliers['DEMO-SUP-001'],
                'Received',
                [
                    [
                        'product' => 'Coca-Cola 500ml',
                        'quantity' => 240,
                        'unit_cost' => 55,
                    ],
                    [
                        'product' => 'Coca-Cola 1.25L',
                        'quantity' => 120,
                        'unit_cost' => 95,
                    ],
                    [
                        'product' => 'Sprite 500ml',
                        'quantity' => 180,
                        'unit_cost' => 55,
                    ],
                    [
                        'product' => 'Fanta Orange 500ml',
                        'quantity' => 180,
                        'unit_cost' => 55,
                    ],
                    [
                        'product' => 'Minute Maid Mango 400ml',
                        'quantity' => 96,
                        'unit_cost' => 65,
                    ],
                    [
                        'product' => 'Schweppes Tonic 500ml',
                        'quantity' => 72,
                        'unit_cost' => 70,
                    ],
                ],
                'Main beverage restocking order.'
            );


            // ------------------------------------------------------------
            // RECEIVED PURCHASE 2 - ENERGY & WATER
            // ------------------------------------------------------------

            $purchases[] = $createPurchase(
                'DEMO-PO-00002',
                $suppliers['DEMO-SUP-001'],
                'Received',
                [
                    [
                        'product' => 'Keringet Water 1L',
                        'quantity' => 180,
                        'unit_cost' => 55,
                    ],
                    [
                        'product' => 'Aqua Mist Water 500ml',
                        'quantity' => 300,
                        'unit_cost' => 28,
                    ],
                    [
                        'product' => 'Red Bull 250ml',
                        'quantity' => 120,
                        'unit_cost' => 105,
                    ],
                    [
                        'product' => 'Monster Energy 500ml',
                        'quantity' => 96,
                        'unit_cost' => 130,
                    ],
                    [
                        'product' => 'Predator Energy 500ml',
                        'quantity' => 120,
                        'unit_cost' => 75,
                    ],
                ],
                'Fast-moving drinks and energy stock.'
            );


            // ------------------------------------------------------------
            // RECEIVED PURCHASE 3 - SNACKS
            // ------------------------------------------------------------

            $purchases[] = $createPurchase(
                'DEMO-PO-00003',
                $suppliers['DEMO-SUP-002'],
                'Received',
                [
                    [
                        'product' => 'Pringles Original 165g',
                        'quantity' => 60,
                        'unit_cost' => 280,
                    ],
                    [
                        'product' => 'Doritos Cheese 100g',
                        'quantity' => 100,
                        'unit_cost' => 120,
                    ],
                    [
                        'product' => 'Oreo Original 133g',
                        'quantity' => 120,
                        'unit_cost' => 85,
                    ],
                    [
                        'product' => 'KitKat 4 Finger',
                        'quantity' => 150,
                        'unit_cost' => 55,
                    ],
                    [
                        'product' => 'Snickers 50g',
                        'quantity' => 150,
                        'unit_cost' => 55,
                    ],
                    [
                        'product' => 'Ferrero Rocher 200g',
                        'quantity' => 30,
                        'unit_cost' => 850,
                    ],
                ],
                'Snacks and confectionery replenishment.'
            );


            // ------------------------------------------------------------
            // RECEIVED PURCHASE 4 - HOUSEHOLD / PERSONAL CARE
            // ------------------------------------------------------------

            $purchases[] = $createPurchase(
                'DEMO-PO-00004',
                $suppliers['DEMO-SUP-006'],
                'Received',
                [
                    [
                        'product' => 'Nivea Men Deep 150ml',
                        'quantity' => 40,
                        'unit_cost' => 390,
                    ],
                    [
                        'product' => 'Dove Beauty Bar 100g',
                        'quantity' => 80,
                        'unit_cost' => 100,
                    ],
                    [
                        'product' => 'Colgate Total 100ml',
                        'quantity' => 60,
                        'unit_cost' => 210,
                    ],
                    [
                        'product' => 'Vaseline Cocoa Glow 400ml',
                        'quantity' => 40,
                        'unit_cost' => 390,
                    ],
                    [
                        'product' => 'Gillette Blue II',
                        'quantity' => 50,
                        'unit_cost' => 180,
                    ],
                    [
                        'product' => 'Ariel Washing Powder 1kg',
                        'quantity' => 60,
                        'unit_cost' => 320,
                    ],
                    [
                        'product' => 'Omo Washing Powder 1kg',
                        'quantity' => 60,
                        'unit_cost' => 300,
                    ],
                    [
                        'product' => 'Harpic Toilet Cleaner 500ml',
                        'quantity' => 50,
                        'unit_cost' => 180,
                    ],
                    [
                        'product' => 'Jik Bleach 1L',
                        'quantity' => 60,
                        'unit_cost' => 140,
                    ],
                    [
                        'product' => 'Sunlight Dishwashing Liquid 750ml',
                        'quantity' => 60,
                        'unit_cost' => 170,
                    ],
                ],
                'Personal and household care restocking.'
            );


            // ------------------------------------------------------------
            // RECEIVED PURCHASE 5 - ELECTRONICS
            // ------------------------------------------------------------

            $purchases[] = $createPurchase(
                'DEMO-PO-00005',
                $suppliers['DEMO-SUP-005'],
                'Received',
                [
                    [
                        'product' => '20W USB-C Fast Charger',
                        'quantity' => 30,
                        'unit_cost' => 650,
                    ],
                    [
                        'product' => 'USB-C Fast Charging Cable',
                        'quantity' => 60,
                        'unit_cost' => 250,
                    ],
                    [
                        'product' => 'Power Bank 10000mAh',
                        'quantity' => 20,
                        'unit_cost' => 1050,
                    ],
                    [
                        'product' => 'Bluetooth Earbuds',
                        'quantity' => 15,
                        'unit_cost' => 1200,
                    ],
                    [
                        'product' => 'Wireless Mouse',
                        'quantity' => 20,
                        'unit_cost' => 650,
                    ],
                ],
                'Higher-margin electronics and accessories.'
            );


            // ------------------------------------------------------------
            // ORDERED PURCHASE
            // ------------------------------------------------------------

            $purchases[] = $createPurchase(
                'DEMO-PO-00006',
                $suppliers['DEMO-SUP-003'],
                'Ordered',
                [
                    [
                        'product' => 'Brookside Fresh Milk 1L',
                        'quantity' => 100,
                        'unit_cost' => 115,
                    ],
                    [
                        'product' => 'Brookside Yoghurt Strawberry 500ml',
                        'quantity' => 80,
                        'unit_cost' => 105,
                    ],
                    [
                        'product' => 'Blue Band Original 500g',
                        'quantity' => 60,
                        'unit_cost' => 210,
                    ],
                    [
                        'product' => 'Weetabix 430g',
                        'quantity' => 40,
                        'unit_cost' => 480,
                    ],
                ],
                'Incoming chilled and breakfast stock.'
            );


            // ------------------------------------------------------------
            // DRAFT PURCHASE
            // ------------------------------------------------------------

            $purchases[] = $createPurchase(
                'DEMO-PO-00007',
                $suppliers['DEMO-SUP-007'],
                'Draft',
                [
                    [
                        'product' => 'Huggies Dry Comfort Medium',
                        'quantity' => 40,
                        'unit_cost' => 850,
                    ],
                    [
                        'product' => 'Pampers Baby Dry Medium',
                        'quantity' => 40,
                        'unit_cost' => 900,
                    ],
                    [
                        'product' => 'Stainless Steel Flask 1L',
                        'quantity' => 20,
                        'unit_cost' => 950,
                    ],
                    [
                        'product' => 'Non-Stick Frying Pan 28cm',
                        'quantity' => 15,
                        'unit_cost' => 1200,
                    ],
                ],
                'Draft procurement plan awaiting approval.'
            );


            // ------------------------------------------------------------
            // CANCELLED PURCHASE
            // ------------------------------------------------------------

            $purchases[] = $createPurchase(
                'DEMO-PO-00008',
                $suppliers['DEMO-SUP-008'],
                'Cancelled',
                [
                    [
                        'product' => 'Ferrero Nutella Biscuits',
                        'quantity' => 50,
                        'unit_cost' => 420,
                    ],
                    [
                        'product' => 'Imported Extra Virgin Olive Oil 500ml',
                        'quantity' => 30,
                        'unit_cost' => 950,
                    ],
                ],
                'Cancelled due to supplier pricing changes.'
            );


            /*
             * ============================================================
             * 7. CALCULATE STOCK FROM RECEIVED PURCHASES
             * ============================================================
             */

            foreach ($products as $product) {
                $product->update([
                    'stock_quantity' => 0,
                ]);
            }

            foreach ($purchases as $purchase) {

                if ($purchase->status !== 'Received') {
                    continue;
                }

                foreach ($purchase->items as $item) {

                    $item->product->increment(
                        'stock_quantity',
                        $item->quantity
                    );

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'IN',
                        'quantity' => $item->quantity,
                        'reference_type' => 'Purchase',
                        'reference_id' => $purchase->id,
                        'user_id' => null,
                        'description' =>
                            'Stock received from purchase '
                            . $purchase->purchase_number,
                    ]);
                }
            }


            /*
             * ============================================================
             * 8. OUTPUT SUMMARY
             * ============================================================
             */

            $this->command->info('');
            $this->command->info('========================================');
            $this->command->info('       TILLORA DEMO DATA SEEDED');
            $this->command->info('========================================');
            $this->command->info('');

            $this->command->info(
                'Categories: ' . count($categories)
            );

            $this->command->info(
                'Suppliers: ' . count($suppliers)
            );

            $this->command->info(
                'Products: ' . count($products)
            );

            $this->command->info(
                'Purchases: ' . count($purchases)
            );

            $this->command->info('');

            $this->command->info('Purchase Statuses:');
            $this->command->info('  Received: 5');
            $this->command->info('  Ordered:  1');
            $this->command->info('  Draft:    1');
            $this->command->info('  Cancelled: 1');

            $this->command->info('');
            $this->command->info('Product images expect:');
            $this->command->info('storage/app/public/products/');
            $this->command->info('');
            $this->command->info('========================================');
        });
    }
}