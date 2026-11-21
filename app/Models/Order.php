<?php

namespace App\Models;

use App\Enums\OrderStatus;

class Order extends BaseModel
{
    protected $table = 'm_orders';

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_address',
        'customer_phone',
        'customer_note',
        'customer_id',
        'total',
        'status',
        'is_deleted',
        'coupon_code',
        'discount_amount',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function isDeleted()
    {
        return $this->status->value != OrderStatus::COMPLETED->value;
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getStatusName()
    {
        switch ($this->status->value) {
            case OrderStatus::PROCESSING->value:
            case OrderStatus::FAILED->value:
                $className = 'danger';
                break;
            case OrderStatus::ON_HOLD->value:
                $className = 'primary';
                break;
            case OrderStatus::PENDING->value:
                $className = 'warning';
                break;
            case OrderStatus::COMPLETED->value:
                $className = 'success';
                break;
            case OrderStatus::REFUNDED->value:
                $className = 'secondary';
                break;
            case OrderStatus::CANCELED->value:
                $className = 'info';
                break;
            default:
                $className = 'light';
                break;
        }

        return '<span class="badge bg-' . $className . '">' . $this->status->label() . '</span>';
    }
}
