# Chapter 5: Seeder, Factory and Faker

## 1. Seeder là gì?

* **Mục đích**: Seeder là các class được sử dụng để "gieo" (seed) dữ liệu ban đầu hoặc dữ liệu mẫu vào cơ sở dữ liệu một cách tự động.
* **Vị trí file**: Nằm trong thư mục `database/seeders/`.
* **Method `run()`**: Khi seeder được gọi, phương thức `run()` sẽ được thực thi, chứa các logic chèn dữ liệu vào cơ sở dữ liệu.
* **Cách tạo bằng Artisan**: Dùng lệnh `php artisan make:seeder NameSeeder`.
* **Cách chạy một seeder**: Chạy `php artisan db:seed --class=NameSeeder`.
* **Cách `DatabaseSeeder` gọi seeder con**: Sử dụng `$this->call(NameSeeder::class);` bên trong hàm `run()` của `DatabaseSeeder`.

## 2. Factory là gì?

* **Trạng thái mặc định**: Factory định nghĩa cấu trúc và dữ liệu mặc định để tạo ra một bản ghi hợp lệ cho Eloquent model.
* **Vị trí file**: Nằm trong thư mục `database/factories/`.
* **`definition()`**: Hàm định nghĩa các trường dữ liệu mặc định của model.
* **`make()` và `create()`**: 
  * `make()`: Tạo một instance của model trong bộ nhớ (chưa lưu vào CSDL).
  * `create()`: Tạo instance và lưu trực tiếp xuống CSDL.
* **`count()`**: Dùng để chỉ định số lượng model cần sinh ra (ví dụ `User::factory()->count(10)->create()`).
* **Factory state**: Các hàm bổ sung giúp thay đổi hoặc ghi đè một vài trường dữ liệu mặc định để sinh ra các trạng thái cụ thể của model (ví dụ user admin, user inactive).

## 3. Faker là gì?

* **Tạo dữ liệu giả**: Faker là một thư viện PHP giúp sinh dữ liệu ngẫu nhiên (chữ, số, ngày tháng, tên người...) rất tiện lợi cho việc test.
* **Helper `fake()`**: Laravel cung cấp helper `fake()` để dễ dàng truy cập tới instance của Faker.
* **Ví dụ**:
  * `fake()->name()`: Trả về tên người ngẫu nhiên.
  * `fake()->unique()->safeEmail()`: Trả về email ngẫu nhiên, đảm bảo unique.
  * `fake()->sentence(4)`: Trả về một câu văn gồm 4 từ ngẫu nhiên.
* **Dữ liệu không dùng cho production**: Faker sinh ra dữ liệu vô nghĩa, chỉ dùng để test, không được để lọt vào database production.
* **Độ dài và constraint**: Khi dùng Faker, phải truyền tham số sao cho phù hợp với độ dài cột trong database (ví dụ không vượt quá chuỗi 255 ký tự).

## 4. Seeder và Factory khác nhau thế nào?

| Đặc điểm | Seeder | Factory |
| --- | --- | --- |
| **Mục đích** | Quản lý quá trình chèn dữ liệu vào database. | Định nghĩa "công thức" sinh dữ liệu ngẫu nhiên hoặc cố định cho 1 model. |
| **Tính xác định** | Thường tạo dữ liệu có tính xác định (như tài khoản admin cố định, danh mục hệ thống). | Sinh ra dữ liệu ngẫu nhiên thông qua Faker. |
| **Loại dữ liệu** | Dữ liệu cấu hình, tài khoản hệ thống, hoặc kết nối tới Factory để tạo dữ liệu test. | Dữ liệu giả lập (dummy data). |
| **Trường hợp sử dụng** | Khi cần chạy một quy trình chèn dữ liệu phức tạp. | Khi cần sinh nhanh hàng loạt bản ghi để test model, API. |
| **Ví dụ trong Quicktask** | `SuperAdminSeeder` tạo tài khoản admin đầu tiên. | `UserFactory` sinh ngẫu nhiên user name, email. |

## 5. UserFactory

Code thực tế của `UserFactory` trong Quicktask:
- Sinh ra một regular user đang active theo mặc định.
- Sử dụng `fake()->unique()->safeEmail()` để tạo email không trùng lặp.
- Mật khẩu (password) được thiết lập bằng chuỗi plaintext `'password'`, nhường việc băm hash cho Password Mutator.
- Có các Factory states bổ sung:
  - `admin()`: Trả về state `['role' => 'admin']`.
  - `inactive()`: Trả về state `['is_active' => false]`.
  - `unverified()`: Dùng cho tính năng xác thực email.

*Lưu ý:* Factory state sử dụng cơ chế nội bộ của Eloquent để chèn dữ liệu, nên không yêu cầu phải thêm `role` hoặc `is_active` vào mảng `$fillable` (mass-assignment whitelist) của model.

