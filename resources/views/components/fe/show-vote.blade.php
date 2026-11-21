@if(!empty($avg) && $avg > 0)
    @if(is_int($avg))
        <div class="d-inline-block position-relative text-FBBF24">
            @for($i=1; $i<=$avg; $i++)
                <i class="icon-star-full"></i>
            @endfor
            @for($i=$avg; $i<5; $i++)
                <i class="icon-star-full text-FBBF24"></i>
            @endfor
        </div>
    @else
        <div class="d-inline-block position-relative text-FBBF24">
            @for($i=1; $i<=5; $i++)
                <i class="icon-star-full"></i>
            @endfor
            <div class="position-absolute h-100 top-0 end-0 text-FBBF24 text-nowrap overflow-hidden" style="direction: rtl; width: {{ 100 - $avg*20 }}%">
                @for($i=1; $i<=(5 - (int) $avg); $i++)
                    <i class="icon-star-full"></i>
                @endfor
            </div>
        </div>
    @endif
@endif