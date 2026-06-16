# ゴルフ場DBメンテナンスシステム

Laravel 11 + MySQL 8 で構築した管理者向けゴルフ場マスタメンテナンス画面。

## 機能

- 管理者ログイン（自前 LoginController、新規登録なし）
- ゴルフ場の一覧 / 詳細 / 新規登録 / 編集 / 削除（論理）/ 復元 / 完全削除
- 検索（キーワード／都道府県／言語／種別）+ ページネーション（条件保持）
- 画像 3 枚アップロード（差し替え／削除）
- バリデーション（FormRequest、緯度経度ペアチェック含む）

## セットアップ手順

```bash
# 1. 依存パッケージのインストール
composer install

# 2. .env を作成（.env.example をコピーして編集）
cp .env.example .env
php artisan key:generate

# 3. MySQL 側でデータベースを作成（例）
#    CREATE DATABASE golf_course_admin
#    DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. .env を編集（DB 接続情報と ADMIN_EMAIL/ADMIN_PASSWORD）

# 5. ストレージのシンボリックリンク
php artisan storage:link

# 6. マイグレーション + シーダー
php artisan migrate:fresh --seed

# 7. 起動
php artisan serve     # http://127.0.0.1:8000
```

## 初期管理者アカウント

`.env` の `ADMIN_EMAIL` / `ADMIN_PASSWORD` で投入されます（デフォルトは `admin@example.com` / `password`）。

## ディレクトリ構成

```
app/Http/Controllers/Auth/LoginController.php
app/Http/Controllers/GolfCourseController.php
app/Http/Requests/GolfCourseRequest.php
app/Models/GolfCourse.php
database/migrations/2026_06_17_000001_create_golf_courses_table.php
database/migrations/2026_06_17_000002_add_softdeletes_to_golf_courses_table.php
database/seeders/AdminUserSeeder.php
database/seeders/GolfCourseSeeder.php
database/factories/GolfCourseFactory.php
resources/views/
  layouts/app.blade.php
  auth/login.blade.php
  golf_courses/{index,show,create,edit,_form,delete,trashed}.blade.php
routes/web.php
```

## ルート一覧

| Method | URL | Name |
| --- | --- | --- |
| GET | /login | login |
| POST | /login | (LoginController@store) |
| POST | /logout | logout |
| GET | /golf-courses | golf-courses.index |
| GET | /golf-courses/create | golf-courses.create |
| POST | /golf-courses | golf-courses.store |
| GET | /golf-courses/{golfCourse} | golf-courses.show |
| GET | /golf-courses/{golfCourse}/edit | golf-courses.edit |
| PUT | /golf-courses/{golfCourse} | golf-courses.update |
| GET | /golf-courses/{golfCourse}/delete | golf-courses.confirm-delete |
| DELETE | /golf-courses/{golfCourse} | golf-courses.destroy |
| GET | /golf-courses/trashed | golf-courses.trashed |
| POST | /golf-courses/{id}/restore | golf-courses.restore |
| DELETE | /golf-courses/{id}/force | golf-courses.force-destroy |

## 設計書からの主な修正点

| 既存 DDL の問題 | 対応 |
| --- | --- |
| PRIMARY KEY が無い | `bigIncrements('id')` で PK + AUTO_INCREMENT |
| `id` が DEFAULT NULL | NOT NULL に修正 |
| `lat`/`lng` が `double(20,15)` | 桁指定なしの `double` |
| 論理削除カラムが無い | 別マイグレーションで `softDeletes()` 追加 |
| 検索高速化のインデックスなし | `country_code`/`locale`/`state_prefecture` に index |
