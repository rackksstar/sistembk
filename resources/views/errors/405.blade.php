@include('errors.layout', [
    'errorCode' => '405',
    'errorTitle' => 'Metode Tidak Diizinkan',
    'errorMessage' => 'Aksi ini tidak tersedia untuk alamat yang diminta. Kembali ke halaman sebelumnya atau buka dashboard.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
