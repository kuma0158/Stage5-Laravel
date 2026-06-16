@extends('layouts.app')

@section('title', $course->course_name)

@section('content')
    <div class="card">
        <h1>{{ $course->course_name }} <span class="text-muted">#{{ $course->id }}</span></h1>
        <p>
            <a href="{{ route('golf-courses.edit', $course) }}" class="btn btn-primary btn-sm">編集</a>
            <a href="{{ route('golf-courses.confirm-delete', $course) }}" class="btn btn-danger btn-sm">削除</a>
            <a href="{{ route('golf-courses.index') }}" class="btn btn-secondary btn-sm">一覧へ戻る</a>
        </p>

        <table>
            <tr><th style="width:180px;">言語 / 国コード</th><td>{{ $course->locale }} / {{ $course->country_code }}</td></tr>
            <tr><th>都道府県・州</th><td>{{ $course->state_prefecture }}</td></tr>
            <tr><th>住所</th><td>{{ $course->address }}</td></tr>
            <tr><th>電話</th><td>{{ $course->phone }}</td></tr>
            <tr><th>問い合わせメール</th><td>{{ $course->form_email }}</td></tr>
            <tr><th>公式サイト</th><td>
                @if ($course->web)
                    <a href="{{ $course->web }}" target="_blank" rel="noopener noreferrer">{{ $course->web }}</a>
                @endif
            </td></tr>
            <tr><th>予約先</th><td>{{ $course->reservation }}</td></tr>
            <tr><th>予約手段</th><td>{{ $course->reservation_method }}</td></tr>
            <tr><th>分類コード</th><td>{{ $course->kinds }}</td></tr>
            <tr><th>種別</th><td>
                @foreach ($course->kind_labels as $label)
                    <span class="badge">{{ $label }}</span>
                @endforeach
            </td></tr>
            <tr><th>緯度 / 経度</th><td>
                @if ($course->lat !== null && $course->lng !== null)
                    {{ $course->lat }} / {{ $course->lng }}
                @endif
            </td></tr>
            <tr><th>備考</th><td style="white-space: pre-wrap;">{{ $course->remarks }}</td></tr>
            <tr><th>作成日時</th><td>{{ optional($course->created_at)->format('Y-m-d H:i') }}</td></tr>
            <tr><th>更新日時</th><td>{{ optional($course->updated_at)->format('Y-m-d H:i') }}</td></tr>
        </table>

        <h2>画像</h2>
        <div style="display:flex; gap: 16px; flex-wrap: wrap;">
            @foreach (['image1', 'image2', 'image3'] as $field)
                @if ($course->{$field})
                    <img src="{{ $course->{$field.'_url'} }}" alt="" class="thumb thumb-lg">
                @endif
            @endforeach
            @if (! $course->image1 && ! $course->image2 && ! $course->image3)
                <p class="text-muted">画像は登録されていません。</p>
            @endif
        </div>
    </div>
@endsection
