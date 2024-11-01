;
window.addEventListener('load', function() {
    var button = document.getElementsByClassName('classified-contact');
    if ( 1 > button.length ) {
        return;
    }
    button = button[0].getElementsByTagName( 'button' );
    if ( 1 > button.length ) {
        return;
    }
    button[0].addEventListener( 'click', function() {
        var http_request = false;
        var url = simple_classifieds.ajaxurl + '?action=classified_get_contact_data';
        url += '&id=' + this.dataset.id;
        url += '&_wpnonce=' + this.dataset.nonce;
        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            http_request = new XMLHttpRequest();
            if (http_request.overrideMimeType) {
                http_request.overrideMimeType('text/xml');
            }
        } else if (window.ActiveXObject) { // IE
            try {
                http_request = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    http_request = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {}
            }
        }
        if (!http_request) {
            return false;
        }
        http_request.onreadystatechange = function() {
            if ( 4 === http_request.readyState ) {
                document.getElementsByClassName('classified-contact-wrap')[0].innerHTML = http_request.responseText;
            }
        };
        http_request.open('GET', url, true);
        http_request.send(null);
    }, false);
});
