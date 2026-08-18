<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Báo cáo thống kê</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
    }

    .chart {
      text-align: center;
      margin-top: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      border: 1px solid black;
      padding: 10px;
      text-align: left;
    }

    th {
      background: #f2f2f2;
    }

    @media print {
      .page-break {
        page-break-before: always;
      }
    }
  </style>
</head>

<body>
  <h2 class="chart">Báo cáo thống kê</h2>
  <p>Số lượng người dùng: {{ $data['totalCustomer'] }}</p>
  <p>Số lượng khóa học: {{ $data['totalCourse'] }}</p>
  <p>Tổng doanh thu: {{ Helper::convertMoney($data['revenue']) }}</p>
  <p>Số lượng đơn hàng: {{ $data['totalOrder'] }}</p>
  <!-- Biểu đồ doanh thu -->
  <div class="chart">
    <h3>Doanh thu theo tháng (năm 2025)</h3>
    <table>
      <thead>
        <tr>
          <th>Tháng</th>
          @foreach($data['monthlyRevenue'] as $month => $revenue)
          <th>{{ $month }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>Doanh thu (triệu VNĐ)</th>
          @foreach($data['monthlyRevenue'] as $revenue)
          <td>{{ number_format($revenue, 0, ',', '.') }}</td>
          @endforeach
        </tr>
      </tbody>
    </table>
  </div>

  <div class="page-break"></div>

  <div class="chart">
    <h3>Lượng người dùng đăng ký theo tháng (năm 2025)</h3>
    <table>
      <thead>
        <tr>
          <th>Tháng</th>
          @foreach($data['monthlyCustomer'] as $month => $customer)
          <th>{{ $month }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>Lượng người dùng</th>
          @foreach($data['monthlyCustomer'] as $customer)
          <td>{{ $customer }}</td>
          @endforeach
        </tr>
      </tbody>
    </table>
  </div>

  <div class="page-break"></div>

  <!-- Biểu đồ doanh thu theo danh mục -->
  <div class="chart">
    <h3>Doanh thu theo danh mục</h3>
    <canvas id="revenueByCategories" width="600" height="400"></canvas>
    <table>
      <thead>
        <tr>
          <th>Danh mục</th>
          <th>Doanh thu (triệu VNĐ)</th>
          <th>Phần trăm tổng doanh thu</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data['categories'] as $category)
        <tr>
          <td>{{ $category['category_name'] }}</td>
          <td>{{ $category['total_revenue'] }}</td>
          <td>{{ Helper::roundPercentage($category['percentage']) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="page-break"></div>

  <!-- Bảng Top khóa học bán chạy -->
  <div class="chart">
    <h3>Top 10 khóa học bán chạy</h3>
    <table>
      <thead>
        <tr>
          <th>STT</th>
          <th>{{__('course.title')}}</th>
          <th>{{__('course.author')}}</th>
          <th>{{__('course.original_price')}}</th>
          <th>{{__('course.sale_off_price')}}</th>
          <th>{{__('course.purchases')}}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data['topPurchasedCourses'] as $key => $course)
        <tr>
          <td>{{ $key + 1 }}</td>
          <td>{{ $course->title }}</td>
          <td>{{ $course->author }}</td>
          <td>{{ Helper::convertMoney($course->original_price) }}</td>
          <td>{{ Helper::convertMoney($course->sale_off_price) }}</td>
          <td>{{ $course->items_count }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // // Bar chart // Sơ đồ doanh thu theo tháng
      const monthlyRevenue = @json($data['monthlyRevenue']);
      let maxYValue = 50;

      const revenueChart = new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'bar',
        data: {
          labels: [
            '1', '2', '3', '4',
            '5', '6', '7', '8',
            '9', '10', '11', '12'
          ],
          datasets: [{
            label: 'Doanh thu (triệu VNĐ)',
            data: monthlyRevenue,
            backgroundColor: [
              'rgba(64, 239, 255, 0.2)'
            ],
            borderColor: [
              'rgba(75, 192, 192, 1)',
            ],
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Doanh thu (triệu VNĐ)'
              },
              ticks: {
                callback: function(value) {
                  return value; // Hiển thị giá trị mà không cần thêm "triệu"
                },
                stepSize: 10,
              },
              min: 0,
              max: maxYValue // Giá trị tối đa ban đầu
            },
            x: {
              title: {
                display: true,
                text: 'Tháng'
              }
            }
          }
        }
      });
      // updateChart(revenueChart);

      // Sơ đồ lượng ng dùng đăng ký tài khoản
      const monthlyCustomer = @json($data['monthlyCustomer']);

      const customer = new Chart(document.getElementById('monthlyCustomer').getContext('2d'), {
        type: 'bar',
        data: {
          labels: [
            '1', '2', '3', '4',
            '5', '6', '7', '8',
            '9', '10', '11', '12'
          ],
          datasets: [{
            label: 'Lượng người dùng đăng ký',
            data: monthlyCustomer,
            backgroundColor: [
              'rgba(22, 226, 56, 0.2)'
            ],
            borderColor: [
              'rgba(64, 255, 172, 0.2)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Tài khoản'
              },
              min: 0,
              max: 100 // Giá trị tối đa ban đầu
            },
            x: {
              title: {
                display: true,
                text: 'Tháng'
              }
            }
          }
        }
      });

      // Line chart Sơ đồ doanh thu theo danh mục khóa học
      const categories = @json($data['categories']);
      console.log(categories)
      const totalRevenue = categories.reduce((sum, category) => sum + category.total_revenue, 0); // Tính tổng doanh thu
      console.log(totalRevenue)
      const labels = categories.map(category => category.category_name);
      const data = categories.map(category => category.total_revenue);
      console.log(labels, data);

      const revenueByCategoriesChart = new Chart(document.getElementById('revenueByCategories').getContext('2d'), {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [{
            label: 'Tổng doanh thu theo danh mục khóa học',
            data: data,
            backgroundColor: [
              'rgba(75, 192, 192, 0.2)',
              'rgba(255, 99, 132, 0.2)',
              'rgba(255, 206, 86, 0.2)',
              'rgba(54, 162, 235, 0.2)',
              'rgba(153, 102, 255, 0.2)',
              'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
              'rgba(75, 192, 192, 1)',
              'rgba(255, 99, 132, 1)',
              'rgba(255, 206, 86, 1)',
              'rgba(54, 162, 235, 1)',
              'rgba(153, 102, 255, 1)',
              'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'top',
            },
            tooltip: {
              callbacks: {
                label: function(tooltipItem) {
                  const category = categories[tooltipItem.dataIndex];
                  const percentage = ((category.total_revenue / totalRevenue) * 100).toFixed(2);
                  return `${tooltipItem.label}: ${category.total_revenue} triệu VNĐ (${percentage}%)`;
                }
              }
            }
          }
        }
      });

      function convertCanvasToImage(canvasId, imageId) {
        let canvas = document.getElementById(canvasId);
        let image = document.getElementById(imageId);
        image.src = canvas.toDataURL('image/png'); // Chuyển canvas thành ảnh
        image.style.display = 'block'; // Hiển thị ảnh
        canvas.style.display = 'none'; // Ẩn canvas
      }

      // Khi nhấn in, tự động chuyển đổi
      window.onbeforeprint = function() {
        convertCanvasToImage('revenueChart', 'revenueChartImg');
        convertCanvasToImage('monthlyCustomer', 'monthlyCustomerImg');
        convertCanvasToImage('revenueByCategories', 'revenueByCategoriesImg');
      };

      // Khi in xong, hiển thị lại canvas nếu cần
      window.onafterprint = function() {
        document.querySelectorAll('canvas').forEach(canvas => canvas.style.display = 'block');
        document.querySelectorAll('img').forEach(img => img.style.display = 'none');
      };
    })
  </script>
</body>

</html>