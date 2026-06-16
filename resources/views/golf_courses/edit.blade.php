@extends('layouts.app')

@section('title', '編集 #' . $course->id)

@section('content')
    <div class="card">
        <h1>ゴルフ場 編集 <span class="text-muted">#{{ $course->id }}</span></h1>
        <form action="{{ route('golf-courses.update', $course) }}" method="POST" enctype="multipart/form-data">
            @include('golf_courses._form', ['course' => $course, 'method' => 'PUT'])
            <div style="margin-top: 16px;">
                <button type="submit" class="btn btn-primary">更新する</button>
                <a href="{{ route('golf-courses.index') }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
