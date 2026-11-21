<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách hóa đơn') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách hóa đơn') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th>{{ __('Mã đơn hàng') }}</th>
                                <th>{{ __('Người mua') }}</th>
                                <th>{{ __('Ngày tạo') }}</th>
                                <th>{{ __('Ngày cập nhật') }}</th>
                                <th>{{ __('Note') }}</th>
                                <th>{{ __('Trạng thái') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->created_at }}</td>
                                <td>{{ $order->updated_at }}</td>
                                <td>{{ $order->customer_note }}</td>
                                <td>{!! $order->getStatusName() !!}</td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.order.show', ['id' => $order->id]) }}">
                                        <i class="fas fa-eye"></i>
                                        {{ __('Xem') }}
                                    </a>
                                    @if ($order->isDeleted())
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa hóa đơn này?')" href="{{ route('backend.order.delete', ['id' => $order->id]) }}">
                                        <i class="fas fa-trash"></i>
                                        {{ __('Xóa') }}
                                    </a>
                                    @endif
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
                    {{ $orders->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>