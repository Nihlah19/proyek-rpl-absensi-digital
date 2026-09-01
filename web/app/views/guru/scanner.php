<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Scanner Absensi Guru</h1>
</div>

<div class="row">
    <div class="col-md-6 offset-md-3 text-center">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Scan QR Code Anda</h5>
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-reader-results" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    function docReady(fn) {
        if (document.readyState === "complete" || document.readyState === "interactive") {
            setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    docReady(function () {
        var lastResult, countResults = 0;
        const resultContainer = document.getElementById('qr-reader-results');
        
        function onScanSuccess(decodedText, decodedResult) {
            if (decodedText !== lastResult) {
                lastResult = decodedText;
                
                // Stop the scanner momentarily
                html5QrcodeScanner.pause();
                
                resultContainer.innerHTML = '<div class="alert alert-info">Memproses absensi...</div>';
                
                // POST to API (MVC route: api/iot/attendance/teacher)
                fetch('<?= BASEURL; ?>/api/iot/attendance/teacher', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ barcode: decodedText })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cek apakah guru tidak punya jadwal hari ini
                        if (data.data && data.data.has_schedule === false) {
                            resultContainer.innerHTML = `<div class="alert alert-warning"><i class="fa fa-info-circle"></i> ${data.message}</div>`;
                        } else {
                            resultContainer.innerHTML = `<div class="alert alert-success"><i class="fa fa-check-circle"></i> ${data.message}</div>`;
                        }
                    } else {
                        resultContainer.innerHTML = `<div class="alert alert-danger"><i class="fa fa-times-circle"></i> ${data.message}</div>`;
                    }
                    setTimeout(() => {
                        lastResult = null;
                        html5QrcodeScanner.resume();
                    }, 3000); // Resume scanner after 3 seconds
                })
                .catch(error => {
                    resultContainer.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan pada server.</div>`;
                    setTimeout(() => {
                        lastResult = null;
                        html5QrcodeScanner.resume();
                    }, 3000);
                });
            }
        }

        var html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    });
</script>
