import '../../css/user/edit.scss';

import $ from 'jquery';

$(document).ready(function(){
    $('.js-save').on('click',function (e) {
        console.log('saveButtonClicked!');
        $(document.user).attr('action', $(e.currentTarget).data("url"));
        document.user.submit();
    });
    
    $('.js-back').on('click',function (e) {
        console.log('cancelButtonClicked!');
        $(document.user).attr('action', $(e.currentTarget).data("url"));
        document.user.submit();
    });

});