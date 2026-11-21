<?php

namespace App\Enums;

enum OrderStatus: String
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELED = 'cancelled';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            static::PENDING => 'Chờ thanh toán',
            static::PROCESSING => 'Đang xử lý',
            static::ON_HOLD => 'Tạm giữ',
            static::COMPLETED => 'Đã hoàn thành',
            static::CANCELED => 'Đã hủy',
            static::REFUNDED => 'Đã hoàn lại tiền',
            static::FAILED => 'Thất bại',
        };
    }

}
