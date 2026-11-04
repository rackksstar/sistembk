@include('errors.layout', [
    'errorCode' => '504',
    'errorTitle' => 'Waktu Permintaan Habis',
    'errorMessage' => 'Sistem membutuhkan waktu terlalu lama untuk memproses permintaan Anda. Silakan coba lagi.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
