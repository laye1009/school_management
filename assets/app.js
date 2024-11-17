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
var adminStudents = null
var adminProfsTab = null
var profToDel = null
//console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.addEventListener('turbo:load', function() {
    //document.addEventListener('turbo:load', function(){})
    // Your code goes here
    $('#classeSelect').select2();
    
    displayDatatable($('#classeSelect option:first').val())

    $('#classeSelect').on('select2:select', function(e) {
        if ($.fn.DataTable.isDataTable('#schoolDatatable')) {
            $('#schoolDatatable').DataTable().destroy();
        }
        displayDatatable($('#classeSelect').val())
    
    });

    //ajout des accordéon
    $('.accordion-header').click(function() {
        // Toggle (ouverture/fermeture) du contenu de l'accordéon associé
        $(this).next('.accordion-content').slideToggle();
        
        // Fermer les autres accordéons si un autre est ouvert
        $('.accordion-content').not($(this).next()).slideUp();
    });
    adminStudents = $('#adminGestionEleve').DataTable()
    adminProfsTab = $('#adminGestionProfs').DataTable()

    $('#adminSelectProfClass').select2({
        //placeholder: "Sélectionnez des classes",
        //allowClear: true
    })
    // prof ui
    $('#schoolDatatable').on('click', 'i.studNoteDetails', function() { //déclencher un évènement depuis une cellulle datatable
        /*$('#schoolModal').fadeIn();
        $('#newNote').val($(this).data('note'))*/
        studentId = $(this).data('studentid')
        displayAndAddStudentMark($(this).data('studentid'), $(this).data('matiere'))

    });

    $("#adminGestionEleve").on('click','i#adminStudentsReports', function() {
        if ($.fn.DataTable.isDataTable('#adminStudReportsTab')) {
            $('#adminStudReportsTab').DataTable().destroy();
        }
        $('#adminStuReportsModal').fadeIn()
        studentReportsTab($(this).data('studentid'))
    })

    $('#adminEditProfButton').on('click', function() {
        $('#adminEditProfForm').submit()
    })
    
    $('#adminGestionProfs').on('click', 'i#adminProfs', function() { 
        let cell=$(this)
        let row = adminProfsTab.row(cell.closest('tr'))
        var rowData = row.data();
        $('#adminProfEdit').fadeIn()
        $('#profNom').val(rowData[0])
        $('#profPrenom').val(rowData[1])
        $('#adminSelectProfClass').val(rowData[2])
        $('#adminSelectedProf').text(rowData[0]+" "+rowData[1])
        let profClasses = rowData[3].split(";")
        $('#adminSelectProfClass').val(profClasses).trigger('change')
    })
    
    $('#adminGestionEleve').on('click', 'i#adminStudents', function() {
        let cell=$(this)
        let row = adminStudents.row(cell.closest('tr'))
        var rowData = row.data();
        $('#adminStudentList').fadeIn()
        $('#nom').val(rowData[0])
        $('#prenom').val(rowData[1])
        $('#classe').val(rowData[2])
        $('#adminSelectedStudent').text(rowData[0]+" "+rowData[1])
    
    })
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

    $('#profStudentNotes').on('click', 'i.profListNotes', function() {
        profAddMark()
    })

    $('#ajoutNote').on('click', function() {
        displayAddMarkFrom(nomPrenom)
    })

    $('#confirmNoteEdition').on('click', function() {
        submitMarkEdition(studentId)
    })
    $('.dismissModal').each(function() {
        dismissModals($(this))
    })
    $('.cAdminProfDel').each(function() {
        $(this).on('click', function() {
            profToDel = $(this).data('profid')
            console.log(profToDel + " del 1") 
            $('#adminConfirmProfDel').fadeIn()
        })
    })
    $('#confirmProfDel').on('click', function() {
        deleteAProf(profToDel)
    })
});
document.addEventListener('DOMContentLoaded', function() {
})

