<?php
namespace App\Http\Controllers\Frontend;


use App\Http\Middleware\FeFormatOutput;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Models\Redirect;

Route::get('clear-cache', function() {
    Artisan::call('cache:clear');
});

Route::middleware(FeFormatOutput::class)->group(function () {
    Route::get('/', [New_HomeController::class, 'index'])->name('frontend.page.home');
});
Route::get('ajax/search', [New_AjaxController::class, 'search'])->name('fe.search');

Route::get('sitemap.xml', [SitemapController::class, 'index']);

$redirect = Redirect::where('redirect_from', url()->current())->first();
if ($redirect) {
    $redirectFrom = str_replace(url(''), '', $redirect->redirect_from);
    $redirectTo = str_replace(url(''), '', $redirect->redirect_to);
    Route::redirect($redirectFrom, $redirectTo, $redirect->redirect_code);
}

Route::group(['prefix' => 'tin-tuc-may'], function () {
    Route::get('/', [PostController::class, 'index'])->name('frontend.post.index');
    Route::get('/xem-truoc-them-moi', [PostController::class, 'previewCreate'])->name('frontend.post.preview.create');
});

Route::get('/gio-hang', [CartController::class, 'index'])->name('frontend.cart.index');

Route::group(['prefix' => 'cart'], function () {
    Route::post('/add/{id}', [CartController::class, 'add'])->name('frontend.cart.add');
    Route::post('/update/{id}', [CartController::class, 'update'])->name('frontend.cart.update');
    Route::post('/remove/{id}', [CartController::class, 'delete'])->name('frontend.cart.delete');
    Route::get('/destroy', [CartController::class, 'destroy'])->name('frontend.cart.destroy');
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('frontend.cart.applyCoupon');
});

Route::group(['prefix' => 'payment'], function () {
    Route::get('/', [PaymentController::class, 'index'])->name('frontend.payment.index');
    Route::post('/store', [PaymentController::class, 'store'])->name('frontend.payment.store');
    Route::get('/{id}/success', [PaymentController::class, 'success'])->name('frontend.payment.success');
});

// Khu tài khoản khách hàng (guard "customers", bảng m_customers). Phải đăng ký TRƯỚC
// route catch-all /{slug} ở cuối file, nếu không SlugDispatcherController sẽ nuốt hết.
Route::prefix('tai-khoan')->name('frontend.customer.')->group(function () {
    Route::get('dang-nhap', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('dang-nhap', [CustomerAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('dang-ky', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('dang-ky', [CustomerAuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
    Route::post('dang-xuat', [CustomerAuthController::class, 'logout'])->name('logout');

    Route::middleware('customer.auth')->group(function () {
        Route::get('/', [CustomerAccountController::class, 'index'])->name('account');
        Route::post('thong-tin', [CustomerAccountController::class, 'updateProfile'])->name('profile');
        Route::post('doi-mat-khau', [CustomerAccountController::class, 'updatePassword'])->name('password');
        Route::get('don-hang', [CustomerOrderController::class, 'index'])->name('orders');
        Route::get('don-hang/{order}', [CustomerOrderController::class, 'show'])->whereNumber('order')->name('order');
    });
});

Route::group(['prefix' => 'ajax'], function () {
    Route::post('/comment/{slug}/{type}', [CommentController::class, 'save'])->name('frontend.product.comment');
    Route::get('/search/product', [SearchController::class, 'product'])->name('frontend.search.product');
    Route::get('/products-by-category/{id}', [ProductController::class, 'loadProducts']);
    Route::get('/news-posts', [PostController::class, 'loadMore'])->name('frontend.post.loadMore');
    Route::get('{method}', [AjaxController::class, 'handleGet'])->where('method', '\w+');
    Route::get('rated/{slug}', [CommentController::class, 'getRated'])->name('product.rated');    
});

Route::post('/contact', [ContactController::class, 'store'])->name('frontend.contact.store');
Route::post('/yeu-cau-bao-gia', [ContactController::class, 'quote'])->name('frontend.quote.store')
    ->middleware('throttle:10,1');
Route::post('/dang-ky-email', [NewsletterController::class, 'store'])->name('frontend.newsletter.store');
Route::get('/tim-kiem', [PageController::class, 'search'])->name('frontend.page.search');
Route::get('/tra-cuu-bao-hanh', [PageController::class, 'getWarranty'])->name('frontend.page.index');
Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('frontend.wishlist.add');
Route::get('/san-pham', [ProductController::class, 'getAll'])->name('frontend.page.product');
Route::get('so-sanh-san-pham/{ids}', [ProductController::class, 'compare'])->name('product.compare')
    ->where('ids', '^[0-9]+(-[0-9]+){1,2}$');

Route::get('/{slug}', [SlugDispatcherController::class, 'handle'])->middleware(FeFormatOutput::class)->name('frontend.slug');

Route::get('/backend/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return redirect()->back()->with('success', 'Đã xóa cache thành công!');
})->name('backend.clear.cache')->middleware('auth');