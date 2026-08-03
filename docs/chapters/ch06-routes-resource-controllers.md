# Chapter 6: Route and Resource Controller

## 1. Route là gì?

Route trong Laravel đóng vai trò như một bộ định tuyến, kết nối các URL (đường dẫn web) mà người dùng yêu cầu tới những đoạn mã xử lý cụ thể trong ứng dụng.

Ví dụ một khai báo route cơ bản:
```php
Route::get('/users', [UserController::class, 'index'])
    ->name('users.index');
```

**Phân tích cấu trúc:**
* **Facade `Route`**: Cung cấp giao diện tĩnh tĩnh để tương tác với router của hệ thống.
* **HTTP method (`get`)**: Phương thức HTTP mà route này sẽ phản hồi (GET, POST, v.v.).
* **URI (`/users`)**: Đường dẫn cụ thể trên trình duyệt.
* **Controller (`UserController::class`)**: Lớp điều khiển chứa logic xử lý.
* **Action (`'index'`)**: Tên phương thức bên trong Controller sẽ được thực thi.
* **Route name (`->name('users.index')`)**: Tên định danh duy nhất đặt cho tuyến đường này, giúp dễ dàng tạo URL hoặc chuyển hướng.

## 2. HTTP methods

Trong RESTful API và các ứng dụng web chuẩn, các phương thức HTTP đại diện cho các hành động khác nhau:

* **GET**: Được sử dụng để yêu cầu đọc dữ liệu hoặc hiển thị một form cho người dùng.
* **POST**: Được sử dụng để gửi dữ liệu lên máy chủ nhằm tạo mới một tài nguyên.
* **PUT**: Được sử dụng để cập nhật toàn bộ thông tin của một tài nguyên hiện có.
* **PATCH**: Tương tự PUT nhưng thường dùng để cập nhật một phần của tài nguyên.
* **DELETE**: Được sử dụng để yêu cầu xóa một tài nguyên.

*(Lưu ý: Trong Chapter 6, chúng ta mới chỉ thiết lập định tuyến, chưa thực hiện các thao tác thao tác thay đổi dữ liệu thật trong Database).*

## 3. Named routes

**Named routes** là tính năng cho phép bạn đặt tên duy nhất cho mỗi route thông qua phương thức `->name()`.

* **Mục đích**: Giúp mã nguồn dễ bảo trì hơn, tách biệt việc tham chiếu route khỏi cấu trúc URI tĩnh.
* **Helper `route()`**: Bạn có thể sinh ra URL tới một named route bất kỳ ở mọi nơi (trong view, controller) bằng cách gọi `route('tên.route')`.
* **Lợi ích**: Nếu sau này bạn thay đổi cấu trúc URI (ví dụ từ `/users` sang `/members`), bạn chỉ cần cập nhật ở file route. Các liên kết gọi qua `route('users.index')` sẽ tự động trỏ đúng về URI mới mà không cần phải sửa ở nhiều nơi.

## 4. Route parameters

Đôi khi bạn cần trích xuất các thành phần động từ URI. Chúng được định nghĩa bằng các dấu ngoặc nhọn `{}`.

Ví dụ:
```text
/users/{user}
/tasks/{task}
```

**Nguyên tắc**: Tên của tham số trên route (ví dụ `{user}`) phải khớp chính xác với tên biến được khai báo (type-hint) trong tham số của phương thức Controller (ví dụ `$user`) để tính năng Route Model Binding của Laravel hoạt động chính xác.

## 5. Route groups

Khi bạn có nhiều route dùng chung một tập hợp thuộc tính (như controller, tiền tố URI, middleware...), bạn có thể gộp chúng lại bằng tính năng Route Group.

Dưới đây là ví dụ sử dụng với `TaskController`:
```php
Route::controller(TaskController::class)
    ->prefix('tasks')
    ->name('tasks.')
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        // Các routes khác...
    });
```
* **`controller()`**: Định nghĩa tất cả route trong group sẽ dùng chung Controller này.
* **`prefix()`**: Thêm một tiền tố chung (`/tasks`) vào đầu mọi URI trong group.
* **`name()`**: Thêm một tiền tố tên (`tasks.`) vào đầu mọi tên route trong group.
* **`group()`**: Nhóm các route lại theo một closure.

## 6. Route model binding

Route Model Binding cung cấp cơ chế thuận tiện để tự động tiêm đối tượng model trực tiếp vào controller route của bạn.

Ví dụ:
```php
public function show(User $user)
```

Thay vì phải tự truy vấn dữ liệu (`User::find($id)`), Laravel sẽ tự động tìm kiếm đối tượng `User` có khóa chính khớp với giá trị tham số `{user}` trên URI. Nếu không tìm thấy, hệ thống tự động trả về phản hồi lỗi `404 Not Found`.

**Lưu ý với Global Scope**: Hiện tại, Model `User` trong dự án đang áp dụng `ActiveUserScope` (chỉ truy vấn các User có `is_active = true`). Do đó, implicit model binding mặc định sẽ không tìm thấy những inactive user (và sẽ trả về 404). Ở Chapter 6, chúng ta chấp nhận và không thay đổi hành vi mặc định này.

## 7. Resource Controller

Laravel Resource Controller gộp tất cả các tác vụ CRUD tiêu chuẩn thành một Controller với 7 action quy ước:

