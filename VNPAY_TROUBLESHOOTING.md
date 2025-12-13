# 🔧 VNPAY Troubleshooting - Lỗi "Sai chữ ký"

## ❌ Vấn đề: Lỗi "Sai chữ ký" khi thanh toán VNPAY

Lỗi này xảy ra khi chữ ký (signature) được tạo không khớp với chữ ký mà VNPAY mong đợi.

---

## 🔍 Nguyên nhân thường gặp

### 1. **HashSecret không đúng** ⚠️ (Nguyên nhân phổ biến nhất)

**Vấn đề:**
- HashSecret trong file `.env` không khớp với HashSecret VNPAY cung cấp
- Copy thiếu ký tự, có khoảng trắng thừa
- Dùng HashSecret của môi trường khác (dev vs production)

**Giải pháp:**
```bash
# Kiểm tra HashSecret trong .env
# Đảm bảo không có khoảng trắng, xuống dòng
VNPAY_PAYMENT_HASHSECRET=your_hash_secret_here

# Clear config cache sau khi sửa .env
php artisan config:clear
```

### 2. **TMNCode không đúng** ⚠️

**Vấn đề:**
- TMNCode trong `.env` không khớp với TMNCode VNPAY cung cấp
- Dùng TMNCode của môi trường khác

**Giải pháp:**
```bash
# Kiểm tra TMNCode trong .env
VNPAY_PAYMENT_TMNCODE=your_tmn_code_here

# Clear config cache
php artisan config:clear
```

### 3. **Môi trường Dev vs Production** 🔴 (Rất quan trọng!)

VNPAY có **2 môi trường** với cấu hình khác nhau:

#### **Môi trường Sandbox (Test/Dev):**
```env
# URL Sandbox
VNPAY_PAYMENT_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html

# TMNCode và HashSecret từ VNPAY Sandbox
VNPAY_PAYMENT_TMNCODE=sandbox_tmn_code
VNPAY_PAYMENT_HASHSECRET=sandbox_hash_secret

# Return URL (phải là URL public, không thể dùng localhost)
VNPAY_PAYMENT_ACADEMY_RETURN_URL=https://your-domain.com/api/payment/result
```

#### **Môi trường Production:**
```env
# URL Production
VNPAY_PAYMENT_URL=https://www.vnpayment.vn/paymentv2/vpcpay.html

# TMNCode và HashSecret từ VNPAY Production
VNPAY_PAYMENT_TMNCODE=production_tmn_code
VNPAY_PAYMENT_HASHSECRET=production_hash_secret

# Return URL (URL production)
VNPAY_PAYMENT_ACADEMY_RETURN_URL=https://your-production-domain.com/api/payment/result
```

**⚠️ Lưu ý quan trọng:**
- **KHÔNG thể dùng localhost** cho Return URL trong môi trường thật
- VNPAY cần gọi callback về server của bạn, localhost không accessible từ internet
- Trong môi trường dev, cần dùng **ngrok** hoặc **tunneling service** để expose localhost

### 4. **Return URL không được đăng ký** ⚠️

**Vấn đề:**
- Return URL chưa được đăng ký với VNPAY
- Return URL không khớp với URL đã đăng ký

**Giải pháp:**
- Đăng nhập vào VNPAY Merchant Portal
- Đăng ký Return URL chính xác (bao gồm cả protocol http/https)
- Đảm bảo Return URL trong `.env` khớp 100% với URL đã đăng ký

### 5. **IP Address không đúng** (Trong môi trường dev)

**Vấn đề:**
- Trong môi trường dev (localhost), `$request->ip()` có thể trả về `127.0.0.1` hoặc `::1`
- VNPAY có thể reject IP này

**Giải pháp:**
```php
// Trong PaymentServices.php, có thể hardcode IP cho dev
$vnp_IpAddr = $request->ip();
// Hoặc dùng IP thật nếu biết
// $vnp_IpAddr = 'your_public_ip';
```

### 6. **Cấu hình Cache chưa clear** ⚠️

**Vấn đề:**
- Sau khi sửa `.env`, config cache chưa được clear
- Laravel vẫn dùng giá trị cũ từ cache

**Giải pháp:**
```bash
# Clear tất cả cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Hoặc trong Docker
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
```

---

## ✅ Cách kiểm tra và khắc phục

### Bước 1: Kiểm tra cấu hình trong `.env`

```bash
# Kiểm tra các biến VNPAY
cat .env | grep VNPAY

# Đảm bảo:
# - Không có khoảng trắng thừa
# - Không có dấu ngoặc kép thừa
# - Giá trị chính xác từ VNPAY
```

