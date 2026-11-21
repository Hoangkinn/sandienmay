<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Thông tin liên hệ') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Thông tin liên hệ') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped2">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>{{ __('Khách hàng') }}</th>
                                <th>{{ __('Số điện thoại') }}</th>
                                <th>{{ __('Nội dung') }}</th>
                                <th>Thời gian gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contacts as $contact)
                            <tr id="row-{{ $contact->id }}">
                                <td>{{ $contact->id }}</td>
                                <td>{{ $contact->name }}</td>
                                <td>
                                    <p class="mb-0">{{ $contact->phone }}</p>
                                </td>
                                <td>
                                    <p style="white-space: pre-line">{{ $contact->content }}
                                    </p>
                                </td>
                                <td>
                                    <p>{{ $contact->created_at }}
                                    </p>
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
                    {{ $contacts->onEachSide(5)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>