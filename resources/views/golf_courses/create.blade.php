@extends('layouts.app')

@section('title', '新規登録')

@section('content')
    <div class="card">
        <h1>ゴルフ場 新規登録</h1>
        <form action="{{ route('golf-courses.store') }}" method="POST" enctype="multipart/form-data">
            @include('golf_courses._form', ['course' => $course])
            <div style="margin-top: 16px;">
                <button type="submit" class="btn btn-primary">登録する</button>
                <a href="{{ route('golf-courses.index') }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
