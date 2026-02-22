# 🕒 勤怠管理アプリ（Laravel）

Laravel を用いた勤怠管理システムです。  
一般ユーザー（従業員）と管理者の2権限構成で、出勤・退勤・休憩管理、勤怠修正申請、承認フローまで実装しています。

PHPUnit による要件ベースの自動テストを実装し、  
バリデーション・画面表示・DB更新・承認フローまで網羅しています。

---

# 📌 アプリ概要

## 👤 一般ユーザー機能
- 出勤 / 退勤
- 休憩入 / 休憩戻
- 月次勤怠一覧表示
- 勤怠詳細表示
- 勤怠修正申請
- メール認証機能

## 🛠 管理者機能
- 日次勤怠一覧
- 従業員一覧
- 月次勤怠（従業員別）
- 修正申請承認フロー
- 管理者専用ログイン

---

# 🐳 セットアップ手順

## ① Docker ビルド

```bash
git clone https://github.com/taira11/kintai.git
cd kintai
docker-compose up -d --build

## ② Laravel 環境構築

docker-compose exec php bash
composer install
cp .env.example .env
php artisan key:generate

## ③ .env 設定

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

## ④　マイグレーション
php artisan migrate

テストデータ投入：
php artisan migrate:fresh --seed


---

# メール認証（Mailtrap）

本アプリではメール認証に Mailtrap を使用しています。
設定手順

1 https://mailtrap.ioに登録
2 Sandbox 作成
3 SMTP情報取得
4 .env に設定

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxx
MAIL_PASSWORD=xxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="勤怠管理アプリ"

---

# 🌐 開発環境URL

アプリトップ	http://localhost
会員登録	http://localhost/register
ログイン	http://localhost/login
管理者ログイン	http://localhost/admin/login
phpMyAdmin	http://localhost:8080

---

# ⚙ 使用技術

---

# ⚙ 使用技術
## 勤怠打刻
- 出勤
- 退勤
- 休憩入
- 休憩戻
- ステータス自動表示
（勤務外 / 出勤中 / 休憩中 / 退勤済）




---

# ⚙ 使用技術

---

# ⚙ 使用技術

---

# ⚙ 使用技術