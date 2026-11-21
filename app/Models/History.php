<?php   

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $table = 'histories';

    protected $guarded = [];

    // Thiết lập mối quan hệ với bảng users để sau này lấy tên/email người thao tác
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}