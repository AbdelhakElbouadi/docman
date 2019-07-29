var currentUser = null;
var numberOfFlow = 1;
var alreadySelected = [];
var removedFlow = [];
var removedSelection = [];
var tmpUrl = null;
// will hold the PDF handle returned by PDF.JS API
var _PDF_DOC;

// PDF.JS renders PDF in a <canvas> element
var _CANVAS = document.getElementById('view-exo');

$(document).ready(function () {
  $('.error').css({'visibility': 'hidden'});
  var navListItems = $('div.setup-panel div a'),
  allWells = $('.setup-content'),
  allNextBtn = $('.nextBtn'),
  allPrevBtn = $('.prevBtn');

  var observer = new MutationObserver(function(mutations) {
    if($('#step-3').is(':visible')){
      showPDF(tmpUrl);
      var desc = $('#description').val();
      $('#desc-card').text(desc);  
    }
  });
  var target = document.querySelector('#step-3');
  observer.observe(target, {
    attributes: true
  });

  allWells.hide();
  allWells.css({'display':'none'});

  navListItems.click(function (e) {
    e.preventDefault();
    var $target = $($(this).attr('href')),
    $item = $(this);

    if ($item.attr('disabled') !== 'disabled') {
      navListItems.removeClass('btn-primary').addClass('btn-default');
      navListItems.addClass('bg-lg');
      $item.addClass('btn-primary');
      $item.removeClass('bg-lg');
      allWells.hide();
      $target.show();
      $target.find('input:eq(0)').focus();
    }
  });
  
  allPrevBtn.click(function(){
    var curStep = $(this).closest(".setup-content"),
    curStepBtn = curStep.attr("id"),
    prevStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a");

    prevStepWizard.removeAttr('disabled').trigger('click');
  });

  allNextBtn.click(function(){
    var curStep = $(this).closest(".setup-content"),
    curStepBtn = curStep.attr("id"),
    nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
    curInputs = curStep.find("input[type='text'],input[type='url']"),
    isValid = true;

    //Remove the error class from all which affected with.
    $('*').removeClass('has-error');
    if($(curStep).attr('id') === 'step-1'){
      var fileInput = document.getElementById('customFile');  
      var textInput = curStep.find("textarea");
      console.log('Before the validation');
      if(fileInput.files.length == 0){
        isValid = false;
        $('#customFile').closest(".custom-file").addClass("has-error");
      }else{
        //application/pdf
        if(fileInput.files.item(0).type !== 'application/pdf'){
          isValid = false;
          $('.error').text('Only PDF file are allowed');
          $('.error').css({'visibility': 'visible'});
          $('#customFile').closest(".custom-file").addClass("has-error");
        }else if(fileInput.files.item(0).size > 10*1024*1024){
          isValid = false;
          $('.error').text('File Size shouldn\'t surpass 10 MB');
          $('.error').css({'visibility': 'visible'});
          $('#customFile').closest(".custom-file").addClass("has-error");    
        }else{
          console.log('Thats pdf file we want');  
        }
      }
      if($(textInput).val() == ''){
        isValid = false;
        $('#description').addClass("has-error");
      }else{
        console.log('there is a description');  
      }
    }else if($(curStep).attr('id') === 'step-2'){
      var selectsDept = curStep.find("select.select-dept");
      var selectsUser = curStep.find("select.select-user");
      var visible = 0, unvisible = 0; 
      var deptValid = false, userValid = false;
      //isValid = false;
      //element appended to the document should have their own visibility
      $(selectsDept).each(function(){
        if($(this).closest('.form-group').is(':visible')){
          visible++;
        }else{
          unvisible++;
        }
      });

      /*console.log("There is visible===>" + visible);
      console.log("There is unvisible=>" + unvisible);*/
      //Cases
      if(!visible && !unvisible){
        isValid = false;
        $('.error').text('You have to provide at least one reviewer');
        $('.error').css({'visibility': 'visible'});
      }else if(!visible && unvisible){
        isValid = false;
        $('.error').text('You have to provide at least one reviewer');
        $('.error').css({'visibility': 'visible'});
      }else{
        $(selectsDept).each(function(){
          if($(this).closest('.form-group').is(':visible')){
            var kids = $(this).children('option');
            $(kids).each(function(){
              //console.log('The Option=>' + $(this).val());
              if(this.selected && this.value !== ''){
                //console.log('There is a selected=>' + $(this).val());
                deptValid = true;
                $(this).parent().removeClass('has-error');
                var usersSelect = $(this).parent().next('select.select-user');
                var usersChild = $(usersSelect).children('option');
                $(usersChild).each(function(){
                  if(this.selected && this.value !== ''){
                    userValid = true;
                    $(this).parent().removeClass('has-error');
                    return false;
                  }else{
                    userValid = false;
                  }
                });
                if(!userValid){
                  $('.error').text('You have to choose at least one user from the department if it is empty delete it and choose another one or contact your admin to add new users to this department');
                  $('.error').css({'visibility': 'visible'}); 
                  $(usersSelect).addClass('has-error');
                }
                return false;
              }else{
                deptValid = false;
              }  
            });
            //If the dept is not valid
            if(!deptValid){
              $('.error').text('You have to choose a department and choose the user from it');
              $('.error').css({'visibility': 'visible'});
              $(this).addClass('has-error');
            }  
            //Validate according to dept and users
            deptValid = deptValid & userValid;
            isValid = isValid & deptValid;
          }else{
            console.log("There is a hidden dept selection=>" + $(this).attr("name"));
          }
        });
      }
    }else{
    }

    if(isValid){
      nextStepWizard.removeAttr('disabled').trigger('click');
      $('.error').css({'visibility': 'hidden'});
    }
  });

$('div.setup-panel div a.btn-primary').trigger('click');
});

