<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Phản hồi') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Phản hồi') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <form class="form-inline">
                            @if ($type)
                            <input type="hidden" name="type" value="{{ old('type', $type)}}">
                            @endif
                            <div class="form-group">
                                <input type="text" name="q" class="form-control form-control-sm" placeholder="Nhập từ khóa tìm kiếm" value="{{ old('q', $q) }}">
                            </div>
                            <div class="form-group ml-1">
                                <select name="kind" class="form-control form-control-sm">
                                    <option value="" @selected(!$kind)>Tất cả phản hồi</option>
                                    <option value="review" @selected($kind == 'review')>Đánh giá</option>
                                    <option value="qa" @selected($kind == 'qa')>Hỏi và đáp</option>
                                </select>
                            </div>
                            <div class="form-group ml-1">
                                <select name="approved" class="form-control form-control-sm">
                                    @foreach ($statusList as $status)
                                    <option value="{{ $status->value }}" @selected($status->value == $currentStatus)>
                                        {{ $status->label() }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group ml-1">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped2">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>{{ __('Khách hàng') }}</th>
                                <th>{{ __('Nội dung') }}</th>
                                <th>{{ __('Tác vụ') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($comments as $comment)
                            <tr id="row-{{ $comment->id }}">
                                <td>{{ $comment->id }}</td>
                                <td>
                                    <b class="d-block">{{ $comment->author }}</b>
                                    <p class="mb-0">Số điện thoại: {{ $comment->author_email }}</p>
                                    <p class="mb-0">{{ $comment->created_at }}</p>
                                    <p class="mb-0">Phê duyệt: {!! $comment->getStatusName() !!}</p>
                                    @if ($comment->rating)
                                    <p class="mb-0">Đánh giá: <span class="badge badge-success">{{ $comment->rating }}</span> ⭐</p>
                                    @elseif ($comment->type === App\Enums\CommentType::PRODUCT)
                                    <p class="mb-0"><span class="badge badge-info">Hỏi và đáp</span></p>
                                    @endif
                                </td>
                                <td>
                                    @if ($comment->productInfo)
                                    <p>Sản phẩm:
                                        <a href="{{ route('frontend.slug', ['slug' => $comment->productInfo->slug]) }}" target="_blank">
                                            {{ $comment->productInfo->name }}
                                        </a>
                                    </p>
                                    @endif

                                    @if ($comment->postInfo)
                                    <p>
                                        <a href="{{ route('frontend.slug', ['slug' => $comment->postInfo->slug]) }}" target="_blank">
                                            {{ $comment->postInfo->title }}
                                        </a>
                                    </p>
                                    @endif

                                    <p class="comment-content-{{ $comment->id }}">{{ $comment->content }}</p>
                                    <!-- <p>
                                        <span class="text-red text-bold">Phản hồi: {{ count($comment->childs) }} <i class="far fa-comment-dots"></i></span>
                                        - <a href="javascript:loadFormReply('{{ $comment->id }}', '{{ $userLogin->name }}', '{{ $userLogin->email }}')" reply-id="{{ $comment->id }}">Thêm phản hồi</a>
                                    </p>
                                    <div class="form-edit-{{ $comment->id }}" data-route="{{ route('backend.comment.update', ['id' => $comment->id]) }}"></div>
                                    <div class="form-reply-{{ $comment->id }}" data-route="{{ route('backend.comment.reply', ['id' => $comment->id]) }}"></div> -->
                                </td>
                                <td>
                                    <div class="table-row-action">
                                        @if ($comment->status->value == App\Enums\CommentStatus::YES->value)
                                        <a class="bg-warning pl-1 pr-1" href="javascript:approved({{ $comment->id }}, 'no');" target="_self">
                                            Không duyệt
                                        </a>
                                        @else
                                        <a class="bg-warning pl-1 pr-1" href="javascript:approved({{ $comment->id }}, 'yes');" target="_self">
                                            Duyệt hiển thị
                                        </a>
                                        @endif
                                        <br>
                                        <!-- <a class="bg-primary pl-1 pr-1" href="javascript:loadFormEdit({{ $comment->id }})" target="_self">
                                            Sửa
                                        </a> -->
                                        <br>
                                        <a class="bg-danger pl-1 pr-1 table-btn-delete" onclick="return confirm('Bạn muốn xóa comment này?')" href="{{ route('backend.comment.delete', ['id' => $comment->id]) }}" target="_self">Xóa</a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $comments->onEachSide(5)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
    @push('scripts')
    <script>
        var token = document.querySelector('meta[name="csrf-token"]').content;

        function loadFormReply(id, name, email) {
            let template = `<form class="comment-reply-form">
            <div class="form-group">
                <label>Nội dung</label>
                <textarea name="content" class="form-control form-control-sm"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tên</label>
                        <input type="text" name="author" class="form-control form-control-sm" value="${name}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" name="author_email" class="form-control form-control-sm" value="${email}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-sm btn-primary btn-update">Cập nhật</button>
                <button type="button" class="btn btn-sm btn-default btn-cancel">Thôi</button>
            </div>
            </form>`;
            $('.form-reply-' + id).html(template);
            this.update(id, 'reply');
        }

        function loadFormEdit(id) {
            let content = $('.comment-content-' + id).html();
            let template = `<form class="comment-edit-form">
            <div class="form-group">
            <label>Nội dung</label>
            <textarea name="content" class="form-control form-control-sm">${content}</textarea>
            </div>
            <div class="form-group">
            <button type="button" class="btn btn-sm btn-primary btn-update">Cập nhật</button>
            <button type="button" class="btn btn-sm btn-default btn-cancel">Thôi</button>
            </div>
            </form>`;
            $('.form-edit-' + id).html(template);
            this.update(id, 'edit');
        }

        function update(id, type) {
            let vm = this,
                form = $(`.comment-${type}-form:not(.process)`),
                wrapper = $(`.form-${type}-${id}`);
            if (!form.length) {
                return;
            }
            form.addClass('process');
            form.on('click', '.btn-cancel', function() {
                wrapper.html('');
            });
            form.on('click', '.btn-update', function() {
                let btn = $(this),
                    route = wrapper.data('route'),
                    formData = form.serializeArray(),
                    content = form.find('[name="content"]').val();

                if (content === '') {
                    toastr.error('Nội dung không được để trống.');
                    return false;
                }

                btn.prop('disabled', true);
                formData.push({
                    'name': '_token',
                    'value': vm.token
                });
                $.post(route, formData, function(response) {
                    if ('message' in response) {
                        if (response.error) {
                            toastr.error(response.message);
                        } else {
                            toastr.success(response.message);
                        }
                    }
                    if ('content' in response) {
                        $('.comment-content-' + id).html(response.content);
                    }
                    if ('row' in response) {
                        let table = $('table.parent-' + response.parent);
                        table.show();
                        table.find('tbody').append(response.row);
                    }

                    btn.prop('disabled', false);
                    wrapper.html('');
                }).always(function() {
                    btn.prop('disabled', false);
                });
            });
        }

        function approved(id, status) {
            let vm = this;
            let route = `/backend/ajax/comments/${id}/approved/${status}`;
            $.post(route, {
                '_token': vm.token
            }, function(response) {
                if ('row' in response) {
                   $('#row-' + id).replaceWith(response.row);
                }
                if ('message' in response) {
                    if (response.error) {
                        toastr.error(response.message);
                    } else {
                        toastr.success(response.message);
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>