# 勤怠　アプリ（Laravel）

Laravel を用いた勤怠管理システムです。
一般ユーザー（従業員）と管理者の2権限構成で、出勤・退勤・休憩管理、勤怠修正申請、承認フローまで実装しています。

PHPUnit による要件ベースの自動テストを実装し、
バリデーション・画面表示・DB更新・承認フローまで網羅しています。

---

## アプリ概要
本アプリでは以下を実装しています。

出勤 / 退勤 / 休憩入 / 休憩戻

月次勤怠一覧表示

勤怠詳細表示

勤怠修正申請機能

管理者承認フロー

メール認証機能

管理者専用画面

## Docker ビルド
git clone https://github.com/taira11/kintai.git

cd kintai

docker-compose up -d --build

## Laravel 環境構築
docker-compose exec php bash

composer install

cp .env.example .env

php artisan key:generate

.env以下修正
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

php artisan migrate

php artisan migrate:fresh --seed

## メール送信（Mailtrap）

本アプリでは、会員登録時のメール認証に Mailtrap を使用しています。

開発環境でメール送信を確認するため、以下の手順で Mailtrap の設定を行ってください。

### Mailtrap 設定手順

1. https://mailtrap.io にアクセスしてアカウントを作成
2. Sandbox を作成
3. Code SamplesにてLaravelのバージョンを設定し SMTP 設定を確認
4. `.env` に以下を設定

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxxxxx
MAIL_PASSWORD=xxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="COACHTECH フリマ"
```

## 開発環境 URL
アプリトップ:http://localhost

会員登録:http://localhost/register

ログイン:http://localhost/login

管理者ログイン:http://localhost/admin/login

phpMyAdmin:http://localhost:8080

## 使用技術（実行環境）
PHP 8.1.33

Laravel 8.83.29

MySQL 8.0.26

nginx 1.21.1

Docker / Docker Compose

jQuery 3.7.1

Stripe（テストモード）

## 主な機能一覧
---
## 勤怠打刻

出勤

退勤

休憩入

休憩戻

ステータス自動表示（勤務外 / 出勤中 / 休憩中 / 退勤済）

## 勤怠一覧
月単位表示

前月 / 翌月切替

当月自動表示

## 勤怠詳細
出勤 / 退勤時刻表示

休憩時間表示

勤務時間自動計算

## 勤怠申請
出退勤修正申請

休憩修正申請

理由必須入力

承認待ち / 承認済み一覧表示

## 日次勤怠一覧
当日全従業員表示

日付切替可能

## 従業員一覧
全ユーザー表示

## 月次勤怠（従業員別）
個別ユーザーの月次勤怠表示

前月 / 翌月切替

## 修正申請管理
承認待ち一覧

承認済み一覧

申請詳細確認

承認処理

承認時に勤怠データ更新

## テスト内容（一部）
出勤 / 退勤処理

休憩処理（複数回対応）

勤怠ステータス表示

月次一覧表示

勤怠詳細表示

修正申請バリデーション

修正申請承認フロー

管理者機能テスト

認証 / メール認証
