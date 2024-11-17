import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
var studentId = null 
var nomPrenom = null

//console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.addEventListener('DOMContentLoaded', function() {
    // Your code goes here
    $('#classeSelect').select2();
    
    displayDatatable($('#classeSelect option:first').val())

    $('#classeSelect').on('select2:select', function(e) {
        if ($.fn.DataTable.isDataTable('#schoolDatatable')) {
            $('#schoolDatatable').DataTable().destroy();
        }
        displayDatatable($('#classeSelect').val())
    
    });
});

var previousOpened = null
$('.matiere').on('click', function(event) {
    let clickedMatiere = $(this).data('matiere')
    $('#'+clickedMatiere).slideToggle()
    if (previousOpened && clickedMatiere != previousOpened) {
        $('#'+previousOpened).slideUp()
    }
    previousOpened = clickedMatiere;
    if ($('#'+clickedMatiere).length == 0) {
        if ($(this).find('.missingNoteInfo').length == 0) {
            $(this).append(
                '<div class="missingNoteInfo"></div><span class="missingNote">Vous n\'avez pas encore de note pour cette matière</span></div>'
            )
        } 
    }
})

const displayDatatable = function(classe) {
    $('#schoolDatatable').DataTable({
        "ajax": {
            url:"/professor/marks/management",
            type:"POST",
            data:{'classe': classe}
        }, // Path to your AJAX data route
        "columnDefs": [
            { 
                targets: -1,
                render: function(data, type, row) {
                    nomPrenom = row.nomPrenom
                    var icon = 'class="fas fa-pencil text-gray-500 hover:text-gray-700 cursor-pointer text-xs studNoteDetails"'
                    data = ""
                    data += "<span><i data-studentid='"+row.studentId+"' data-matiere='"+row.matiere+"' data-note='"+row.note+"' "+icon+"></i></span>"
                    return data
                }
            }
        ]
    });
}

const displayProfStudentNotesDatatable = function(matiereLibelle, studentId) {
    $('#profStudentNotesModalBody').DataTable({
        "ajax": {
            url:"/student/notes/list",
            type:"POST",
            data:{'matiere': matiereLibelle, 'student': studentId}
        }, 
        "columnDefs": [
            { 
                targets: 1,
                render: function(data, type, row) {
                    var icon = 'class="fas fa-pencil text-gray-500 hover:text-gray-700 cursor-pointer text-xs profListNotes"'
                    //data += "<span><i "+icon+"></i></span>"
                    data += `<span><i id='profListNotes' data-controle='${row[1]}' data-studentid='${row.studentId}' 
                    data-matiere='${row.matiere}' data-appreciation='${row[3]}' data-note='${row.note}' ${icon}></i></span>`
                    return data
                }
            }
        ]
    });
}

$('#schoolDatatable').on('click', 'i.studNoteDetails', function() { //déclencher un évènement depuis une cellulle datatable
    /*$('#schoolModal').fadeIn();
    $('#newNote').val($(this).data('note'))
    studentId = $(this).data('studentid')*/
    studentId = $(this).data('studentid')
    if ($.fn.DataTable.isDataTable('#profStudentNotesModalBody')) {
        $('#profStudentNotesModalBody').DataTable().destroy();
    }
    
    $('#profStudentNotes').fadeIn()
    //let table
    console.log("matiere"+$(this).data('matiere'))
    let tabprofStudentNotesTable = displayProfStudentNotesDatatable($(this).data('matiere'), studentId)
    $('#profStudentNotesModalBody').append(tabprofStudentNotesTable)
    $('#displayedStudent2').text(`${nomPrenom}`)
});

$('#profStudentNotes').on('click', 'i.profListNotes', function() {
    $('#profStudentNotes').fadeOut()
    $('#schoolModal').fadeIn();
    $('#controleTypes').select2();
    $('#newNote').val($(this).data('note'))
    $('#controleTypes').val($(this).data('controle'));
    $('#appreciation').val($(this).data('appreciation'))
    $('#matiere').val($(this).data('matiere'))
})
$('#confirmNoteEdition').on('click', function() {
    console.log($(this))
    console.log("matiere &"+$('#matiere').val())
    $.ajax({
        url: '/edit/note',
        type:'POST',
        data:{'studentId': studentId, 
            'newNote': $('#newNote').val(), 
            'matiere': $('#matiere').val(),
            'controle': $('#controleTypes').val(),
            'appreciation': $('#appreciation').val()
        },
        success: function(data) {
            console.log(data)
        }
    })
    $('#schoolModal').fadeOut();
})
$('.dismissModal').each(function() {
    $(this).on('click', function() {
        $('#schoolModal').fadeOut();
    })
    $(this).on('click', function() {
        $('#profStudentNotes').fadeOut();
    })
})
// ajoute d'une nouvelle note
$('#ajoutNote').on('click', function() {
    $('#profStudentNotes').fadeOut()
    $('#schoolModal').fadeIn();
    $('#newNote').val("")
    $('#controleTypes').val("");
    $('#appreciation').val("")
    $('#controleSelect').css('display', 'block')
    $('#displayedStudent').text(`${nomPrenom}`)
    /*$.ajax({ // abandon de chercher toutes la matiere. la matière est celle enseignée par le prof connecté
        url : "/matiere/list",
        type: "POST",
        data: {"classeId": $('#classeSelect').val()},
        success: function(data) {
            Object.keys(data).forEach(function(element) {
                console.log(data)
                //let option = `<option val='${element}'>${element}</option>`
                //$('#matiereSelect').append(option)

            })

        }
    })*/

})
