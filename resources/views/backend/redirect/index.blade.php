<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Url Redirect') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Url Redirect') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <a href="{{ route('backend.redirect.create') }}" class="btn btn-success float-left">{{ __('Tạo mới') }}</a>
                    <div class="card-tools">
                        {{ $redirects->onEachSide(5)->links('backend.paginator') }}
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Từ') }}</th>
                                <th>{{ __('Đến') }}</th>
                                <th>{{ __('Code') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($redirects as $redirect)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $redirect->redirect_from }}</td>
                                <td>{{ $redirect->redirect_to }}</td>
                                <td>{{ $redirect->redirect_code }}</td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-primary btn-sm" target="_blank" href="{{ $redirect->redirect_from }}">
                                        <i class="fas fa-eye"></i>
                                        {{ __('Test') }}
                                    </a>
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.redirect.edit', ['id' => $redirect->id]) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn chuyển hướng url này?')" href="{{ route('backend.redirect.delete', ['id' => $redirect->id]) }}">
                                        <i class="fas fa-trash"></i>
                                        {{ __('Xóa') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $redirects->onEachSide(5)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>