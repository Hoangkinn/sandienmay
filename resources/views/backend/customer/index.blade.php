<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách khách hàng') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách khách hàng') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Danh sách khách hàng') }}</h3>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 20%">{{ __('Khách hàng') }}</th>
                                <th style="width: 60%">{{ __('Thông tin') }}</th>
                                <th style="width: 20%">{{ __('Thời gian') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $idx => $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>
                                    <small>Email: {{ $customer->email }}</small><br>
                                    <small>Phone: {{ $customer->phone_number }}</small><br>
                                    <small>City: {{ $customer->city }}</small><br>
                                    <small>Country: {{ $customer->country }}</small><br>
                                    <small>Address: {{ $customer->address }}</small><br>
                                </td>
                                <td>{{ ($customer->created_at)->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>