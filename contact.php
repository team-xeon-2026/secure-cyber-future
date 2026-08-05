<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8a2be2;
            --secondary-color: #1e90ff;
            --dark-bg: #0a0a20;
            --text-light: #ffffff;
            --text-muted: #b0b0cc;
        }

        body, html {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            overflow-x: hidden;
        }

        .contact-wrapper {
            padding: 40px 0;
            position: relative;
            z-index: 1;
        }

        .contact-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .contact-info {
            background: rgba(138, 43, 226, 0.1);
            padding: 40px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Contact Info Boxes */
        .contact-info-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .contact-info-item::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, 
                transparent 0%, 
                rgba(138, 43, 226, 0.1) 50%, 
                transparent 100%);
            transform: rotate(45deg);
            animation: shine 6s infinite;
        }

        .contact-info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(138, 43, 226, 0.2);
            border-color: rgba(138, 43, 226, 0.3);
        }

        @keyframes shine {
            0% { transform: rotate(45deg) translateX(-100%); }
            100% { transform: rotate(45deg) translateX(100%); }
        }

        .contact-info .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.3);
        }

        .contact-info .icon i {
            color: var(--text-light);
            font-size: 24px;
        }

        .contact-info h3 {
            color: var(--text-light);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .contact-info p {
            color: var(--text-muted);
            margin-bottom: 5px;
            line-height: 1.7;
        }

        .contact-info a {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            font-weight: 600;
        }

        .contact-form {
            padding: 40px;
            background: rgba(255, 255, 255, 0.02);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: var(--text-light);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25);
            color: var(--text-light);
        }

        .form-control::placeholder {
            color: rgba(176, 176, 204, 0.7);
        }

        .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25);
            color: var(--text-light);
        }

        textarea.form-control {
            min-height: 120px;
        }

        .btn-submit {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: var(--text-light);
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(138, 43, 226, 0.4);
        }

        .floating-element {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            opacity: 0.6;
            filter: blur(30px);
            z-index: -1;
            animation: pulse 4s ease-in-out infinite;
        }

        .floating-element-1 {
            width: 300px;
            height: 300px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element-2 {
            width: 200px;
            height: 200px;
            bottom: 10%;
            right: 5%;
            animation-delay: 1s;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.6;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.4;
            }
            100% {
                transform: scale(1);
                opacity: 0.6;
            }
        }

        .alert {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border-color: rgba(40, 167, 69, 0.3);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="container contact-wrapper">
        <div class="floating-element floating-element-1"></div>
        <div class="floating-element floating-element-2"></div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="contact-box row">
                    <!-- Contact Info -->
                    <div class="col-md-4 contact-info">
                        <div class="contact-info-item">
                            <div class="icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3>Email</h3>
                            <p>Send an email, we're always ready to assist.</p>
                            <a href="mailto:Support@Lockbyte.Com">Support@Lockbyte.Com</a>
                        </div>

                        <div class="contact-info-item">
                            <div class="icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <h3>Phone</h3>
                            <p>Call us now, expert help is a dial away.</p>
                            <a href="tel:+478958936">+47 895-8936</a>
                        </div>

                        <div class="contact-info-item">
                            <div class="icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <h3>Global</h3>
                            <p>Based in Detroit, ready to assist you.</p>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="col-md-8 contact-form">
                        <?php
                        $success = false;
                        $error = '';

                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            $name = $_POST['name'] ?? '';
                            $email = $_POST['email'] ?? '';
                            $phone = $_POST['phone'] ?? '';
                            $message = $_POST['message'] ?? '';
                            $companyName = $_POST['companyName'] ?? '';
                            $industry = $_POST['industry'] ?? '';

                            $errors = [];
                            if (empty($name)) $errors[] = "Name is required";
                            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email is required";
                            if (empty($message)) $errors[] = "Message is required";

                            if (empty($errors)) {
                                $success = true;
                            } else {
                                $error = implode('<br>', $errors);
                            }
                        }
                        ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Thank you for your message! We'll get back to you soon.
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">Enter your name</label>
                                <input type="text" class="form-control" name="name" placeholder="Enter your name" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" placeholder="Phone">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" name="message" placeholder="Message" required></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" name="companyName" placeholder="Company Name">
                            </div>

                            <div class="form-group">
                                <label class="form-label">What industry are you in?</label>
                                <select class="form-select" name="industry">
                                    <option value="">Select one</option>
                                    <option value="tech">Technology</option>
                                    <option value="finance">Finance</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="retail">Retail</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-submit">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>