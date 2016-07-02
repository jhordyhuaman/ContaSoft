<?php


require_model('planilla.php');
require_once 'modulos/core/extras/xlsxwriter.class.php';

/**
 * Description of informe_resumenarticulos
 */
class informe_planilla extends fs_controller
{
   public $planilla;

   
   public function __construct()
   {
      parent::__construct(__CLASS__, "planilla", 'informes', FALSE, TRUE);
   }

   protected function private_core() {

      $this->planilla = new planilla();
      $this->share_extension();
      if(isset($_POST['register_personal'])){
         $this->planilla->nombre = $_POST['nombre'];
         $this->planilla->apellido= $_POST['apellido'];
         $this->planilla->total_ganado = '10';

         if( $this->planilla->save() ){
            $this->new_message("Factura modificada correctamente.");
            }else{
            $this->new_error_msg("Imposible modificar la fecha del asiento.");
            }
         $this->new_message("Factura modificada correctamente.");
         //$this->template = false;
         header('Content-Type: application/json');
         $return = $_POST;
         $return["json"] = json_encode($return);
         echo json_encode($return);
      }


   }


   private function share_extension()
   {
      $extensiones = array(
         array(
            'name' => 'planilla_css001',
            'page_from' => __CLASS__,
            'page_to' => 'informe_planilla',
            'type' => 'head',
            'text' => '<link rel="stylesheet" type="text/css" media="screen" href="modulos/kardex/view/css/ui.jqgrid-bootstrap.css"/>',
            'params' => ''
         ),
         array(
            'name' => 'planilla_css002',
            'page_from' => __CLASS__,
            'page_to' => 'informe_planilla',
            'type' => 'head',
            'text' => '<link rel="stylesheet" type="text/css" media="screen" href="modulos/kardex/view/css/bootstrap-select.min.css"/>',
            'params' => ''
         ),
      );

      foreach ($extensiones as $ext) {
         $fsext0 = new fs_extension($ext);
         if (!$fsext0->save()) {
            $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
         }
      }
   }

}
