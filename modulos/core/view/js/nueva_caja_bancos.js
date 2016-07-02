


/*
 */

var numlineas = 0;
var fs_nf0 = 2;
var all_impuestos = [];
var all_series = [];
var cliente = false;
var nueva_venta_url = '';
var kiwimaru_url = '';
var fin_busqueda1 = true;
var fin_busqueda2 = true;
var siniva = false;
var irpf = 0;
var solo_con_stock = true;

function removert(celda){
   $(celda).parents('tr').remove();
}
function usar_cliente(codcliente)
{
   if(nueva_venta_url !== '')
   {
      $.getJSON(nueva_venta_url, 'datoscliente='+codcliente, function(json) {
         cliente = json;
         document.f_buscar_articulos.codcliente.value = cliente.codcliente;
         if(cliente.regimeniva == 'Exento')
         {
            irpf = 0;
            for(var j=0; j<numlineas; j++)
            {
               if($("#linea_"+j).length > 0)
               {
                  $("#iva_"+j).val(0);
                  $("#recargo_"+j).val(0);
               }
            }
         }
         recalcular();
      });
   }
}

function usar_serie()
{
   for(var i=0; i<all_series.length; i++)
   {
      if(all_series[i].codserie == $("#codserie").val())
      {
         siniva = all_series[i].siniva;
         irpf = all_series[i].irpf;

         for(var j=0; j<numlineas; j++)
         {
            if($("#linea_"+j).length > 0)
            {
               if(siniva)
               {
                  $("#iva_"+j).val(0);
                  $("#recargo_"+j).val(0);
               }
            }
         }

         break;
      }
   }
}

function usar_almacen()
{
   document.f_buscar_articulos.codalmacen.value = document.f_new_albaran.almacen.value;
}

function recalcular()
{
   var l_uds = 0;
   var l_pvp = 0;
   var l_dto = 0;
   var l_neto = 0;
   var l_iva = 0;
   var l_irpf = 0;
   var l_recargo = 0;
   var neto = 0;
   var total_iva = 0;
   var total_irpf = 0;
   var total_recargo = 0;

   for(var i=0; i<numlineas; i++)
   {
      if($("#linea_"+i).length > 0)
      {
         l_uds = parseFloat( $("#cantidad_"+i).val() );
         l_pvp = parseFloat( $("#pvp_"+i).val() );
         l_dto = parseFloat( $("#dto_"+i).val() );
         l_neto = l_uds*l_pvp*(100-l_dto)/100;
         l_iva = parseFloat( $("#iva_"+i).val() );
         l_irpf = parseFloat( $("#irpf_"+i).val() );

         if(cliente.recargo)
         {
            l_recargo = parseFloat( $("#recargo_"+i).val() );
         }
         else
         {
            l_recargo = 0;
            $("#recargo_"+i).val(0);
         }

         $("#neto_"+i).val( l_neto );
         if(numlineas == 1)
         {
            $("#total_"+i).val( fs_round(l_neto, fs_nf0) + fs_round(l_neto*(l_iva-l_irpf+l_recargo)/100, fs_nf0) );
         }
         else
         {
            $("#total_"+i).val( number_format(l_neto + (l_neto*(l_iva-l_irpf+l_recargo)/100), fs_nf0, '.', '') );
         }

         neto += l_neto;
         total_iva += l_neto * l_iva/100;
         total_irpf += l_neto * l_irpf/100;
         total_recargo += l_neto * l_recargo/100;

         /// adaptamos el alto del textarea al texto
         var txt = $("textarea[name='desc_"+i+"']").val();
         txt = txt.split(/\r*\n/);
         if(txt.length > 1)
         {
            $("textarea[name='desc_"+i+"']").prop('rows', txt.length);
         }
      }
   }

   neto = fs_round(neto, fs_nf0);
   total_iva = fs_round(total_iva, fs_nf0);
   total_irpf = fs_round(total_irpf, fs_nf0);
   total_recargo = fs_round(total_recargo, fs_nf0);
   $("#aneto").html( show_numero(neto) );
   $("#aiva").html( show_numero(total_iva) );
   $("#are").html( show_numero(total_recargo) );
   $("#airpf").html( show_numero(total_irpf) );
   $("#atotal").val( neto + total_iva - total_irpf + total_recargo );

   if(total_recargo == 0 && !cliente.recargo)
   {
      $(".recargo").hide();
   }
   else
   {
      $(".recargo").show();
   }

   if(total_irpf == 0 && irpf == 0)
   {
      $(".irpf").hide();
   }
   else
   {
      $(".irpf").show();
   }
}

