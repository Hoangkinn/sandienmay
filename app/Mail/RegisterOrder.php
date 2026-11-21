<?php

namespace App\Mail;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterOrder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.register-order.index')
            ->subject($this->order->customer_phone . ' - đặt đơn hàng thành công')
            ->bcc(['info@kumisai.vn'])
            ->with([
                'order' => $this->order
            ]);
    }
}
