# Chapter 3: Eloquent Relationships

## 1. Relationship trong Eloquent là gì?

Trong Laravel Eloquent, các relationship (mối quan hệ) được định nghĩa dưới dạng các method (phương thức) bên trong các class Model. Nhờ việc định nghĩa dưới dạng phương thức, chúng không chỉ phục vụ việc lấy dữ liệu một cách tiện lợi mà còn đóng vai trò như những Query Builder mạnh mẽ, cho phép bạn tiếp tục nối thêm các phương thức truy vấn (chaining) trước khi thực thi lệnh SQL vào cơ sở dữ liệu.

## 2. Các loại relationship cơ bản

| Quan hệ | Method phía parent | Method phía child/inverse | Ví dụ |
|---------|--------------------|---------------------------|-------|
| One-to-one | `hasOne` | `belongsTo` | User & Phone |
| One-to-many | `hasMany` | `belongsTo` | User & Task |
| Many-to-many | `belongsToMany` | `belongsToMany` | User & Role |
| Has-one-through | `hasOneThrough` | N/A | Mechanic & Car & Owner |
| Has-many-through | `hasManyThrough` | N/A | Country & User & Post |
| Polymorphic one-to-one | `morphOne` | `morphTo` | Image & User/Post |
| Polymorphic one-to-many | `morphMany` | `morphTo` | Comment & Post/Video |
| Polymorphic many-to-many | `morphToMany` | `morphedByMany` | Tag & Post/Video |

## 3. Relationship trong Quicktask

Dựa trên cấu trúc database của dự án Quicktask:
- Một `User` có thể có nhiều `Task` (One-to-Many).
- Mỗi `Task` thuộc về đúng một `User` duy nhất.
- Bảng `tasks` lưu trữ khóa ngoại là `user_id`.

**Code phía Model `User`:**
```php
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Task;

public function tasks(): HasMany
{
    return $this->hasMany(Task::class);
}
```

**Code phía Model `Task`:**
```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

## 4. Relationship method và dynamic property

Dù có chung tên gọi, cách bạn sử dụng relationship sẽ quyết định hành vi của nó:

* **`$user->tasks()` và `$task->user()` (Relationship Method):**
  Trả về một đối tượng Query Builder. Khi dùng phương thức, Laravel chưa gọi DB lấy dữ liệu. Bạn dùng cách này để xây dựng thêm truy vấn (ví dụ: `$user->tasks()->where('status', 'done')->get()`) hoặc tạo mới bản ghi (`create()`).

* **`$user->tasks` và `$task->user` (Dynamic Property):**
  Trả về dữ liệu thực tế (Collection hoặc Object). Khi bạn truy cập dưới dạng thuộc tính, Eloquent sẽ thực thi query ngầm (Lazy Loading) để lấy tất cả tasks của user (hoặc lấy user của task). Kết quả này được cache lại trong biến để dùng cho những lần sau mà không cần query lại CSDL.

## 5. Tạo dữ liệu qua relationship

Bạn có thể lưu task mới bằng cách sử dụng trực tiếp relationship method:

```php
$user->tasks()->create($validated);
```

**Tại sao nên dùng cách này?**
Khi tạo qua `tasks()`, Laravel sẽ tự động nhận diện ID của `$user` hiện tại và điền vào khóa ngoại `user_id` của `Task`. Quá trình này diễn ra an toàn ở mức Framework và bỏ qua bước kiểm duyệt Mass Assignment thông thường. Đó là lý do bạn **không nên** và **không cần** thêm `user_id` vào mảng whitelist (`#[Fillable]`) của model `Task`, giúp phòng chống triệt để các lỗ hổng bảo mật (ví dụ tin tặc cố tình gửi `user_id` giả từ form đăng ký).

## 6. Quan hệ many-to-many

*(Ví dụ lý thuyết giữa `User` và `Role`, chưa áp dụng vào schema Quicktask)*

Quan hệ Many-to-Many yêu cầu một **bảng trung gian (pivot table)** (ví dụ: bảng `role_user`) để lưu trữ các khóa liên kết. Phương thức định nghĩa trên cả hai model là `belongsToMany()`.

Các phương thức quản lý bản ghi liên kết qua bảng trung gian:
* **`attach()`**: Thêm một liên kết mới vào bảng trung gian.
* **`detach()`**: Xóa liên kết trong bảng trung gian, nhưng hoàn toàn **không xóa** các model User hay Role gốc.
* **`toggle()`**: Đảo trạng thái liên kết. Nếu đã có thì xóa (detach), nếu chưa có thì thêm (attach).
* **`sync()`**: Cập nhật lại sao cho giữ đúng danh sách ID được truyền vào. Những liên kết còn lại không nằm trong mảng sẽ bị xóa đi.
* **`syncWithoutDetaching()`**: Thêm các liên kết mới vào mà **không xóa** các liên kết cũ đang tồn tại trong bảng trung gian.

## 7. Lấy dữ liệu bảng trung gian

Trong quan hệ Many-to-Many, để truy cập các cột của bảng trung gian, bạn gọi thuộc tính `pivot`.

Mặc định, Laravel chỉ lấy khóa ngoại của 2 model. Để lấy thêm cột tùy chỉnh hoặc timestamp, bạn cần định nghĩa chúng bằng `withPivot()` và `withTimestamps()`:

```php
return $this->belongsToMany(Role::class)
    ->withPivot('active')
    ->withTimestamps();
```

Khi đó, bạn có thể lấy dữ liệu một cách dễ dàng:
```php
$role->pivot->active;
$role->pivot->created_at;
```

## 8. Quan hệ mở rộng

* **Through relationship** (`hasOneThrough`, `hasManyThrough`): Dùng để đi tắt qua một model trung gian. Hữu ích khi bạn muốn lấy trực tiếp dữ liệu từ model thứ 3 mà không cần viết truy vấn thủ công.
* **Polymorphic relationship** (Đa hình): Cho phép một model thuộc về nhiều model khác nhau trên cùng một cấu trúc bảng (như `morphOne`, `morphMany`).
* **Khi nào nên dùng**: Khi có sự phân cấp đối tượng 3 lớp (through) hoặc có một thành phần có tính tái sử dụng trên nhiều bảng (VD: bảng Comments, Tags).
* **Vì sao Chapter 3 của Quicktask chưa cần triển khai?**: Hệ thống Quicktask hiện tại chỉ có User và Task đơn giản, không có thực thể nào dùng chung cho đa bảng hoặc cấu trúc quan hệ 3 tầng, triển khai lúc này sẽ sinh ra sự dư thừa (over-engineering).

## 9. Cách kiểm tra Chapter 3

Để xác minh đầy đủ tính toàn vẹn và format code của chương này, sử dụng các lệnh sau:

```bash
# Kiểm tra Logic với test
php artisan test

# Fix format code
./vendor/bin/pint

# Biên dịch asset
npm run build

# Kiểm tra thay đổi Git và lỗi khoảng trắng
git diff --check
git status --short
```
