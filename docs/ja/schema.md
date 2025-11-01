# データベーススキーマ概要

この文書は `src/database/migrations` に定義されているテーブル構造を整理したものです。カラム型は Laravel が生成するデフォルトの型名称（例: `foreignId` は `unsignedBigInteger`）を基準に記載しています。

## users

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | unsignedBigInteger | NO | PK | auto increment | ユーザーID |
| name | string(255) | NO |  |  | 氏名 |
| name_kana | string(255) | YES |  |  | 氏名かな |
| email | string(255) | NO | UNIQUE |  | メールアドレス |
| email_verified_at | timestamp | YES |  |  | メールアドレス検証日時 |
| password | string(255) | NO |  |  | ハッシュ化済みパスワード |
| remember_token | string(100) | YES |  |  | 「ログイン状態を保持する」トークン |
| created_at | timestamp | YES |  |  | 作成日時（自動付与） |
| updated_at | timestamp | YES |  |  | 更新日時（自動付与） |
| deleted_at | timestamp | YES |  |  | 論理削除日時 |

**インデックス / 制約**
- `email` にユニーク制約
- `deleted_at` を含むソフトデリート運用

## password_reset_tokens

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| email | string(255) | NO | PK |  | パスワードリセット対象メールアドレス |
| token | string(255) | NO |  |  | リセットトークン |
| created_at | timestamp | YES |  |  | トークン生成日時 |

## sessions

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | string(255) | NO | PK |  | セッションID |
| user_id | unsignedBigInteger | YES | FK, INDEX |  | 紐付くユーザーID |
| ip_address | string(45) | YES |  |  | アクセス元IP |
| user_agent | text | YES |  |  | ユーザーエージェント |
| payload | longText | NO |  |  | セッションデータ |
| last_activity | integer | NO | INDEX |  | 最終アクセス時刻（UNIX時間） |

**外部キー**
- `user_id` → `users.id`（`nullable`、削除時挙動未指定）

## cache

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| key | string(255) | NO | PK |  | キャッシュキー |
| value | mediumText | NO |  |  | キャッシュ値 |
| expiration | integer | NO |  |  | 期限（UNIX時間） |

## cache_locks

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| key | string(255) | NO | PK |  | ロックキー |
| owner | string(255) | NO |  |  | ロック所有者識別子 |
| expiration | integer | NO |  |  | 期限（UNIX時間） |

## jobs

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | unsignedBigInteger | NO | PK | auto increment | ジョブID |
| queue | string(255) | NO | INDEX |  | キュー名 |
| payload | longText | NO |  |  | シリアライズ済みジョブ |
| attempts | unsignedTinyInteger | NO |  | 0 | 試行回数 |
| reserved_at | unsignedInteger | YES |  |  | 予約確定時刻（UNIX時間） |
| available_at | unsignedInteger | NO |  |  | 実行可能時刻（UNIX時間） |
| created_at | unsignedInteger | NO |  |  | 登録時刻（UNIX時間） |

## job_batches

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | string(255) | NO | PK |  | バッチID |
| name | string(255) | NO |  |  | バッチ名 |
| total_jobs | integer | NO |  |  | 全ジョブ数 |
| pending_jobs | integer | NO |  |  | 未完了ジョブ数 |
| failed_jobs | integer | NO |  |  | 失敗ジョブ数 |
| failed_job_ids | longText | NO |  |  | 失敗ジョブID一覧 |
| options | mediumText | YES |  |  | バッチオプション（JSON等） |
| cancelled_at | integer | YES |  |  | キャンセル時刻（UNIX時間） |
| created_at | integer | NO |  |  | 作成時刻（UNIX時間） |
| finished_at | integer | YES |  |  | 完了時刻（UNIX時間） |

## failed_jobs

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | unsignedBigInteger | NO | PK | auto increment | 失敗ジョブID |
| uuid | string(255) | NO | UNIQUE |  | ジョブUUID |
| connection | text | NO |  |  | 接続ドライバ名 |
| queue | text | NO |  |  | キュー名 |
| payload | longText | NO |  |  | ジョブ内容 |
| exception | longText | NO |  |  | 失敗例外情報 |
| failed_at | timestamp | NO |  | CURRENT_TIMESTAMP | 失敗日時 |

## areas

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | unsignedBigInteger | NO | PK | auto increment | エリアID |
| number | string(255) | NO |  |  | エリア番号 |
| name | string(255) | YES |  |  | エリア名称 |
| boundary_kml | longText | NO |  |  | 境界情報（KML） |
| center_lat | decimal(10, 7) | YES |  |  | 中心緯度 |
| center_lng | decimal(10, 7) | YES |  |  | 中心経度 |
| memo | text | YES |  |  | メモ |
| created_at | timestamp | YES |  |  | 作成日時 |
| updated_at | timestamp | YES |  |  | 更新日時 |

## visits

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | unsignedBigInteger | NO | PK | auto increment | 訪問ID |
| user_id | unsignedBigInteger | NO | FK, INDEX |  | ユーザーID |
| area_id | unsignedBigInteger | NO | FK, INDEX |  | エリアID |
| start_date | date | NO |  |  | 訪問開始日 |
| end_date | date | YES |  |  | 訪問終了日 |
| memo | text | YES |  |  | メモ |
| created_at | timestamp | YES |  |  | 作成日時 |
| updated_at | timestamp | YES |  |  | 更新日時 |

**外部キー**
- `user_id` → `users.id`（`onDelete('cascade')`）
- `area_id` → `areas.id`（`onDelete('cascade')`）

## pins

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | unsignedBigInteger | NO | PK | auto increment | ピンID |
| user_id | unsignedBigInteger | NO | FK, INDEX |  | ユーザーID |
| area_id | unsignedBigInteger | NO | FK, INDEX |  | エリアID |
| visit_id | unsignedBigInteger | YES | FK |  | 紐付く訪問ID |
| lat | decimal(10, 7) | NO |  |  | 緯度 |
| lng | decimal(10, 7) | NO |  |  | 経度 |
| status | enum('visited','not_visited') | NO |  |  | 訪問状況 |
| memo | text | YES |  |  | メモ |
| created_at | timestamp | YES |  |  | 作成日時 |
| updated_at | timestamp | YES |  |  | 更新日時 |

**インデックス / 制約**
- `user_id` → `users.id`（`onDelete('cascade')`）
- `area_id` → `areas.id`（`onDelete('cascade')`）
- `visit_id` → `visits.id`（`nullable`, `onDelete('set null')`）
- 複合インデックス: `area_id`, `lat`, `lng`

## admins

| カラム | 型 | NULL | キー | 既定値 | 説明 |
| --- | --- | --- | --- | --- | --- |
| id | unsignedBigInteger | NO | PK | auto increment | 管理者ID |
| name | string(255) | NO |  |  | 管理者名 |
| email | string(255) | NO | UNIQUE |  | メールアドレス |
| password | string(255) | NO |  |  | ハッシュ化済みパスワード |
| remember_token | string(100) | YES |  |  | 「ログイン状態を保持する」トークン |
| created_at | timestamp | YES |  |  | 作成日時 |
| updated_at | timestamp | YES |  |  | 更新日時 |

---

最終更新日時: 2025-11-01