### Bước 2: Kiểm tra logs

Code đã có logging, kiểm tra logs để debug:

```bash
# Xem logs Laravel
tail -f storage/logs/laravel.log

# Tìm các dòng:
# "--- START LOG DATA SEND TO VNPAY ---"
# "--- RETURN URL: START LOG DATA RECEIVED FROM VNPAY ---"
```

### Bước 3: So sánh chữ ký

**Khi tạo payment URL:**
- Log `$inputData` và `$hashdata` trước khi tạo hash
- So sánh với cách VNPAY tạo hash

**Khi nhận callback:**
- Log `$inputData` và `$hashData` trước khi verify
- So sánh `$secureHash` (tính toán) vs `$vnp_SecureHash` (từ VNPAY)

### Bước 4: Test với VNPAY Sandbox

1. Đăng ký tài khoản VNPAY Sandbox: https://sandbox.vnpayment.vn/
2. Lấy TMNCode và HashSecret từ Sandbox
3. Cấu hình trong `.env`:
   ```env
   VNPAY_PAYMENT_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
   VNPAY_PAYMENT_TMNCODE=sandbox_tmn_code
   VNPAY_PAYMENT_HASHSECRET=sandbox_hash_secret
   ```
4. Dùng **ngrok** để expose localhost:
   ```bash
   ngrok http 8081
   # Lấy URL: https://xxxx.ngrok.io
   # Cấu hình Return URL: https://xxxx.ngrok.io/api/payment/result
   ```

---

## 🐛 Debug Code

### Kiểm tra code tạo hash

Trong `app/Services/PaymentServices.php`, dòng 112-129:

```php
ksort($inputData);
$query = '';
$i = 0;
$hashdata = '';
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . '=' . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . '=' . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . '?' . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}
```

**Đảm bảo:**
- ✅ `ksort($inputData)` - Sắp xếp theo key
- ✅ `urlencode()` cho cả key và value
- ✅ Format: `key=value&key2=value2` (không có `&` ở đầu)
- ✅ Hash algorithm: `sha512`
- ✅ Hash function: `hash_hmac('sha512', $hashdata, $vnp_HashSecret)`

### Thêm debug logging

Thêm vào code để debug:

```php
// Trước khi tạo hash
Helper::createLogInfo('HashData: ' . $hashdata);
Helper::createLogInfo('HashSecret: ' . $vnp_HashSecret);
Helper::createLogInfo('Calculated Hash: ' . $vnpSecureHash);

// Khi verify
Helper::createLogInfo('Received Hash: ' . $vnp_SecureHash);
Helper::createLogInfo('Calculated Hash: ' . $secureHash);
Helper::createLogInfo('Hash Match: ' . ($secureHash === $vnp_SecureHash ? 'YES' : 'NO'));
```

---

## 📋 Checklist khắc phục

- [ ] Kiểm tra HashSecret trong `.env` - Khớp 100% với VNPAY cung cấp
- [ ] Kiểm tra TMNCode trong `.env` - Khớp 100% với VNPAY cung cấp
- [ ] Kiểm tra môi trường (Sandbox vs Production) - Dùng đúng URL và credentials
- [ ] Kiểm tra Return URL - Đã đăng ký với VNPAY và khớp 100%
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Kiểm tra logs để xem data gửi/nhận
- [ ] Test với VNPAY Sandbox trước
- [ ] Dùng ngrok nếu test localhost

---

## 🔗 Tài liệu tham khảo

- [VNPAY Integration Guide](https://sandbox.vnpayment.vn/apis/)
- [VNPAY Sandbox](https://sandbox.vnpayment.vn/)
- [Ngrok - Expose localhost](https://ngrok.com/)

---

## 💡 Lưu ý quan trọng

1. **Môi trường Dev:**
   - Phải dùng VNPAY Sandbox
   - Phải dùng ngrok hoặc tunneling để expose localhost
   - Return URL phải là URL public (không thể localhost)

2. **Môi trường Production:**
   - Dùng VNPAY Production URL
   - Dùng Production TMNCode và HashSecret
   - Return URL phải là domain production

3. **Bảo mật:**
   - ⚠️ **KHÔNG commit** HashSecret vào Git
   - ⚠️ HashSecret phải được giữ bí mật
   - ✅ Chỉ lưu trong `.env` (đã có trong `.gitignore`)

---

**Nếu vẫn gặp lỗi sau khi kiểm tra tất cả các bước trên, hãy:**
1. Kiểm tra logs chi tiết
2. Liên hệ VNPAY support với thông tin:
   - TMNCode
   - Return URL
   - Sample request data (đã remove HashSecret)
   - Error message


