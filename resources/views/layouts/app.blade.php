<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ゴルフ場DB管理')｜{{ config('app.name') }}</title>
    <style>
        :root { --primary: #1e7e34; --danger: #c0392b; --bg: #f6f7f9; --border: #d8dde3; }
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Yu Gothic", sans-serif;
               margin: 0; background: var(--bg); color: #222; }
        header { background: var(--primary); color: #fff; padding: 12px 24px;
                 display: flex; align-items: center; gap: 16px; }
        header a { color: #fff; text-decoration: none; }
        header .brand { font-size: 1.1rem; font-weight: bold; }
        header nav { margin-left: auto; display: flex; gap: 16px; align-items: center; }
        main { max-width: 1200px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid var(--border); border-radius: 8px;
                padding: 20px; margin-bottom: 16px; }
        h1 { font-size: 1.4rem; margin: 0 0 16px; }
        h2 { font-size: 1.1rem; margin: 12px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 10px; border-bottom: 1px solid var(--border); text-align: left;
                 font-size: 0.95rem; vertical-align: middle; }
        th { background: #f0f3f6; font-weight: 600; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 0.9rem; }
        .form-group input[type=text], .form-group input[type=email], .form-group input[type=password], .form-group input[type=url],
        .form-group input[type=number], .form-group textarea, .form-group select {
            width: 100%; padding: 8px 10px; border: 1px solid var(--border); border-radius: 4px;
            font-size: 0.95rem; }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .form-row { display: flex; gap: 12px; flex-wrap: wrap; }
        .form-row > .form-group { flex: 1 1 200px; }
        .check-row { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; }
        .check-row label { font-weight: normal; display: inline-flex; align-items: center; gap: 4px; }
        .btn { display: inline-block; padding: 8px 14px; border-radius: 4px; border: 1px solid transparent;
               text-decoration: none; cursor: pointer; font-size: 0.95rem; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-secondary { background: #fff; color: #333; border-color: var(--border); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-sm { padding: 4px 8px; font-size: 0.85rem; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .flash { padding: 10px 14px; border-radius: 4px; margin-bottom: 12px; background: #dff6e0;
                 border: 1px solid #9ed6a8; color: #1e6b30; }
        .errors { padding: 10px 14px; border-radius: 4px; margin-bottom: 12px; background: #fceaea;
                  border: 1px solid #e3a4a4; color: #8a1f1f; }
        .errors ul { margin: 4px 0 0 16px; padding: 0; }
        .invalid { border-color: var(--danger) !important; }
        .text-muted { color: #777; font-size: 0.85rem; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;
                 background: #e6eef5; color: #2c3e50; margin-right: 4px; }
        .pagination { display: flex; gap: 4px; flex-wrap: wrap; list-style: none; padding: 0; margin: 16px 0; }
        .pagination li { display: inline-block; }
        .pagination a, .pagination span { display: inline-block; padding: 6px 10px; border: 1px solid var(--border);
                                          border-radius: 4px; text-decoration: none; color: #333; background: #fff; }
        .pagination .active span { background: var(--primary); color: #fff; border-color: var(--primary); }
        .pagination .disabled span { color: #aaa; }
        .thumb { max-width: 120px; max-height: 80px; object-fit: cover; border-radius: 4px;
                 border: 1px solid var(--border); }
        .thumb-lg { max-width: 320px; max-height: 240px; }
        .login-wrap { max-width: 380px; margin: 80px auto; }
    </style>
</head>
<body>
    @auth
        <header>
            <a href="{{ route('golf-courses.index') }}" class="brand">⛳ ゴルフ場DB管理</a>
            <nav>
                <a href="{{ route('golf-courses.index') }}">一覧</a>
                <a href="{{ route('golf-courses.create') }}">新規登録</a>
                <a href="{{ route('golf-courses.trashed') }}">削除済み</a>
                <span>{{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-secondary">ログアウト</button>
                </form>
            </nav>
        </header>
    @endauth

    <main>
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <strong>入力内容を確認してください。</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
