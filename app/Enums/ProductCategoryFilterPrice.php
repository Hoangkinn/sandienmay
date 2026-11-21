<?php

namespace App\Enums;

enum ProductCategoryFilterPrice: String
{
    case DUOI_1_TRIEU = 'duoi-1-trieu';
    case TU_1_DEN_3_TRIEU = 'tu-1-trieu-den-3-trieu';
    case TU_3_DEN_5_TRIEU = 'tu-3-trieu-den-5-trieu';
    case TU_5_DEN_10_TRIEU = 'tu-5-trieu-den-10-trieu';
    case TU_10_DEN_15_TRIEU = 'tu-10-trieu-den-15-trieu';
    case TU_15_DEN_20_TRIEU = 'tu-15-trieu-den-20-trieu';
    case TREN_20_TRIEU = 'tren-20-trieu';

    public function getTitle(): string
    {
        return match ($this) {
            self::DUOI_1_TRIEU => 'Dưới 1 triệu',
            self::TU_1_DEN_3_TRIEU => 'Từ 1 - 3 triệu',
            self::TU_3_DEN_5_TRIEU => 'Từ 3 - 5 triệu',
            self::TU_5_DEN_10_TRIEU => 'Từ 5 - 10 triệu',
            self::TU_10_DEN_15_TRIEU => 'Từ 10 - 15 triệu',
            self::TU_15_DEN_20_TRIEU => 'Từ 15 - 20 triệu',
            self::TREN_20_TRIEU => 'Trên 20 triệu',
        };
    }
}
