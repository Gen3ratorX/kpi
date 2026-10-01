<footer class="modern-footer">
    <div class="footer-content">
        <!-- Brand Section -->
        <div class="footer-brand">
            <a href="index.php" class="footer-brand-link">
                <div class="footer-logo-wrapper">
                    <img src="../static/images/logo.png" alt="NLA Logo">
                </div>
                <div class="footer-brand-text">
                    <h1>Target Performance Appraisal</h1>
                    <span class="footer-tagline">Employee Portal</span>
                </div>
            </a>
        </div>

        <!-- Quick Links -->
        <div class="footer-links">
            <div class="footer-links-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php"><i class="bi bi-house-fill"></i> Dashboard</a></li>
                    <li><a href="#"><i class="bi bi-folder-fill"></i> My Projects</a></li>
                    <li><a href="#"><i class="bi bi-person-fill"></i> My Profile</a></li>
                </ul>
            </div>
            <div class="footer-links-column">
                <h4>Support</h4>
                <ul>
                    <li><a href="#"><i class="bi bi-question-circle-fill"></i> Help Center</a></li>
                    <li><a href="#"><i class="bi bi-envelope-fill"></i> Contact Support</a></li>
                    <li><a href="#"><i class="bi bi-file-text-fill"></i> Documentation</a></li>
                </ul>
            </div>
        </div>

        <!-- Social & Contact -->
        <div class="footer-social">
            <h4>Connect With Us</h4>
            <div class="social-icons">
                <a href="#" class="social-icon" aria-label="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="social-icon" aria-label="Twitter">
                    <i class="bi bi-twitter-x"></i>
                </a>
                <a href="#" class="social-icon" aria-label="LinkedIn">
                    <i class="bi bi-linkedin"></i>
                </a>
                <a href="#" class="social-icon" aria-label="YouTube">
                    <i class="bi bi-youtube"></i>
                </a>
                <a href="#" class="social-icon" aria-label="Email">
                    <i class="bi bi-envelope-fill"></i>
                </a>
            </div>
            <div class="footer-contact">
                <p><i class="bi bi-geo-alt-fill"></i> Accra, Ghana</p>
                <p><i class="bi bi-telephone-fill"></i> +233 20 000 0000</p>
                <p><i class="bi bi-envelope-fill"></i> support@targetappraisal.com</p>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <div id="copyright"></div>
            <div class="footer-version">
                <span>v2.0.1</span>
                <span class="dot-separator">•</span>
                <span>Made with <i class="bi bi-heart-fill" style="color: #10b981;"></i> for Employees</span>
            </div>
        </div>
    </div>
</footer>

