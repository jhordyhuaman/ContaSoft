<?php


require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';
require_model('almacen.php');
require_model('cuenta_banco.php');
require_model('ejercicio.php');
require_model('forma_pago.php');
require_model('pais.php');
require_model('serie.php');


class admin_empresa extends fs_controller
{
   public $almacen;
   public $cuenta_banco;
   public $divisa;
   public $ejercicio;
   public $forma_pago;
   public $impresion;
   public $serie;
   public $pais;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Empresa / web', 'admin', TRUE, TRUE);
   }
   
   protected function private_core()
   {
      $this->almacen = new almacen();
      $this->cuenta_banco = new cuenta_banco();
      $this->divisa = new divisa();
      $this->ejercicio = new ejercicio();
      $this->forma_pago = new forma_pago();
      $this->serie = new serie();
      $this->pais = new pais();
      
      if( isset($_POST['nombre']) )
      {

         $this->empresa->nombre = $_POST['nombre'];
         $this->empresa->nombrecorto = $_POST['nombrecorto'];
         $this->empresa->web = $_POST['web'];
         $this->empresa->email = $_POST['email'];
         

         $this->empresa->email_config['mail_password'] = $_POST['mail_password'];
         $this->empresa->email_config['mail_bcc'] = $_POST['mail_bcc'];
         $this->empresa->email_config['mail_firma'] = $_POST['mail_firma'];
         $this->empresa->email_config['mail_host'] = $_POST['mail_host'];
         $this->empresa->email_config['mail_port'] = intval($_POST['mail_port']);
         $this->empresa->email_config['mail_enc'] = strtolower($_POST['mail_enc']);
         $this->empresa->email_config['mail_user'] = $_POST['mail_user'];
         $this->empresa->email_config['mail_low_security'] = isset($_POST['mail_low_security']);
         
         if( $this->empresa->save() )
         {
            $this->new_message('Datos guardados correctamente.');
            $this->mail_test();
         }
         else
            $this->new_error_msg ('Error al guardar los datos.');
      }
   }
   
   private function mail_test()
   {
      if( $this->empresa->can_send_mail() )
      {

         if( extension_loaded('openssl') )
         {
            $mail = new PHPMailer();
            $mail->Timeout = 3;
            $mail->isSMTP();
            $mail->SMTPAuth = TRUE;
            $mail->SMTPSecure = $this->empresa->email_config['mail_enc'];
            $mail->Host = $this->empresa->email_config['mail_host'];
            $mail->Port = intval($this->empresa->email_config['mail_port']);
            $mail->Username = $this->empresa->email;
            if($this->empresa->email_config['mail_user'] != '')
            {
               $mail->Username = $this->empresa->email_config['mail_user'];
            }
            
            $mail->Password = $this->empresa->email_config['mail_password'];
            $mail->From = $this->empresa->email;
            $mail->FromName = $this->user->nick;
            $mail->CharSet = 'UTF-8';
            
            $mail->Subject = 'TEST';
            $mail->AltBody = 'TEST';
            $mail->WordWrap = 50;
            $mail->msgHTML('TEST');
            $mail->isHTML(TRUE);
            
            $SMTPOptions = array();
            if($this->empresa->email_config['mail_low_security'])
            {
               $SMTPOptions = array(
                   'ssl' => array(
                       'verify_peer' => false,
                       'verify_peer_name' => false,
                       'allow_self_signed' => true
                   )
               );
            }
            
            if( !$mail->smtpConnect($SMTPOptions) )
            {
               $this->new_error_msg('No se ha podido conectar por email. ¿La contraseña es correcta?');
               
               if($mail->Host == 'smtp.gmail.com')
               {
                  $this->new_error_msg('Aunque la contraseña de gmail sea correcta, en ciertas '
                          . 'situaciones los servidores de gmail bloquean la conexión. '
                          . 'Para superar esta situación debes crear y usar una '
                          . '<a href="https://support.google.com/accounts/answer/185833?hl=es" '
                          . 'target="_blank">contraseña de aplicación</a>');
               }
            }
         }
         else
         {
            $this->new_error_msg('No se encuentra la extensión OpenSSL,'
                    . ' imprescindible para enviar emails.');
         }
      }
   }
}
