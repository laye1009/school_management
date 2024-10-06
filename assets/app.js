import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

//console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

var previousOpened = null
$('.matiere').on('click', function(event) {
    let clickedMatiere = $(this).data('matiere')
    $('#'+clickedMatiere).slideToggle()
    if (previousOpened && clickedMatiere != previousOpened) {
        $('#'+previousOpened).slideUp()

    }
    event.stopPropagation()
    previousOpened = clickedMatiere;
   
    
    
    /*if (clickedMatiere != previousOpenedNoteDetail) {
        $('#'+previousOpenedNoteDetail).slideToggle()
    }
    previousOpenedNoteDetail = clickedMatiere
    console.log(previousOpenedNoteDetail)*/
})