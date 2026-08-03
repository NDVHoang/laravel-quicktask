# Chapter 5: Seeder, Factory và Faker

## 1. Giới thiệu

Trong quá trình phát triển ứng dụng, việc có sẵn một tập dữ liệu mẫu (dummy data) là cực kỳ quan trọng để kiểm thử UI và logic. Laravel cung cấp các công cụ mạnh mẽ như **Factories** và **Seeders** kết hợp với thư viện **Faker** để sinh dữ liệu giả một cách dễ dàng và nhất quán.

## 2. Model Factories

Factory là nơi định nghĩa cấu trúc dữ liệu giả cho một Eloquent Model.

### 2.1 UserFactory

`UserFactory` được sinh ra mặc định bởi Laravel nhưng đã được chúng ta tùy chỉnh để phù hợp với yêu cầu thực tế:
- **Cơ chế Password**: Mật khẩu mặc định trong factory được đặt thành chuỗi plaintext `'password'`, sau đó **Password Mutator** trong model `User` sẽ chịu trách nhiệm băm mật khẩu này một cách an toàn.
- **Factory States**: Chúng ta đã định nghĩa thêm các trạng thái:
  - `admin()`: Gán `role = 'admin'` để sinh tài khoản quản trị.
  - `inactive()`: Gán `is_active = false` để sinh tài khoản bị vô hiệu hóa.

### 2.2 TaskFactory

`TaskFactory` được tạo ra để sinh dữ liệu cho model `Task`.
- Sử dụng Faker để sinh nội dung ngẫu nhiên (tiêu đề, mô tả, hạn chót).
- **Quan hệ tự động**: Thuộc tính `user_id` được gán bằng `User::factory()`. Điều này cho phép Laravel tự động tạo một User mới liên kết với Task nếu ta không cung cấp sẵn User khi sinh Task.

## 3. Database Seeders

Seeder là các lớp dùng để gieo dữ liệu (seed) vào cơ sở dữ liệu. 

### 3.1 SuperAdminSeeder

`SuperAdminSeeder` chịu trách nhiệm khởi tạo một tài khoản quản trị viên tối cao (Super Admin) đầu tiên cho hệ thống.
- **Bảo mật và Cấu hình**: Thông tin tài khoản được lấy từ file `config/quicktask.php` (đọc từ `.env`), đảm bảo không hard-code thông tin nhạy cảm.
- **Tính Idempotent**: Seeder được thiết kế để có thể chạy nhiều lần mà không sinh ra lỗi hay ghi đè mật khẩu của quản trị viên đã tồn tại (nếu họ đã thay đổi mật khẩu).
- **Tránh Mass-Assignment**: Seeder gán giá trị cho các thuộc tính `role` và `is_active` bằng toán tử gán trực tiếp thay vì qua mảng `$fillable`, giúp bảo đảm an toàn dữ liệu mà vẫn hoạt động bình thường.

### 3.2 DatabaseSeeder

`DatabaseSeeder` là điểm kích hoạt chính.
- Gọi `SuperAdminSeeder` để luôn đảm bảo có tài khoản quản trị.
- Tự động sinh dữ liệu giả (Demo Data) thông qua Factory **nếu không phải là môi trường Production**:
  ```php
  if (! app()->isProduction()) {
      User::factory(10)->hasTasks(3)->create();
  }
  ```

## 4. Kiểm thử

Các tính năng sinh dữ liệu đã được bảo đảm tính chính xác qua các bài test tự động (Pest) bao gồm kiểm thử `UserFactory`, `TaskFactory` và `SuperAdminSeeder`, đảm bảo cơ sở dữ liệu và quan hệ giữa các bảng hoạt động đúng như mong đợi.
