@extends('layouts.app')

@section('title', '削除確認')

@section('content')
    <div class="card">
        <h1>削除確認</h1>
        <p>以下のゴルフ場を削除しようとしています。論理削除のため、削除済み一覧から復元できます。</p>
        <table>
            <tr><th style="width: 140px;">ID</th><td>{{ $course->id }}</td></tr>
            <tr><th>コース名</th><td>{{ $course->course_name }}</td></tr>
            <tr><th>都道府県・州</th><td>{{ $course->state_prefecture }}</td></tr>
            <tr><th>住所</th><td>{{ $course->address }}</td></tr>
        </table>
        <form action="{{ route('golf-courses.destroy', $course) }}" method="POST" style="margin-top: 16px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">削除する</button>
            <a href="{{ route('golf-courses.index') }}" class="btn btn-secondary">キャンセル</a>
        </form>
    </div>
@endsection
