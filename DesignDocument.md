# ゴルフ場DBメンテナンスシステム　詳細設計書

> **対象**：PHP/Laravel 学習修了後の新入社員 実戦課題
> **目的**：既存テーブル（`golf_courses`）を題材に、認証付きの**マスタメンテナンス画面**を一から設計・実装できること
> **前提**：Stage5（タスク管理 CRUD）課題を一通り完成させていること。本課題はその応用編。

---

## 0. この課題の進め方（最初に読むこと）

本書は「何を作るか・どう振る舞うか」を定義した**仕様書**です。実装コードは載せていません。Laravel の作法に従い自分で組み立ててください。

### 0.1 Stage5 との違い（難易度を一段上げています）

| 観点 | Stage5（タスク管理） | 本課題（ゴルフ場メンテ） |
| --- | --- | --- |
| テーブル | 自分で設計（簡単） | **既存DDLを読み解く**（実務に近い） |
| 認証 | ユーザー登録あり | **管理者のみ**。新規登録なし（シード投入） |
| 検索 | なし | **複数条件の絞り込み検索が必須** |
| ページネーション | なし | **必須**（数千件のデータ前提） |
| バリデーション | 3項目 | 20項目超（型・桁・範囲・URL・緯度経度） |
| 削除 | 物理削除 | **論理削除（SoftDeletes）+ 復元** |
| 画像 | なし | **複数画像のアップロード・差し替え** |
| 多言語 | なし | `locale`/`country_code` で**多言語データ**を扱う |

### 0.2 推奨スケジュール（目安：合計 5〜7 日）

| Day | 内容 |
| --- | --- |
| 1 | 環境構築・DDL の読み解き・マイグレーション再設計（4〜5章） |
| 2 | 認証（管理者ログイン）・シーダー（6章） |
| 3 | 一覧（検索・ページネーション）（7.1） |
| 4 | 新規作成・編集（7.2〜7.3、バリデーション） |
| 5 | 画像アップロード（8章） |
| 6 | 論理削除・削除済み一覧・復元（7.4、9章） |
| 7 | セキュリティ確認・自己チェック・仕上げ（10〜11章） |

### 0.3 進めるときの約束

- 設計書を読み、自分で実装 → 公式ドキュメント → 先輩へ質問、の順。
- 1機能ずつ「実装 → 自己チェック（11章）→ 次へ」。
- 「動けば OK」ではなく、**他人のレビューに耐えるコード**を意識する。

---

## 1. システム概要

業務で保有している**ゴルフ場マスタ（既存テーブル `golf_courses`、約数千件）**を、運用担当者が Web 画面から保守できるようにする社内管理ツールです。

- 管理者のみがログインして利用する（顧客は使わない）。
- ゴルフ場の情報を **一覧 / 検索 / 登録 / 編集 / 削除 / 復元** できる。
- 写真（最大3枚）の差し替えができる。
- 国・言語（`country_code`/`locale`）別にデータが存在し、同じ施設が複数言語で登録される運用を想定する。

### 1.1 利用者像

| ロール | 説明 |
| --- | --- |
| 管理者（admin） | 営業企画部のスタッフ。1〜数名。社内 LAN からアクセス。 |

> 本課題では「一般ユーザー」は登場しない。**全画面が認証必須**。

### 1.2 用語定義

| 用語 | 説明 |
| --- | --- |
| ゴルフ場（Course） | 1施設1レコード。本課題のメイン対象。 |
| 種別フラグ | `indoor` / `outdoor` / `short_course` / `long_course` の4つの真偽値。複数同時に true もあり得る（例：屋外＋ロング）。 |
| ロケール | `locale`（例：`ja`/`en`）。同じ施設でも言語別にデータが分かれる前提。 |
| 論理削除 | 行を物理削除せず、`deleted_at` を入れて「削除済み」状態にする方式。誤操作からの復元を可能にする。 |

---

## 2. 動作環境・技術スタック

| 項目 | 内容 |
| --- | --- |
| 言語 | PHP 8.3 以上 |
| フレームワーク | Laravel 12 / 13 系 |
| DB | **MySQL 8.x**（既存テーブルが MySQL 前提のため） |
| 必須 PHP 拡張 | `pdo_mysql`, `openssl`, `mbstring`, `gd` または `imagick`（画像処理用） |
| ビュー | Blade テンプレート |
| ORM | Eloquent（SoftDeletes トレイト使用） |
| ストレージ | ローカル `storage/app/public`（`php artisan storage:link` 必須） |
| 起動 | `php artisan serve` |

