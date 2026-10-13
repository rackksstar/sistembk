@include('errors.layout', [
    'errorCode' => '503',
    'errorTitle' => 'Layanan Sedang Tidak Tersedia',
    'errorMessage' => 'Sistem sedang dalam pemeliharaan atau mengalami kendala sementara. Silakan coba lagi nanti.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
