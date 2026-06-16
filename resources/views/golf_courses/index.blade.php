@extends('layouts.app')

@section('title', 'ゴルフ場一覧')

@section('content')
    <div class="card">
        <h1>ゴルフ場一覧</h1>
        <form method="GET" action="{{ route('golf-courses.index') }}">
            <div class="form-row">
                <div class="form-group">
                    <label for="q">キーワード（コース名・住所）</label>
                    <input type="text" name="q" id="q" value="{{ $filters['q'] ?? '' }}" maxlength="100">
                </div>
                <div class="form-group" style="max-width: 220px;">
                    <label for="prefecture">都道府県・州（完全一致）</label>
                    <input type="text" name="prefecture" id="prefecture"
                           value="{{ $filters['prefecture'] ?? '' }}">
                </div>
                <div class="form-group" style="max-width: 140px;">
                    <label for="locale">言語</label>
                    <select name="locale" id="locale">
                        <option value="">--</option>
                        @foreach (['ja' => '日本語', 'en' => '英語'] as $code => $label)
                            <option value="{{ $code }}"
                                {{ ($filters['locale'] ?? '') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="max-width: 160px;">
                    <label for="kind">種別</label>
                    <select name="kind" id="kind">
                        <option value="">--</option>
                        @foreach ([
                            'indoor' => '室内', 'outdoor' => '屋外',
                            'short'  => 'ショート', 'long' => 'ロング',
                        ] as $val => $label)
                            <option value="{{ $val }}"
                                {{ ($filters['kind'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">検索</button>
            <a href="{{ route('golf-courses.index') }}" class="btn btn-secondary">クリア</a>
        </form>
    </div>

    <div class="card">
        @if ($courses->isEmpty())
            <p>該当するゴルフ場が見つかりませんでした。</p>
        @else
            <p class="text-muted">全 {{ $courses->total() }} 件中
                {{ $courses->firstItem() }}〜{{ $courses->lastItem() }} 件を表示</p>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>コース名</th>
                        <th style="width: 120px;">都道府県・州</th>
                        <th style="width: 60px;">言語</th>
                        <th style="width: 180px;">種別</th>
                        <th style="width: 140px;">電話</th>
                        <th style="width: 220px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr>
                            <td>{{ $course->id }}</td>
                            <td>{{ $course->course_name }}</td>
                            <td>{{ $course->state_prefecture }}</td>
                            <td>{{ $course->locale }}</td>
                            <td>
                                @foreach ($course->kind_labels as $label)
                                    <span class="badge">{{ $label }}</span>
                                @endforeach
                            </td>
                            <td>{{ $course->phone }}</td>
                            <td class="actions">
                                <a href="{{ route('golf-courses.show', $course) }}" class="btn btn-sm btn-secondary">詳細</a>
                                <a href="{{ route('golf-courses.edit', $course) }}" class="btn btn-sm btn-secondary">編集</a>
                                <a href="{{ route('golf-courses.confirm-delete', $course) }}" class="btn btn-sm btn-danger">削除</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $courses->links() }}
        @endif
    </div>
@endsection
