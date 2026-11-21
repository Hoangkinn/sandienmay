# Checklist upload — demo.sandienmay.com

Site này là **`demo.sandienmay.com`** (DB `ynlbdgfvhosting_sandienmay`, app root
`/home/ynlbdgfvhosting/public_html/demo.sandienmay.com`).
`sandienmay.com` là **một site khác**, không liên quan — mọi con số/kết luận ở đây chỉ áp dụng cho demo.

Lần upload 2026-10-26 **đã chạy xong và đã kiểm tra**: trang chủ 200, `/ajax/rated/<slug>` 200 JSON,
slug giả trả 404/422 JSON (không phải HTML), `/sitemap.xml` 200, `/backend/login` 200,
`X-Robots-Tag: noindex, nofollow` có mặt. Vì vậy checklist này là **quy trình cho các lần upload sau**,
không phải lệnh phải làm ngay hôm nay.

Số liệu thật từ git (thời điểm 2026-10-26):

- **166 file đã sửa** (`M`) + **42 file mới** (`??`) + **462 file đã xoá** (`D`) so với commit gần nhất.
  Trong đó có `app/Models/History.php`, `EmailSubscriber.php`, `Backend/HistoryController.php`,
  `Frontend/NewsletterController.php`, `Middleware/NoIndex.php`, `CheckPermission.php`.
- **3 migration chưa chạy ở đâu trên host**: `2026_10_12` (cột `badge` cho `m_categories`),
  `2026_10_14` (3 dòng `m_setting_fields` tác giả bài viết), `2026_10_20` (bảng `email_subscribers`).
- **2 đối tượng KHÔNG có migration nào cả** → bắt buộc tạo tay: bảng `histories`, cột `m_comments.product_id`.
- Không có shell trên host (chỉ FTP 21) → **không chạy được `php artisan migrate`**. Mọi thứ trên là SQL tay trong phpMyAdmin.

Vì sao hay sập: code mới lọc comment theo `product_id`, `CategoryService` select tường minh `badge`,
`ProductDetailDto` đọc `m_products.schema`. Thiếu một trong các cột đó → **mọi trang sản phẩm và trang
danh mục 500 ngay**, không chỉ lỗi review. Sập vì `.env` (mật khẩu có `#` không bọc nháy → `1045`) thì
site chết trắng, không liên quan code.

---

## Bước 0 — Đường lùi (làm trước, ~5 phút)

1. phpMyAdmin (DB `ynlbdgfvhosting_sandienmay`) → tab **Export** → lưu 1 file `.sql`. Đây là cách duy nhất để lùi dữ liệu.
2. WinSCP: trên server, copy các thư mục sau sang `/backup-truoc-deploy/` **ngay bên cạnh**:
   `app/`, `bootstrap/cache/`, `config/`, `resources/views/`, `routes/`, `public/css-new/`, `public/js-new/`, `.htaccess`, `public/.htaccess`.
   Rollback = copy ngược các thư mục đó về cũ. Không có git trên server nên đây là đường lùi duy nhất cho code.

## Bước 1 — Schema (TRƯỚC khi upload code)

Có 2 file, chạy theo đúng thứ tự này:

1. **`deploy/kiem-tra-schema.sql`** — chỉ SELECT, không sửa gì. Mở tab SQL của DB
   `ynlbdgfvhosting_sandienmay` rồi chạy nguyên file (**đừng bỏ dòng `USE ...`** ở đầu —
   thiếu nó phpMyAdmin dễ chạy nhầm sang `information_schema` và mọi kết quả ra 0; đã xảy ra 2026-10-26).
   - Dòng `chan` ở mục (0) phải ra **"OK - DUNG DB cua site"**. Nếu ra "SAI DB" thì dừng, gửi tôi ảnh.
   - Gửi tôi kết quả **mục (2)** (chữ ký cột từng bảng) và **mục (3)** (6 dòng `ton_tai`).
2. **`deploy/fix-schema-demo.sql`** — tạo đúng phần còn thiếu (4 cột + 2 bảng + 3 dòng cấu hình).
   Mọi câu đều `IF NOT EXISTS`, chạy lại nhiều lần không sao, không đụng dữ liệu cũ.
   Đã chạy thử trên MySQL local: xoá trước 4 cột + 2 bảng đó trong một bản sao cấu trúc, chạy lại file
   (bản dịch bỏ `IF NOT EXISTS` vì MySQL local không nhận cú này) → không lỗi, và `histories` /
   `email_subscribers` tạo lại từ đầu cho **chữ ký cột khớp baseline local**:
   `m_comments 15 9ea4785a`, `m_categories 26 31f11f81`, `m_products 37 7e05eb0e`,
   `histories 11 d0b0ab24`, `email_subscribers 4 0d0b6285`. Chạy 2 lần không tạo bản trùng.
   Câu `INSERT IGNORE` vào `m_setting_fields` nằm **cuối file** theo chủ đích: nếu bảng đó chưa có,
   phpMyAdmin dừng ở đó nhưng mọi thứ cần thiết phía trên đã chạy xong — chỉ cần báo lại tôi.
3. Chạy lại `kiem-tra-schema.sql` lần nữa: 6 dòng mục (3) phải đều `1`.
   Dòng nào vẫn `0` → gửi tôi nguyên văn thông báo lỗi của câu tương ứng trong `fix-schema-demo.sql`.
4. Mục (5) của file kiểm tra: nếu `review_chua_co_product_id` > 0 thì **không bắt buộc** backfill —
   code có fallback `product_slug` cho row cũ. Chỉ chạy câu backfill khi bạn sắp đổi "Đường dẫn"
   (slug) của một sản phẩm **đã có review**.

## Bước 2 — `.env` trên host: **KHÔNG upload file `.env` từ máy lên**

`.env` local (`DB_DATABASE=sandienmay`, `DB_USERNAME=root`) upload lên là site chết ngay. Ngược lại, copy `.env` host
về máy rồi quên sửa cũng làm local 500 và trông hệt lỗi code. Chỉ mở file `.env` **trên server**
(`demo.sandienmay.com/.env`, nằm ngang hàng với thư mục `public/`) và kiểm tra:

```
APP_ENV=production
APP_DEBUG=false          <-- demo đang để true: trang lỗi lộ stack + tên DB ra ngoài
APP_URL=https://demo.sandienmay.com
APP_NO_INDEX=true        <-- demo: CHỦ ĐỘNG chặn search engine, đừng tắt
DB_PASSWORD="<bọc nháy kép, hoặc chỉ dùng chữ+số>"
```

- `DB_PASSWORD` có `#` mà không bọc nháy → Dotenv cắt chuỗi tại `#`, MySQL trả `1045`, site sập toàn bộ dù code đúng 100%.
  Tốt nhất: reset mật khẩu trong panel hosting sang **chỉ chữ + số** để diệt hẳn lớp lỗi này.
- **Không `php artisan key:generate` trên host** dưới mọi lý do: demo đang dùng chung `APP_KEY` với local; đổi key là hỏng cookie/session và dữ liệu đã mã hoá.

## Bước 3 — Xoá cache bootstrap (khi có sửa config/route)

Trong `bootstrap/cache/` trên server, **xoá** nếu tồn tại: `config.php`, `routes.php`, `events.php`, `packages.php`, `services.php` (giữ `.gitignore`).
Hiện tại demo **không có** `bootstrap/cache/config.php`, nên sửa `.env` là ăn ngay. Nhưng nếu sau này phát sinh file đó mà không xoá thì: `.env` sửa không ăn, route mới (`/ajax/rated/...`, `/newsletter`, `/backend/history`) trả 404.

## Bước 4 — Upload

- Upload **toàn bộ** `app/`, `config/`, `routes/`, `resources/views/`, `bootstrap/app.php`, và `public/css-new/` + `public/js-new/` (bản min đã build).
- `composer.json` / `composer.lock`: **không đổi** → không cần `composer update` trên host.
- `package.json` có đổi nhưng chỉ để build asset tại máy bạn → không liên quan host.
- **KHÔNG xoá hàng loạt file trên server.** Cây local đang có 462 file đã xoá (dọn dead code). Cứ để file cũ nằm lại trên host — chúng không được tham chiếu nữa nên vô hại; xoá theo kiểu sync-two-way dễ gây 404 hơn là lợi.
- Server là **OpenLiteSpeed**, không phải Apache trên Laragon → file `.htaccess` có thể bị bỏ qua một phần. Sau khi upload, kiểm tra gzip/`Expires` có thật sự áp dụng không thay vì assume.
- **Reload PHP/opcache** sau khi upload file PHP (panel hosting thường có nút này), nếu không sẽ có lúc vẫn chạy code cũ.
- Hai file `.htaccess` **có sửa**, và là chỗ dễ gây sập nhất:
  - `.htaccess` gốc: bản mới **đã bỏ** khối `AddHandler application/x-httpd-ea-php74` (khai báo bản PHP của cPanel, là rác thừa từ site `kumisai.vn`) và bỏ luôn redirect `www.kumisai.vn → kumisai.vn`. Redirect thừa thì nên bỏ, nhưng nếu host dựa vào `AddHandler` để chọn bản PHP thì bỏ xong site trắng trang → **không upload file `.htaccess` gốc**, hoặc upload rồi kiểm tra ngay trang chủ.
  - `public/.htaccess`: thêm gzip + `ExpiresByType` 30 ngày cho css/js/ảnh, mở khoá `.well-known`, và **ép `Content-Type: application/json` cho mọi file trong `.well-known/`**. Nếu host phát hành SSL bằng ACME http-01, kiểm tra gia hạn SSL vẫn chạy sau khi upload.
  - `public/auth.md` (tài liệu AI-agent, không chứa key nào) hiện **đang công khai trên demo** (`/auth.md` → 200). Nếu không muốn lộ, đừng upload file đó.
- dọn dẹp script tạm: xoá `public/zz-db-check.php` và `.env.bak-dbfix` trên host nếu lần sửa `.env` trước còn sót (file `zz-db-check.php` tôi đã xoá ở local).

## Bước 5 — Kiểm ngay sau upload (tôi chạy được qua HTTP, gửi tôi "xong" là tôi làm)

| Kiểm | Kỳ vọng |
|---|---|
| `https://demo.sandienmay.com/` | 200, **có** header `X-Robots-Tag: noindex, nofollow` (demo thì đây là đúng) |
| 1 trang danh mục + 1 trang sản phẩm | 200, có khối đánh giá |
| `/ajax/rated/<slug thật>` | 200 JSON `{"1":..,"5":..}` |
| `/ajax/rated/<slug giả>` | 404 JSON toàn 0 (không phải HTML) |
| Gửi review, slug giả | 422 + toast đỏ "Sản phẩm không tồn tại" |
| `/backend/login` | 200 |
| `/sitemap.xml` | 200, XML hợp lệ |
| 1 trang bài viết + `/backend/history` | 200 |

Lưu ý khi kiểm bằng curl: demo chặn UA mặc định của curl (403) → luôn gửi `-A "<UA trình duyệt>"`.

## Ghi chú những thứ tôi cố ý KHÔNG sửa

- `Comment::productInfo()` vẫn khoá theo `product_slug` (chỉ liên kết trong danh sách admin).
- `CommentService::create()` là code chết, không có ai gọi.
- `Backend/CommentController.php:73,103,194` gọi `Cache::forget('product-stat-review-…')` cho một cache không tồn tại — vô nghĩa nhưng vô hại.
- Cột `author_email` thật ra chứa **số điện thoại** (form ghi "Nhập số điện thoại", regex 10–11 số). Chỉ là tên cột cũ, không phải lỗi.
- Dữ liệu review/schema trên DB demo là **fixture tôi seed** để test, không phải số liệu thật → đừng dùng để suy ra điều gì về dữ liệu thật.
- Dọn dẹp còn treo: xoá row test `author="ZZ Test"` / `content="zz-noi-dung-test-co-the-xoa"` trong `m_comments` trên demo.

---

## Vòng 2026-10-27 — PageSpeed vòng 1 (chỉ 4 file)

Đã đối chiếu md5 local ↔ demo nên danh sách này là **phần chênh thật**, không phải "upload cả thư mục":

| File local | Đường dẫn trên server | Lý phải upload |
|---|---|---|
| `config/debugbar.php` (MỚI) | `/config/debugbar.php` | Tắt Laravel Debugbar — nó nhúng ~420KB CSS/JS chặn render vào **mọi** trang |
| `public/css-new/dist/base.min.css` | `/public/css-new/dist/base.min.css` | `font-display: block` → `swap` cho icon font icomoon |
| `resources/views/web/home/index.blade.php` | `/resources/views/web/home/index.blade.php` | Ảnh đối tác có `width/height`; dùng `logo-m.png?ver=3` thay `logo.svg` (49KB) |
| `resources/views/web/mobile-menu.blade.php` | `/resources/views/web/mobile-menu.blade.php` | Logo drawer dùng chung URL với header, `loading="lazy"` |

**Không cần clear cache.** Demo không có `bootstrap/cache/config.php` → `config/debugbar.php` có hiệu lực
ngay khi upload; Blade tự recompile theo mtime. `resources/views/backend/*`, `public/js/backend/*`,
`public/css/backend/customize.css`, `product.min.css` và 8 file dist còn lại **đã khớp demo từ vòng trước**,
upload lại chỉ thừa. `.env` — **không sync**.

Bằng chứng trước khi upload (2026-10-27 09:5x):
`/_debugbar` còn 2 tham chiếu ở trang chủ và trang sản phẩm; `base.min.css` trên demo vẫn
`font-display:block`; trang chủ demo còn 13 tham chiếu `logo.svg`.

## Vòng 2026-10-27 — PageSpeed vòng 2 (chỉ 3 file)

| # | File local | Đường dẫn trên demo |
|---|---|---|
| 1 | `resources/views/web/layout.blade.php` | `resources/views/web/layout.blade.php` |
| 2 | `public/css-new/dist/home-v2.min.css` | `public/css-new/dist/home-v2.min.css` |
| 3 | `public/css-new/dist/layout.min.css` | `public/css-new/dist/layout.min.css` |

- **Không sync `.env`.**
- **Không cần clear cache**: 3 file trên không đụng `config`/`route`/`view` đã cache.
  `layout.blade.php` là view — nếu site đang bật LiteSpeed cache thì purge giúp
  HTML đổi sớm hơn, nhưng không bắt buộc.
- File `src` (`public/css-new/src/layout.css`, `home-v2.css`) upload hay không cũng được,
  chỉ có `dist` được browser tải.

Nội dung đã sửa:
1. `layout.blade.php`: preload 3 script `main.min.js` → `CustomGlide.min.js` → `glide.min.js`
   (cùng `?v=`) để phá chuỗi fetch nối tiếp; thêm `defer` cho script DMCA.
2. `home-v2.min.css`: `.hero-banner img { width:100% }` (bỏ 1920px giả định) và
   `.hero-banner .glide__slide { flex:0 0 100%; width:100% }` (chống CLS trước/sau JS).
3. `layout.min.css`: `.newsletter-form button { color: var(--navy); font-weight:700 }`
   — chữ trắng trên amber chỉ 1.86:1, navy trên amber 5.81:1 (axe color-contrast).

Bằng chứng trước khi upload (2026-10-27 11:0x): demo vẫn còn chuỗi script nối tiếp,
DMCA không `defer`, và nút "Đăng ký" footer vẫn `color: rgb(255,255,255)`.
Local sau sửa: computed style `.newsletter-form button` = `rgb(38,52,139)` trên
`rgb(255,174,13)`, `font-weight:700`; HTML có 3 preload + `defer`.

## Vòng 2026-10-27 — PageSpeed vòng 3 (inline CSS chặn render)

PSI mobile 72: FCP 3,0s + LCP 4,7s đỏ. Audit "Yêu cầu chặn hiển thị" = 20,2 KiB CSS
mất 1.810 ms (layout 10,1 + home-v2 2,5 + base 7,7). Sửa bằng cách nhúng thẳng CSS
vào `<style>` trong head -> 0 request chặn hiển thị, cùng số byte trên dây.

| # | File local | Đường dẫn trên demo | Thao tác |
|---|---|---|---|
| 1 | `app/Providers/AppServiceProvider.php` | `app/Providers/AppServiceProvider.php` | ghi đè |
| 2 | `resources/views/web/layout.blade.php` | `resources/views/web/layout.blade.php` | ghi đè |
| 3 | `resources/views/web/home/index.blade.php` | `resources/views/web/home/index.blade.php` | ghi đè |
| 4 | `public/css-new/dist/fancybox.min.css` | `public/css-new/dist/fancybox.min.css` | **file mới** (32.711 B) |

- **BẮT BUỘC clear cache sau khi upload**: vào `/backend/clear-cache` một lần.
  Vòng này có sửa `AppServiceProvider` (đăng ký directive Blade `@inlineCss`) và
  `layout.blade.php` đã đổi từ vòng 2 nhưng demo vẫn chạy bản cũ -> phải clear để
  view+provider được nạp lại.
- **Không sync `.env`.**
- Upload theo thứ tự 1 -> 2,3 -> 4 (provider trước để directive có sẵn).
- `public/css-new/dist/base.min.css`, `layout.min.css`, `home-v2.min.css` vẫn phải còn
  trên disk: các trang khác (danh mục, chi tiết, tin tức) vẫn đang `<link>` chúng.

Kiểm tra vòng 2 trên demo TRƯỚC khi upload vòng 3 (phát hiện demo chưa có vòng 2):
`main.min.js` vẫn bị cache `max-age=2592000` trả bản cũ (28.383 B, md5 `18354b4397506a15910cd8f91567c21d`,
`Expires: Tue, 03 Nov 2026`) trong khi `?v=1792982206` là bản mới (md5 `640b9d0b45650b128909565d6527200e`).
`home-v2.min.css` + `layout.min.css` vòng 2 thì ĐÃ lên đúng.

Bằng chứng local sau khi sửa:
`curl http://sandienmay.test/` -> HTML 330.839 B, 3 khối `<style>` = 31.207 + 43.094 + 9.553 B
(khớp đúng nội dung 3 file dist), `grep base.min.css` = 0, `grep jsdelivr` = 0.
Screenshot 520px: trang chủ render đúng, không vỡ layout.
Smoke 200: `/`, `/kumisai-kms-20b`, `/tin-tuc-may`.
Ước tính brotli: HTML cũ 10.405 B + CSS ~15.200 B = ~25.600 B (4 request)
-> HTML mới 25.588 B (1 request). Không tăng dung lượng, mất 3 round trip.

## Vòng 2026-10-27 — PageSpeed vòng 4 (CLS: Glide pre-mount + khai sai tỉ lệ ảnh)

PSI mục "Nguyên nhân làm thay đổi bố cục" tổng 0,395: `.cat-block` 0,166 + 0,153 + 0,077
và `img flash-sale.jpg` bị báo "chưa xác định kích thước".

Đo trên DOM local (nhân bản `.glide`, bỏ class `glide--slider` + style inline để xem
trạng thái TRƯỚC khi JS mount) -> chiều cao trươc/sau mount:

