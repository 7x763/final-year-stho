Đồ án tốt nghiệp cá nhân, có AI compilot, đánh giá rủi ro của dự án
0357376158  
Project Management System

Built with Laravel 12 & Filament 4

Ứng dụng quản lý dự án giúp theo dõi ticket, epic, tiến độ công việc và hiệu suất thành viên theo thời gian thực. Phù hợp cho team nội bộ hoặc client portal.

✨ Features

Project management với ticket prefix

Role & Permission (Filament Shield)

Quản lý thành viên & phân quyền

Ticket status tùy chỉnh (màu sắc)

Gán ticket cho nhiều người

Epic management

Bình luận ticket (rich text)

Kanban Board

Timeline View

Biểu đồ đóng góp người dùng

Leaderboard hiệu suất

Export ticket CSV

Client Portal (External Dashboard)

Email notification (Queue-based)

Google OAuth Login

🛠 Tech Stack

PHP >= 8.2

Laravel 12

Filament 4

MySQL

Node.js & npm

📦 Installation
1. Clone repository
git clone https://github.com/7x763/final-year-stho
cd finalyear-project-management-main

2. Install dependencies
composer install
npm install

3. Environment setup
cp .env.example .env
php artisan key:generate

4. Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_management
DB_USERNAME=
DB_PASSWORD=

5. Run migrations
php artisan migrate
php artisan storage:link

6. Create admin user
php artisan make:filament-user

7. Setup roles & permissions
php artisan shield:setup
php artisan shield:install
php artisan shield:super-admin
php artisan shield:generate --all --option=policies

8. Build assets & run server
npm run dev
php artisan serve

🧑‍💻 Usage

Truy cập admin panel:
👉 http://localhost:8000/admin

Đăng nhập bằng tài khoản 

Tạo Project → Ticket Status → Ticket

Quản lý Epic, Board, Timeline & báo cáo

🔐 Google OAuth Login
Environment config

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

Redirect URI

/auth/google/callback

📧 Queue & Email
Queue config
QUEUE_CONNECTION=database

Run worker
php artisan queue:work

Email notifications

Project assignment

Ticket comment

Ticket status update

🧹 Post-Setup Checklist
php artisan optimize:clear


Kiểm tra quyền người dùng

Kiểm tra resource & widget

Test email & queue

📄 License
