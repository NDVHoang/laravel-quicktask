# Chapter 4: Accessors, Mutators and Query Scopes

## 1. Accessor là gì?
Accessor là phương thức dùng để biến đổi hoặc định dạng lại giá trị của một thuộc tính (attribute) khi nó được đọc (truy xuất) từ model.  
Trong Laravel hiện hành, Accessor được định nghĩa qua class `Attribute` (chỉ định hàm `get`).

Ví dụ minh họa (chuyển đổi chữ cái đầu thành chữ hoa khi đọc tên):
```php
protected function firstName(): Attribute
{
    return Attribute::make(
        get: fn (string $value) => ucfirst($value),
    );
}
```

## 2. Mutator là gì?
Mutator là phương thức dùng để biến đổi giá trị của một thuộc tính **trước khi** nó được lưu (gán) vào cơ sở dữ liệu.
Cú pháp hiện nay sử dụng `Attribute::make(set: ...)`.
Mutator được tự động gọi mỗi khi bạn gán giá trị trực tiếp qua property (ví dụ: `$user->attribute = 'value'`) hoặc thông qua mass-assignment (như hàm `create`, `update`).

## 3. Password mutator trong Quicktask
Trong dự án Quicktask, chúng ta bảo vệ mật khẩu thông qua Mutator thực tế tại model `User`:
```php
protected function password(): Attribute
{
    return Attribute::make(
        set: fn (string $value): string => Hash::needsRehash($value)
            ? Hash::make($value)
            : $value,
    );
}
```
* **`Hash::make()`**: Thực hiện băm mật khẩu từ chuỗi thường (plaintext) thành một chuỗi mã hóa (hash).
* **`Hash::needsRehash()`**: Kiểm tra xem chuỗi truyền vào đã là mã băm hợp lệ theo chuẩn Bcrypt hay chưa. Điều này chống lại lỗi băm hai lần (double-hashing).
* **Không có getter trả về plaintext**: Password đã bị băm một chiều, hệ thống không có khóa để dịch ngược lại. Việc cố gắng viết một getter lấy plaintext là phi thực tế và vô nghĩa.
* **Nằm trong mảng `$hidden`**: Mật khẩu vẫn bắt buộc phải được khai báo trong `$hidden` (hoặc `#[Hidden]`) để đảm bảo chuỗi băm (hash) không bị lộ ra ngoài khi serialize dữ liệu sang API Response (`toArray`, `toJson`).

## 4. Hashing và Encryption
| Tiêu chí | Hashing (Băm) | Encryption (Mã hóa) |
| --- | --- | --- |
| **Khả năng đảo ngược** | Không thể dịch ngược (One-way). | Có thể giải mã nếu có chìa khóa (Two-way). |
| **Mục đích** | Xác thực đối chiếu nguyên bản một chiều. | Bảo mật dữ liệu nhạy cảm nhưng có nhu cầu đọc lại bản gốc. |
| **Ví dụ** | Mật khẩu (bcrypt, argon2). | Số thẻ tín dụng, CCCD, Token. |
| **Trường hợp sử dụng** | Khi chỉ cần biết "User nhập đúng chuỗi ban đầu không" mà không cần biết lưu trữ chuỗi gốc. | Khi hệ thống/người dùng cần lấy lại nội dung gốc. |

> Mật khẩu bắt buộc phải dùng Hashing (không dùng mã hóa để có thể giải mã), để nếu cơ sở dữ liệu rò rỉ, không ai có quyền năng truy ra mật khẩu thật của người dùng.

## 5. Mutator và hashed cast
Trong Laravel, có thể giải quyết bài toán băm mật khẩu bằng 2 cách phổ biến:
* Đăng ký ép kiểu qua `$casts` (`'password' => 'hashed'`).
* Xây dựng **Mutator** như cách chúng ta vừa làm (`password()`).

