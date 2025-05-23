環境構築
Docokerビルド
・git clone git@github.com:haru268/Attendance.git
・docker-compose up -d

Laravel環境構築
・docker-compose exec php bash
・composer install
・cp .env.example .env
・php artisan key:generate
・php artisan migrate
・php artisan db:seed

開発環境
打刻トップ	http://localhost/
ユーザー登録	http://localhost/register	
ログイン（一般）	http://localhost/login	
ログイン（管理者）	http://localhost/admin/login	
phpMyAdmin	http://localhost:8080

使用技術(実行環境)
・PHP　8.2.27
・Laravel 8.83.29
・Docker
・MySQL　8.0.26
・Fortify
・PHPUnit

初期アカウント(Seederで作成済み)
管理者	admin@example.com / password
一般サンプル	user@example.com / password  / 手動で「出勤→休憩→退勤」を試したい場合
一般サンプル2 seeduser@example.com  / password / 一覧や詳細表示のデータ確認用に直近30日分シード済み

追記
・PHPUnitを使用してテスト済み

.env の変更内容
アプリ基本設定
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
データベース
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

![スクリーンショット (124)](https://github.com/user-attachments/assets/49a68e82-2bb8-458e-8ee6-2a4c4c1a3450)