const deleteAProf = function(profId) {
    $('#adminConfirmProfDel').fadeOut()
    $.ajax({
        url: '/admin/delete/prof',
        type: 'POST',
        data: {'profId': profId},
        success: function(data) {
            console.log(data['data'])
            $('#deleteMessage').css('display', 'block')
            $('#deleteMessage').text(data['data'])
            setTimeout(() => {
                $('#deleteMessage').css('display', 'none')
            }, 1500); 
            let cell=$("#prof"+profId)

            let row = adminProfsTab.row(cell.closest('tr'))
            adminProfsTab.row(row).remove().draw();
            //var rowData = row.data();
        }
    })
}

const studentReportsTab = function(studentId) {
    $('#adminStudReportsTab').DataTable({
        "ajax": {
            url:"/display/report",
            type:"POST",
            data:{'studentId': studentId}
        }, // Path to your AJAX data route
        buttons: [
            {
                extend: 'pdfHtml5', // Export to PDF button
                text: 'Export to PDF',
                orientation: 'portrait', // or 'landscape' for horizontal layout
                pageSize: 'A4', // Choose the page size (e.g., A4, Letter)
                title: 'Data Export', // PDF Title
                exportOptions: {
                    columns: ':visible' // Export only visible columns
                }
            }
        ]
    });
}

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

const displayProfStudentNotesDatatable = function(matiereId, studentId) {
    $('#profStudentNotesModalBody').DataTable({
        "ajax": {
            url:"/student/notes/list",
            type:"POST",
            data:{'matiere': matiereId, 'student': studentId}
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

const displayAndAddStudentMark = function(studentId, matiere) {
    if ($.fn.DataTable.isDataTable('#profStudentNotesModalBody')) {
        $('#profStudentNotesModalBody').DataTable().destroy();
    }
    
    $('#profStudentNotes').fadeIn()
    //let table
    $.ajax({
        url:"/controle/a_ajouter",
        type:'POST',
        data: {'studentId': studentId},
        success: function(data) {
            console.log(data)
            Object.keys(data).forEach(async function(element){
                await $('#controleTypes').empty()
                let libelle = ""
                if (data[element] == 'c1') {
                    libelle ="Contrôle 1"
                } else if (data[element] == 'c2') {
                    libelle ="Contrôle 2"
                } else {
                    libelle ="Contrôle final"
                }
                
                $('#controleTypes').append(`<option value=${data[element]}>${libelle}</option>`)
            })
        }
    })
    let tabprofStudentNotesTable = displayProfStudentNotesDatatable(matiere, studentId)
    $('#profStudentNotesModalBody').append(tabprofStudentNotesTable)
    $('#displayedStudent2').text(`${nomPrenom}`)
}

const profAddMark = function() {
    $('#profStudentNotes').fadeOut()
    $('#schoolModal').fadeIn();
    $('#controleTypes').select2();
    $('#newNote').val($(this).data('note'))
    $('#controleTypes').val($(this).data('controle'));
    $('#appreciation').val($(this).data('appreciation'))
    $('#matiere').val($(this).data('matiere'))
}


const submitMarkEdition = function(studentId) {
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
}

const dismissModals = function(thisClose) {
    thisClose.on('click', function() {
        $('#schoolModal').fadeOut();
    })
    thisClose.on('click', function() {
        $('#profStudentNotes').fadeOut();
    })
    thisClose.on('click', function() {
        $('#adminStudentList').fadeOut()
    })
    
    thisClose.on('click', function() {
        $('#adminProfEdit').fadeOut()
    })
    
    thisClose.on('click', function() {
        $('#adminStuReportsModal').fadeOut()
    })

    thisClose.on('click', function() {
        $('#adminConfirmProfDel').fadeOut()
    })
}

// ajoute d'une nouvelle note, affichage du formulaire
const displayAddMarkFrom = function(nomPrenom) {
    $('#profStudentNotes').fadeOut()
    $('#schoolModal').fadeIn();
    $('#newNote').val("")
    $('#controleTypes').val("");
    $('#appreciation').val("")
    $('#controleSelect').css('display', 'block')
    $('#displayedStudent').text(`${nomPrenom}`)
}

