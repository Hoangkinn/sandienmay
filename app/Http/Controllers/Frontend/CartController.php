<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Vat;
use App\Helpers\AgentHelper;
use App\Helpers\SeoHelper;
use App\Http\Controllers\FrontendController;
use App\Http\Requests\CartRequest;
use App\Models\New_Product;
use App\Models\Product;
use App\Services\FeServices\CartService;
use App\Services\FeServices\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends BaseController
{
    public function __construct(private readonly CartService $cartService)
    {
        parent::__construct();
    }

    public function index()
    {
        SeoHelper::setTitle('Giỏ hàng');
        return view('web.cart.index', [
            'buyMoreUrl' => $this->buyMoreUrl(),
        ]);
    }

    /**
     * "Mua thêm sản phẩm khác" trả khách về danh mục của món cuối họ bỏ vào giỏ (session carts giữ
     * theo thứ tự thêm nên end() = món mới nhất), không phải về Trang chủ — về Trang chủ thì khách
     * phải tìm lại từ đầu. Ưu tiên danh mục cấp 1 (parent_id = 0): danh mục con ở đây gán theo
     * thương hiệu và admin hay gán lệch (SP DQC-Tech nằm trong "Máy hút bụi Kumisai"), về cấp 1 thì
     * khách vẫn thấy đúng loại hàng. SP chỉ có danh mục con thì lấy danh mục con.
     */
    private function buyMoreUrl(): string
    {
        $home = route('frontend.page.home');

        $carts = session('carts', []);
        $last = $carts ? end($carts) : null;
        if (empty($last['id'])) {
            return $home;
        }

        $product = New_Product::find($last['id']);
        if (!$product) {
            return $home;
        }

        $categories = resolve(CategoryService::class)->getByProduct($product);
        usort($categories, fn ($a, $b) => $a['parent_id'] <=> $b['parent_id']);

        return $categories[0]['url'] ?? $home;
    }

    public function add($id)
    {
        $product = Product::findOrFail($id);
        $carts = session()->get('carts', []);
        $quantity = request()->input('quantity', 1);
        $quantity = max(1, intval($quantity));

        if (isset($carts[$id])) {
            $carts[$id]['quantity'] += $quantity;
        } else {
            $carts[$id] = [
                "id" => $product->id,
                "name" => $product->name,
                "slug" => $product->slug,
                "is_vat_label" => ($product->is_vat ?? Vat::VAT_NO)->label(),
                "is_vat" => ($product->is_vat ?? Vat::VAT_NO)->value,
                "quantity" => $quantity,
                "regular_price" => $product->sale_price ? $product->sale_price : $product->regular_price,
                "image" => $product->imageFirst() ? $product->imageFirst()->url : '/storage/' . $product->image
            ];
        }

        session()->put('carts', $carts);
        session()->flash('cart.success', $product->name . ' đã được thêm vào giỏ hàng');

        return $carts;
    }

    

    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);
        $quantity = $request->quantity;
        if ($quantity) {
            $carts = session()->get('carts', []);
            $carts[$id]["quantity"] = $quantity;
            $carts[$id]["regular_price"] = $product->sale_price ? $product->sale_price : $product->regular_price;

            session()->put('carts', $carts);
        }
        return $carts;
    }

    public function delete($id)
    {
        if ($id) {
            $carts = session()->get('carts');
            if (isset($carts[$id])) {
                $productName = $carts[$id]['name'];
                unset($carts[$id]);
                session()->put('carts', $carts);
                session()->flash('cart.delete.success', $productName . ' đã được xóa khỏi giỏ hàng');
            }

            return $carts;
        }
    }

    public function destroy()
    {
        session()->put('carts', []);
        session()->flash('cart.delete.success', 'Đã xóa hết sản phẩm khỏi giỏ hàng');
        return redirect()->route('frontend.cart.index');
    }

    // public function add($id, Request $request)
    // {
       
    //     $this->cartService->add($id, $request->validated('quantity'), $request->validated('atv'));
    //     return $this->json([
    //         'message' => 'Đã thêm giỏ hàng'
    //     ]);
    // }

    public function clear(): JsonResponse
    {
        $this->cartService->clear();
        return $this->json([
            'message' => 'Đã xóa giỏ hàng'
        ]);
    }

    public function applyCoupon(Request $request)
{
    try {
        $code = trim($request->input('code'));
        $total = 0;
        $cartItems = session('carts', []);
        
        if (empty($cartItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng của bạn đang trống!'
            ]);
        }

        $productIds = [];
        foreach ($cartItems as $cart) {
            $total += ($cart['is_vat'] == 0) 
                ? ($cart['regular_price'] + ($cart['regular_price'] * 0.1)) * $cart['quantity'] 
                : $cart['regular_price'] * $cart['quantity'];
                
            if (isset($cart['id'])) {
                $productIds[] = $cart['id'];
            }
        }

        // 1. Quét tìm mã giảm giá trong tất cả các danh mục
        $couponOwnerCategoryId = null;
        $matchedCoupon = null;
        $matchedCodeKey = '';

        $allCategories = \App\Models\Category::whereNotNull('coupon_data')->get();
        foreach ($allCategories as $cat) {
            $coupons = is_string($cat->coupon_data) ? json_decode($cat->coupon_data, true) : $cat->coupon_data;
            
            if (is_array($coupons)) {
                foreach ($coupons as $couponItem) {
                    if (!empty($couponItem['code']) && strcasecmp(trim($couponItem['code']), $code) === 0) {
                        $matchedCoupon = $couponItem;
                        $matchedCodeKey = trim($couponItem['code']);
                        $couponOwnerCategoryId = $cat->id; // ID danh mục chứa mã này
                        break 2;
                    }
                }
            }
        }

        if (!$matchedCoupon) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại hoặc không hợp lệ!'
            ]);
        }

        // 2. Kiểm tra xem sản phẩm trong giỏ có thuộc ĐÚNG danh mục chứa mã hay không (Không xét danh mục cha)
        $isApplicable = false;
        foreach ($productIds as $productId) {
            $productModel = \App\Models\New_Product::find($productId) ?? \App\Models\Product::find($productId);
            
            if ($productModel) {
                $productCategoryIds = [];
                
                if (isset($productModel->category_id)) {
                    $productCategoryIds[] = $productModel->category_id;
                }
                
                if (method_exists($productModel, 'categories')) {
                    $relIds = $productModel->categories()->pluck('m_categories.id')->toArray();
                    $productCategoryIds = array_merge($productCategoryIds, $relIds);
                }

                // Kiểm tra khớp tuyệt đối ID danh mục chứa mã với ID danh mục của sản phẩm
                if (in_array($couponOwnerCategoryId, $productCategoryIds)) {
                    $isApplicable = true;
                    break;
                }
            }
        }

        if (!$isApplicable) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá này không áp dụng cho sản phẩm trong giỏ hàng của bạn!'
            ]);
        }

        // 3. Kiểm tra giá trị tối thiểu của đơn hàng (min_spend)
        $minSpend = isset($matchedCoupon['min_spend']) && is_numeric($matchedCoupon['min_spend']) ? (float)$matchedCoupon['min_spend'] : 0;
        
        if ($total < $minSpend) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($minSpend, 0, ',', '.') . '₫ để áp dụng mã này!'
            ]);
        }

        // 4. Tính toán số tiền giảm giá (Hỗ trợ % hoặc số tiền trực tiếp / có chữ k)
        $discountRaw = trim($matchedCoupon['discount'] ?? '0');
        $discountAmount = 0;
        
        if (str_ends_with($discountRaw, '%')) {
            $percent = (float)str_replace('%', '', $discountRaw);
            $discountAmount = ($total * $percent) / 100;
        } else {
            $cleanDiscount = str_ireplace(['k', '.'], ['', ''], $discountRaw);
            if (stripos($discountRaw, 'k') !== false) {
                $discountAmount = (float)$cleanDiscount * 1000;
            } else {
                $discountAmount = (float)$cleanDiscount;
            }
        }

        if ($discountAmount > $total) {
            $discountAmount = $total;
        }

        // Lưu thông tin mã giảm giá vào session
        session(['applied_coupon' => [
            'code' => $matchedCodeKey,
            'discount' => $discountAmount
        ]]);

        $newTotal = $total - $discountAmount;

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'discount_formatted' => number_format($discountAmount, 0, ',', '.') . '₫',
            'new_total_formatted' => number_format($newTotal, 0, ',', '.') . '₫'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ], 500);
    }
}
    

}
