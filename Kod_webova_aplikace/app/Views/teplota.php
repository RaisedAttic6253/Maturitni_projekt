<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12 text-center">
        <h1 class="mb-4 fw-bold text-danger"><i class="fa-solid fa-temperature-half"></i> Teplota okolního vzduchu</h1>
    </div>
</div>

<div class="card shadow-sm mt-4 border-danger bg-black">
    <div class="card-header bg-danger d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3">
        <h5 class="mb-0 text-black fw-bold"><i class="fa-solid fa-chart-line me-2"></i></h5>
        
        <div class="d-flex flex-column flex-md-row align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 240px;">
                <input type="date" id="datumOd" class="form-control bg-dark text-white border-dark px-2 text-center" title="Datum od">
                <span class="input-group-text bg-dark text-secondary border-dark d-flex justify-content-center" style="min-width: 30px;">-</span>
                <input type="date" id="datumDo" class="form-control bg-dark text-white border-dark px-2 text-center" title="Datum do">
            </div>

            <div class="btn-group btn-group-sm w-100 justify-content-center" style="min-width: 300px;">
                <button class="btn btn-dark active" id="btn-hour" onclick="nactiData('1 HOUR', this)">Hodina</button>
                <button class="btn btn-dark" id="btn-day" onclick="nactiData('1 DAY', this)">Den</button>
                <button class="btn btn-dark" id="btn-week" onclick="nactiData('1 WEEK', this)">Týden</button>
                <button class="btn btn-dark" id="btn-month" onclick="nactiData('1 MONTH', this)">Měsíc</button>
                <button class="btn btn-dark" id="btn-year" onclick="nactiData('1 YEAR', this)">Rok</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0" style="background-color: #1d1d1d;">
        <canvas id="teplotniGraf" style="min-height: 350px;"></canvas>
    </div>
</div>

<div class="row justify-content-center mt-4"> 
    <div class="col-md-12">
        <div class="card bg-dark text-light mb-3 text-center border-danger">
            <div class="card-body">
                <h6 id="titulekPrumer" class="text-uppercase fw-bold text-danger" style="letter-spacing: 1px;">Průměr (1h)</h6>
                <h3 id="hodnotaPrumer" class="display-6 fw-bold">-- <?= $jednotka ?></h3>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-group .btn-dark { background-color: #212529; border-color: #373b3e; color: #ffffff; }
    .btn-group .btn-dark:hover, .btn-group .btn-dark.active { background-color: #8b0613 !important; border-color: #8b0613 !important; color: #ffffff !important; }
    input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
</style>

<script>
    let aktualniObdobi = '1 HOUR';
    let datumOd = '';
    let datumDo = '';
    let refreshInterval;

    function ziskejDnesniDatum() {
        const today = new Date();
        return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    }

    function zmenenoDatum() {
        datumOd = document.getElementById('datumOd').value;
        datumDo = document.getElementById('datumDo').value;

        if (datumOd !== '' && datumDo === '') {
            datumDo = datumOd;
            document.getElementById('datumDo').value = datumDo;
        }

        if (datumOd !== '' && datumDo !== '') {
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            nactiData('DATE'); 
        }
    }

    document.getElementById('datumOd').addEventListener('change', zmenenoDatum);
    document.getElementById('datumDo').addEventListener('change', zmenenoDatum);

    const ctx = document.getElementById('teplotniGraf').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [], 
            datasets: [{
                label: '<?= ucfirst($velicina) ?> (<?= $jednotka ?>)',
                data: [], 
                borderColor: '<?= $barva ?>', 
                backgroundColor: 'rgba(220, 53, 69, 0.2)', 
                fill: true, tension: 0.3, pointRadius: 2
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: true, labels: { color: '#fff', usePointStyle: true, pointStyle: 'rect', boxWidth: 12 } },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            let label = "Teplota";
                            let hodnota = context.parsed.y;
                            if (hodnota !== null) label += ': ' + parseFloat(hodnota).toFixed(1) + ' <?= $jednotka ?>';
                            return label;
                        }
                    }
                },
                beforeDraw: (chart) => {
                    const {ctx, width, height} = chart;
                    ctx.fillStyle = '#212529';
                    ctx.fillRect(0, 0, width, height);
                }
            },
            scales: {
                y: { grid: { color: '#373b3e' }, ticks: { color: '#adb5bd' } },
                x: { grid: { color: '#373b3e' }, ticks: { color: '#adb5bd' } }
            }
        }
    });

    function nactiData(obdobi, tlacitko = null) {
        if(obdobi !== 'DATE') {
            aktualniObdobi = obdobi;
            datumOd = '';
            datumDo = '';
            document.getElementById('datumOd').value = ziskejDnesniDatum();
            document.getElementById('datumDo').value = ziskejDnesniDatum();
            
            if (tlacitko) {
                document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
                tlacitko.classList.add('active');
            }
        }

        let url = `<?= base_url('hlavni/ziskejDataGrafu') ?>?obdobi=${aktualniObdobi}&typ=<?= $velicina ?>`;
        if (datumOd !== '' && datumDo !== '') {
            url = `<?= base_url('hlavni/ziskejDataGrafu') ?>?datumOd=${datumOd}&datumDo=${datumDo}&typ=<?= $velicina ?>`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data || !data.hodnoty || data.hodnoty.length === 0) {
                    chart.data.labels = []; chart.data.datasets[0].data = [];
                    chart.data.datasets[0].label = `V tomto období nejsou žádná data`;
                    chart.update();
                    document.getElementById('hodnotaPrumer').innerText = '-- <?= $jednotka ?>';
                    return;
                }

                chart.data.labels = data.stity;
                chart.data.datasets[0].data = data.hodnoty;
                const posledniHodnota = parseFloat(data.hodnoty[data.hodnoty.length - 1]).toFixed(1);
                chart.data.datasets[0].label = `Teplota (Poslední hodnota: ${posledniHodnota} <?= $jednotka ?>)`;
                chart.update();

                let titulek = '';
                if (datumOd !== '' && datumDo !== '') {
                    if (datumOd === datumDo) titulek = 'Průměr (' + datumOd.split('-').reverse().join('.') + ')';
                    else titulek = 'Průměr (' + datumOd.split('-').reverse().join('.') + ' - ' + datumDo.split('-').reverse().join('.') + ')';
                } else {
                    const popisky = { '1 HOUR': 'Průměr (1h)', '1 DAY': 'Průměr (24h)', '1 WEEK': 'Průměr (7d)', '1 MONTH': 'Průměr (30d)', '1 YEAR': 'Průměr (rok)' };
                    titulek = popisky[aktualniObdobi];
                }
                
                document.getElementById('titulekPrumer').innerText = titulek;
                document.getElementById('hodnotaPrumer').innerText = parseFloat(data.prumer).toFixed(1) + ' <?= $jednotka ?>';
            })
            .catch(err => console.error('Chyba AJAXu:', err));
    }

    window.onload = () => {
        document.getElementById('datumOd').value = ziskejDnesniDatum();
        document.getElementById('datumDo').value = ziskejDnesniDatum();
        nactiData('1 HOUR');
        
        refreshInterval = setInterval(() => {
            if (datumOd === '' && datumDo === '') nactiData(aktualniObdobi);
        }, 5000);
    };
</script>

<?= $this->endSection() ?>