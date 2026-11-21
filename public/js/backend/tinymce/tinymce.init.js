// var tinymceInit = {
//     height: 500,
//     resize: true,
//     autosave_ask_before_unload: false,
//     powerpaste_allow_local_images: true,
//     plugins: 'image media link advlist lists wordcount code',
//     toolbar: 'undo redo | styles | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | link image media| code',
//     menubar: true,
//     image_title: true,
//     automatic_uploads: true,
//     file_picker_types: 'image',
//     relative_urls: false,
// };
var tinymceInit = {
    height: 750,
    resize: true,
    autosave_ask_before_unload: false,
    powerpaste_allow_local_images: true,
    
    plugins: 'image media link advlist lists wordcount code paste table',
    
    toolbar: 'undo redo | styles | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | link image media table | code',
    menubar: true,
    image_title: true,
    automatic_uploads: true,
    file_picker_types: 'image',
    relative_urls: false,

    // Cấu hình Table đầy đủ chức năng: Thêm/xoá cột, hàng, gộp ô, thuộc tính bảng/ô
    table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablecellprops tablemergecells tablesplitcells',
    table_appearance_options: true,
    table_grid: true,
    table_resize_bars: true,
    table_default_attributes: {
        border: '1'
    },
    table_default_styles: {
        'border-collapse': 'collapse',
        'width': '100%'
    },
    contextmenu: 'link image table',

    content_style: `
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 15px 0;
        }
        table td, table th {
            border: 1px solid #ddd;
            padding: 8px 12px;
            min-height: 30px;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    `,

    // Sử dụng paste_preprocess: Xử lý nội dung ngay trước khi nó được dán vào editor
    // Cách này không dùng e.preventDefault() nên tuyệt đối không bị lỗi không dán được văn bản
    paste_preprocess: function(plugin, args) {
        // args.content là chuỗi HTML chuẩn bị được dán vào editor
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = args.content;
        
        // Tìm và tự động lột bỏ thẻ strong/b nằm bên trong các thẻ h1 -> h6
        tempDiv.querySelectorAll('h1 strong, h1 b, h2 strong, h2 b, h3 strong, h3 b, h4 strong, h4 b, h5 strong, h5 b, h6 strong, h6 b').forEach(function(el) {
            var parent = el.parentNode;
            while (el.firstChild) {
                parent.insertBefore(el.firstChild, el);
            }
            parent.removeChild(el);
        });

        // Gán ngược lại nội dung đã được làm sạch vào args.content
        args.content = tempDiv.innerHTML;
    }
};

function uploadImg(url, cb, value, meta) {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');

    input.onchange = function() {
        var image = this.files[0];
        var data = new FormData();
        data.append("image", image);
        data.append("_token", document.getElementsByName('csrf-token')[0].content);

        $.ajax({
            url: url,
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            type: "post",
            success: function(url) {
                cb(url, { title: image.name });
            },
            error: function(xhr) {
                var res = xhr.responseJSON || {};
                var msg = (res.errors && res.errors.image && res.errors.image[0]) || res.message || 'Không upload được ảnh.';
                toastr.error(msg);
            }
        });
    };

    input.click();
}