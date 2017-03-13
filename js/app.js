$(document).ready(function(){

  /* create task new */
  // using ajaxForm with fallbacks for users without JS support
  $('#create-task-new').ajaxForm({
    success: function(data) {
      $('#ajax_msg').css("display", "block").delay(3000).slideUp(300).html(data);
      if(data.match(/success/i)){
        document.getElementById('create-task-new').reset();
      }
    }
  });

  //send submitted admin credentials.
  $('#admin-login').ajaxForm({
    success: function(data) {
      if(data.match(/successful/i)){
        $('#admin').html(data);//replace if all ok
        loadTaskListAdmin();
      }else{
        $('#ajax_msg').css("display", "block").delay(3000).slideUp(300).html(data);
      }
    }
  });

  loadTaskList();

  /* add upload feature */
  var fileInputTextDiv = document.getElementById('file_input_text_div');
  var fileInput = document.getElementById('file_input_file');
  var fileInputText = document.getElementById('file_input_text');

  fileInput.addEventListener('change', changeInputText);
  fileInput.addEventListener('change', changeState);

  function changeInputText() {
    var str = fileInput.value;
    var i;
    if (str.lastIndexOf('\\')) {
      i = str.lastIndexOf('\\') + 1;
    } else if (str.lastIndexOf('/')) {
      i = str.lastIndexOf('/') + 1;
    }
    fileInputText.value = str.slice(i, str.length);
  }

  function changeState() {
    if (fileInputText.value.length != 0) {
      if (!fileInputTextDiv.classList.contains("is-focused")) {
        fileInputTextDiv.classList.add('is-focused');
      }
    } else {
      if (fileInputTextDiv.classList.contains("is-focused")) {
        fileInputTextDiv.classList.remove('is-focused');
      }
    }
  }

});

function loadTaskList() {
  $('#task-list').load('/ajax/', function() {
    $('[data-toggle="tooltip"]').tooltip();
    //load table sorter
    $("#myTable").delay(1000).tablesorter();
    $('#ajax_msg').css("display", "block").delay(3000).slideUp(300).html("Loading task list data...");
    $("#loadFreshList").prop('disabled', true).delay(10000).prop('disabled', false);
  });
}

function loadTaskListAdmin() {
  $('#task-list-admin').load('/ajax/indexadmin', function() {
    //alerting thats all ok.
    $('#ajax_msg').css("display", "block").delay(3000).slideUp(300).html("Loading data");
    //display popups with images
    $('[data-toggle="tooltip"]').tooltip();
    //load table sorter
    $("#myTable2").delay(1000).tablesorter();
  });
}


/*get value of specific cookie*/
function getCookie(n){
  var re = new RegExp(n+'=[^;]+','i');
  return document.cookie.match(re)?document.cookie.match(re)[0].split("=")[1]:null;
}
/*set name=>val cookie */
function setCookie(name, val){// set cookies with specific values
  return document.cookie= name + '=' + val + ';expires='+(new Date(new Date().getTime()+2*86400000).toGMTString())+';path=/';
}

//logout form with replacrd loginpage for admin
function logout() {
  setCookie('admintoken', 0);
  //restores form
  $('#admin').load('/ajax/adminform');
}

function makeElementEditable(div){
  //open input for editing
  div.style.border = "1px solid lavender";
  div.style.padding = "5px";
  div.style.background = "white";
  div.contentEditable = true;
}

//update cells, one by one. safe and fast.
function updateTask(target, fieldname ,taskId){
  //make normal cell
  var data = target.textContent;
  target.style.border = "none";
  target.style.padding = "0";
  target.style.background = "#ececec";
  target.contentRditable = false;

  $.ajax({
    url: '/ajax/update',
    method: 'POST',
    data: {update: data, field: fieldname, id: taskId},
    success: function(data){
      $('#ajax_msg').css("display", "block").delay(3000).slideUp(300).html(data);

    }
  });
}

function deleteTask(taskId){
  if(confirm("Do you really want to delete this task?")){
    $.ajax({
      url: '/ajax/delete',
      method: 'POST',
      data: {id: taskId},
      success: function(data){
        $('#ajax_msg').css("display", "block").delay(3000).slideUp(300).html(data);
      }
    });

    loadTaskListAdmin();
    loadTaskList();

  }
  return false;
}

/* helper for features, not supported in jq1.9+*/
var matched, browser;
jQuery.uaMatch = function( ua ) {
  ua = ua.toLowerCase();

  var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
      /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
      /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
      /(msie) ([\w.]+)/.exec( ua ) ||
      ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
      [];

  return {
    browser: match[ 1 ] || "",
    version: match[ 2 ] || "0"
  };
};

matched = jQuery.uaMatch( navigator.userAgent );
browser = {};

if ( matched.browser ) {
  browser[ matched.browser ] = true;
  browser.version = matched.version;
}

// Chrome is Webkit, but Webkit is also Safari.
if ( browser.chrome ) {
  browser.webkit = true;
} else if ( browser.webkit ) {
  browser.safari = true;
}

jQuery.browser = browser;
