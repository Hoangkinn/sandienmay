<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmailSubscriber;
use Illuminate\Http\Request;

class EmailSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $subscribers = EmailSubscriber::when($request->filled('search'), function ($query) use ($request) {
                $query->where('email', 'like', '%' . $request->search . '%');
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('backend.email-subscriber.index', compact('subscribers'));
    }

    public function destroy($id)
    {
        EmailSubscriber::findOrFail($id)->delete();

        return redirect()->route('backend.email-subscriber.index')->with('success', 'Đã xóa email!');
    }
}