| Carousel | trươc sửa | sau sửa |
|---|---|---|
| Danh mục Máy hút bụi (16 slide) | 1341 -> 182 (**-1159px**) | 343 -> 343 (0) |
| Danh mục Máy chà sàn (4 slide) | 347 -> 182 (-165px) | 343 -> 343 (0) |
| Sản phẩm khuyến mãi (7 slide) | 321 -> 348 (+27px) | 348 -> 348 (0) |
| 5 carousel còn lại | 0 | 0 |

Hai nguyên nhân:
1. `home-v2.css:623-636` để `.glide:not(.glide--slider) .glide__slides { display: grid }`
   -> trước khi mount 16 slide xếp 8 hàng, Glide mount xong dồn về 1 hàng.
   Đã đổi sang ép `flex-basis` đúng perView trên từng slide (25% / 33.3333% / 50% theo
   breakpoint 1024 và 992 - khớp `CustomGlide.js:45-58`), thêm 3 rule cho `.sale-box`.
2. Khai `width/height` sai ảnh thật: hero khai 1920x500 nhưng ảnh 1440x529 (o chong
   cao thêm 41% khi decode), flash-sale khai 1216x447 nhưng ảnh 1216x204 (o chong thu
   183px -> 84px). Them `@imgAttrs($url, $fallbackW, $fallbackH)` doc kich thuoc that
   tren disk, ap cho hero + flash-sale + promo + anh ben phai khoi danh muc.

File thay doi: `app/Providers/AppServiceProvider.php`,
`resources/views/web/home/index.blade.php`, `public/css-new/src/home-v2.css`,
`public/css-new/dist/home-v2.min.css` (9.473 -> 9.745 B).

Chưa sửa (không nằm trên đường CLS mobile, chỉ ghi nhận):
- `components/fe/menu.blade.php:86` khai 26x26 cho anh 205x179 - o trong mega menu an.
- `/images/new.png` (badge 10x10) không có width/height.
- `/images/bct.png` khai 102x32, thật 172x64 -> lech ~6px ở footer.
- `.press-carousel` slide truoc mount rong 93px sau mount 279px (chieu cao khong doi
  nen khong gây CLS, chi là nhấp nháy thị giác trước khi JS chạy).

## Vòng 2026-10-27 — PageSpeed vòng 5 (CLS: hai "quả bom" ảnh không giữ chỗ)

Điểm tụt 72 -> 53 (CLS 0.085 -> 0.404, LCP 4.7 -> 6.2s). Đã dựng được harness đo
layout-shift thật trên máy (xem mục "Cách đo" cuối file) và tìm ra 2 nguyên nhân,
cả hai đều ở CSS chứ không phải Blade.

### Nguyên nhân 1 — rule `aspect-ratio: 0` trong base.css (mobile)
```css
@media (max-width: 992px) { .glide__slide picture img { aspect-ratio: 0 } }
```
`base.css:2106-2109`. Với ảnh CHƯA decode, ratio 0 => chiều cao ô ảnh = 0. Ảnh decode
xong Chrome mới dùng tỉ lệ thật. Kết quả đo trên HTML của demo, viewport 360px:

| thời điểm | `.hero-banner` |
|---|---|
| t = 84ms (ảnh chưa về) | **0px** |
| t = 362ms (ảnh decode) | **183.7px** |

Toàn bộ nội dung bên dưới (wide-banner, sale-section, cat-block…) nhảy xuống 184px
=> 1 cú shift **0.2055**. Rule này đã bị xoá khỏi `base.css`.

### Nguyên nhân 2 — ô ảnh card sản phẩm không giữ chỗ
`layout.css`: `.product-card .product-img-main { width:auto; height:auto; max-width:100%;
max-height:100% }`. Với `width/height auto`, khai `width="214" height="214"` trong
`components/fe/products.blade.php` không giúp gì: đo được ô ảnh **0x0 trước decode ->
189x165 sau decode** (ảnh thật là `demo-sp-1.png` 205x179, `demo-sp-2.png` 200x173).
Card sản phẩm cao thêm 147px => shift **0.0745**, và xảy ra với MỌI card trên mọi trang.

Đã sửa: cho chính cái ô (`wrapper`) giữ hình vuông, ảnh chỉ việc `contain` vào trong:
```css
.product-card .product-image-wrapper { position: relative; overflow: hidden; aspect-ratio: 1 / 1; }
.product-card .product-img-main { width: 100%; height: 100%; display: block; object-fit: contain; }
```
Không còn phụ thuộc kích thước ảnh upload lên (ảnh 150x150, 671x969… đều vào cùng 1 ô).

### Kết quả đo lại (cùng harness, cùng HTML demo, viewport 360px)
| | CLS | hero | ô ảnh card |
|---|---|---|---|
| Trước | 0.28 (0.2055 + 0.0745) | 0 -> 184 | 0x0 -> 189x165 |
| Sau | **0.0000** | 183.7 ngay từ frame đầu | 189x189 ngay từ frame đầu |

Card cao hơn ~16px so với trước (ô vuông 189 thay vì 165). Đã xem screenshot 360 /
500 / 1280 ở trang chủ, trang danh mục: ảnh nằm giữa ô, các card đều nhau.

### File upload (2 file, không sửa Blade, không cần clear-cache)
- `public/css-new/dist/base.min.css` (31.207 -> 31.166 B, đã kiểm tra không còn `aspect-ratio:0`)
- `public/css-new/dist/layout.min.css` (43.096 -> 43.096 B, cùng dung lượng nhưng khác nội dung)
KHÔNG sync `.env`. Không có SQL.

### Cách đo (để vòng sau còn dùng)
1. `curl` HTML demo về `demo-r5.html` (brotli đã giải nén).
2. Script `mk-mode.js`: đổi mọi `src|href="/storage|images|css-new|js-new|..."` thành
   URL tuyệt đối, sửa/xoá rule CSS ngay trong `<style>` inline (A/B tại chỗ, không cần
   upload), nối `probe-js.txt` vào trước `</head>`.
3. `probe-js.txt` = `PerformanceObserver({type:'layout-shift', buffered:true})` lấy `value`,
   `startTime`, `sources[].previousRect/currentRect` + một `setInterval` 5ms quay chiều
   cao các block chính để biết block nào nở ra.
4. `chrome --headless=new --window-size=360,900 --disk-cache-size=1
   --virtual-time-budget=11000 --dump-dom file:///.../cls-both.html` rồi bắt chuỗi
   `CLSREPORT{...}ENDCLS` trong DOM.
   Lưu ý: `--window-size` rộng nhất vẫn bị Chrome ép tối thiểu 500px, nhưng media query
   `max-width:992px` vẫn chạy đúng nên layout mobile giữ nguyên.

### Việc còn lại của PageSpeed (không phải CLS)
- LCP 6.2s: ảnh hero `storage/1793001326_6adf076e39bd4.jpg` **245.642 B** cho màn 360px.
  Đây là phase ảnh (đã hẹn làm cuối) + cần `image_mobile` trong `/backend/banner`.
- FCP 3.2s: 84KB CSS giờ nằm inline trong HTML (`base` 31.2 + `layout` 43.1 + `home-v2` 9.7)
  => không còn request chặn render nhưng parser phải đọc hết trước khi vẽ. Đổi lại đã bỏ
  được 20.2 KiB render-blocking, nên giữ.
- "Forced reflow" 84ms + 37ms từ `glide.min.js`: chưa sửa.

---

## Vòng 2026-10-27 (2) — Re-skin header + mega menu desktop (theo mẫu cellphones)

### Đã đổi gì
- **Bỏ hàng nav ngang** `.header-bottom` (43px) khỏi `web/header.blade.php`; thay bằng
  nút **"Danh mục"** màu navy ngay cạnh logo. Hàng header: logo | Danh mục | ô search lớn
  ("Bạn muốn mua gì hôm nay?") | Hotline | Giỏ hàng. Không có "Hồ Chí Minh", không có cột
  "Ưu đãi"; vị trí "Đăng nhập" trên mẫu dùng cho **hotline** (site không có đăng nhập).
- **Panel mega** (`<x-fe.menu mode="mega">`, absolute `top:100%` nên không đẩy nội dung):
  rail trái 14 danh mục cấp 1 (icon + tên + dấu mũi tên, tự cuộn vì cao hơn cột phải),
  khu phải chia cột theo `m_categories.display_type`: `brand` → THƯƠNG HIỆU (tile nền navy
  vì logo PNG chữ trắng), `attribute` → NHU CẦU (chip có thumb), `spec` → THÔNG SỐ,
  `price` → PHÂN KHÚC GIÁ (chip chữ). Nhãn HOT/NEW giữ nguyên quy tắc chỉ cấp 2.
- Mở/đóng bằng **click** nút "Danh mục"; hover rail đổi cột; Esc / click ngoài / focus vào
  ô search thì đóng. Mobile (<=992px) **không đổi**: vẫn drawer `#modelMenuMobi`,
  `.mega` và `.cate-btn` là `display:none`.
- `display_type` thêm 2 giá trị mới `spec`, `price` ở select "Kiểu danh mục" trong
  form thêm/sửa danh mục (không cần migration, cột là varchar(50)).
- Offset nội dung dưới header fixed: `.main { margin-top }` **133px -> 90px** ở 4 nơi
  (`base.css`, `layout.css`, `category-v2.css`, `post-v2.css`).
- Xoá class JS `NavSlider` trong `main.js` (nó chỉ phục vụ `.header-bottom .main-nav` đã bỏ),
  thay bằng `MegaMenu`.

### Số liệu đo tại local (CDP, 1440x900)
| | trước | sau |
|---|---|---|
| Chiều cao header fixed | 133px | **90px** |
| `.main` margin-top | 133px | 90px (đo trên trang chủ + danh mục + chi tiết SP) |
| CLS desktop 1440 | 0 | **0.0006** (panel absolute nên không gây shift) |
| CLS mobile 390 | 0 | 0.018 — lỗi cũ của `SECTION.cat-block` (255->275px) + phpdebugbar, không liên quan header |
| Panel mega | — | cao 425px, rail 14 mục, pane đầu tiên 4 cột 2/3/4/4 item |
| Tài nguyên 404 khi mở panel | — | 0 |

### File cần upload (9 file)
Blade/PHP:
- `app/View/Components/Fe/Menu.php`
- `resources/views/components/fe/menu.blade.php`
- `resources/views/web/header.blade.php`
- `resources/views/backend/category/create.blade.php` *(chỉ admin)*
- `resources/views/backend/category/edit.blade.php` *(chỉ admin)*

CSS/JS đã build (bản dist, không upload file src):
- `public/css-new/dist/layout.min.css` (43.096 -> 47.367 B — thêm block mega)
- `public/css-new/dist/base.min.css` (31.166 -> 31.165 B — `.main` 125 -> 90)
- `public/css-new/dist/category-v2.min.css` (`.main` 133 -> 90)
- `public/css-new/dist/post-v2.min.css` (`.main` 133 -> 90)
- `public/js-new/fe/main.min.js` (18.837 B)

Thiếu `category-v2.min.css` / `post-v2.min.css` thì 2 trang đó bị **thừa 43px** dưới header.

### Pre-flight
- **Không có SQL bắt buộc**: `display_type` là varchar(50) tự do, `is_menu`/`badge` đã có sẵn.
- Không sync `.env`. Sau khi upload: mở `/clear-cache` một lần (Blade + config cache).
- **Không upload `deploy/seed-menu-demo.php`** — đó là fixture local.
- Dữ liệu demo hiện tại trên host mới chỉ có 1 danh mục cấp 1 có con nên panel mega sẽ trống
  cột phải; muốn giống local thì phải bật `is_menu` + tạo danh mục con trong `/backend/category`.

### Hoàn tác fixture local (khi nào cần)
```sql
delete from m_categories where seo_keywords = 'ZZ-MENU-FIXTURE';  -- 171 row (3 cấp 1 + 168 con)
-- 2 row ảnh logo đã đổi tay (không nằm trong marker):
update m_categories set image = 'demo-sp-1.png' where id = 359;
update m_categories set image = 'demo-sp-2.png' where id = 360;
```
Chạy lại fixture: `php deploy/seed-menu-demo.php` (idempotent, lần 2 báo "Tong row tao moi: 0").

---

## Vòng 2026-10-28 — Tinh chỉnh panel mega cho sát ảnh 3 (desktop)

So lại với ảnh mẫu, panel hôm trước còn 4 chỗ lệch. Đã sửa:

- **Panel là card nổi bo góc**, không phải dải trắng full ngang: `.mega` trong suốt,
  `.mega-inner` mới có nền trắng + viền `#E9EDF7` + `border-radius:14px` + shadow,
  nằm trong container và cách mép dưới 18px.
  *(Vòng sau bỏ `padding:0 40px` ở `.mega .container` để card thẳng hàng lưới trang — xem mục 2026-10-28 (2).)*
- **Chip nội dung**: nền xám `#F5F6FA` -> **nền trắng + viền nhạt**, cao tối thiểu 42px,
  hover viền xanh navy + shadow nhẹ (mẫu dùng chip trắng viền).
- **Tiêu đề nhóm**: hết HOA HẾT chữ xám nhỏ -> **Title Case, 15px, 600, màu ink**
  ("Thương hiệu", "Nhu cầu", "Thông số", "Phân khúc giá"). Blade mega in `$groupLabels`
  trực tiếp; drawer mobile vẫn giữ hoa hết như cũ.
- **Chiều cao card bám nội dung**: bỏ `max-height:424px` hard-code ở rail, JS `syncHeight()`
  đo `.mega-pane` đang hiện rồi gán `--mega-h` cho rail (sàn 420px). Trước đây rail và
  cột phải độc lập nên card luôn thừa ~170px khoảng trắng.
- **Bug lệch cột**: 3 nhóm bên phải bị tụt 20px so với nhóm đầu, nguyên nhân là rule cũ
  `.home-v2 section + section { margin-top: 20px }` (viết cho các section toàn trang chủ)
  đánh trúng `section.mega-group`. Đã đè bằng `.mega-groups > .mega-group { margin: 0 }`
  (cần selector 2 class mới thắng độ ưu tiên).

Giữ nguyên: tile thương hiệu **nền navy** — đo 4 file logo trong `public/storage/brands/`
thấy avgRGB (255,255,255), tức logo chữ trắng trên nền trong suốt, đặt lên nền trắng là mất hút.

### Số đo sau khi sửa (CDP)
| | 1440 | 1280 | 1024 | 500 |
|---|---|---|---|---|
| Chiều cao card mega | 422 | 458 | 452 | ẩn |
| Số cột nhóm | 4 | 3 | 2 | — |
| Tiêu đề nhóm thẳng hàng | top 111 cả 4 cột | — | — | — |
| Header / `.main` margin-top | 90 / 90 | 90 / 90 | 90 / 90 | 0 (header mobile) |
| 404 tài nguyên khi mở panel | 0 | 0 | 0 | 0 |
| Drawer mobile | mở, accordion 569px, 13 item, badge HOT/HOT/NEW | | | |
| CLS | 0.018 (lỗi cũ `SECTION.cat-block`, không đổi so với trước) | | | |

### File upload vòng này
Danh sách cuối cùng (đã cộng cả vòng 2026-10-28 (2) bên dưới) nằm ở mục **(2)** — vòng này
chưa kịp upload thì chỉ cần upload theo danh sách đó, không cần tách riêng.

`base.min.css` (31.165 B), `category-v2.min.css` (9.737 B), `post-v2.min.css` (12.642 B)
và 4 file Blade/PHP còn lại của vòng trước **không đổi** — nếu chưa upload vòng trước thì
upload gộp cả 9 file danh sách ở trên.
Pre-flight y hệt vòng trước: không sync `.env`, mở `/clear-cache` sau khi upload.

---

## Vòng 2026-10-28 (2) — Sidebar danh mục dọc + banner ở trang chủ (desktop)

Theo ảnh mẫu cellphones trang chủ: **bên trái là menu danh mục dựng đứng, bên phải là vùng
banner**. Trước đó trang chủ chỉ có dải banner full ngang.

Đã làm:

- **`x-fe.menu` thêm `mode="sidebar"`**: render rail (`.mega-rail`) + cột nội dung (`.mega-body`)
  bằng đúng markup của panel mega, nên icon + tên + mũi tên `>` và các nhóm Thương hiệu/Nhu cầu/
  Thông số/Phân khúc giá giống hệt trong header.
- **Blade trang chủ**: `hero-banner` + `promo-section` (3 banner phụ) được gói vào
  `.home-hero > .container > .home-hero-grid` gồm `[.home-side] [.home-hero-main]`.
  `hero-banner` và `promo-section` chuyển từ `<section>` thành `<div>` để rule
  `.home-v2 section + section { margin-top:20px }` không còn chen vào giữa 2 khối
  (khoảng cách giờ do grid quản lý).
- **Submenu của sidebar là khối RIÊNG, tách tuyệt đối khỏi header** (ý chính của vòng này):
  class `SideMenu` trong main.js chỉ nhìn `.home-side`, hover một mục thì bật `.home-side.is-open`
  + đổi `.mega-pane.is-active` **bên trong sidebar**. Không đụng `mega-open`, không đụng panel
  mega của nút "Danh mục". Đã gỡ hết cây nối vòng trước (`railItems` gom 2 rail,
  `open(true)`, 3 rule `.mega-from-side` trong layout.css).
- **Vị trí submenu**: `.mega-body` của sidebar là `position:absolute` nhưng thoát lên
  `.home-hero-grid` làm vùng chứa (`.home-side`/`.mega-inner` giữ `position:static` và
  `.mega-inner { overflow:visible }`), `top:0; bottom:0; left:284px; right:0` =>
  **rect bằng khít cột banner** (284 = 268 card sidebar + 16 khoảng trống). Nội dung dài
  quá thì cột submenu tự cuộn.
- **Không sửa gì panel mega của nút "Danh mục"** (user yêu cầu): vẫn giữ `padding:0 40px` ở
  `.mega .container` và toàn bộ rule cũ. Đo lại ở 1440px, bấm nút "Danh mục" trên **trang chủ
  và trang danh mục đều ra card `[90, 76, 1288, 422]`** — giống hệt vòng 2026-10-28 (1).
- **Đóng submenu sidebar**: `mouseleave` hẹn 150ms (`.mega-body` là con của `.home-side` nên rê
  chuột từ sidebar sang submenu không bị coi là rời khỏi sidebar), Escape cũng đóng.
- **Dòng đang hover trong sidebar**: mẫu dùng nền xám nhạt + chữ giữ nguyên màu, nên
  `.home-side` đè rule `.is-active` của rail header (bỏ nền xanh `#EEF3FF` + vạch xanh trái).
- **Khai lại bảng màu trên `.home-side`**: `--chip/--line/--ink/--navy` vốn chỉ được khai báo
  trong `.site-header` (layout.css) mà sidebar nằm ngoài header — thiếu `--chip` thì hover mất
  nền, thiếu `--line` thì mất viền card.
- **Chỉ hiện từ 1200px trở lên** (`.home-side { display:none }` dưới 1200) — ở 1024px cột banner
  chỉ còn ~700px, banner 1920x500 bị bé quá.
- **Giá phải trả (đã đo, chấp nhận được)**: vì submenu là khối riêng nên HTML của 14 `.mega-pane`
  bị nhân đôi **ở riêng trang chủ**: `+180.556 B` thô, nhưng **gzip chỉ +3.819 B** và
  **brotli +49 B** (khối lặp lại nén gần hết). Trang khác không có sidebar nên không tăng
  (trang danh mục 633.654 B thô, bằng trước khi làm). CLS 1440 = 0.0006, 390 = 0.0183 — không đổi.
