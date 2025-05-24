@extends('adminlte::page')
@section('css')
<style>
  .container {
    margin-top: 50px;
  }
</style>
@endsection
@section('content')

<div class="container py-6">
  <!-- Hàng đầu: 4 ô thống kê -->
  <div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card shadow-sm p-3 text-center">
        <i class="fas fa-users fa-3x text-primary"></i>
        <h5 class="mt-2">Khách hàng</h5>
        <h3 class="font-weight-bold">{{ $totalCustomer }}</h3>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card shadow-sm p-3 text-center">
        <i class="fas fa-book-open fa-3x text-success"></i>
        <h5 class="mt-2">Khóa học</h5>
        <h3 class="font-weight-bold">{{ $totalCourse }}</h3>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card shadow-sm p-3 text-center">
        <i class="fas fa-dollar-sign fa-3x text-warning"></i>
        <h5 class="mt-2">Doanh thu</h5>
        <h3 class="font-weight-bold">{{ App\Helpers\Helper::convertMoney($revenue) }}</h3>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card shadow-sm p-3 text-center">
        <i class="fas fa-shopping-bag fa-3x text-danger"></i>
        <h5 class="mt-2">Đơn hàng</h5>
        <h3 class="font-weight-bold">{{ $totalOrder }}</h3>
      </div>
    </div>
  </div>

  <!-- Hàng thứ hai: 2 biểu đồ -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm p-3">
        <h5 class="font-weight-bold">Doanh thu theo tháng</h5>
        <canvas id="revenue" width="400" height="200"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm p-3">
        <h5 class="font-weight-bold">Lượng xem khóa học theo tháng</h5>
        <canvas id="viewCourses"></canvas>
      </div>
    </div>
  </div>

  <!-- Hàng thứ ba: 2 biểu đồ -->
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
            @foreach($topPurchasedCourses as $course)
            <tr>
              <td class="cell"><span class="truncate">{{ $course->id }}</span></td>
              <td class="cell"><img style="width: 120px" src="{{ $course->thumbnail }}" /></td>
              <td class="cell"><span class="truncate">{{ $course->title }}</span></td>
              <td class="cell">{{ $course->author }}</td>
              <td class="cell"><del>{{ $course->original_price }}</del></td>
              <td class="cell"><strong>{{ $course->original_price }}</strong></td>
              <td class="cell">{{ $course->items_count }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card shadow-sm p-3" style="height: 100%;">
        <h5 class="font-weight-bold">Tỷ lệ hoàn thành khóa học</h5>
        <canvas id="completionChart"></canvas>
      </div>
    </div>
  </div>
</div>
<!-- /.content -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  // Bar chart
  const monthlyRevenue = @json($monthlyRevenue);
  let maxYValue = 50;

  function updateChart(chart) {
    const currentMaxRevenue = Math.max(...monthlyRevenue);
    if (currentMaxRevenue > maxYValue) {
      maxYValue = Math.ceil(currentMaxRevenue / 10) * 10;
      chart.options.scales.y.max = maxYValue;
      chart.update();
    }
  }
  const revenueChart = new Chart(document.getElementById('revenue').getContext('2d'), {
    type: 'bar',
    data: {
      labels: [
        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
        'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
        'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
      ],
      datasets: [{
        label: 'Doanh thu (triệu VNĐ)',
        data: monthlyRevenue, // Dữ liệu doanh thu cho 12 tháng
        backgroundColor: [
          'rgba(255, 99, 132, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(255, 206, 86, 0.2)',
          'rgba(75, 192, 192, 0.2)',
          'rgba(153, 102, 255, 0.2)',
          'rgba(255, 159, 64, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(255, 206, 86, 0.2)',
          'rgba(75, 192, 192, 0.2)',
          'rgba(153, 102, 255, 0.2)',
          'rgba(255, 159, 64, 0.2)'
        ],
        borderColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)'
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
  updateChart(revenueChart);

  // Line chart
  const viewCourses = new Chart(document.getElementById('viewCourses').getContext('2d'), {
    type: 'line',
    data: {
      labels: [
        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
        'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
        'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
      ],
      datasets: [{
        label: 'Tổng lượt xem',
        data: [100, 200, 300, 250, 400, 350, 500, 450, 600, 700, 800, 900], // Dữ liệu lượt xem
        borderColor: 'rgba(75, 192, 192, 1)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderWidth: 2,
        fill: true
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Tổng lượt xem'
          }
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
</script>
@endsection