---

## 3. 全体構成

```
[ブラウザ（管理者）]
   │  HTTPリクエスト（社内LAN想定）
   ▼
auth ミドルウェア ── 未ログインは /login へ
   │
   ▼
routes/web.php
   │
   ▼
Controllers/
  ├ Auth/LoginController     ログイン/ログアウト
  └ GolfCourseController     一覧・検索・CRUD・復元
   │
   ▼
Models/
  ├ User         （管理者）
  └ GolfCourse   （SoftDeletes・スコープ・ミューテータ）
   │
   ▼
[MySQL DB] + [storage/app/public/golf_courses/{id}/]（画像）

Controller ──▶ View(Blade) ──▶ HTML
```

### 3.1 主なファイル構成

```
app/
├ Http/
│  ├ Controllers/
│  │  ├ Auth/LoginController.php
│  │  └ GolfCourseController.php
│  └ Requests/
│     └ GolfCourseRequest.php        フォームリクエスト（store/update共通）
└ Models/
   ├ GolfCourse.php
   └ User.php
database/
├ migrations/
│  ├ 0001_..._create_users_table.php
│  ├ XXXX_..._create_golf_courses_table.php   ★自作（既存DDLの再設計）
│  └ XXXX_..._add_softdeletes_to_golf_courses_table.php
├ seeders/
│  ├ DatabaseSeeder.php
│  └ AdminUserSeeder.php              ★管理者を1人投入
└ factories/
   └ GolfCourseFactory.php            ★テスト・デモ用ダミーデータ
resources/views/
├ layouts/app.blade.php
├ auth/login.blade.php
└ golf_courses/
   ├ index.blade.php       一覧（検索ボックス + ページネーション）
   ├ create.blade.php
   ├ edit.blade.php
   ├ show.blade.php        詳細表示（画像プレビュー含む）
   ├ delete.blade.php      削除確認
   └ trashed.blade.php     削除済み一覧（復元用）
routes/web.php
```

---

## 4. 対象テーブル（既存DDL）の読み解きと再設計

### 4.1 既存DDL（与えられた素材）

```sql
CREATE TABLE `golf_courses` (
  `id` bigint UNSIGNED DEFAULT NULL,
  `locale` varchar(2) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `state_prefecture` varchar(255) DEFAULT NULL,
  `course_name` varchar(255) NOT NULL,
  `kinds` int DEFAULT NULL,
  `web` text,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `indoor` tinyint(1) DEFAULT '0',
  `outdoor` tinyint(1) DEFAULT '0',
  `short_course` tinyint(1) DEFAULT '0',
  `long_course` tinyint(1) DEFAULT '0',
  `lat` double(20,15) DEFAULT NULL,
  `lng` double(20,15) DEFAULT NULL,
  `form_email` varchar(255) DEFAULT NULL,
  `reservation` varchar(255) DEFAULT NULL,
  `reservation_method` varchar(255) DEFAULT NULL,
  `remarks` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image1` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.2 まず DDL の問題点を見抜く（重要）

新人が最初にぶつかる**実務的な気づき**ポイントです。

| # | 問題点 | 影響 | 本課題での対応 |
| --- | --- | --- | --- |
| 1 | **PRIMARY KEY が定義されていない** | Eloquent が動かない・一意性が保証されない | マイグレーションで `id` を **PK + AUTO_INCREMENT** に修正 |
| 2 | `id` が `DEFAULT NULL` | 主キーは NULL 不可が原則 | 上記と合わせて NOT NULL にする |
| 3 | `lat`/`lng` が `double(20,15)` | 桁指定は MySQL 8.0.17 で非推奨 | マイグレーションでは `double` で宣言する |
| 4 | `kinds` の意味が不明 | 後で何の値か追えなくなる | **業務担当に確認**するのが正解。本課題では「会員区分などの分類コード（任意の整数）」として扱う |
| 5 | 同じ施設の多言語データを区別する方法が曖昧 | データ重複の温床 | 検索や一意性の指針を 4.4 で明示 |

