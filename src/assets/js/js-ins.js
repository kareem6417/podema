 $(document).ready(function() {
            $(".popup-trigger").click(function() {
                var date = $(this).data('date');
                var jenis = $(this).data('jenis');
                var nama_user = $(this).data('nama_user');
                var status = $(this).data('status');
                var merk = $(this).data('merk');
                var serialnumber = $(this).data('serialnumber');
                var lokasi = $(this).data('lokasi');
                var informasi_keluhan = $(this).data('informasi_keluhan');
                var hasil_pemeriksaan = $(this).data('hasil_pemeriksaan');
                var rekomendasi = $(this).data('rekomendasi');

                $("#popup-date").text(date);
                $("#popup-jenis").text(jenis);
                $("#popup-nama_user").text(nama_user);
                $("#popup-status").text(status);
                $("#popup-merk").text(merk);
                $("#popup-serialnumber").text(serialnumber);
                $("#popup-lokasi").text(lokasi);
                $("#popup-informasi_keluhan").html(informasi_keluhan.replace(/\n/g, "<br>"));
                $("#popup-hasil_pemeriksaan").html(hasil_pemeriksaan.replace(/\n/g, "<br>"));
                $("#popup-rekomendasi").html(rekomendasi.replace(/\n/g, "<br>"));

                $(".popup-overlay, .popup-content").fadeIn();
            });

            $(".popup-overlay").click(function() {
                $(".popup-overlay, .popup-content").fadeOut();
            });

            $(".scroll-to-top").click(function() {
                $("html, body").animate({ scrollTop: 0 }, "slow");
            });
        });