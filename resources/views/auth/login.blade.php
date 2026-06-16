@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
    <div class="login-wrap">
        <div class="card">
            <h1>管理者ログイン</h1>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email') }}" required autofocus
                           class="@error('email') invalid @enderror">
                </div>
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input type="password" name="password" id="password" required
                           class="@error('password') invalid @enderror">
                </div>
                <div class="form-group">
                    <label style="font-weight: normal;">
                        <input type="checkbox" name="remember" value="1"> ログイン状態を保持する
                    </label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">ログイン</button>
            </form>
        </div>
    </div>
@endsection