> **学び**：実際の業務でも、既存テーブルは「そのまま使えない」ことがほとんど。**DDLを鵜呑みにせず、まず問題点を洗い出す**のがエンジニアの最初の仕事。

### 4.3 マイグレーション仕様（再設計後）

| カラム | 型 | NULL | 既定値 | 備考 |
| --- | --- | --- | --- | --- |
| id | bigIncrements | × | auto | **PK・自動採番**（DDLの問題1・2を修正） |
| locale | string(2) | × | - | `ja` / `en` 等の ISO 639-1 |
| country_code | string(2) | × | - | `JP` / `US` 等の ISO 3166-1 alpha-2 |
| state_prefecture | string(255) | ○ | NULL | 都道府県・州名 |
| course_name | string(255) | × | - | 施設名 |
| kinds | integer | ○ | NULL | 分類コード（業務上の意味は要確認） |
| web | text | ○ | NULL | 公式サイトURL |
| phone | string(255) | ○ | NULL | 代表電話 |
| address | string(255) | ○ | NULL | 住所 |
| indoor | boolean | ○ | false | 室内コースか |
| outdoor | boolean | ○ | false | 屋外コースか |
| short_course | boolean | ○ | false | ショートコースを持つか |
| long_course | boolean | ○ | false | ロングコースを持つか |
| lat | double | ○ | NULL | 緯度 -90.0〜90.0 |
| lng | double | ○ | NULL | 経度 -180.0〜180.0 |
| form_email | string(255) | ○ | NULL | 問い合わせメール |
| reservation | string(255) | ○ | NULL | 予約先URL／番号 |
| reservation_method | string(255) | ○ | NULL | 予約手段（電話／WEB／メール 等） |
| remarks | text | ○ | NULL | 備考 |
| image1 | string(255) | ○ | NULL | 画像1ファイルパス |
| image2 | string(255) | ○ | NULL | 画像2ファイルパス |
| image3 | string(255) | ○ | NULL | 画像3ファイルパス |
| created_at | timestamp | ○ | - | Eloquent 自動 |
| updated_at | timestamp | ○ | - | Eloquent 自動 |
| **deleted_at** | timestamp | ○ | NULL | **追加**：SoftDeletes 用 |

**マイグレーション実装のヒント**

- `$table->bigIncrements('id');` で DDL の問題1・2を一発で修正。
- ブール系は `$table->boolean('indoor')->default(false);` を4回。
- 緯度経度は `$table->double('lat')->nullable();`（桁指定は付けない）。
- `deleted_at` は **別マイグレーション**で `softDeletes()` を使って追加すると、既存DDL（与えられた状態）からの差分が分かりやすい。
- インデックス：`country_code`, `locale`, `state_prefecture` に index を張る（検索の高速化）。

### 4.4 多言語データの扱い

同じ施設について `(locale=ja, country_code=JP)` と `(locale=en, country_code=JP)` の2行が存在しうる前提です。

- **本課題では言語別に独立した行として扱う**（紐付けは行わない）。
- 一覧画面では `locale` を絞り込み条件として提供する。
- 完全な多言語管理（翻訳テーブル化）は**発展課題**とする。

---

## 5. 画面一覧と画面遷移

### 5.1 画面一覧

| No | 画面名 | URL | 概要 |
| --- | --- | --- | --- |
| S1 | ログイン | `GET /login` | 管理者ログイン |
| S2 | 一覧（検索結果） | `GET /golf-courses` | 検索フォーム＋表＋ページネーション |
| S3 | 詳細表示 | `GET /golf-courses/{id}` | 全項目と画像プレビュー |
| S4 | 新規作成 | `GET /golf-courses/create` | 入力フォーム |
| S5 | 編集 | `GET /golf-courses/{id}/edit` | 既存値入りフォーム |
| S6 | 削除確認 | `GET /golf-courses/{id}/delete` | 「本当に削除しますか？」 |
| S7 | 削除済み一覧 | `GET /golf-courses/trashed` | 復元・完全削除の起点 |

S1 以外はすべて `auth` ミドルウェア配下。

### 5.2 画面遷移図

