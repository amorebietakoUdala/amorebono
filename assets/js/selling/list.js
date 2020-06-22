import '../../css/selling/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'tableexport.jquery.plugin/tableExport';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import 'bootstrap-datepicker';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.es';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.eu';
import 'eonasdan-bootstrap-datetimepicker';
import 'pc-bootstrap4-datetimepicker';

import {createConfirmationAlert} from '../common/alert';

$(document).ready(function(){
	console.log("Selling list view!!!!");

	$('#taula').bootstrapTable({
		cache : false,
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
		pageList: [10,25,50,100],
		sortable: true,
		locale: $('html').attr('lang')+'-'+$('html').attr('lang').toUpperCase(),
	});
	var $table = $('#taula');
	$(function () {
		$('#toolbar').find('select').change(function () {
			$table.bootstrapTable('destroy').bootstrapTable({
			exportDataType: $(this).val(),
			});
		});
	});
	$.extend(true, $.fn.datetimepicker.defaults, {
	  icons: {
		time: 'fa fa-clock-o',
		date: 'fa fa-calendar',
		up: 'fa fa-arrow-up',
		down: 'fa fa-arrow-down',
		previous: 'fa fa-chevron-left',
		next: 'fa fa-chevron-right',
		today: 'fa fa-calendar-check-o',
		clear: 'fa fa-trash',
		close: 'fa fa-times'
	  }
	});	
	$('.js-fromDate').datetimepicker({
		locale: $('html').attr('lang')+'-'+$('html').attr('lang'),
		format: 'YYYY-MM-DD',
	});
	$('.js-toDate').datetimepicker({
		locale: $('html').attr('lang')+'-'+$('html').attr('lang'),
		format: 'YYYY-MM-DD',
	});
	$(document).on('click','.js-delete',function(e){
		e.preventDefault();
		var url = e.currentTarget.dataset.url;
		createConfirmationAlert(url);
	});
});