function ajustar_neto()
{
   var l_uds = 0;
   var l_pvp = 0;
   var l_dto = 0;
   var l_neto = 0;

   for(var i=0; i<numlineas; i++)
   {
      if($("#linea_"+i).length > 0)
      {
         l_uds = parseFloat( $("#cantidad_"+i).val() );
         l_pvp = parseFloat( $("#pvp_"+i).val() );
         l_dto = parseFloat( $("#dto_"+i).val() );
         l_neto = parseFloat( $("#neto_"+i).val() );
         if( isNaN(l_neto) )
         {
            l_neto = 0;
         }

         if( l_neto <= l_pvp*l_uds )
         {
            l_dto = 100 - 100*l_neto/(l_pvp*l_uds);
            if( isNaN(l_dto) )
            {
               l_dto = 0;
            }
         }
         else
         {
            l_dto = 0;
            l_pvp = 100*l_neto/(l_uds*(100-l_dto));
            if( isNaN(l_pvp) )
            {
               l_pvp = 0;
            }
         }

         $("#pvp_"+i).val(l_pvp);
         $("#dto_"+i).val(l_dto);
      }
   }

   recalcular();
}

function ajustar_total()
{
   var l_uds = 0;
   var l_pvp = 0;
   var l_dto = 0;
   var l_iva = 0;
   var l_irpf = 0;
   var l_recargo = 0;
   var l_neto = 0;
   var l_total = 0;

   for(var i=0; i<numlineas; i++)
   {
      if($("#linea_"+i).length > 0)
      {
         l_uds = parseFloat( $("#cantidad_"+i).val() );
         l_pvp = parseFloat( $("#pvp_"+i).val() );
         l_dto = parseFloat( $("#dto_"+i).val() );
         l_iva = parseFloat( $("#iva_"+i).val() );
         l_recargo = parseFloat( $("#recargo_"+i).val() );

         l_irpf = irpf;
         if(l_iva <= 0)
         {
            l_irpf = 0;
         }

         l_total = parseFloat( $("#total_"+i).val() );
         if( isNaN(l_total) )
         {
            l_total = 0;
         }

         if( l_total <= l_pvp*l_uds + (l_pvp*l_uds*(l_iva-l_irpf+l_recargo)/100) )
         {
            l_neto = 100*l_total/(100+l_iva-l_irpf+l_recargo);
            l_dto = 100 - 100*l_neto/(l_pvp*l_uds);
            if( isNaN(l_dto) )
            {
               l_dto = 0;
            }
         }
         else
         {
            l_dto = 0;
            l_neto = 100*l_total/(100+l_iva-l_irpf+l_recargo);
            l_pvp = l_neto/l_uds;
         }

         $("#pvp_"+i).val(l_pvp);
         $("#dto_"+i).val(l_dto);
      }
   }

   recalcular();
}

function ajustar_iva(num)
{
   if($("#linea_"+num).length > 0)
   {
      if(cliente.regimeniva == 'Exento')
      {
         $("#iva_"+num).val(0);
         $("#recargo_"+num).val(0);

         alert('El cliente tiene regimen de IVA: '+cliente.regimeniva);
      }
      else if(siniva && $("#iva_"+num).val() != 0)
      {
         $("#iva_"+num).val(0);
         $("#recargo_"+num).val(0);

         alert('La serie selecciona es sin IVA.');
      }
      else if(cliente.recargo)
      {
         for(var i=0; i<all_impuestos.length; i++)
         {
            if($("#iva_"+num).val() == all_impuestos[i].iva)
            {
               $("#recargo_"+num).val(all_impuestos[i].recargo);
            }
         }
      }
   }

   recalcular();
}

//------------------------------------------------------------------------------------------------------------------
$(document).on('change' , '#in_igv' , function(){

   if(this.checked) {

      var coste =$(this).parent().siblings('input').val();
      $(this).parent().siblings('input').val(Math.round(coste/1.18));
      $(this).parent().siblings('input').addClass('');
   }else{
      $(this).parent().siblings('input').val($(this).val());
   }
   recalcular();

});
//-------------------------------------------------------------------------------------------------------------------

