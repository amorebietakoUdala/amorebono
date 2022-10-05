import '../../css/selling/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'tableexport.jquery.plugin';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export';
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import tempusDominus from '@eonasdan/tempus-dominus';
import customDateFormat from '@eonasdan/tempus-dominus/dist/plugins/customDateFormat';

//import '@eonasdan/tempus-dominus/dist/plugins/customDateFormat';

import { createConfirmationAlert } from '../common/alert';

$(document).ready(function() {
    let current_locale = $('html').attr('lang') + '-' + $('html').attr('lang').toUpperCase();
    $('#taula').bootstrapTable({
        cache: false,
        showExport: true,
        exportTypes: ['excel'],
        exportDataType: 'all',
        exportOptions: {
            fileName: "users",
            ignoreColumn: ['options']
        },
        showColumns: false,
        pagination: true,
        search: true,
        striped: true,
        sortStable: true,
        pageSize: 10,
        pageList: [10, 25, 50, 100],
        sortable: true,
        locale: current_locale,
    });
    var $table = $('#taula');
    $(function() {
        $('#toolbar').find('select').change(function() {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val(),
            });
        });
    });
    
    tempusDominus.extend(customDateFormat);
    const options = {
      display: {
        buttons: {
          close: true,
          clear: true,
        },
        components: {
          decades: false,
          year: true,
          month: true,
          date: true,
          clock: false,
        },
      },
      localization: {
        locale: current_locale,
        dayViewHeaderFormat: { month: 'long', year: 'numeric' },
        format: 'yyyy-MM-dd',
      },
    };
    let datepicker1 = new tempusDominus.TempusDominus(document.getElementById('selling_search_form_fromDate'), options);
    let datepicker2 = new tempusDominus.TempusDominus(document.getElementById('selling_search_form_toDate'), options);

    $(document).on('click', '.js-delete', function(e) {
        e.preventDefault();
        var url = e.currentTarget.dataset.url;
        createConfirmationAlert(url);
    });
});