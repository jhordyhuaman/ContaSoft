<?php

require_model('articulo.php');
require_model('almacen.php');


class planilla extends fs_model {

   public $nombre;
   public $apellido;
   public $remu_basica;
   public $bonifi_basica;
   public $bonifi_espesifica;
   public $total_ganado;
   public $desc_afp;
   public $desc_snp;
   public $desc_adelanto;
   public $desc_otros;
   public $desc_total;
   public $neto_pagar;
   public $aporte_esalud;
   public $aporte_sctr;
   public $aporte_total;
   public $fecha_pago;
   public $idplanilla;
 

   public function __construct($s = FALSE) {
       
      parent::__construct('planilla', 'modulos/core/');
      if ($s) {
         $this->idplanilla = $this->intval( $s['idplanilla'] );
         $this->nombre =$s['nombre'];
         $this->apellido =$s['apellido'];
         $this->remu_basica =floatval($s['remu_basica']);
         $this->bonifi_basica =floatval($s['bonifi_basica']);
         $this->bonifi_espesifica =floatval($s['bonifi_espesifica']);
         $this->total_ganado =floatval($s['total_ganado']);
         $this->desc_afp =floatval($s['desc_afp']);
         $this->desc_snp =floatval($s['desc_snp']);
         $this->desc_adelanto =floatval($s['desc_adelanto']);
         $this->desc_otros =floatval($s['desc_otros']);
         $this->desc_total =floatval($s['desc_total']);
         $this->neto_pagar =floatval($s['neto_pagar']);
         $this->aporte_esalud =floatval($s['aporte_esalud']);
         $this->aporte_sctr =floatval($s['aporte_sctr']);
         $this->aporte_total =floatval($s['aporte_total']);
         $this->fecha_pago =$s['fecha_pago'];
      } else {
         $this->idplanilla = NULL;
         $this->nombre = NULL;
         $this->apellido = NULL;
         $this->remu_basica =0;
         $this->bonifi_basica =0;
         $this->bonifi_espesifica =0;
         $this->total_ganado =0;
         $this->desc_afp =0;
         $this->desc_snp =0;
         $this->desc_adelanto =0;
         $this->desc_otros =0;
         $this->desc_total =0;
         $this->neto_pagar =0;
         $this->aporte_esalud =0;
         $this->aporte_sctr =0;
         $this->aporte_total =0;
         $this->fecha_pago = NULL;
      }
    
      $this->cron = false;
   }

   public function install() {
      return '';
   }

   public function exists() {
      $sql = "SELECT idplanilla FROM " . $this->table_name . " WHERE "
              . " idplanilla = " . $this->var2str($this->idplanilla).";";
      $data = $this->db->select($sql);
      if ($data) {
         return TRUE;
      } else {
         return FALSE;
      }
   }

    public function listar(){
        $sql = "SELECT * FROM". $this->table_name . ";";
        $data = $this->db->select($sql);
        return $data;
    }

   public function save() {
      if ($this->exists()) {
         $sql = "UPDATE " . $this->table_name . " SET "
               . "  nombre = " .  $this->var2str($this->nombre)
               . ", apellido = " . $this->var2str($this->apellido)
               . ", remu_basica = " . $this->var2str($this->remu_basica)
               . ", bonifi_basica = " . $this->var2str($this->bonifi_basica)
               . ", bonifi_espesifica = " . $this->var2str($this->bonifi_espesifica)
               . ", total_ganado = " . $this->var2str($this->total_ganado)
               . ", desc_afp = " . $this->var2str($this->desc_afp)
               . ", desc_snp = " . $this->var2str($this->desc_snp)
               . ", desc_adelanto = " . $this->var2str($this->desc_adelanto)
               . ", desc_otros = " . $this->var2str($this->desc_otros)
               . ", desc_total = " . $this->var2str($this->desc_total)
               . ", neto_pagar = " . $this->var2str($this->neto_pagar)
               . ", aporte_esalud = " . $this->var2str($this->aporte_esalud)
               . ", aporte_sctr = " . $this->var2str($this->aporte_sctr)
               . ", aporte_total = " . $this->var2str($this->aporte_total) 
               . ", fecha_pago = " .  $this->var2str($this->fecha_pago).
                 " WHERE idplanilla = ".$this->var2str($this->idplanilla).";";
         
         return $this->db->exec($sql);
      } else {
         $sql = "INSERT INTO " . $this->table_name . " (nombre,apellido,remu_basica,bonifi_basica,bonifi_espesifica,
         total_ganado,desc_afp,desc_snp,desc_adelanto,desc_otros,desc_total,neto_pagar,aporte_esalud,aporte_sctr,aporte_total,fecha_pago) VALUES
                   ("
                     .$this->var2str($this->nombre)
             . "," . $this->var2str($this->apellido)
             . "," . $this->var2str($this->remu_basica)
             . "," . $this->var2str($this->bonifi_basica)
             . "," . $this->var2str($this->bonifi_espesifica)
             . "," . $this->var2str($this->total_ganado)
             . "," . $this->var2str($this->desc_afp)
             . "," . $this->var2str($this->desc_snp)
             . "," . $this->var2str($this->desc_adelanto)
             . "," . $this->var2str($this->desc_otros)
             . "," . $this->var2str($this->desc_total)
             . "," . $this->var2str($this->neto_pagar)
             . "," . $this->var2str($this->aporte_esalud)
             . "," . $this->var2str($this->aporte_sctr)
             . "," . $this->var2str($this->aporte_total)
             . "," .  $this->var2str($this->fecha_pago).
                   ");";

         if ($this->db->exec($sql)) {
            return TRUE;
         } else {
            return FALSE;
         }
      }
   }

   
   public function delete() {
      return '';
   }
   

   public function cron_job() {
    echo "*********** ejecutando planilla ******************";
   }

 

   
   
   

}
