<?php

function fs_tipos_id_fiscal()
{
   return array(FS_CIFNIF,'Pasaporte','DNI','NIF','CIF','CUIT');
}

function remote_printer()
{
   if( isset($_REQUEST['terminal']) )
   {
      require_model('terminal_caja.php');
      
      $t0 = new terminal_caja();
      $terminal = $t0->get($_REQUEST['terminal']);
      if($terminal)
      {
         echo $terminal->tickets;
         
         $terminal->tickets = '';
         $terminal->save();
      }
      else
         echo 'ERROR: terminal no encontrado.';
   }
}

function plantilla_email($tipo, $documento, $firma)
{
   /// obtenemos las plantillas
   $fsvar = new fs_var();
   $plantillas = array(
       'mail_factura' => "Buenos días, le adjunto su #DOCUMENTO#.\n#FIRMA#",
       'mail_albaran' => "Buenos días, le adjunto su #DOCUMENTO#.\n#FIRMA#",
       'mail_pedido' => "Buenos días, le adjunto su #DOCUMENTO#.\n#FIRMA#",
       'mail_presupuesto' => "Buenos días, le adjunto su #DOCUMENTO#.\n#FIRMA#",
   );
   $plantillas = $fsvar->array_get($plantillas, FALSE);
   
   if($tipo == 'albaran')
   {
      $documento = FS_ALBARAN.' '.$documento;
   }
   else if($tipo == 'pedido')
   {
      $documento = FS_PEDIDO.' '.$documento;
   }
   else if($tipo == 'presupuesto')
   {
      $documento = FS_PRESUPUESTO.' '.$documento;
   }
   else
   {
      $documento = $tipo.' '.$documento;
   }
   
   $txt = str_replace('#DOCUMENTO#', $documento, $plantillas['mail_'.$tipo]);
   $txt = str_replace('#FIRMA#', $firma, $txt);
   
   return $txt;
}