// load the PDF
function showPDF(pdf_url) {
  // Loaded via <script> tag, create shortcut to access PDF.js exports.
  //var pdfjsLib = window['js/pdf'];

  // The workerSrc property shall be specified.
  pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';
  pdfjsLib.getDocument({ url: pdf_url }).then(function(pdf_doc) {
    _PDF_DOC = pdf_doc;

    // show the first page of PDF
    showPage(1);

    // destroy previous object url
    URL.revokeObjectURL(tmpUrl);
  }).catch(function(error) {
    // error reason
    alert(error.message);
  });
}

// show page of PDF
function showPage(page_no) {
  _PDF_DOC.getPage(page_no).then(function(page) {
    // set the scale of viewport
    //var scale_required = _CANVAS.width / page.getViewport(1).width;
    var _CANVAS = document.getElementById("view-exo");

    // get viewport of the page at required scale
    var viewport = page.getViewport(1);

    // set canvas height
    _CANVAS.height = viewport.height;
    _CANVAS.width = viewport.width;

    var renderContext = {
      canvasContext: _CANVAS.getContext('2d'),
      viewport: viewport
    };

    // render the page contents in the canvas
    page.render(renderContext);
  });
}

function addReviewer(){
  var container = $('#workflow-dept');
  var row = $('<div>');
  //row.addClass('row');
  var div = $('<div>');
  div.addClass('form-group');
  var select = $('<select></select>');
  select.addClass('custom-select select-conv select-dept col-md-5');
  select.attr('name', 'flow' + (numberOfFlow + 1));
  //select.attr('required', 'required');
  var initOpt = $('<option></option>').attr('value', '');
  initOpt.text('Choose a department');
  select.append(initOpt);

  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    method:"POST",
    url: "getDeptsAjax",
    success: function(data, status, xhr){
      if(numberOfFlow < data.length){
        console.log("Data=>" + JSON.stringify(data));
        var array = data;
        for(var i = 0; i < array.length; i++){
          var obj = array[i];
          var option = $('<option></option>');
          option.attr('value', obj.id);
          option.text(obj.name);
          select.append(option); 
        }
        row.append(select);
        div.append(row);
        container.append(div);
        numberOfFlow++;
        //The number of flow that we have actually
        $('#flow').val(numberOfFlow);
      }else if(numberOfFlow >= data.length && (numberOfFlow - removedFlow.length < data.length)){
        //There is the case when there are removedFlow that can be restored.
        //var first = removedFlow.shift();
        var array = data;
        for(var i = 0; i < array.length; i++){
          var obj = array[i];
          var option = $('<option></option>');
          option.attr('value', obj.id);
          option.text(obj.name);
          select.append(option); 
        }
        row.append(select);
        div.append(row);
        container.append(div);
        numberOfFlow++;
        //The number of flow that we have actually
        $('#flow').val(numberOfFlow);
      }else{
        //console.log('Flow=>' + numberOfFlow + " <>DataLength=>" + data.length);   
      }
    },
    error: function(xhr, status, error){
      console.log("XHR=>" + JSON.stringify(xhr));
      console.log("Status=>" + status);
      console.log("Error=>" + error);
    },
    dataType:"json"
  });
}


function removeReviewer(id){
  $('#' + id).css({'display': 'none'});
  var actualFlow = id.substr(4,1);
  removedFlow.push(actualFlow);
  var selectedDept = $('#' + id).find('select.select-dept').val();
  removedSelection.push(selectedDept);
  removeElement(alreadySelected, selectedDept);
}

function removeElement(array, elem) {  
  var index = array.indexOf(elem);
  if (index > -1) {
    array.splice(index, 1);
  }
}


