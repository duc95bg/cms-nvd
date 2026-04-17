<?php

return [
    'hero' => [
        'label' => ['vi' => 'Banner chính', 'en' => 'Hero Banner'],
        'icon' => '🖼️',
        'fields' => [
            ['name' => 'title', 'type' => 'text_i18n'],
            ['name' => 'subtitle', 'type' => 'text_i18n'],
            ['name' => 'cta_label', 'type' => 'text_i18n'],
            ['name' => 'cta_url', 'type' => 'text'],
            ['name' => 'background_image', 'type' => 'image'],
        ],
        'default_content' => [
            'title' => ['vi' => 'Tiêu đề chính', 'en' => 'Main Heading'],
            'subtitle' => ['vi' => 'Mô tả ngắn', 'en' => 'Short description'],
            'cta_label' => ['vi' => 'Tìm hiểu thêm', 'en' => 'Learn More'],
            'cta_url' => '#',
            'background_image' => '',
        ],
    ],

    'text' => [
        'label' => ['vi' => 'Văn bản', 'en' => 'Text'],
        'icon' => '📝',
        'fields' => [
            ['name' => 'heading', 'type' => 'text_i18n'],
            ['name' => 'body', 'type' => 'textarea_i18n'],
        ],
        'default_content' => [
            'heading' => ['vi' => 'Tiêu đề', 'en' => 'Heading'],
            'body' => ['vi' => 'Nội dung văn bản', 'en' => 'Text content goes here'],
        ],
    ],

    'image' => [
        'label' => ['vi' => 'Hình ảnh', 'en' => 'Image'],
        'icon' => '🏞️',
        'fields' => [
            ['name' => 'src', 'type' => 'image'],
            ['name' => 'alt', 'type' => 'text_i18n'],
            ['name' => 'caption', 'type' => 'text_i18n'],
        ],
        'default_content' => [
            'src' => '',
            'alt' => ['vi' => '', 'en' => ''],
            'caption' => ['vi' => '', 'en' => ''],
        ],
    ],

    'gallery' => [
        'label' => ['vi' => 'Bộ sưu tập ảnh', 'en' => 'Gallery'],
        'icon' => '🖼️',
        'fields' => [
            ['name' => 'images', 'type' => 'repeater', 'fields' => [
                ['name' => 'src', 'type' => 'image'],
                ['name' => 'alt', 'type' => 'text_i18n'],
            ]],
        ],
        'default_content' => [
            'images' => [],
        ],
    ],

    'features' => [
        'label' => ['vi' => 'Tính năng', 'en' => 'Features'],
        'icon' => '⭐',
        'fields' => [
            ['name' => 'heading', 'type' => 'text_i18n'],
            ['name' => 'items', 'type' => 'repeater', 'fields' => [
                ['name' => 'icon', 'type' => 'text'],
                ['name' => 'title', 'type' => 'text_i18n'],
                ['name' => 'body', 'type' => 'textarea_i18n'],
            ]],
        ],
        'default_content' => [
            'heading' => ['vi' => 'Tính năng nổi bật', 'en' => 'Key Features'],
            'items' => [
                ['icon' => '🚀', 'title' => ['vi' => 'Nhanh chóng', 'en' => 'Fast'], 'body' => ['vi' => 'Tốc độ xử lý nhanh', 'en' => 'Lightning fast processing']],
                ['icon' => '🔒', 'title' => ['vi' => 'An toàn', 'en' => 'Secure'], 'body' => ['vi' => 'Bảo mật cao', 'en' => 'Enterprise-grade security']],
                ['icon' => '💡', 'title' => ['vi' => 'Thông minh', 'en' => 'Smart'], 'body' => ['vi' => 'Giải pháp thông minh', 'en' => 'Intelligent solutions']],
            ],
        ],
    ],

    'products' => [
        'label' => ['vi' => 'Sản phẩm', 'en' => 'Products Showcase'],
        'icon' => '🛍️',
        'fields' => [
            ['name' => 'heading', 'type' => 'text_i18n'],
            ['name' => 'category_id', 'type' => 'select'],
            ['name' => 'count', 'type' => 'number'],
        ],
        'default_content' => [
            'heading' => ['vi' => 'Sản phẩm nổi bật', 'en' => 'Featured Products'],
            'category_id' => null,
            'count' => 4,
        ],
    ],

    'about' => [
        'label' => ['vi' => 'Giới thiệu', 'en' => 'About'],
        'icon' => 'ℹ️',
        'fields' => [
            ['name' => 'title', 'type' => 'text_i18n'],
            ['name' => 'body', 'type' => 'textarea_i18n'],
            ['name' => 'image', 'type' => 'image'],
        ],
        'default_content' => [
            'title' => ['vi' => 'Về chúng tôi', 'en' => 'About Us'],
            'body' => ['vi' => 'Giới thiệu về công ty', 'en' => 'Company introduction'],
            'image' => '',
        ],
    ],

    'contact' => [
        'label' => ['vi' => 'Liên hệ', 'en' => 'Contact'],
        'icon' => '📞',
        'fields' => [
            ['name' => 'heading', 'type' => 'text_i18n'],
            ['name' => 'email', 'type' => 'text'],
            ['name' => 'phone', 'type' => 'text'],
            ['name' => 'address', 'type' => 'text_i18n'],
        ],
        'default_content' => [
            'heading' => ['vi' => 'Liên hệ', 'en' => 'Contact Us'],
            'email' => 'info@example.com',
            'phone' => '+84 123 456 789',
            'address' => ['vi' => 'TP. Hồ Chí Minh, Việt Nam', 'en' => 'Ho Chi Minh City, Vietnam'],
        ],
    ],

    'cta' => [
        'label' => ['vi' => 'Kêu gọi hành động', 'en' => 'Call to Action'],
        'icon' => '📢',
        'fields' => [
            ['name' => 'title', 'type' => 'text_i18n'],
            ['name' => 'description', 'type' => 'textarea_i18n'],
            ['name' => 'button_label', 'type' => 'text_i18n'],
            ['name' => 'button_url', 'type' => 'text'],
        ],
        'default_content' => [
            'title' => ['vi' => 'Bắt đầu ngay', 'en' => 'Get Started'],
            'description' => ['vi' => 'Liên hệ ngay hôm nay', 'en' => 'Contact us today'],
            'button_label' => ['vi' => 'Liên hệ', 'en' => 'Contact'],
            'button_url' => '#contact',
        ],
    ],

    'spacer' => [
        'label' => ['vi' => 'Khoảng cách', 'en' => 'Spacer'],
        'icon' => '↕️',
        'fields' => [
            ['name' => 'height', 'type' => 'number'],
        ],
        'default_content' => [
            'height' => 48,
        ],
    ],

    'html' => [
        'label' => ['vi' => 'HTML tùy chỉnh', 'en' => 'Raw HTML'],
        'icon' => '💻',
        'fields' => [
            ['name' => 'content', 'type' => 'textarea'],
        ],
        'default_content' => [
            'content' => '',
        ],
    ],
];
