@include('errors.layout', [
    'errorCode' => '502',
    'errorTitle' => 'Layanan Tidak Merespons',
    'errorMessage' => 'Sistem belum menerima respons yang semestinya. Silakan coba lagi beberapa saat lagi.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
