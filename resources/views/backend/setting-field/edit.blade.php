<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Tạo setting') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.setting-field.index') }}">{{ __('Danh sách setting') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Tạo mới') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.setting-field.update', ['id' => $settingField->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin setting') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="field_name">{{ __('Field name') }} <code>*</code></label>
                                    <input type="text" name="field_name" value="{{ old('field_name', $settingField->field_name) }}" class="form-control @error('field_name') is-invalid @enderror" id="field_name">
                                    @error('field_name')
                                    <span id="field_name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="field_key">{{ __('Field key') }} <code>*</code></label>
                                    <input type="text" name="field_key" value="{{ old('field_key', $settingField->field_key) }}" class="form-control @error('field_key') is-invalid @enderror" id="field_key" placeholder="field_key" disabled>
                                    <small>Nhập không dấu, không khoảng cách. vd: site_name</small>
                                    @error('field_key')
                                    <span id="field_key-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="field_type">{{ __('Field type') }} <code>*</code></label>
                                    <select class="form-control select2" name="field_type">
                                        @foreach ($fieldTypes as $fieldType)
                                        <option value="{{ $fieldType->value }}" @selected($fieldType->value == $settingField->field_type->value)>
                                            {{ $fieldType->label() }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('field_type')
                                    <span id="field_type-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                @if ($settingField->field_type->value === App\Enums\FieldType::FILE->value)
                                <div class="form-group">
                                    <label for="field_value">{{ __('Field name') }}</label><br>
                                    <input type="file" name="field_value" value="{{ old('field_value', $settingField->field_value) }}"><br>
                                    <small>{{ $settingField->field_value }}</small>
                                </div>
                                @elseif ($settingField->field_type->value === App\Enums\FieldType::TEXTAREA->value)
                                <div class="form-group">
                                    <label for="field_value">{{ __('Field name') }}</label>
                                    <textarea name="field_value" class="form-control" rows="5">{{ old('field_value', $settingField->field_value) }}</textarea>
                                </div>
                                @elseif ($settingField->field_type->value === App\Enums\FieldType::EDITOR->value)
                                <div class="form-group">
                                    <label for="field_value">{{ __('Field name') }}</label>
                                    <textarea id="field_value" name="field_value" class="form-control">{{ old('field_value', $settingField->field_value) }}</textarea>
                                </div>
                                @elseif ($settingField->field_type->value === App\Enums\FieldType::IMAGE->value)
                                <div class="form-group">
                                    <label for="field_value">{{ __('Field name') }}</label><br>
                                    <input type="file" name="field_value" value="{{ old('field_value', $settingField->field_value) }}"><br><br>
                                    <img src="{{asset('storage/' . $settingField->field_value )}}" title="{{ $settingField->field_name }}" class="img-size-64">
                                </div>
                                @else
                                <div class="form-group">
                                    <label for="field_value">{{ __('Field value') }}</label>
                                    <input type="text" name="field_value" value="{{ old('field_value', $settingField->field_value) }}" class="form-control">
                                </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.setting-field.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.setting-field.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
    @push('scripts')
    <script>
        $(function() {
            $('.select2').select2();

            tinymceInit.selector = '#field_value';
            tinymceInit.file_picker_callback = function(cb, value, meta) {
                var url = "{{ route('backend.photo.store') }}";
                uploadImg(url, cb, value, meta);
            };
            tinymce.init(tinymceInit);
        });
    </script>
    @endpush
</x-app-layout>