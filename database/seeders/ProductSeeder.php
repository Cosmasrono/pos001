<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductBranchStock;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private int $skuCounter = 1;

    private function generateSku(string $prefix): string
    {
        return $prefix . '-' . str_pad($this->skuCounter++, 4, '0', STR_PAD_LEFT);
    }

    public function run(): void
    {
        // Resolve company — seeder has no authenticated user so we set it manually
        $company = Company::first();
        if (!$company) {
            $this->command->warn('No company found. Register first, then re-run the seeder.');
            return;
        }
        app()->instance('current_company_id', $company->id);

        $mainBranch  = Branch::where('company_id', $company->id)->where('is_main', true)->first();
        $allBranches = Branch::where('company_id', $company->id)->where('is_active', true)->get();

        if (!$mainBranch) {
            $this->command->warn('No main branch found for this company.');
            return;
        }

        $cat = fn(string $name) => Category::firstOrCreate(['name' => $name, 'company_id' => $company->id]);

        $prescription = $cat('Prescription Medicines');
        $otc          = $cat('Over-the-Counter (OTC)');
        $painFever    = $cat('Pain & Fever Relief');
        $antibiotics  = $cat('Antibiotics & Anti-Infectives');
        $vitamins     = $cat('Vitamins & Supplements');
        $firstAid     = $cat('First Aid & Wound Care');
        $babyCare     = $cat('Baby & Maternal Care');
        $personalCare = $cat('Personal Care & Hygiene');
        $devices      = $cat('Medical Devices & Equipment');
        $respiratory  = $cat('Respiratory & Allergy');
        $digestive    = $cat('Digestive Health');
        $chronic      = $cat('Chronic & Lifestyle');
        $eyeEarDental = $cat('Eye, Ear & Dental Care');
        $herbal       = $cat('Herbal & Traditional');

        // ------------------------------------------------------------------
        // Products — add 'main_only' => true to restrict to main branch only
        // ------------------------------------------------------------------
        $products = [
            // ── Pain & Fever Relief (all branches) ──────────────────────────
            ['name' => 'Paracetamol 500mg (100 tabs)',    'barcode' => '5000000000001', 'cost_price' => 120,  'selling_price' => 200,  'quantity_in_stock' => 300, 'reorder_level' => 80,  'category_id' => $painFever->id,    'main_only' => false],
            ['name' => 'Ibuprofen 400mg (100 tabs)',      'barcode' => '5000000000002', 'cost_price' => 180,  'selling_price' => 300,  'quantity_in_stock' => 250, 'reorder_level' => 60,  'category_id' => $painFever->id,    'main_only' => false],
            ['name' => 'Diclofenac 50mg (30 tabs)',       'barcode' => '5000000000003', 'cost_price' => 90,   'selling_price' => 150,  'quantity_in_stock' => 200, 'reorder_level' => 50,  'category_id' => $painFever->id,    'main_only' => false],
            ['name' => 'Aspirin 300mg (100 tabs)',        'barcode' => '5000000000004', 'cost_price' => 100,  'selling_price' => 170,  'quantity_in_stock' => 180, 'reorder_level' => 40,  'category_id' => $painFever->id,    'main_only' => false],
            ['name' => 'Mefenamic Acid 500mg (20 tabs)',  'barcode' => '5000000000005', 'cost_price' => 80,   'selling_price' => 140,  'quantity_in_stock' => 150, 'reorder_level' => 30,  'category_id' => $painFever->id,    'main_only' => false],

            // ── Antibiotics (all branches) ───────────────────────────────────
            ['name' => 'Amoxicillin 500mg (21 caps)',     'barcode' => '5000000000010', 'cost_price' => 150,  'selling_price' => 280,  'quantity_in_stock' => 200, 'reorder_level' => 50,  'category_id' => $antibiotics->id,  'main_only' => false],
            ['name' => 'Azithromycin 500mg (3 tabs)',     'barcode' => '5000000000011', 'cost_price' => 200,  'selling_price' => 350,  'quantity_in_stock' => 150, 'reorder_level' => 30,  'category_id' => $antibiotics->id,  'main_only' => false],
            ['name' => 'Metronidazole 400mg (21 tabs)',   'barcode' => '5000000000012', 'cost_price' => 80,   'selling_price' => 150,  'quantity_in_stock' => 180, 'reorder_level' => 40,  'category_id' => $antibiotics->id,  'main_only' => false],
            ['name' => 'Ciprofloxacin 500mg (10 tabs)',   'barcode' => '5000000000013', 'cost_price' => 130,  'selling_price' => 250,  'quantity_in_stock' => 120, 'reorder_level' => 25,  'category_id' => $antibiotics->id,  'main_only' => false],
            ['name' => 'Fluconazole 150mg (1 cap)',       'barcode' => '5000000000014', 'cost_price' => 50,   'selling_price' => 100,  'quantity_in_stock' => 200, 'reorder_level' => 50,  'category_id' => $antibiotics->id,  'main_only' => false],

            // ── OTC (all branches) ───────────────────────────────────────────
            ['name' => 'Oral Rehydration Salts x20',     'barcode' => '5000000000020', 'cost_price' => 60,   'selling_price' => 120,  'quantity_in_stock' => 250, 'reorder_level' => 60,  'category_id' => $otc->id,          'main_only' => false],
            ['name' => 'Cetirizine 10mg (30 tabs)',       'barcode' => '5000000000021', 'cost_price' => 100,  'selling_price' => 180,  'quantity_in_stock' => 180, 'reorder_level' => 40,  'category_id' => $otc->id,          'main_only' => false],
            ['name' => 'Loperamide 2mg (10 caps)',        'barcode' => '5000000000022', 'cost_price' => 60,   'selling_price' => 120,  'quantity_in_stock' => 200, 'reorder_level' => 50,  'category_id' => $otc->id,          'main_only' => false],
            ['name' => 'Antacid Suspension 200ml',        'barcode' => '5000000000023', 'cost_price' => 150,  'selling_price' => 250,  'quantity_in_stock' => 120, 'reorder_level' => 30,  'category_id' => $otc->id,          'main_only' => false],
            ['name' => 'Zinc Sulphate 20mg (100 tabs)',   'barcode' => '5000000000024', 'cost_price' => 80,   'selling_price' => 150,  'quantity_in_stock' => 160, 'reorder_level' => 30,  'category_id' => $otc->id,          'main_only' => false],

            // ── Vitamins (all branches) ──────────────────────────────────────
            ['name' => 'Multivitamin Tablets (100 tabs)', 'barcode' => '5000000000030', 'cost_price' => 250,  'selling_price' => 450,  'quantity_in_stock' => 180, 'reorder_level' => 40,  'category_id' => $vitamins->id,     'main_only' => false],
            ['name' => 'Vitamin C 1000mg (30 tabs)',      'barcode' => '5000000000031', 'cost_price' => 200,  'selling_price' => 350,  'quantity_in_stock' => 150, 'reorder_level' => 30,  'category_id' => $vitamins->id,     'main_only' => false],
            ['name' => 'Iron + Folic Acid (100 tabs)',    'barcode' => '5000000000032', 'cost_price' => 120,  'selling_price' => 220,  'quantity_in_stock' => 200, 'reorder_level' => 50,  'category_id' => $vitamins->id,     'main_only' => false],
            ['name' => 'Calcium + Vitamin D3 (60 tabs)',  'barcode' => '5000000000033', 'cost_price' => 300,  'selling_price' => 500,  'quantity_in_stock' => 120, 'reorder_level' => 25,  'category_id' => $vitamins->id,     'main_only' => false],
            ['name' => 'Omega-3 Fish Oil (60 softgels)',  'barcode' => '5000000000034', 'cost_price' => 450,  'selling_price' => 750,  'quantity_in_stock' => 90,  'reorder_level' => 20,  'category_id' => $vitamins->id,     'main_only' => false],

            // ── Respiratory (all branches) ───────────────────────────────────
            ['name' => 'Cough Syrup 100ml',               'barcode' => '5000000000040', 'cost_price' => 150,  'selling_price' => 280,  'quantity_in_stock' => 160, 'reorder_level' => 35,  'category_id' => $respiratory->id,  'main_only' => false],
            ['name' => 'Salbutamol Inhaler 100mcg',        'barcode' => '5000000000041', 'cost_price' => 350,  'selling_price' => 600,  'quantity_in_stock' => 80,  'reorder_level' => 15,  'category_id' => $respiratory->id,  'main_only' => false],
            ['name' => 'Loratadine 10mg (30 tabs)',        'barcode' => '5000000000042', 'cost_price' => 90,   'selling_price' => 170,  'quantity_in_stock' => 180, 'reorder_level' => 40,  'category_id' => $respiratory->id,  'main_only' => false],
            ['name' => 'Nasal Decongestant Spray 15ml',    'barcode' => '5000000000043', 'cost_price' => 180,  'selling_price' => 300,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $respiratory->id,  'main_only' => false],
            ['name' => 'Menthol Vapour Rub 50g',           'barcode' => '5000000000044', 'cost_price' => 120,  'selling_price' => 200,  'quantity_in_stock' => 140, 'reorder_level' => 30,  'category_id' => $respiratory->id,  'main_only' => false],

            // ── Digestive Health (all branches) ─────────────────────────────
            ['name' => 'Omeprazole 20mg (28 caps)',        'barcode' => '5000000000050', 'cost_price' => 150,  'selling_price' => 280,  'quantity_in_stock' => 180, 'reorder_level' => 40,  'category_id' => $digestive->id,    'main_only' => false],
            ['name' => 'Ranitidine 150mg (30 tabs)',       'barcode' => '5000000000051', 'cost_price' => 100,  'selling_price' => 180,  'quantity_in_stock' => 150, 'reorder_level' => 30,  'category_id' => $digestive->id,    'main_only' => false],
            ['name' => 'Bisacodyl 5mg (30 tabs)',          'barcode' => '5000000000052', 'cost_price' => 70,   'selling_price' => 130,  'quantity_in_stock' => 120, 'reorder_level' => 25,  'category_id' => $digestive->id,    'main_only' => false],
            ['name' => 'Probiotic Capsules (30 caps)',     'barcode' => '5000000000053', 'cost_price' => 350,  'selling_price' => 600,  'quantity_in_stock' => 90,  'reorder_level' => 15,  'category_id' => $digestive->id,    'main_only' => false],

            // ── Chronic & Lifestyle (main branch only — high-value/controlled) ─
            ['name' => 'Metformin 500mg (100 tabs)',       'barcode' => '5000000000060', 'cost_price' => 200,  'selling_price' => 380,  'quantity_in_stock' => 150, 'reorder_level' => 30,  'category_id' => $chronic->id,      'main_only' => true],
            ['name' => 'Amlodipine 5mg (30 tabs)',         'barcode' => '5000000000061', 'cost_price' => 80,   'selling_price' => 150,  'quantity_in_stock' => 180, 'reorder_level' => 40,  'category_id' => $chronic->id,      'main_only' => true],
            ['name' => 'Atorvastatin 20mg (30 tabs)',      'barcode' => '5000000000062', 'cost_price' => 120,  'selling_price' => 230,  'quantity_in_stock' => 140, 'reorder_level' => 30,  'category_id' => $chronic->id,      'main_only' => true],
            ['name' => 'Losartan 50mg (30 tabs)',          'barcode' => '5000000000063', 'cost_price' => 100,  'selling_price' => 200,  'quantity_in_stock' => 160, 'reorder_level' => 35,  'category_id' => $chronic->id,      'main_only' => true],
            ['name' => 'Levothyroxine 50mcg (100 tabs)',   'barcode' => '5000000000064', 'cost_price' => 250,  'selling_price' => 420,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $chronic->id,      'main_only' => true],

            // ── First Aid (all branches) ─────────────────────────────────────
            ['name' => 'Adhesive Bandages (100 pcs)',      'barcode' => '5000000000070', 'cost_price' => 150,  'selling_price' => 280,  'quantity_in_stock' => 120, 'reorder_level' => 25,  'category_id' => $firstAid->id,     'main_only' => false],
            ['name' => 'Cotton Wool Roll 500g',            'barcode' => '5000000000071', 'cost_price' => 200,  'selling_price' => 350,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $firstAid->id,     'main_only' => false],
            ['name' => 'Povidone Iodine 10% 100ml',       'barcode' => '5000000000072', 'cost_price' => 180,  'selling_price' => 320,  'quantity_in_stock' => 90,  'reorder_level' => 15,  'category_id' => $firstAid->id,     'main_only' => false],
            ['name' => 'Gauze Swabs (100 pcs)',            'barcode' => '5000000000073', 'cost_price' => 120,  'selling_price' => 220,  'quantity_in_stock' => 110, 'reorder_level' => 20,  'category_id' => $firstAid->id,     'main_only' => false],
            ['name' => 'Hydrogen Peroxide 3% 200ml',       'barcode' => '5000000000074', 'cost_price' => 100,  'selling_price' => 180,  'quantity_in_stock' => 80,  'reorder_level' => 15,  'category_id' => $firstAid->id,     'main_only' => false],

            // ── Baby & Maternal (all branches) ───────────────────────────────
            ['name' => 'Baby Diapers Medium (40 pcs)',     'barcode' => '5000000000080', 'cost_price' => 600,  'selling_price' => 950,  'quantity_in_stock' => 80,  'reorder_level' => 15,  'category_id' => $babyCare->id,     'main_only' => false],
            ['name' => 'Baby Gripe Water 150ml',           'barcode' => '5000000000081', 'cost_price' => 200,  'selling_price' => 350,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $babyCare->id,     'main_only' => false],
            ['name' => 'Infant Paracetamol Drops 15ml',    'barcode' => '5000000000082', 'cost_price' => 180,  'selling_price' => 320,  'quantity_in_stock' => 90,  'reorder_level' => 20,  'category_id' => $babyCare->id,     'main_only' => false],
            ['name' => 'Prenatal Vitamins (60 tabs)',      'barcode' => '5000000000083', 'cost_price' => 350,  'selling_price' => 580,  'quantity_in_stock' => 70,  'reorder_level' => 15,  'category_id' => $babyCare->id,     'main_only' => false],
            ['name' => 'Baby Petroleum Jelly 250g',        'barcode' => '5000000000084', 'cost_price' => 150,  'selling_price' => 250,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $babyCare->id,     'main_only' => false],

            // ── Personal Care (all branches) ─────────────────────────────────
            ['name' => 'Hand Sanitizer 500ml',             'barcode' => '5000000000090', 'cost_price' => 250,  'selling_price' => 420,  'quantity_in_stock' => 120, 'reorder_level' => 25,  'category_id' => $personalCare->id, 'main_only' => false],
            ['name' => 'Surgical Face Masks (50 pcs)',     'barcode' => '5000000000091', 'cost_price' => 200,  'selling_price' => 350,  'quantity_in_stock' => 150, 'reorder_level' => 30,  'category_id' => $personalCare->id, 'main_only' => false],
            ['name' => 'Toothpaste 100ml',                 'barcode' => '5000000000092', 'cost_price' => 120,  'selling_price' => 200,  'quantity_in_stock' => 180, 'reorder_level' => 40,  'category_id' => $personalCare->id, 'main_only' => false],
            ['name' => 'Antiseptic Soap 100g',             'barcode' => '5000000000093', 'cost_price' => 80,   'selling_price' => 150,  'quantity_in_stock' => 200, 'reorder_level' => 50,  'category_id' => $personalCare->id, 'main_only' => false],
            ['name' => 'Sanitary Pads (10 pcs)',           'barcode' => '5000000000094', 'cost_price' => 100,  'selling_price' => 180,  'quantity_in_stock' => 200, 'reorder_level' => 50,  'category_id' => $personalCare->id, 'main_only' => false],

            // ── Medical Devices (main branch only — expensive/specialised) ───
            ['name' => 'Digital Thermometer',              'barcode' => '5000000000100', 'cost_price' => 350,  'selling_price' => 600,  'quantity_in_stock' => 60,  'reorder_level' => 10,  'category_id' => $devices->id,      'main_only' => true],
            ['name' => 'Blood Pressure Monitor',           'barcode' => '5000000000101', 'cost_price' => 2500, 'selling_price' => 4200, 'quantity_in_stock' => 20,  'reorder_level' => 5,   'category_id' => $devices->id,      'main_only' => true],
            ['name' => 'Glucometer Kit',                   'barcode' => '5000000000102', 'cost_price' => 1800, 'selling_price' => 3000, 'quantity_in_stock' => 25,  'reorder_level' => 5,   'category_id' => $devices->id,      'main_only' => true],
            ['name' => 'Nebulizer Machine',                'barcode' => '5000000000103', 'cost_price' => 3500, 'selling_price' => 5500, 'quantity_in_stock' => 10,  'reorder_level' => 3,   'category_id' => $devices->id,      'main_only' => true],
            ['name' => 'Disposable Syringes 5ml (100 pcs)','barcode' => '5000000000104', 'cost_price' => 350,  'selling_price' => 600,  'quantity_in_stock' => 80,  'reorder_level' => 15,  'category_id' => $devices->id,      'main_only' => true],

            // ── Eye, Ear & Dental (all branches) ────────────────────────────
            ['name' => 'Eye Drops (Lubricant) 10ml',       'barcode' => '5000000000110', 'cost_price' => 200,  'selling_price' => 380,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $eyeEarDental->id, 'main_only' => false],
            ['name' => 'Chloramphenicol Eye Drops 10ml',   'barcode' => '5000000000111', 'cost_price' => 120,  'selling_price' => 220,  'quantity_in_stock' => 80,  'reorder_level' => 15,  'category_id' => $eyeEarDental->id, 'main_only' => false],
            ['name' => 'Ear Drops (Wax Removal) 10ml',    'barcode' => '5000000000112', 'cost_price' => 150,  'selling_price' => 280,  'quantity_in_stock' => 70,  'reorder_level' => 15,  'category_id' => $eyeEarDental->id, 'main_only' => false],
            ['name' => 'Oral Gel for Toothache 10g',       'barcode' => '5000000000113', 'cost_price' => 100,  'selling_price' => 180,  'quantity_in_stock' => 90,  'reorder_level' => 20,  'category_id' => $eyeEarDental->id, 'main_only' => false],

            // ── Herbal (all branches) ────────────────────────────────────────
            ['name' => 'Aloe Vera Gel 200ml',              'barcode' => '5000000000120', 'cost_price' => 250,  'selling_price' => 420,  'quantity_in_stock' => 90,  'reorder_level' => 15,  'category_id' => $herbal->id,       'main_only' => false],
            ['name' => 'Eucalyptus Oil 50ml',              'barcode' => '5000000000121', 'cost_price' => 180,  'selling_price' => 300,  'quantity_in_stock' => 80,  'reorder_level' => 15,  'category_id' => $herbal->id,       'main_only' => false],
            ['name' => 'Honey & Lemon Syrup 200ml',        'barcode' => '5000000000122', 'cost_price' => 200,  'selling_price' => 350,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $herbal->id,       'main_only' => false],
            ['name' => 'Turmeric Capsules (60 caps)',      'barcode' => '5000000000123', 'cost_price' => 300,  'selling_price' => 500,  'quantity_in_stock' => 70,  'reorder_level' => 15,  'category_id' => $herbal->id,       'main_only' => false],

            // ── Prescription (main branch only — controlled dispensing) ──────
            ['name' => 'Prednisolone 5mg (100 tabs)',      'barcode' => '5000000000130', 'cost_price' => 200,  'selling_price' => 380,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $prescription->id, 'main_only' => true],
            ['name' => 'Amoxicillin Suspension 125mg/5ml', 'barcode' => '5000000000131', 'cost_price' => 180,  'selling_price' => 320,  'quantity_in_stock' => 120, 'reorder_level' => 25,  'category_id' => $prescription->id, 'main_only' => true],
            ['name' => 'Tramadol 50mg (20 caps)',          'barcode' => '5000000000132', 'cost_price' => 250,  'selling_price' => 420,  'quantity_in_stock' => 60,  'reorder_level' => 10,  'category_id' => $prescription->id, 'main_only' => true],
            ['name' => 'Enalapril 10mg (30 tabs)',         'barcode' => '5000000000133', 'cost_price' => 100,  'selling_price' => 200,  'quantity_in_stock' => 140, 'reorder_level' => 30,  'category_id' => $prescription->id, 'main_only' => true],
            ['name' => 'Glibenclamide 5mg (100 tabs)',     'barcode' => '5000000000134', 'cost_price' => 150,  'selling_price' => 280,  'quantity_in_stock' => 100, 'reorder_level' => 20,  'category_id' => $prescription->id, 'main_only' => true],
        ];

        $otherBranches = $allBranches->where('is_main', false);

        foreach ($products as $data) {
            $mainOnly = $data['main_only'];
            unset($data['main_only']);

            $data['sku']        = $this->generateSku('PH');
            $data['company_id'] = $company->id;
            $data['is_active']  = true;

            $product = Product::updateOrCreate(
                ['barcode' => $data['barcode'], 'company_id' => $company->id],
                $data
            );

            $totalStock = $product->quantity_in_stock;

            if ($mainOnly || $otherBranches->isEmpty()) {
                // All stock goes to main branch only
                ProductBranchStock::updateOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $mainBranch->id],
                    ['quantity_in_stock' => $totalStock, 'initial_allocation' => $totalStock]
                );
            } else {
                // Main branch gets 40%, other branches split the remaining 60% equally
                $mainQty  = (int) ceil($totalStock * 0.40);
                $leftover = $totalStock - $mainQty;
                $perOther = $otherBranches->count() > 0 ? (int) ceil($leftover / $otherBranches->count()) : 0;

                ProductBranchStock::updateOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $mainBranch->id],
                    ['quantity_in_stock' => $mainQty, 'initial_allocation' => $mainQty]
                );

                foreach ($otherBranches as $branch) {
                    ProductBranchStock::updateOrCreate(
                        ['product_id' => $product->id, 'branch_id' => $branch->id],
                        ['quantity_in_stock' => $perOther, 'initial_allocation' => $perOther]
                    );
                }
            }
        }

        $this->command->info('Seeded ' . count($products) . ' products for company: ' . $company->name);
        $this->command->info('Main-only products: ' . collect($products)->where('main_only', true)->count() . ' (Chronic, Devices, Prescription)');
        $this->command->info('All-branch products: ' . collect($products)->where('main_only', false)->count());
    }
}
