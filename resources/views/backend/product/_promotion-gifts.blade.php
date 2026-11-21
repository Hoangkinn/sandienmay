{{--
    Khối "Khuyến mãi" (đồ tặng kèm) ở form sản phẩm: mỗi dòng một món quà (tên + ảnh),
    thao tác giống khối "Mã ưu đãi" của form danh mục. $promotionValue là HTML đang lưu
    trong cột m_products.promotion. Không đổi schema: ProductController gọi
    PromotionGift::toHtml() dựng lại HTML từ danh sách dòng khi lưu.
--}}
@php
    $isGiftList = \App\Helpers\PromotionGift::isGiftList($promotionValue);
    $giftRows = $isGiftList
        ? (old('gift') ?: \App\Helpers\PromotionGift::parse($promotionValue))
        : [];
    if ($isGiftList && count($giftRows) === 0) {
        $giftRows = [['name' => '', 'image' => '']];
    }
@endphp

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">{{ __('Khuyến mãi') }}</h3>
        @if ($isGiftList)
            <div class="card-tools">
                <button type="button" id="add-gift-btn" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i> {{ __('Thêm quà mới') }}
                </button>
            </div>
        @endif
    </div>
    <div class="card-body" id="gift-wrapper" data-upload-route="{{ route('backend.dropzone.uploadImage') }}">
        @if (!$isGiftList)
            <div class="alert alert-warning">
                {{ __('Nội dung đang ở dạng HTML tự do nên vẫn dùng trình soạn thảo cũ. Muốn chuyển sang danh sách quà tặng theo dòng, xoá hết nội dung ô này rồi lưu lại.') }}
            </div>
            <div class="form-group">
                <label for="promotion">{{ __('Khuyến mại') }}</label>
                <textarea id="promotion" name="promotion" class="form-control">{{ $promotionValue }}</textarea>
            </div>
        @else
            @foreach ($giftRows as $i => $gift)
                @php
                    $giftImage = trim((string) ($gift['image'] ?? ''));
                    $giftName = (string) ($gift['name'] ?? '');
                @endphp
                <div class="gift-row{{ $giftImage === '' ? '' : ' has-image' }}">
                    <input type="hidden" name="gift[{{ $i }}][image]" class="gift-image" value="{{ $giftImage }}">
                    <div class="gift-thumb-wrap">
                        <label class="gift-thumb{{ $giftImage === '' ? '' : ' has-image' }}">
                            <input type="file" accept="image/*" class="gift-file">
                            <img class="gift-thumb-img" src="{{ $giftImage }}" alt="">
                            <span class="gift-thumb-empty"><i class="far fa-image"></i>{{ __('Tải ảnh') }}</span>
                            <span class="gift-thumb-mask">{{ __('Đổi ảnh') }}</span>
                            <span class="gift-thumb-loading"><i class="fas fa-circle-notch fa-spin"></i></span>
                        </label>
                        <button type="button" class="gift-image-clear" title="{{ __('Xoá ảnh') }}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <input type="text" name="gift[{{ $i }}][name]" class="form-control gift-name"
                           placeholder="{{ __('VD: Tặng 01 bàn hút bụi') }}" value="{{ $giftName }}">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-gift-btn" title="{{ __('Xoá quà') }}">
                        <i class="far fa-trash-alt"></i>
                    </button>
                </div>
            @endforeach
        @endif
    </div>
</div>
