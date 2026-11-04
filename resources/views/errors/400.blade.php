@include('errors.layout', [
    'errorCode' => '400',
    'errorTitle' => 'Permintaan Tidak Valid',
    'errorMessage' => 'Permintaan Anda tidak dapat diproses. Periksa kembali data yang dikirim, lalu coba lagi.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
