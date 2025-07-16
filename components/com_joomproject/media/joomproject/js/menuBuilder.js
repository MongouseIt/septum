$(document).ready(function(){
    $('#wrapper').removeClass('open').addClass('closed');

    // add Class to distinguish if is parent or child
    $('div.sidebar-nav ul.nav > li').each(function(){

        if($(this).find('i').hasClass('isParent') == true){
            $(this).addClass('isParent');
        }else{
            $(this).addClass('isChild');
        }

    });

    // find and hide children

    $('div.sidebar-nav ul.nav > li.isParent').each(function(){

        let i = 0;

        $(this).nextUntil('li.isParent').each(function(){
            $(this).hide();
            i++;
        });

        if(i > 0){
            $(this).find('a').append('<span class="fas fa-angle-down me-3 subMenuControl"></span>');
        }


    })

    // find parent of active child
    $('.isChild.active').prevUntil('.isParent').prev().last().addClass('active').find('span.fa-angle-down').toggleClass('fa-angle-up');

    $('.isParent.active').nextUntil('li.isParent').each(function(){

        $(this).show('fast');

    })

    // open or close parent on click
    $('.isParent').click(function(e){

        let i = 0;

        $(this).nextUntil('li.isParent').each(function(){
            $(this).slideToggle('fast');
            i++
        });

        if(i > 0){
            e.preventDefault();
        }

        $(this).find('.fa-angle-down,.fa-angle-up').toggleClass('fa-angle-down').toggleClass('fa-angle-up');

    });

});
