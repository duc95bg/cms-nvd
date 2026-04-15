<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Seed the templates table with the default `product` template.
     */
    public function run(): void
    {
        Template::updateOrCreate(
            ['type' => 'product'],
            [
                'name' => 'Product Landing',
                'view' => 'templates.product',
                'default_content' => [
                    'brand' => [
                        'name' => [
                            'en' => 'Acme Studio',
                            'vi' => 'Acme Studio',
                        ],
                    ],
                    'seo' => [
                        'title' => [
                            'en' => 'Acme Studio — Launch your product with confidence',
                            'vi' => 'Acme Studio — Ra mắt sản phẩm của bạn một cách tự tin',
                        ],
                        'description' => [
                            'en' => 'A modern landing page template to showcase your product, attract customers, and grow your business.',
                            'vi' => 'Mẫu trang đích hiện đại giúp giới thiệu sản phẩm, thu hút khách hàng và phát triển doanh nghiệp của bạn.',
                        ],
                    ],
                    'hero' => [
                        'title' => [
                            'en' => 'Your product, beautifully launched',
                            'vi' => 'Ra mắt sản phẩm của bạn thật đẹp',
                        ],
                        'subtitle' => [
                            'en' => 'Everything you need to turn visitors into customers — fast, flexible, and built for growth.',
                            'vi' => 'Tất cả những gì bạn cần để biến khách truy cập thành khách hàng — nhanh chóng, linh hoạt và sẵn sàng tăng trưởng.',
                        ],
                        'cta_label' => [
                            'en' => 'Get started',
                            'vi' => 'Bắt đầu ngay',
                        ],
                        'cta_url' => '#features',
                    ],
                    'features' => [
                        'heading' => [
                            'en' => 'Why choose us',
                            'vi' => 'Vì sao chọn chúng tôi',
                        ],
                        'items' => [
                            [
                                'title' => [
                                    'en' => 'Blazing fast performance',
                                    'vi' => 'Hiệu năng cực nhanh',
                                ],
                                'body' => [
                                    'en' => 'Optimized from the ground up so your pages load in milliseconds and rank higher on search engines.',
                                    'vi' => 'Được tối ưu từ gốc để trang của bạn tải trong vài mili giây và xếp hạng cao hơn trên công cụ tìm kiếm.',
                                ],
                            ],
                            [
                                'title' => [
                                    'en' => 'Effortless customization',
                                    'vi' => 'Tùy chỉnh dễ dàng',
                                ],
                                'body' => [
                                    'en' => 'Edit every section from a friendly admin panel — no code required to change copy, images, or colors.',
                                    'vi' => 'Chỉnh sửa mọi phần từ bảng quản trị thân thiện — không cần viết code để thay đổi nội dung, hình ảnh hay màu sắc.',
                                ],
                            ],
                            [
                                'title' => [
                                    'en' => 'Built for conversions',
                                    'vi' => 'Thiết kế để chuyển đổi',
                                ],
                                'body' => [
                                    'en' => 'Clear calls to action, trust signals, and proven layouts that guide visitors toward becoming paying customers.',
                                    'vi' => 'Lời kêu gọi hành động rõ ràng, dấu hiệu tin cậy và bố cục đã được kiểm chứng giúp khách truy cập trở thành khách hàng trả phí.',
                                ],
                            ],
                        ],
                    ],
                    'pricing' => [
                        'heading' => [
                            'en' => 'Simple, transparent pricing',
                            'vi' => 'Giá cả đơn giản, minh bạch',
                        ],
                        'subheading' => [
                            'en' => 'Start free, upgrade when you are ready. No hidden fees, cancel any time.',
                            'vi' => 'Bắt đầu miễn phí, nâng cấp khi bạn sẵn sàng. Không phí ẩn, hủy bất cứ lúc nào.',
                        ],
                        'cta_label' => [
                            'en' => 'View plans',
                            'vi' => 'Xem các gói',
                        ],
                        'cta_url' => '#pricing',
                    ],
                ],
            ]
        );
    }
}