- **Mobile ≤992px giữ nguyên như cũ**: sidebar ẩn, `.home-hero > .container` padding 0 nên
  banner LCP vẫn là dải full ngang, `.promo-section` vẫn ẩn.

Không có gì phải revert ở admin: **đã xoá** option "Mega menu" (position 3) khỏi
`backend/banner/create.blade.php` + `edit.blade.php`, và fixture 4 dòng `m_banners` position 3
ở local đã xoá sạch (`delete from m_banners where position = 3`).

### Số đo sau khi làm (CDP, localhost)
| | 1440 | 1280 | 1200 | 1024 | 992 | 390 |
|---|---|---|---|---|---|---|
| Sidebar hiện? | có | có | có | ẩn | ẩn | ẩn |
| Card sidebar (rộng × cao) | 268 × 646 | 268 × 646 | 268 × 646 | — | — | — |
| Số mục trong sidebar | 14 (14 × 46px) | 14 | 14 | — | — | — |
| Mép trái banner = mép phải sidebar + 16 | 332 ✓ | 328 ✓ | 329 ✓ | 40 (full container) | 25 | 0 (full ngang) |
| Submenu hover `[top, left, rộng, cao]` | 90, 332, 1060, 646 | 90, 328, 908, 646 | 90, 329, 826, 646 | — | — | — |
| = đúng rect banner chính? | ✓ | ✓ | ✓ (không còn lệch 3px như bản cũ) | — | — | — |
| `mega-open` của header khi hover sidebar | false (tách riêng) | false | false | — | — | — |
| Banner chính (rộng × cao) | 1053 × 389 | 908 × 334 | 819 × 303 | 936 × 346 | 936 × 346 | 390 × 143 |
| Card mega khi BẤM nút "Danh mục" 1440 | 90, 76, 1288, 422 | không đổi so với vòng (1) ở cả trang chủ lẫn trang danh mục | | | | |
| 404 tài nguyên | 0 | 0 | 0 | 0 | 0 | 0 |
| Lỗi JS (Runtime.exceptionThrown) | 0 | 0 | 0 | — | — | 0 |
| CLS trang chủ | 0.0006 | — | — | — | — | 0.0183 (lỗi cũ `SECTION.cat-block`) |
| Drawer mobile `#modelMenuMobi` | | | | | | 14 item, nguyên vẹn |

Ghi chú: submenu cao bằng cả cột (646px) vì `.home-hero-grid` lấy chiều cao của cột dài nhất
(sidebar 14 mục). Ở dữ liệu local mỗi danh mục chỉ có vài nhóm nên card còn thừa khoảng trắng
phía dưới — đúng mẫu, nội dung dài hơn thì cột submenu tự cuộn.

Ghi chú: ở khoảng 768–1199px banner từ "dải full ngang" thành "nằm trong lưới container"
(lệch ~20–40px mỗi bên) — hệ quả của việc nó được chuyển vào `.container`. Điện thoại
(≤ ~500px) không đổi, vẫn full ngang.

### File upload vòng này
Danh sách cuối cùng (đã gồm cả vòng (3) bên dưới) nằm ở mục "File upload vòng 2026-10-28 (3)".

Nếu **chưa** upload vòng 2026-10-28 (1) thì chỉ cần lấy 2 file CSS/JS ở trên là đủ
(vòng (1) và (2) đã gộp lại). `app/View/Components/Fe/Menu.php` vòng này chỉ còn khác bản
trên host đúng 1 dòng comment — không bắt buộc upload.

### Pre-flight
- **Không sync `.env`.**
- **Không có SQL** cho vòng này.
- Sau khi upload mở `/backend/clear-cache` một lần (Blade cache).
- Trang chủ demo cần có banner vị trí Top (1 ảnh) và Midde (3 ảnh) trong `/backend/banner`
  thì cột phải mới đầy; không có banner thì chỉ còn sidebar.
- Kiểm tra nhanh: ở ≥1200px, rê chuột vào "Máy chà sàn" ở sidebar -> panel nhóm bật ra ngay
  bên phải sidebar, banner bị che tạm; rê ra ngoài ~0,15s panel đóng.

---

## Vòng 2026-10-28 (3) — Lớp phủ đen mờ + khoá scroll khi mở panel "Danh mục"

Chỉ áp dụng cho **nút "Danh mục" trên header** (user chốt). Sidebar ở khối banner trang chủ vẫn
hover mở submenu, click vào tên vẫn vào trang danh mục, **không có lớp phủ**.

Đã làm:

- **`resources/views/web/header.blade.php`**: thêm `<div class="mega-scrim" id="megaScrim">`
  ngay **sau `</header>`**. Bắt buộc đặt ngoài header vì `.header-hide` dùng `transform`, và
  phần tử có transform biến thành containing block của `position:fixed` — đặt trong header thì
  scrim chỉ phủ đúng dải 90px của header (đã đo và đã gặp).
- **`layout.css`** (trong `@media (min-width:993px)`): `.mega-scrim { position:fixed; inset:0;
  z-index:9998; background:#0000009a }` — thấp hơn `.site-header` (9999) nên thanh header và
  panel mega vẫn sáng; `body:has(.site-header.mega-open) .mega-scrim { display:block }`.
- **Khoá scroll**: `html:has(.site-header.mega-open) { overflow:hidden }` — theo đúng cách
  drawer mobile đang khoá (`html:has(.has-backdrop[open])`), không thêm class JS nào.
- **Không có JS mới**: lớp phủ nằm ngoài `.site-header` nên handler "click ra ngoài là đóng"
  sẵn có trong `MegaMenu` đã xử lý luôn; Escape và bấm lại nút cũng đã có từ trước.
- **`html { scrollbar-gutter: stable }`** (cũng trong media query ≥993px): site đặt
  `::-webkit-scrollbar { width:7px }` nên lúc mở panel, `overflow:hidden` làm ẩn scrollbar và
  **mọi thứ nở thêm 7px** — header 1433 -> 1440, `.main .container` 1361 -> 1368, nút "Danh mục"
  nhảy từ trái 203 -> 205. Giữ choáng 7px ổn định thì hết giật (đo A/B ở bảng dưới).

### A/B `scrollbar-gutter` (CDP, trang danh mục 1440, [left, width])
| | header | `.header-top .container` | nút Danh mục | `.main .container` | panel mega (khi mở) |
|---|---|---|---|---|---|
| `stable`, đang đóng | 0, 1433 | 36, 1361 | 203, 143 | 36, 1361 | — (ẩn) |
| `stable`, đang mở | 0, **1433** | 36, **1361** | **203**, 143 | 36, **1361** | 76, 1281 |
| `auto` (mặc định cũ), đóng | 0, 1433 | 36, 1361 | 203, 143 | 36, 1361 | — |
| `auto`, đang mở | 0, **1440** | 36, **1368** | **205**, 143 | 36, **1368** | 76, 1288 |

=> với `stable`, đóng/mở **không dịch một px nào**; panel khi mở hẹp hơn 7px (1281 so với 1288)
và đổi lại là đúng bằng lưới container của trang. Không có `stable` thì cả trang giật 7px mỗi lần
mở menu.

### Hành vi đã đo (CDP, trang chủ 1440)
| Bước | Kết quả |
|---|---|
| Bấm nút "Danh mục" | scrim `[0, 0, 1433, 900]` phủ kín viewport, `display:block`, nền `rgba(0,0,0,.6)` |
| `elementFromPoint(700,700)` | `DIV.mega-scrim` (trang bị chặn, không bấm xuyên được) |
| `elementFromPoint(700,200)` / `(100,45)` | `PANEL` / `HEADER` — panel và thanh header vẫn trên scrim |
| Wheel 400px khi đang mở | `scrollY` giữ nguyên 0, `html overflow: hidden` |
| Bấm thật vào scrim | panel + scrim đóng, `overflow:visible`, cuộn lại được (`scrollY` 1200) |
| Bấm nút lần 2 / Escape | đóng, scrim `display:none` |
| 390px | `.cate-btn` ẩn nên scrim không bao giờ bật; banner LCP vẫn 390×143, sidebar ẩn |
| CLS | 1440 = 0.0006, 390 = 0.0183 — không đổi |
| Lỗi JS / 404 tài nguyên | 0 / 0 |

### File upload vòng 2026-10-28 (3) — 6 file (gộp toàn bộ vòng (2))
> Kích thước 2 file CSS dưới đây là của vòng (3); bản mới nhất (vòng (4)) là
> `layout.min.css 47.736 B` và `home-v2.min.css 11.298 B` — upload theo danh sách vòng (4).

- `public/css-new/dist/layout.min.css` — **47.843 B**
- `public/css-new/dist/home-v2.min.css` — **10.970 B**
- `public/js-new/fe/main.min.js` — **20.357 B**
- `resources/views/components/fe/menu.blade.php`
- `resources/views/web/home/index.blade.php`
- `resources/views/web/header.blade.php`

### Pre-flight vòng (3)
- **Không sync `.env`.** Không có SQL.
- Mở `/backend/clear-cache` sau khi upload (Blade cache của header + home + component).
- Kiểm nhanh: bấm "Danh mục" -> trang tối đi, cuộn bằng bánh xe không được; bấm ra ngoài -> sáng lại.
- Safari < 15.4 / Chrome < 105 không hỗ trợ `:has()`: lớp phủ sẽ không hiện (menu vẫn mở/đóng
  bình thường bằng nút và Escape). `scrollbar-gutter` cũng cần Chrome 94+ — trình duyệt cũ thì
  chỉ bị giật 7px lúc mở, không hỏng gì.

## Vòng 2026-10-28 (4) — Sidebar danh mục cao đúng bằng cột banner (+ sửa 20px đầu trang)

Yêu cầu: *"bên danh mục để height bằng vs banner, nếu mà nhiều danh mục hơn thì dùng scroll"*.

Đã làm — **chỉ 1 file nguồn CSS: `public/css-new/src/home-v2.css`** (block `@media (min-width:1200px)`):

- **Ép chiều cao bằng cột banner**: `.home-hero-grid` đổi `align-items` sang `stretch` và
  `.home-side .mega-inner { height:0; min-height:100% }`. Mẹo `height:0` là để card sidebar
  **không góp phần quyết định chiều cao dòng lưới** — nếu không thì chính nó 646px kéo cả lưới
  theo. Dùng `:has(.hero-banner, .promo-section)` để chỉ ép khi thật sự có banner: trang chủ
  không có banner thì cột phải cao 0, ép stretch sẽ làm sidebar sập.
- **Rail tự cuộn**: `.home-side .mega-rail { max-height:none; overflow-y:auto;
  overscroll-behavior:contain; scrollbar-width:thin }` — bỏ `max-height: var(--mega-h)` (đó là
  chiều cao panel header), bỏ viền phải vì card không còn nối với panel nào.
- **20px đầu trang bị header che** (lỗi cũ, phát hiện khi đo): từ 993px header `fixed` cao **90px**
  nhưng `.main` chỉ `margin-top:70px` (`base.css:2405` + `layout.css:1391`). Trang danh mục và bài
  viết đã override `90px` từ trước (`category-v2.css:25`, `post-v2.css:22`), **riêng trang chủ
  thiếu** → 20px trên cùng của sidebar + hero nằm đè dưới header. Thêm
  `@media (min-width:993px){ .home-v2 .main { margin-top:90px } }`.

### Đo (CDP, `http://sandienmay.test/`, [top, left, width, height])
| | header | sidebar card | cột banner (hero+promo) | submenu flyout | rail `scroll/client` |
|---|---|---|---|---|---|
| 1200px | 0, 0, 1193, 90 | 90, 45, 268, **423** | 90, 329, 819, **423** | 90, 329, 819, **423** | **617 / 421** → cuộn |
| 1280px | 0, 0, 1273, 90 | 90, 44, 268, **465** | 90, 328, 901, **465** | 90, 328, 901, **465** | **617 / 463** → cuộn |
| 1440px | 0, 0, 1433, 90 | 90, 48, 268, **542** | 90, 332, 1053, **542** | 90, 332, 1053, **542** | **617 / 540** → cuộn |

Card sidebar và cột banner **cao bằng nhau tuyệt đối**, submenu bay ra phủ **khít đúng** cột banner
(cùng top/left/width/height) ở cả 3 mốc; `scrollWidth` không vượt `innerWidth` (không vỡ ngang).
14 danh mục cấp 1 = 617px > chiều cao khả dụng nên rail có thanh cuộn mỏng; item cuối bị cắt một
phần ở đáy card là hành vi cuộn đúng.

`mainTop` sau khi sửa: 993 / 1100 / 1200 / 1440 / 1920 đều **90 = headerBottom**; 992 trở xuống vẫn
70 = headerBottom (header mobile cao 70, `.main` margin 0) — không đổi.

Không có lỗi JS (`Runtime.exceptionThrown` 0), không 404, panel mega header vẫn mở/đóng + scrim
vẫn chạy, `.home-side` vẫn `display:none` dưới 1200px.

### File upload vòng (4) — 3 file
- `public/css-new/dist/home-v2.min.css` — **11.298 B**
- `public/css-new/dist/layout.min.css` — **47.736 B**
- `public/js-new/fe/main.min.js` — **20.357 B**

Blade **không đổi** ở vòng này, nên nếu đã upload đủ 6 file vòng (3) thì chỉ cần 2 file CSS.

### Pre-flight vòng (4)
- **Không sync `.env`.** Không có SQL. Không cần `/backend/clear-cache` (không sửa Blade).
- Kiểm nhanh: mở trang chủ ở desktop ≥1200px -> đáy card "Danh mục" ngang đáy 3 banner nhỏ,
  rê chuột vào sidebar -> submenu phủ đúng vùng banner; cuộn trong sidebar được nhưng trang
  không cuộn theo.
- Trang chủ demo phải có banner Top (1 ảnh) + Midde (3 ảnh) thì cột banner mới cao; thiếu banner
  thì sidebar giữ chiều cao tự nhiên (theo thiết kế của rule `:has()`).

---

## Vòng 2026-10-28 (5) — "Phân khúc giá" + "Thông số" thành bộ lọc thật

Yêu cầu: *"hover máy hút bụi công nghiệp mà chọn dưới 3tr thì phải ra bộ lọc sản phẩm máy hút bụi
công nghiệp dưới 3tr"*, và *"trong danh mục cha có thêm phần các mức giá (nút từ … tới …), thông số
thì có cột thông số cho nhập ở đó"*. **Thương hiệu và Nhu cầu giữ nguyên, không sửa.**

Cách làm: bỏ hẳn kiểu "tạo danh mục con display_type=price/spec" (88 danh mục fixture ở demo đều có
**0 sản phẩm**, bấm vào là trang trống). Thay bằng **2 bảng cấu hình gắn vào danh mục cấp 1**:

- `m_category_price_bands` — mỗi dòng `label` + `min_price` + `max_price` → cột "Phân khúc giá".
- `m_category_specs` — mỗi dòng trỏ một `m_attribute_options` có sẵn → cột "Thông số".

Chip trong menu (mega header, sidebar trang chủ, drawer mobile) giờ là link thật:
`/may-hut-bui-cong-nghiep?price_range=0-5000000` và `?attr=9001`. Cùng 2 tham số đó được bộ lọc
trong trang dùng lại, nên bấm từ menu xong con chip vẫn sáng và đổi lựa chọn không tải lại trang.
Danh mục cấp 1 **chưa cấu hình mức giá** thì cột "Khoảng giá" trong trang tự rơi về 7 khoảng enum cũ.

### File upload vòng (5) — 14 file
PHP (9):
- `app/Helpers/CategoryFilterConfig.php` — **mới**
- `app/Models/CategoryPriceBand.php` — **mới**
- `app/Models/CategorySpec.php` — **mới**
- `app/Models/Category.php` — thêm 2 quan hệ `priceBands()` / `specs()`
- `app/Traits/ActivityLoggable.php` — loại 2 model mới khỏi log
- `app/Services/FeServices/CategoryService.php` — thêm `getFilterConfig()` (cache `fe_menu_filters_v1` 600s)
- `app/View/Components/Fe/Menu.php` — 2 cột giá/thông số sinh từ 2 bảng trên
- `app/Http/Controllers/Backend/CategoryController.php` — `edit()` đưa cấu hình vào form, `update()` lưu
- `app/Http/Controllers/Frontend/New_ProductController.php` — nhận `price_range` / `attr`

Blade (3):
- `resources/views/backend/category/edit.blade.php` — 2 card "Các mức giá" + "Thông số"
- `resources/views/components/fe/menu.blade.php` — nhãn nhóm nhỏ `.mega-spec-attr`
- `resources/views/web/product/category.blade.php` — khối lọc "Khoảng giá" (cấu hình theo danh mục cha,
  fallback 7 khoảng enum). **Thông số không hiển thị trong khối lọc** — xem mục "Cập nhật 2026-10-29" dưới.

CSS build (1):
- `public/css-new/dist/layout.min.css` — **47.877 B** (tăng 141 B so với vòng (4))

Migration (1) — chỉ để site có shell sau này chạy được, demo thì tạo bảng bằng SQL ở dưới:
- `database/migrations/2026_10_28_170000_create_category_price_bands_and_specs_tables.php` — **mới**

`home-v2.min.css` (11.298 B) và `main.min.js` (20.357 B) vòng này **không đổi**.

### SQL pre-flight vòng (5) — chạy TRƯỚC khi upload code
Trên host không có shell → tạo 2 bảng bằng tay trong phpMyAdmin (giống hệt schema local):

```sql
CREATE TABLE `m_category_price_bands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `label` varchar(100) NOT NULL,
  `min_price` bigint unsigned DEFAULT NULL,
  `max_price` bigint unsigned DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_category_price_bands_category_id_index` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `m_category_specs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `option_id` bigint unsigned NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `m_category_specs_category_id_option_id_unique` (`category_id`,`option_id`),
  KEY `m_category_specs_category_id_index` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Kiểm tra trước khi chạy: `SHOW TABLES LIKE 'm_category_%';` — 2 bảng này **phải chưa tồn tại**.
Nếu demo có chạy `php artisan migrate` trong tương lai thì cần chèn thêm 1 dòng vào bảng
`migrations` (`2026_10_28_170000_create_category_price_bands_and_specs_tables`, `batch` = `MAX(batch)+1`)
để Laravel không tạo lại bảng.

Dọn danh mục fixture price/spec (local đã chạy, **88 dòng**, tất cả đều 0 sản phẩm và không có con):

```sql
-- Xem trước số dòng sẽ bị ẩn:
SELECT id, parent_id, name, display_type FROM m_categories
 WHERE type='product' AND display_type IN ('price','spec');

UPDATE m_categories SET is_menu=0, status='inactived'
 WHERE type='product' AND display_type IN ('price','spec');
```

Demo **không có** 88 danh mục đó (chúng chỉ được tạo ở local) thì câu UPDATE là no-op, cứ chạy cho chắc.
Không có câu SQL nào để "chuyển dữ liệu" — cấu hình mức giá/thông số do admin nhập lại trên demo.

