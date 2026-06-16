<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class GolfCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'locale',
        'country_code',
        'state_prefecture',
        'course_name',
        'kinds',
        'web',
        'phone',
        'address',
        'indoor',
        'outdoor',
        'short_course',
        'long_course',
        'lat',
        'lng',
        'form_email',
        'reservation',
        'reservation_method',
        'remarks',
        'image1',
        'image2',
        'image3',
    ];

    protected function casts(): array
    {
        return [
            'indoor'       => 'boolean',
            'outdoor'      => 'boolean',
            'short_course' => 'boolean',
            'long_course'  => 'boolean',
            'lat'          => 'float',
            'lng'          => 'float',
            'kinds'        => 'integer',
        ];
    }

    /**
     * 検索スコープ
     *  - q          : course_name / address の部分一致（LIKE メタ文字エスケープ済み）
     *  - prefecture : 完全一致
     *  - locale     : 完全一致
     *  - kind       : indoor / outdoor / short / long のいずれかのフラグが true
     */
    public function scopeSearch(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $q, string $keyword) {
                $escaped = self::escapeLike($keyword);
                $q->where(function (Builder $sub) use ($escaped) {
                    $sub->where('course_name', 'like', "%{$escaped}%")
                        ->orWhere('address', 'like', "%{$escaped}%");
                });
            })
            ->when($filters['prefecture'] ?? null, fn (Builder $q, string $pref) =>
                $q->where('state_prefecture', $pref))
            ->when($filters['locale'] ?? null, fn (Builder $q, string $loc) =>
                $q->where('locale', $loc))
            ->when($filters['kind'] ?? null, function (Builder $q, string $kind) {
                $column = match ($kind) {
                    'indoor'  => 'indoor',
                    'outdoor' => 'outdoor',
                    'short'   => 'short_course',
                    'long'    => 'long_course',
                    default   => null,
                };
                if ($column !== null) {
                    $q->where($column, true);
                }
            });
    }

    /**
     * LIKE メタ文字（% _ \）をエスケープ
     */
    public static function escapeLike(string $value, string $char = '\\'): string
    {
        return str_replace(
            [$char, '%', '_'],
            [$char.$char, $char.'%', $char.'_'],
            $value
        );
    }

    /**
     * 画像 URL アクセサ（Blade から $course->image1_url のように呼べる）
     */
    public function getImage1UrlAttribute(): ?string
    {
        return $this->image1 ? Storage::url($this->image1) : null;
    }

    public function getImage2UrlAttribute(): ?string
    {
        return $this->image2 ? Storage::url($this->image2) : null;
    }

    public function getImage3UrlAttribute(): ?string
    {
        return $this->image3 ? Storage::url($this->image3) : null;
    }

    /**
     * 種別ラベル
     */
    public function getKindLabelsAttribute(): array
    {
        $labels = [];
        if ($this->indoor)       $labels[] = '室内';
        if ($this->outdoor)      $labels[] = '屋外';
        if ($this->short_course) $labels[] = 'ショート';
        if ($this->long_course)  $labels[] = 'ロング';
        return $labels;
    }
}
