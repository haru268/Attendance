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

初期アカウント
管理者	admin@example.com / password
一般サンプル	user@example.com / password (Seederで作成)

![スクリーンショット (124)](https://github.com/user-attachments/assets/49a68e82-2bb8-458e-8ee6-2a4c4c1a3450)
