<?php

namespace App\Models;

use App\Enums\VisitStatus;
use App\States\VisitStatus\VisitStatusContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'area_id',
        'start_date',
        'end_date',
        'memo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => VisitStatus::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pins()
    {
        return $this->hasMany(Pin::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function statusContext(): VisitStatusContext
    {
        return VisitStatusContext::from($this->status);
    }
}
