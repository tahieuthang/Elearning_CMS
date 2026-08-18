@extends('adminlte::page')
@section('css')
<style>
  .statistics-page {
    min-height: calc(100vh - 57px);
    margin: -15px;
    padding: 24px 28px 36px;
    background: #f4f7f8;
  }

  .statistics-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
  }

  .statistics-heading {
    margin: 0;
    color: #1f2937;
    font-size: 1.45rem;
    font-weight: 700;
  }

  .statistics-subheading {
    margin: 4px 0 0;
    color: #7b8794;
    font-size: .88rem;
  }

  .statistics-card {
    height: 100%;
    border: 1px solid #e5eaed;
    border-radius: 9px;
    box-shadow: 0 3px 12px rgba(31, 41, 55, .06);
  }

  .statistics-card .card-body {
    padding: 18px;
  }

  .stat-card {
    position: relative;
    min-height: 126px;
    overflow: hidden;
  }

  .stat-card::after {
    position: absolute;
    right: -24px;
    bottom: -35px;
    width: 112px;
    height: 112px;
    border-radius: 50%;
    background: rgba(32, 201, 151, .08);
    content: '';
  }

  .stat-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    font-size: 1.1rem;
  }

  .stat-card__label {
    margin: 15px 0 4px;
    color: #7b8794;
    font-size: .83rem;
    font-weight: 600;
  }

  .stat-card__value {
    margin: 0;
    color: #17202a;
    font-size: 1.35rem;
    font-weight: 700;
  }

  .icon-primary {
    color: #1683d8;
    background: #e5f3ff;
  }

  .icon-success {
    color: #0d9f67;
    background: #e1f8ee;
  }

  .icon-warning {
    color: #e49b00;
    background: #fff4d8;
  }

  .icon-danger {
    color: #dd4b5b;
    background: #ffe5e8;
  }

  .chart-card {
    min-height: 360px;
  }

  .chart-card canvas {
    max-height: 275px;
  }

  .card-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
  }

  .card-title-row h5 {
    margin: 0;
    color: #25313c;
    font-size: 1rem;
    font-weight: 700;
  }

  .card-title-row small {
    display: block;
    margin-top: 4px;
    color: #98a3ad;
    font-size: .76rem;
  }

  .table-card {
    overflow: hidden;
  }

  .table-card .table-responsive {
    max-height: 390px;
    overflow: auto;
  }

  .table-card table {
    min-width: 760px;
  }

  .table-card thead th {
    border-top: 0;
    border-bottom: 1px solid #e5eaed;
    color: #7b8794;
    font-size: .76rem;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .table-card tbody td {
    border-top: 1px solid #f0f2f4;
    color: #3f4a54;
    vertical-align: middle;
  }

  .table-card tbody tr:first-child td {
    border-top: 0;
  }

  .course-thumbnail {
    width: 76px;
    height: 46px;
    border-radius: 5px;
    object-fit: cover;
  }

  .category-chart-card {
    min-height: 100%;
  }

  #revenueByCategories {
    display: block;
    width: min(100%, 280px) !important;
    height: auto !important;
    max-height: 280px;
    margin: 12px auto 0;
  }

  @media (max-width: 767.98px) {
    .statistics-page {
      margin: -15px -10px;
      padding: 18px 14px 28px;
    }

    .statistics-toolbar {
      align-items: flex-start;
      flex-direction: column;
      gap: 12px;
    }
  }
</style>
@endsection
@section('content')

