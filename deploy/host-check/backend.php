<?php

use App\Enums\CategoryType;
use App\Http\Controllers\Backend\AdminController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\FaqController;
use App\Http\Controllers\Backend\PhotoController;
use App\Http\Controllers\Backend\PageController;
use App\Http\Controllers\Backend\PostController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\BannerController;
use App\Http\Controllers\Backend\AttributeController;
use App\Http\Controllers\Backend\AttributeOptionController;
use App\Http\Controllers\Backend\CommentController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DropzoneController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\ProjectController;
use App\Http\Controllers\Backend\PromotionController;
use App\Http\Controllers\Backend\RedirectController;
use App\Http\Controllers\Backend\ReviewController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\SettingFieldController;
use App\Http\Controllers\Backend\UpdateOrderController;
use App\Http\Controllers\Backend\VideoController;
use UniSharp\LaravelFilemanager\Controllers\LfmController;
use App\Http\Controllers\Backend\BrandController; 
use App\Http\Controllers\Backend\HistoryController;
use App\Http\Controllers\Backend\EmailSubscriberController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => 'backend',
    'middleware' => 'auth'
], function () {

    Route::get('/', [DashboardController::class, 'index'])->name('backend.dashboard');

    Route::group(['prefix' => 'change-password'], function () {
        Route::get('/', [AdminController::class, 'changePass'])->name('backend.admin.change-pass');
        Route::post('/', [AdminController::class, 'updatePass'])->name('backend.admin.update-pass');
    });

    Route::post('/photo/store', [PhotoController::class, 'store'])->name('backend.photo.store');

    // --- 1. TRANG TĨNH (PAGE) ---
    Route::group(['prefix' => 'page', 'middleware' => ['permission:page_view_list']], function () {
        Route::match(['get', 'post'], '/', [PageController::class, 'index'])->name('backend.page.index');
        Route::get('/add', [PageController::class, 'create'])->name('backend.page.create')->middleware('permission:page_create');
        Route::post('/store', [PageController::class, 'store'])->name('backend.page.store')->middleware('permission:page_create');
        Route::get('/{id}/edit', [PageController::class, 'edit'])->name('backend.page.edit')->middleware('permission:page_edit_all_except_url');
        Route::post('/{id}/update', [PageController::class, 'update'])->name('backend.page.update')->middleware('permission:page_edit_all_except_url');
        Route::get('/{id}/delete', [PageController::class, 'destroy'])->name('backend.page.delete')->middleware('permission:page_delete');
    });

    // --- 2. BÀI VIẾT (POST) ---
    Route::group(['prefix' => 'post', 'middleware' => ['permission:post_view_list']], function () {
        Route::match(['get', 'post'], '/', [PostController::class, 'index'])->name('backend.post.index');
        Route::get('/add', [PostController::class, 'create'])->name('backend.post.create')->middleware('permission:post_create');
        Route::post('/store', [PostController::class, 'store'])->name('backend.post.store')->middleware('permission:post_create');
        Route::match(['get', 'post'], '/preview-create', [PostController::class, 'previewCreate'])->name('backend.post.preview.create')->middleware('permission:post_create');
        Route::post('/delete-multiple', [PostController::class, 'deleteMultiple'])->name('backend.post.delete-multiple')->middleware('permission:post_delete');

        Route::get('/{id}/edit', [PostController::class, 'edit'])->name('backend.post.edit')->middleware('permission:post_edit_all_except_url');
        Route::post('/{id}/update', [PostController::class, 'update'])->name('backend.post.update')->middleware('permission:post_edit_all_except_url');
        Route::get('/{id}/delete', [PostController::class, 'destroy'])->name('backend.post.delete')->middleware('permission:post_delete');
        Route::match(['get', 'post'], '/{id}/preview', [PostController::class, 'preview'])->name('backend.post.preview')->middleware('permission:post_view_detail');
        // Route::post('/{id}/preview', [PostController::class, 'preview'])->name('backend.post.preview')->middleware('permission:post_view_detail');
        Route::post('/ajax/update-featured/{id}', [PostController::class, 'updateFeatured'])->middleware('permission:post_edit_all_except_url');
    });

    Route::group(['prefix' => 'faq'], function () {
        Route::get('/', [FaqController::class, 'index'])->name('backend.faq.index');
        Route::get('/add', [FaqController::class, 'create'])->name('backend.faq.create');
        Route::post('/store', [FaqController::class, 'store'])->name('backend.faq.store');
        Route::get('/{id}/edit', [FaqController::class, 'edit'])->name('backend.faq.edit');
        Route::post('/{id}/update', [FaqController::class, 'update'])->name('backend.faq.update');
        Route::get('/{id}/delete', [FaqController::class, 'destroy'])->name('backend.faq.delete');
    });

    // --- 3. SẢN PHẨM ---
    Route::group(['prefix' => 'product', 'middleware' => ['permission:product_view_list']], function () {
        Route::get('/', [ProductController::class, 'index'])->name('backend.product.index');
        Route::get('/add', [ProductController::class, 'create'])->name('backend.product.create')->middleware('permission:product_create');
        Route::post('/store', [ProductController::class, 'store'])->name('backend.product.store')->middleware('permission:product_create');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('backend.product.edit')->middleware('permission:product_edit_all_except_url');
        Route::post('/{id}/update', [ProductController::class, 'update'])->name('backend.product.update')->middleware('permission:product_edit_all_except_url');
        Route::get('/{id}/delete', [ProductController::class, 'destroy'])->name('backend.product.delete')->middleware('permission:product_delete');
        Route::get('/{id}/attribute', [ProductController::class, 'attribute'])->name('backend.product.attribute')->middleware('permission:product_edit_spec');
        Route::post('/{product_id}/attribute/save', [ProductController::class, 'saveProductAttribute'])->name('backend.product.attribute.save')->middleware('permission:product_edit_spec');
        Route::post('/{product_id}/updateFeature', [ProductController::class, 'updateFeature'])->name('backend.product.updateFeature')->middleware('permission:product_edit_all_except_url');
        Route::get('/export', [ProductController::class, 'exportProducts'])->name('products.export');
        Route::post('/delete-multiple', [ProductController::class, 'deleteMultiple'])->name('backend.product.delete-multiple')->middleware('permission:product_delete');
        Route::get('/{attribute_id}/attribute-options', [ProductController::class, 'getAttributeOptions'])->name('backend.product.getAttributeOptions')->middleware('permission:product_edit_spec');
        // Giá trị thuộc tính
        Route::group(['prefix' => 'attribute'], function () {
            Route::match(['get', 'post'], '/', [AttributeController::class, 'index'])->name('backend.attribute.index')->middleware('permission:attribute_value_view_list'); 
            Route::get('/add', [AttributeController::class, 'create'])->name('backend.attribute.create')->middleware('permission:attribute_value_create');
            Route::post('/store', [AttributeController::class, 'store'])->name('backend.attribute.store')->middleware('permission:attribute_value_create');
            Route::get('/{id}/edit', [AttributeController::class, 'edit'])->name('backend.attribute.edit')->middleware('permission:attribute_value_edit_all_except_url');
            Route::post('/{id}/update', [AttributeController::class, 'update'])->name('backend.attribute.update')->middleware('permission:attribute_value_edit_all_except_url');
            Route::get('/{id}/delete', [AttributeController::class, 'destroy'])->name('backend.attribute.delete')->middleware('permission:attribute_value_delete');

            Route::post('/{attribute_id}/option/save', [AttributeOptionController::class, 'save'])->name('backend.option.save')->middleware('permission:attribute_value_create');
            Route::get('/{attribute_id}/option/list', [AttributeOptionController::class, 'index'])->name('backend.option.list')->middleware('permission:attribute_value_view_list');
        });

        // Thương hiệu
        Route::group(['prefix' => 'brand', 'as' => 'backend.brand.'], function () {
            Route::get('/', [BrandController::class, 'index'])->name('index')->middleware('permission:brand_view_list');
            Route::get('/add', [BrandController::class, 'create'])->name('create')->middleware('permission:brand_create');
            Route::post('/store', [BrandController::class, 'store'])->name('store')->middleware('permission:brand_create');
            Route::get('/{id}/edit', [BrandController::class, 'edit'])->name('edit')->middleware('permission:brand_edit_all_except_url');
            Route::post('/{id}/update', [BrandController::class, 'update'])->name('update')->middleware('permission:brand_edit_all_except_url');
            Route::get('/{id}/delete', [BrandController::class, 'destroy'])->name('delete')->middleware('permission:brand_delete');
        });
    });

    // --- 4. DANH MỤC (CHUNG CHO POST & PRODUCT) ---
    Route::group(['prefix' => 'category'], function () {
        Route::get('/{type}/list', [CategoryController::class, 'index'])
            ->name('backend.category.index')
            ->middleware('permission:category_product_view_list')
            ->whereIn('type', [CategoryType::POST->value, CategoryType::PRODUCT->value]);

        Route::get('/{type}/add', [CategoryController::class, 'create'])
            ->name('backend.category.create')
            ->middleware('permission:category_product_create')
            ->whereIn('type', [CategoryType::POST->value, CategoryType::PRODUCT->value]);

        Route::get('/{type}/{id}/edit', [CategoryController::class, 'edit'])
            ->name('backend.category.edit')
            ->middleware('permission:category_product_edit_all_except_url')
            ->whereIn('type', [CategoryType::POST->value, CategoryType::PRODUCT->value]);

        Route::get('/{type}/{id}/delete', [CategoryController::class, 'destroy'])
            ->name('backend.category.delete')
            ->middleware('permission:category_product_delete')
            ->whereIn('type', [CategoryType::POST->value, CategoryType::PRODUCT->value]);

        Route::post('/{type}/{id}/update', [CategoryController::class, 'update'])->name('backend.category.update')->middleware('permission:category_product_edit_all_except_url');
        Route::post('/{type}/store', [CategoryController::class, 'store'])->name('backend.category.store')->middleware('permission:category_product_create');
        Route::post('/{type}/update-sort', [CategoryController::class, 'updateSort'])->name('backend.category.update-sort')->middleware('permission:category_product_edit_all_except_url');
        Route::post('toggle-menu', [CategoryController::class, 'toggleMenu'])->name('backend.category.toggle-menu');
        Route::post('update-badge', [CategoryController::class, 'updateBadge'])->name('backend.category.update-badge');

        Route::match(['get', 'post'], '/{type}/preview', [CategoryController::class, 'preview'])
            ->name('backend.category.preview.create')
            ->middleware('permission:category_product_create')
            ->whereIn('type', [CategoryType::POST->value, CategoryType::PRODUCT->value]);

        Route::match(['get', 'post'], '/{type}/{id}/preview', [CategoryController::class, 'previewEdit'])
            ->name('backend.category.preview.edit')
            ->middleware('permission:category_product_edit_all_except_url')
            ->whereIn('type', [CategoryType::POST->value, CategoryType::PRODUCT->value]);
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'index'])->name('backend.user.index');
        Route::get('/add', [UserController::class, 'create'])->name('backend.user.create');
        Route::post('/store', [UserController::class, 'store'])->name('backend.user.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('backend.user.edit');
        Route::match(['post', 'put'], '{id}/update', [UserController::class, 'update'])->name('backend.user.update');
        Route::get('/{id}/delete', [UserController::class, 'destroy'])->name('backend.user.delete');
    });

    // --- 5. BANNER ---
    Route::group(['prefix' => 'banner', 'middleware' => ['permission:banner_view_list']], function () {
        Route::get('/', [BannerController::class, 'index'])->name('backend.banner.index');
        Route::get('/add', [BannerController::class, 'create'])->name('backend.banner.create')->middleware('permission:banner_create');
        Route::post('/store', [BannerController::class, 'store'])->name('backend.banner.store')->middleware('permission:banner_create');
        Route::get('/{id}/edit', [BannerController::class, 'edit'])->name('backend.banner.edit')->middleware('permission:banner_edit_all_except_url');
        Route::post('/{id}/update', [BannerController::class, 'update'])->name('backend.banner.update')->middleware('permission:banner_edit_all_except_url');
        Route::get('/{id}/delete', [BannerController::class, 'destroy'])->name('backend.banner.delete')->middleware('permission:banner_delete');
    });

    Route::group(['prefix' => 'reviews', 'middleware' => ['permission:review_view_list']], function () {
        Route::get('/', [ReviewController::class, 'index'])->name('backend.review.index');
        Route::get('/add', [ReviewController::class, 'create'])->name('backend.review.create')->middleware('permission:review_create');
        Route::post('/store', [ReviewController::class, 'store'])->name('backend.review.store')->middleware('permission:review_create');
        Route::get('/{id}/edit', [ReviewController::class, 'edit'])->name('backend.review.edit')->middleware('permission:review_edit_all_except_url');
        Route::post('/{id}/update', [ReviewController::class, 'update'])->name('backend.review.update')->middleware('permission:review_edit_all_except_url');
        Route::get('/{id}/delete', [ReviewController::class, 'destroy'])->name('backend.review.delete')->middleware('permission:review_delete');
    });

    // --- 6. LIÊN HỆ & KHÁCH HÀNG ---
    Route::group(['prefix' => 'customer'], function () {
        Route::get('/', [CustomerController::class, 'index'])->name('backend.customer.index');
        Route::get('/contact', [CustomerController::class, 'listContact'])->name('backend.contact.index')->middleware('permission:contact_view_list');
    });

    // --- 6.1 EMAIL ĐĂNG KÝ NHẬN TIN (footer) ---
    Route::group(['prefix' => 'email-subscriber'], function () {
        Route::get('/', [EmailSubscriberController::class, 'index'])->name('backend.email-subscriber.index')->middleware('permission:email_subscriber_view_list');
        Route::get('/{id}/delete', [EmailSubscriberController::class, 'destroy'])->name('backend.email-subscriber.delete')->middleware('permission:email_subscriber_delete');
    });

    // --- 7. CẤU HÌNH & THÔNG TIN WEBSITE ---
    Route::group(['prefix' => 'setting', 'middleware' => ['permission:system_info_view']], function () {
        Route::get('/', [SettingController::class, 'index'])->name('backend.setting.index');
        Route::post('/save', [SettingController::class, 'save'])->name('backend.setting.save')->middleware('permission:system_info_edit');
    });

    // --- 8. ĐƠN HÀNG ---
    Route::group(['prefix' => 'order', 'middleware' => ['permission:order_view_list']], function () {
        Route::get('/', [OrderController::class, 'index'])->name('backend.order.index');
        Route::get('/{id}/show', [OrderController::class, 'show'])->name('backend.order.show')->middleware('permission:order_view_detail');
        Route::get('/{id}/delete', [OrderController::class, 'destroy'])->name('backend.order.delete')->middleware('permission:order_delete');
        Route::get('/{id}/print', [OrderController::class, 'print'])->name('backend.order.print')->middleware('permission:order_view_detail');
        Route::post('/{id}/update-status', [OrderController::class, 'updateStatus'])->name('backend.order.updateStatus')->middleware('permission:order_edit_all_except_url');
    });

    Route::group(['prefix' => 'promotions'], function () {
        Route::match(['get', 'post'], '/', [PromotionController::class, 'index'])->name('backend.promotion.index');
        Route::get('/add', [PromotionController::class, 'create'])->name('backend.promotion.create');
        Route::post('/store', [PromotionController::class, 'store'])->name('backend.promotion.store');
        Route::get('/{id}/edit', [PromotionController::class, 'edit'])->name('backend.promotion.edit');
        Route::post('/{id}/update', [PromotionController::class, 'update'])->name('backend.promotion.update');
        Route::get('/{id}/delete', [PromotionController::class, 'destroy'])->name('backend.promotion.delete');
    });

    Route::group(['prefix' => 'redirects'], function () {
        Route::match(['get', 'post'], '/', [RedirectController::class, 'index'])->name('backend.redirect.index');
        Route::get('/add', [RedirectController::class, 'create'])->name('backend.redirect.create');
        Route::post('/store', [RedirectController::class, 'store'])->name('backend.redirect.store');
        Route::get('/{id}/edit', [RedirectController::class, 'edit'])->name('backend.redirect.edit');
        Route::post('/{id}/update', [RedirectController::class, 'update'])->name('backend.redirect.update');
        Route::get('/{id}/delete', [RedirectController::class, 'destroy'])->name('backend.redirect.delete');
    });

    Route::group(['prefix' => 'setting-field', 'middleware' => ['permission:setting_view_list']], function () {
        Route::match(['get', 'post'], '/', [SettingFieldController::class, 'index'])->name('backend.setting-field.index');
        Route::get('/add', [SettingFieldController::class, 'create'])->name('backend.setting-field.create');
        Route::post('/store', [SettingFieldController::class, 'store'])->name('backend.setting-field.store');
        Route::get('/{id}/edit', [SettingFieldController::class, 'edit'])->name('backend.setting-field.edit');
        Route::post('/{id}/update', [SettingFieldController::class, 'update'])->name('backend.setting-field.update');
        Route::get('/{id}/delete', [SettingFieldController::class, 'destroy'])->name('backend.setting-field.delete');
    });

    // --- 9. BÌNH LUẬN ---
    Route::group(['prefix' => 'comments', 'middleware' => ['permission:comment_management']], function () {
        Route::match(['get', 'post'], '/', [CommentController::class, 'index'])->name('backend.comment.index');
        Route::post('/{id}/update', [CommentController::class, 'update'])->name('backend.comment.update');
        Route::get('/{id}/delete', [CommentController::class, 'destroy'])->name('backend.comment.delete');
    });

    Route::group(['prefix' => 'ajax'], function () {
        Route::post('/comments/{id}/reply', [CommentController::class, 'reply'])->name('backend.comment.reply');
        Route::post('/comments/{id}/approved/{status}', [CommentController::class, 'approved'])->name('backend.comment.approved');
    });

    Route::group(['prefix' => 'file-manager'], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
        Route::get('/', [LfmController::class, 'show'])->name('backend.file-manager.index');
    });

    Route::group(['prefix' => 'dropzone'], function () {
        Route::post('/upload-image', [DropzoneController::class, 'uploadImage'])->name('backend.dropzone.uploadImage');
        Route::post('/delete-image', [DropzoneController::class, 'deleteImage'])->name('backend.dropzone.deleteImage');
    });

    Route::group(['prefix' => 'video', 'middleware' => ['permission:video_view_list']], function () {
        Route::match(['get', 'post'], '/', [VideoController::class, 'index'])->name('backend.video.index');
        Route::get('/add', [VideoController::class, 'create'])->name('backend.video.create')->middleware('permission:video_create');
        Route::post('/store', [VideoController::class, 'store'])->name('backend.video.store')->middleware('permission:video_create');
        Route::get('/{id}/edit', [VideoController::class, 'edit'])->name('backend.video.edit')->middleware('permission:video_edit_all_except_url');
        Route::post('/{id}/update', [VideoController::class, 'update'])->name('backend.video.update')->middleware('permission:video_edit_all_except_url');
        Route::get('/{id}/delete', [VideoController::class, 'destroy'])->name('backend.video.delete')->middleware('permission:video_delete');
    });

    Route::group(['prefix' => 'project'], function () {
        Route::match(['get', 'post'], '/', [ProjectController::class, 'index'])->name('backend.project.index');
        Route::get('/add', [ProjectController::class, 'create'])->name('backend.project.create');
        Route::post('/store', [ProjectController::class, 'store'])->name('backend.project.store');
        Route::get('/{id}/edit', [ProjectController::class, 'edit'])->name('backend.project.edit');
        Route::post('/{id}/update', [ProjectController::class, 'update'])->name('backend.project.update');
        Route::get('/{id}/delete', [ProjectController::class, 'destroy'])->name('backend.project.delete');
    });

    Route::group(['prefix' => 'ajax'], function () {
        Route::post('/update-order/{type}/{id}/order/{order}', [UpdateOrderController::class, 'updateOrder']);
        Route::post('/update-status/{type}/{id}/{status}', [UpdateOrderController::class, 'updateStatus']);
        Route::post('/update-stock/{id}/{status}', [UpdateOrderController::class, 'updateStock']);
    });

    // --- 10. LỊCH SỬ CHỈNH SỬA ---
    Route::group(['middleware' => ['permission:history_view']], function () {
        Route::get('/history', [HistoryController::class, 'index'])->name('backend.history.index');
        Route::get('/history/{id}', [HistoryController::class, 'show'])->name('backend.history.show');
        Route::get('/history/{id}/restore', [HistoryController::class, 'restore'])->name('backend.history.restore');
    });
});