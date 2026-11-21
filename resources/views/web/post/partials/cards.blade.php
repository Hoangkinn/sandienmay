@foreach ($posts as $post)
    <x-fe.post-card :post="$post" />
@endforeach
