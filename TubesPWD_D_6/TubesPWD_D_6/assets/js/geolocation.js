// 1. Fungsi utama saat tombol "Gunakan Lokasi Saya" diklik
function cariLokasi() {
  var status = document.getElementById('status-lokasi');

  // cek apakah browser mendukung fitur GPS/lokasi
  if (!navigator.geolocation) {
    status.innerHTML = 'Browser Anda tidak mendukung geolocation.';
    return;
  }

  status.innerHTML = 'Mencari lokasi Anda...';

  // minta koordinat lokasi saat ini ke browser
  // untuk memunculkan pop-up izin lokasi di browser ("Izinkan situs ini mengakses lokasi Anda?").
  navigator.geolocation.getCurrentPosition(lokasiBerhasil, lokasiGagal);
  // jika allow akan ke lokasiBerhasil() dan jika block akan ke lokasiGagal()
}

// Fungsi jika lokasi berhasil ditemukan
function lokasiBerhasil(posisi) {
  var form = document.getElementById('form-cari');

  // isi input tersembunyi (hidden) di dalam form dengan koordinat GPS
  form.elements['lat'].value = posisi.coords.latitude; 
  form.elements['lng'].value = posisi.coords.longitude;
  form.elements['urut'].value = 'jarak';

  // Kirim form pencarian secara otomatis
  form.submit();
}

// Fungsi jika pengguna menolak izin lokasi atau GPS mati
function lokasiGagal() {
  var status = document.getElementById('status-lokasi');
  status.innerHTML = 'Lokasi tidak bisa diakses. Izinkan akses lokasi di browser.';
}
