<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Thêm mới thương hiệu') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('backend.brand.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label>{{ __('Tên thương hiệu') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Logo') }}</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Danh mục áp dụng') }}</label>
                            <div class="border p-2" style="max-height: 200px; overflow-y: auto;">
                                @foreach($categories as $cate)
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="categories[]" 
                                            id="cate_{{ $cate->id }}" value="{{ $cate->id }}"
                                            {{ (isset($brand) && $brand->categories->contains($cate->id)) ? 'checked' : '' }}>
                                        <label for="cate_{{ $cate->id }}" class="custom-control-label">{{ $cate->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Trạng thái') }}</label>
                            <select name="is_active" class="form-control">
                                <option value="1">{{ __('Hiển thị') }}</option>
                                <option value="0">{{ __('Ẩn') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">{{ __('Lưu lại') }}</button>
                        <a href="{{ route('backend.brand.index') }}" class="btn btn-default">{{ __('Quay lại') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>