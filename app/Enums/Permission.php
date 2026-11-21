<?php

namespace App\Enums;

enum Permission: string
{
    // --- 1. SẢN PHẨM ---
    case PRODUCT_EDIT_PRICE = 'product_edit_price';
    case PRODUCT_EDIT_SPEC = 'product_edit_spec';
    case PRODUCT_EDIT_URL = 'product_edit_url';
    case PRODUCT_DELETE = 'product_delete';
    case PRODUCT_EDIT_ALL_EXCEPT_URL = 'product_edit_all_except_url';
    case PRODUCT_CREATE = 'product_create';
    case PRODUCT_VIEW_DETAIL = 'product_view_detail';
    case PRODUCT_VIEW_LIST = 'product_view_list';

    // Thêm các quyền Bình luận sản phẩm
    case COMMENT_MANAGEMENT = 'comment_management';

    // --- 2. TRANG TĨNH ---
    case PAGE_EDIT_URL = 'page_edit_url';
    case PAGE_DELETE = 'page_delete';
    case PAGE_EDIT_ALL_EXCEPT_URL = 'page_edit_all_except_url';
    case PAGE_CREATE = 'page_create';
    case PAGE_VIEW_DETAIL = 'page_view_detail';
    case PAGE_VIEW_LIST = 'page_view_list';

    // --- 3. DANH MỤC BÀI VIẾT ---
    case POST_CATEGORY_EDIT_URL = 'post_category_edit_url';
    case POST_CATEGORY_DELETE = 'post_category_delete';
    case POST_CATEGORY_EDIT_ALL_EXCEPT_URL = 'post_category_edit_all_except_url';
    case POST_CATEGORY_CREATE = 'post_category_create';
    case POST_CATEGORY_VIEW_DETAIL = 'post_category_view_detail';
    case POST_CATEGORY_VIEW_LIST = 'post_category_view_list';

    // --- 4. BÀI VIẾT ---
    case POST_EDIT_URL = 'post_edit_url';
    case POST_DELETE = 'post_delete';
    case POST_EDIT_ALL_EXCEPT_URL = 'post_edit_all_except_url';
    case POST_CREATE = 'post_create';
    case POST_VIEW_DETAIL = 'post_view_detail';
    case POST_VIEW_LIST = 'post_view_list';

    // --- 5. DANH MỤC SẢN PHẨM ---
    case CATEGORY_PRODUCT_EDIT_URL = 'category_product_edit_url';
    case CATEGORY_PRODUCT_DELETE = 'category_product_delete';
    case CATEGORY_PRODUCT_EDIT_ALL_EXCEPT_URL = 'category_product_edit_all_except_url';
    case CATEGORY_PRODUCT_CREATE = 'category_product_create';
    case CATEGORY_PRODUCT_VIEW_DETAIL = 'category_product_view_detail';
    case CATEGORY_PRODUCT_VIEW_LIST = 'category_product_view_list';

    // --- 6. THÔNG TIN WEBSITE ---
    case SYSTEM_INFO_EDIT = 'system_info_edit';
    case SYSTEM_INFO_VIEW = 'system_info_view';

    // --- 7. CẤU HÌNH ---
    case SETTING_DELETE = 'setting_delete';
    case SETTING_EDIT_ALL_EXCEPT_URL = 'setting_edit_all_except_url';
    case SETTING_CREATE = 'setting_create';
    case SETTING_VIEW_DETAIL = 'setting_view_detail';
    case SETTING_VIEW_LIST = 'setting_view_list';

    // --- 8. LỊCH SỬ CHỈNH SỬA (Gộp chung) ---
    case HISTORY_VIEW = 'history_view';

    // --- 9. CHI TIẾT ĐƠN HÀNG & ĐƠN HÀNG ---
    case ORDER_DETAIL_DELETE = 'order_detail_delete';
    case ORDER_DETAIL_EDIT_EXCEPT_URL = 'order_detail_edit_except_url';
    case ORDER_DETAIL_CREATE = 'order_detail_create';
    case ORDER_DETAIL_VIEW = 'order_detail_view';
    case ORDER_DETAIL_LIST = 'order_detail_list';
    
