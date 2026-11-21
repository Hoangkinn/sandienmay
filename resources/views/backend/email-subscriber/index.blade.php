<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Email đăng ký nhận tin') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Email đăng ký nhận tin') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <form class="input-group input-group-sm" style="width: 250px;" method="GET" action="{{ route('backend.email-subscriber.index') }}">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control float-right" placeholder="Tìm email...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped2">
                        <thead>
                            <tr>
                                <th style="width: 8%">Id</th>
                                <th style="width: 47%">{{ __('Email') }}</th>
                                <th style="width: 25%">{{ __('Thời gian đăng ký') }}</th>
                                <th style="width: 20%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subscribers as $subscriber)
                            <tr id="row-{{ $subscriber->id }}">
                                <td>{{ $subscriber->id }}</td>
                                <td>{{ $subscriber->email }}</td>
                                <td>{{ $subscriber->created_at }}</td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa email này?')" href="{{ route('backend.email-subscriber.delete', ['id' => $subscriber->id]) }}">
                                        <i class="fas fa-trash"></i> {{ __('Xóa') }}
                                    </a>
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
                    {{ $subscribers->onEachSide(5)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