function add_linea_libre()
{
   codimpuesto = false;
   for(var i=0; i<all_impuestos.length; i++)
   {
      codimpuesto = all_impuestos[i].codimpuesto;
      break;
   }

   var html = '<tr id="linea_"' + numlineas + '">' +

   '<td>'+
       '<div class="form-control">'+
       '<div class="row" style="position: relative; left: 8px; bottom: 9px">' +
       '<div type="checkbox" class="col-ms-6">' +
       '<label class="radio-inline" style="font-size: 9px; color:#1a237e"><input value="ent" type="radio" name="saldo_' + numlineas +'" checked="checked"/>Entrada</label>'+
       '</div>'+
       '<div type="checkbox" class="col-ms-6" style="position: relative; bottom: 3px">'+
              '<label class="radio-inline" style="font-size: 9px; color:#91170a"><input  value="sal" type="radio" name="saldo_' + numlineas +'" />Salida</label>'+
        '</div>'+
       '</div>'+
       '</div>'+
    '</td>'+

       '<td>'+
       '<textarea  class="form-control" id="desc1_' + numlineas + '" name="desc1_' + numlineas + '" rows="1" ></textarea>'+
       '</td>' +

    '<td>'+
       '<input type="hidden" name="idlinea_' + numlineas + '" value=""/>' +
       '<input type="hidden" name="referencia_"' + numlineas + '"/>' +
       '<input class="form-control"/>'+
    '</td>' +

       ' <td>'+
       "<input class='form-control' id='codsubcuenta_"+numlineas+"' name='idsubcuenta' type='text'\n\
               onclick=\"show_buscar_subcuentas('"+numlineas+"','subcuenta')\" onkeypress='return false' onkeyup='document.f_buscar_subcuentas.query.value=$(this).val();buscar_subcuentas()'\n\
               autocomplete='off' placeholder='Seleccionar'/>"+
       '</td>'+

    '<td>'+
      '<textarea placeholder="Selcionar Codigo" onkeypress="return false" class="form-control" id="desc_' + numlineas + '" name="desc_' + numlineas + '" rows="1" ></textarea>'+
    '</td>' +
       '<td>'+
       '<input  class="form-control" id="cant_' + numlineas + '" name="cant_' + numlineas + '" rows="1"/>'+
       '</td>' +
    '<td>'+
   '<button class="btn btn-sm btn-danger" type="button" onclick="removert(this);recalcular();">' +
   '<span class="glyphicon glyphicon-trash"></span></button>'+
   '</td>' +

'</tr>';

   $("#lineas_albaran").append(html);

   numlineas += 1;
   $("#numlineas").val(numlineas);
   recalcular();

   $("#desc_"+(numlineas-1)).select();
   return false;
}



$(document).ready(function() {
   $("#i_new_line").click(function() {
      $("#i_new_line").val("");
      $("#nav_articulos li").each(function() {
         $(this).removeClass("active");
      });
      $("#li_mis_articulos").addClass('active');
      $("#search_results").show();
      $("#kiwimaru_results").html('');
      $("#kiwimaru_results").hide();
      $("#nuevo_articulo").hide();
      $("#modal_articulos").modal('show');
      document.f_buscar_articulos.query.select();
   });

   $("#i_new_line").keyup(function() {
      document.f_buscar_articulos.query.value = $("#i_new_line").val();
      $("#i_new_line").val('');
      $("#nav_articulos li").each(function() {
         $(this).removeClass("active");
      });
      $("#li_mis_articulos").addClass('active');
      $("#search_results").html('');
      $("#search_results").show();
      $("#kiwimaru_results").html('');
      $("#kiwimaru_results").hide();
      $("#nuevo_articulo").hide();
      $("#modal_articulos").modal('show');
      document.f_buscar_articulos.query.select();
      buscar_articulos();
   });

   $("#f_buscar_articulos").keyup(function() {
      buscar_articulos();
   });

   $("#f_buscar_articulos").submit(function(event) {
      event.preventDefault();
      buscar_articulos();
   });

   $("#b_mis_articulos").click(function(event) {
      event.preventDefault();
      $("#nav_articulos li").each(function() {
         $(this).removeClass("active");
      });
      $("#li_mis_articulos").addClass('active');
      $("#kiwimaru_results").hide();
      $("#nuevo_articulo").hide();
      $("#search_results").show();
      document.f_buscar_articulos.query.focus();
   });

   $("#b_kiwimaru").click(function(event) {
      event.preventDefault();
      $("#nav_articulos li").each(function() {
         $(this).removeClass("active");
      });
      $("#li_kiwimaru").addClass('active');
      $("#nuevo_articulo").hide();
      $("#search_results").hide();
      $("#kiwimaru_results").show();
      document.f_buscar_articulos.query.focus();
   });

   $("#b_nuevo_articulo").click(function(event) {
      event.preventDefault();
      $("#nav_articulos li").each(function() {
         $(this).removeClass("active");
      });
      $("#li_nuevo_articulo").addClass('active');
      $("#search_results").hide();
      $("#kiwimaru_results").hide();
      $("#nuevo_articulo").show();
      document.f_nuevo_articulo.referencia.select();
   });
});