    case ORDER_DELETE = 'order_delete';
    case ORDER_EDIT_ALL_EXCEPT_URL = 'order_edit_all_except_url';
    case ORDER_CREATE = 'order_create';
    case ORDER_VIEW_DETAIL = 'order_view_detail';
    case ORDER_VIEW_LIST = 'order_view_list';

    // --- 10. LIÊN HỆ ---
    case CONTACT_DELETE = 'contact_delete';
    case CONTACT_EDIT_ALL_EXCEPT_URL = 'contact_edit_all_except_url';
    case CONTACT_CREATE = 'contact_create';
    case CONTACT_VIEW_DETAIL = 'contact_view_detail';
    case CONTACT_VIEW_LIST = 'contact_view_list';

    // --- 11. THƯƠNG HIỆU ---
    case BRAND_DELETE = 'brand_delete';
    case BRAND_EDIT_ALL_EXCEPT_URL = 'brand_edit_all_except_url';
    case BRAND_CREATE = 'brand_create';
    case BRAND_VIEW_DETAIL = 'brand_view_detail';
    case BRAND_VIEW_LIST = 'brand_view_list';

    // --- 12. BANNER ---
    case BANNER_DELETE = 'banner_delete';
    case BANNER_EDIT_ALL_EXCEPT_URL = 'banner_edit_all_except_url';
    case BANNER_CREATE = 'banner_create';
    case BANNER_VIEW_DETAIL = 'banner_view_detail';
    case BANNER_VIEW_LIST = 'banner_view_list';

    // --- 13. GIÁ TRỊ THUỘC TÍNH ---
    case ATTRIBUTE_VALUE_DELETE = 'attribute_value_delete';
    case ATTRIBUTE_VALUE_EDIT_ALL_EXCEPT_URL = 'attribute_value_edit_all_except_url';
    case ATTRIBUTE_VALUE_CREATE = 'attribute_value_create';
    case ATTRIBUTE_VALUE_VIEW_DETAIL = 'attribute_value_view_detail';
    case ATTRIBUTE_VALUE_VIEW_LIST = 'attribute_value_view_list';

    // --- 14. ĐÁNH GIÁ (REVIEW) ---
    case REVIEW_DELETE = 'review_delete';
    case REVIEW_EDIT_ALL_EXCEPT_URL = 'review_edit_all_except_url';
    case REVIEW_CREATE = 'review_create';
    case REVIEW_VIEW_DETAIL = 'review_view_detail';
    case REVIEW_VIEW_LIST = 'review_view_list';

    // --- 15. VIDEO ---
    case VIDEO_DELETE = 'video_delete';
    case VIDEO_EDIT_ALL_EXCEPT_URL = 'video_edit_all_except_url';
    case VIDEO_CREATE = 'video_create';
    case VIDEO_VIEW_DETAIL = 'video_view_detail'; // hoặc case VIDEO_VIEW_DETAIL = 'video_view_detail';
    case VIDEO_VIEW_LIST = 'video_view_list';

    // --- 16. EMAIL ĐĂNG KÝ NHẬN TIN ---
    case EMAIL_SUBSCRIBER_DELETE = 'email_subscriber_delete';
    case EMAIL_SUBSCRIBER_VIEW_LIST = 'email_subscriber_view_list';

