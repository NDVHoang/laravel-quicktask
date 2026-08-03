# Chapter 8: Authentication

## 1. Authentication là gì?

Phân biệt:
- **Authentication (Xác thực)**: Xác định người dùng là ai (đã đăng nhập thành công hay chưa).
- **Authorization (Phân quyền)**: Xác định người dùng được làm gì (có quyền thực hiện một hành động cụ thể hay không).

Liên hệ trong Quicktask:
- `auth` middleware đảm nhiệm Authentication.
- `CheckSuperAdmin` middleware đảm nhiệm Authorization.

## 2. Laravel starter kit

Hệ sinh thái Laravel cung cấp nhiều starter kits như Laravel Breeze, Laravel Jetstream, Laravel Fortify (chỉ backend).
Trong dự án Quicktask thực tế này, chúng ta sử dụng **Laravel Breeze** với stack **React / Inertia**.

## 3. Kiến trúc Authentication thực tế

- **Package**: `laravel/breeze` (React version).
- **Guard**: `web` (session-based authentication).
- **Provider**: `users` (dùng Eloquent model `App\Models\User`).
- **Session Driver**: Cấu hình trong `.env` (file, database, redis...).
- **Route file**: `routes/auth.php` được gọi vào trong `routes/web.php`.
- **Backend action/controller/request**: Đặt trong `app/Http/Controllers/Auth` và `app/Http/Requests/Auth`.
- **Frontend page/view**: Nằm tại `resources/js/Pages/Auth/`.
- **Nơi customize logic**: Chủ yếu trong các Controller và các Request Validation tương ứng.

## 4. Register

Trình bày luồng:
```text
GET /register
→ form
→ POST /register
→ validate
→ create normal user
→ hash password
→ login
→ redirect
```
Ngăn chặn client tự gán role: Field `role` được bảo vệ khỏi mass-assignment. Mọi payload truyền field `'role' => 'admin'` đều bị Eloquent bỏ qua, user mới luôn nhận vai trò mặc định (normal user).

## 5. Login

- Đăng nhập bằng `email` và `password`.
- Form Request validation đảm bảo đúng định dạng trước khi check.
- Auth Guard kiểm tra thông tin. Nếu sai, trả về lỗi validation.
- User có trạng thái không kích hoạt (`is_active = false`) sẽ bị từ chối đăng nhập do cơ chế global scope (nếu thiết kế hỗ trợ).
- Regenerate session id sau khi đăng nhập thành công.
- Tích hợp rate limiting ngăn brute-force.

## 6. Logout

Trình bày:
```text
POST /logout
```
Giải thích:
- **Không dùng GET**: GET request có nguy cơ bị dính CSRF (VD: nhúng thẻ `<img>` load link logout khiến nạn nhân tự out).
- Kiểm tra qua logout guard hiện tại.
- Invalidate toàn bộ session cũ.
- Regenerate CSRF token mới.

## 7. Forgot/reset password

- Sử dụng Password Broker mặc định của Laravel. 
- Route cho phép gửi email mang theo token mã hóa ngẫu nhiên.
- Testing bằng cách sử dụng `Notification::fake()`, qua đó ngăn chặn gửi email thật và không yêu cầu cấu hình SMTP, kiểm tra token trên Test Database.

## 8. Auth routes

| Method | URI | Name | Middleware | Chức năng |
| ------ | --- | ---- | ---------- | --------- |
| GET | `/register` | `register` | `guest` | Hiển thị form đăng ký |
| POST | `/register` | - | `guest` | Xử lý tạo tài khoản |
| GET | `/login` | `login` | `guest` | Hiển thị form đăng nhập |
| POST | `/login` | - | `guest` | Xác thực đăng nhập |
| GET | `/forgot-password` | `password.request` | `guest` | Hiển thị form quên mật khẩu |
| POST | `/forgot-password` | `password.email` | `guest` | Xử lý gửi email reset link |
| GET | `/reset-password/{token}` | `password.reset` | `guest` | Form nhập mật khẩu mới |
| POST | `/reset-password` | `password.store` | `guest` | Xử lý reset mật khẩu |
| POST | `/logout` | `logout` | `auth` | Xử lý đăng xuất |

## 9. Route protection

- `tasks.*`: `auth`.
- `users.*`: `auth` → `super_admin`.
- **Guest redirect login**: Người dùng chưa đăng nhập khi truy cập route tasks/users sẽ bị ném thẳng về `/login`.
- **Normal user nhận 403**: Người dùng thông thường vào được `tasks.*` nhưng khi gọi `users.*` sẽ bị middleware `CheckSuperAdmin` ném HTTP 403 (Unauthorized).
- **Super admin được phép đi tiếp**: Vượt cả hai middleware một cách hợp lệ.

## 10. Nơi customize

- **User creation**: `app/Http/Controllers/Auth/RegisteredUserController.php`
- **Password validation**: Các file Request validation trong `app/Http/Requests/Auth` hoặc mặc định bên trong Controller tương ứng.
- **Login validation**: `app/Http/Requests/Auth/LoginRequest.php`
- **Logout**: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- **Auth frontend**: `resources/js/Pages/Auth/`
- **Feature configuration**: `config/auth.php`

## 11. Bảo mật

- **Password hashing**: Sử dụng bcrypt hoặc argon2 theo chuẩn bảo mật.
- **CSRF**: Protect bằng `@csrf` hoặc Inertia/Axios.
- **Session regeneration**: Chặn lỗi Session Fixation mỗi khi đổi trạng thái.
- **POST logout**: Đảm bảo Intent-based actions.
- **Validation**: Strict Data Checking.
- **Login throttling**: Limit access rates.
- **Không nhận role từ request**: Protect Model state.
- **Không commit `.env`**: Tránh lộ App Key và DB.

## 12. Quan hệ với các chapter

- **Chapter 7**: Xây dựng `CheckSuperAdmin` để phục vụ sau authentication.
- **Chapter 8**: Authentication (chương hiện tại).
- **Chapter 9**: Cấu hình localization cho màn hình login/register.
- **Chapter 10**: Frontend template/build và styling chuyên sâu.
- **Chapter 12**: CRUD hoàn chỉnh.
- **Chapter 15**: Dùng Gate/Policy thay middleware phân quyền đơn giản.

## 13. Phạm vi chưa thực hiện

- CRUD.
- Gate/Policy.
- Social login / WorkOS / Team / API token.
- Template Chapter 10.
- Localization Chapter 9.
- Production mail configuration.

## 14. Cách kiểm tra

```bash
php artisan test tests/Feature/Auth
php artisan test
php artisan route:list -vv --except-vendor
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
composer ci:check
npm run build
sunlint --all --input=resources/js
git diff --check
```
