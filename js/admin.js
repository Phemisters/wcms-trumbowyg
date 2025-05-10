
 
$(function() {
    // Fix for admin settings correctly removing the active class in the menu
    $(".nav-tabs li.nav-item a.nav-link").click(function() {
        $(".nav-tabs li.nav-item a.nav-link").removeClass('active');
    });
	
    var editElements = {};
    let timeoutSave;

$(document).ready(function() {
	console.info("document ready");
$('.editable').trumbowyg({
removeformatPasted: true,

	btns: [
	['viewHTML'],
        ['undo', 'redo'], // Only supported in Blink browsers
        ['formatting'],
        ['strong', 'em', 'del', 'preformatted'],
        ['superscript', 'subscript'],
	['fontfamily', 'fontsize', 'foreColor', 'backColor'],
        ['link'],
        ['insertImage'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
        ['unorderedList', 'orderedList'],
        ['table', 'tableCellBackgroundColor', 'tableBorderColor'],
        ['horizontalRule'],
        ['removeformat'],
	['upload'],
        ['fullscreen']
	],
	plugins: {
		upload: {
            serverPath: 'plugins/wcms-trumbowyg/files.php',	    // Some upload plugin options, see details below
            error: function() {console.error("Error callback");},
		},
		table: {
			rows: 10,
			columns: 12,
      dropdown :
[
    {
        title: 'tableRows',
        buttons: [
            'tableAddHeaderRow',
            'tableAddRowAbove',
            'tableAddRow',
            'tableDeleteRow',
        ],
    },
    {
        title: 'tableColumns',
        buttons: [
            'tableAddColumnLeft',
            'tableAddColumn',
            'tableDeleteColumn',
        ],
    },
    {
        title: 'tableVerticalAlign',
        buttons: [
            'tableVerticalAlignTop',
            'tableVerticalAlignMiddle',
            'tableVerticalAlignBottom',
        ],
    },
    {
        title: 'tableOthers',
        buttons: [
            // Cell merge/split
            'tableMergeCells',
            'tableUnmergeCells',
            'tableDestroy',
        ]
    }
]
		},
        resizimg: {
            minSize: 64,
            step: 16,
        }
	    }
})
.on('tbwchange', function(){editElements[$(this).attr('id')] = $('#'+$(this).attr('id')).trumbowyg('html');})
.on('tbwchange', function(){clearTimeout();})
.on('tbwblur', function(){const that = $(this); timeoutSave = setTimeout(function () { saveData(that) }, 200); });

});


// Perform other work here ...
 
    function saveData(editor) {
	    console.info("saveData called");
        if (editElements[editor.attr('id')]!=undefined) {
            // Confirmation popup for saving changes (set in the database)
            if (typeof saveChangesPopup !== 'undefined' && saveChangesPopup && !confirm('Save new changes?')) {
                alert("Changes are not saved, you can continue to edit or refresh the page.");
                return
            }

            var id = editor.attr('id');
            var content = editElements[editor.attr('id')];
            var target = (editor.attr('data-target')!=undefined) ? editor.attr('data-target'):'pages';
            editElements[editor.attr('id')] = undefined;
            $.post("",{
                fieldname: id,
                content: content,
                target: target,
                token: token,
            })
              .done(function() {
                  $("#save").show();
                  $('#save').delay(100).fadeOut();
              });
        }
    }
});