## 6. TaskFactory

`TaskFactory` sinh dữ liệu cho model `Task`:
- Cột `title` dùng `fake()->sentence(4)`.
- Cột `description` dùng `fake()->optional()->paragraph()`.
- Cột `status` dùng `fake()->randomElement(['pending', 'in_progress', 'completed'])`.
- Cột `user_id` liên kết tự động bằng cú pháp: `'user_id' => User::factory()`.

Cách tạo task kèm user mới tự động:
```php
Task::factory()->create();
```

Cách tạo task và gán cho user đã có sẵn:
```php
Task::factory()
    ->for($user)
    ->create();
```

## 7. Factory relationship

Factory cho phép sinh các quan hệ phức tạp, ví dụ tạo 1 user kèm theo 3 tasks của họ:

```php
User::factory()
    ->has(Task::factory()->count(3))
    ->create();
```

Ở đây, Laravel sẽ sử dụng relationship `tasks()` (đã định nghĩa trong model `User`) để tự động gán chính xác `user_id` của user vừa tạo cho 3 tasks mới được sinh ra.

## 8. SuperAdminSeeder

- **Mục đích**: Đảm bảo luôn tồn tại một tài khoản Super Admin quản trị hệ thống.
- **Config**: Đọc qua `config('quicktask.super_admin.email')` v.v. Không hard-code các credential trực tiếp trong mã nguồn.
- **Global Scope**: Dùng `User::withoutGlobalScope(ActiveUserScope::class)` để đảm bảo tìm được tài khoản dù nó đang bị đánh dấu `is_active = false`.
- **Tái kích hoạt**: Nếu tài khoản đã tồn tại, seeder sẽ đặt lại `is_active = true` và `role = 'admin'` (lưu ý: Quicktask quy định `admin` là role cấp cao nhất).
- **Idempotent**: Không sinh trùng email khi chạy lại nhiều lần.
- **Không ghi đè Password**: Nếu tài khoản đã có, seeder tuyệt đối không thay đổi password (người dùng tự quản lý password).
- **Password Mutator**: Khi tạo mới, seeder gán plaintext password. Mutator từ Chapter 4 sẽ chịu trách nhiệm băm.

## 9. DemoDataSeeder

- **Mục đích**: Sinh dữ liệu dummy phục vụ test.
- Seeder này tạo 10 users, mỗi user đi kèm 3 tasks (sử dụng Factory relationship).
- **Bảo vệ**: Chỉ chạy trong môi trường `local` hoặc `testing` bằng điều kiện `if (app()->isProduction()) return;`. Không bao giờ chạy trên production.
- **Chạy lại**: Nếu chạy nhiều lần, nó sẽ tiếp tục sinh thêm dữ liệu mới.

## 10. Các lệnh thường dùng

- Tạo Factory:
  ```bash
  php artisan make:factory TaskFactory --model=Task
  ```
- Tạo Seeder:
  ```bash
  php artisan make:seeder SuperAdminSeeder
  ```
- Chạy tất cả Seeders mặc định:
  ```bash
  php artisan db:seed
  ```
- Chạy một Seeder cụ thể:
  ```bash
  php artisan db:seed --class=SuperAdminSeeder
  ```
*Lưu ý:* Lệnh `php artisan migrate:fresh --seed` sẽ xóa sạch toàn bộ các bảng và chạy lại từ đầu. Nó chỉ dùng với môi trường local. Không bao giờ chạy lệnh này trên môi trường thật vì nó gây mất toàn bộ dữ liệu!

## 11. Seeder, Factory và production

- **Không dùng Faker trên production**: Faker sinh dữ liệu rác, gây lộn xộn database thật.
- **Không commit credential**: Tất cả mật khẩu/seeder config phải nằm trong `.env`.
- **Không tùy tiện `--force`**: Chạy seeder có tính rủi ro, cần cẩn trọng.
- **Idempotent**: Các seeder triển khai trên production (như cài đặt tài khoản Admin, cấu hình hệ thống ban đầu) phải có khả năng chạy nhiều lần không lỗi và không bị đè dữ liệu.

## 12. Cách kiểm tra Chapter 5

- **Factory Tests**: Kiểm tra các logic sinh model, state `admin()`, `inactive()`.
- **Seeder Tests**: Kiểm tra tính Idempotent của `SuperAdminSeeder` và `DemoDataSeeder`.
- **Chạy toàn bộ test**: `php artisan test tests/Feature/Database/Factories/ModelFactoryTest.php tests/Feature/Database/Seeders`
- **Pint**: Kiểm tra format code `vendor/bin/pint`.
- Các bước khác như build frontend, chạy SunLint và Git checks để đảm bảo code gọn gàng trước khi commit.
