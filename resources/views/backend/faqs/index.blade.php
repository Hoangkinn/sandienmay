<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách câu hỏi') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách câu hỏi') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('backend.faq.create') }}" class="btn btn-success float-left">{{ __('Tạo mới') }}</a>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 1%">#</th>
                                <th style="width: 20%">{{ __('Câu hỏi') }}</th>
                                <th style="width: 20%" class="text-center">
                                    {{ __('Trả lời') }}
                                </th>
                                <th style="width: 20%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($faqs as $idx => $faq)
                            <tr>
                                <td>{{ ($idx + 1) }}</td>
                                <td><a>{{ $faq->question }}</a></td>
                                <td><a>{!! $faq->answer !!}</a></td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.faq.edit', ['id' => $faq->id]) }}">
                                        <i class="fas fa-pencil-alt">
                                        </i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa faq này?')" href="{{ route('backend.faq.delete', ['id' => $faq->id]) }}">
                                        <i class="fas fa-trash">
                                        </i>
                                        {{ __('Xóa') }}
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
            </div>
        </div>
    </section>
</x-app-layout>