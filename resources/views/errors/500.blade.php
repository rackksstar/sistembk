@include('errors.layout', [
    'errorCode' => '500',
    'errorTitle' => 'Terjadi Kesalahan',
    'errorMessage' => 'Terjadi kendala pada sistem saat memproses permintaan Anda. Silakan coba lagi nanti.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
