@extends('adminlte::page')
@section('css')
<style>
  .container {
    margin-top: 50px;
  }

  #revenueByCategories {
    width: 266px !important;
    /* 2/3 của 400px */
    height: 266px !important;
    /* 2/3 của 400px */
    margin: auto;
  }
</style>
@endsection
@section('content')

<div class="container py-6">
  <div class="text-right mb-3">
    <form action="{{ route('statistics.export') }}" method="POST">
      {{ csrf_field() }}
      <button class="btn btn-primary">
        <i class="fas fa-file-pdf"></i> Xuất PDF
      </button>
    </form>
  </div>

  <!-- Hàng đầu: 4 ô thống kê -->
  <div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card shadow-sm p-3 text-center">
        <i class="fas fa-users fa-3x text-primary"></i>
        <h5 class="mt-2">Khách hàng</h5>
        <h3 class="font-weight-bold">{{ $data['totalCustomer'] }}</h3>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card shadow-sm p-3 text-center">
        <i class="fas fa-book-open fa-3x text-success"></i>
        <h5 class="mt-2">Khóa học</h5>
        <h3 class="font-weight-bold">{{ $data['totalCourse'] }}</h3>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card shadow-sm p-3 text-center">
        <i class="fas fa-dollar-sign fa-3x text-warning"></i>
        <h5 class="mt-2">Doanh thu</h5>
        <h3 class="font-weight-bold">{{ Helper::convertMoney($data['revenue']) }}</h3>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card shadow-sm p-3 text-center">
        <i class="fas fa-shopping-bag fa-3x text-danger"></i>
        <h5 class="mt-2">Đơn hàng</h5>
        <h3 class="font-weight-bold">{{ $data['totalOrder'] }}</h3>
      </div>
    </div>
  </div>

  <!-- Hàng thứ hai: 2 biểu đồ -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm p-3">
        <h5 class="font-weight-bold">Doanh thu theo tháng</h5>
        <canvas id="revenueChart"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm p-3">
        <h5 class="font-weight-bold">Lượng người dùng đăng ký hàng tháng</h5>
        <canvas id="monthlyCustomer"></canvas>
      </div>
    </div>
  </div>

  <!-- Hàng thứ ba: Di chuyển bảng xuống dưới và giữ box Doanh thu theo danh mục khóa học ở vị trí cũ nhưng sang trái -->
  <div class="row">
    <div class="col-md-7">
      <div class="card shadow-sm p-3" style="height: 100%; overflow-y: auto; max-height: 400px;">
        <h5 class="font-weight-bold">Khóa học được mua nhiều nhất</h5>
        <table class="table table-hover" style="border-collapse: separate; border-spacing: 0 8px;">
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
              <td class="cell"><img style="width: 120px" src="{{ $course->thumbnail }}" /></td>
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
    <div class="col-md-5">
      <div class="card shadow-sm p-3" style="height: 100%;">
        <h5 class="font-weight-bold">Doanh thu theo danh mục khóa học</h5>
        <canvas id="revenueByCategories" width="400" height="400"></canvas>
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