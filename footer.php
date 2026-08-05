<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.C.F Footer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes glowPulse {
            0%, 100% { box-shadow: 0 0 5px rgba(168, 85, 247, 0.3); }
            50% { box-shadow: 0 0 15px rgba(168, 85, 247, 0.6); }
        }

        .section-title::before {
            content: '•';
            color: #8A2BE2;
            margin-right: 10px;
            font-weight: bold;
        }

        .footer-link {
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
        }

        .footer-link-icon {
            color: #8A2BE2;
            margin-right: 10px;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .footer-link:hover .footer-link-icon {
            opacity: 1;
        }

        .footer-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #8A2BE2;
            transition: width 0.3s ease;
        }

        .footer-link:hover::after {
            width: 100%;
        }

        .social-icon {
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .social-icon:hover {
            transform: scale(1.2);
            color: #8A2BE2;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] text-white">
    <footer class="bg-gradient-to-br from-[#1E2433] to-[#0F172A] py-16 px-12">
        <div class="container mx-auto grid md:grid-cols-4 gap-8">
            <!-- Logo Section -->
            <div class="md:col-span-1">
                <div class="flex items-center space-x-4">
                    <img src="assets/password.gif" alt="Secure Cyber Future Logo" class="w-12 h-12 rounded-lg"/>
                    <h2 class="text-2xl font-bold text-white">Secure Cyber Future</h2>
                </div>
                <p class="text-sm text-gray-400 mt-4">Securing Future, Securing Cyber Security Solutions</p>
            </div>

            <!-- Company -->
            <div>
                <h3 class="font-semibold mb-6 text-lg text-white section-title">Company</h3>
                <ul class="space-y-4">
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-home footer-link-icon"></i>
                        Home
                    </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-info-circle footer-link-icon"></i>
                        About Us
                    </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-users footer-link-icon"></i>
                        Our Team
                    </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-project-diagram footer-link-icon"></i>
                        Projects
                    </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-envelope footer-link-icon"></i>
                        Contact Us
                    </a></li>
                </ul>
            </div>

            <!-- Services -->
            <div>
                <h3 class="font-semibold mb-6 text-lg text-white section-title">Services</h3>
                <ul class="space-y-4">
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-shield-alt footer-link-icon"></i>
                        Threat Detection
                    </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-exclamation-triangle footer-link-icon"></i>
                        Incident Response
                    </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-user-secret footer-link-icon"></i>
                        Penetration Testing
                    </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-bug footer-link-icon"></i>
                        Vulnerability Assessment
                    </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white">
                        <i class="fas fa-fire-alt footer-link-icon"></i>
                        Firewall Management
                    </a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="font-semibold mb-6 text-lg text-white section-title">Contact</h3>
                <ul class="space-y-4">
                    <li class="flex items-center text-gray-300">
                        <i class="fas fa-phone mr-3 text-purple-500"></i>
                        <span>(234) 567-8912</span>
                    </li>
                    <li class="flex items-center text-gray-300">
                        <i class="fas fa-envelope mr-3 text-purple-500"></i>
                        <span>testingwork102030@gmail.com</span>
                    </li>
                    <li class="flex items-center text-gray-300">
                        <i class="fas fa-map-marker-alt mr-3 text-purple-500"></i>
                        <span>Kazipur 6710, Sirajganj, BD</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 mt-12 pt-6 text-center">
            <p class="text-gray-400">&copy; 2025 Secure Cyber Future. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
