@extends('admin.layouts.app')

@section('title', 'Absensi In')

@section('content')


    <div class="section-body">
        <div class="row">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Absensi In</h4>
                    </div>
                    <div class="card-body">
                        <video id="video" autoplay playsinline
                            style="width:100%; border-radius:10px; background:#000"></video>
                        <canvas id="canvas" style="display:none;"></canvas>

                        <div class="mt-3">
                            <button class="btn btn-primary" id="btn-absen">Lakukan Absen In</button>
                            <button class="btn btn-light" id="btn-stop" disabled>Stop Kamera</button>
                        </div>

                        <small class="text-muted d-block mt-2">
                            Catatan: kamera di browser butuh HTTPS (kecuali localhost).
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Hasil Absensi In</h4>
                    </div>
                    <div class="card-body">
                        <div id="resultBox" class="alert alert-light">Belum ada hasil.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div class="loading-text">Sedang memproses absensi in...</div>
            <div class="loading-sub">Mohon tunggu sebentar</div>
        </div>
    </div>

    <style>
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        }

        .loading-box {
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.2s ease-in-out;
        }

        .loading-text {
            margin-top: 15px;
            font-weight: 600;
            font-size: 18px;
            color: #333;
        }

        .loading-sub {
            font-size: 13px;
            color: #777;
            margin-top: 5px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #eee;
            border-top: 5px solid #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        let stream = null;
        let busy = false;

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const resultBox = document.getElementById('resultBox');

        async function openCamera() {
            try {
                if (stream) return;

                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: "user"
                    },
                    audio: false
                });

                video.srcObject = stream;

                await new Promise((resolve) => {
                    video.onloadedmetadata = () => resolve();
                });

                await video.play();

                // enable stop button
                const stopBtn = document.getElementById('btn-stop');
                if (stopBtn) stopBtn.disabled = false;

            } catch (err) {
                resultBox.className = "alert alert-danger";
                resultBox.textContent = "Gagal buka kamera: " + (err?.name || err) + " - " + (err?.message || "");
                console.error("openCamera error:", err);
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            video.srcObject = null;
            const stopBtn = document.getElementById('btn-stop');
            if (stopBtn) stopBtn.disabled = true;
        }

        function showLoading() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = "flex";
            document.body.style.overflow = "hidden";

            // cegah user keluar halaman saat proses
            window.onbeforeunload = function() {
                return "Absensi sedang diproses. Yakin ingin keluar?";
            };
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = "none";
            document.body.style.overflow = "auto";
            window.onbeforeunload = null;
        }

        async function hitRecognizeOnce() {
            if (busy) return;
            busy = true;

            const absenBtn = document.getElementById('btn-absen');
            if (absenBtn) absenBtn.disabled = true;

            showLoading();

            try {
                if (!stream) {
                    await openCamera();
                    if (!stream) return;
                }

                const w = video.videoWidth;
                const h = video.videoHeight;

                if (!w || !h) {
                    resultBox.className = "alert alert-warning";
                    resultBox.textContent = "Kamera belum siap. Coba klik lagi...";
                    return;
                }

                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, w, h);

                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.85));
                const fd = new FormData();
                fd.append('image', blob, 'frame.jpg');

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);

                let res;
                let data;

                try {
                    res = await fetch("{{ route('absensi.recognizeIn') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: fd,
                        signal: controller.signal
                    });

                    try {
                        data = await res.json();
                    } catch (e) {
                        iziToast.error({
                            title: "Error",
                            message: "Server mengembalikan response yang tidak valid.",
                            position: "topRight",
                            timeout: 4000
                        });
                        return;
                    }
                } catch (err) {
                    if (err.name === "AbortError") {
                        resultBox.className = "alert alert-danger";
                        resultBox.textContent =
                        "Request recognize timeout. Server tidak merespons lebih dari 10 detik.";

                        iziToast.error({
                            title: "Timeout",
                            message: "Request recognize timeout. Server tidak merespons lebih dari 10 detik.",
                            position: "topRight",
                            timeout: 4000
                        });
                        return;
                    }

                    throw err;
                } finally {
                    clearTimeout(timeoutId);
                }

                if (!res.ok) {
                    iziToast.error({
                        title: "Error",
                        message: data.detail || data.message || "FACE_API_ERROR",
                        position: "topRight",
                        timeout: 4000
                    });
                    return;
                }

                if (!data.ok) {
                    if (data.message === "ALREADY_ATTENDANCE") {
                        iziToast.warning({
                            title: "Warning",
                            message: data.detail,
                            position: "topRight",
                            timeout: 3000
                        });
                        return;
                    }

                    iziToast.error({
                        title: "Error",
                        message: data.detail || data.message || "Terjadi kesalahan",
                        position: "topRight",
                        timeout: 3000
                    });
                    return;
                }

                if (data.ok && data.message === "ABSENSI_SAVED") {
                    const r = (Array.isArray(data.results) && data.results.length) ? data.results[0] : null;

                    iziToast.success({
                        title: "Success",
                        message: r ?
                            `Berhasil melakukan absen: ${r.name || r.id} (${r.percent}%)` :
                            "Berhasil melakukan absen.",
                        position: "topRight",
                        timeout: 3000
                    });
                }

                if (data.ok && Array.isArray(data.results) && data.results.length) {
                    const r = data.results[0];
                    resultBox.className = "alert alert-success";
                    resultBox.innerHTML = `<b>${r.name || r.id}</b><br>ID: ${r.id}<br>Match: ${r.percent}%`;
                } else {
                    resultBox.className = "alert alert-warning";
                    resultBox.textContent = data.detail || data.message || "NO_MATCH";
                }

            } catch (e) {
                resultBox.className = "alert alert-danger";
                resultBox.textContent = "Error: " + (e?.message || e);
                console.error(e);

                iziToast.error({
                    title: "Error",
                    message: e?.message || "Terjadi kesalahan",
                    position: "topRight",
                    timeout: 4000
                });
            } finally {
                busy = false;
                hideLoading();

                if (absenBtn) absenBtn.disabled = false;
            }
        }

        // ✅ auto open kamera saat halaman dibuka
        document.addEventListener('DOMContentLoaded', function() {
            openCamera();
        });

        // ✅ tombol lakukan absensi: baru hit recognize
        document.getElementById('btn-absen').addEventListener('click', hitRecognizeOnce);

        // optional stop button
        const stopBtn = document.getElementById('btn-stop');
        if (stopBtn) stopBtn.addEventListener('click', stopCamera);
    </script>
@endpush
