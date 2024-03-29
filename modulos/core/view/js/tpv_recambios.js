
var fs_nf0 = 2;
var numlineas = 0;
var tpv_url = '';
var siniva = false;
var irpf = 0;
var all_impuestos = [];
var all_series = [];
var cliente = false;
var fin_busqueda1 = true;
var codbarras = false;

function usar_cliente(codcliente)
{
   if(tpv_url !== '')
   {
      $.getJSON(tpv_url, 'datoscliente='+codcliente, function(json) {
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
      if(all_series[i].codserie == document.f_tpv.serie.value)
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
   
   for(var i=1; i<=numlineas; i++)
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
   $("#atotal").html( neto + total_iva - total_irpf + total_recargo );
   
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
   
   $("#tpv_total").val( show_precio(neto + total_iva - total_irpf + total_recargo) );
   $("#tpv_total2").val(neto + total_iva - total_irpf + total_recargo);
   var tpv_efectivo = parseFloat( $("#tpv_efectivo").val() );
   $("#tpv_cambio").val( show_precio(tpv_efectivo - (neto + total_iva - total_irpf + total_recargo)) );
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
   
   for(var i=1; i<=numlineas; i++)
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

function get_precios(ref)
{
   $.ajax({
      type: 'POST',
      url: tpv_url,
      dataType: 'html',
      data: "referencia4precios="+ref+"&codcliente="+document.f_tpv.cliente.value,
      success: function(datos) {
         $("#search_results").html(datos);
      }
   });
}

function add_articulo(ref,desc,pvp,dto,codimpuesto,cantidad)
{
   numlineas += 1;
   $("#numlineas").val(numlineas);
   desc = Base64.decode(desc);
   var iva = 0;
   var recargo = 0;
   if(cliente.regimeniva != 'Exento' && !siniva)
   {
      for(var i=0; i<all_impuestos.length; i++)
      {
         if(all_impuestos[i].codimpuesto == codimpuesto)
         {
            iva = all_impuestos[i].iva;
            if(cliente.recargo)
            {
              recargo = all_impuestos[i].recargo;
            }
            break;
         }
      }
   }
   
   $("#lineas_albaran").prepend("<tr id=\"linea_"+numlineas+"\">\n\
         <td><input type=\"hidden\" name=\"referencia_"+numlineas+"\" value=\""+ref+"\"/>\n\
            <input type=\"hidden\" id=\"iva_"+numlineas+"\" name=\"iva_"+numlineas+"\" value=\""+iva+"\"/>\n\
            <input type=\"hidden\" id=\"recargo_"+numlineas+"\" name=\"recargo_"+numlineas+"\" value=\""+recargo+"\"/>\n\
            <input type=\"hidden\" id=\"irpf_"+numlineas+"\" name=\"irpf_"+numlineas+"\" value=\""+irpf+"\"/>\n\
            <div class=\"form-control\"><a target=\"_blank\" href=\"index.php?page=ventas_articulo&ref="+ref+"\">"+ref+"</a></div></td>\n\
         <td><textarea class=\"form-control\" id=\"desc_"+numlineas+"\" name=\"desc_"+numlineas+"\" rows=\"1\" onclick=\"this.select()\">"+desc+"</textarea></td>\n\
         <td><input type=\"number\" step=\"any\" id=\"cantidad_"+numlineas+"\" class=\"form-control text-right\" name=\"cantidad_"+numlineas+
            "\" onchange=\"recalcular()\" onkeyup=\"recalcular()\" autocomplete=\"off\" value=\""+cantidad+"\"/></td>\n\
         <td><button class=\"btn btn-sm btn-danger\" type=\"button\" onclick=\"$('#linea_"+numlineas+"').remove();recalcular();\">\n\
            <span class=\"glyphicon glyphicon-trash\"></span></button></td>\n\
         <td><input type=\"text\" class=\"form-control text-right\" id=\"pvp_"+numlineas+"\" name=\"pvp_"+numlineas+"\" value=\""+pvp+
            "\" onkeyup=\"recalcular()\" onclick=\"this.select()\" autocomplete=\"off\"/></td>\n\
         <td><input type=\"text\" id=\"dto_"+numlineas+"\" name=\"dto_"+numlineas+"\" value=\""+dto+
            "\" class=\"form-control text-right\" onkeyup=\"recalcular()\" onchange=\"recalcular()\" onclick=\"this.select()\" autocomplete=\"off\"/></td>\n\
         <td><input type=\"text\" class=\"form-control text-right\" id=\"neto_"+numlineas+"\" name=\"neto_"+numlineas+
            "\" readonly/></td>\n\
         <td class=\"text-right\"><div class=\"form-control\">"+iva+"</div></td>\n\
         <td class=\"text-right recargo\"><div class=\"form-control\">"+recargo+"</div></td>\n\
         <td class=\"text-right irpf\"><div class=\"form-control\">"+irpf+"</div></td>\n\
         <td><input type=\"text\" class=\"form-control text-right\" id=\"total_"+numlineas+"\" name=\"total_"+numlineas+
            "\" onchange=\"ajustar_total()\" onclick=\"this.select()\" autocomplete=\"off\"/></td></tr>");
   recalcular();
   $("#modal_articulos").modal('hide');
   
   $("#cantidad_"+(numlineas)).focus();
}

function add_articulo_atributos(ref,desc,pvp,dto,codimpuesto,cantidad)
{
   $.ajax({
      type: 'POST',
      url: 'index.php?page=tpv_recambios',
      dataType: 'html',
      data: "referencia4combi="+ref+"&desc="+desc+"&pvp="+pvp+"&dto="+dto
              +"&codimpuesto="+codimpuesto+"&cantidad="+cantidad,
      success: function(datos) {
         $("#nav_articulos").hide();
         $("#search_results").html(datos);
      }
   });
}

function buscar_articulos()
{
   if(document.f_buscar_articulos.query.value == '')
   {
      $("#search_results").html('');
   }
   else
   {
      document.f_buscar_articulos.codcliente.value = document.f_tpv.cliente.value;
      
      fin_busqueda1 = false;
      codbarras = false;
      $.getJSON(tpv_url, $("form[name=f_buscar_articulos]").serialize(), function(json) {
         var items = [];
         var insertar = false;
         $.each(json, function(key, val) {
            var stock = val.stockalm;
            if(val.stockalm != val.stockfis)
            {
               stock += ' ('+val.stockfis+')';
            }
            
            var descripcion = Base64.encode(val.descripcion);
            var descripcion_visible = val.descripcion;
            if(val.codfamilia)
            {
               descripcion_visible += ' <span class="label label-default" title="Familia: '+val.codfamilia+'">'
                       +val.codfamilia+'</span>';
            }
            if(val.codfabricante)
            {
               descripcion_visible += ' <span class="label label-default" title="Fabricante: '+val.codfabricante+'">'
                       +val.codfabricante+'</span>';
            }
            
            var tr_aux = '<tr>';
            if(val.bloqueado)
            {
               tr_aux = "<tr class=\"danger\">";
            }
            else if(val.stockfis < val.stockmin)
            {
               tr_aux = "<tr class=\"warning\">";
            }
            else if(val.stockalm > 0)
            {
               tr_aux = "<tr class=\"success\">";
            }
            
            if(val.codbarras != '' && val.codbarras == document.f_buscar_articulos.query.value && !codbarras)
            {
               codbarras = true;
               
               if( val.sevende && (val.stockalm > 0 || val.controlstock) )
               {
                  var funcion = "add_articulo('"+val.referencia+"','"+descripcion+"','"+val.pvp+"','"
                          +val.dtopor+"','"+val.codimpuesto+"','"+val.cantidad+"')";
                  
                  if(val.tipo)
                  {
                     funcion = "add_articulo_"+val.tipo+"('"+val.referencia+"','"+descripcion+"','"
                             +val.pvp+"','"+val.dtopor+"','"+val.codimpuesto+"','"+val.cantidad+"')";
                  }
                  
                  eval(funcion);
               }
               else if(val.sevende)
               {
                  alert('Sin stock.');
               }
            }
            else if( val.sevende && (val.stockalm > 0 || val.controlstock) )
            {
               var funcion = "add_articulo('"+val.referencia+"','"+descripcion+"','"+val.pvp+"','"
                       +val.dtopor+"','"+val.codimpuesto+"','"+val.cantidad+"')";
               
               if(val.tipo)
               {
                  funcion = "add_articulo_"+val.tipo+"('"+val.referencia+"','"+descripcion+"','"+val.pvp
                          +"','"+val.dtopor+"','"+val.codimpuesto+"','"+val.cantidad+"')";
               }
               
               items.push(tr_aux+"<td><a href=\"#\" onclick=\"get_precios('"+val.referencia+"')\" title=\"más detalles\">\n\
                  <span class=\"glyphicon glyphicon-eye-open\"></span></a>\n\
                  &nbsp; <a href=\"#\" onclick=\""+funcion+"\">"+val.referencia+'</a> '+descripcion_visible+"</td>\n\
                  <td class=\"text-right\"><a href=\"#\" onclick=\""+funcion+"\" title=\"actualizado el "+val.factualizado
                       +"\">"+show_precio(val.pvp*(100-val.dtopor)/100)+"</a></td>\n\
                  <td class=\"text-right\"><a href=\"#\" onclick=\""+funcion+"\" title=\"actualizado el "+val.factualizado
                       +"\">"+show_pvp_iva(val.pvp*(100-val.dtopor)/100,val.codimpuesto)+"</a></td>\n\
                  <td class=\"text-right\">"+stock+"</td></tr>");
            }
            else if(val.sevende)
            {
               items.push(tr_aux+"<td><a href=\"#\" onclick=\"get_precios('"+val.referencia+"')\" title=\"más detalles\">\n\
                  <span class=\"glyphicon glyphicon-eye-open\"></span></a>\n\
                  &nbsp; <a href=\"#\" onclick=\"alert('Sin stock.')\">"+val.referencia+'</a> '+descripcion_visible+"</td>\n\
                  <td class=\"text-right\"><a href=\"#\" onclick=\"alert('Sin stock.')\" title=\"actualizado el "+val.factualizado
                       +"\">"+show_precio(val.pvp*(100-val.dtopor)/100)+"</a></td>\n\
                  <td class=\"text-right\"><a href=\"#\" onclick=\"alert('Sin stock.')\" title=\"actualizado el "+val.factualizado
                       +"\">"+show_pvp_iva(val.pvp*(100-val.dtopor)/100,val.codimpuesto)+"</a></td>\n\
                  <td class=\"text-right\">"+stock+"</td></tr>");
            }
            
            if(val.query == document.f_buscar_articulos.query.value)
            {
               insertar = true;
               fin_busqueda1 = true;
            }
         });
         
         if(items.length == 0 && !fin_busqueda1)
         {
            items.push("<tr><td colspan=\"4\" class=\"warning\">Sin resultados.</td></tr>");
            insertar = true;
         }
         
         if(insertar)
         {
            $("#search_results").html("<div class=\"table-responsive\"><table class=\"table table-hover\"><thead><tr>\n\
               <th class=\"text-left\">Referencia + descripción</th><th class=\"text-right\">Precio</th><th class=\"text-right\">Precio+IVA</th>\n\
               <th class=\"text-right\">Stock</th></tr></thead>"+items.join('')+"</table></div>");
         }
      });
   }
}

function show_pvp_iva(pvp,codimpuesto)
{
   var iva = 0;
   if(cliente.regimeniva != 'Exento' && !siniva)
   {
      for(var i=0; i<all_impuestos.length; i++)
      {
         if(all_impuestos[i].codimpuesto == codimpuesto)
         {
            iva = all_impuestos[i].iva;
            break;
         }
      }
   }
   
   return show_precio(pvp + pvp*iva/100);
}

function sin_stock()
{
   alert('No hay stock.');
}

$(document).ready(function() {
   $("#b_reticket").click(function() {
      window.location.href = tpv_url+"&reticket="+prompt('Introduce el código del ticket (o déjalo en blanco para re-imprimir el último):');
   });
   
   $("#b_cerrar_caja").click(function() {
      if( confirm("¿Realmente deseas cerrar la caja?") )
      {
         window.location.href = tpv_url+"&cerrar_caja=TRUE";
      }
   });
   
   $("#i_new_line").click(function() {
      $("#i_new_line").val("");
      $("#modal_articulos").modal('show');
      document.f_buscar_articulos.query.select();
   });
   
   $("#i_new_line").keyup(function() {
      document.f_buscar_articulos.query.value = $("#i_new_line").val();
      $("#i_new_line").val('');
      buscar_articulos();
      $("#modal_articulos").modal('show');
      document.f_buscar_articulos.query.focus();
   });
   
   $("#f_buscar_articulos").keyup(function() {
      buscar_articulos();
   });
   
   $("#f_buscar_articulos").submit(function(event) {
      event.preventDefault();
      buscar_articulos();
   });
   
   $("#b_tpv_guardar").click(function() {
      $("#modal_guardar").modal('show');
      document.f_tpv.tpv_efectivo.focus();
   });
   
   $("#tpv_efectivo").keypress(function(e) {
      if(e.which == 13)
      {
         e.preventDefault();
         document.f_tpv.submit();
      }
   });
   $("#tpv_efectivo").keyup(function (e) {
      $("#tpv_cambio").val(number_format(parseFloat($(this).val()) - parseFloat($("#tpv_total2").val()), 2, '.', ''));
   });
});