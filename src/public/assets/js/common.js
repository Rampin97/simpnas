feather.replace();

$("form").on('submit', function() {
    $('#cover-spin').show(0);
});

// Highlight the active nav link.
const url = window.location.pathname;
const filename = url.substr(url.lastIndexOf('/') + 1);
$('.nav-item a[href$="' + filename + '"]').addClass("active");

//Prevents resubmit on forms
if (window.history.replaceState){
    window.history.replaceState(null, null, window.location.href);
}

//Slide alert up after 2 secs
$("#alert").fadeTo(6000, 500).slideUp(500);

$("#checkAll").on('click', function() {
    $(':checkbox').each(function() {
        this.checked = this.checked !== true;
    });
});

$("#passwordbox").hide();

$("#encrypt").on('click', function() {
    if ($(this).is(":checked")){
        $("#passwordbox").show();
    }else{
        $("#passwordbox").hide();
    }
});

function checkStatic() {
    if ($("#static").is(":selected")){
        $("#staticSettings").show();
    }else{
        $("#staticSettings").hide();
    }
}

checkStatic();

$("#method").on('click', function() {
    checkStatic();
});


// TODO: da spostare

$("#vpnSettings").hide();

$("#configVpn").on('click', function() {
    if($(this).is(":checked")){
        $("#vpnSettings").show();
    }else{
        $("#vpnSettings").hide();
    }
});
