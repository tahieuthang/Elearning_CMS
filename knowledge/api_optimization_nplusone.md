# Khắc phục lỗi truy vấn N+1 (Database N+1 Query) trong vòng lặp

### Vấn đề
Trong phương thức định dạng dữ liệu khóa học `formatCourseData`, đối với mỗi khóa học trong danh sách kết quả, hệ thống thực hiện một truy vấn SQL độc lập vào bảng `customer_course_progress` để lấy tiến độ học tập của người dùng:
```php
foreach ($courseList as &$course) {
  if ($customerInfo) {
    // Thực hiện 1 câu query cho mỗi khóa học trong vòng lặp
    $courseProgress = \App\Models\CustomerCourseProgress::where('customer_id', $customerInfo->id)
      ->where('course_id', $course->id)
      ->first();
    $course->progress_percent = $courseProgress ? $courseProgress->progress_percent : 0;
  }
}
```
**Hậu quả**: Nếu kết quả tìm kiếm trả về $N$ khóa học, ứng dụng sẽ thực hiện $N$ truy vấn độc lập đến Database. Điều này làm tăng độ trễ mạng và nghẽn kết nối Database.

---

### Giải pháp
1.  **Gộp truy vấn (Batch Query)**: Lấy toàn bộ ID của các khóa học có trong trang kết quả trước khi vào vòng lặp.
2.  **Truy vấn 1 lần duy nhất**: Sử dụng câu lệnh `whereIn` và hàm `pluck` để lấy toàn bộ tiến độ của người dùng cho tất cả các khóa học đó chỉ bằng một truy vấn SQL duy nhất.
3.  **Áp dụng dữ liệu từ RAM**:
```php
// Bước 1: Lấy danh sách ID
$courseIds = [];
foreach ($courseList as $course) {
  $courseIds[] = $course->id;
}

// Bước 2: Query gộp một lần
$progressData = [];
if ($customerInfo && !empty($courseIds)) {
  $progressData = \App\Models\CustomerCourseProgress::where('customer_id', $customerInfo->id)
    ->whereIn('course_id', $courseIds)
    ->pluck('progress_percent', 'course_id')
    ->toArray();
}

// Bước 3: Gán giá trị từ mảng trong bộ nhớ
foreach ($courseList as &$course) {
  $course->progress_percent = $progressData[$course->id] ?? 0;
}
```

---

### Kết quả
*   Số lượng câu truy vấn database giảm từ $N+1$ xuống còn **1 truy vấn** duy nhất.
*   Hiệu năng API tìm kiếm và danh sách hoạt động ổn định và cực kỳ nhanh chóng.
