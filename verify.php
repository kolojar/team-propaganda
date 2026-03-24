<?php
session_start();
if (isset($_SESSION["userId"])) {
    header("userPanel.html");
    exit();
}
if (isset($_POST["login"])) {
    $_SESSION["email"] = $_POST["login"];
} else if (isset($_POST["email"]) && isset($_POST["name"]) && isset($_POST["surname"])) {
    $_SESSION["email"] = $_POST["email"];
    $_SESSION["name"] = $_POST["name"];
    $_SESSION["surname"] = $_POST["surname"];
    verify($_POST["email"]);
} else {
    header('Location: ./loginForm.html');
    exit;
}

function verify($email)
{
    $code = rand(10000, 99999);
    //echo $code;
    $_SESSION["verify"] = $code;
    $message = str_replace("\$code", $code, file_get_contents("./assets/codeEmailTemplate.html"));
    sendMail($email, "Ověření Emailu", $message);
}

function sendMail($email, $subject, $message)
{
    $url = "https://alba-rosa.cz/school-projects/emailSender/mail.php";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $data = [
        "email" => $email,
        "subject" => $subject,
        "message" => $message
    ];

    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

    $resp = curl_exec($curl);
    echo $resp;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        /* Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Card Container */
        .card {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Icon Styling */
        .icon-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background-color: #eff6ff;
            border-radius: 16px;
            margin-bottom: 24px;
        }

        .icon-container svg {
            width: 32px;
            height: 32px;
            color: #2563eb;
        }

        /* Typography */
        h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        p.description {
            font-size: 1rem;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 32px;
        }

        /* OTP Inputs */
        .otp-group {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .otp-field {
            width: 54px;
            height: 64px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background-color: #f8fafc;
            color: #1f2937;
            transition: all 0.2s ease;
        }

        .otp-field:focus {
            border-color: #3b82f6;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
            transform: translateY(-2px);
        }

        /* Button Styling */
        .verify-btn {
            width: 100%;
            background-color: #2563eb;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            padding: 16px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .verify-btn:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .verify-btn:active {
            transform: translateY(1px);
        }

        /* Footer Section */
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
        }

        .footer p {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 12px;
        }

        .resend-link {
            font-size: 0.875rem;
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
            transition: color 0.2s;
        }

        .resend-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* Responsive Adjustments */
        @media (max-width: 400px) {
            .otp-field {
                width: 45px;
                height: 56px;
            }

            .card {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="icon-container">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                </path>
            </svg>
        </div>

        <h1>Verify email</h1>
        <p class="description">Enter the 5-digit code we sent to your inbox to continue.</p>

        <form id="otp-form">
            <div class="otp-group">
                <input type="text" name="digit1" pattern="[0-9]*" inputmode="numeric" maxlength="1" required
                    class="otp-field" autofocus>
                <input type="text" name="digit2" pattern="[0-9]*" inputmode="numeric" maxlength="1" required
                    class="otp-field">
                <input type="text" name="digit3" pattern="[0-9]*" inputmode="numeric" maxlength="1" required
                    class="otp-field">
                <input type="text" name="digit4" pattern="[0-9]*" inputmode="numeric" maxlength="1" required
                    class="otp-field">
                <input type="text" name="digit5" pattern="[0-9]*" inputmode="numeric" maxlength="1" required
                    class="otp-field">
            </div>

            <button type="submit" class="verify-btn">Verify Code</button>
        </form>

        <div class="footer">
            <p>Didn't receive the email?</p>
            <a href="#" class="resend-link">Resend Code</a>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        const fields = document.querySelectorAll('.otp-field');

        fields.forEach((field, index) => {
            // Handle entering a digit
            field.addEventListener('input', (e) => {
                const value = e.target.value;
                // Auto-advance cursor
                if (value && index < fields.length - 1) {
                    fields[index + 1].focus();
                }
            });

            // Handle backspace logic
            field.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !field.value && index > 0) {
                    fields[index - 1].focus();
                } else if (e.key === 'Enter' && index === 4) {
                    e.preventDefault();
                    submit(e)
                }
            });

            // Handle pasting the code
            field.addEventListener('paste', (e) => {
                e.preventDefault();
                // Get the pasted data and filter for numbers only
                const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');

                if (pasteData.length > 0) {
                    // Fill each field starting from the one being pasted into
                    for (let i = 0; i < pasteData.length; i++) {
                        const targetIndex = index + i;
                        if (targetIndex < fields.length) {
                            fields[targetIndex].value = pasteData[i];
                        }
                    }

                    // Focus the next available empty field or the last field
                    const nextFocusIndex = Math.min(index + pasteData.length, fields.length - 1);
                    fields[nextFocusIndex].focus();
                }
            });

            // Prevent non-numeric characters from being typed
            field.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });

        // Simple form submission feedback
        document.getElementById('otp-form').addEventListener('submit', (e) => {
            submit(e)
        })

        function submit(e) {
            e.preventDefault();
            const btn = document.querySelector('.verify-btn');
            btn.textContent = 'Verifying...';
            btn.style.opacity = '0.7';
            //btn.disabled = true;

            // Collect the full code
            let verificationCode = "";
            fields.forEach(field => {
                verificationCode += field.value;
            });

            let data = {
                code: Number(verificationCode)
            };
            $.ajax({
                url: 'https://alba-rosa.cz/school-projects/emailSender/codeVerify.php',
                method: "POST",
                data: data,
                success: function(response) {
                    console.log('Odpověď serveru:', response);
                    if (response == "true") {
                        btn.textContent = 'Verified!';
                        btn.style.backgroundColor = '#10b981';
                        btn.style.opacity = '1';
                        console.log("hell yeah")
                        setTimeout(() => {
                            console.log("red") /*redirect*/
                        }, 2000)
                    } else {
                        btn.textContent = 'Verify Code';
                        btn.style.backgroundColor = '#b91032';
                        btn.style.opacity = '1';
                        console.log("hell nah")
                        // tell them
                    }
                },
                error: function(err) {
                    console.error(err);
                    alert('Došlo k chybě při odesílání dat.');
                }
            });
        };
    </script>
</body>

</html>
