<?php


require_model('ejercicio.php');
require_model('serie.php');

class contabilidad_series extends fs_controller
{
   public $allow_delete;
   public $ejercicios;
   public $num_personalizada;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, ucfirst(FS_SERIES), 'contabilidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $eje = new ejercicio();
      $this->ejercicios = $eje->all();
      $this->serie = new serie();
      
      $fsvar = new fs_var();
      if( isset($_GET['num_personalizada']) )
      {
         $this->num_personalizada = TRUE;
         $fsvar->simple_save('numeracion_personalizada', $this->num_personalizada);
      }
      else
      {
         $this->num_personalizada = $fsvar->simple_get('numeracion_personalizada');
      }
      
      if( isset($_POST['codserie']) )
      {
         $serie = $this->serie->get($_POST['codserie']);
         if( !$serie )
         {
            $serie = new serie();
            $serie->codserie = $_POST['codserie'];
         }
         $serie->descripcion = $_POST['descripcion'];
         $serie->siniva = isset($_POST['siniva']);
         $serie->irpf = floatval($_POST['irpf']);
         
         if($this->num_personalizada)
         {
            $serie->codejercicio = NULL;
            $serie->numfactura = 1;
            
            if($_POST['codejercicio'] != '')
            {
               $serie->codejercicio = $_POST['codejercicio'];
               $serie->numfactura = intval($_POST['numfactura']);
            }
         }
         
         if( $serie->save() )
         {
            $this->new_message('Datos guardados correctamente.');
         }
         else
            $this->new_error_msg("¡Imposible guardar ".FS_SERIE."!");
      }
      else if( isset($_GET['delete']) )
      {
         if(!$this->user->admin)
         {
            $this->new_error_msg('Sólo un administrador puede eliminar '.FS_SERIES.'.');
         }
         else
         {
            $serie = $this->serie->get($_GET['delete']);
            if($serie)
            {
               if( $serie->delete() )
               {
                  $this->new_message('Datos eliminados correctamente: '.FS_SERIE.' '.$_GET['delete'], TRUE);
               }
               else
                  $this->new_error_msg("¡Imposible eliminar ".FS_SERIE.' '.$_GET['delete']."!");
            }
            else
               $this->new_error_msg('Datos no encontrados: '.FS_SERIE.' '.$_GET['delete']);
         }
      }
   }
}
