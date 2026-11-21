<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderByDesc('created_at')->paginate(10);
        return view('backend.customer.index', [
            'customers' => $customers
        ]);
    }

    public function listContact()
    {
        $contacts = Contact::orderByDesc('created_at')->paginate(10);
        return view('backend.customer.contact', [
            'contacts' => $contacts
        ]);
    }
}