```
┌──────────────┐ ログイン成功    ┌──────────────────┐
│ S1 ログイン   │────────────────▶│ S2 一覧（検索）   │◀─┐
└──────────────┘                  └──────────────────┘  │
   ▲ ログアウト                       │  │  │  │  │      │
   └──────────────────────────────────┘  │  │  │  └──────┤
                              「新規作成」│  │  │「削除済み」
                                          ▼  │  │  │      │
                                  ┌──────────┐│  │  ▼      │
                                  │S4 新規作成││  │ ┌──────────┐
                                  └──────────┘│  │ │S7 削除済み│
                                       │保存  │  │ │一覧       │
                                       └──────┘  │ └──────────┘
                                                  │  │復元
                              「詳細」/「編集」    │  └──────────▶ S2
                                          ▼       ▼
                                  ┌──────────┐ ┌──────────┐
                                  │ S3 詳細   │ │ S5 編集   │
                                  └──────────┘ └──────────┘
                                       │「削除」    │更新
                                       ▼            └──▶ S2
                                  ┌──────────┐
                                  │S6 削除確認│──▶ S2（フラッシュ）
                                  └──────────┘
```

- ルート `/` は `/golf-courses` へリダイレクト。
- 各処理の成功後は一覧へ戻し、フラッシュメッセージで結果を伝える（PRG パターン）。

---

## 6. 認証機能の詳細設計

Stage5 と異なり、**ユーザー登録画面は作らない**。管理者は**シーダーで投入**する。

### 6.1 ルート

| メソッド | URL | アクション | 認証 |
| --- | --- | --- | --- |
| GET | `/login` | LoginController@show | guest |
| POST | `/login` | LoginController@store | guest |
| POST | `/logout` | LoginController@destroy | auth |

### 6.2 ログイン処理

Stage5 の LoginController と同等仕様。

- `email`（必須・email）、`password`（必須・string）をバリデーション。
- `Auth::attempt()` 失敗時は `email` フィールドに `メールアドレスまたはパスワードが違います。`（区別しない＝ユーザー列挙対策）。
- 成功時は `session()->regenerate()` → `golf-courses.index` へ。
- ログアウトは **POST**（CSRF 対策）。

### 6.3 管理者の投入（AdminUserSeeder）

| 項目 | 値（例） |
| --- | --- |
| name | 管理者 |
| email | `admin@example.com`（`.env` から取得すること） |
| password | `.env` の `ADMIN_PASSWORD` をハッシュ化して保存 |

- パスワードを**シーダーのソースに直書きしない**。`.env` から `env('ADMIN_PASSWORD')` で取得する。
- 初期化は `php artisan migrate:fresh --seed`。

---

## 7. ゴルフ場 CRUD の詳細設計

### 7.1 一覧（index）

#### URL とパラメータ

`GET /golf-courses?q=...&prefecture=...&locale=...&kind=...&page=...`

| パラメータ | 意味 | バリデーション |
| --- | --- | --- |
| q | フリーワード（施設名・住所の部分一致） | nullable, string, max:100 |
| prefecture | 都道府県・州（完全一致） | nullable, string, max:255 |
| locale | 言語コード | nullable, in:ja,en |
| kind | 種別フラグ | nullable, in:indoor,outdoor,short,long |
| page | ページ番号（Laravel 標準） | - |

#### 検索条件の組み立てルール

- 各パラメータは **AND** で結合する。
- `q` は `course_name` または `address` を **LIKE 部分一致**（`like` の値は `%キーワード%`。ユーザー入力に `%`/`_` が含まれる場合のエスケープも考慮）。
- `kind` は対応するブール列を `true` 条件にする（例：`kind=indoor` → `where('indoor', true)`）。
- `prefecture`, `locale` は完全一致。
- どのパラメータが空でも崩れないこと（`when()` メソッドの使用を推奨）。

#### 表示仕様

- 1ページ **20件**。並びは `id` 降順。
- 表示カラム：`id` / `course_name` / `state_prefecture` / `locale` / 種別（indoor等のアイコン or ラベル） / `phone` / 操作（詳細・編集・削除）。
- ページネーションリンクを必ず描画する（`$courses->links()`）。
- **検索条件はページ送り後も維持**する（`appends(request()->query())`）。
- ヒット 0 件のときは「該当するゴルフ場が見つかりませんでした。」を表示。

#### コントローラ実装の指針

- 検索ロジックは Controller に書きすぎず、**モデルのスコープ**（`scopeSearch` 等）に切り出す。
- N+1 はそもそも本テーブル単独なので発生しないが、画像URL の整形は**アクセサ**（`getImage1UrlAttribute` 等）で行う。

