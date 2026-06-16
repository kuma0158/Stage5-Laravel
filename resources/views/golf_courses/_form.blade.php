{{-- 共通フォーム部品。$course は GolfCourse モデル（新規時は空）、$action はフォームの POST 先 URL --}}
@csrf
@isset($method)
    @method($method)
@endisset

<div class="form-row">
    <div class="form-group">
        <label for="course_name">コース名 <span style="color:red">*</span></label>
        <input type="text" name="course_name" id="course_name"
               value="{{ old('course_name', $course->course_name) }}"
               class="@error('course_name') invalid @enderror" required>
    </div>
    <div class="form-group" style="max-width: 140px;">
        <label for="locale">言語 <span style="color:red">*</span></label>
        <select name="locale" id="locale" class="@error('locale') invalid @enderror" required>
            @foreach (['ja' => '日本語(ja)', 'en' => '英語(en)'] as $code => $label)
                <option value="{{ $code }}"
                    {{ old('locale', $course->locale) === $code ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group" style="max-width: 140px;">
        <label for="country_code">国コード <span style="color:red">*</span></label>
        <input type="text" name="country_code" id="country_code" maxlength="2"
               value="{{ old('country_code', $course->country_code) }}"
               class="@error('country_code') invalid @enderror" required>
        <span class="text-muted">ISO 3166-1 alpha-2（例: JP）</span>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="state_prefecture">都道府県・州</label>
        <input type="text" name="state_prefecture" id="state_prefecture"
               value="{{ old('state_prefecture', $course->state_prefecture) }}"
               class="@error('state_prefecture') invalid @enderror">
    </div>
    <div class="form-group">
        <label for="address">住所</label>
        <input type="text" name="address" id="address"
               value="{{ old('address', $course->address) }}"
               class="@error('address') invalid @enderror">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="phone">電話番号</label>
        <input type="text" name="phone" id="phone"
               value="{{ old('phone', $course->phone) }}"
               class="@error('phone') invalid @enderror">
    </div>
    <div class="form-group">
        <label for="form_email">問い合わせメール</label>
        <input type="email" name="form_email" id="form_email"
               value="{{ old('form_email', $course->form_email) }}"
               class="@error('form_email') invalid @enderror">
    </div>
    <div class="form-group" style="max-width: 160px;">
        <label for="kinds">分類コード</label>
        <input type="number" name="kinds" id="kinds"
               value="{{ old('kinds', $course->kinds) }}"
               class="@error('kinds') invalid @enderror">
    </div>
</div>

<div class="form-group">
    <label for="web">公式サイト URL</label>
    <input type="url" name="web" id="web"
           value="{{ old('web', $course->web) }}"
           class="@error('web') invalid @enderror"
           placeholder="https://...">
</div>

<div class="form-row">
    <div class="form-group">
        <label for="reservation">予約先（URL／番号）</label>
        <input type="text" name="reservation" id="reservation"
               value="{{ old('reservation', $course->reservation) }}"
               class="@error('reservation') invalid @enderror">
    </div>
    <div class="form-group">
        <label for="reservation_method">予約手段</label>
        <input type="text" name="reservation_method" id="reservation_method"
               value="{{ old('reservation_method', $course->reservation_method) }}"
               placeholder="電話 / WEB / メール 等"
               class="@error('reservation_method') invalid @enderror">
    </div>
</div>

<h2>種別</h2>
<div class="check-row">
    {{-- hidden で 0 を先に送ることで OFF を確実に伝える --}}
    @foreach ([
        'indoor'       => '室内',
        'outdoor'      => '屋外',
        'short_course' => 'ショートコース',
        'long_course'  => 'ロングコース',
    ] as $name => $label)
        <label>
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" name="{{ $name }}" value="1"
                   {{ old($name, $course->{$name}) ? 'checked' : '' }}>
            {{ $label }}
        </label>
    @endforeach
</div>

<h2>位置情報</h2>
<div class="form-row">
    <div class="form-group" style="max-width: 220px;">
        <label for="lat">緯度（-90 〜 90）</label>
        <input type="text" name="lat" id="lat"
               value="{{ old('lat', $course->lat) }}"
               class="@error('lat') invalid @enderror"
               placeholder="例: 35.681236">
    </div>
    <div class="form-group" style="max-width: 220px;">
        <label for="lng">経度（-180 〜 180）</label>
        <input type="text" name="lng" id="lng"
               value="{{ old('lng', $course->lng) }}"
               class="@error('lng') invalid @enderror"
               placeholder="例: 139.767125">
    </div>
</div>

<h2>備考</h2>
<div class="form-group">
    <textarea name="remarks" id="remarks"
              class="@error('remarks') invalid @enderror">{{ old('remarks', $course->remarks) }}</textarea>
</div>

<h2>画像（最大3枚 / JPG・PNG・WebP / 5MB 以内）</h2>
<div class="form-row">
    @foreach (['image1', 'image2', 'image3'] as $field)
        <div class="form-group">
            <label>{{ strtoupper($field) }}</label>
            @if ($course->{$field})
                <div style="margin-bottom: 6px;">
                    <img src="{{ $course->{$field.'_url'} }}" alt="" class="thumb">
                </div>
                <label style="font-weight: normal;">
                    <input type="checkbox" name="remove_{{ $field }}" value="1"> 既存画像を削除する
                </label>
            @endif
            <input type="file" name="{{ $field }}" accept="image/jpeg,image/png,image/webp"
                   class="@error($field) invalid @enderror">
        </div>
    @endforeach
</div>