### Sau khi upload
1. `/backend/clear-cache` (có 3 Blade mới + cache `fe_menu_filters_v1`).
2. Vào `Danh mục → sản phẩm → sửa` một danh mục **cấp 1** → 2 card "Các mức giá" và "Thông số" nằm
   giữa card "Mã ưu đãi" và card "SEO". Danh mục con không hiện 2 card này (đúng chủ đích).
3. Thêm vài mức giá (min/max chỉ cần nhập 1 ô, dạng `5.000.000`), chọn thông số trong select2, **Lưu**.
   Quay lại trang sửa → giá trị phải còn nguyên.

### Kết quả đo local (http://sandienmay.test, danh mục 348 cấu hình 4 mức giá + 4 thông số)
| Kiểm tra | Kết quả |
|---|---|
| Mega header 1440px | 4 cột: Thương hiệu / Nhu cầu / **Thông số** (4 chip, có nhãn nhóm Công suất, Số động cơ, Điện áp, Dung tích thùng chứa) / **Phân khúc giá** (4 chip) |
| Sidebar trang chủ 1440px | flyout phủ khít cột banner `[90, 332, 1053, 542]`, 4 cột như header, `overflowX: false` |
| Drawer mobile 390px | 8 link lọc (`4 price_range` + `4 attr`) hiện trong `#modelMenuMobi` |
| Bấm chip "Dưới 5 triệu" | vào `/may-hut-bui-cong-nghiep?price_range=0-5000000` → "Tìm thấy 6 sản phẩm", chip sáng |
| Bấm chip "5500W" | `?attr=9001` → "Tìm thấy 1 sản phẩm" |
| Đổi lọc tại chỗ (AJAX) | 6 → bấm "Trên 20 triệu" → 3 → thêm "5500W" → 0; address bar không đổi |
| Bấm lại chip đang chọn | 6 → 23 sản phẩm, `checked: []` (bỏ lọc, dùng chuột thật qua CDP) |
| Danh mục chưa cấu hình (`/may-cha-san`) | "Khoảng giá" vẫn đủ **7 chip enum cũ**, không có nhóm thông số |
| Mobile 390px | các nhóm lọc xếp chồng `w=318`, `docOverflow: false` |
| Admin 348 | 12 input `price_band[i][*]` đúng giá trị đã lưu, 4 `<option selected>` thông số |
| Admin 357 (danh mục con) | 0 input giá, không có select thông số |

Không có JS exception, không 404 tài nguyên.

### Cập nhật 2026-10-29 — bỏ các nhóm thông số khỏi khối lọc trang danh mục

User xem screenshot khối lọc và yêu cầu: *"bên trang danh mục bỏ Công suất, Số động cơ, Điện áp,
Dung tích đi"*. Chỉ bỏ ở **khối lọc trong trang**; cột "Thông số" trong mega header / sidebar trang chủ /
drawer mobile **giữ nguyên** (đó là chỗ user đồng ý từ đầu), nên `m_category_specs`, card cấu hình trong
admin và tham số `?attr=` vẫn còn tác dụng.

- Blade: xoá `@foreach ($specGroups ...)` + biến `$specGroups` trong `web/product/category.blade.php`.
- Controller: bỏ biến `$specs` và key `'specs'` trong `compact()` (`New_ProductController::category()`),
  vì blade không còn dùng. `getFilterConfig()` vẫn gọi 1 lần để lấy `price`.
- `filterState.attr` trong inline script **cố ý giữ**: vào trang từ chip `?attr=9001` rồi bấm chip giá thì
  bộ lọc thông số vẫn được giữ trong request AJAX kế tiếp, không bị rơi.

Không thêm file nào vào danh sách upload (2 file sửa đều đã có trong 14 file vòng (5)), **không build lại
CSS/JS**, không có SQL mới.

Đo lại tại `http://sandienmay.test/may-hut-bui-cong-nghiep` (danh mục 348):

| Kiểm tra | Kết quả |
|---|---|
| Nhãn trong `.filter-card` | `["Thương hiệu nổi bật", "Chọn theo nhu cầu", "Khoảng giá"]` — 1280px và 390px đều vậy |
| Radio trong khối lọc | chỉ `brand` và `price_range` (không còn `attr`) |
| Bấm "Dưới 5 triệu" (chuột thật qua CDP) | 23 → **6** sản phẩm, `checked: price_range=0-5000000`, address bar không đổi |
| Bấm lại lần 2 | 6 → **23**, `checked: none` |
| Lọc qua URL | `?attr=9001` → 1 · `?price_range=20000000-` → 3 · `?attr=9001&price_range=0-5000000` → 0 |
| Cột Thông số ở mega header | còn 6 nhãn `.mega-spec-attr` + link `?attr=9001/9002/9003/9005` |
| Tràn ngang | 1280px `1273/1273` (không tràn) · 390px `410/390` — **bug cũ của lưới sản phẩm** (`.product-card` right=410), khối lọc chỉ `w=360`, chưa được giao sửa |


---

## Vòng 2026-10-29 (1) — submenu drawer mobile bấm "+" không mở

User báo trên mobile: *"click submenu đang k ra"* (kèm screenshot `#modelMenuMobi` mở, 14 danh mục cấp 1,
không có nhóm nào bung).

**Nguyên nhân** (`public/js-new/fe/main.js`, khối DOMContentLoaded số 3): handler toggle bị gắn
**một lần** bằng `querySelectorAll('.submenu-toggle-btn')` và chặn sau `if (window.innerWidth <= 992)`.
Trang nào được load lúc `innerWidth > 992` rồi mới thu nhỏ (resize cửa sổ, DevTools responsive mode,
xoay tablet) thì listener **không bao giờ được gắn** → bấm "+" không có tác dụng gì, `li` vẫn
`class="has-submenu-custom"` không `is-active`, `.sub-menu-custom` cao 0.

Đã tái hiện TRƯỚC khi sửa: load 1440px → ép 390px → click → `{"liClass":"has-submenu-custom","subH":0}`.

**Cách sửa:** bỏ cổng `innerWidth`, chuyển sang **event delegation trên chính `<dialog id="modelMenuMobi">`**
(`mobileMenu.addEventListener('click', ...)` + `e.target.closest('.submenu-toggle-btn')`). Chỉ áp cho drawer;
mega header và sidebar trang chủ vẫn là hai mạch JS riêng, không đụng tới.

### File upload vòng này — 1 file
- `public/js-new/fe/main.min.js` — **20.345 B** (giảm 12 B so với 20.357 B). Kèm nguồn để đồng bộ:
  `public/js-new/fe/main.js` (51.858 B).
- `?v=` của `main.min.js` lấy từ `filemtime` (`layout.blade.php` dòng 31) → upload xong browser tự thấy bản mới,
  không cần clear cache trình duyệt. **Không build CSS**, không sửa Blade/PHP, không có SQL.

### Kết quả đo local sau khi sửa
| Kiểm tra | Kết quả |
|---|---|
| Load 1440px → thu 390px → bấm "+" | `is-active`, submenu cao 613px (TRƯỚC: không `is-active`, cao 0) |
| Load 390px → bấm "+" | `is-active`, 613px |
| Load 1280px → thu 360px → bấm "+" | `is-active`, 636px |
| Mở 2 nhóm liên tiếp | `[true,true]`, `visibleSubs: 2` (độc lập, không đóng cái kia) |
| Bấm lại "+" đang mở | về `[false,true]` |
| Bấm **tên** danh mục trong drawer | vẫn điều hướng `/may-hut-bui-cong-nghiep` |
| Bấm **link con** trong nhóm đã mở | vẫn điều hướng `/may-hut-bui-palada` |
| Drawer trên trang danh mục | mở + toggle bình thường |
| Mega header 1440px | `megaOpen: true`, 1 pane hiện, đủ 4 nhãn `.mega-spec-attr` |
| Sidebar trang chủ 1440px (hover) | flyout `shown: 1`, `w: 1005` |
| Console | 0 exception, cả trang chủ và trang danh mục |

---

## 2026-10-29 (2) — Dọn danh mục cấp 2 fixture (CHỈ DB LOCAL, không upload gì)

User: *"để lại danh mục cấp 2 của máy hút bụi công nghiệp để test thôi, còn lại danh mục cấp 2 của máy chà sàn,
xe quét rác,... xóa đi. Để có đúng đâu mà cũng để"*.

Đã kiểm tra trước khi xoá: 160 danh mục con của 10 danh mục cấp 1 (351, 352, 353, 354, 355, 363, 364, 379, 396, 413)
có **0 sản phẩm** và **0 tham chiếu** ở `m_category_product`, `category_brand_pivot`, `category_attribute`,
`m_category_post`, `m_promotion_category`, `m_videos`, `m_category_price_bands`, `m_category_specs`; không có cấp 3.
`m_products` không có cột `category_id` (quan hệ chỉ qua pivot) nên không thể mồ côi sản phẩm.

Backup 160 INSERT: `storage/app/zz-backup/category-level2-fixture-2026-10-29.sql` (restore: `mysql ... < file`).

```sql
DELETE c FROM m_categories c JOIN m_categories p ON c.parent_id = p.id
 WHERE c.type='product' AND p.type='product' AND p.parent_id IS NULL AND p.id <> 348;
-- 160 rows
```

**Demo không có 160 dòng này** (chúng chỉ được seed ở local) → vòng này **không upload file nào**, chỉ cần
`/backend/clear-cache` ở local (đã chạy `php artisan cache:clear`).

Kết quả đo sau khi xoá + clear cache (`http://sandienmay.test`):
| Kiểm tra | Kết quả |
|---|---|
| Drawer mobile 390px | 14 item cấp 1, **chỉ 1 có "+"** (Máy hút bụi công nghiệp); bấm "+" mở/đóng OK, bấm tên vẫn sang `/may-hut-bui-cong-nghiep`, bấm link con vẫn sang `/may-hut-bui-palada` |
| Mega header 1440px | rail item 0 có `.mega-rail-arrow`; 13 item còn lại `mega-rail-item--leaf`, hover không đổi cột, không hiện pane rỗng |
| Sidebar trang chủ 1440px | hover item 0 → flyout `w=1005`, 13 link; hover item lá → giữ flyout cũ (không sinh panel trống) |
| HTTP | `/` `/may-hut-bui-cong-nghiep` `/may-cha-san` `/xe-quet-rac` `/may-cong-nghiep` `/tim-kiem?s=may` `/backend/login` đều 200 |
| Console | 0 exception |

---

## 2026-10-29 (3) — Hover danh mục cấp 1 không có con: cột submenu phải TRẮNG

User: *"hover vào những danh mục cấp 1 mà không có danh mục con thì phải là ô trắng bên hiển thị submenu chứ,
k để là cái cũ đc. ví dụ t hover vào tháp giải nhiệt nó k có submenu thì nó phải là trang trắng"*.

Nguyên nhân: `MegaMenu` và `SideMenu` cùng có `if (!li.querySelector('.mega-rail-arrow')) return;` — dòng đó
làm item không có con **không bao giờ gắn listener**, nên pane cũ vẫn còn `.is-active`.

**Lưu ý về dữ liệu**: sau vòng dọn fixture, 351 "Máy chà sàn" đã có lại 2 danh mục con `brand`
(550 Máy chà sàn DQC - Tech, 551 Máy chà sàn Kumisai) tạo từ backend → nó **không còn là lá**.
Lá thật lúc này: 352, 353, 354 (Tháp giải nhiệt), 355, 356, 362, 363, 364, 366, 379, 396, 413 (12/14 item).

### File đã sửa (3 file upload + 1 lệnh)
| File | Size | Nội dung |
|---|---|---|
| `public/js-new/fe/main.js` | (nguồn, không upload) | Bỏ guard ở `MegaMenu` (~dòng 450) và `SideMenu` (~dòng 497) → mọi item rail đều gắn `mouseenter`/`focusin` |
| `public/js-new/fe/main.min.js` | 20.251 B | **upload** (tự đổi `?v=` theo filemtime) |
| `public/css-new/src/layout.css` | (nguồn, không upload) | Xoá rule `.mega-leaf-cta` đã chết |
| `public/css-new/dist/layout.min.css` | 47.665 B | **upload** (file này được inline vào HTML qua `@inlineCss`) |
| `public/css-new/src/home-v2.css` | (nguồn, không upload) | Bỏ `border-left-color: transparent;` trong `.home-side .mega-rail-item.is-active .mega-rail-link` → rail sidebar giữ vạch xanh trái khi hover |
| `public/css-new/dist/home-v2.min.css` | 11.268 B | **upload** (round này sửa sau, xem mục "Bổ sung" dưới) |
| `resources/views/components/fe/menu.blade.php` | 11.210 B | **upload** — pane lá render rỗng, bỏ nút "Xem tất cả …" |

Sau khi upload: `/backend/clear-cache` (view cache) rồi **Ctrl+F5** — không có SQL, không đụng `.env`, không `key:generate`.

### Kết quả đo local (1440px, headless Chrome + mouse thật)
| Kiểm tra | Kết quả |
|---|---|
| Sidebar trang chủ: hover 0 → 4 (Tháp giải nhiệt) | `shown: 1`, `paneIndex: 4`, `links: 0`, text rỗng, flyout vẫn `1053x542` nền trắng |
| Sidebar: hover 1 (Máy chà sàn, có 2 con) | `paneIndex: 1`, `links: 2`, hiện đúng "Thương hiệu / Máy chà sàn DQC - Tech / Kumisai" |
| Sidebar: 4 → 1 → 3 → 4 | pane đổi theo đúng item hover, không bao giờ giữ submenu cũ |
| Mega header: hover 4 rồi 11 (lá) | `shown: 1`, `links: 0`, card giữ nguyên `1013x623` (không sập, không nhảy) |
| Click vào **tên** lá | link vẫn `http://sandienmay.test/thap-giai-nhiet` |
| Drawer mobile 390px (regression) | mở/đóng accordion `[true,true]` → `[false,true]`, điều hướng `/may-hut-bui-cong-nghiep`, `/may-hut-bui-palada` OK |
| HTTP | `/` `/may-hut-bui-cong-nghiep` `/thap-giai-nhiet` `/tin-tuc-may` đều 200 |
| Console | 0 exception |

### Bổ sung cùng vòng — rail sidebar mất vạch xanh trái khi hover
User (screenshot hover "Xe quét rác"): *"phía dưới này lại mất hover border-left bên danh mục cha rồi"*.

Nguyên nhân: `home-v2.css` từng set `border-left-color: transparent` cho `.home-side .mega-rail-item.is-active .mega-rail-link`
(override theo mẫu cellphones). Trước đây **item lá không bao giờ nhận `.is-active`** nên vẫn ăn `:hover` của
`layout.css` → còn vạch xanh. Vòng này cho lá nhận `.is-active` (để hiện panel trắng) nên vạch xanh biến mất luôn.
Đã bỏ đúng 1 dòng `border-left-color: transparent;` → nền xám nhạt + chữ thường giữ nguyên, chỉ thêm lại vạch xanh.

Đo lại tại 1440px (mouse thật, `getComputedStyle` trên `.mega-rail-link` đang hover):
| Item sidebar | border-left | background | color | pane |
|---|---|---|---|---|
| 2 Xe quét rác (lá) | `3px solid rgb(56,117,250)` | `#F0F1F5` | `--ink` | 0 link (trắng) |
| 4 Tháp giải nhiệt (lá) | `3px solid rgb(56,117,250)` | `#F0F1F5` | `--ink` | 0 link (trắng) |
| 0 Máy hút bụi công nghiệp | `3px solid rgb(56,117,250)` | `#F0F1F5` | `--ink` | 13 link |
| 1 Máy chà sàn | `3px solid rgb(56,117,250)` | `#F0F1F5` | `--ink` | 2 link |
| Mega header 0 / 4 | `3px solid rgb(56,117,250)` | `#EEF3FF` | `--navy` | không đổi |

→ **File upload của vòng này gồm 4 file** ở bảng trên (`main.min.js`, `layout.min.css`, `home-v2.min.css`,
`menu.blade.php`), không có SQL, không sync `.env`.

### Bổ sung 2 — cột "Thương hiệu" trong menu phải ra logo, không ra chữ
User (screenshot 2 chip xanh navy "Máy chà sàn DQC - Tech / Máy chà sàn Kumisai"):
*"chỗ này t chọn thương hiệu thì nó phải ra ảnh thương hiệu chứ k phải ra text"*.

Nguyên nhân: `m_categories.image` của 550/551 **rỗng** (khi tạo danh mục con chỉ chọn Thương hiệu ở backend,
ảnh lưu trong danh mục nên không có). `BaseCategoryDto` chỉ lấy `image` của chính danh mục → chip rơi về nhãn chữ.
Đã sửa ở `app/View/Components/Fe/Menu.php`: một query `category_brand_pivot ⋈ brands` lấy `brands.image`,
dùng làm ảnh dự phòng cho danh mục con không có ảnh riêng (áp cho mọi nhóm, thực tế chỉ danh mục gắn brand mới có dòng pivot).
Viết theo đúng cách `New_ProductController.php:649` vẫn làm cho khối "Chọn theo tính năng".

| Kiểm tra (1440px, sidebar trang chủ) | Kết quả |
|---|---|
| Hover "Máy chà sàn" | 2 chip là **ảnh logo** `DQC-TECH` + `KUMISAI` (`/storage/brands/…png`, HTTP 200), không còn chữ |
| Hover "Máy hút bụi công nghiệp" (regression) | `Thương hiệu`: Palada + Camry vẫn logo (image riêng của danh mục, không đi qua dự phòng); `Nhu cầu` 3 chip có ảnh + badge HOT/NEW; `Phân khúc giá` 4 mức; `Thông số` 4 nhóm — nguyên như trước |
| Console | 0 exception, 0 lỗi ảnh |

File upload tăng thêm 1: `app/View/Components/Fe/Menu.php` (không build, không SQL).

### Bổ sung 3 — logo thương hiệu trong menu quá nhỏ, khó nhìn
User (screenshot 3 chip Máy chà sàn): *"cho cái ảnh logo hiển thị lên to chút, khó nhìn quá"*.

Đo: chip 116×48 nhưng ảnh chỉ đặt 72×24 trong khi **file logo gốc là 101×34** (cả 4 file trong
`public/storage/brands/` đều đúng 101×34) → đang bị hạ kích thước.
Đã sửa: `menu.blade.php` đặt `width/height` ảnh logo = 101×34 (1:1 với file gốc, không phóng vỡ nét),
`layout.css` nới chip `.mega-group--brand .mega-item-link` lên 124×56, padding 10px.
Muốn to hơn nữa phải upload logo gốc lớn hơn (hiện 101×34 là trần, vượt là mờ).

| Kiểm tra (1440px) | Trước | Sau |
|---|---|---|
| Ảnh logo (rendered / natural) | 72×24 / 101×34 | **101×34 / 101×34** |
| Chip thương hiệu | 116×48 | 124×56 |
| Cột brand (4 cột đều nhau) | 232px, 1 chip/hàng | 232px, 1 chip/hàng (không đổi) |
| Badge HOT trên chip Camry | đúng vị trí | đúng vị trí |
| Console | — | 0 exception, 0 lỗi ảnh |

