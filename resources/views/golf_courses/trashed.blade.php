@extends('layouts.app')

@section('title', '削除済みゴルフ場')

@section('content')
    <div class="card">
        <h1>削除済みゴルフ場</h1>
        <p class="text-muted">復元または完全削除が可能です。完全削除すると画像ファイルも消去されます。</p>

        @if ($courses->isEmpty())
            <p>削除済みのゴルフ場はありません。</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>コース名</th>
                        <th style="width: 120px;">都道府県・州</th>
                        <th style="width: 140px;">削除日時</th>
                        <th style="width: 260px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr>
                            <td>{{ $course->id }}</td>
                            <td>{{ $course->course_name }}</td>
                            <td>{{ $course->state_prefecture }}</td>
                            <td>{{ optional($course->deleted_at)->format('Y-m-d H:i') }}</td>
                            <td class="actions">
                                <form action="{{ route('golf-courses.restore', $course->id) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">復元</button>
                                </form>
                                <form action="{{ route('golf-courses.force-destroy', $course->id) }}" method="POST" style="margin:0;"
                                      onsubmit="return confirm('本当に完全削除しますか？ 画像ファイルも削除されます。');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">完全削除</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $courses->links() }}
        @endif
    </div>
@endsection