### 7.2 新規作成（create / store）

- `create`：空のフォームを表示。`locale`/`country_code` の初期値は `ja`/`JP`。
- `store`：8章のバリデーションを通過 → `GolfCourse::create()` → 一覧へ（フラッシュ `登録しました。`）。
- 画像は同時にアップロード可（保存方法は8章）。

### 7.3 編集（edit / update）

- `edit`：既存値を埋めたフォームを表示。
- `update`：バリデーション → `$course->update($data)` → 一覧へ（フラッシュ `更新しました。`）。
- 画像の差し替えは**フィールド単位**：
  - 何も触らなければ既存画像のまま。
  - 「画像を削除する」チェックボックス ON → 既存画像を消去し DB を NULL に。
  - 新しいファイル選択 → 既存を削除して差し替え。

### 7.4 削除（confirmDelete / destroy）と復元（restore / forceDestroy）

| アクション | URL | 処理 |
| --- | --- | --- |
| 削除確認 | `GET /golf-courses/{id}/delete` | 確認画面（施設名を見せる） |
| 論理削除 | `DELETE /golf-courses/{id}` | `$course->delete()`（SoftDeletes が `deleted_at` をセット） |
| 削除済み一覧 | `GET /golf-courses/trashed` | `onlyTrashed()` で抽出 |
| 復元 | `POST /golf-courses/{id}/restore` | `$course->restore()` |
| 完全削除 | `DELETE /golf-courses/{id}/force` | `$course->forceDelete()` + 画像ファイル削除 |

- **削除済みデータは通常の一覧には出さない**（SoftDeletes トレイトでデフォルトそうなる）。
- 完全削除のときだけ、画像ファイルもストレージから削除する。論理削除では画像を残す。
- 完全削除画面でも**確認のワンクッション**を入れる。

---

## 8. バリデーションと画像アップロード

### 8.1 入力バリデーション（store / update 共通）

`GolfCourseRequest`（FormRequest クラス）に切り出して、Controller は薄く保つ。

| 項目 | ルール | エラーメッセージ |
| --- | --- | --- |
| locale | required / string / size:2 / in:ja,en | 言語コードを正しく指定してください。 |
| country_code | required / string / size:2 | 国コードを正しく指定してください。 |
| state_prefecture | nullable / string / max:255 | - |
| course_name | required / string / max:255 | コース名を入力してください。 |
| kinds | nullable / integer | 分類コードは整数で入力してください。 |
| web | nullable / url / max:2048 | 公式サイトURLの形式が正しくありません。 |
| phone | nullable / string / max:30 | - |
| address | nullable / string / max:255 | - |
| indoor / outdoor / short_course / long_course | boolean | - |
| lat | nullable / numeric / between:-90,90 | 緯度は -90〜90 の範囲で入力してください。 |
| lng | nullable / numeric / between:-180,180 | 経度は -180〜180 の範囲で入力してください。 |
| form_email | nullable / email / max:255 | メールアドレスの形式が正しくありません。 |
| reservation | nullable / string / max:255 | - |
| reservation_method | nullable / string / max:255 | - |
| remarks | nullable / string / max:5000 | 備考は5000文字以内で入力してください。 |
| image1 / image2 / image3 | nullable / image / mimes:jpg,jpeg,png,webp / max:5120 | 画像はJPG/PNG/WebPで5MB以内にしてください。 |

**チェックボックス系の罠**

HTML のチェックボックスは未チェック時に**値が送信されない**。Laravel 側では `$request->boolean('indoor')` を使うか、ビュー側で `<input type="hidden" name="indoor" value="0">` を直前に置く。これを忘れると「OFFにできない」バグになる。

**緯度経度の整合性チェック**

`lat` と `lng` は「両方入力 or 両方空」にする。片方だけ入っていたら独自バリデーションで弾く（`after` クロージャを使う）。

### 8.2 画像アップロード仕様

| 項目 | 仕様 |
| --- | --- |
| 保存ディスク | `public`（`storage/app/public`） |
| 保存パス | `golf_courses/{id}/{ランダム名}.{拡張子}` |
| DB に保存する値 | 上記の相対パス（例：`golf_courses/42/abc123.jpg`） |
| 表示時のURL | `Storage::url($path)`（=`/storage/golf_courses/42/abc123.jpg`） |
| 公開設定 | `php artisan storage:link` を必ず実施 |
| 既存画像の差し替え | 新ファイル保存 → DB 更新 → **古いファイルを Storage::delete()** |
| 完全削除時 | 当該レコードの画像3点をまとめて削除 |

