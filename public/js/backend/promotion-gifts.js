/**
 * Khối "Khuyến mãi" ở form sản phẩm: thêm/xoá dòng quà tặng và tải ảnh lên từ máy.
 * Chỉ lo phần nhập — việc dựng HTML lưu vào cột promotion làm bên PHP (PromotionGift::toHtml).
 */
(function ($) {
    $(document).ready(function () {
        let wrapper = $('#gift-wrapper');

        if (!wrapper.length || !wrapper.find('.gift-row').length) {
            return;
        }

        let uploadRoute = wrapper.data('upload-route')
            , csrfToken = $('meta[name="csrf-token"]').attr('content');

        function rowMarkup() {
            return '' +
                '<div class="gift-row">' +
                    '<input type="hidden" class="gift-image">' +
                    '<div class="gift-thumb-wrap">' +
                        '<label class="gift-thumb">' +
                            '<input type="file" accept="image/*" class="gift-file">' +
                            '<img class="gift-thumb-img" src="" alt="">' +
                            '<span class="gift-thumb-empty"><i class="far fa-image"></i>Tải ảnh</span>' +
                            '<span class="gift-thumb-mask">Đổi ảnh</span>' +
                            '<span class="gift-thumb-loading"><i class="fas fa-circle-notch fa-spin"></i></span>' +
                        '</label>' +
                        '<button type="button" class="gift-image-clear" title="Xoá ảnh"><i class="fas fa-times"></i></button>' +
                    '</div>' +
                    '<input type="text" class="form-control gift-name" placeholder="VD: Tặng 01 bàn hút bụi" value="">' +
                    '<button type="button" class="btn btn-outline-danger btn-sm remove-gift-btn" title="Xoá quà"><i class="far fa-trash-alt"></i></button>' +
                '</div>';
        }

        // Tên input phải đánh số liên tục, nếu không hai dòng sẽ ghi đè nhau khi PHP nhận gift[].
        function reindex() {
            wrapper.find('.gift-row').each(function (i) {
                $(this).find('.gift-image').attr('name', 'gift[' + i + '][image]');
                $(this).find('.gift-name').attr('name', 'gift[' + i + '][name]');
            });
        }

        function setImage(row, url) {
            row.find('.gift-image').val(url);
            row.find('.gift-thumb-img').attr('src', url);
            row.toggleClass('has-image', url !== '');
            row.find('.gift-thumb').toggleClass('has-image', url !== '');
        }

        function upload(row, file) {
            let thumb = row.find('.gift-thumb');

            if (!/^image\//.test(file.type)) {
                toastr.error('File chọn không phải là ảnh.');
                return;
            }

            thumb.addClass('is-loading');

            let data = new FormData();
            data.append('file', file);

            $.ajax({
                url: uploadRoute,
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                headers: {'X-CSRF-TOKEN': csrfToken}
            }).done(function (items) {
                if (!items || !items.length || !items[0].url) {
                    toastr.error('Không nhận được đường dẫn ảnh từ server.');
                    return;
                }
                setImage(row, items[0].url);
            }).fail(function (xhr) {
                let error = xhr.responseJSON || {}
                    , msg = (error.errors && error.errors.file && error.errors.file[0]) || error.message;

                toastr.error(msg || 'Tải ảnh lên không thành công.');
            }).always(function () {
                thumb.removeClass('is-loading');
                // Cho phép chọn lại đúng file vừa upload mà không cần reload trang.
                row.find('.gift-file').val('');
            });
        }

        $('#add-gift-btn').on('click', function () {
            wrapper.append(rowMarkup());
            reindex();
        });

        wrapper.on('click', '.remove-gift-btn', function () {
            $(this).closest('.gift-row').remove();
            reindex();
        });

        wrapper.on('click', '.gift-image-clear', function () {
            setImage($(this).closest('.gift-row'), '');
        });

        wrapper.on('change', '.gift-file', function () {
            let row = $(this).closest('.gift-row')
                , file = this.files && this.files[0];

            if (file) {
                upload(row, file);
            }
        });

        reindex();
    });
})(jQuery);
