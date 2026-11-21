@php
    try {
        $hotline = \App\Services\FeServices\SettingFieldService::getKey('new_phone_hn');
    } catch (\Throwable $e) {
        $hotline = '';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>404 - Trang bạn đang tìm không tồn tại</title>
    <style>
        /* Inter self-host (giong trang web/layout.blade.php), chi 2 subset page nay can. */
        @font-face {
            font-display: swap;
            font-family: Inter;
            font-style: normal;
            font-weight: 400 800;
            src: url(/css-new/fonts/inter/inter-latin-wght.woff2?v=2026) format("woff2");
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        @font-face {
            font-display: swap;
            font-family: Inter;
            font-style: normal;
            font-weight: 400 800;
            src: url(/css-new/fonts/inter/inter-vietnamese-wght.woff2?v=2026) format("woff2");
            unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
        }
    </style>
    <style>
        :root {
            --navy: #26348B;
            --navy-hi: #3848B6;
            --ink: #1F2430;
            --muted: #6B7280;
            --line: #E7EAF2;
            --bg: #F5F7FC;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: var(--ink);
            background:
                radial-gradient(900px 420px at 50% -140px, #E4E9F8 0%, rgba(228, 233, 248, 0) 100%),
                var(--bg);
        }

        img {
            display: block;
            max-width: 100%;
            height: auto;
            width: 100%;
        }

        a {
            color: var(--navy);
        }

        .e404-call {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            text-decoration: none;
            white-space: nowrap;
        }

        .e404-main {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 40px 20px 48px;
        }

        .e404-card {
            width: 100%;
            max-width: 1060px;
            margin: 0 auto;
            padding: 28px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(38, 52, 139, .10);
            text-align: center;
        }

        .e404-art {
            margin: 0;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #EDF1FA;
        }

        .e404-title {
            margin: 12px 0 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -.01em;
        }

        .e404-desc {
            max-width: 540px;
            margin: 10px auto 0;
            color: var(--muted);
            font-size: 16px;
        }

        .e404-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 26px;
        }

        .e404-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 46px;
            padding: 0 26px;
            border-radius: 10px;
            border: 1.5px solid transparent;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color .2s ease, color .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .e404-btn-primary {
            background: var(--navy);
            color: #fff;
            box-shadow: 0 10px 22px rgba(38, 52, 139, .22);
        }

        .e404-btn-primary:hover {
            background: var(--navy-hi);
            transform: translateY(-2px);
        }

        .e404-btn-ghost {
            background: #fff;
            border-color: var(--navy);
            color: var(--navy);
        }

        .e404-btn-ghost:hover {
            background: var(--navy);
            color: #fff;
            transform: translateY(-2px);
        }

        .e404-btn:focus-visible {
            outline: 3px solid #9FB0EA;
            outline-offset: 2px;
        }

        .e404-links {
            margin: 24px 0 0;
            padding-top: 20px;
            border-top: 1px dashed var(--line);
            font-size: 14px;
            color: var(--muted);
        }

        .e404-links a {
            display: inline-block;
            margin: 6px 4px 0;
            padding: 6px 14px;
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--ink);
            font-weight: 500;
            text-decoration: none;
            transition: border-color .2s ease, color .2s ease, background-color .2s ease;
        }

        .e404-links a:hover {
            border-color: var(--navy);
            background: #EEF1FB;
            color: var(--navy);
        }

        .e404-foot {
            padding: 16px 20px 26px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }

        @media (max-width: 576px) {
            .e404-main {
                padding: 24px 14px 32px;
            }

            .e404-card {
                padding: 16px;
                border-radius: 16px;
            }

            .e404-title {
                font-size: 22px;
            }

            .e404-desc {
                font-size: 15px;
            }

            .e404-actions {
                flex-direction: column;
            }

            .e404-btn {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .e404-btn,
            .e404-links a {
                transition: none;
            }

            .e404-btn:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>

    <main class="e404-main">
        <div class="e404-card">
            <figure class="e404-art">
                <img src="{{ asset('images/404.jpg') }}" alt="404 - Trang bạn tìm không tồn tại" width="1024"
                    height="506" loading="eager" fetchpriority="high">
            </figure>

            <h1 class="e404-title">Không tìm thấy trang này</h1>
            <p class="e404-desc">Trang bạn truy cập không tồn tại, đã bị xoá hoặc đổi sang địa chỉ mới.
                Bạn hãy thử lại sau, hoặc chọn nhanh các mục dưới đây.</p>

            <div class="e404-actions">
                <a href="{{ url('/') }}" class="e404-btn e404-btn-primary">Về trang chủ</a>
                <a href="{{ url('/san-pham') }}" class="e404-btn e404-btn-ghost">Tham khảo sản phẩm</a>
            </div>

            <p class="e404-links">
                Gợi ý cho bạn:
                <a href="{{ url('/may-hut-bui-cong-nghiep') }}">Máy hút bụi công nghiệp</a>
                <a href="{{ url('/may-cha-san') }}">Máy chà sàn</a>
                <a href="{{ url('/may-rua-xe') }}">Máy rửa xe</a>
                <a href="{{ url('/tin-tuc-may') }}">Tin tức</a>
            </p>
        </div>
    </main>

    <div class="e404-foot">
        © {{ date('Y') }} {{ config('app.name') }} — Thiết bị công nghiệp
    </div>
</body>
</html>
