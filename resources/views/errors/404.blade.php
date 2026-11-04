@include('errors.layout', [
    'errorCode' => '404',
    'errorTitle' => 'Halaman Tidak Ditemukan',
    'errorMessage' => 'Halaman yang Anda cari mungkin sudah dipindahkan, dihapus, atau alamatnya salah.',
    'actionUrl' => auth()->check() ? route(auth()->user()->dashboardRoute()) : route('login'),
    'actionLabel' => auth()->check() ? 'Ke Dashboard' : 'Ke Halaman Masuk',
])
