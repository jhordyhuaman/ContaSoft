<?php if(!class_exists('raintpl')){exit;}?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="es" xml:lang="es" >
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   <title><?php echo $fsc->page->title;?> &lsaquo; <?php if( $fsc->empresa->nombrecorto ){ ?><?php echo $fsc->empresa->nombrecorto;?><?php }else{ ?><?php echo $fsc->empresa->nombre;?><?php } ?></title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <link rel="shortcut icon" href="view/img/favicon.ico" />
   <link rel="stylesheet" href="<?php echo $fsc->user->css;?>" />
   <link rel="stylesheet" href="view/css/font-awesome.min.css" />
   <link rel="stylesheet" href="view/css/datepicker.css" />
   <link rel="stylesheet" href="view/css/flat-ui.css" />
   <link rel="stylesheet" href="view/css/custom.css" />
   <script type="text/javascript" src="view/js/jquery.min.js"></script>
   <script type="text/javascript" src="view/js/bootstrap.min.js"></script>
   <script type="text/javascript" src="view/js/bootstrap-datepicker.js" charset="UTF-8"></script>
   <script type="text/javascript" src="view/js/jquery.autocomplete.min.js"></script>
   <script type="text/javascript" src="view/js/jquery-ui.min.js"></script>
   <script type="text/javascript" src="view/js/base.js"></script>
   <script type="text/javascript">
      function show_precio(precio)
      {
         <?php if( FS_POS_DIVISA=='right' ){ ?>

         return number_format(precio, <?php  echo FS_NF0;?>, '<?php  echo FS_NF1;?>', '<?php  echo FS_NF2;?>')+' <?php echo $fsc->simbolo_divisa();?>';
         <?php }else{ ?>

         return '<?php echo $fsc->simbolo_divisa();?>'+number_format(precio, <?php  echo FS_NF0;?>, '<?php  echo FS_NF1;?>', '<?php  echo FS_NF2;?>');
         <?php } ?>

      }
      function show_numero(numero)
      {
         return number_format(numero, <?php  echo FS_NF0;?>, '<?php  echo FS_NF1;?>', '<?php  echo FS_NF2;?>');
      }
   </script>
   <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <?php if( $value1->type=='head' ){ ?><?php echo $value1->text;?><?php } ?>

   <?php } ?>

