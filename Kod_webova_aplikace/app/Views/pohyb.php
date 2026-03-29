<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="text-center mb-5">
    <h1 class="display-4 fw-bold text-success">
        <i class="fa-solid fa-person-walking"></i> Aktivita v místnosti
    </h1>
</div>

<div class="card bg-dark text-white border-success shadow">
    <div class="card-header bg-success text-black d-flex flex-column flex-lg-row justify-content-between align-items-center py-3 gap-3">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2"></i> Pohybová aktivita</h5>

        <div class="d-flex flex-column flex-md-row align-items-center gap-2">

            <div class="input-group input-group-sm" style="max-width: 400px;">
                <input type="date" id="datumOd" class="form-control bg-dark text-white border-dark px-1 text-center" title="Datum od">
                <span class="input-group-text bg-dark text-secondary border-dark">-</span>
                <input type="date" id="datumDo" class="form-control bg-dark text-white border-dark px-1 text-center" title="Datum do">
            </div>
            <div class="btn-group btn-group-sm w-100 justify-content-center" style="min-width: 160px;">
                <button class="btn btn-dark active w-50" id="btn-hour" onclick="nactiTimeline('1 HOUR', this)">Hodina</button>
                <button class="btn btn-dark w-50" id="btn-day" onclick="nactiTimeline('1 DAY', this)">Den</button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="p-3 bg-black rounded">
            <div id="timelineBar" class="position-relative w-100 rounded" style="height: 50px; background-color: #212529; overflow: hidden; border: 1px solid #373b3e;"></div>
            <div class="d-flex justify-content-between mt-2 text-secondary small" id="timelineLabels">
                <span id="labelStart">--:--</span>
                <span id="labelEnd">--:--</span>
            </div>
        </div>
        
        <div id="detailPanel" class="text-center mt-3 p-2 rounded" style="background-color: #1a1e21; min-height: 50px; border: 1px dashed #373b3e;">
            <span class="text-solid"><i class="fa-solid fa-hand-pointer me-1"></i> Klikněte nebo najeďte na úsek pro zobrazení času</span>
        </div>

        <div class="d-flex justify-content-center gap-4 mt-4 small text-secondary">
            <div class="d-flex align-items-center"><div style="width:15px; height:15px; background:#212529; border:1px solid #373b3e; margin-right:8px; border-radius:3px;"></div> Klid</div>
            <div class="d-flex align-items-center"><div style="width:15px; height:15px; background:#198754; margin-right:8px; border-radius:3px;"></div> Přítomnost</div>
        </div>
    </div>
</div>

<style>
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
        document.getElementById('detailPanel').innerHTML = '<span class="text-solid"><i class="fa-solid fa-hand-pointer me-1"></i> Klikněte nebo najeďte na úsek pro zobrazení času</span>';
        nactiTimeline('DATE'); 
    }
}

document.getElementById('datumOd').addEventListener('change', zmenenoDatum);
document.getElementById('datumDo').addEventListener('change', zmenenoDatum);

