<?php
namespace App\Models;
use CodeIgniter\Model;
class DataSenzorPohybuModel extends Model
{
    protected $table = 'data_senzor_pohybu';
    protected $primaryKey = 'id';
    protected $allowedFields = ['cas_ulozeni', 'pohyb', 'pocet_osob'];
    public function ziskejDataProGraf(string $interval)
    {
        return $this->select('cas_ulozeni, pohyb, pocet_osob')
            ->where("
                cas_ulozeni BETWEEN 
                (
                    SELECT DATE_SUB(MAX(cas_ulozeni), INTERVAL $interval)
                    FROM {$this->table}
                )
                AND
                (
                    SELECT MAX(cas_ulozeni)
                    FROM {$this->table}
                )
            ")
            ->orderBy('cas_ulozeni', 'ASC')
            ->findAll();
}
}