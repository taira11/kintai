# COACHTECH フリマ（Laravel）

フリマアプリの学習用プロジェクトです。  
会員登録・ログイン（メール認証あり）を前提に、商品出品・購入、いいね、コメント、マイリスト、プロフィール編集などの一連の機能を実装しています。  
PHPUnit を用いて要件ベースのテストを作成し、バリデーション・画面表示・DB反映まで確認できる状態にしています。

---

## Docker ビルド
git clone git@github.com:taira11/flea-market

cd flea-market/online-marketplace

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

## ダミーデータについて
商品データ / カテゴリ / ユーザー情報は Seeder により作成

商品画像は ダウンロード済み素材を storage に保存 して使用

php artisan migrate:fresh --seed

## 画像アップロードについて
本課題では、採点時に画面表示を正しく確認できるよう  
`storage/app/public` 配下にサンプル画像を含めています。

プロフィール画像 storage/app/public/products/profiles に保存

商品画像は torage/app/public/products/products に保存

php artisan storage:link により

/public/storage から参照可能

初回セットアップ時は、以下のコマンドを実行してください。

php artisan storage:link

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
## 認証機能
会員登録

ログイン / ログアウト

メール認証（再送信対応）

## プロフィール
プロフィール画像登録（storage 使用）

ユーザー名 / 住所変更

出品商品一覧 / 購入商品一覧表示

## 商品機能
商品一覧表示
　
商品詳細表示

商品検索（部分一致）

カテゴリ複数選択

商品出品（画像アップロード対応）

購入機能（Stripe Checkout）

## いいね/コメント
商品へのいいね追加 / 解除

マイリスト表示

コメント投稿（認証必須）

## テスト
PHPUnit を使用してテストを実装しています。

php artisan test

## テスト内容（一部）
会員登録バリデーション

ログイン処理

商品一覧取得

いいね機能

コメント投稿

商品購入処理

## Stripe 決済について
Stripe Checkout（テストモード）を使用

最低決済金額の制約により、商品価格は120円以上に設定しています

stripeを登録した後,APIキーを確認し.envに以下の内容を設定

STRIPE_KEY=pk_test_xxxxx

STRIPE_SECRET=sk_test_xxxxx

## カード決済を行う場合は、以下のテストカードをご利用ください。
カード番号：4242 4242 4242 4242

有効期限：未来の日付

CVC：任意の3桁

