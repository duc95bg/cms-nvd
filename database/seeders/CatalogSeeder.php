<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $aoThun = Category::updateOrCreate(
            ['slug' => 'ao-thun'],
            [
                'name' => ['vi' => 'Áo thun', 'en' => 'T-shirt'],
                'parent_id' => null,
                'status' => 'active',
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'quan'],
            [
                'name' => ['vi' => 'Quần', 'en' => 'Pants'],
                'parent_id' => null,
                'status' => 'active',
                'sort_order' => 2,
            ]
        );

        // 2. Attributes
        $sizeAttr = Attribute::firstOrCreate(
            ['name->vi' => 'Kích thước'],
            [
                'name' => ['vi' => 'Kích thước', 'en' => 'Size'],
                'type' => 'select',
            ]
        );

        $colorAttr = Attribute::firstOrCreate(
            ['name->vi' => 'Màu sắc'],
            [
                'name' => ['vi' => 'Màu sắc', 'en' => 'Color'],
                'type' => 'color',
            ]
        );

        // Attribute values - Size
        $sizeValues = [];
        foreach ([
            ['vi' => 'S', 'en' => 'S'],
            ['vi' => 'M', 'en' => 'M'],
            ['vi' => 'L', 'en' => 'L'],
            ['vi' => 'XL', 'en' => 'XL'],
        ] as $index => $value) {
            $sizeValues[$value['vi']] = AttributeValue::firstOrCreate(
                ['attribute_id' => $sizeAttr->id, 'value->vi' => $value['vi']],
                [
                    'attribute_id' => $sizeAttr->id,
                    'value' => $value,
                    'sort_order' => $index + 1,
                ]
            );
        }

        // Attribute values - Color
        $colorValues = [];
        foreach ([
            ['vi' => 'Đỏ', 'en' => 'Red', 'sku' => 'DO'],
            ['vi' => 'Xanh', 'en' => 'Blue', 'sku' => 'XANH'],
            ['vi' => 'Vàng', 'en' => 'Yellow', 'sku' => 'VANG'],
        ] as $index => $data) {
            $colorValues[$data['vi']] = AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value->vi' => $data['vi']],
                [
                    'attribute_id' => $colorAttr->id,
                    'value' => ['vi' => $data['vi'], 'en' => $data['en']],
                    'sort_order' => $index + 1,
                ]
            );
            $colorValues[$data['vi']]->skuCode = $data['sku'];
        }

        // 3. Products
        $products = [];

        $products[] = Product::updateOrCreate(
            ['slug' => 'ao-thun-co-tron'],
            [
                'category_id' => $aoThun->id,
                'name' => ['vi' => 'Áo thun cổ tròn', 'en' => 'Round neck T-shirt'],
                'slug' => 'ao-thun-co-tron',
                'description' => ['vi' => 'Áo thun cổ tròn chất liệu cotton', 'en' => 'Round neck cotton T-shirt'],
                'short_description' => ['vi' => 'Áo thun cổ tròn', 'en' => 'Round neck T-shirt'],
                'base_price' => 150000,
                'status' => 'active',
                'featured' => false,
                'sort_order' => 1,
            ]
        );

        $products[] = Product::updateOrCreate(
            ['slug' => 'ao-thun-co-v'],
            [
                'category_id' => $aoThun->id,
                'name' => ['vi' => 'Áo thun cổ V', 'en' => 'V-neck T-shirt'],
                'slug' => 'ao-thun-co-v',
                'description' => ['vi' => 'Áo thun cổ V thời trang', 'en' => 'Fashionable V-neck T-shirt'],
                'short_description' => ['vi' => 'Áo thun cổ V', 'en' => 'V-neck T-shirt'],
                'base_price' => 180000,
                'status' => 'active',
                'featured' => false,
                'sort_order' => 2,
            ]
        );

        $products[] = Product::updateOrCreate(
            ['slug' => 'ao-thun-oversize'],
            [
                'category_id' => $aoThun->id,
                'name' => ['vi' => 'Áo thun oversize', 'en' => 'Oversize T-shirt'],
                'slug' => 'ao-thun-oversize',
                'description' => ['vi' => 'Áo thun oversize phong cách', 'en' => 'Stylish oversize T-shirt'],
                'short_description' => ['vi' => 'Áo thun oversize', 'en' => 'Oversize T-shirt'],
                'base_price' => 200000,
                'status' => 'active',
                'featured' => true,
                'sort_order' => 3,
            ]
        );

        // 4. Attach attributes to all products
        foreach ($products as $product) {
            $product->attributes()->syncWithoutDetaching([$sizeAttr->id, $colorAttr->id]);
        }

        // 5. Variants for product 1 (ao-thun-co-tron): Size × Color = 12 variants
        $product1 = $products[0];
        $colorSkuMap = ['Đỏ' => 'DO', 'Xanh' => 'XANH', 'Vàng' => 'VANG'];

        foreach ($sizeValues as $sizeName => $sizeVal) {
            foreach ($colorValues as $colorName => $colorVal) {
                $sku = "ATCT-{$sizeName}-{$colorSkuMap[$colorName]}";
                $variant = ProductVariant::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'product_id' => $product1->id,
                        'price' => null,
                        'stock' => rand(5, 50),
                        'status' => 'active',
                    ]
                );
                $variant->attributeValues()->syncWithoutDetaching([
                    $sizeVal->id,
                    $colorVal->id,
                ]);
            }
        }

        // 6. Sample variants for product 2 (ao-thun-co-v)
        $product2 = $products[1];
        $sampleVariants2 = [
            ['sku' => 'ATCV-M-DO', 'size' => 'M', 'color' => 'Đỏ'],
            ['sku' => 'ATCV-L-XANH', 'size' => 'L', 'color' => 'Xanh'],
            ['sku' => 'ATCV-XL-VANG', 'size' => 'XL', 'color' => 'Vàng'],
        ];

        foreach ($sampleVariants2 as $sv) {
            $variant = ProductVariant::updateOrCreate(
                ['sku' => $sv['sku']],
                [
                    'product_id' => $product2->id,
                    'price' => null,
                    'stock' => rand(5, 50),
                    'status' => 'active',
                ]
            );
            $variant->attributeValues()->syncWithoutDetaching([
                $sizeValues[$sv['size']]->id,
                $colorValues[$sv['color']]->id,
            ]);
        }

        // Sample variants for product 3 (ao-thun-oversize)
        $product3 = $products[2];
        $sampleVariants3 = [
            ['sku' => 'ATOS-L-DO', 'size' => 'L', 'color' => 'Đỏ'],
            ['sku' => 'ATOS-XL-XANH', 'size' => 'XL', 'color' => 'Xanh'],
        ];

        foreach ($sampleVariants3 as $sv) {
            $variant = ProductVariant::updateOrCreate(
                ['sku' => $sv['sku']],
                [
                    'product_id' => $product3->id,
                    'price' => null,
                    'stock' => rand(5, 50),
                    'status' => 'active',
                ]
            );
            $variant->attributeValues()->syncWithoutDetaching([
                $sizeValues[$sv['size']]->id,
                $colorValues[$sv['color']]->id,
            ]);
        }
    }
}