| Action    | Công dụng             |
| --------- | --------------------- |
| `index`   | Hiển thị danh sách tài nguyên    |
| `create`  | Hiển thị form tạo tài nguyên mới     |
| `store`   | Lưu tài nguyên mới vào database      |
| `show`    | Hiển thị chi tiết một tài nguyên |
| `edit`    | Hiển thị form chỉnh sửa tài nguyên     |
| `update`  | Cập nhật tài nguyên hiện tại     |
| `destroy` | Xóa tài nguyên khỏi database          |

*(Lưu ý: Bảng trên trình bày trách nhiệm thiết kế. Trong Chapter 6, chúng ta mới chỉ tạo các method stub rỗng mà chưa điền logic xử lý).*

## 8. UserController

`UserController` được khai báo bằng phương pháp tiện lợi của Laravel:
```php
Route::resource('users', UserController::class);
```

Chỉ với một dòng code trên, Laravel tự động sinh ra 7 route với thông số tương đương bảng sau:

| Method      | URI                   | Name          | Action   |
| ----------- | --------------------- | ------------- | -------- |
| `GET\|HEAD` | `users`               | `users.index` | `index`  |
| `POST`      | `users`               | `users.store` | `store`  |
| `GET\|HEAD` | `users/create`        | `users.create`| `create` |
| `GET\|HEAD` | `users/{user}`        | `users.show`  | `show`   |
| `PUT\|PATCH`| `users/{user}`        | `users.update`| `update` |
| `DELETE`    | `users/{user}`        | `users.destroy`| `destroy`|
| `GET\|HEAD` | `users/{user}/edit`   | `users.edit`  | `edit`   |

## 9. TaskController

Thay vì dùng `Route::resource`, `TaskController` được khai báo thủ công để minh họa rõ cách mapping:

```php
Route::controller(TaskController::class)
    ->prefix('tasks')
    ->name('tasks.')
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{task}', 'show')->name('show');
        Route::get('/{task}/edit', 'edit')->name('edit');
        Route::match(['put', 'patch'], '/{task}', 'update')->name('update');
        Route::delete('/{task}', 'destroy')->name('destroy');
    });
```

Bảng 7 route sinh ra hoàn toàn tương đương với Resource Route tiêu chuẩn.

**Lưu ý quan trọng về thứ tự**: Tuyến đường tĩnh `Route::get('/create')` **phải** được định nghĩa trước tuyến đường động `Route::get('/{task}')`. Nếu đặt `/create` bên dưới, Laravel sẽ nhầm lẫn chuỗi `"create"` trên URL là một mã `{task}` động và điều hướng sai tới action `show`.

## 10. Hai cách khai báo route

Bảng so sánh hai kỹ thuật đã được sử dụng:

| Tiêu chí     | `Route::resource` | Route tường minh |
| ------------ | ----------------- | ---------------- |
| Số dòng      | Rất ngắn (1 dòng) | Dài hơn (nhiều dòng) |
| Convention   | Tự động sinh chuẩn| Lập trình viên tự kiểm soát |
| Route name   | Được tự sinh      | Bắt buộc phải tự đặt tên |
| Dễ tùy chỉnh | Ít chi tiết hơn   | Cực kỳ linh hoạt, dễ điều chỉnh từng route |
| Quicktask    | Dành cho `User`   | Dành cho `Task`  |

## 11. Các lệnh thường dùng

Một số lệnh Artisan hữu ích liên quan đến nội dung Chapter này:
```bash
# Tạo bộ khung Resource Controller kết nối sẵn với Model
php artisan make:controller UserController --model=User --resource
php artisan make:controller TaskController --model=Task --resource

# Hiển thị toàn bộ cấu trúc định tuyến trong dự án
php artisan route:list

# Hiển thị cấu trúc định tuyến của một nhóm cụ thể
php artisan route:list --path=users
php artisan route:list --path=tasks
```

## 12. Phạm vi Chapter 6

Chapter 6 tập trung vào cấu trúc định hướng ứng dụng và bộ khung. Các yếu tố sau **chưa được thực hiện** và sẽ được giải quyết trong các chapter tiếp theo:
* Truy vấn, xử lý CRUD vào Database.
* Validation dữ liệu đầu vào.
* Khai báo và sử dụng Form Request.
* Tích hợp Route Middleware bảo vệ các tuyến đường.
* Các quy tắc Authentication và Authorization (Policy).
* Rendering giao diện (Blade views hay Inertia).
* Eager loading giải quyết bài toán N+1 query.

## 13. Cách kiểm tra Chapter 6

Đảm bảo tiến trình được kiểm tra chặt chẽ bằng các công cụ sau:
* **Route Test**: Sử dụng Pest/PHPUnit (`php artisan test tests/Feature/Routing/ResourceRouteTest.php`) để xác thực các URI và method.
* **Toàn bộ Test**: Chạy `php artisan test` nhằm chắc chắn không ảnh hưởng tới các Chapter cũ.
* **`route:list`**: Dùng lệnh artisan để đối soát hiển thị thủ công.
* **Pint**: Định dạng tiêu chuẩn code PHP (`./vendor/bin/pint`).
* **PHPStan** (nếu có): Kiểm tra an toàn bộ phân tích tĩnh.
* **Frontend Build**: Chạy `npm run build` để chắc chắn asset compiler không gặp lỗi.
* **SunLint** (nếu có): Quét code quality.
* **Git Checks**: Xem lại `git diff`, `git status` trước khi tạo commit mới.
