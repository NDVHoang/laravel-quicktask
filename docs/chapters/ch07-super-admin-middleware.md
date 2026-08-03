# Chapter 7: Middleware

## 1. Middleware là gì?

Middleware hoạt động như một lớp trung gian (người gác cổng) kiểm tra, lọc hoặc xử lý các HTTP request trước khi chúng đi sâu vào ứng dụng (tới Controller). 

Luồng xử lý cơ bản:

```text
Request → Middleware → Controller → Response
```

Nếu request thỏa mãn điều kiện của middleware, closure `$next($request)` sẽ được gọi để chuyển request sang bước tiếp theo trong ứng dụng. Nếu không, middleware có thể từ chối request và trả về response lỗi (ví dụ: HTTP 403) ngay lập tức mà không cần gọi `$next`.

## 2. Middleware dùng để làm gì?

Middleware thường được sử dụng cho các tác vụ mang tính chất "cắt ngang" toàn bộ hoặc nhiều phần của ứng dụng. Ví dụ:
- **Authentication**: Kiểm tra người dùng đã đăng nhập chưa.
- **Authorization đơn giản**: Kiểm tra người dùng có quyền thực hiện hành động hay không (chức năng của `CheckSuperAdmin`).
- **CSRF**: Xác thực token bảo mật đối với các form submission.
- **Logging**: Lưu lại thông tin các request được gửi tới server.
- **Rate limiting**: Giới hạn số lượng request trong một khoảng thời gian (ngăn chặn spam).
- **Localization**: Thiết lập ngôn ngữ hiển thị dựa trên thiết lập của người dùng hoặc header request.

Trong Laravel có hai nguồn middleware chính: Middleware có sẵn của framework (như CSRF, ThrottleRequests) và Middleware tự tạo theo nhu cầu nghiệp vụ của dự án.

## 3. Các loại middleware

| Loại | Phạm vi | Cách đăng ký/sử dụng | Ví dụ |
| --- | --- | --- | --- |
| Global Middleware | Mọi request | Application middleware stack (`bootstrap/app.php`) | CORS, maintenance mode |
| Group Middleware | Một nhóm middleware | `web`, `api` hoặc custom group | Session, CSRF |
| Route Middleware | Route được chỉ định | `->middleware()` trên route khai báo | `CheckSuperAdmin` |

Trong Chapter 7, chúng ta triển khai **Route Middleware** để có thể áp dụng chính xác luật lệ cho các resource đặc thù như `users` mà không ảnh hưởng tới `tasks`.

## 4. Tạo middleware

Lệnh tạo middleware mới trong Laravel:

```bash
php artisan make:middleware CheckSuperAdmin
```

Lệnh này sẽ sinh ra một file class PHP tại đường dẫn: `app/Http/Middleware/CheckSuperAdmin.php`. Bạn sẽ đặt logic kiểm tra quyền vào bên trong file này.

## 5. Cấu trúc `CheckSuperAdmin`

Đây là đoạn code thực tế của middleware `CheckSuperAdmin` đã được triển khai:

```php
<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
```

Các bước kiểm tra:
1. Lấy thông tin user hiện tại qua `$request->user()`.
2. Kiểm tra user đó có tồn tại và thuộc kiểu `App\Models\User` hay không (đảm bảo request đã xác thực).
3. Sử dụng strict comparison (`!==`) để kiểm tra thuộc tính `role` có chính xác bằng giá trị literal `'admin'` đang có trong schema hay không.
4. Nếu sai, ném ngay HTTP lỗi `403 Forbidden` thông qua hàm `abort(403)`.
5. Nếu hoàn toàn hợp lệ, gọi `$next($request)` để request được đi tiếp.

## 6. Đăng ký middleware

Từ phiên bản Laravel 11, việc đăng ký middleware alias không còn thực hiện trong `app/Http/Kernel.php` như trước đây nữa. Thay vào đó, tất cả cấu hình được quản lý gọn gàng trong `bootstrap/app.php`. 

Alias của chúng ta được cấu hình như sau:

```php
->withMiddleware(function (Middleware $middleware): void {
    // ...
    $middleware->alias([
        'super_admin' => \App\Http\Middleware\CheckSuperAdmin::class,
    ]);
})
```
Alias thực tế sử dụng: `super_admin`.

## 7. Sử dụng middleware

Sau khi có alias, ta áp dụng middleware vào route trong `routes/web.php`:

```php
Route::resource('users', UserController::class)
    ->middleware('super_admin');
```

Chỉ với một dòng khai báo `->middleware('super_admin')`, Laravel đã tự động áp dụng luật bảo vệ cho toàn bộ bảy action mặc định của route resource `users`.