**よくあるミス**

- **ファイル名にユーザー入力を使う**：日本語ファイル名や記号でトラブル。必ず `Str::random()` か `hashName()` で安全な名前にする。
- **古いファイルが残り続ける**：差し替え・完全削除時に古いファイルを消し忘れ、ストレージが肥大化する。
- **`storage:link` 忘れ**：ファイルは保存されるが画面で 404 になる。

---

## 9. 認可

- 全ルートを `auth` ミドルウェアで保護する。
- 本課題ではロールが「admin のみ」のため、ユーザー間のデータ分離は不要（誰でも全件操作できる）。
- 発展課題として「閲覧のみのロール（viewer）」を追加する場合は、Laravel の **Policy** または **Gate** を使う。

---

## 10. セキュリティ要件（必達）

Stage5 の要件はすべて引き継いだうえで、ファイル系・検索系の対策を追加する。

| # | 脅威 | 対策 |
| --- | --- | --- |
| 1 | SQL インジェクション | Eloquent/クエリビルダのバインディング。`whereRaw()` を使わない |
| 2 | XSS | Blade の `{{ }}`。`{!! !!}` は使わない |
| 3 | CSRF | 全 POST/PUT/DELETE フォームに `@csrf` |
| 4 | パスワード平文保存 | User の `password` cast に `'hashed'` |
| 5 | セッション固定化 | ログイン時 `session()->regenerate()` |
| 6 | ユーザー列挙 | ログイン失敗メッセージを統一 |
| 7 | LIKE 検索のメタ文字 | `%` `_` をエスケープしてから `where('col','like',$keyword)` に渡す |
| 8 | ファイルアップロード | `image` + `mimes` でホワイトリスト。サイズ上限。ファイル名は再生成。実行可能パスに置かない |
| 9 | パストラバーサル | 画像保存先パスにユーザー入力を含めない。`{id}` のみ使う |
| 10 | オープンリダイレクト | 戻り先 URL をパラメータで受け取らない（受け取るなら `route()` でホワイトリスト化） |
| 11 | 大量データ全件表示 | 一覧は必ず `paginate()`（`get()` で全件は禁止） |

---

## 11. 自己チェックリスト（受け入れ基準）

実装後、以下を手動で確認すること。

### 11.1 認証

- [ ] 未ログインで `/golf-courses` にアクセスすると `/login` へ。
- [ ] 間違ったパスワードでも、存在しないメールでも、同じ文言が出る。
- [ ] DB の `users.password` がハッシュ化されている。
- [ ] ログアウトは POST であり、GET では動かない。

### 11.2 一覧・検索

- [ ] 1ページ20件で表示される。
- [ ] 該当0件の検索結果でも画面が壊れず、メッセージが出る。
- [ ] 検索条件を入れた状態で2ページ目へ進んでも**条件が維持される**。
- [ ] `q=%` や `q=_` のような LIKE メタ文字を入れても、想定通りの「部分一致」として扱われる（全件ヒットにならない）。
- [ ] `kind=indoor` で絞り込むと `indoor=true` のレコードのみ出る。
- [ ] `prefecture=東京都` で東京都のデータのみ出る。

### 11.3 登録・編集

- [ ] `course_name` 空欄では登録できない。
- [ ] `lat=100` のような範囲外の値は弾かれる。
- [ ] `lat` だけ入力（`lng` 空）は弾かれる。
- [ ] チェックボックスを OFF にすると、DB が `0` に更新される（**ON のまま残らない**）。
- [ ] 画像未選択で更新しても、既存画像は消えない。

### 11.4 画像

- [ ] JPG/PNG/WebP 以外（例：PDF・EXE）はアップロードできない。
- [ ] 5MB 超のファイルは弾かれる。
- [ ] 画像差し替え時、**古いファイルがストレージから消える**。
- [ ] `php artisan storage:link` 後、画像が画面に表示される。

### 11.5 削除・復元