    public function label(): string
    {
        return match($this) {
            // Sản phẩm
            static::PRODUCT_EDIT_PRICE => 'Sửa giá',
            static::PRODUCT_EDIT_SPEC => 'Sửa thông số kỹ thuật',
            static::PRODUCT_EDIT_URL => 'Sửa url',
            static::PRODUCT_DELETE => 'Xoá',
            static::PRODUCT_EDIT_ALL_EXCEPT_URL => 'Quyền sửa tất cả trừ url',
            static::PRODUCT_CREATE => 'Thêm mới',
            static::PRODUCT_VIEW_DETAIL => 'Xem chi tiết',
            static::PRODUCT_VIEW_LIST => 'Xem danh sách',

            static::COMMENT_MANAGEMENT => 'Quản lý bình luận',

            // Sửa URL chung
            static::PAGE_EDIT_URL, 
            static::POST_CATEGORY_EDIT_URL, 
            static::POST_EDIT_URL, 
            static::CATEGORY_PRODUCT_EDIT_URL => 'Sửa url',
            
            // Xóa chung
            static::PAGE_DELETE, static::POST_CATEGORY_DELETE, static::POST_DELETE, static::CATEGORY_PRODUCT_DELETE, 
            static::SETTING_DELETE, static::ORDER_DETAIL_DELETE, 
            static::ORDER_DELETE, static::CONTACT_DELETE, static::BRAND_DELETE, 
            static::BANNER_DELETE, static::ATTRIBUTE_VALUE_DELETE,
            static::REVIEW_DELETE, static::VIDEO_DELETE, static::EMAIL_SUBSCRIBER_DELETE => 'Xoá',

            // Sửa tất cả trừ url chung
            static::PAGE_EDIT_ALL_EXCEPT_URL, static::POST_CATEGORY_EDIT_ALL_EXCEPT_URL, static::POST_EDIT_ALL_EXCEPT_URL, 
            static::CATEGORY_PRODUCT_EDIT_ALL_EXCEPT_URL, static::SETTING_EDIT_ALL_EXCEPT_URL, static::ORDER_DETAIL_EDIT_EXCEPT_URL, 
            static::ORDER_EDIT_ALL_EXCEPT_URL, static::CONTACT_EDIT_ALL_EXCEPT_URL, static::BRAND_EDIT_ALL_EXCEPT_URL, 
            static::BANNER_EDIT_ALL_EXCEPT_URL, static::ATTRIBUTE_VALUE_EDIT_ALL_EXCEPT_URL,
            static::REVIEW_EDIT_ALL_EXCEPT_URL, static::VIDEO_EDIT_ALL_EXCEPT_URL => 'Quyền sửa tất cả trừ url',

            // Thêm mới chung
            static::PAGE_CREATE, static::POST_CATEGORY_CREATE, static::POST_CREATE, static::CATEGORY_PRODUCT_CREATE, 
            static::SETTING_CREATE, static::ORDER_DETAIL_CREATE, static::ORDER_CREATE, 
            static::CONTACT_CREATE, static::BRAND_CREATE, 
            static::BANNER_CREATE, static::ATTRIBUTE_VALUE_CREATE,
            static::REVIEW_CREATE, static::VIDEO_CREATE => 'Thêm mới',

            // Xem chi tiết chung
            static::PAGE_VIEW_DETAIL, static::POST_CATEGORY_VIEW_DETAIL, static::POST_VIEW_DETAIL, static::CATEGORY_PRODUCT_VIEW_DETAIL, 
            static::SETTING_VIEW_DETAIL, static::ORDER_DETAIL_VIEW, static::ORDER_VIEW_DETAIL, 
            static::CONTACT_VIEW_DETAIL, static::BRAND_VIEW_DETAIL, 
            static::BANNER_VIEW_DETAIL, static::ATTRIBUTE_VALUE_VIEW_DETAIL,
            static::REVIEW_VIEW_DETAIL, static::VIDEO_VIEW_DETAIL => 'Xem chi tiết',

            // Xem danh sách chung
            static::PAGE_VIEW_LIST, static::POST_CATEGORY_VIEW_LIST, static::POST_VIEW_LIST, static::CATEGORY_PRODUCT_VIEW_LIST, 
            static::SETTING_VIEW_LIST, static::ORDER_DETAIL_LIST, static::ORDER_VIEW_LIST, 
            static::CONTACT_VIEW_LIST, static::BRAND_VIEW_LIST, 
            static::BANNER_VIEW_LIST, static::ATTRIBUTE_VALUE_VIEW_LIST,
            static::REVIEW_VIEW_LIST, static::VIDEO_VIEW_LIST, static::EMAIL_SUBSCRIBER_VIEW_LIST => 'Xem danh sách',

            // Thông tin website
            static::SYSTEM_INFO_EDIT => 'Cập nhật thông tin',
            static::SYSTEM_INFO_VIEW => 'Xem thông tin',

            // Lịch sử
            static::HISTORY_VIEW => 'Xem lịch sử',
        };
    }
}