## 8. Các route được bảo vệ

Bằng việc bọc resource `users`, 7 route sau đây được bảo vệ bởi middleware `super_admin`:

| Method | URI | Name | Action |
| --- | --- | --- | --- |
| GET\|HEAD | `users` | `users.index` | `UserController@index` |
| POST | `users` | `users.store` | `UserController@store` |
| GET\|HEAD | `users/create` | `users.create` | `UserController@create` |
| GET\|HEAD | `users/{user}` | `users.show` | `UserController@show` |
| PUT\|PATCH | `users/{user}` | `users.update` | `UserController@update` |
| DELETE | `users/{user}` | `users.destroy` | `UserController@destroy` |
| GET\|HEAD | `users/{user}/edit` | `users.edit` | `UserController@edit` |

## 9. Các trường hợp truy cập

| Người truy cập | Kết quả Chapter 7 |
| --- | --- |
| Guest | 403 |
| User thường (`role === 'user'`) | 403 |
| Super admin (`role === 'admin'`) | Request đi tiếp |

*Ghi chú: Sau khi Chapter 8 cài đặt thành công authentication, hành vi dành cho Guest (khách chưa đăng nhập) có thể được chuyển sang hành vi redirect tự động tới trang đăng nhập thông qua middleware `auth`.*

## 10. Vì sao không dùng local scope `admin()`?

Trong `User` model, chúng ta đã có sẵn local scope `scopeAdmin()`. Tuy nhiên, trong logic Middleware ta không sử dụng nó để kiểm tra quyền hạn, vì:
- Local scope được sinh ra để tùy chỉnh Eloquent Query Builder (áp dụng các điều kiện `WHERE` khi SELECT records lên).
- Tại thời điểm Middleware hoạt động, `$request->user()` đã trả về sẵn một instance thực tế lấy từ database. Chúng ta không có nhu cầu truy vấn lại database để tạo query, mà chỉ cần xét xem object trong memory này đang giữ thuộc tính `role` gì.
- Kiểm tra trực tiếp `$user->role === 'admin'` là cách tối ưu bộ nhớ và số lượng query nhất.

## 11. Middleware và authentication

- **Authentication (Xác thực)**: Là quá trình xác định xem "Bạn là ai?" thông qua thông tin như username/password (session, token).
- **Middleware Chapter 7 (Authorization - Phân quyền)**: Xác định "Bạn được phép làm gì?". `CheckSuperAdmin` chỉ thực hiện kiểm tra quyền đơn giản sau khi bạn đã được định danh.
- Do chúng ta chưa làm Chapter 8 (thiết lập Starter Kit, UI Login/Logout), hệ thống tạm thời chưa có luồng đăng nhập thực tế. Chapter 8 sẽ xử lý cơ chế Authentication đầy đủ.

## 12. Middleware và Gate/Policy

- **Middleware**: Như `CheckSuperAdmin`, nó là cách áp dụng phân quyền đơn giản, bao quát ở cấp độ Route. Nó trả lời các luật chung (chẳng hạn: Tất cả những ai có chức danh Admin đều được vào route này).
- **Gate / Policy**: Phù hợp cho việc authorization phức tạp, tinh tế hơn tới từng record (chẳng hạn: User có quyền sửa Bài viết này không? Chỉ tác giả bài viết mới được sửa nó).
- Dựa trên guideline, ở Chapter 15 chúng ta sẽ thay thế middleware hiện tại bằng Gate để đáp ứng nhu cầu hệ thống mở rộng.

## 13. Phạm vi Chapter 7

Vì mục đích bài học được cô lập cho tính rành mạch, các tính năng sau **chưa** được thực hiện trong Chapter 7:
- Giao diện và luồng xử lý CRUD cho Users.
- Cài đặt authentication starter kit hay UI chức năng Register/Login/Logout.
- Các Blade views và Blade directives.
- Data validation và Form Request.
- Khai báo Gate và Policy.
- Database transactions.
- Chỉnh sửa migration thay đổi schema role sang kiến trúc phức tạp hơn.

## 14. Cách kiểm tra

Trong thực tế phát triển, bạn có thể kiểm tra hiệu quả của hệ thống thông qua các lệnh dưới đây:

```bash
php artisan test tests/Feature/Http/Middleware/CheckSuperAdminTest.php
php artisan test tests/Feature/Routing/ResourceRouteTest.php
php artisan test
php artisan route:list -vv --path=users --except-vendor
php artisan route:list -vv --path=tasks --except-vendor
./vendor/bin/pint --test
composer ci:check
git diff --check
```
