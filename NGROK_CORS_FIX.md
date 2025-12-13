# 🔧 Fix CORS với Ngrok Free Tier

## ❌ Vấn đề: "blocked:other" khi call API từ ngrok URL

Khi test bằng `curl` thì thành công, nhưng từ browser ở ngrok URL thì bị **"blocked:other"**.

## 🔍 Nguyên nhân

### 1. **Mixed Content (HTTPS → HTTP)**
Browser block requests từ HTTPS (ngrok) đến HTTP (localhost:8081) vì lý do bảo mật.

### 2. **Ngrok Warning Page**
Ngrok free tier có warning page có thể intercept và block CORS requests.

## ✅ Giải pháp

### **Giải pháp 1: Sử dụng ngrok cho cả backend (Khuyến nghị)**

Thay vì gọi `http://localhost:8081/api`, hãy tạo ngrok tunnel cho backend:

```bash
# Terminal 1: Chạy ngrok cho frontend (đã có)
ngrok http 5173

# Terminal 2: Chạy ngrok cho backend
ngrok http 8081
```

Sau đó trong frontend, sử dụng ngrok URL của backend:

```javascript
// Thay vì
const API_URL = 'http://localhost:8081/api';

// Dùng
const API_URL = 'https://your-backend-ngrok-url.ngrok-free.dev/api';
```

**Lợi ích:**
- ✅ Không có mixed content issue (HTTPS → HTTPS)
- ✅ CORS hoạt động bình thường
- ✅ Có thể test từ bất kỳ đâu

### **Giải pháp 2: Bypass ngrok warning page**

Nếu vẫn muốn dùng `localhost:8081`, thêm header `ngrok-skip-browser-warning`:

**Frontend (JavaScript/React/Vue):**

```javascript
// Axios example
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8081/api',
  headers: {
    'ngrok-skip-browser-warning': 'true',
    'Content-Type': 'application/json',
  }
});

// Fetch example
fetch('http://localhost:8081/api/course/list', {
  method: 'GET',
  headers: {
    'ngrok-skip-browser-warning': 'true',
    'Content-Type': 'application/json',
  }
});
```

**Lưu ý:** Giải pháp này vẫn có thể gặp mixed content issue với một số browser.

### **Giải pháp 3: Cấu hình ngrok để bypass warning (Nếu có ngrok account)**

Nếu có ngrok account, có thể cấu hình để bypass warning:

```bash
ngrok http 8081 --domain=your-custom-domain.ngrok-free.dev
```

Hoặc sử dụng ngrok config file:

```yaml
# ngrok.yml
version: "2"
authtoken: YOUR_AUTH_TOKEN
tunnels:
  backend:
    addr: 8081
    proto: http
    inspect: false
    bind_tls: true
```

### **Giải pháp 4: Tạm thời disable mixed content blocking (Chỉ cho dev)**

⚠️ **Chỉ dùng cho development, không dùng production!**

1. Chrome: Mở `chrome://flags/#block-insecure-private-network-requests` → Disable
2. Hoặc chạy Chrome với flag:
   ```bash
   google-chrome --disable-web-security --user-data-dir=/tmp/chrome_dev
   ```

## 🧪 Test CORS

### Test với curl (đã thành công):
```bash
curl -v -X GET \
  -H "Origin: https://marth-venerative-ferally.ngrok-free.dev" \
  -H "Content-Type: application/json" \
  http://localhost:8081/api/course/list
```

### Test từ browser console:
```javascript
fetch('http://localhost:8081/api/course/list', {
  method: 'GET',
  headers: {
    'ngrok-skip-browser-warning': 'true',
    'Content-Type': 'application/json',
  }
})
.then(r => r.json())
.then(console.log)
.catch(console.error);
```

## 📋 Checklist

- [x] CORS config đã đúng (có ngrok URL trong allowed_origins)
- [x] Pattern regex match ngrok URLs
- [x] JWT middleware skip OPTIONS request
- [ ] **Frontend sử dụng ngrok URL cho backend (Giải pháp 1)**
- [ ] Hoặc thêm `ngrok-skip-browser-warning` header (Giải pháp 2)

## 🎯 Khuyến nghị

**Tốt nhất:** Sử dụng **Giải pháp 1** - tạo ngrok tunnel cho cả backend. Điều này:
- ✅ Tránh mixed content issue
- ✅ CORS hoạt động hoàn hảo
- ✅ Có thể test từ bất kỳ đâu
- ✅ Giống production environment hơn

---

**Sau khi áp dụng giải pháp, test lại từ browser và cho biết kết quả!**

