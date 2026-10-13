@include('errors.layout', [
    'errorCode' => '429',
    'errorTitle' => 'Terlalu Banyak Permintaan',
    'errorMessage' => 'Anda mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu sebentar, lalu coba lagi.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