- [ ] 削除後、`deleted_at` がセットされ、通常一覧には出ない。
- [ ] 削除済み一覧に表示される。
- [ ] 復元するとまた通常一覧に戻る。
- [ ] 完全削除すると DB から消え、画像ファイルもストレージから消える。

### 11.6 セキュリティ

- [ ] `course_name` に `<script>alert(1)</script>` を入れても、画面でスクリプトが実行されない。
- [ ] フォームから `@csrf` を外すと 419 で拒否される。

---

## 12. 環境構築・起動手順

```bash
# 1. プロジェクト作成
composer create-project laravel/laravel golf-course-admin
cd golf-course-admin

# 2. MySQLの設定（.env）
#    DB_CONNECTION=mysql / DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD
#    ADMIN_PASSWORD=（任意の文字列）も追記しておく

# 3. マイグレーション・モデル等の生成
php artisan make:migration create_golf_courses_table
php artisan make:migration add_softdeletes_to_golf_courses_table
php artisan make:model GolfCourse
php artisan make:controller GolfCourseController
php artisan make:controller Auth/LoginController
php artisan make:request GolfCourseRequest
php artisan make:seeder AdminUserSeeder
php artisan make:factory GolfCourseFactory

# 4. ストレージのシンボリックリンク
php artisan storage:link

# 5. 実行
php artisan migrate:fresh --seed
php artisan serve   # http://127.0.0.1:8000
```

### 12.1 既存データの取り込み（任意）

業務で持っているゴルフ場 CSV を読み込ませる場合：

```bash
php artisan tinker
>>> # CSV を読んで GolfCourse::create() するスクリプトを書く
```

正式な CSV インポートコマンドの実装は**発展課題**。

---

## 13. 評価ポイント（指導者向け / 自己採点の指針）

| 観点 | 見るところ | 配点目安 |
| --- | --- | --- |
| 機能の完成度 | 11章のチェックリストの通過率 | 35% |
| DDLの読み解き | 4.2 の問題点に気づき、マイグレーションで適切に修正できているか | 10% |
| 検索・ページネーション | スコープへの切り出し、条件維持、LIKEエスケープ | 15% |
| 画像処理 | 差し替え時の旧ファイル削除、ファイル名再生成 | 10% |
| 論理削除 | SoftDeletes の正しい運用、復元・完全削除の使い分け | 10% |
| Laravel の作法 | FormRequest、アクセサ/ミューテータ、`when()`、`paginate()->appends()` | 10% |
| セキュリティ | XSS / CSRF / LIKEメタ文字 / アップロード制限 | 5% |
| コードの質 | 命名・重複の排除・Controller の薄さ | 3% |
| ドキュメント | README・コミットメッセージ | 2% |

### よくある減点ポイント

- 一覧で `GolfCourse::all()` を使ってしまい、全件取得 → メモリと描画が破綻。
- 検索条件をページ送りで失う（`appends()` 忘れ）。
- チェックボックスを OFF にできない（hidden で 0 を送る or `boolean()` を使う）。
- 画像差し替えで古いファイルを消し忘れる。
- パスワードを seeder にハードコードする。
- バリデーションを Controller に直書きして肥大化させる。
- マイグレーションで DDL の問題（PK 欠落）を見抜かずそのまま使う。

---

## 14. 発展課題（余裕があれば挑戦）

1. **CSV 一括取り込み**：`php artisan golf-courses:import path/to/file.csv` のような Artisan コマンドを作る。1行ずつバリデーションし、失敗行は別ファイルに書き出す。
2. **CSV エクスポート**：検索結果をそのまま CSV ダウンロードできるようにする。
3. **地図プレビュー**：詳細画面で `lat`/`lng` を Google Maps もしくは Leaflet（OSS）で表示。
4. **ロール分離**：閲覧のみの `viewer` ロールを追加し、Policy で書き込みを禁止する。
5. **多言語データの紐付け**：同じ施設の `ja` 版と `en` 版をリンクさせる別テーブル（`course_groups`）を導入。
6. **画像のサムネイル生成**：Intervention Image を使い、保存時に縮小版も作る。
7. **テスト**：主要シナリオの Feature テストを書き、`php artisan test` を緑にする。

---

*以上。最初は「ログインして一覧が出る」までを最優先で動かしてから、検索・画像・論理削除と順に積み上げてください。実務では「動くものを早く出して、少しずつ強くする」のが鉄則です。*