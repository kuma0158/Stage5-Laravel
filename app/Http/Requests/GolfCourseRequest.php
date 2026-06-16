<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GolfCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 認可は auth ミドルウェアで担保
        return true;
    }

    /**
     * チェックボックスの未送信を false として補完
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'indoor'       => $this->boolean('indoor'),
            'outdoor'      => $this->boolean('outdoor'),
            'short_course' => $this->boolean('short_course'),
            'long_course'  => $this->boolean('long_course'),
        ]);
    }

    public function rules(): array
    {
        return [
            'locale'             => ['required', 'string', 'size:2', 'in:ja,en'],
            'country_code'       => ['required', 'string', 'size:2'],
            'state_prefecture'   => ['nullable', 'string', 'max:255'],
            'course_name'        => ['required', 'string', 'max:255'],
            'kinds'              => ['nullable', 'integer'],
            'web'                => ['nullable', 'url', 'max:2048'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'address'            => ['nullable', 'string', 'max:255'],
            'indoor'             => ['boolean'],
            'outdoor'            => ['boolean'],
            'short_course'       => ['boolean'],
            'long_course'        => ['boolean'],
            'lat'                => ['nullable', 'numeric', 'between:-90,90'],
            'lng'                => ['nullable', 'numeric', 'between:-180,180'],
            'form_email'         => ['nullable', 'email', 'max:255'],
            'reservation'        => ['nullable', 'string', 'max:255'],
            'reservation_method' => ['nullable', 'string', 'max:255'],
            'remarks'            => ['nullable', 'string', 'max:5000'],
            'image1'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image2'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image3'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            // 「画像を削除する」チェックボックス（true 時に該当画像を消す）
            'remove_image1'      => ['nullable', 'boolean'],
            'remove_image2'      => ['nullable', 'boolean'],
            'remove_image3'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'locale.in'           => '言語コードを正しく指定してください。',
            'country_code.size'   => '国コードを正しく指定してください。',
            'course_name.required'=> 'コース名を入力してください。',
            'kinds.integer'       => '分類コードは整数で入力してください。',
            'web.url'             => '公式サイトURLの形式が正しくありません。',
            'lat.between'         => '緯度は -90〜90 の範囲で入力してください。',
            'lng.between'         => '経度は -180〜180 の範囲で入力してください。',
            'form_email.email'    => 'メールアドレスの形式が正しくありません。',
            'remarks.max'         => '備考は5000文字以内で入力してください。',
            'image1.image'        => '画像はJPG/PNG/WebPで5MB以内にしてください。',
            'image2.image'        => '画像はJPG/PNG/WebPで5MB以内にしてください。',
            'image3.image'        => '画像はJPG/PNG/WebPで5MB以内にしてください。',
            'image1.max'          => '画像はJPG/PNG/WebPで5MB以内にしてください。',
            'image2.max'          => '画像はJPG/PNG/WebPで5MB以内にしてください。',
            'image3.max'          => '画像はJPG/PNG/WebPで5MB以内にしてください。',
            'image1.mimes'        => '画像はJPG/PNG/WebPで5MB以内にしてください。',
            'image2.mimes'        => '画像はJPG/PNG/WebPで5MB以内にしてください。',
            'image3.mimes'        => '画像はJPG/PNG/WebPで5MB以内にしてください。',
        ];
    }

    /**
     * lat / lng は「両方入力 or 両方空」
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $lat = $this->input('lat');
            $lng = $this->input('lng');

            $latFilled = $lat !== null && $lat !== '';
            $lngFilled = $lng !== null && $lng !== '';

            if ($latFilled !== $lngFilled) {
                $v->errors()->add('lat', '緯度と経度は両方入力するか両方空にしてください。');
            }
        });
    }
}
