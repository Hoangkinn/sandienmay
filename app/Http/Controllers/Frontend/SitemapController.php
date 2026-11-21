<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use Illuminate\Http\Request;
use App\Enums\Status;
use App\Models\Post;
use App\Models\Category;
use App\Models\Product;

class SitemapController extends FrontendController
{
    public function index()
    {
        $categories = $this->indexable(Category::class);
        $products = $this->indexable(Product::class);
        $posts = $this->indexable(Post::class);

        $urls = [
            route('frontend.page.home') => [
                'lastmod' => $categories->merge($products)->merge($posts)->max('updated_at'),
                'priority' => '1.00',
            ],
            route('frontend.post.index') => [
                'lastmod' => $posts->max('updated_at'),
                'priority' => '0.80',
            ],
            route('frontend.page.index') => [
                'lastmod' => null,
                'priority' => '0.60',
            ],
        ];

        // Key = URL, `??=` = URL da co thi giu lan dau. Can khoa nay vi `/{slug}` la route
        // cuoi cung trong routes/frontend.php: danh mục co slug `tin-tuc-may` (id 368) bi
        // route('frontend.post.index') che mat, xuat ca hai la sitemap co 2 <loc> giong het nhau.
        foreach ([['priority' => '0.80', 'items' => $categories],
                  ['priority' => '0.64', 'items' => $products],
                  ['priority' => '0.51', 'items' => $posts]] as $group) {
            foreach ($group['items'] as $item) {
                $urls[route('frontend.slug', ['slug' => $item->slug])] ??= [
                    'lastmod' => $item->updated_at,
                    'priority' => $group['priority'],
                ];
            }
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'text/xml');
    }

    /**
     * seo_no_index = 1 nghia la trang dang mang <meta name="robots" content="noindex">,
     * de link trong sitemap thi Google van khong index (chi ghi nhan "Crawled - currently not
     * indexed"), nen loai truoc khi xuat. Cot nay co the NULL -> whereNull phai di kem,
     * vi "seo_no_index != 1" loai luon dong NULL trong MySQL.
     */
    private function indexable(string $modelClass)
    {
        return $modelClass::where('status', Status::ACTIVED->value)
            ->where(function ($query) {
                $query->whereNull('seo_no_index')->orWhere('seo_no_index', 0);
            })
            ->get();
    }
}