<div class="statistics-page">
  <div class="statistics-toolbar">
    <div>
      <h1 class="statistics-heading">Tổng quan hệ thống</h1>
      <p class="statistics-subheading">Theo dõi hoạt động và hiệu quả kinh doanh của nền tảng.</p>
    </div>
    <form action="{{ route('statistics.export') }}" method="POST">
      {{ csrf_field() }}
      <button class="btn btn-primary px-3">
        <i class="fas fa-file-pdf"></i> Xuất PDF
      </button>
    </form>
  </div>

  <!-- Hàng đầu: 4 ô thống kê -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
      <div class="card statistics-card stat-card">
        <div class="card-body">
          <span class="stat-card__icon icon-primary"><i class="fas fa-users"></i></span>
          <p class="stat-card__label">Khách hàng</p>
          <h3 class="stat-card__value">{{ $data['totalCustomer'] }}</h3>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
      <div class="card statistics-card stat-card">
        <div class="card-body">
          <span class="stat-card__icon icon-success"><i class="fas fa-book-open"></i></span>
          <p class="stat-card__label">Khóa học</p>
          <h3 class="stat-card__value">{{ $data['totalCourse'] }}</h3>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
      <div class="card statistics-card stat-card">
        <div class="card-body">
          <span class="stat-card__icon icon-warning"><i class="fas fa-dollar-sign"></i></span>
          <p class="stat-card__label">Doanh thu</p>
          <h3 class="stat-card__value">{{ Helper::convertMoney($data['revenue']) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
      <div class="card statistics-card stat-card">
        <div class="card-body">
          <span class="stat-card__icon icon-danger"><i class="fas fa-shopping-bag"></i></span>
          <p class="stat-card__label">Đơn hàng</p>
          <h3 class="stat-card__value">{{ $data['totalOrder'] }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Hàng thứ hai: 2 biểu đồ -->
  <div class="row mb-4">
    <div class="col-lg-6 mb-4 mb-lg-0">
      <div class="card statistics-card chart-card">
        <div class="card-body">
          <div class="card-title-row">
            <div>
              <h5>Doanh thu theo tháng</h5>
              <small>Doanh thu trong 12 tháng gần nhất</small>
            </div>
          </div>
        <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card statistics-card chart-card">
        <div class="card-body">
          <div class="card-title-row">
            <div>
              <h5>Lượng người dùng đăng ký hàng tháng</h5>
              <small>Xu hướng đăng ký tài khoản theo tháng</small>
            </div>
          </div>
        <canvas id="monthlyCustomer"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Hàng thứ ba: Di chuyển bảng xuống dưới và giữ box Doanh thu theo danh mục khóa học ở vị trí cũ nhưng sang trái -->
  <div class="row">
    <div class="col-xl-8 mb-4 mb-xl-0">
      <div class="card statistics-card table-card">
        <div class="card-body">
          <div class="card-title-row">
            <div>
              <h5>Khóa học được mua nhiều nhất</h5>
              <small>Các khóa học có số lượt mua cao nhất</small>
            </div>
          </div>
          <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>{{__('course.id')}}</th>
              <th>{{__('course.thumbnail')}}</th>
              <th>{{__('course.title')}}</th>
              <th>{{__('course.author')}}</th>
              <th>{{__('course.original_price')}}</th>
              <th>{{__('course.sale_off_price')}}</th>
              <th>{{__('course.purchases')}}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data['topPurchasedCourses'] as $course)
            <tr>
              <td class="cell"><span class="truncate">{{ $course->id }}</span></td>
              <td class="cell"><img class="course-thumbnail" src="{{ $course->thumbnail }}" /></td>
              <td class="cell"><span class="truncate">{{ $course->title }}</span></td>
              <td class="cell">{{ $course->author }}</td>
              <td class="cell"><del>{{ Helper::convertMoney($course->original_price) }}</del></td>
              <td class="cell"><strong>{{ Helper::convertMoney($course->sale_off_price) }}</strong></td>
              <td class="cell">{{ $course->items_count }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="card statistics-card category-chart-card">
        <div class="card-body">
          <div class="card-title-row">
            <div>
              <h5>Doanh thu theo danh mục khóa học</h5>
              <small>Tỷ trọng doanh thu của từng danh mục</small>
            </div>
          </div>
        <canvas id="revenueByCategories" width="400" height="400"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- /.content -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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
                return value;
              },
              stepSize: 10,
            },
            min: 0,
            max: maxYValue
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
            max: 100
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
  })
</script>
@endsection