Tổng kết vòng 2026-10-29 (3): **5 file upload** = `main.min.js` 20.251 B, `layout.min.css` 47.665 B,
`home-v2.min.css` 11.268 B, `menu.blade.php` 11.211 B, `app/View/Components/Fe/Menu.php` 5.905 B.

### Việc chưa làm — trang danh mục "Máy chà sàn" không có khối "Thương hiệu nổi bật" (LỖI DỮ LIỆU)
User: *"trong danh mục máy chà sàn đang có 2 thương hiệu tại sao trang danh mục k có"*.

`New_ProductController.php:743` lấy thương hiệu từ **sản phẩm có `brand_id > 0` trong danh mục đó**, không lấy từ
danh mục con đã gán brand. Đo DB local:

| Danh mục | Sản phẩm | Có `brand_id > 0` |
|---|---|---|
| 348 Máy hút bụi công nghiệp (control) | 20 | **20** → khối Thương hiệu hiện đúng |
| 351 Máy chà sàn | 4 (2282, 2283, 2284, 2287) | **0** — cả 4 đều `brand_id = 0` → khối trống, blade `@if ($brandCats->isNotEmpty())` ẩn luôn nhóm |
| 550 Máy chà sàn DQC - Tech | 0 | — |
| 551 Máy chà sàn Kumisai | 0 | — |
| 552 Máy chà sàn Camry (thêm sau) | 0 | — |

Hệ quả phụ cần lưu ý: bấm các chip logo mới trong menu sẽ vào `/may-cha-san-dqc-tech`,
`/may-cha-san-kumisai`, `/may-cha-san-camry` nhưng **0 sản phẩm** (chưa gán sản phẩm cho danh mục con).
Hai việc data phải làm trong backend (chưa tự sửa vì là ghi DB):
1. Sửa 4 sản phẩm → chọn **Thương hiệu** (2282/2283 là Kumisai; 2284 Máy rửa xe và 2287 Máy đánh giầy đang nằm
   nhầm trong Máy chà sàn).
2. Gán sản phẩm cho danh mục con 550/551/552 để các trang thương hiệu không trống.

---

## VÒNG GỘP 2026-10-29 (4) — Upload MỘT LẦN cho toàn bộ 27/10 (2) → 29/10 (3)

Demo **đang đứng ở PageSpeed vòng 5 (27/10 sáng)**, chưa có bất kỳ thay đổi nào của mảng
header/menu/sidebar/bộ lọc. Cách duy nhất để site không sập: **chạy SQL trước, rồi upload đủ 25 file**.

### Bằng chứng (đo trực tiếp trên `https://demo.sandienmay.com`, 29/10)

| Marker code mới | Demo | Local |
|---|---|---|
| `id="mega-toggle"` (nút "Danh mục") | **0** | có |
| `home-side` / `home-hero-grid` (sidebar + lưới banner) | **0** | có |
| `price_range=` (chip lọc giá mới) | **0** — khối "Khoảng giá" vẫn `<input name="price">` enum cũ | có |
| `mega-scrim` (lớp phủ khi mở panel) | **0** | có |

| Asset đã build | Demo (B) | Local mới (B) | Kết luận |
|---|---|---|---|
| `css-new/dist/layout.min.css` | 43.096 | **47.653** | thiếu cả mega + scrim + logo brand |
| `css-new/dist/home-v2.min.css` | 9.745 | **11.268** | thiếu sidebar trang chủ + vạch xanh rail |
| `css-new/dist/category-v2.min.css` | 9.738 | **9.761** | `.main` 133→90 + khối lọc |
| `css-new/dist/base.min.css` | 31.166 | **31.165** | `.main` 125→90 — **lệch 1 byte nên vẫn phải upload** |
| `css-new/dist/post-v2.min.css` | 12.643 | **12.642** | `.main` 133→90 — lệch 1 byte, vẫn phải upload |
| `js-new/fe/main.min.js` | 19.157 (`?v=1792982206` = 26/10) | **20.251** | thiếu MegaMenu/SideMenu/drawer/leaf |
| `product.min.css`, `product.min.js` | = local | = local | **không cần upload** |

Thiếu 2 file `.main` 1 byte thì header đè lên 20–43px đầu trang danh mục/bài viết.

### B1 — SQL: `deploy/sql-demo-2026-10-29.sql` (chạy TRƯỚC khi upload code)

Lý do bắt buộc: `CategoryService::getFilterConfig()` + `Menu.php` + `New_ProductController` đọc
`m_category_price_bands` / `m_category_specs`. Trong bản dump demo ngày 26/10 **hai bảng này không tồn tại**
→ upload code trước là **trang chủ và mọi trang danh mục 500**.

 phpMyAdmin → DB `ynlbdgfvhosting_sandienmay` → tab SQL → dán **cả file** → chạy.
 Dòng `(0)` phải ra `chan = OK - DUNG DB cua demo`. Các câu `(8)` cuối file in ra bảng đối chiếu.

| Mục | Làm gì | Kỳ vọng trên demo |
|---|---|---|
| (1) | `CREATE TABLE IF NOT EXISTS` 2 bảng + ghi danh migration `2026_10_28_170000_...` | `migrations` thêm đúng 1 dòng, `batch = MAX+1` |
| (2) | 4 mức giá cho `may-hut-bui-cong-nghiep` (resolve theo **slug**) | 4 dòng: Dưới 5tr / 5–10tr / 10–20tr / Trên 20tr |
| (3) | 4 thông số (resolve theo **tên** thuộc tính + giá trị) | 4 dòng: Công suất 5500W → Số động cơ 3 motor → Điện áp 380V/50Hz → Dung tích 100L |
| (4) | 3 danh mục con brand của `may-cha-san` + `category_brand_pivot` | 3 dòng pivot: DQC-Tech / Kumisai / Camry |
| (5) | Ảnh của `may-hut-bui-camry` + `may-hut-bui-palada` = logo thương hiệu | hết `demo-sp-1.png` / `demo-sp-2.png` |
| (6) | Dọn danh mục fixture `display_type IN (price,spec)` | `se_an_danh_muc_price_spec = 0` (demo không có fixture) |
| (7) | **TÙY CHỌN** — `brand_id` cho 4 sản phẩm Kumisai + gán 2 sản phẩm chà sàn vào `may-cha-san-kumisai` | chạy thì khối "Thương hiệu nổi bật" của `/may-cha-san` hiện + trang kumisai có 2 SP; bỏ qua thì vào backend tự làm |

**Đã kiểm chứng**: dựng DB `zz_demo_copy` từ chính bản dump demo (`sql.sql`, 24 danh mục / 27 sản phẩm),
chạy file **3 lần liên tiếp** → không lỗi, số dòng không đổi (4/4/3, `migrations` vẫn 1 dòng), không tạo bản trùng.
Kết quả khớp 100% bảng đang có trên local.

Bỏ qua mục (7) thì trang `/may-cha-san-dqc-tech` và `/may-cha-san-camry` **có menu nhưng 0 sản phẩm** —
không sập, chỉ trống (blade ẩn khối khi rỗng).

Hoàn tác (chỉ phần do file SQL tạo ra, nhận diện bằng marker):
```sql
DELETE cp FROM category_brand_pivot cp JOIN m_categories c ON c.id=cp.category_id WHERE c.seo_keywords='ZZ-BRAND-CATE';
DELETE FROM m_categories WHERE seo_keywords='ZZ-BRAND-CATE';
DELETE FROM m_category_price_bands WHERE category_id=(SELECT id FROM (SELECT id FROM m_categories WHERE slug='may-hut-bui-cong-nghiep') t);
DELETE FROM m_category_specs       WHERE category_id=(SELECT id FROM (SELECT id FROM m_categories WHERE slug='may-hut-bui-cong-nghiep') t);
DELETE FROM migrations WHERE migration='2026_10_28_170000_create_category_price_bands_and_specs_tables';
-- 2 ảnh danh mục đã đổi ở mục (5):
UPDATE m_categories SET image='demo-sp-1.png' WHERE slug='may-hut-bui-camry';
UPDATE m_categories SET image='demo-sp-2.png' WHERE slug='may-hut-bui-palada';
```

### B2 — 25 file upload (đường dẫn gốc = `public_html/demo.sandienmay.com/`)

**PHP — 9 file (3 file mới, tạo thư mục nếu thiếu)**
| File | B |
|---|---|
| `app/Helpers/CategoryFilterConfig.php` *(mới)* | 3.134 |
| `app/Models/CategoryPriceBand.php` *(mới)* | 125 |
| `app/Models/CategorySpec.php` *(mới)* | 114 |
| `app/Models/Category.php` | 5.657 |
| `app/Traits/ActivityLoggable.php` | 3.157 |
| `app/Services/FeServices/CategoryService.php` | 8.613 |
| `app/View/Components/Fe/Menu.php` | 5.905 |
| `app/Http/Controllers/Backend/CategoryController.php` | 25.392 |
| `app/Http/Controllers/Frontend/New_ProductController.php` | 45.304 |

**Blade — 9 file**
| File | B | Thuộc vòng |
|---|---|---|
| `resources/views/components/fe/menu.blade.php` | 11.211 | 27/10(2)→29/10(3) — mega/sidebar/drawer + pane lá trống + logo 101×34 |
| `resources/views/web/header.blade.php` | 4.312 | 27/10(2) + 28/10(3) `mega-scrim` |
| `resources/views/web/home/index.blade.php` | 22.962 | 28/10(2) sidebar + lưới banner |
| `resources/views/web/mobile-menu.blade.php` | 1.023 | 28/10 — khung drawer `#modelMenuMobi` |
| `resources/views/web/product/category.blade.php` | 18.256 | 28/10(5) + 29/10 khối lọc `price_range` |
| `resources/views/backend/category/create.blade.php` | 47.960 | 27/10(2) select `display_type` |
| `resources/views/backend/category/edit.blade.php` | 62.435 | 28/10(5) 2 card "Các mức giá" + "Thông số" |
| `resources/views/backend/banner/create.blade.php` + `edit.blade.php` | 8.123 / 9.372 | chỉ admin — upload kèm cho khớp, không ảnh frontend |

**Asset đã build — 6 file (chỉ file `dist`, KHÔNG upload `src`)**
| File | B |
|---|---|
| `public/css-new/dist/base.min.css` | 31.165 |
| `public/css-new/dist/layout.min.css` | 47.653 |
| `public/css-new/dist/home-v2.min.css` | 11.268 |
| `public/css-new/dist/category-v2.min.css` | 9.761 |
| `public/css-new/dist/post-v2.min.css` | 12.642 |
| `public/js-new/fe/main.min.js` | 20.251 |

`base.min.css` + `layout.min.css` được **inline vào HTML** (`@inlineCss`, `web/layout.blade.php`) →
phải upload đủ 2 file, thiếu 1 là CSS trang chủ/danh mục giữ bản cũ.

**Migration — 1 file (không chạy, chỉ để shell đủ)**:
`database/migrations/2026_10_28_170000_create_category_price_bands_and_specs_tables.php` (1.442 B).
Trên host không có shell → không chạy được `artisan migrate`; đã ghi danh bằng SQL mục (1).

**KHÔNG upload**: `.env` (tuyệt đối), `deploy/*`, `sql.sql`, `public/css-new/src/*`,
`public/js-new/fe/main.js` (nguồn), `resources/views/mockup*`, `resources/views/web/test-blade.php`,
`app/Http/Controllers/Frontend/MockCategoryController.php`, `zz-verify-home.mjs`, `zz-probe.js`,
`deploy/seed-menu-demo.php` (fixture local).
Không đổi: `web/layout.blade.php` (demo đã có bản 27/10 — `?v=` theo `filemtime`), `config/debugbar.php`,
`app/Providers/AppServiceProvider.php`, `product.min.css`, `product.min.js`.

### B3 — Sau khi upload
1. Mở `https://demo.sandienmay.com/backend/clear-cache` (3 Blade component + cache `fe_menu_filters_v1`).
2. **Ctrl+F5** ở trang chủ (`base`/`layout` nằm inline trong HTML, `main.min.js` tự đổi `?v=` theo `filemtime`).
3. Đối chiếu nhanh với số đo local:

| Kiểm tra trên demo | Kỳ vọng |
|---|---|
| View source trang chủ | có `id="mega-toggle"`, `home-side`, `home-hero-grid`, `mega-scrim` — mỗi cái ≥ 1 |
| Bấm "Danh mục" (≥993px) | panel card nổi 4 cột: Thương hiệu / Nhu cầu / Thông số / Phân khúc giá; trang tối đi, cuộn bằng wheel không được; bấm ra ngoài → sáng lại |
| Rê chuột "Máy chà sàn" ở sidebar trang chủ | flyout phủ **khít** cột banner; 2–3 chip là **ảnh logo** 101×34, không phải chữ |
| Rê chuột "Tháp giải nhiệt" (không có con) | cột phải **trắng xoá**, không giữ submenu trước đó; bấm vào tên vẫn vào `/thap-giai-nhiet` |
| Rail sidebar khi hover | có vạch xanh trái 3px + nền `#F0F1F5` |
| Bấm chip "Dưới 5 triệu" | `/may-hut-bui-cong-nghiep?price_range=0-5000000`, khối "Khoảng giá" sáng đúng chip |
| Bấm chip "5500W" | `?attr=<id option trên demo>` — id có thể khác local, quan trọng là ra kết quả |
| Mobile 390px | bấm ☰ → drawer mở; bấm "+" nhóm có con → accordion bung; bấm tên → sang trang danh mục |
| `/may-cha-san` | nếu chạy SQL mục (7): có khối "Thương hiệu nổi bật"; nếu không: vẫn chỉ "Khoảng giá" (7 khoảng enum) |
| Console | 0 exception, 0 ảnh 404 |

4. Việc còn lại **trong backend demo** (không làm bằng SQL được): vào `Danh mục → sản phẩm → sửa`
   từng danh mục cấp 1 muốn có lọc giá/thông số, nhập "Các mức giá" và chọn "Thông số" rồi Lưu;
   cấu hình demo sẽ khác local vì thuộc tính/giá trị của demo có id riêng.

### Lùi lại khi cần
- Code: WinSCP → `/backup-truoc-deploy/` (copy `app/`, `resources/views/`, `public/css-new/`, `public/js-new/` trước khi upload) → copy ngược về.
- Dữ liệu: chạy khối `DELETE`/`UPDATE` hoàn tác ở B1, hoặc Import bản Export ở Bước 0.

---

## KẾT QUẢ THỰC ĐỢT — 29/10 (tôi chạy trực tiếp, không cần user thao tác)

Toàn bộ vòng gộp (4) đã lên demo. Đường đi khác checklist ở chỗ **không có phpMyAdmin**: host chỉ mở FTP nên
SQL chạy bằng script tạm `public/zz-sql-run.php` (token `?k=`, dùng chính kết nối DB của app) + payload
`storage/zz-sql-payload.sql` (ngoài webroot), chạy xong **xoá cả 2 và xác nhận 404**.

| Bước | Kết quả |
|---|---|
| Test SQL trên bản copy DB demo (`zz_demo_copy` từ `sql.sql`) | 23/23 statement, chạy 2 lần liên tiếp không sinh bản trùng → `DROP DATABASE` |
| SQL trên demo (`ynlbdgfvhosting_sandienmay`, **MariaDB 10.5.29**) | 23/23, `chan = OK - DUNG DB cua demo`; price_bands 4, specs 4, pivot 3, cate con brand 5, sp chưa thương hiệu 3 (trước 7), logo Camry/Palada = ảnh brand, 2 SP vào `/may-cha-san-kumisai` |
| Upload 25 file (`deploy/winscp-upload.txt`) | đủ 25, `main.min.js` host 20.251 B = local |
| Dọn cache | `storage/framework/views/*.php` đã xoá. `storage/framework/cache/data/` **không xoá được** (WinSCP `rm` không đệ quy, `mirror` không có trong script mode) → không sao: key `categories` TTL 600 s, các key đổi shape đã bump suffix trong code |
| Verify HTTP | `/` 200 (302.074 B, trước 243.087 B), `/may-hut-bui-cong-nghiep` 200, `/may-cha-san` 200, `/may-cha-san-kumisai` 200 có 4 card, `?price_range=5000000-10000000` 200 còn 16 card |
| Marker | `#mega-toggle` 1, `.home-side` 13, `.home-hero-grid` 4, `.mega-scrim` 3, `price_range=` 12 (trang chủ) — **trước deploy cả 5 marker = 0** |
| Screenshot 1440px | header mới + sidebar danh mục + cột banner; mega panel mở được, rail 11 mục, pane 4 cột (Thương hiệu / Nhu cầu / Phân khúc giá / Thông số), `scrollWidth 1433` không tràn |
| File tạm | `zz-sql-run.php` + `zz-sql-payload.sql` đã xoá khỏi host (404), đã xoá khỏi repo; `deploy/winscp-sql.txt`, `winscp-del.txt`, `winscp-clearcache.txt`, `winscp-mirror.txt`, `winscp-help.txt`, `zz-empty/` đã dọn |
| Rollback | `deploy/backup-host-2026-10-29/` (21 file bản cũ) + SQL hoàn tác có marker `ZZ-BRAND-CATE` |

**Chưa verify / còn treo:**
- Trang backend (`/backend/...`) cần đăng nhập — user tự mở `Danh mục → sửa` để chắc 2 khối "Các mức giá"/"Thông số" hiện đúng.
- Điểm PageSpeed/PSI sau vòng này: chưa đo lại.
- Data demo: `/may-cha-san-dqc-tech` và `/may-cha-san-camry` **0 sản phẩm**; các danh mục cấp 1 khác chưa cấu hình mức giá/thông số.
- `/tim-kiem?q=<từ không có kết quả>` → 302 về trang chủ: **hành vi có sẵn** (local cũng 302), không phải lỗi deploy.

**Ghi chú WinSCP cho vòng sau** (đã đốt 3 vòng hôm nay): `lcd` phải **sau** `open`; `put` không nhận path tương đối
nhiều cấp → sinh script kiểu `lcd <thư mục chứa file>` + `cd <thư mục remote>` + `put "<basename>"`;
`ls <tên-file>` hợp lệ thành `ls <mask*>`; `rm` không đệ quy.

---

## VÒNG 6 (30/10) — PageSpeed mobile: bỏ render-blocking `main.min.js` + hết forced reflow lúc load

Hai vấn đề DevTools Insights báo trên mobile (desktop 96, mobile 79):
- **Yêu cầu chặn hiển thị**: `/js-new/fe/main.js` (Bên Thứ Nhất, 6.1 KiB).
- **Buộc chỉnh lại luồng**: top call `main.min.js…:15903`, `[chưa được phân bổ]` 53 ms, `main.min.js…1:16013`, `glide.min.js…6:13476` (22 ms).

### File cần upload (9) — KHÔNG sync `.env`, không có SQL

