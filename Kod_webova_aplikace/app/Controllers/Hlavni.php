<?php
namespace App\Controllers;
use App\Models\DataSenzorTeplotyModel;
use App\Models\DataSenzorPohybuModel;

class Hlavni extends BaseController {

    public function index() {
        $teplotaModel = new DataSenzorTeplotyModel();
        $pohybModel = new DataSenzorPohybuModel();

        $data = [
            'title' => 'Hlavní stránka',
            'icon'  => '🏠',
            'teplota' => $teplotaModel->orderBy('cas_ulozeni', 'DESC')->first(),
            'pohyb' => $pohybModel->orderBy('cas_ulozeni', 'DESC')->first()
        ];
        return view('uvod', $data);
    }

    public function teplota() {
        return view('teplota', [
            'title' => 'Teplota okolního vzduchu', 
            'icon' => '🌡️', 
            'velicina' => 'teplota', 
            'jednotka' => '°C', 
            'barva' => '#dc3545'
        ]);
    }

    public function vlhkost() {
        return view('vlhkost', [
            'title' => 'Relativní vlhkost vzduchu', 
            'icon' => '💧', 
            'velicina' => 'vlhkost', 
            'jednotka' => '%', 
            'barva' => '#007bff'
        ]);
    }

    public function tlak() {
        return view('tlak', [
            'title' => 'Absolutní atmosférický tlak', 
            'icon' => '📉', 
            'velicina' => 'tlak', 
            'jednotka' => 'hPa', 
            'barva' => '#ffc107'
        ]);
    }

    public function nadmorska_vyska() {
        return view('nadmorska_vyska', [
            'title' => 'Barometrická nadmořská výška', 
            'icon' => '⛰️', 
            'velicina' => 'nadmorska_vyska', 
            'jednotka' => 'm', 
            'barva' => '#6c757d'
        ]);
    }

    public function pohyb() {
        return view('pohyb', [
            'title' => 'Aktivita v místnosti',
            'icon' => '🚶'
        ]);
    }

    public function dataPohybTimeline()
    {
        $obdobi = $this->request->getGet('obdobi');
        $datumOd = $this->request->getGet('datumOd');
        $datumDo = $this->request->getGet('datumDo');
        $pohybModel = new DataSenzorPohybuModel();

        if ($datumOd && $datumDo) {
            $sql = "
                SELECT cas_ulozeni, pocet_osob as aktivita
                FROM data_senzor_pohybu
                WHERE DATE(cas_ulozeni) >= ? AND DATE(cas_ulozeni) <= ?
                ORDER BY cas_ulozeni ASC
            ";
            $raw = $pohybModel->query($sql, [$datumOd, $datumDo])->getResultArray();
            
        } else {
            $obdobi = $obdobi ?? '1 HOUR';
            $povolenAObdobi = ['1 HOUR', '1 DAY', '1 WEEK', '1 MONTH', '1 YEAR'];
            if (!in_array($obdobi, $povolenAObdobi)) {
                $obdobi = '1 HOUR';
            }

            $tz = new \DateTimeZone('Europe/Prague');
            $date = new \DateTime('now', $tz);
            $date->modify('-' . $obdobi);
            $refTime = $date->format('Y-m-d H:i:s');

            $sql = "
                SELECT cas_ulozeni, pocet_osob as aktivita
                FROM data_senzor_pohybu
                WHERE cas_ulozeni >= ?
                ORDER BY cas_ulozeni ASC
            ";
            $raw = $pohybModel->query($sql, [$refTime])->getResultArray();
        }

        return $this->response->setJSON($raw);
    }

    public function ziskejDataGrafu() {
        $obdobi = $this->request->getGet('obdobi') ?? '1 HOUR';
        $typ = $this->request->getGet('typ') ?? 'teplota'; 
        $datumOd = $this->request->getGet('datumOd'); 
        $datumDo = $this->request->getGet('datumDo');

        $povolenAObdobi = ['1 HOUR', '1 DAY', '1 WEEK', '1 MONTH', '1 YEAR'];
        if (!in_array($obdobi, $povolenAObdobi)) {
            $obdobi = '1 HOUR';
        }

        $model = new DataSenzorTeplotyModel();
        $raw = $model->ziskejDataProGraf($obdobi, $datumOd, $datumDo);
        $prumery = $model->prumerZDat($obdobi, $datumOd, $datumDo);
    
        $sloupec = ($typ === 'vyska') ? 'nadmorska_vyska' : $typ;
        $hodnoty = array_column($raw, $sloupec);
        $aktualniPrumer = $prumery[$sloupec] ?? 0;

        $stity = array_map(function($item) use ($obdobi, $datumOd, $datumDo) {
            $cas = strtotime($item['cas_ulozeni']);
            
            if ($datumOd && $datumDo) {
                $rozdilDnu = (new \DateTime($datumOd))->diff(new \DateTime($datumDo))->days;
                if ($rozdilDnu <= 1) return date('H:i', $cas);
                if ($rozdilDnu <= 14) return date('d.m. H:i', $cas);
                return date('d.m. Y', $cas);
            }

            if ($obdobi == '1 HOUR' || $obdobi == '1 DAY') return date('H:i', $cas);
            if ($obdobi == '1 WEEK') return date('d.m. H:i', $cas);
            return date('d.m.', $cas);
        }, $raw);

        return $this->response->setJSON([
            'stity' => $stity, 
            'hodnoty' => $hodnoty,
            'prumer' => round($aktualniPrumer, 1)
        ]);
    }

    public function aktualniDataDashboard() {
        $teplotaModel = new \App\Models\DataSenzorTeplotyModel();
        $pohybModel = new \App\Models\DataSenzorPohybuModel();
        return $this->response->setJSON([
            'teplota' => $teplotaModel->orderBy('cas_ulozeni', 'DESC')->first(),
            'pohyb' => $pohybModel->orderBy('cas_ulozeni', 'DESC')->first()
        ]);
    }
}