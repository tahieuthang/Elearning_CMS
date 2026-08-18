# Xử lý Race Condition khi áp dụng Coupon/Giảm giá

### Vấn đề
Khi nhiều người dùng cùng áp dụng một mã giảm giá giới hạn số lượt sử dụng (`max_uses`) tại cùng một thời điểm, các luồng xử lý đồng thời có thể gây ra hiện tượng **Race Condition**:
* Cả hai luồng cùng đọc số lượt dùng hiện tại từ DB (ví dụ: $9/10$).
* Cả hai luồng cùng kiểm tra thấy hợp lệ và tiến hành tăng số lượt dùng lên.
* Kết quả: Số lượt dùng tăng vượt ngưỡng cho phép thành $11/10$.

---

### Giải pháp
Hai phương pháp giải quyết ở tầng Database đã được thử nghiệm và so sánh:

#### Cách 1: Pessimistic Locking (Khóa bi quan)
Sử dụng `lockForUpdate()` trong một Database Transaction để giữ khóa dòng dữ liệu từ lúc đọc cho đến khi kết thúc giao dịch:
```php
DB::transaction(function () use ($couponCode) {
    // 1. SELECT và khóa dòng dữ liệu (các request khác muốn ghi phải xếp hàng chờ)
    $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();
    
    // 2. Kiểm tra điều kiện ở PHP
    if ($coupon->uses >= $coupon->max_uses) {
        throw new \Exception('Hết lượt sử dụng');
    }
    
    // 3. Tăng lượt dùng và cập nhật
    $coupon->increment('uses');
});
```
* **Ưu điểm**: Linh hoạt, dễ kiểm tra nhiều logic nghiệp vụ phức tạp ở tầng ứng dụng (PHP/NodeJS) trước khi ghi đè dữ liệu.
* **Nhược điểm**: Giữ khóa lâu, dễ gây nghẽn kết nối (connection pool) hoặc lỗi timeout khi lượng truy cập cực lớn.

#### Cách 2: Atomic Update (Cập nhật nguyên tử)
Đẩy điều kiện kiểm tra từ ứng dụng xuống thẳng mệnh đề `WHERE` của câu lệnh `UPDATE` trên Database:
```php
$updated = Coupon::where('code', $couponCode)
    ->where(function ($q) {
        $q->where('max_uses', 0)
          ->orWhereColumn('uses', '<', 'max_uses');
    })
    ->increment('uses'); // Trả về số dòng bị ảnh hưởng (1 nếu thành công, 0 nếu thất bại)

if ($updated === 0) {
    throw new \Exception('Hết lượt sử dụng hoặc mã không tồn tại.');
}
```
* **Ưu điểm**: Câu lệnh SQL chạy độc lập không cần giữ Transaction lâu. Khóa hàng được giải phóng cực nhanh giúp hiệu năng và khả năng chịu tải (concurrency) tốt hơn rất nhiều.
* **Nhược điểm**: Chỉ phù hợp cho các phép toán tăng/giảm, cập nhật đơn giản.

---

### Bài học phỏng vấn & Thiết kế hệ thống (NodeJS/NestJS)
* **Quy tắc vàng**: *"Check-then-Act"* (Kiểm tra rồi mới Hành động) là nguyên nhân hàng đầu gây ra Race Condition.
* Khi viết backend (kể cả NodeJS với Prisma/TypeORM), luôn ưu tiên **Atomic Update** cho các thao tác tăng/giảm (ví dụ: trừ kho hàng, trừ lượt sử dụng, trừ số dư tài khoản).
* Chỉ chọn **Pessimistic Lock** khi luồng nghiệp vụ phức tạp bắt buộc phải đọc dữ liệu ra tính toán trước khi cập nhật.
