import '../../css/selling/edit.scss';

import $ from 'jquery';
import 'jquery-ui/ui/widget.js';
import 'jquery-ui/ui/widgets/autocomplete';
const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

$(document).ready(function(){
	$( ".js-autocomplete" ).autocomplete({
	    minLength: 2,
	    source: function (request, response) {
            $.ajax({
            url: $( ".js-autocomplete" ).data("url"),
            dataType: "json",
            data: {
                nan: request.term
            },
            success: function (data) {
                    response(JSON.parse(data));
              },
            error: function (data) {
                    Routing.setRoutingData(routes);
                    document.location.href='/amorebono'+Routing.generate('app_login');
              },
              });
            },
	    select: function( event, ui ) {
	        $( ".js-autocomplete" ).val( ui.item._nan );
            $( ".js-izena" ).val( ui.item.izena );
            $( ".js-abizenak" ).val( ui.item.abizenak );
            $( ".js-telefonoa" ).val( ui.item.telefonoa );
            return false;
	    }
	})
	.autocomplete( "instance" )._renderItem = function( ul, item ) {
	  return $( "<li>" )
	    .append( "<div>" + item._nan + " ("+ item.izena + " " + item.abizenak +")" + "</div>" )
	    .appendTo( ul );
	};            
    
    $('.js-bonus').on('change',function (e) {
        if ( $('.js-bonus').val() === "")
            return;
        $.ajax({
            url: $('.js-bonus').data('url'),
            data: {
                id: $('.js-bonus').val()
            },
            success: function (json) {
                try {
                    var json_data = JSON.parse(json);
                } catch (e) {
                    console.log(e);
                    if (e instanceof SyntaxError) {
                        Routing.setRoutingData(routes);
                        document.location.href='/amorebono'+Routing.generate('app_login');
                    } else {
                        printError(e, false);
                    }
                }
                var json_data = JSON.parse(json);
                var selling_quantity_input = $('.js-quantity')[0];
                $(selling_quantity_input).attr('max',json_data.pertsonako_gehienezko_kopurua);
                if ($(selling_quantity_input).val() > json_data.pertsonako_gehienezko_kopurua ) {
                    $(selling_quantity_input).val(json_data.pertsonako_gehienezko_kopurua);
                }
                $('.js-totalPrice').val(json_data.price*$('.js-quantity').val());
                $('.js-remaining').text(json_data.guztira-json_data.emandakoak-$('.js-quantity').val());
            }
        });
    });
    $('.js-quantity').on('change',function (e) {
        console.log('quantityChanged!!');
        if ( $('.js-bonus').val() === "")
            return;
        $.ajax({
            url: $('.js-bonus').data('url'),
            data: {
                id: $('.js-bonus').val()
            },
            success: function (json) {
                var json_data = JSON.parse(json);
                $('.js-totalPrice').val(json_data.price*$('.js-quantity').val());
                $('.js-remaining').text(json_data.guztira-json_data.emandakoak-$('.js-quantity').val());
            }
        });
    });
    
    $('.js-save').on('click',function (e) {
        console.log('saveButtonClicked!');
        $(document.selling).attr('action', $(e.currentTarget).data("url"));
        document.selling.submit();
    });
    $('.js-back').on('click',function (e) {
        console.log('backButtonClicked!');
        $(document.selling).attr('action', $(e.currentTarget).data("url"));
        document.selling.submit();
    });
});