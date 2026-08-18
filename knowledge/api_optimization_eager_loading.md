# Tối ưu hóa Eager Loading và giảm tải Payload cho API danh sách

### Vấn đề
API `/course/list` trước đây mất từ 2-4 giây để phản hồi do nạp trước (eager loading) toàn bộ chương trình học chi tiết (bao gồm cả danh sách video, quiz, toàn bộ câu hỏi trắc nghiệm và đáp án) của tất cả các khóa học trong kết quả tìm kiếm:
```php
$results = Course::select($selectColumns)->with([
  'courseCategories:id,category_name',
  'courseTags:id,tag_name',
  'videos',
  'quizzes.questions.options' // Nạp quá sâu & không cần thiết cho trang list
]);
```
Điều này gây ra:
1.  **MySQL chậm**: Thực hiện quá nhiều truy vấn JOIN phức tạp.
2.  **PHP quá tải**: Khởi tạo (hydrate) hàng ngàn Eloquent Model thừa trong RAM.
3.  **Dung lượng JSON lớn**: Payload trả về chứa Megabytes dữ liệu thừa mà Client/Frontend không hiển thị.

---

### Giải pháp
1.  **Chỉ eager load những gì hiển thị**: Tại phương thức `getCoursesList` trong `CourseServices`, loại bỏ việc load `videos` và `quizzes.questions.options`. Chỉ giữ lại `courseCategories` và `courseTags`.
2.  **Conditional Processing (Xử lý có điều kiện)**: Tại `formatCourseData`, chỉ thực hiện xử lý nghiệp vụ bảo mật thông tin và tạo thuộc tính `curriculum` khi các quan hệ đó thực sự được nạp (sử dụng `$course->relationLoaded('relationName')`).
3.  **Unload Relations**: Sử dụng `$course->unsetRelation('relationName')` để loại bỏ hoàn toàn các relation chưa được nạp ra khỏi JSON trả về nhằm thu nhỏ payload tối đa.

---

### Kết quả
*   Thời gian phản hồi API giảm mạnh (từ vài giây xuống còn vài chục mili-giây).
*   Giảm tải đáng kể dung lượng mạng và RAM máy chủ.
