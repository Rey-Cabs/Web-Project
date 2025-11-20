<?php
// contact.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <script src="<?= base_url(); ?>public/JS/script.js"></script>
</head>
<body>

    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <main class="contact">
            <!-- CONTACT HERO -->
            <section class="contact-hero">
                <h1>Get in Touch</h1>
                <p>We’re here to answer your questions and provide the best healthcare support.</p>
            </section>

            <!-- CONTACT INFO -->
            <section class="contact-info">
                <div class="info-card">
                    <h3>Address</h3>
                    <p>123 Health Street, Wellness City, Country</p>
                </div>
                <div class="info-card">
                    <h3>Phone</h3>
                    <p>+1 (555) 123-4567</p>
                </div>
                <div class="info-card">
                    <h3>Email</h3>
                    <p>info@healthsync.com</p>
                </div>
            </section>

            <!-- CONTACT FORM -->
            <section class="contact-form-section">
                <h2>Send us a message</h2>
                <form class="contact-form" action="<?= site_url('/send-message'); ?>" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Your email address" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" placeholder="Your message" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Send Message</button>
                </form>
            </section><br><br>

            <!-- MAP PLACEHOLDER -->
            <section class="contact-map">
                <h2>Our Location</h2>
                <div class="map-placeholder">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3254.354263918982!2d121.1770840231856!3d13.414242303156678!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bce95218162537%3A0x979acd3f691c3cf9!2sCHSD%20South!5e1!3m2!1sen!2sph!4v1762953963923!5m2!1sen!2sph" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </section>
        </main>
    </div>

    <?php include('templates/footer.php'); ?>
</body>
</html>
