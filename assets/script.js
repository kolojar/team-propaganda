function sendData(e) {
    e.preventDefault()
    var data = {
        email: document.getElementById("email").value,
        subject: document.getElementById("subject").value,
        message: document.getElementById("message").value
    };

    $.ajax({
        method: "POST",
        url: 'https://alba-rosa.cz/school-projects/emailSender/mail.php',
        data: data,
        success: function(response) {
            console.log('Odpověď serveru:', response);
            alert('Data byla úspěšně odeslána na email.' + response);
        },
        error: function(err) {
            console.error(err);
            alert('Došlo k chybě při odesílání dat na email.');
        }
    });
}
