
<x-app-layout>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Dashboard') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard v1</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-7">
                    <div class="card card-primary card-outline">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Đơn hàng mới nhất</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Khách hàng</th>
                                            <th>Thời gian đặt hàng</th>
                                            <th>Trạng thái</th>
                                            <th>Giá trị đơn hàng</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($orders as $order)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $order->customer_name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y H:i:s') }}</td>
                                            <td>
                                                {!! $order->getStatusName() !!}
                                            </td>
                                            <td>{{ number_format($order->total) }}₫</td>
                                            <td class="project-actions text-right">
                                                <a target="_blank" class="btn btn-primary btn-sm" href="{{ route('backend.order.show', ['id' => $order->id]) }}">
                                                    {{ __('Xem chi tiết') }}
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5">Chưa có đơn hàng</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{ route('backend.order.index') }}" class="uppercase">Xem tất cả</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Sản phẩm mới</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">
                                @forelse ($products as $product)
                                <li class="item">
                                    @forelse ($product->imagePrimary as $image)
                                        <div class="product-img">
                                            <img src="{{ $image->url }}" title="{{ $product->name }}" class="img-size-50" />
                                        </div>
                                    @empty
                                        <div class="product-img">
                                            <img src="{{asset('storage/' . $product->image) }}" title="{{ $product->name }}" class="img-size-50" />
                                        </div>
                                    @endforelse
                                    <div class="product-info">
                                        <a href="{{ route('backend.product.edit', ['id' => $product->id]) }}" class="product-title">
                                            {{ $product->name }}
                                        </a>
                                        <span class="product-description">Giá: {{ number_format($product->regular_price) }}₫</span>
                                    </div>
                                </li>
                                @empty
                                <li class="item">
                                </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>