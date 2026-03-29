<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12 text-center">
        <h1 class="display-4 fw-bold text-dark">Smart Home Dashboard</h1>
        <p class="text-muted text-uppercase fw-light" style="letter-spacing: 1.5px;">
            Data odesílaná z desky ESP32
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-primary h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fa-solid fa-temperature-half me-2"></i>Senzor prostředí</h5>
            </div>
            <div class="card-body text-center">
                <?php if ($teplota): ?>
                    <div class="row">
                        <div class="col-6 mb-4">
                            <small class="text-muted d-block text-uppercase fw-bold">Teplota</small>
                            <span class="h2 text-dark fw-bold" id="val-teplota"><?= number_format($teplota['teplota'], 1) ?> °C</span>
                        </div>
                        <div class="col-6 mb-4">
                            <small class="text-muted d-block text-uppercase fw-bold">Vlhkost</small>
                            <span class="h2 text-dark fw-bold" id="val-vlhkost"><?= number_format($teplota['vlhkost'], 1) ?> %</span>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Tlak</small>
                            <span class="h2 text-dark fw-bold" id="val-tlak"><?= number_format($teplota['tlak'], 1) ?> <small class="h6">hPa</small></span>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Nadm. výška</small>
                            <span class="h2 text-dark fw-bold" id="val-vyska"><?= $teplota['nadmorska_vyska'] ?> <small class="h6">m</small></span>
                        </div>

                        <div class="col-12 border-top pt-2 mt-2">
                            <small class="text-muted italic" id="val-cas">Poslední měření: <?= date('d.m.Y H:i:s', strtotime($teplota['cas_ulozeni'])) ?></small>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-danger">Data ze senzoru teploty nejsou k dispozici.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-success h-100">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fa-solid fa-person-walking me-2"></i>Senzor pohybu (mmWave)</h5>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <?php if ($pohyb): ?>
                    <div class="mb-3" id="val-pohyb-kontejner">
                        <?php if ($pohyb['pohyb'] == 1 || $pohyb['pocet_osob'] > 0): ?>
                            <span class="badge bg-danger p-3 fs-5 w-100" id="val-pohyb-badge">POHYB DETEKOVÁN</span>
                        <?php else: ?>
                            <span class="badge bg-secondary p-3 fs-5 w-100" id="val-pohyb-badge">KLID</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <small class="text-muted d-block">Počet osob v místnosti</small>
                        <span class="display-5 fw-bold" id="val-pocet-osob"><?= $pohyb['pocet_osob'] ?></span>
                    </div>
                <?php else: ?>
                    <p class="text-danger">Data ze senzoru pohybu nejsou k dispozici.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function obnovDashboard() {
    fetch('<?= base_url('hlavni/aktualniDataDashboard') ?>')
    .then(response => response.json())
    .then(data => {
        if(data.teplota) {
            document.getElementById('val-teplota').innerText = parseFloat(data.teplota.teplota).toFixed(1) + ' °C';
            document.getElementById('val-vlhkost').innerText = parseFloat(data.teplota.vlhkost).toFixed(1) + ' %';
            document.getElementById('val-tlak').innerHTML = parseFloat(data.teplota.tlak).toFixed(1) + ' <small class="h6">hPa</small>';
            document.getElementById('val-vyska').innerHTML = data.teplota.nadmorska_vyska + ' <small class="h6">m</small>';

            let d = new Date(data.teplota.cas_ulozeni);
            let formattedDate = ("0" + d.getDate()).slice(-2) + "." + ("0" + (d.getMonth() + 1)).slice(-2) + "." + d.getFullYear() + " " + 
                                ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2) + ":" + ("0" + d.getSeconds()).slice(-2);
            document.getElementById('val-cas').innerText = 'Poslední měření: ' + formattedDate;
        }

        if(data.pohyb) {
            const badge = document.getElementById('val-pohyb-badge');
            const aktivita = parseInt(data.pohyb.pohyb || data.pohyb.aktivita || data.pohyb.pocet_osob); 
            
            if (aktivita > 0) {
                badge.className = 'badge bg-danger p-3 fs-5 w-100';
                badge.innerText = 'POHYB DETEKOVÁN';
            } else {
                badge.className = 'badge bg-secondary p-3 fs-5 w-100';
                badge.innerText = 'KLID';
            }
            document.getElementById('val-pocet-osob').innerText = data.pohyb.pocet_osob;
        }
    })
    .catch(error => console.error("Chyba při obnově dashboardu:", error));
}

setInterval(obnovDashboard, 5000);
</script>

<?= $this->endSection() ?>