| Local | Demo (`/home/ynlbdgfvhosting/public_html/demo.sandienmay.com/…`) |
|---|---|
| `public/js-new/fe/main.min.js` | `public/js-new/fe/main.min.js` (20.367 B) |
| `resources/views/web/layout.blade.php` | `resources/views/web/layout.blade.php` |
| `resources/views/web/home/index.blade.php` | `resources/views/web/home/index.blade.php` |
| `resources/views/web/product/detail.blade.php` | `resources/views/web/product/detail.blade.php` |
| `resources/views/web/product/index.blade.php` | `resources/views/web/product/index.blade.php` |
| `resources/views/web/product/_first-screen.blade.php` | `resources/views/web/product/_first-screen.blade.php` |
| `resources/views/web/page/search.blade.php` | `resources/views/web/page/search.blade.php` |
| `resources/views/components/fe/products.blade.php` | `resources/views/components/fe/products.blade.php` |
| `resources/views/web/cart/index.blade.php` | `resources/views/web/cart/index.blade.php` (gộp nốt nút −/+ giỏ hàng của vòng trước) |

Cache-busting: `?v=` = `filemtime(main.min.js)` → tự đổi khi upload, không phải sửa gì.
Blade: mtime local mới hơn bản host nên view tự biên dịch lại; nếu muốn chắc thì
`rm "/public_html/demo.sandienmay.com/storage/framework/views/*.php"` (1 tầng, WinSCP làm được).

### Bẫy phải nhớ nếu sửa tiếp phần này
`main.min.js` đã chuyển sang **`defer`** → inline script trong `@stack('scripts')` chạy **trong lúc parse**,
trước khi script defer thực thi. Mọi lời gọi `Common.*` / `postData` ở **top-level** sẽ
`ReferenceError` **im lặng** (mất tính năng, không đỏ console kiểu bình thường).
Rule: `Common.loadJs(...)` luôn bọc trong `document.addEventListener('DOMContentLoaded', …)`.
Đã bọc 7 chỗ: `home/index`, `product/detail`, `product/index`, `product/_first-screen` (2 khối),
`page/search`, `components/fe/products`. `window.syncCarouselNav` vẫn để top-level (khai bằng `function`, có hoist).

### Rollback
`deploy/winscp-backup-c.txt` → `deploy/backup-host-2026-10-30/` (9 file bản host).

### KẾT QUẢ VÒNG 6 (tôi deploy, 30/10)

| Bước | Kết quả |
|---|---|
| Backup (`winscp-backup-c.txt`) | 9/9 → `deploy/backup-host-2026-10-30/` |
| Upload (`winscp-upload-c.txt`) | 9/9; `get` lại 3 file rồi `diff` + `md5sum` → **khớp local 100%** (175e1e95…  cho `main.min.js`) |
| HTML thật trên demo | `<script src="/js-new/fe/main.min.js?v=1793328237" defer>` xuất hiện ở `/`, `/may-hut-bui-cong-nghiep`, `/gio-hang`; 200, size 314.605 / 226.054 / 168.513 B |
| Vòng 6b (`winscp-upload-c2.txt`) | `home/index.blade.php`: preload banner bỏ `media` khi `image_mobile` trống → demo còn `<link rel=preload as=image ... fetchpriority=high>` (không còn `media`) |
| Lighthouse mobile (đánh thẳng demo) | **81** (trước 80). `render-blocking-resources` **0 ms / score 1**, TBT 24 ms, CLS 0.003, FCP 1.86 s, **LCP 4.89 s** (← 5.18 s) |
| Lighthouse desktop | **96** — không tụt so với trước |
| PSI API | 429 hết quota ngày (project ẩn danh) → **số chính thức vẫn là PSI user đo** |
| Rollback | `deploy/backup-host-2026-10-30/` + `winscp-upload-c.txt` đảo chiều (`put` từ thư mục backup) |

**Còn lại giữa 81 và 95 (mobile) — đã đo ra tên, không còn đoán:**
1. `m_banners.image_mobile` rỗng → LCP mobile tải `1793001326_6adf076e39bd4.jpg` **1440×529 = 240 KB** cho chỗ 390×143.
   Bản đã sinh sẵn (chỉ resize, không crop): `deploy/banner-mobile/banner-mobile-828x304.webp` **26.4 KB**.
   → User upload trong `/backend/banner` (sửa banner, ô "Hình ảnh Mobile"), **không cần SQL, không cần sửa code**.
2. `.wide-banner img` = `/images/flash-sale.jpg` **294 KB**, `loading="lazy"` trong khi nằm ở top 231 px; bản mobile
   đã sinh: `deploy/banner-mobile/flash-sale-m-828x139.webp` **15.3 KB** — nhưng cần thêm `<picture>` vào blade
   + build lại `home-v2.min.css` (đang hard-code ảnh trong CSS).
3. `.press-section` nền `/images/back-bao.jpg` **157 KB** khai trong `home-v2.css:362` (không lazy được qua CSS).
4. `uses-long-cache-ttl`: host phát `max-age=2592000` (30 ngày) cho 25–40 tài nguyên.
5. Trần ước tính sau mục 1–3: **88–92 mobile**, không phải 95 (HTML 36 KB, 15 request font = 172 KB, 1.538 node DOM).

---

## VÒNG 7 (30/10) — Accessibility trang danh mục 88 → 100

**Bối cảnh:** user gửi screenshot DevTools Lighthouse trên `demo.sandienmay.com/may-hut-bui-cong-nghiep`
(96 / **88** / 96 / 69) và yêu cầu "trang danh mục fix phần này".

| # | Lỗi axe | Nguồn đo được | Fix | File |
|---|---|---|---|---|
| 1 | `color-contrast` | `--muted:#7F7F7F` trên nền `#F7F7F8` = **3.74:1** | `#707070` (4.54:1) | `public/css-new/src/category-v2.css:9` |
| 2 | `color-contrast` (desktop) | `.topbar-zalo #0068FF` trên topbar `#E9EEFA` = **4.09:1** | `#0060EB` | `public/css-new/src/layout.css:1476` |
| 3 | `heading-order` | `h1` tên danh mục nhảy thẳng xuống `h3.product-name` | `<p class="found">` → `<h2 class="found">` + `font-weight:400; line-height:inherit` để **render không đổi** | `category.blade.php:201`, `category-v2.css` |
| 4 | `label` | mobile `.sort-pill span{display:none}` → radio mất tên | `aria-label="{{ $sortName }}"` | `category.blade.php:208` |
| 5 | `image-redundant-alt` ×5 | `alt` ảnh tin tức trùng `<h3>` kế bên | `alt=""` | `category.blade.php:254` |
| 6 | `link-name` (mobile) | `.hotline`/`.cart-link` có `<span>` `display:none` ≤768px | giữ `aria-label`, và **aria-label = nguyên văn text nhìn thấy** để không dính `label-content-name-mismatch` | `header.blade.php:105,113` |

**Bài học:** lần đầu bỏ `aria-label` ở hotline/cart (nghĩ "để text làm tên") → **tự gây lỗi `link-name` trên mobile**,
vì span bị ẩn. axe `label-content-name-mismatch` cũng **không** tha cho `aria-hidden="true"` trên badge giỏ hàng.

**Deploy:** `winscp-backup-a11y.txt` (4 file → `deploy/backup-host-2026-10-30b/`) rồi `winscp-upload-a11y.txt`.
Không sửa `.env`, không SQL, không clear cache. `get` ngược 4 file → **md5 khớp local 100%**:
`344999882d…` category.blade / `b6a168c78b…` header.blade / `1267a2d6d2…` category-v2.min.css / `0f27d06b32…` layout.min.css.
`?v=` trên demo sau upload = **1793335206**; CSS phục vụ thực tế `--muted:#707070`, layout có `#0060eb`, không còn `#0068ff`.

**Kết quả đo trên demo (Lighthouse local đánh thẳng demo):**

| Trang | a11y mobile | a11y desktop | Ghi chú |
|---|---|---|---|
| `/may-hut-bui-cong-nghiep` | **100** (← 88) | **100** | 0 audit fail |
| `/` (kiểm tra vì `header.blade.php` dùng toàn site) | **98** | — | còn `heading-order` ×2 (`h3.product-name`, `h4.news-title`) — lỗi **có sẵn**, không thuộc phạm vi vòng này |

`verify-demo.js`: trang chủ console sạch, Glide 6/8 mount, quickview 32 nút; trang danh mục chỉ còn **1 lỗi tồn tại từ trước**:
`404 /images/no-image.png` (`New_ProductController.php:626,628` dùng làm ảnh dự phòng nhưng file không tồn tại ở local lẫn host).

**Chưa làm (đã báo user):**
- `resources/views/web/page/search.blade.php:113-121` mang **y hệt** lỗi #3/#4/#1 (dùng chung pattern `.found` + `.sort-pill`).
- `/images/no-image.png` thiếu.

---

## VÒNG 8 (31/10) — Nhom B: 2 anh hard-code cua trang chu sang WebP

**Khong phai fix code a11y**: user bao "hom nay t do lai diem lighthouse tren demo deu rat thap".
Do control: 7 trang mobile tu 85-100, khong trang nao thap. So user thay = **desktop 87** (dai mau CAM 50-89
 cua Lighthouse), cua toi do cung trang = 94. SEO 69 co tinh (APP_NO_INDEX), A11y 98 = heading-order trang chu.

**Bang chung co che LCP** (`deploy/lcp-throphases.js` / `lcp-throttled.js` = tool tai su dung,
throttle 1.6Mbps + RTT 150ms + CPU 4x giong Lighthouse):
banner hero request luc 496ms, **tai xong 3915ms**, LCP render 3948ms (chi 28ms sau do)
=> LCP chet vi **het bang thong cho anh**, khong phai JS/Glide/render-delay.

| Asset | Cu | Moi |
|---|---|---|
| `images/flash-sale.jpg` 1216x204 | **294.3 KB** | `flash-sale.webp` 26.9 KB + `flash-sale-m.webp` 780x131 **14.2 KB** |
| `images/back-bao.jpg` 1216x264 (nen CSS `.press-section`) | **157.5 KB** | `back-bao.webp` 4.8 KB + `-m` 780x169 **2.3 KB** |

**Deploy** (`winscp-backup-b.txt` → `winscp-upload-b.txt`, 7 file, khong SQL, khong `.env`, khong clear cache):
backup `deploy/backup-host-2026-10-31/`; `get` nguoc 6 file → **md5 khop 100%**.
**Phat hien quan trong: CSS cua trang chu duoc INLINE vao HTML** (khong co `<link>` home-v2.min.css),
nên doi CSS la thay doi ngay trong document, khong lo `?v=`.

**Ket qua do tren demo:**

| | mobile | desktop |
|---|---|---|
| Performance | 85 → **89** | 94 → **96** |
| LCP | 4085 → **3541 ms** | 1515 → **1217 ms** |
| Speed Index | 3316 → **2840** | 900 → 891 |
| Trong trang | 1087 → **651 KiB** (−40%) | 1653 → 1233 KiB |
| CLS | 0.003 (khong doi) | 0.0005 → 0 |

`verify-demo.js`: trang chu console sạch, Glide 6/8, quickview 32 → khong regress.

**Con lai (nhom A, chua lam):** banner hero `image_mobile` tren host van la **1440x529 = 142.7 KB**
(ban that cua no, KHONG phai ban resize) → request 496ms tai xong 2848ms, van la nut co that cua LCP.
+ 3 tam `storage/home-promo-1/2/3.png` 384x160 **129 KB/tam** (PNG cua anh chup → webp ~18KB).
+ Google Fonts **142 KB / 15 request** (site da co `public/css-new/fonts/Inter_18pt-*.ttf` → self-host duoc).

---

## VÒNG 9 (31/10) — Self-host Inter, cắt hẳn Google Fonts

**Việc:** `web/layout.blade.php:12-18` nạp `css2?family=Inter:wght@400;500;600;700;800` → demo đo được
**16 request Google + 142 KB font** (latin 47.4 + vietnamese 10.3 + **latin-ext 83.1** + css 1.1 KiB).

**Làm gì:**
- Google phục vụ Inter v20 dạng **variable**: 5 weight dùng chung 1 file woff2/subset (đã đối chiếu
  `css2?family=Inter:wght@400..800` ra đúng cùng URL). Nên chỉ cần **3 file** cho 400→800.
- Tải 3 subset vào `public/css-new/fonts/inter/` (latin 48.256 B, latin-ext 85.068 B, vietnamese 10.252 B).
- `base.css`: 3 block `@font-face` (`font-display: swap`, `font-weight: 400 800`, URL tuyệt đối
  `/css-new/...` vì **CSS trang chủ được inline vào HTML**), đặt cạnh block icomoon.
  `font-weight:900` (base.css:1525) clamp về 800 = y hệt hành vi cũ.
- `web/layout.blade.php`: xoá 2 preconnect + preload/onload + noscript.
- `errors/404.blade.php`: trang này nạp Google Inter **render-blocking** riêng → thay bằng inline
  `@font-face` 2 subset (Blade không ăn `@font-face`, đã kiểm tra HTML ra).

**Thu hep `unicode-range` cua latin-ext (đây là chỗ ăn tiền nhất):** chu trang co 181 ky tu
rơi vào range latin-ext nhưng **0 ky tu thuộc RIÊNG latin-ext** — toàn bộ là `Đ đ ă ơ ư ỳ`…
đã có trong subset vietnamese. Giữ nguyên range gốc thì Chrome vẫn tải thêm file **83 KB** vô ích.
`deploy/range-diff.php` tính (latin-ext \ (latin ∪ vietnamese)) → 21 dải, khai báo lại đúng phần còn lại.
`deploy/charset-audit.php /tmp/d-home.html` = tool doi chieu.

**Verify local (`deploy/font-audit.js <url>`, do ca platform fonts bang CDP):**

| | demo (Google) | local (self-host) |
|---|---|---|
| Request Google Fonts | 16 | **0** |
| Request font | 3 subset + 1 css | **2 subset** (latin + vietnamese) |
| Byte font | ~142 KB | **57.7 KB** (−84 KB) |
| HTML inline CSS | — | **+1.075 B** (3 block Inter, gzip còn ~400 B) |

Render **khong doi 1 px nao**: do width cung chuoi o 6 weight, demo vs local khop tuyệt đối
(741.55 / 744.52 / 747.55 / 750.41 / 754.03 / 754.03). `CSS.getPlatformFontsForNode` trên
`.current-price`, `.old-price` = `Inter x10` (khong rơi về fallback). 3 loai trang (home,
`/may-hut-bui`, `/tin-tuc`) + 404: 0 request Google, MIME `font/woff2` 200.

**File upload (7, khong SQL, khong sync `.env`):**
```
public/css-new/fonts/inter/inter-latin-wght.woff2        260c81a4759baf163c025001c4f27872  (moi)
public/css-new/fonts/inter/inter-latin-ext-wght.woff2    1ad231aac0a8a891b8374aa5526a5813  (moi)
public/css-new/fonts/inter/inter-vietnamese-wght.woff2   9df17551da76cba6ee2d5d35fc762ed9  (moi)
public/css-new/dist/base.min.css                         2ee575e111dc23abcc56a6084ffa7722
public/css-new/src/base.css                              5070fb94db8b5466535c1bdc346a5d81
resources/views/web/layout.blade.php                     3bf5ef4c6331c6c64fd309c718801f14
resources/views/errors/404.blade.php                     4b2490c4f6466074717ecc87a6f9f3f1
```
Blade tren host tu re-compile vi `put` giu mtime moi hon; neu HTML van con `fonts.googleapis`
thi vao `/backend/clear-cache` (dang nhap admin; `/clear-cache` public KHONG clear view).

**Khong pham vi (bao lai de nghi):** `public/css/style.css` + 4 file `Inter_18pt-*.ttf` (344 KB/tam)
van con dung cho `frontend/payment/index.blade.php` (`layouts.frontend`) nen gi lai.
Admin/auth van xai `Source+Sans+Pro` cua Google (font khac, chua touch).
Muc 3 xong thi con **nhóm A** (banner hero mobile 142.7 KB + 3 tam home-promo PNG) la nut co that cua LCP.

### Deploy + ket qua vong 9 (31/10, luc ~12:2x)

`winscp-backup-fonts.txt` (4 file ghi de → `deploy/backup-host-2026-10-31/`) →
`winscp-upload-fonts.txt` (7 file, co `mkdir fonts/inter`) → `winscp-verify-fonts.txt`:
**md5 ca 7 file khop local 100%** (260c81a4 / 1ad231aa / 9df17551 / 2ee575e1 / 5070fb94 / 3bf5ef4c / 4b2490c4).
HTML live tren demo: **0** chuoi `fonts.googleapis|gstatic`, 3 URL `css-new/fonts/inter/*.woff2` nam trong
CSS inline; `curl -I` font → `200`, `content-type: font/woff2`, `cache-control: max-age=2592000`.
Khong can clear cache (Blade tu re-compile nho mtime).

`font-audit.js https://demo.sandienmay.com/`: **0 request Google**, 2 request font (latin 47.4 KB +
vietnamese 10.3 KB = **57.7 KB**, khong tai latin-ext 83 KB), width 6 weight khop **tuyet doi**
voi nguyen tron (741.55/744.52/747.55/750.41/754.03/754.03), platform fonts `.current-price` = `Inter x10`.
`verify-demo.js` 5 loai trang (home / danh muc / chi tiet / tin-tuc-may / 404): console sach,
Glide 6/8, quickview 32, khong 404 moi (chi con `/images/no-image.png` **da bao cao tu vong truoc**).

| | mobile | desktop |
|---|---|---|
| Performance home | 89 → **89 / 90** (2 lan do) | 96 → **98** |
| LCP home | 3541 → 3681 / 3500 ms (noise) | 1217 → **1000 ms** |
| Speed Index home | 2840 → 2433 / 2000 | 891 → 900 |
| Trong trang home | 651 → **567 KiB** (−84 KB dung bang font) | 1233 → **1136 KiB** |
| a11y home | 98 (heading-order co san) | — |
| Danh muc mobile | perf **95**, a11y **100**, 0 audit fail, 429 KiB | — |

**Ket luan:** cut Google Fonts khong doi LCP mobile (van la banner hero `image_mobile` 142.7 KB = nhom A),
nhung an -84 KB weight va -14 request; desktop +2 diem va LCP 1.0 s.

**Phat hien lan can (KHONG phai do vong nay, da do width font khop tuyet doi):**
trang danh muc tai viewport 390 co `documentElement.scrollWidth = 410` (> innerWidth 20 px),
ep `font-family: sans-serif` thi con 390 → **no thuoc ve text/pseudo-element dung Inter**,
khong tim thay element nao co `right > innerWidth` trong DOM (nghi la `::before/::after`).
Chua dau va, cho lenh.

---

## VONG 10 (02/11) — Header mobile: cuon len la header hien ra (position: sticky)

**Lenh user:** "o mobile khi minh luot xuong xong minh cuon len mot cai header no dang k hien thi ra,
phai la khi cuon len la hien thi header phia tren cung".

**Nguyen nhan (do bang `deploy/header-probe.js` tren demo, 390x844):**
`@media (max-width: 992px)` khai bao `.site-header { position: relative }` (layout.css:2210) → header nam
trong dong chay, cuon xuong la troi theo trang. JS van them/bo class du, nhung rule ngay duoi do
`.site-header.hidden { transform: none }` (layout.css:2215) **vo hieu hoa** `translateY(-100%)` → khong co gi
hien ra khi cuon nguoc. So lieu demo truoc fix: y=1200 (cuon len) → `position: relative`, rectTop **-1200**,
visible = false. Desktop (>992px) header `fixed` nen van dung → chi mobile bi hong.