</head>
<body>
   <nav class="navbar navbar-inverse navbar-embossed" role="navigation">
      <div class="container-fluid">
         <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
               <span class="sr-only">Menú</span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
            </button>

            <a class="navbar-brand" href="index.php">

               <span class="hidden-sm"><img src="./view/img/logo.png" width="50" height="50" style="margin-top: -8px;"/><?php echo $fsc->empresa->nombre;?></span>
            </a>
         </div>
         
         <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
               <?php $loop_var1=$fsc->folders(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <li class="dropdown<?php if( $value1==$fsc->page->folder ){ ?> active<?php } ?>">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                     <?php if( $value1=='admin' ){ ?>

                      <span class="glyphicon glyphicon-wrench" title="Administración" ></span>
                     <?php }else{ ?>

                     <span class="text-capitalize"><?php echo $value1;?></span>
                     <?php } ?>

                  </a>
                  <ul class="dropdown-menu">
                     <?php $loop_var2=$fsc->pages($value1); $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

                     <li<?php if( $value2->showing() ){ ?> class="active"<?php } ?>><a href="<?php echo $value2->url();?>"><?php echo $value2->title;?></a></li>
                     <?php } ?>

                  </ul>
               </li>
               <?php } ?>

               <?php if( count($GLOBALS['modulos'])>0 ){ ?>

               <li class="dropdown hidden-sm">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" title="Acceso rápido">
                     <span class="glyphicon glyphicon-star"></span>
                  </a>
                  <ul class="dropdown-menu">
                     <?php $menu_ar_vacio=$this->var['menu_ar_vacio']=TRUE;?>

                     <?php $loop_var1=$fsc->user->get_menu(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $value1->important ){ ?>

                        <li><a href="<?php echo $value1->url();?>"><?php echo $value1->title;?></a></li>
                        <?php $menu_ar_vacio=$this->var['menu_ar_vacio']=FALSE;?>

                        <?php } ?>

                     <?php } ?>

                     <?php if( $menu_ar_vacio ){ ?>

                     <li><a href="#">Vacío</a></li>
                     <?php } ?>

                  </ul>
               </li>
               <?php } ?>

            </ul>
            
            <ul class="nav navbar-nav navbar-right">

               
               <?php if( $fsc->get_last_changes() ){ ?>

               <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                     <span class="glyphicon glyphicon-time hidden-xs"></span>
                     <span class="visible-xs">Historial</span>
                  </a>
                  <ul class="dropdown-menu">
                     <?php $loop_var1=$fsc->get_last_changes(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <li title="<?php if( $value1['nuevo'] ){ ?>creado<?php }else{ ?>modificado<?php } ?> el <?php echo $value1['cambio'];?>">
                           <a href="<?php echo $value1['url'];?>">
                              <?php if( $value1['nuevo'] ){ ?>

                              <span class="glyphicon glyphicon-file"></span> &nbsp;
                              <?php }else{ ?>

                              <span class="glyphicon glyphicon-edit"></span> &nbsp;
                              <?php } ?>

                              <?php echo $value1['texto'];?>

                           </a>
                        </li>
                     <?php } ?>

                  </ul>
               </li>
               <?php } ?>


               <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?php echo $fsc->user->nick;?>">
                     <span class="glyphicon glyphicon-user hidden-xs"></span>
                     <span class="visible-xs">Usuario</span>
                  </a>
                  <ul class="dropdown-menu">
                     <li>
                        <a href="<?php echo $fsc->user->url();?>">
                           <span class="glyphicon glyphicon-user"></span> &nbsp; <?php echo $fsc->user->nick;?>

                        </a>
                     </li>
                     <li class="divider"></li>
                     <li>
                        <a href="<?php echo $fsc->url();?>&logout=TRUE">
                           <span class="glyphicon glyphicon-log-out"></span> &nbsp; Cerrar sesión
                        </a>
                     </li>
                  </ul>

               </li>
               <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?php echo $fsc->user->nick;?>">
                     <span class="glyphicon glyphicon-user hidden-xs"></span>
                     <span class="visible-xs">Usuario</span>
                  </a>


               </li>
            </ul>
         </div>
      </div>
   </nav>
   
   <?php if( $fsc->user->css=='view/css/bootstrap-yeti.min.css' ){ ?>

   <div style="margin-bottom: 55px"></div>
   <?php }else{ ?>

   <div style="margin-bottom: 70px"></div>
   <?php } ?>

   
   <?php if( $fsc->get_errors() ){ ?>

   <div class="alert alert-danger hidden-print">
      <ul><?php $loop_var1=$fsc->get_errors(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?><li><?php echo $value1;?></li><?php } ?></ul>
   </div>
   <?php } ?>

   <?php if( $fsc->get_messages() ){ ?>

   <div class="alert alert-success hidden-print">
      <ul><?php $loop_var1=$fsc->get_messages(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?><li><?php echo $value1;?></li><?php } ?></ul>
   </div>
   <?php } ?>

   <?php if( $fsc->get_advices() ){ ?>

   <div class="alert alert-info hidden-print">
      <ul><?php $loop_var1=$fsc->get_advices(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?><li><?php echo $value1;?></li><?php } ?></ul>
   </div>
   <?php } ?>


   <div class="modal fade" id="modal_iframe" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
               <h4 class="modal-title">Modal title</h4>
            </div>
            <iframe src="" width="100%" height="600" frameBorder="0">
               Este navegador no soporta frames.
            </iframe>
         </div>
      </div>
   </div>