<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        Theme::updateOrCreate(['slug' => 'landing-product'], [
            'name' => ['vi' => 'Landing bán hàng', 'en' => 'Product Landing'],
            'description' => ['vi' => 'Trang giới thiệu sản phẩm với banner, tính năng, sản phẩm nổi bật và CTA', 'en' => 'Product landing page with hero banner, features, product showcase and CTA'],
            'status' => 'active',
            'blocks_preset' => [
                ['id' => 'preset_hero', 'type' => 'hero', 'order' => 0, 'content' => [
                    'title' => ['vi' => 'Sản phẩm chất lượng cao', 'en' => 'Premium Quality Products'],
                    'subtitle' => ['vi' => 'Khám phá bộ sưu tập mới nhất của chúng tôi', 'en' => 'Discover our latest collection'],
                    'cta_label' => ['vi' => 'Mua ngay', 'en' => 'Shop Now'],
                    'cta_url' => '#products',
                    'background_image' => '',
                ]],
                ['id' => 'preset_features', 'type' => 'features', 'order' => 1, 'content' => [
                    'heading' => ['vi' => 'Tại sao chọn chúng tôi', 'en' => 'Why Choose Us'],
                    'items' => [
                        ['icon' => '🚚', 'title' => ['vi' => 'Giao hàng nhanh', 'en' => 'Fast Delivery'], 'body' => ['vi' => 'Giao hàng toàn quốc trong 2-3 ngày', 'en' => 'Nationwide delivery in 2-3 days']],
                        ['icon' => '💯', 'title' => ['vi' => 'Chất lượng đảm bảo', 'en' => 'Quality Guarantee'], 'body' => ['vi' => 'Cam kết hàng chính hãng 100%', 'en' => '100% authentic products guaranteed']],
                        ['icon' => '🔄', 'title' => ['vi' => 'Đổi trả dễ dàng', 'en' => 'Easy Returns'], 'body' => ['vi' => 'Đổi trả miễn phí trong 30 ngày', 'en' => 'Free returns within 30 days']],
                    ],
                ]],
                ['id' => 'preset_products', 'type' => 'products', 'order' => 2, 'content' => [
                    'heading' => ['vi' => 'Sản phẩm nổi bật', 'en' => 'Featured Products'],
                    'category_id' => null,
                    'count' => 4,
                ]],
                ['id' => 'preset_cta', 'type' => 'cta', 'order' => 3, 'content' => [
                    'title' => ['vi' => 'Bắt đầu mua sắm ngay', 'en' => 'Start Shopping Now'],
                    'description' => ['vi' => 'Ưu đãi đặc biệt cho khách hàng mới', 'en' => 'Special offers for new customers'],
                    'button_label' => ['vi' => 'Xem tất cả sản phẩm', 'en' => 'View All Products'],
                    'button_url' => '/vi/products',
                ]],
                ['id' => 'preset_contact', 'type' => 'contact', 'order' => 4, 'content' => [
                    'heading' => ['vi' => 'Liên hệ', 'en' => 'Contact Us'],
                    'email' => 'info@example.com',
                    'phone' => '+84 123 456 789',
                    'address' => ['vi' => 'TP. Hồ Chí Minh, Việt Nam', 'en' => 'Ho Chi Minh City, Vietnam'],
                ]],
            ],
        ]);

        Theme::updateOrCreate(['slug' => 'landing-service'], [
            'name' => ['vi' => 'Landing dịch vụ', 'en' => 'Service Landing'],
            'description' => ['vi' => 'Trang giới thiệu dịch vụ với about, danh sách dịch vụ và form liên hệ', 'en' => 'Service landing page with about section, service list and contact form'],
            'status' => 'active',
            'blocks_preset' => [
                ['id' => 'preset_s_hero', 'type' => 'hero', 'order' => 0, 'content' => [
                    'title' => ['vi' => 'Dịch vụ chuyên nghiệp', 'en' => 'Professional Services'],
                    'subtitle' => ['vi' => 'Giải pháp toàn diện cho doanh nghiệp', 'en' => 'Comprehensive solutions for your business'],
                    'cta_label' => ['vi' => 'Tìm hiểu thêm', 'en' => 'Learn More'],
                    'cta_url' => '#about',
                    'background_image' => '',
                ]],
                ['id' => 'preset_s_about', 'type' => 'about', 'order' => 1, 'content' => [
                    'title' => ['vi' => 'Về chúng tôi', 'en' => 'About Us'],
                    'body' => ['vi' => 'Chúng tôi là đội ngũ chuyên gia với hơn 10 năm kinh nghiệm.', 'en' => 'We are a team of experts with over 10 years of experience.'],
                    'image' => '',
                ]],
                ['id' => 'preset_s_features', 'type' => 'features', 'order' => 2, 'content' => [
                    'heading' => ['vi' => 'Dịch vụ của chúng tôi', 'en' => 'Our Services'],
                    'items' => [
                        ['icon' => '💼', 'title' => ['vi' => 'Tư vấn', 'en' => 'Consulting'], 'body' => ['vi' => 'Tư vấn chiến lược kinh doanh', 'en' => 'Business strategy consulting']],
                        ['icon' => '🎨', 'title' => ['vi' => 'Thiết kế', 'en' => 'Design'], 'body' => ['vi' => 'Thiết kế UI/UX chuyên nghiệp', 'en' => 'Professional UI/UX design']],
                        ['icon' => '⚙️', 'title' => ['vi' => 'Phát triển', 'en' => 'Development'], 'body' => ['vi' => 'Phát triển web và mobile', 'en' => 'Web and mobile development']],
                    ],
                ]],
                ['id' => 'preset_s_contact', 'type' => 'contact', 'order' => 3, 'content' => [
                    'heading' => ['vi' => 'Liên hệ ngay', 'en' => 'Get In Touch'],
                    'email' => 'contact@example.com',
                    'phone' => '+84 987 654 321',
                    'address' => ['vi' => 'Hà Nội, Việt Nam', 'en' => 'Hanoi, Vietnam'],
                ]],
                ['id' => 'preset_s_cta', 'type' => 'cta', 'order' => 4, 'content' => [
                    'title' => ['vi' => 'Sẵn sàng hợp tác?', 'en' => 'Ready to Collaborate?'],
                    'description' => ['vi' => 'Liên hệ để nhận báo giá miễn phí', 'en' => 'Contact us for a free quote'],
                    'button_label' => ['vi' => 'Gửi yêu cầu', 'en' => 'Send Request'],
                    'button_url' => '#contact',
                ]],
            ],
        ]);

        Theme::updateOrCreate(['slug' => 'landing-blog'], [
            'name' => ['vi' => 'Landing blog', 'en' => 'Blog Landing'],
            'description' => ['vi' => 'Trang landing đơn giản với nội dung bài viết và hình ảnh', 'en' => 'Simple blog-style landing with content and gallery'],
            'status' => 'active',
            'blocks_preset' => [
                ['id' => 'preset_b_hero', 'type' => 'hero', 'order' => 0, 'content' => [
                    'title' => ['vi' => 'Blog & Tin tức', 'en' => 'Blog & News'],
                    'subtitle' => ['vi' => 'Cập nhật thông tin mới nhất', 'en' => 'Stay updated with the latest news'],
                    'cta_label' => ['vi' => 'Đọc ngay', 'en' => 'Read Now'],
                    'cta_url' => '#content',
                    'background_image' => '',
                ]],
                ['id' => 'preset_b_text', 'type' => 'text', 'order' => 1, 'content' => [
                    'heading' => ['vi' => 'Chào mừng', 'en' => 'Welcome'],
                    'body' => ['vi' => 'Chào mừng bạn đến với blog của chúng tôi. Nơi chia sẻ kiến thức và kinh nghiệm.', 'en' => 'Welcome to our blog. A place to share knowledge and experience.'],
                ]],
                ['id' => 'preset_b_gallery', 'type' => 'gallery', 'order' => 2, 'content' => [
                    'images' => [],
                ]],
                ['id' => 'preset_b_cta', 'type' => 'cta', 'order' => 3, 'content' => [
                    'title' => ['vi' => 'Theo dõi chúng tôi', 'en' => 'Follow Us'],
                    'description' => ['vi' => 'Đăng ký để nhận tin mới nhất', 'en' => 'Subscribe for the latest updates'],
                    'button_label' => ['vi' => 'Đăng ký', 'en' => 'Subscribe'],
                    'button_url' => '#',
                ]],
            ],
        ]);
    }
}