**Sua (2 file CSS, KHONG dong JS/Blade):**
- `.site-header`: `relative` → **`sticky`** (khong dung `fixed` de `.main` khong phai bu margin 70px header).
- **Xoa** `.site-header.hidden { transform: none }` de `.site-header.hidden{transform:translateY(-100%)}`
  trong `base.css` (ap dung moi width) co hieu luc tren mobile.

**Deploy:** `winscp-backup-header.txt` (2 file → `deploy/backup-host-2026-11-02/`, da xac nhan ban backup
chua `position:relative` va 0 lan `sticky`) → `winscp-upload-header.txt` → `winscp-verify-header.txt`:
**md5 2/2 khop local** (492d48b5 layout.min.css, c11e2a0e layout.css). HTML live tren demo:
`site-header{position:sticky;height:auto;padding:0}` = 1, `position:relative` = 0,
`site-header.hidden{transform:none}` = 0. Khong sync `.env`, khong clear cache (CSS inline vao HTML nen
doi CSS = doi document, khong co `?v=`).

**Hanh vi sau fix tren demo** (`header-probe.js`): y=0 sticky/rectTop 0; y=1500 `translateY(-70px)` rectTop -70
(an); y=1200/900/600 **rectTop 0, visible = true** (hien). Regression da kiem: desktop 1280 van `fixed` +
an/hien nhu cu; trang danh muc + chi tiet san pham cung hanh vi; `.header-top` nen trang cao dung 70px nen
khi din khong cho noi dung xuyen qua; mo drawer `#modelMenuMobi` luc header dang din → drawer van nam tren
(`elementFromPoint(150,20)` = `modelMenuMobi`, khong phai header).

| | mobile (home) | desktop (home) |
|---|---|---|
| Performance | 89/90 → **89** (khong tuthem) | 98 → **99** |
| LCP | 3500-3681 → **3679 ms** (noise) | 1000 → **1023 ms** |
| FCP | — | — (335 ms) |
| TBT | 0-3 → **3 ms** | 0 → **0** |
| CLS | 0.004 → **0.0041** | 0.0005 |
| Trong trang | 567 → **567 KiB** (khong doi) | 1136 → **1148 KiB** (+12) |
| a11y / BP / SEO | 98 / 100 / 93 | 98 / 100 / 92 |

**Audit fail con lai (deu da bao cao, khong phai vong nay):** `heading-order` x2 (h3.product-name,
h4.news-title) va `is-crawlable` (= `x-robots-tag: noindex, nofollow` + `<meta name=robots>` do
`APP_NO_INDEX` ban thu nghiem — co y, dung sua).

**Tool tai su dung:** `deploy/header-probe.js <url> [W] [H] [shot.png] [drawer]` — cuon 0→1500→1200→900→600→0,
in `position/transform/rectTop/visible/bgSelf/z/bgTop/scrollWidth` + A/B `scrollWidth` ep ve `relative`
+ liet ke phan tu tron ngan 390px. `deploy/lh-read.js <json...>` — diem 4 category + FCP/LCP/TBT/SI/CLS/
weight/request + audit fail.

---

## VONG 11 (02/11) — sitemap.xml dung chuan (2 file PHP/Blade, khong co SQL)

**Yeu cau:** "file sitemap, chinh cho dung cho t".

**5 loi da do duoc tren ban cu:**
1. `<lastmod>` dung `created_at` → 30/86 URL khai sai ngay (duong danh muc/san pham/bai viet da sua muon hon).
2. Home hard-code `2026-11-25T09:24:06+00:00` (fix trong blade, khong lay tu DB).
3. Trang dang mang `seo_no_index = 1` van nam trong sitemap → Google crawl roi bo, ghi nhan
   "Da thu crawl - hien khong duoc lap chi muc".
4. Thieu 2 trang that: `/tin-tuc-may` (danh sach bai viet) va `/tra-cuu-bao-hanh`.
5. `<loc>` trung: danh muc id 368 co slug `tin-tuc-may` bi route tinh `tin-tuc-may` che (route `/{slug}`
   o `routes/frontend.php:85` la route CUOI cung) → 3 vong `@foreach` rieng xuat 2 entry cung 1 URL
   (89 `<url>` / 88 unique).

**Sua:** `SitemapController@index` loc `status` + `whereNull('seo_no_index')->orWhere('seo_no_index', 0)`
(phai kem `whereNull` vi cot nay dang NULL toan bo, `!= 1` loai luon dong NULL trong MySQL), dung
`updated_at`, them 3 entry tinh (home = `max(updated_at)` toan bo entity, `/tin-tuc-may`,
`/tra-cuu-bao-hanh`), **khoá mảng = URL** + `??=` giu lan dau. `sitemap.blade.php` con 1 vong lap.
Thu `?? now()` cua site khac: khong dung duoc o day (`m_categories` 30 / `m_products` 27 / `m_posts` 29 dong,
**0 dong NULL `updated_at`**) va `now()` la ngay bia → thà bo `<lastmod>` (spec cho phep).

**Chung minh bo loc noindex chay that:** bat `seo_no_index = 1` cho product id 2281
(`demo-may-hut-bui-kms601`) → 88 → **87**; phoi ve NULL → **88**. DB local nguyen trang.

**Deploy:** `winscp-backup-sitemap.txt` (2 file → `deploy/backup-host-2026-11-02/sitemap-controller`
`.../sitemap-view`; ban cu tren demo 742 B, md5 `fc9eb163` / `d807ea75`, chua dung `created_at`)
→ `winscp-upload-sitemap.txt` → `winscp-verify-sitemap.txt`: **md5 2/2 khop local**
(`784576bf` SitemapController.php 2.484 B, `e2f14a7b` sitemap.blade.php 542 B). **Khong sync `.env`.**
Blade tu compile theo mtime → khong clear cache.

**Ket qua tren demo** (`deploy/sitemap-audit.php`): 88 `<url>` / 88 unique / trung 0 / lastmod sai dinh
dang 0 / khac host 0 / duoi tran 50.000. `deploy/sitemap-status.php`: **88/88 tra ve 200** (khong theo
doi redirect nen 301 cung lo duoc). Vi du: home `2026-11-02T08:14:07+00:00`, `/tra-cuu-bao-hanh` khong
`<lastmod>` (khong co ban ghi).

**Chua lam / bao cao:**
- Toan site demo van `x-robots-tag: noindex, nofollow` + `<meta name=robots>` (`APP_NO_INDEX` ban thu
  nghiem) → sitemap demo dung ve ky thuat nhung **nop len GSC la vo nghia** cho toi khi tat co che do do.
- `public/robots.txt:20` khai bao `Sitemap: https://sandienmay.com/sitemap.xml` → dung cho domain chinh,
  sai tren demo.
- Dead code: `app/Enums/IsSitemapEnum.php` + `BaseService::getSitemapIndexData()` (`:52-94`) — cot
  `is_sitemap` **khong ton tai** trong DB, admin khong co cong tac bat/tat sitemap tung bai.
- `->get()` toan bang, khong chunk/cache; tran spec 50.000 URL/1 sitemap → khi vuot phai dung sitemap index.

**Tool tai su dung:** `deploy/sitemap-audit.php <sitemap-url>` (parse XML, dem unique/trung, W3C lastmod,
khac host, tran 50k) · `deploy/sitemap-status.php <sitemap-url>` (curl_multi HEAD-less, tat ca `<loc>`
co phai 200 khong; dat UA `sitemap-status-check` de phan biet voi crawler that khi doc access log).

---

## VONG 12 (02/11) — Breadcrumbs trung 1 cap tren trang chi tiet bai viet

**Yeu cau:** screenshot `demo.sandienmay.com/demo-hd-chon-may-hut-bui` → breadcrumb hien cung 1 danh muc 2 lan.

**Nguyen nhan (duoc chung minh, KHONG phai loi code):** bang trung gian `m_category_post` co **1 dong trung**
(post 13910 × category 374, id `13939` duplicate cua id `13910`). `PostController.php:248` lay
`$similarCategories = $post->categories` (relation tho, khong unique) → view `web/post/detail.blade.php:145-153`
lap lai thanh 2 muc. Cung dong nay nam trong **dump production** → demo va site that deu bi.
`m_category_product` da kiem: sach.

**Da lam local:** xoa `m_category_post.id = 13939` → breadcrumb len 1 cap, block "Danh muc" cung het trung
(dem `grep -o | sort | uniq -c`: 6 card / 5 unique → 6/6).

**`deploy/sql-fix-category-post-dup.sql`** (chua chay tren demo): pre-flight dem so nhom trung → sinh
`INSERT ... ` statement de phuc hoi → `DELETE cp JOIN (SELECT post_id, category_id, MIN(id) keep_id ...
HAVING COUNT(*)>1) d ON ... AND cp.id <> d.keep_id` → 2 query kiem tra sau. cuc `ALTER TABLE m_category_post
ADD UNIQUE KEY uq_category_post (post_id, category_id)` de nguyen trong comment + rollback.

**Chua lam / cho quyet:**
- (A) Chay `sql-fix-category-post-dup.sql` tren demo (phpMyAdmin hoac script PHP tam token-gated qua FTP).
- (B) Them `UNIQUE KEY uq_category_post` — can quyet vi phai INSERT trung ve sau (admin khong the tao trung
  bang UI: form bai viet render 1 checkbox / category id, nen `Backend/PostController` KHONG da duoc sua;
  code da duoc hoan tac 3 edit, kiem chung 0 lan xuat hien `uniqueCategoryIds`).
- `web/post/detail.blade.php:38` dung `??` cho `seo_title` → 3/29 bai co `seo_title = ''` (chuoi rong, `??`
  khong bo qua) → `BlogPosting.headline = ""` va `BreadcrumbList` name cuoi rong. Sua `??` → `?:` la 1 dong,
  nhung thuoc pham vi khac → cho lenh.

## VONG 13 (02/11) — Nut "Yeu cau bao gia": PC mo popup form, mobile giu tel:

**Yeu cau:** screenshot hop thoai Windows "Select an app to open this 'tel' link" — PC bam nut bao gia bi loi,
chuyen sang popup form de sale lien he lai.

**3 lua chon da chot:** chi popup tren PC (mobile van `tel:`) · tai dung bang `contacts` (khong migration,
sale xem san o `/backend/contact`) · Ten + SDT bat buoc, email tuy chon.

**Code (7 file):**
- `app/Http/Requests/QuoteRequest.php` (MOI): name/phone bat buoc, `phone` regex `^[0-9+.\s()-]{8,20}$`,
  email nullable, `product_id` `Rule::exists('m_products','id')`, quantity 1..999, note <= 2000, messages tieng Viet.
- `app/Http/Controllers/Frontend/ContactController.php`: `store()` giu nguyen; them `quote()` — ten san pham +
  URL lay tu DB theo `product_id` (khong tin chuoi client gui), ghep 3 dong vao `content`.
  `email` dung `?? null` (KHONG `?: null`): `validated()` kh tra key khi input bo trong → `?:` throw 500
  "Undefined array key \"email\"" — da dung trong browser.
- `routes/frontend.php:76-78`: `POST /yeu-cau-bao-gia` + `throttle:10,1`.
- `resources/views/web/product/_first-screen.blade.php`: nut `btn-quote` bo `onclick` tel:, them
  `data-quote-phone`; `<dialog id="quoteDialog">` + form dung lai pipeline AJAX san co (`data-onsuccess/
  data-onerror`, `@csrf`). Nam trong nhanh `@if ($product['instock'] && $showFull)` → popup xem nhanh
  (`showFull=false`) KHONG render dialog → khong the ID `quoteDialog` (`.btn-quote` cua no la link
  "XEM CHI TIET", khong co `data-quote-phone` nen handler bo qua).
- `public/js-new/fe/pages/product.js` (+ `product.min.js`): delegated click, `matchMedia('(pointer: coarse)')`
  → `location.href='tel:'`, nguoc lai `showModal()` + focus + copy so luong tu `.input-number` sang
  `input[name=quantity]` an. `onQuoteError` xoa `setCustomValidity` cua input an (bay chan submit lan sau)
  va **luon ra toast do** — 429 throttle khong co `errors` → trc day im lang.
- `public/css-new/src/product.css` (+ `dist/product.min.css`): dialog `min(460px, 100vw-32px)`,
  `max-height: calc(100vh - 24px)` + `overflow:auto` (cua so laptop thap), `::backdrop` navy 55% (top layer,
  khong can scrim hack, khong khoa scroll body → tranh bay no 7px).
- `resources/views/backend/customer/contact.blade.php`: `white-space: pre-line` cho cot `content` de sale doc
  3 dong de dang (mac dinh HTML gom newline thanh khoang trang).

**Da verify local (browser that, khong phai curl):** PC click → dialog 460x555, focus `name`,
`product_id=2281`, quantity dong bo; submit → 200, dialog dong, toast xanh, `contacts` vao dung 3 dong
(`[Yeu cau bao gia] May hut bui cong nghiep Kumisai KMS601 — So luong: 2 / Trang san pham: ... / Ghi chu: ...`).
422 (phone `abc`) → toast do `#ED1B24` "So dien thoai khong hop le.", dialog mo lai, **submit lai duoc ngay**
(khong bi chan lan 2). 429 (burst 12 request → 10x422 + 2x429) → toast do "Too Many Attempts.", khong vao DB.
Nhanh `pointer:coarse` → dialog KHONG mo. Test rows da xoa, `contacts` = 0 dong.

**Deploy demo:** 7 file tren (khong co `.env`, khong co SQL — bang `contacts` da ton tai; kiem tra
`SHOW TABLES LIKE 'contacts'` truoc khi bam nut thu). Blade/CSS/JS tu nap theo mtime, khong clear cache.
Mang build: `npm run build:css` (product.css 46.428 → 32.871 B) + `npm run build:js` (product.js 4.013 → 2.015 B).

## VONG 14 (02/11) — Nut "Mua them san pham khac" trong gio hang

**Yeu cau:** screenshot `demo.sandienmay.com/gio-hang` — nut nay tro ve Trang chu, khach phai tim lai tu dau.

**Sua:** `CartController::index()` truyen `buyMoreUrl` = **URL danh mục sau nhat cua mon cuoi khach bo vao gio**
(`session('carts')` giu thu tu them → `end()`; `CategoryService::getByProduct()` da co cache
`cateByPro_v3-{id}`; `usort` theo `parent_id` TANG dan → **danh muc cap 1** (parent_id = 0) duoc uu tien (user da
chinh: ve `may-hut-bui-cong-nghiep`, khong ve `may-hut-bui-kumisai` - danh muc con o day gán theo
thuong hieu va admin hay gan lech).). Fallback Trang chu khi: gio rong / `product_id` khong con trong DB / san pham khong co danh muc.
`web/cart/index.blade.php:185` dung `{{ $buyMoreUrl ?? route('frontend.page.home') }}`.

**Khong dung** `history.back()` / `document.referrer`: khach quay lai chinh trang san pham ho vua xem (it huu ich
hon danh muc), referrer mat khi tu ngoai vao, va link that `<a href>` van co loi cho SEO + khong JS.

**Verify local (browser that):**
- gio 1 SP 2281 (chi co danh muc cap 1 348) → `http://sandienmay.test/may-hut-bui-cong-nghiep` (mo dung trang, 20 card).
- gio 2 SP, mon cuoi 2296 (348 + 360) → `/may-hut-bui-cong-nghiep` (cap 1 thang, khong phai cap con).
- dung bang hinh cua screenshot: SP 2288 `dq-tech-dq-80l-2-motor` (348 + 357) → **`/may-hut-bui-cong-nghiep`**
  (da do lai sau khi user chinh yeu cau; truoc do sort nguoc ra `/may-hut-bui-kumisai`).
- Kiem DB: **0/27** SP chi co danh muc con (khong gan cap 1) → nhanh fallback cap con chua duoc du lieu kich hoat.
- `GET /cart/destroy` → block `@if(session('carts'))` (dong 74-194) khong render → khong con nut, khong loi.
- `php -l` CartController: khong loi cú pháp.

**Bao cao (khong sua):** `web/cart/index.blade.php` khong co `@else` cho gio hang rong → khach thay trang tran,
khong co thong bao "gio hang trong" / nut dan di dau. Day la loi cu, ngoai pham vi yeu cau.

**Tong goi deploy VONG 13 + 14 = 9 file** (khong `.env`, khong SQL): them 2 file so voi VONG 13 la
`app/Http/Controllers/Frontend/CartController.php` va `resources/views/web/cart/index.blade.php`.

### Ket qua deploy VONG 13+14 (02/11, 9 file)

- `winscp-backup-quote-cart.txt` → 8 file cu ve `deploy/backup-host-2026-11-02/quote-cart/` (md5 ban cu:
  ContactController `67afb0ad` 412 B, CartController `fc77c0a8` 8.747 B, routes/frontend.php `76770698` 5.342 B,
  _first-screen `fc249b5f` 39.533 B, backend contact.blade `1ea4c8e5` 2.745 B, cart index `3f7f128d` 15.727 B,
  product.min.js `a492d392` 1.058 B, product.min.css `a864c327` 31.265 B). QuoteRequest.php la file MOI.
- `winscp-upload-quote-cart.txt` → 9/9 OK. **Khong dong `.env`.**
- `winscp-verify-quote-cart.txt` (lay nguoc ve `deploy/verify-quote-cart/`) → **md5 khop 9/9**:
  `adf169b4` QuoteRequest 1.450 B · `88f8f3a2` ContactController 1.815 B · `845ca840` CartController 10.069 B ·
  `9c0eac40` routes/frontend.php 5.476 B · `a3718ea3` _first-screen 42.380 B · `213271b8` backend contact 2.775 B ·
  `78c993fc` cart index 15.742 B · `51ad4a1d` product.min.js 2.015 B · `dd2fe14a` product.min.css 32.871 B.
- Asset nghi cache: `product.min.css?v=1793610571` (tu doi theo mtime) → fetch ve 32.871 B, chua `.quote-dialog`,
  `::backdrop`, `max-height:calc(100vh - 24px)`. `product.min.js?v=1793610777` chua `quoteDialog`.
- Demo browser: nut `btn-quote` khong con `onclick`, `data-quote-phone="0967 998 982"`; click → dialog 460x573,
  focus `name`, `product_id=2288`; 422 (phone `abc`) → toast do "So dien thoai khong hop le.", dialog mo lai;
  200 (gui hop le) → toast xanh. `/gio-hang` (gio co DQ-80L) → nut tro `/may-hut-bui-cong-nghiep` (mo ra H1
  "May hut bui cong nghiep", 20 card).
- **1 dong test con sot tren demo** (khong co nut xoa o `/backend/contact`): ten `ZZ TEST VERIFY DEPLOY 02/11`,
  sdt `0900001122`. Day bang `contacts` tren phpMyAdmin demo:
  `DELETE FROM contacts WHERE name = 'ZZ TEST VERIFY DEPLOY 02/11';`

---

## VONG 15 - Fix <title> trong trang chi tiet san pham (02/11, CHUA DEPLOY)

**Loi user bao cao va da duyet sua** ("lua giup toi loi seo do di"): toan bo trang chi tiet san pham
render `<title></title>` + `<meta name="description" content="">`.

