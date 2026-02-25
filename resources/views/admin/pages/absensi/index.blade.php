@extends('admin.layouts.app')

@section('title', 'Absensi')

@section('content')


    <div class="section-body">
        <div class="row">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Absensi</h4>
                    </div>
                    <div class="card-body">
                        <video id="video" autoplay playsinline
                            style="width:100%; border-radius:10px; background:#000"></video>
                        <canvas id="canvas" style="display:none;"></canvas>

                        <div class="mt-3">
                            <button class="btn btn-primary" id="btn-absen">Lakukan Absen</button>
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
                        <h4>Hasil Absensi</h4>
                    </div>
                    <div class="card-body">
                        <div id="resultBox" class="alert alert-light">Belum ada hasil.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

        async function hitRecognizeOnce() {
            if (busy) return;
            busy = true;

            try {
                if (!stream) {
                    await openCamera();
                    if (!stream) {
                        busy = false;
                        return;
                    }
                }

                const w = video.videoWidth;
                const h = video.videoHeight;
                if (!w || !h) {
                    resultBox.className = "alert alert-warning";
                    resultBox.textContent = "Kamera belum siap. Coba klik lagi...";
                    busy = false;
                    return;
                }

                // snapshot
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, w, h);

                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.85));
                const fd = new FormData();
                fd.append('image', blob, 'frame.jpg');

                // hit Laravel -> forward ke FastAPI recognize
                const res = await fetch("{{ route('absensi.recognize') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: fd
                });

                let data;

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

                // kalau HTTP status error (mis 500)
                if (!res.ok) {
                    iziToast.error({
                        title: "Error",
                        message: data.detail || data.message || "FACE_API_ERROR",
                        position: "topRight",
                        timeout: 4000
                    });
                    return; // ⛔ stop di sini
                }

                // sukses absensi tersimpan
                if (!data.ok) {
                    // sudah absen
                    if (data.message === "ALREADY_ATTENDANCE") {
                        iziToast.warning({
                            title: "Warning",
                            message: data.detail,
                            position: "topRight",
                            timeout: 3000
                        });
                        return; // ⛔ stop supaya tidak masuk ke bawah
                    }

                    // error lainnya
                    iziToast.error({
                        title: "Error",
                        message: data.detail || data.message || "Terjadi kesalahan",
                        position: "topRight",
                        timeout: 3000
                    });

                    return; // ⛔ penting
                }

                // sukses absensi tersimpan
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

                // update resultBox
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
                resultBox.textContent = "Error: " + e;
                console.error(e);
            } finally {
                busy = false;
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