function nactiTimeline(obdobi, tlacitko = null) {
    if(obdobi !== 'DATE') {
        aktualniObdobi = obdobi;
        datumOd = '';
        datumDo = '';
        document.getElementById('datumOd').value = ziskejDnesniDatum(); 
        document.getElementById('datumDo').value = ziskejDnesniDatum(); 
        if(tlacitko) {
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            tlacitko.classList.add('active');
            document.getElementById('detailPanel').innerHTML = '<span class="text-solid"><i class="fa-solid fa-hand-pointer me-1"></i> Klikněte nebo najeďte na úsek pro zobrazení času</span>';
        }
    }

    let url = `<?= base_url('hlavni/dataPohybTimeline') ?>?obdobi=${aktualniObdobi}`;
    if (datumOd !== '' && datumDo !== '') {
        url = `<?= base_url('hlavni/dataPohybTimeline') ?>?datumOd=${datumOd}&datumDo=${datumDo}`;
    }

    fetch(url)
    .then(res => res.json())
    .then(data => {
        const bar = document.getElementById('timelineBar');
        bar.innerHTML = ''; 

        let firstTime, lastTime;
        if (datumOd !== '' && datumDo !== '') {
            firstTime = new Date(datumOd + 'T00:00:00').getTime();
            lastTime = new Date(datumDo + 'T23:59:59').getTime();
            if (lastTime > new Date().getTime()) lastTime = new Date().getTime(); 
        } else {
            lastTime = new Date().getTime();
            if (aktualniObdobi === '1 HOUR') firstTime = lastTime - (60 * 60 * 1000);
            else if (aktualniObdobi === '1 DAY') firstTime = lastTime - (24 * 60 * 60 * 1000);
        }
        const totalDuration = lastTime - firstTime;

        document.getElementById('labelStart').innerText = new Date(firstTime).toLocaleDateString() + ' ' + new Date(firstTime).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
        
        if (datumOd !== '' && lastTime < new Date().getTime() - 60000) {
            document.getElementById('labelEnd').innerText = new Date(lastTime).toLocaleDateString() + ' ' + new Date(lastTime).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
        } else {
            document.getElementById('labelEnd').innerText = 'Nyní';
        }

        if(!data || data.length === 0) {
            bar.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-secondary">V tomto období není žádný záznam</div>';
            return;
        }

        let rawMovements = [];
        let startPohybu = null;

        data.forEach(zaznam => {
            const cas = new Date(zaznam.cas_ulozeni).getTime();
            const stav = parseInt(zaznam.aktivita);

            if (stav === 1 && startPohybu === null) {
                startPohybu = cas;
            } else if (stav === 0 && startPohybu !== null) {
                rawMovements.push({ start: startPohybu, end: cas });
                startPohybu = null;
            }
        });

        if (startPohybu !== null) rawMovements.push({ start: startPohybu, end: lastTime });

        const rozdilDnu = totalDuration / (1000 * 60 * 60 * 24);
        let MERGE_THRESHOLD = 0;
        
        if (datumOd !== '') {
            if (rozdilDnu <= 1) MERGE_THRESHOLD = 10 * 60 * 1000;         
            else if (rozdilDnu <= 3) MERGE_THRESHOLD = 20 * 60 * 1000;   
            else if (rozdilDnu <= 7) MERGE_THRESHOLD = 40 * 60 * 1000;   
            else MERGE_THRESHOLD = 60 * 60 * 1000;                       
        } else if (aktualniObdobi === '1 DAY') {
            MERGE_THRESHOLD = 5 * 60 * 1000;
        }

        let mergedMovements = [];
        if (rawMovements.length > 0) {
            let currentMerge = rawMovements[0];
            for(let i = 1; i < rawMovements.length; i++) {
                if ((rawMovements[i].start - currentMerge.end) <= MERGE_THRESHOLD) currentMerge.end = rawMovements[i].end;
                else {
                    mergedMovements.push(currentMerge);
                    currentMerge = rawMovements[i];
                }
            }
            mergedMovements.push(currentMerge);
        }

        let finalBlocks = [];
        let currentTime = firstTime;

        mergedMovements.forEach(m => {
            if (m.start > currentTime) finalBlocks.push({ typ: 'klid', start: currentTime, end: m.start });
            finalBlocks.push({ typ: 'pohyb', start: m.start, end: m.end });
            currentTime = m.end;
        });

        if (currentTime < lastTime) finalBlocks.push({ typ: 'klid', start: currentTime, end: lastTime });

        finalBlocks.forEach(blok => {
            vykresliBlok(blok.typ, blok.start, blok.end, firstTime, totalDuration, bar);
        });
    })
    .catch(err => console.error("Chyba AJAXu:", err));
}

function vykresliBlok(typ, start, end, timelineStart, timelineDuration, container) {
    if (timelineDuration <= 0) return;

    const leftPercent = ((start - timelineStart) / timelineDuration) * 100;
    let widthPercent = ((end - start) / timelineDuration) * 100;
    
    if (typ === 'pohyb' && widthPercent < 0.5) widthPercent = 0.5; 

    const block = document.createElement('div');
    block.style.position = 'absolute';
    block.style.left = leftPercent + '%';
    block.style.width = widthPercent + '%';
    block.style.height = '100%';
    block.style.cursor = 'pointer';
    
    if (typ === 'pohyb') {
        block.style.backgroundColor = '#198754';
        block.style.borderRadius = '2px';
    } else {
        block.style.backgroundColor = 'transparent';
    }

    const options = {hour:'2-digit',minute:'2-digit', second:'2-digit'};
    const startTimeStr = new Date(start).toLocaleTimeString([], options);
    const endTimeStr = new Date(end).toLocaleTimeString([], options);
    
    const textStavu = typ === 'pohyb' ? '<i class="fa-solid fa-person-walking me-1"></i> Pohyb zaznamenán' : '<i class="fa-solid fa-bed me-1"></i> Klid (žádný pohyb)';
    const barvaTextu = typ === 'pohyb' ? 'text-success' : 'text-secondary';
    
    const ukazDetail = () => {
        const panel = document.getElementById('detailPanel');
        panel.innerHTML = `<span class="fw-bold ${barvaTextu} d-block mb-1">${textStavu}</span><span class="text-light">Od: ${new Date(start).toLocaleDateString()} ${startTimeStr} &nbsp;&nbsp; Do: ${new Date(end).toLocaleDateString()} ${endTimeStr}</span>`;
        if (typ === 'pohyb') block.style.filter = "brightness(1.3)";
        else block.style.backgroundColor = 'rgba(255,255,255,0.05)';
    };
    
    const skryjDetail = () => {
        if (typ === 'pohyb') block.style.filter = "brightness(1)";
        else block.style.backgroundColor = 'transparent';
    };

    block.title = (typ === 'pohyb' ? 'Pohyb' : 'Klid') + `\nOd: ${new Date(start).toLocaleDateString()} ${startTimeStr}\nDo: ${new Date(end).toLocaleDateString()} ${endTimeStr}`;
    block.onmouseenter = ukazDetail;
    block.onmouseleave = skryjDetail;
    block.ontouchstart = (e) => ukazDetail();

    container.appendChild(block);
}

window.onload = () => {
    document.getElementById('datumOd').value = ziskejDnesniDatum();
    document.getElementById('datumDo').value = ziskejDnesniDatum();
    nactiTimeline('1 HOUR');
    refreshInterval = setInterval(() => {
        if(datumOd === '' && datumDo === '') nactiTimeline(aktualniObdobi);
    }, 5000);
};
</script>

<?= $this->endSection() ?>