**Nguyen nhan (da do, khong doan)**: `m_products` co 27/27 san pham thieu seo_title/seo_description/
seo_url_canonical. `ProductDetailDto.php:43-46` cast `(string)` nen NULL -> `''`. Controller dung `??`
ma `??` khong coi `''` la rong -> `SeoHelper::setTitle('')` -> title trong. Cung la bai hoc `??` vs `?:`
da gap o seo_title danh muc (VONG 12).

**Sua**: `app/Http/Controllers/Frontend/New_ProductController.php:129-134` - 3 dong setTitle/
setDescription/setKeywords doi `??` -> `?:` (fallback ve `name`), khong doi DTO, khong doi DB.
`php -l` sach. **md5 local `38daab7c0324916f8919d95bb7648ee0`, 45.617 B** (1 file duy nhat, khong asset,
khong SQL, khong `.env`).

**Verify local**: `demo-may-hut-bui-kms601` -> title "May hut bui cong nghiep Kumisai KMS601";
`kumisai-kms-601-60-lit` -> "May hut bui nuoc Kumisai KMS-601 60 lit"; canonical san (dong setUrl da
dung `?:` tu truoc). **Da quay vong du 27/27 URL san pham: 0 trang con `<title>` rong** (slugs go tay,
khong lay tu mysql - xem canh bao CRLF duoi day).

> **Canh bao khi lap lai bang shell**: `mysql.exe -N -B` tren Windows in ra **CRLF**, du `tr -d $'\r'`
> van con sot CR trong bien -> `curl http://host/$s` tra **`000 0`** (yeu cau malform) trong khi in ra
> thi khong nhin thay. Dau hieu nhan biet: chi DONG CUOI CUNG chay dung, cac dong truoc do deu `000`.
> Lan dau gap hien tuong nay thi do la bug cua script, KHONG phai cua site - kiem tra lai bang cach go
> tay 1 URL roi moi ket luan "trang bi loi".

### Kham nghiem toan site khi fix (deploy/seo-audit.sh - chay duoc tren demo sau khi upload)

| Loai trang | Vi tri | Hien trang |
|---|---|---|
| San pham (27/27 thieu SEO field) | New_ProductController:132-134 | DA SUA + verify local |
| Bai viet: 4/29 co seo_title = '' -> `<title></title>` | DtoSeoTrait:19-20 (`??`) | CHUA SUA - bao cao user |
| Bai viet: 24/29 co seo_url_canonical = '' -> `<link rel="canonical" href="">` | DtoSeoTrait:22 (`??`) | CHUA SUA - khong hai (href="" tro ve chinh URL) nhưng lem nhem |
| Danh muc: 8/30 thieu seo_description -> meta description rong | CategoryController:128 (`??`) | CHUA SUA - title da co `?:` o :127 nen khong trong |
| Schema BlogPosting `headline` cua bai viet rong | web/post/detail.blade.php:38 (`??`) | CHUA SUA - cung locus `??` |

-> User quyet: "fix het" (DtoSeoTrait + CategoryController + detail.blade.php, moi cho chi thay 1 dong
`??` -> `?:` co fallback) hay chi de rieng VONG 15 (1 file san pham) len demo.

### Ket qua deploy VONG 15 (03/11, 1 file)

- `winscp-backup-seo-product.txt` -> ban cu tren host ve
  `deploy/backup-host-2026-11-02/seo-product/controllers/`: md5 `9c184294`, 45.304 B (van la 3 dong `??`).
  **Day la duong rollback duy nhat** (upload lai file do bang cung cuc `lcd`/`cd` + basename).
- `winscp-upload-seo-product.txt` -> OK, **1 file**, khong `.env`, khong SQL.
- `winscp-verify-seo-product.txt` -> lay nguoc ve, **md5 `38daab7c` khop local** (45.617 B).
- Quan trong ve duong dan: host **khong co** ngoai `app/` nhu duong dan tuyet doi
  `/home/ynlbdgfvhosting/public_html/...` -> FTP report sai "No such file or directory". **Phai viet tu
  home FTP: `cd "/public_html/demo.sandienmay.com/app/Http/Controllers/Frontend"`** (giong cac vong 13+14).
- Do demo (UA desktop, 27 URL san pham, go tay slug vi mysql.exe in CRLF lam curl tra 000):
  **26/26 trang ton tai co <title> + meta description, 0 trang rong**; 1 URL
  `superclean-sp-100` la fixture rieng local -> 404 tren demo (dung, khong phai loi).
- Thu nghiem mobile (iPhone UA): home, `may-hut-bui-cong-nghiep`, `kumisai-kms-c1500` deu 200 va title
  giong het desktop -> dong loi KHONG thay doi theo thiet bi.
- **Phat sinh khong do code**: `kumisai-kms-c1500` tren demo chay ra `<title>cccc</title>` (ca desktop lan
  mobile). Day la du lieu `m_products.seo_title='cccc'` trong DB **demo** (local la NULL), admin de lai sau
  lan test. Uu tien du lieu -> code dung. Can xoa hoac dien lai trong `/backend/product/.../edit` tren demo.

---

## VONG 16 - Dien alt cho TOAN BO anh frontend (24 file blade, DA DEPLOY 03/11)

**User yeu cau**: screenshot DevTools tren demo cho thay `alt=""` o card "Tin tuc lien quan"
(`/may-hut-bui-cong-nghiep`), o icon danh muc mega menu + sidebar trang chu. Ket luan cua user:
"cho nao k co alt thi bo het vao". **Quyet dinh: dien alt vao moi `alt=""` va moi `<img>` thieu alt**,
ke ca nhung cho truoc day de co tinh `alt=""` vi a11y.

**Kiem tra truoc khi sua (control)**: Lighthouse a11y local `http://sandienmay.test/may-hut-bui-cong-nghiep`
= **95**, chi 1 audit fail la `color-contrast` (3 node) -> alt khong phai nguyen nhan.

### Cac cho da dien (nhan cua moi nhom)

| File | So anh | Chu gi alt dung |
|---|---|---|
| `web/header.blade.php` | 3 lap ticker topbar | `{{ $item['text'] }}` (USP) |
| `web/header.blade.php` | 2 | "Goi dien tu van", "Gio hang" |
| `components/fe/menu.blade.php` | badge HOT/NEW | "Danh muc ban chay" / "Danh muc san pham moi" (da bo `aria-hidden` vi anh bay gio mang nghia) |
| `components/fe/menu.blade.php` | rail mega + sidebar trang chu | "icon danh mục {{ $cate['name'] }}" (dong nhat voi drawer mobile :100/:124/:134) |
| `web/home/index.blade.php` | cat pills | "icon danh mục {{ $i2['name'] }}" |
| `web/product/category.blade.php` | 5 thumb "Tin tuc lien quan" | `{{ $item['name'] }}` (da xoa comment "alt rong vi a11y") |
| `components/fe/products.blade.php` | anh hover (anh thu 2) | "Ảnh khác của {{ $product['name'] }}" |
| `web/product/compare.blade.php`, `web/ajax/_modal-compare.blade.php` | 2 | `{{ $product['name'] }}` |
| `web/post/detail.blade.php`, `web/product/detail.blade.php` | avatar hoi dap | "Ảnh đại diện {{ $r['author'] ?? 'quản trị viên' }}" |
| `web/product/_first-screen.blade.php` | 5 (gif + icon1-4) | "Bieu tuong qua tang - ma khuyen mai" + 4 cam ket noi dung day du |
| `web/mobile-menu.blade.php` | search.png | "Tim kiem" |
| 11 file breadcrumb `ic-right.svg` (`components/fe/br.blade.php`, `web/cart/index`, `web/page/{index,contact,warranty}`, `web/payment/success`, `web/customer/{login,register,account,orders,order}`) | 1 | "Mui ten phan cach breadcrumb" |
| `components/fe/product-list.blade.php`, `components/setting.blade.php` | 2 | **2 component chet** (khong `@include` noi nao) - thieu hoan toan alt, da them cho du |

### Kiem nghiem local (khong deploy van con chay duoc tren demo sau nay)

Cong cu do HTML that (khong doc blade): `/tmp/altcheck.pl` - dem `<img>` thieu alt va `alt=""`, slurp
ca the `<img>` nhe (grep `'<img[^>]*>'` theo dong **bo sot** the cat ngang dong).

| Trang | img | thieu alt | `alt=""` |
|---|---|---|---|
| `/` | 181 | 0 | 0 |
| `/may-hut-bui-cong-nghiep` | 112 | 0 | 0 |
| `/kumisai-kms-c1500` | 90 | 0 | 0 |
| `/tin-tuc-may` | 111 | 0 | 0 |
| `/demo-may-hut-bui-1` | 77 | 0 | 0 |
| `/gio-hang` | 73 | 0 | 0 |
| `/tim-kiem?keyword=may` (-L) | 181 | 0 | 0 |
| `/tra-cuu-bao-hanh` | 73 | 0 | 0 |
| `/tai-khoan/dang-nhap`, `/dang-ky` | 73 | 0 | 0 |
| `/san-pham` | 97 | 0 | 0 |
| `/so-sanh-san-pham/2281-2282` | 75 | 0 | 0 |
| `/demo-hd-chon-may-hut-bui` (bai viet) | 76 | 0 | 0 |

**2 cho con `alt=""` ma co tinh khong sua**: `web/header.blade.php:135-138` (4 icon nam trong block
`{{-- commit-bar --}}` da comment bo, khong render) va `backend/product/_promotion-gifts.blade.php:48`
(khu admin, ngoai pham vi frontend).

### gia phai tra ve a11y (da do sau khi sua)

`image-alt` **pass 100%** o moi trang do. Nhung axe them audit **`image-redundant-alt` = 5 node** o trang
danh muc: 5 thumb "Tin tuc lien quan" co alt trung nguyen van voi `<h3>` ben canh. Diem:

| Trang local | a11y TRUOC | a11y SAU | Audit fail sau khi sua |
|---|---|---|---|
| `/may-hut-bui-cong-nghiep` | 95 | **94** | `color-contrast` 3 (cu), `image-redundant-alt` 5 (MOI) |
| `/` | chua do | 92 | `color-contrast` 3, `heading-order` 2 (khong lien quan alt) |
| `/kumisai-kms-c1500` | chua do | 95 | `color-contrast` 7 |
| `/demo-hd-chon-may-hut-bui` | chua do | 90 | `color-contrast` 11, `document-title` 1 (= bai viet co `seo_title=''`, thuoc b viec VONG 15 da bao cao) |

-> **SEO du 100% alt, a11y danh muc giam 1 diem**. Muon lay lai diem thi dung `alt=""` cho 5 thumb do
hoac viet alt khac nhe `<h3>` (vd "Anh minh hoa: ...") - user quyet.

### Danh sach file len demo (24 file blade, khong asset, khong SQL, khong `.env`)

md5 local ngay 03/11:
```
8a747246d8bc7d38adc42bf0754a7cbd  9789  resources/views/web/header.blade.php
5911affb9f007a54543c7382452607c7  1034  resources/views/web/mobile-menu.blade.php
906eeffbea7abb2b5904017e3e54516f   403  resources/views/components/setting.blade.php
b9127c5608448e098f81c3e5b6a45966 11343  resources/views/components/fe/menu.blade.php
b50e1bab73ed60aca7667de51820ae8d  1141  resources/views/components/fe/br.blade.php
313cd0f92fa0dfa869977cbb069c3a59  5363  resources/views/components/fe/products.blade.php
bee0a72a44d8351492b217a66590f09d 23788  resources/views/web/home/index.blade.php
5c5ec85c658c21697704e71dc4f3f993 18461  resources/views/web/product/category.blade.php
d60fa67356538c1b7b5c9b7ff13ce44d 17919  resources/views/web/product/detail.blade.php
51dea08302d887ec2749b0cfedec580b  3921  resources/views/web/product/compare.blade.php
b3ea3762aa2f161fe832085ef6802c1d 42653  resources/views/web/product/_first-screen.blade.php
54bacd586429eb0de9d02db34f002202 19448  resources/views/web/post/detail.blade.php
aa3015e13dccb0012daab95cb0eabee0  1170  resources/views/web/ajax/_modal-compare.blade.php
4b22c002a2fe8dae34bae7295998e11e 15774  resources/views/web/cart/index.blade.php
1d49acb444be19ab83989f8cf115e8ae  3387  resources/views/web/page/index.blade.php
bc7474e4889c54ea240d3f508fdb1f99  3506  resources/views/web/page/contact.blade.php
d952c4ee31151423a54dd46d62b84339  1479  resources/views/web/page/warranty.blade.php
66193dbd9b7b381651c64bfc1c681a06  5698  resources/views/web/payment/success.blade.php
5b482b0c3f4ad216de8afcdd3c0ce6d7  3218  resources/views/web/customer/login.blade.php
459739719077539d481358ce54b61c54  3930  resources/views/web/customer/register.blade.php
c8be46116906795906ff72029b7f4c87  5779  resources/views/web/customer/account.blade.php
d3d8c321196fa252af5ebd61d910ea7c  4972  resources/views/web/customer/orders.blade.php
9a27ee43d2e04e44da37bc9b3af34dbd  6769  resources/views/web/customer/order.blade.php
24363bd0128e23c4d7edb3dc4d872e0a  1601  resources/views/components/fe/product-list.blade.php
```
Khong can build CSS/JS va khong can `artisan view:clear` tren host: Laravel tam bien lại view khi
`.blade.php` tren host moi hon `.php` da cache, ma tren demo khong co artisan thi cache/view da co
san - neu demo van ra `alt=""` sau khi upload thi do loi cache, bao cao user chu khong tu chay lenh.

**TRANG THAI: da sua + da verify local, CHUA upload. Cho lenh "deploy di" cua user.**

### So lieu baseline tren DEMO ngay truoc khi deploy (do bang chinh `deploy/alt-audit.sh`)

| Trang demo | img | `alt=""` |
|---|---|---|
| `/` | 163 | **60** |
| `/may-hut-bui-cong-nghiep` | 104 | **37** |
| `/kumisai-kms-c1500` | 84 | **38** |

Dac nhan trung voi danh sach da sua (icon1-4.svg x3, hot.svg x6, new.png x3, search.png, shopping-cart.svg,
phone-call-v2.svg, gif.png, ic-right.svg, demo-sp-1/2.png cua thumb tin tuc, cac logo brand 22x22 trong
`data-parent-id` cua cat-pill trang chu). Da doi chieu 1-1: cung the `<img>` do o local bay gio mang
`alt="icon danh mục Máy hút bụi Camry"` (home/index.blade.php:185) trong khi demo van `alt=""` -> upload
24 file la het, khong con nguon nao khac. **Muc tieu sau deploy: 3/3 trang tren `alt_rong=0`.**

### Ket qua deploy VONG 16 (03/11, 24 file blade)

- `winscp-backup-alt.txt` -> **24/24** ban cu tren demo ve `deploy/backup-host-2026-11-03/alt/<duong-dan-tuong-ung>`
  (bang md5 + size o duoi day; da doi chieu: **ca 24 file backup deu KHAC ban moi**, nen day la duong
  rollback that, khong phai ban sao trung). Rollback = `put` nguoc tung file cung cuc `lcd`/`cd` + basename.
- `winscp-upload-alt.txt` (`option batch abort`) -> **24/24 `put` 100%**, khong `.env`, khong SQL, khong asset.
- `winscp-verify-alt.txt` -> `get` nguoc **24 file**, **md5 khop local 24/24 (0 lech)**.
- Khong can clear cache: WinSCP giu mtime cua file local (moi hon compiled view cu) -> Blade tu recompile.
  Chung minh: do demo sau `put` thay `alt=""` = 0 ngay, khong phai xin `/backend/clear-cache`.

| Rollback (ban cu tren demo) | md5 8 dau | size | duong doi |
|---|---|---|---|
| web/header.blade.php | `b6a168c7` | 9736 | `deploy/backup-host-2026-11-03/alt/web/` |
| web/mobile-menu.blade.php | `239201bd` | 1023 | " |
| components/setting.blade.php | `05c8df44` | 376 | `.../alt/components/` |
| components/fe/menu.blade.php | `10b8fa99` | 11211 | `.../alt/components/fe/` |
| components/fe/br.blade.php | `cc11b83e` | 1109 | " |
| components/fe/products.blade.php | `6e1ff6f4` | 5323 | " |
| components/fe/product-list.blade.php | `2c42303e` | 1555 | " |
| web/home/index.blade.php | `f54c977a` | 23755 | `.../alt/web/home/` |
| web/product/category.blade.php | `34499988` | 18556 | `.../alt/web/product/` |
| web/product/detail.blade.php | `35af0144` | 17857 | " |
| web/product/compare.blade.php | `ab8d1f70` | 3899 | " |
| web/product/_first-screen.blade.php | `a3718eaf` | 42380 | " |
| web/post/detail.blade.php | `f1898a67` | 19386 | `.../alt/web/post/` |
| web/ajax/_modal-compare.blade.php | `548264f9` | 1148 | `.../alt/web/ajax/` |
| web/cart/index.blade.php | `78c993fc` | 15742 | `.../alt/web/cart/` |
| web/page/index.blade.php | `487db733` | 3355 | `.../alt/web/page/` |
| web/page/contact.blade.php | `8e6bec6c` | 3474 | " |
| web/page/warranty.blade.php | `9af76b67` | 1447 | " |
| web/payment/success.blade.php | `24c82611` | 5666 | `.../alt/web/payment/` |
| web/customer/login.blade.php | `a4cdeb3d` | 3186 | `.../alt/web/customer/` |
| web/customer/register.blade.php | `beeee59f` | 3898 | " |
| web/customer/account.blade.php | `5da48696` | 5747 | " |
| web/customer/orders.blade.php | `844eff10` | 4940 | " |
| web/customer/order.blade.php | `3b8701b2` | 6737 | " |

**Do lai tren DEMO sau deploy** (`bash deploy/alt-audit.sh https://demo.sandienmay.com ...`):

| Trang | img | `alt=""` TRUOC | `alt=""` SAU |
|---|---|---|---|
| `/` | 163 | 60 | **0** |
| `/may-hut-bui-cong-nghiep` | 104 | 37 | **0** |
| `/kumisai-kms-c1500` | 84 | 38 | **0** |

- Thu nghiem mobile (iPhone UA): home 200, cat-pill ra `alt="icon danh mục Máy hút bụi công nghiệp"` ->
  khong khac desktop.
- **Khong tuyen doi SEO**: `<title>` + meta description cua `kumisai-kms-c1500` van dung nhung gi VONG 15
  tra ra (van la `cccc` vi du lieu DB demo, khong phai do code).
- Lighthouse a11y tren demo sau deploy: **danh muc 100 -> 99** (chi con `image-redundant-alt` 5 node = gia
  cua viec dien alt trung voi `<h3>` ben canh), **home 98 -> 98** (chi con `heading-order` 2, lỗi có sẵn từ
  vòng 9-10, user chưa quyết). `image-alt` pass 100% mọi trang.
- Neu user muon lay lai diem 100 a11y: **khong** quay ve `alt=""`, ma doi 5 alt thumb "Tin tuc lien quan"
  thanh dan dat khong trung nguyen van title (vd `Ảnh minh họa: {{ ... }}`) - do bang `alt-audit.sh` +
  Lighthouse truoc khi ket luan.