Cả hai cơ chế đều có năng lực giải quyết việc băm password. Quicktask chọn viết thủ công một Mutator trong Chapter 4 nhằm mục tiêu đáp ứng bài tập và tìm hiểu sâu về vòng đời của dữ liệu. Chúng ta kiên quyết loại bỏ `'password' => 'hashed'` để không duy trì hai cơ chế trùng trách nhiệm, qua đó giảm độ phức tạp và nguy cơ phát sinh lỗi khó lường.

## 6. Scope là gì?
Query Scope là kỹ thuật cho phép đóng gói, gom nhóm và tái sử dụng các điều kiện (constraints) phổ biến của truy vấn Eloquent, giúp mã nguồn ở tầng Controller hoặc Service trở nên cực kỳ trong sáng và gắn liền với nghiệp vụ.

## 7. Các loại scope
* **Local scope:** Định nghĩa cụ thể trong model và chỉ chạy khi được gọi thủ công (`User::admin()->get()`).
* **Dynamic local scope:** Gọi thủ công nhưng cho phép truyền thêm tham số động vào điều kiện (`User::ofType('admin')->get()`).
* **Global scope:** Tự động chèn ẩn danh vào **tất cả** mọi query liên quan đến Model đó mà không cần gọi hàm nào (`User::all()`).

## 8. Local scope admin
Mã thực tế khai báo trong model `User.php`:
```php
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

#[Scope]
protected function admin(Builder $query): void
{
    $query->where(
        $query->getModel()->qualifyColumn('role'),
        'admin',
    );
}
```
Cách gọi truy vấn:
```php
User::admin()->get();
```

## 9. Global active scope
Với tính năng này, mọi truy vấn tìm User mặc định chỉ được thao tác với những tài khoản đang hoạt động. Quicktask quy định bằng class độc lập `ActiveUserScope`:
```php
public function apply(Builder $builder, Model $model): void
{
    $builder->where($model->qualifyColumn('is_active'), true);
}
```
Scope này được đăng ký thẳng vào class Model bằng Attribute (Laravel 11+):
```php
use App\Models\Scopes\ActiveUserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([ActiveUserScope::class])]
class User extends Authenticatable
{
    // ...
}
```
Nó sẽ ảnh hưởng sâu rộng tới mọi hành vi truy vấn Eloquent (từ `all()`, `find()`, relationship cho tới việc xác thực đăng nhập).

## 10. Bỏ global scope
Nếu nghiệp vụ yêu cầu bắt buộc phải lấy cả những user đã bị khóa (ví dụ: Trang quản trị xem toàn bộ người dùng), ta phải chủ động gỡ bộ lọc:
```php
User::withoutGlobalScope(ActiveUserScope::class)->get();
```

## 11. Mass assignment
Ràng buộc an toàn dữ liệu:
* Các cột nhạy cảm như `role` và `is_active` **không** được thêm vào whitelist (như `#[Fillable]`).
* Scope chỉ làm nhiệm vụ lọc và đọc dữ liệu.
* Khi cần cập nhật các trường được bảo vệ, lập trình viên sử dụng "direct assignment" (gán trực tiếp) sau khi đã kiểm tra quyền một cách cẩn thận:
```php
$user->role = 'admin';
$user->is_active = false;
$user->save();
```

## 12. Cách kiểm tra Chapter 4
Để đảm bảo toàn bộ thiết lập Chapter 4 hoạt động đúng đắn trước khi commit, tiến hành lần lượt các bước sau:
* Chạy test riêng các thành phần mới:
  ```bash
  php artisan test tests/Feature/Models/UserAttributeTest.php tests/Feature/Models/UserScopeTest.php
  ```
* Chạy toàn bộ test suite để đảm bảo không gãy đổ các module cũ:
  ```bash
  php artisan test
  ```
* Kiểm tra và định dạng lại cấu trúc code PHP:
  ```bash
  vendor/bin/pint
  ```
* Dọn dẹp/build assets frontend:
  ```bash
  npm run build
  ```
* Kiểm tra định dạng JS/TS (SunLint) và kiểu tĩnh (PHPStan):
  ```bash
  npm run lint:check
  npm run types:check
  ```
* Xác nhận thay đổi Git (Git checks):
  ```bash
  git diff --check
  git status --short
  ```
