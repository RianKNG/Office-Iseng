<!DOCTYPE html>
<html>
<head><title>Offline</title></head>
<body><h1>Tidak Ada Koneksi</h1></body>
</html>
<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
      .then(reg => console.log('SW registered'))
      .catch(err => console.log('SW error:', err));
  }
</script>