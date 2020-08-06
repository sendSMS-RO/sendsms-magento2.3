document.onreadystatechange = function () {
    console.log(document.readyState);
    var max_chars = 160;
    var char_counts = document.getElementsByClassName('sendsms-char-count');
    if (document.readyState === "complete") {
        for (var i = 0; i < char_counts.length; i++) {
            var msgTxt = document.createElement('p');
            // find textfield
            var char_textfield = char_counts[i].parentNode.getElementsByClassName('sendsms-char-count')[0];
            // set max length
            char_textfield.setAttribute('maxlength', max_chars);
            char_textfield.after(msgTxt);
            // count remaining characters
            if (char_textfield.value.length !== typeof undefined) {
                msgTxt.innerHTML = max_chars - char_textfield.value.length + ' caractere ramase';
            }
            // add event
            char_textfield.onkeyup = function () {
                var text_length = this.value.length;
                var text_remaining = max_chars - text_length;
                this.nextSibling.innerHTML = text_remaining + ' caractere ramase';
            };
        }
    }
};