<!-- ===== FOOTER STYLES ===== -->
<style>
    /* ===== MODERN EMPLOYEE FOOTER STYLES ===== */
    .modern-footer {
        background: linear-gradient(135deg, #0f0c29 0%, #1a1a2e 30%, #16213e 60%, #0f3460 100%);
        color: rgba(255, 255, 255, 0.8);
        margin-top: 3rem;
        padding: 3rem 2rem 0;
        position: relative;
        overflow: hidden;
        border-top: 2px solid rgba(16, 185, 129, 0.2);
    }

    /* Animated top border glow - Green for employee */
    .modern-footer::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -100%;
        width: 200%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #10b981, #34d399, #10b981, transparent);
        animation: borderGlow 4s linear infinite;
        opacity: 0.5;
    }

    @keyframes borderGlow {
        0% { transform: translateX(0); }
        100% { transform: translateX(50%); }
    }

    /* Decorative background orbs */
    .modern-footer::after {
        content: '';
        position: absolute;
        bottom: -150px;
        right: -100px;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .footer-content {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 2fr 1.5fr 1.5fr;
        gap: 3rem;
        position: relative;
        z-index: 1;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* Brand Section */
    .footer-brand {
        display: flex;
        align-items: flex-start;
    }

    .footer-brand-link {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        text-decoration: none;
        transition: transform 0.3s ease;
    }

    .footer-brand-link:hover {
        transform: translateX(5px);
    }

    .footer-logo-wrapper {
        width: 80px;
        height: 80px;
        min-width: 80px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }

    .footer-brand-link:hover .footer-logo-wrapper {
        background: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.2);
        box-shadow: 0 0 30px rgba(16, 185, 129, 0.1);
    }

    .footer-logo-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 0 20px rgba(16, 185, 129, 0.2));
        transition: all 0.3s ease;
    }

    .footer-brand-link:hover .footer-logo-wrapper img {
        filter: drop-shadow(0 0 30px rgba(16, 185, 129, 0.4));
        transform: scale(1.05) rotate(-3deg);
    }

    .footer-brand-text h1 {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(135deg, #34d399, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
    }

    .footer-tagline {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.4);
        font-weight: 300;
        letter-spacing: 1px;
        -webkit-text-fill-color: rgba(255, 255, 255, 0.4);
    }

    /* Footer Links */
    .footer-links {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .footer-links-column h4 {
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 1.2rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    .footer-links-column h4::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 30px;
        height: 2px;
        background: linear-gradient(90deg, #10b981, #34d399);
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    .footer-links-column:hover h4::after {
        width: 50px;
    }

    .footer-links-column ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links-column ul li {
        margin-bottom: 0.6rem;
    }

    .footer-links-column ul li a {
        color: rgba(255, 255, 255, 0.6);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-links-column ul li a i {
        font-size: 0.8rem;
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .footer-links-column ul li a:hover {
        color: #34d399;
        transform: translateX(5px);
    }

    .footer-links-column ul li a:hover i {
        opacity: 1;
        color: #10b981;
    }

    /* Social Section */
    .footer-social h4 {
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 1.2rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    .footer-social h4::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 30px;
        height: 2px;
        background: linear-gradient(90deg, #10b981, #34d399);
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    .footer-social:hover h4::after {
        width: 50px;
    }

    .social-icons {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .social-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.6);
        font-size: 1.2rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .social-icon:hover {
        transform: translateY(-5px);
        background: rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34d399;
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.2);
    }

    .footer-contact p {
        margin: 0.4rem 0;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.5);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        transition: all 0.3s ease;
    }

    .footer-contact p i {
        color: #10b981;
        font-size: 0.9rem;
        width: 18px;
        text-align: center;
    }

    .footer-contact p:hover {
        color: rgba(255, 255, 255, 0.8);
    }

    /* Footer Bottom */
    .footer-bottom {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem 0;
        position: relative;
        z-index: 1;
    }

    .footer-bottom-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    #copyright {
        color: rgba(255, 255, 255, 0.4);
        font-size: 0.85rem;
    }

    #copyright a {
        color: rgba(255, 255, 255, 0.5);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    #copyright a:hover {
        color: #34d399;
    }

    .footer-version {
        color: rgba(255, 255, 255, 0.3);
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dot-separator {
        color: rgba(255, 255, 255, 0.1);
    }

    .footer-version i {
        transition: all 0.3s ease;
    }

    .footer-version:hover i {
        animation: heartBeat 0.8s ease infinite;
    }

    @keyframes heartBeat {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.1); }
        50% { transform: scale(1); }
        75% { transform: scale(1.1); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .footer-content {
            grid-template-columns: 1.5fr 1.5fr;
            gap: 2rem;
        }
        
        .footer-brand {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 768px) {
        .modern-footer {
            padding: 2rem 1.2rem 0;
        }

        .footer-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .footer-brand-link {
            flex-direction: column;
            text-align: center;
        }

        .footer-brand-text h1 {
            font-size: 1.2rem;
            text-align: center;
        }

        .footer-links {
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .footer-bottom-content {
            flex-direction: column;
            text-align: center;
        }

        #copyright {
            font-size: 0.75rem;
        }

        .footer-version {
            font-size: 0.75rem;
        }

        .social-icons {
            justify-content: center;
        }

        .footer-contact p {
            justify-content: center;
        }

        .footer-links-column h4 {
            text-align: center;
        }

        .footer-links-column h4::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .footer-links-column ul li a {
            justify-content: center;
            width: 100%;
        }

        .footer-social h4 {
            text-align: center;
        }

        .footer-social h4::after {
            left: 50%;
            transform: translateX(-50%);
        }
    }

    @media (max-width: 480px) {
        .footer-links {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .footer-logo-wrapper {
            width: 60px;
            height: 60px;
            min-width: 60px;
        }

        .footer-brand-text h1 {
            font-size: 1rem;
        }

        .social-icon {
            width: 38px;
            height: 38px;
            font-size: 1rem;
        }

        .footer-contact p {
            font-size: 0.75rem;
        }
    }

    /* Scroll to top button (optional enhancement) */
    .scroll-top {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        z-index: 999;
    }

    .scroll-top.visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .scroll-top:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5);
    }
</style>

<!-- ===== SCROLL TO TOP BUTTON ===== -->
<button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- ===== SCRIPTS ===== -->
<script src="../static/js/jquery-3.6.0.min.js"></script>
<script src="../static/js/bootstrap.bundle.min.js"></script>
<script src="../static/js/base.js"></script>
<script src="../static/js/employees.js"></script>
<script>
    $(document).ready(function() {
        // ===== SCROLL TO TOP BUTTON =====
        const $scrollTop = $('#scrollTop');
        
        $(window).on('scroll', function() {
            if ($(window).scrollTop() > 300) {
                $scrollTop.addClass('visible');
            } else {
                $scrollTop.removeClass('visible');
            }
        });

        $scrollTop.on('click', function() {
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        });

        // ===== COPYRIGHT YEAR =====
        const currentYear = new Date().getFullYear();
        $('#copyright').html(
            `Copyright &copy ${currentYear} <a href="index.php">Target Performance Appraisal</a>. All rights reserved.`
        );

        console.log('✅ Employee Footer loaded successfully!');
    });
</script>
</body>
</html>