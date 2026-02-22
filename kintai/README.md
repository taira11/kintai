# 🕒 勤怠管理アプリ（Laravel）

## 📌 概要

Laravel を用いた勤怠管理システムです。  
一般ユーザー（従業員）と管理者の2権限構成で、  
出勤・退勤・休憩管理、勤怠修正申請、承認フローまで実装しています。

PHPUnit による要件ベースの自動テストを実装し、  
バリデーション・画面表示・DB更新まで網羅しています。

---

## ⚙ 使用技術

- PHP 8.1.33
- Laravel 8.83.29
- MySQL 8.0.26
- nginx 1.21.1
- Docker / Docker Compose
- PHPUnit

---

## 🧩 主な機能

### 👤 一般ユーザー

- 出勤 / 退勤
- 休憩入 / 休憩戻
- 月次勤怠一覧
- 勤怠詳細表示
- 勤怠修正申請
- メール認証

### 🛠 管理者

- 日次勤怠一覧
- 従業員一覧
- 月次勤怠（従業員別）
- 修正申請承認
- 勤怠データ更新

---

## Docker ビルド
```env
git clone https://github.com/taira11/kintai.git

cd kintai

docker-compose up -d --build
```

## Laravel 環境構築
```env
docker-compose exec php bash

composer install

cp .env.example .env

php artisan key:generate
```

.env以下修正
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

## マイグレーション
```env
php artisan migrate
```

## ダミーデータ
```env
php artisan migrate:fresh --seed
```

### Mailtrap 設定手順

1. https://mailtrap.io にアクセスしてアカウントを作成
2. Sandbox を作成
3. SMTP 設定を確認
4. `.env` に以下を設定

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxxxxx
MAIL_PASSWORD=xxxxxxxx
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="COACHTECH"
```

## 開発環境 URL
アプリトップ:http://localhost

会員登録:http://localhost/register

ログイン:http://localhost/login

ログイン:http://localhost/admin/login

phpMyAdmin:http://localhost:8080
