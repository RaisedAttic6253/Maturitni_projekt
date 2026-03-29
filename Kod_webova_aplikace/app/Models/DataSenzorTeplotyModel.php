<?php
namespace App\Models;
use CodeIgniter\Model;

class DataSenzorTeplotyModel extends Model
{
    protected $table = 'data_senzor_teploty';
    protected $primaryKey = 'id';
    protected $allowedFields = ['cas_ulozeni', 'teplota', 'tlak', 'vlhkost', 'nadmorska_vyska'];

    private function getRefTime(string $interval) {
        $tz = new \DateTimeZone('Europe/Prague');
        $date = new \DateTime('now', $tz);
        $date->modify('-' . $interval);
        return $date->format('Y-m-d H:i:s');
    }

    public function prumerZDat(string $interval, $datumOd = null, $datumDo = null)
    {
        $builder = $this->select('AVG(teplota) as teplota, AVG(tlak) as tlak, AVG(vlhkost) as vlhkost, AVG(nadmorska_vyska) as nadmorska_vyska');
        
        if ($datumOd && $datumDo) {
            $builder->where('DATE(cas_ulozeni) >=', $datumOd)
                    ->where('DATE(cas_ulozeni) <=', $datumDo);
        } else {
            $builder->where('cas_ulozeni >=', $this->getRefTime($interval));
        }
        return $builder->first();
    }

    public function ziskejDataProGraf(string $interval, $datumOd = null, $datumDo = null)
    {
        $select = 'cas_ulozeni, teplota, vlhkost, tlak, nadmorska_vyska';
        $groupBy = '';

        if ($datumOd && $datumDo) {
            $rozdilDnu = (new \DateTime($datumOd))->diff(new \DateTime($datumDo))->days;
            
            if ($rozdilDnu <= 1) {
                $select = 'MIN(cas_ulozeni) as cas_ulozeni, AVG(teplota) as teplota, AVG(vlhkost) as vlhkost, AVG(tlak) as tlak, AVG(nadmorska_vyska) as nadmorska_vyska';
                $groupBy = 'YEAR(cas_ulozeni), MONTH(cas_ulozeni), DAY(cas_ulozeni), HOUR(cas_ulozeni), MINUTE(cas_ulozeni) DIV 15';
            } elseif ($rozdilDnu <= 14) {
                $select = 'MIN(cas_ulozeni) as cas_ulozeni, AVG(teplota) as teplota, AVG(vlhkost) as vlhkost, AVG(tlak) as tlak, AVG(nadmorska_vyska) as nadmorska_vyska';
                $groupBy = 'YEAR(cas_ulozeni), MONTH(cas_ulozeni), DAY(cas_ulozeni), HOUR(cas_ulozeni) DIV 2';
            } elseif ($rozdilDnu <= 60) {
                $select = 'MIN(cas_ulozeni) as cas_ulozeni, AVG(teplota) as teplota, AVG(vlhkost) as vlhkost, AVG(tlak) as tlak, AVG(nadmorska_vyska) as nadmorska_vyska';
                $groupBy = 'YEAR(cas_ulozeni), MONTH(cas_ulozeni), DAY(cas_ulozeni)';
            } else {
                $select = 'MIN(cas_ulozeni) as cas_ulozeni, AVG(teplota) as teplota, AVG(vlhkost) as vlhkost, AVG(tlak) as tlak, AVG(nadmorska_vyska) as nadmorska_vyska';
                $groupBy = 'YEAR(cas_ulozeni), MONTH(cas_ulozeni), WEEK(cas_ulozeni)';
            }
        } else {
            if ($interval === '1 DAY') {
                $select = 'MIN(cas_ulozeni) as cas_ulozeni, AVG(teplota) as teplota, AVG(vlhkost) as vlhkost, AVG(tlak) as tlak, AVG(nadmorska_vyska) as nadmorska_vyska';
                $groupBy = 'YEAR(cas_ulozeni), MONTH(cas_ulozeni), DAY(cas_ulozeni), HOUR(cas_ulozeni), MINUTE(cas_ulozeni) DIV 15';
            } elseif ($interval === '1 WEEK') {
                $select = 'MIN(cas_ulozeni) as cas_ulozeni, AVG(teplota) as teplota, AVG(vlhkost) as vlhkost, AVG(tlak) as tlak, AVG(nadmorska_vyska) as nadmorska_vyska';
                $groupBy = 'YEAR(cas_ulozeni), MONTH(cas_ulozeni), DAY(cas_ulozeni), HOUR(cas_ulozeni) DIV 2';
            } elseif ($interval === '1 MONTH') {
                $select = 'MIN(cas_ulozeni) as cas_ulozeni, AVG(teplota) as teplota, AVG(vlhkost) as vlhkost, AVG(tlak) as tlak, AVG(nadmorska_vyska) as nadmorska_vyska';
                $groupBy = 'YEAR(cas_ulozeni), MONTH(cas_ulozeni), DAY(cas_ulozeni)';
            } elseif ($interval === '1 YEAR') {
                $select = 'MIN(cas_ulozeni) as cas_ulozeni, AVG(teplota) as teplota, AVG(vlhkost) as vlhkost, AVG(tlak) as tlak, AVG(nadmorska_vyska) as nadmorska_vyska';
                $groupBy = 'YEAR(cas_ulozeni), MONTH(cas_ulozeni), WEEK(cas_ulozeni)';
            }
        }

        $builder = $this->select($select);

        if ($datumOd && $datumDo) {
            $builder->where('DATE(cas_ulozeni) >=', $datumOd)->where('DATE(cas_ulozeni) <=', $datumDo);
        } else {
            $builder->where('cas_ulozeni >=', $this->getRefTime($interval));
        }

        if ($groupBy !== '') $builder->groupBy($groupBy);

        return $builder->orderBy('cas_ulozeni', 'ASC')->findAll();
    }
}