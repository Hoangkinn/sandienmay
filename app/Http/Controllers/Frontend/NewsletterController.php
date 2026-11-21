<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Requests\NewsletterRequest;
use App\Models\EmailSubscriber;

class NewsletterController extends BaseController
{
    public function store(NewsletterRequest $request)
    {
        EmailSubscriber::create($request->validated());

        return response()->json([
            'status'  => 200,
            'message' => 'Cảm ơn bạn đã đăng ký nhận tin khuyến mãi!',
        ]);
    }
}
