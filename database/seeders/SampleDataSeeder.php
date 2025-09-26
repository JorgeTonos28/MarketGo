<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\ConsumptionLog;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\Supermarket;
use App\Models\SupermarketSection;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $categories = $this->seedCategories();
            $supermarkets = $this->seedSupermarkets();
            $products = $this->seedProducts($categories);
            $inventoryItems = $this->seedInventory($products, $supermarkets);

            [$adminUser, $demoUser] = $this->seedUsers();

            $this->seedShoppingList($adminUser, $supermarkets, $products, $inventoryItems);
            $this->seedConsumptionLog($adminUser, $supermarkets, $products);
            $this->seedContributions($adminUser, $demoUser, $products, $supermarkets);
        });
    }

    /**
     * @return array<string, ProductCategory>
     */
    private function seedCategories(): array
    {
        $categoriesData = [
            ['name' => 'Lácteos y Huevos', 'icon' => 'ph:egg-crack', 'description' => 'Productos refrigerados, quesos y huevos frescos.'],
            ['name' => 'Frutas y Verduras', 'icon' => 'ph:leaf', 'description' => 'Ingredientes frescos de temporada.'],
            ['name' => 'Despensa', 'icon' => 'ph:basket', 'description' => 'Enlatados, granos, pastas y básicos de cocina.'],
            ['name' => 'Bebidas', 'icon' => 'ph:coffee', 'description' => 'Cafés, tés, jugos y bebidas embotelladas.'],
            ['name' => 'Limpieza y Hogar', 'icon' => 'ph:broom', 'description' => 'Artículos para el cuidado del hogar.'],
        ];

        $categories = [];

        foreach ($categoriesData as $categoryData) {
            $slug = Str::slug($categoryData['name']);

            $categories[$slug] = ProductCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $categoryData['name'],
                    'icon' => $categoryData['icon'],
                    'description' => $categoryData['description'],
                ],
            );
        }

        return $categories;
    }

    /**
     * @return array<string, array{model: Supermarket, sections: array<string, SupermarketSection>}> 
     */
    private function seedSupermarkets(): array
    {
        $supermarketsData = [
            [
                'name' => 'Supermercado Central',
                'brand' => 'Central',
                'address_line1' => 'Av. Reforma 123',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'country' => 'México',
                'postal_code' => '06500',
                'latitude' => 19.432608,
                'longitude' => -99.133209,
                'opening_hours' => [
                    'monday' => ['08:00', '22:00'],
                    'tuesday' => ['08:00', '22:00'],
                    'wednesday' => ['08:00', '22:00'],
                    'thursday' => ['08:00', '22:00'],
                    'friday' => ['08:00', '23:00'],
                    'saturday' => ['08:00', '23:00'],
                    'sunday' => ['09:00', '21:00'],
                ],
                'sections' => [
                    ['name' => 'Entrada y Ofertas', 'position' => 1, 'color' => '#F97316'],
                    ['name' => 'Frutas y Verduras', 'position' => 2, 'color' => '#16A34A'],
                    ['name' => 'Lácteos y Refrigerados', 'position' => 3, 'color' => '#38BDF8'],
                    ['name' => 'Despensa', 'position' => 4, 'color' => '#FACC15'],
                    ['name' => 'Limpieza y Hogar', 'position' => 5, 'color' => '#A855F7'],
                    ['name' => 'Cajas', 'position' => 6, 'color' => '#9CA3AF'],
                ],
            ],
            [
                'name' => 'Mercado Express',
                'brand' => 'Express',
                'address_line1' => 'Calle 5 de Mayo 45',
                'city' => 'Guadalajara',
                'state' => 'Jalisco',
                'country' => 'México',
                'postal_code' => '44100',
                'latitude' => 20.67359,
                'longitude' => -103.343803,
                'opening_hours' => [
                    'monday' => ['07:30', '22:00'],
                    'tuesday' => ['07:30', '22:00'],
                    'wednesday' => ['07:30', '22:00'],
                    'thursday' => ['07:30', '22:00'],
                    'friday' => ['07:30', '22:30'],
                    'saturday' => ['07:30', '22:30'],
                    'sunday' => ['08:30', '21:30'],
                ],
                'sections' => [
                    ['name' => 'Frutas y Verduras', 'position' => 1, 'color' => '#22C55E'],
                    ['name' => 'Carnes y Delicatesen', 'position' => 2, 'color' => '#F87171'],
                    ['name' => 'Lácteos', 'position' => 3, 'color' => '#60A5FA'],
                    ['name' => 'Despensa y Abarrotes', 'position' => 4, 'color' => '#EAB308'],
                    ['name' => 'Bebidas', 'position' => 5, 'color' => '#0EA5E9'],
                    ['name' => 'Limpieza y Cuidado del Hogar', 'position' => 6, 'color' => '#A855F7'],
                ],
            ],
            [
                'name' => 'Ferretería La Esquina',
                'brand' => 'Ferretería',
                'address_line1' => 'Av. Obreros 987',
                'city' => 'Monterrey',
                'state' => 'Nuevo León',
                'country' => 'México',
                'postal_code' => '64000',
                'latitude' => 25.686613,
                'longitude' => -100.316116,
                'opening_hours' => [
                    'monday' => ['09:00', '19:00'],
                    'tuesday' => ['09:00', '19:00'],
                    'wednesday' => ['09:00', '19:00'],
                    'thursday' => ['09:00', '19:00'],
                    'friday' => ['09:00', '19:00'],
                    'saturday' => ['09:00', '18:00'],
                    'sunday' => ['10:00', '15:00'],
                ],
                'sections' => [
                    ['name' => 'Herramientas manuales', 'position' => 1, 'color' => '#F97316'],
                    ['name' => 'Ferretería pesada', 'position' => 2, 'color' => '#2563EB'],
                    ['name' => 'Pinturas y selladores', 'position' => 3, 'color' => '#9333EA'],
                    ['name' => 'Jardinería', 'position' => 4, 'color' => '#16A34A'],
                    ['name' => 'Cajas y facturación', 'position' => 5, 'color' => '#64748B'],
                ],
            ],
            [
                'name' => 'Tienda Urbana Departamental',
                'brand' => 'Urbana',
                'address_line1' => 'Paseo del Centro 450',
                'city' => 'Querétaro',
                'state' => 'Querétaro',
                'country' => 'México',
                'postal_code' => '76000',
                'latitude' => 20.588793,
                'longitude' => -100.389888,
                'opening_hours' => [
                    'monday' => ['10:00', '21:00'],
                    'tuesday' => ['10:00', '21:00'],
                    'wednesday' => ['10:00', '21:00'],
                    'thursday' => ['10:00', '21:00'],
                    'friday' => ['10:00', '22:00'],
                    'saturday' => ['10:00', '22:00'],
                    'sunday' => ['11:00', '20:00'],
                ],
                'sections' => [
                    ['name' => 'Electrodomésticos', 'position' => 1, 'color' => '#0EA5E9'],
                    ['name' => 'Hogar y decoración', 'position' => 2, 'color' => '#FBBF24'],
                    ['name' => 'Ropa y accesorios', 'position' => 3, 'color' => '#EC4899'],
                    ['name' => 'Tecnología', 'position' => 4, 'color' => '#6366F1'],
                    ['name' => 'Cajas principales', 'position' => 5, 'color' => '#475569'],
                ],
            ],
        ];

        $supermarkets = [];

        foreach ($supermarketsData as $supermarketData) {
            $slug = Str::slug($supermarketData['name']);

            $supermarket = Supermarket::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $supermarketData['name'],
                    'brand' => $supermarketData['brand'],
                    'address_line1' => $supermarketData['address_line1'],
                    'address_line2' => $supermarketData['address_line2'] ?? null,
                    'city' => $supermarketData['city'],
                    'state' => $supermarketData['state'],
                    'country' => $supermarketData['country'],
                    'postal_code' => $supermarketData['postal_code'],
                    'latitude' => $supermarketData['latitude'],
                    'longitude' => $supermarketData['longitude'],
                    'opening_hours' => $supermarketData['opening_hours'],
                ],
            );

            $sections = [];
            foreach ($supermarketData['sections'] as $sectionData) {
                $section = SupermarketSection::updateOrCreate(
                    [
                        'supermarket_id' => $supermarket->id,
                        'name' => $sectionData['name'],
                    ],
                    [
                        'position' => $sectionData['position'],
                        'color' => $sectionData['color'] ?? null,
                        'is_active' => $sectionData['is_active'] ?? true,
                    ],
                );

                $sections[$section->name] = $section;
            }

            $supermarkets[$slug] = [
                'model' => $supermarket,
                'sections' => $sections,
            ];
        }

        return $supermarkets;
    }

    /**
     * @param  array<string, ProductCategory>  $categories
     * @return array<string, Product>
     */
    private function seedProducts(array $categories): array
    {
        $productsData = [
            [
                'name' => 'Leche entera 1L',
                'slug' => 'leche-entera-1l',
                'brand' => 'La Vaquita',
                'category' => 'lacteos-y-huevos',
                'barcode' => '7501234567890',
                'package_size' => '1 L',
                'unit' => 'pieza',
                'average_price' => 28.50,
                'image_url' => 'https://images.unsplash.com/photo-1580915411954-282cb1b0d780?auto=format&fit=crop&w=600&q=80',
                'description' => 'Leche entera pasteurizada ideal para desayunos y recetas.',
            ],
            [
                'name' => 'Huevos orgánicos docena',
                'slug' => 'huevos-organicos-docena',
                'brand' => 'Granja Verde',
                'category' => 'lacteos-y-huevos',
                'barcode' => '7500987654321',
                'package_size' => '12 pz',
                'unit' => 'paquete',
                'average_price' => 52.90,
                'image_url' => 'https://images.unsplash.com/photo-1515548211226-06d01d7eae76?auto=format&fit=crop&w=600&q=80',
                'description' => 'Huevos orgánicos libres de jaula certificados.',
            ],
            [
                'name' => 'Manzana gala kilo',
                'slug' => 'manzana-gala-kilo',
                'brand' => 'Campo Vivo',
                'category' => 'frutas-y-verduras',
                'barcode' => null,
                'package_size' => '1 kg',
                'unit' => 'kilogramo',
                'average_price' => 45.75,
                'image_url' => 'https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?auto=format&fit=crop&w=600&q=80',
                'description' => 'Manzanas gala crujientes y dulces.',
            ],
            [
                'name' => 'Lechuga romana pieza',
                'slug' => 'lechuga-romana-pieza',
                'brand' => 'Huerto Fresco',
                'category' => 'frutas-y-verduras',
                'barcode' => null,
                'package_size' => '1 pza',
                'unit' => 'pieza',
                'average_price' => 18.90,
                'image_url' => 'https://images.unsplash.com/photo-1506807803488-8eafc15323c1?auto=format&fit=crop&w=600&q=80',
                'description' => 'Lechuga fresca ideal para ensaladas.',
            ],
            [
                'name' => 'Arroz integral 1kg',
                'slug' => 'arroz-integral-1kg',
                'brand' => 'Cosecha Dorada',
                'category' => 'despensa',
                'barcode' => '7505432167890',
                'package_size' => '1 kg',
                'unit' => 'paquete',
                'average_price' => 34.10,
                'image_url' => 'https://images.unsplash.com/photo-1604908177225-dce4fc419c30?auto=format&fit=crop&w=600&q=80',
                'description' => 'Arroz integral de grano largo fuente de fibra.',
            ],
            [
                'name' => 'Frijol negro 900g',
                'slug' => 'frijol-negro-900g',
                'brand' => 'Casa Azteca',
                'category' => 'despensa',
                'barcode' => '7505123498765',
                'package_size' => '900 g',
                'unit' => 'paquete',
                'average_price' => 36.50,
                'image_url' => 'https://images.unsplash.com/photo-1506801310323-534be5e7c0cb?auto=format&fit=crop&w=600&q=80',
                'description' => 'Frijol negro seleccionado listo para cocinar.',
            ],
            [
                'name' => 'Café molido 400g',
                'slug' => 'cafe-molido-400g',
                'brand' => 'Sierra Alta',
                'category' => 'bebidas',
                'barcode' => '7507778889991',
                'package_size' => '400 g',
                'unit' => 'paquete',
                'average_price' => 139.00,
                'image_url' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80',
                'description' => 'Café molido de altura con tueste medio.',
            ],
            [
                'name' => 'Jabón líquido para ropa 3L',
                'slug' => 'jabon-liquido-ropa-3l',
                'brand' => 'Pureza',
                'category' => 'limpieza-y-hogar',
                'barcode' => '7502223334445',
                'package_size' => '3 L',
                'unit' => 'botella',
                'average_price' => 189.90,
                'image_url' => 'https://images.unsplash.com/photo-1581579186988-c6ef1fcf5ef2?auto=format&fit=crop&w=600&q=80',
                'description' => 'Detergente líquido con fragancia fresca y fórmula biodegradable.',
            ],
        ];

        $products = [];

        foreach ($productsData as $productData) {
            $category = $categories[$productData['category']] ?? null;

            if ($category === null) {
                continue;
            }

            $products[$productData['slug']] = Product::updateOrCreate(
                ['slug' => $productData['slug']],
                [
                    'product_category_id' => $category->id,
                    'name' => $productData['name'],
                    'brand' => $productData['brand'],
                    'barcode' => $productData['barcode'],
                    'package_size' => $productData['package_size'],
                    'unit' => $productData['unit'],
                    'average_price' => $productData['average_price'],
                    'image_url' => $productData['image_url'],
                    'description' => $productData['description'],
                ],
            );
        }

        return $products;
    }

    /**
     * @param  array<string, Product>  $products
     * @param  array<string, array{model: Supermarket, sections: array<string, SupermarketSection>}>  $supermarkets
     * @return array<string, InventoryItem>
     */
    private function seedInventory(array $products, array $supermarkets): array
    {
        $inventoryData = [
            ['product_slug' => 'leche-entera-1l', 'supermarket_slug' => 'supermercado-central', 'section_name' => 'Lácteos y Refrigerados', 'price' => 27.90, 'status' => 'in_stock', 'stock_quantity' => 42, 'checked_days_ago' => 1],
            ['product_slug' => 'leche-entera-1l', 'supermarket_slug' => 'mercado-express', 'section_name' => 'Lácteos', 'price' => 28.50, 'status' => 'in_stock', 'stock_quantity' => 35, 'checked_days_ago' => 2],
            ['product_slug' => 'huevos-organicos-docena', 'supermarket_slug' => 'supermercado-central', 'section_name' => 'Lácteos y Refrigerados', 'price' => 51.40, 'status' => 'low_stock', 'stock_quantity' => 12, 'checked_days_ago' => 1],
            ['product_slug' => 'huevos-organicos-docena', 'supermarket_slug' => 'mercado-express', 'section_name' => 'Lácteos', 'price' => 53.20, 'status' => 'in_stock', 'stock_quantity' => 24, 'checked_days_ago' => 3],
            ['product_slug' => 'manzana-gala-kilo', 'supermarket_slug' => 'supermercado-central', 'section_name' => 'Frutas y Verduras', 'price' => 44.30, 'status' => 'in_stock', 'stock_quantity' => 58, 'checked_days_ago' => 0],
            ['product_slug' => 'manzana-gala-kilo', 'supermarket_slug' => 'mercado-express', 'section_name' => 'Frutas y Verduras', 'price' => 46.10, 'status' => 'in_stock', 'stock_quantity' => 61, 'checked_days_ago' => 1],
            ['product_slug' => 'lechuga-romana-pieza', 'supermarket_slug' => 'supermercado-central', 'section_name' => 'Frutas y Verduras', 'price' => 17.40, 'status' => 'in_stock', 'stock_quantity' => 36, 'checked_days_ago' => 0],
            ['product_slug' => 'lechuga-romana-pieza', 'supermarket_slug' => 'mercado-express', 'section_name' => 'Frutas y Verduras', 'price' => 19.20, 'status' => 'in_stock', 'stock_quantity' => 28, 'checked_days_ago' => 2],
            ['product_slug' => 'arroz-integral-1kg', 'supermarket_slug' => 'supermercado-central', 'section_name' => 'Despensa', 'price' => 33.80, 'status' => 'in_stock', 'stock_quantity' => 74, 'checked_days_ago' => 1],
            ['product_slug' => 'arroz-integral-1kg', 'supermarket_slug' => 'mercado-express', 'section_name' => 'Despensa y Abarrotes', 'price' => 35.10, 'status' => 'in_stock', 'stock_quantity' => 65, 'checked_days_ago' => 2],
            ['product_slug' => 'frijol-negro-900g', 'supermarket_slug' => 'supermercado-central', 'section_name' => 'Despensa', 'price' => 35.90, 'status' => 'in_stock', 'stock_quantity' => 80, 'checked_days_ago' => 1],
            ['product_slug' => 'frijol-negro-900g', 'supermarket_slug' => 'mercado-express', 'section_name' => 'Despensa y Abarrotes', 'price' => 37.50, 'status' => 'in_stock', 'stock_quantity' => 73, 'checked_days_ago' => 3],
            ['product_slug' => 'cafe-molido-400g', 'supermarket_slug' => 'supermercado-central', 'section_name' => 'Despensa', 'price' => 138.40, 'status' => 'in_stock', 'stock_quantity' => 21, 'checked_days_ago' => 1],
            ['product_slug' => 'cafe-molido-400g', 'supermarket_slug' => 'mercado-express', 'section_name' => 'Bebidas', 'price' => 141.20, 'status' => 'in_stock', 'stock_quantity' => 18, 'checked_days_ago' => 2],
            ['product_slug' => 'jabon-liquido-ropa-3l', 'supermarket_slug' => 'supermercado-central', 'section_name' => 'Limpieza y Hogar', 'price' => 186.50, 'status' => 'in_stock', 'stock_quantity' => 44, 'checked_days_ago' => 2],
            ['product_slug' => 'jabon-liquido-ropa-3l', 'supermarket_slug' => 'mercado-express', 'section_name' => 'Limpieza y Cuidado del Hogar', 'price' => 191.00, 'status' => 'in_stock', 'stock_quantity' => 37, 'checked_days_ago' => 4],
        ];

        $inventoryItems = [];

        foreach ($inventoryData as $inventoryDatum) {
            $product = $products[$inventoryDatum['product_slug']] ?? null;
            $supermarketData = $supermarkets[$inventoryDatum['supermarket_slug']] ?? null;

            if ($product === null || $supermarketData === null) {
                continue;
            }

            $section = $supermarketData['sections'][$inventoryDatum['section_name']] ?? null;

            $inventoryItems[$inventoryDatum['product_slug'].'@'.$inventoryDatum['supermarket_slug']] = InventoryItem::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'supermarket_id' => $supermarketData['model']->id,
                ],
                [
                    'supermarket_section_id' => $section?->id,
                    'price' => $inventoryDatum['price'],
                    'currency' => 'MXN',
                    'availability_status' => $inventoryDatum['status'],
                    'stock_quantity' => $inventoryDatum['stock_quantity'],
                    'last_checked_at' => now()->subDays($inventoryDatum['checked_days_ago'])->startOfDay(),
                    'source' => 'community',
                ],
            );
        }

        return $inventoryItems;
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function seedUsers(): array
    {
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@marketgo.test'],
            [
                'name' => 'Admin MarketGo',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        $demoUser = User::updateOrCreate(
            ['email' => 'demo@marketgo.test'],
            [
                'name' => 'Usuario Demo',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ],
        );

        return [$adminUser, $demoUser];
    }

    /**
     * @param  User  $adminUser
     * @param  array<string, array{model: Supermarket, sections: array<string, SupermarketSection>}>  $supermarkets
     * @param  array<string, Product>  $products
     * @param  array<string, InventoryItem>  $inventoryItems
     */
    private function seedShoppingList(User $adminUser, array $supermarkets, array $products, array $inventoryItems): void
    {
        $supermarket = $supermarkets['supermercado-central']['model'] ?? null;

        if ($supermarket === null) {
            return;
        }

        $shoppingList = ShoppingList::updateOrCreate(
            [
                'user_id' => $adminUser->id,
                'name' => 'Compra semanal familiar',
            ],
            [
                'supermarket_id' => $supermarket->id,
                'status' => 'active',
                'budget' => 1200.00,
                'estimated_total' => 0,
                'planned_for' => now()->addDays(2)->startOfDay(),
                'notes' => 'Lista de demostración para recorrer el flujo de compra.',
            ],
        );

        $itemsData = [
            ['product_slug' => 'lechuga-romana-pieza', 'quantity' => 2, 'unit' => 'pieza'],
            ['product_slug' => 'manzana-gala-kilo', 'quantity' => 1.5, 'unit' => 'kg'],
            ['product_slug' => 'leche-entera-1l', 'quantity' => 3, 'unit' => 'pieza'],
            ['product_slug' => 'huevos-organicos-docena', 'quantity' => 1, 'unit' => 'paquete'],
            ['product_slug' => 'arroz-integral-1kg', 'quantity' => 1, 'unit' => 'paquete'],
            ['product_slug' => 'frijol-negro-900g', 'quantity' => 1, 'unit' => 'paquete'],
            ['product_slug' => 'cafe-molido-400g', 'quantity' => 1, 'unit' => 'paquete'],
            ['product_slug' => 'jabon-liquido-ropa-3l', 'quantity' => 1, 'unit' => 'botella'],
        ];

        $estimatedTotal = 0;

        foreach ($itemsData as $index => $itemData) {
            $product = $products[$itemData['product_slug']] ?? null;

            if ($product === null) {
                continue;
            }

            $inventoryItem = $inventoryItems[$itemData['product_slug'].'@supermercado-central'] ?? null;
            $estimatedPrice = $inventoryItem?->price !== null
                ? round($inventoryItem->price * $itemData['quantity'], 2)
                : null;

            $listItem = ShoppingListItem::updateOrCreate(
                [
                    'shopping_list_id' => $shoppingList->id,
                    'product_id' => $product->id,
                ],
                [
                    'inventory_item_id' => $inventoryItem?->id,
                    'supermarket_id' => $inventoryItem?->supermarket_id ?? $supermarket->id,
                    'supermarket_section_id' => $inventoryItem?->supermarket_section_id,
                    'quantity' => $itemData['quantity'],
                    'quantity_unit' => $itemData['unit'],
                    'status' => 'pending',
                    'estimated_price' => $estimatedPrice,
                    'position' => $index + 1,
                ],
            );

            if ($listItem->estimated_price !== null) {
                $estimatedTotal += (float) $listItem->estimated_price;
            }
        }

        $shoppingList->update(['estimated_total' => round($estimatedTotal, 2)]);
    }

    /**
     * @param  User  $adminUser
     * @param  array<string, array{model: Supermarket, sections: array<string, SupermarketSection>}>  $supermarkets
     * @param  array<string, Product>  $products
     */
    private function seedConsumptionLog(User $adminUser, array $supermarkets, array $products): void
    {
        $product = $products['leche-entera-1l'] ?? null;
        $supermarket = $supermarkets['supermercado-central']['model'] ?? null;

        if ($product === null || $supermarket === null) {
            return;
        }

        $consumedAt = now()->subDays(10)->startOfDay();

        ConsumptionLog::updateOrCreate(
            [
                'user_id' => $adminUser->id,
                'product_id' => $product->id,
                'consumed_at' => $consumedAt,
            ],
            [
                'supermarket_id' => $supermarket->id,
                'quantity' => 2,
                'quantity_unit' => 'pieza',
                'price' => 55.80,
                'next_restock_at' => $consumedAt->copy()->addDays(14),
                'notes' => 'Consumo semanal estimado para activar recordatorios.',
            ],
        );
    }

    /**
     * @param  User  $adminUser
     * @param  User  $demoUser
     * @param  array<string, Product>  $products
     * @param  array<string, array{model: Supermarket, sections: array<string, SupermarketSection>}>  $supermarkets
     */
    private function seedContributions(User $adminUser, User $demoUser, array $products, array $supermarkets): void
    {
        $coffeeProduct = $products['cafe-molido-400g'] ?? null;
        $expressSupermarket = $supermarkets['mercado-express']['model'] ?? null;

        if ($coffeeProduct !== null && $expressSupermarket !== null) {
            Contribution::updateOrCreate(
                [
                    'user_id' => $demoUser->id,
                    'type' => 'price_update',
                ],
                [
                    'payload' => [
                        'product_slug' => $coffeeProduct->slug,
                        'supermarket_slug' => $expressSupermarket->slug,
                        'price' => 135.90,
                        'currency' => 'MXN',
                        'observations' => 'Precio verificado en góndola de bebidas.',
                    ],
                    'status' => 'approved',
                    'reviewed_by' => $adminUser->id,
                    'reviewed_at' => now()->subDay(),
                    'review_notes' => 'Datos confirmados por el equipo de curación.',
                ],
            );
        }

        $lettuceProduct = $products['lechuga-romana-pieza'] ?? null;

        if ($lettuceProduct !== null) {
            Contribution::updateOrCreate(
                [
                    'user_id' => $demoUser->id,
                    'type' => 'stock_report',
                ],
                [
                    'payload' => [
                        'product_slug' => $lettuceProduct->slug,
                        'status' => 'out_of_stock',
                        'supermarket_slug' => 'mercado-express',
                        'comment' => 'Última visita sin existencias en la mañana.',
                    ],
                    'status' => 'pending',
                ],
            );
        }
    }
}
