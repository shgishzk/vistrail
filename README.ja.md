# Vistrail

# Vistrail 本番ビルド & デプロイ手順（Vite + Laravel）

このドキュメントは、`Vite + Laravel` 開発環境を本番ビルドし、Nginx 経由で提供する構成のための手順です。

---

## 📦 本番ビルドの概要

- Vue + Tailwind CSS などのフロント資産は **Vite の `npm run build`** で `public/build/` に書き出されます。
- Laravel の `@vite()` は本番環境でこの `build` ディレクトリを参照します。
- Vite Dev Server（5173番ポート）は **本番では不要です**。

---

## 🛠️ インストール
以下のコマンドを実行して、アプリケーションをインストールします。Docker と Docker Compose が必要です。

```bash
$ make install
```

データベースにテストデータをシードするには、以下のコマンドを実行します。

```bash
$ make seed
```

## ✅ 環境変数の設定（.env）

```env
APP_ENV=production
APP_URL=https://your-domain.com

# 以下は本番では不要なので削除またはコメントアウト
# VITE_DEV_SERVER_URL=http://localhost:5173