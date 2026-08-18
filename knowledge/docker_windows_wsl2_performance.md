# Tối ưu hóa hiệu năng Docker trên Windows (WSL2) & CORS Cache

### 1. Tối ưu CORS Preflight Cache
*   **Vấn đề**: Mặc định cấu hình CORS trong `config/cors.php` đặt `'max_age' => 0`. Điều này bắt trình duyệt gửi request `OPTIONS` (Preflight) trên mọi request từ Frontend, làm nhân đôi số lượng request và nhân đôi thời gian phản hồi.
*   **Giải pháp**: Cấu hình lại thành `'max_age' => 86400` (cache trong 24 giờ). Trình duyệt sẽ cache kết quả kiểm tra CORS, giảm thời gian trễ từ FE đi một nửa từ các request tiếp theo.

---

### 2. Khắc phục độ trễ I/O của Docker trên Windows (WSL2)
*   **Vấn đề**: Khi lưu mã nguồn ở phân vùng Windows (ví dụ ổ `D:\`, `C:\`) và mount vào Docker (`volumes: - .:/var/www`), mọi thao tác đọc/ghi file từ container PHP qua phân vùng Windows bị chậm (gây trễ từ 1s - 2s do cơ chế dịch hệ thống file giữa Linux và Windows).
*   **Giải pháp (Khuyên dùng cho Môi trường Phát triển)**:
    1.  Không nên lưu code ở ổ đĩa Windows (`D:\...`).
    2.  Hãy clone hoặc di chuyển toàn bộ thư mục dự án trực tiếp vào trong hệ thống tệp tin gốc của WSL2 (ví dụ thư mục `~/projects/elearning_cms` trong máy ảo Ubuntu).
    3.  Mở thư mục code đó trong VS Code bằng cách sử dụng tiện ích mở rộng **WSL (Remote - WSL)**.
    4.  Chạy `docker-compose` từ bên trong terminal của WSL2.
    5.  **Kết quả**: Tốc độ phản hồi PHP sẽ giảm xuống **dưới 100ms** (nhanh như trên server Linux thật) vì hệ thống tệp tin hoạt động trực tiếp trên Ext4 gốc của Linux mà không bị thông qua cơ chế mount chậm chạp của Windows.
